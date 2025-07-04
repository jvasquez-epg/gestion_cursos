<?php
// admin/controllers/PeriodoController.php

require_once __DIR__ . '/../models/PeriodoModel.php';
require_once __DIR__ . '/../models/SolicitudModel.php';

require_once __DIR__ . '/../../vendor/autoload.php';

use Emscherland\Fpdf\Fpdf as FPDF;

class PeriodoController
{
    private PDO $pdo;
    private PeriodoModel $periodoModel;
    private SolicitudModel $solicitudModel;


    public function __construct(PDO $pdo)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        // Sólo administradores o administrativos
        if (
            empty($_SESSION['usuario_rol']) ||
            !in_array($_SESSION['usuario_rol'], ['administrador', 'administrativo'], true)
        ) {
            header('Location: ' . BASE_URL . 'login.php');
            exit;
        }

        $this->pdo = $pdo;
        $this->periodoModel = new PeriodoModel($pdo);
        $this->solicitudModel = new SolicitudModel($pdo);

    }

    /**
     * Muestra la lista de periodos: cierra expirados y genera resoluciones
     */
    public function index()
    {
        // 1) Cerrar los que vencieron y obtener IDs
        $cerrados = $this->periodoModel->verificarYActualizarEstado();
        // 2) Generar resolución automática si aún no existe
        foreach ($cerrados as $pid) {
            if (!$this->periodoModel->getResolucion($pid)) {
                $this->generateResolucionPDF($pid);
            }
        }

        // 3) Cargar datos para la vista
        $periodoActivo = $this->periodoModel->getActivo();
        if ($periodoActivo) {
            $id  = (int)$periodoActivo['id'];
            $periodoActivo['total_solicitudes'] = $this->periodoModel->countEnvios($id);
            $periodoActivo['cursos_asignados']  = $this->periodoModel->countAperturados($id);
            $periodoActivo['ultima_resolucion'] = $this->periodoModel->getResolucion($id);
        }

        $historial = $this->periodoModel->getHistorial();
        foreach ($historial as &$p) {
            $pid = (int)$p['id'];
            $p['total_solicitudes'] = $this->periodoModel->countEnvios($pid);
            $p['cursos_asignados']  = $this->periodoModel->countAperturados($pid);
            $p['ultima_resolucion'] = $this->periodoModel->getResolucion($pid);
        }
        unset($p);

        // Flash messages
        $error   = $_SESSION['error']   ?? null;
        $success = $_SESSION['success'] ?? null;
        unset($_SESSION['error'], $_SESSION['success']);

        include __DIR__ . '/../views/periodos.php';
    }


    public function create(): void
    {
        $last    = $this->periodoModel->getUltimo();
        $anio    = $last ? (int)$last['anio'] : (int)date('Y');
        $periodo = $last ? (int)$last['periodo'] : 1;
        if ($periodo < 3) {
            $periodo++;
        } else {
            $anio++;
            $periodo = 1;
        }

        $now = date('Y-m-d H:i:00');
        $periodData = [
            'anio'                       => $anio,
            'periodo'                    => $periodo,
            'inicio_envio_solicitudes'   => $now,
            'fin_envio_solicitudes'      => $now,
            'inicio_asignacion_docentes' => $now,
            'fin_asignacion_docentes'    => $now,
            'minimo_solicitudes'         => 15,
            'maximo_cursos'              => 3,
            'maximo_creditos'            => 12,
        ];

        $error   = $_SESSION['error']   ?? null;
        $success = $_SESSION['success'] ?? null;
        unset($_SESSION['error'], $_SESSION['success']);

        $formAction  = '?action=store';
        $buttonLabel = 'Registrar Periodo';
        include __DIR__ . '/../views/periodos_create.php';
    }

    public function store(array $data): void
    {
        $last         = $this->periodoModel->getUltimo();
        $expectedPer  = $last
            ? ($last['periodo'] < 3 ? $last['periodo'] + 1 : 1)
            : 1;
        $expectedYear = $last
            ? ($last['periodo'] < 3 ? $last['anio'] : $last['anio'] + 1)
            : (int)date('Y');

        if ((int)$data['periodo'] !== $expectedPer || (int)$data['anio'] !== $expectedYear) {
            $_SESSION['error'] = "Solo puedes crear el periodo siguiente: {$expectedYear}-{$expectedPer}.";
            header('Location: ' . BASE_URL . 'admin/periodos.php?action=create');
            exit;
        }

        if (
            $msg = $this->validarFechas($data)
            ?? $this->validarNumeros($data)
            ?? $this->validarDuplicado($data)
        ) {
            $_SESSION['error'] = $msg;
            header('Location: ' . BASE_URL . 'admin/periodos.php?action=create');
            exit;
        }

        $this->normalizarFechas($data);
        $this->periodoModel->closeActivo();
        $this->periodoModel->create(
            anio: $expectedYear,
            periodo: $expectedPer,
            fEnvioInicio: $data['inicio_envio_solicitudes'],
            fEnvioFin: $data['fin_envio_solicitudes'],
            fAperturaInicio: $data['inicio_asignacion_docentes'],
            fAperturaFin: $data['fin_asignacion_docentes'],
            minSolicitudes: (int)$data['minimo_solicitudes'],
            maxCursos: (int)$data['maximo_cursos'],
            maxCreditos: (int)$data['maximo_creditos']
        );

        $_SESSION['success'] = "Periodo {$expectedYear}-{$expectedPer} creado correctamente.";
        header('Location: ' . BASE_URL . 'admin/periodos.php');
        exit;
    }

    public function edit(int $id): void
    {
        $periodo = $this->periodoModel->getById($id);
        if (!$periodo) {
            header('Location: ' . BASE_URL . 'admin/periodos.php');
            exit;
        }
        $periodData  = $periodo;
        $formAction  = '?action=update&id=' . $id;
        $buttonLabel = 'Guardar Cambios';
        include __DIR__ . '/../views/periodos_create.php';
    }

    public function update(array $data): void
    {
        $id = (int)($_GET['id'] ?? 0);

        if ($msg = $this->validarFechas($data) ?? $this->validarNumeros($data)) {
            $_SESSION['error'] = $msg;
            header("Location: " . BASE_URL . "admin/periodos.php?action=edit&id={$id}");
            exit;
        }

        $maxCursos   = (int)$data['maximo_cursos'];
        $maxCreditos = (int)$data['maximo_creditos'];
        $confCount   = $this->periodoModel->countSolicitudesExcedentes($id, $maxCursos, $maxCreditos);

        if ($confCount > 0) {
            $_SESSION['error'] = "Hay {$confCount} solicitud(es) que exceden los nuevos límites.";
            header("Location: " . BASE_URL . "admin/periodos.php?action=edit&id={$id}");
            exit;
        }

        $this->normalizarFechas($data);
        $this->periodoModel->update(
            id: $id,
            anio: (int)$data['anio'],
            periodo: (int)$data['periodo'],
            fEnvioInicio: $data['inicio_envio_solicitudes'],
            fEnvioFin: $data['fin_envio_solicitudes'],
            fAperturaInicio: $data['inicio_asignacion_docentes'],
            fAperturaFin: $data['fin_asignacion_docentes'],
            minSolicitudes: (int)$data['minimo_solicitudes'],
            maxCursos: $maxCursos,
            maxCreditos: $maxCreditos
        );

        $_SESSION['success'] = 'Periodo actualizado correctamente.';
        header('Location: ' . BASE_URL . 'admin/periodos.php');
        exit;
    }

    public function delete(int $id): void
    {
        if ($this->periodoModel->hasSolicitudes($id)) {
            $_SESSION['error'] = 'No se puede eliminar: ya existen solicitudes.';
        } else {
            $this->periodoModel->delete($id);
            $_SESSION['success'] = 'Periodo eliminado correctamente.';
        }
        header('Location: ' . BASE_URL . 'admin/periodos.php');
        exit;
    }

    // ---------------------------------------------------
    // Métodos auxiliares
    // ---------------------------------------------------

    private function normalizarFechas(array &$data): void
    {
        foreach (
            [
                'inicio_envio_solicitudes',
                'fin_envio_solicitudes',
                'inicio_asignacion_docentes',
                'fin_asignacion_docentes'
            ] as $campo
        ) {
            if (!empty($data[$campo])) {
                $data[$campo] = str_replace('T', ' ', $data[$campo]);
                if (strlen($data[$campo]) === 16) {
                    $data[$campo] .= ':00';
                }
            }
        }
    }

    private function validarFechas(array $d): ?string
    {
        $iniEnv = strtotime($d['inicio_envio_solicitudes']);
        $finEnv = strtotime($d['fin_envio_solicitudes']);
        $iniApt = strtotime($d['inicio_asignacion_docentes']);
        $finApt = strtotime($d['fin_asignacion_docentes']);

        if ($iniEnv >= $finEnv) {
            return 'La fecha de fin de envío debe ser posterior al inicio.';
        }
        if ($finEnv > $iniApt) {
            return 'La asignación debe iniciar después del fin de envío.';
        }
        if ($iniApt > $finApt) {
            return 'La fecha de fin de asignación debe ser posterior al inicio.';
        }
        return null;
    }

    private function validarNumeros(array $d): ?string
    {
        $minS  = (int)$d['minimo_solicitudes'];
        $maxC  = (int)$d['maximo_cursos'];
        $maxCr = (int)$d['maximo_creditos'];
        if ($minS <= 0 || $maxC <= 0 || $maxCr <= 0) {
            return 'Los valores deben ser mayores que cero.';
        }
        return null;
    }

    private function validarDuplicado(array $d): ?string
    {
        if ($this->periodoModel->existePeriodo((int)$d['anio'], (int)$d['periodo'])) {
            return "Ya existe el periodo {$d['anio']}-{$d['periodo']}.";
        }
        return null;
    }



    /**
     * Genera el PDF de resolución y lo guarda
     */
    private function generateResolucionPDF(int $id): void
    {
        // Función auxiliar para convertir texto a ISO-8859-1 (tildes y eñes)
        function toPDF($str)
        {
            return mb_convert_encoding($str, 'ISO-8859-1', 'UTF-8');
        }

        // Dibuja una tabla, muestra "Sin registros" si la tabla está vacía
        function drawTable($pdf, $headers, $data, $widths)
        {
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetFillColor(200, 220, 255);
            foreach ($headers as $i => $h) {
                $pdf->Cell($widths[$i], 7, toPDF($h), 1, 0, 'C', true);
            }
            $pdf->Ln();
            $pdf->SetFont('Arial', '', 8);

            if (empty($data)) {
                $pdf->Cell(array_sum($widths), 6, toPDF('Sin registros'), 1, 0, 'C');
                $pdf->Ln(4);
            } else {
                foreach ($data as $i => $row) {
                    $pdf->Cell($widths[0], 6, $i + 1, 1, 0, 'C');
                    foreach (array_slice($widths, 1) as $j => $w) {
                        $keys = array_keys($row);
                        $key = $keys[$j];
                        $valor = isset($row[$key]) ? $row[$key] : '';
                        $pdf->Cell($w, 6, toPDF((string)$valor), 1, 0, 'L');
                    }
                    $pdf->Ln();
                }
                $pdf->Ln(4);
            }
        }

        $periodo = $this->periodoModel->getById($id);
        $anio    = $periodo['anio'];
        $numPer  = $periodo['periodo'];

        // Determinar el ciclo de cursos según el periodo
        $cicloPeriodo = 3; // 1: impar, 2: par, 3: ambos
        if ($numPer == 1) $cicloPeriodo = 1;
        elseif ($numPer == 2) $cicloPeriodo = 2;

        // Consulta de cursos según ciclo (ver lógica en modelo)
        $cursosAsignados      = $this->periodoModel->getCursosAsignados($id, $cicloPeriodo);
        $cursosSinDocente     = $this->periodoModel->getCursosSinDocente($id, $cicloPeriodo);
        $cursosSinSolicitudes = $this->periodoModel->getCursosSinSolicitudes($id, $cicloPeriodo);

        $minSolicitudes = $periodo['minimo_solicitudes'];

        $pdf = new Fpdf(orientation: 'P', unit: 'mm', format: 'A4');
        $pdf->AliasNbPages();
        $pdf->SetMargins(15, 15, 15);
        $pdf->AddPage();

        // Encabezado
        $logoPath = __DIR__ . '/../../assets/img/logo_fisi_color.jpg';
        if (file_exists($logoPath)) {
            $pdf->Image($logoPath, 15, 10, 15); // Tamaño ajustado para que no sea tan grande
        }
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(10);
        $pdf->Cell(0, 10, toPDF('UNIVERSIDAD NACIONAL DE LA AMAZONÍA PERUANA'), 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(10);
        $pdf->Cell(0, 8, toPDF('FACULTAD DE INGENIERÍA DE SISTEMAS E INFORMÁTICA'), 0, 1, 'C');
        $pdf->Ln(5);

        // Número de resolución correlativo
        $num = sprintf('%03d', $this->periodoModel->getNextResolucionNumber($anio));
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 6, toPDF("OFICIO N.º {$num}/{$anio} - FISI"), 0, 1, 'R');
        $pdf->Ln(4);

        // Asunto y fecha
        $fecha = (new DateTime())->format('d/m/Y');
        $pdf->Cell(0, 6, toPDF("Asunto: Apertura de Cursos de Nivelación y Vacacional"), 0, 1);
        $pdf->Cell(0, 6, toPDF("Fecha: {$fecha}"), 0, 1);
        $pdf->Ln(6);

        // Introducción
        $pdf->MultiCell(
            0,
            6,
            toPDF(
                "Por medio del presente, y en cumplimiento de la planificación académica correspondiente al periodo "
                    . "{$anio}-{$numPer}, se comunica de manera oficial la resolución de apertura de cursos, "
                    . "así como la asignación de docentes y la denegación de cursos por las razones que a continuación se detallan."
            )
        );
        $pdf->Ln(6);

        // Cursos aperturados con docentes asignados
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 6, toPDF('CURSOS APERTURADOS CON DOCENTES ASIGNADOS'), 0, 1);
        drawTable(
            $pdf,
            ['N°', 'Código de Curso', 'Nombre del Curso', 'Docente Asignado', 'Solicitudes'],
            $cursosAsignados,
            [10, 30, 60, 60, 30]
        );
        $pdf->Ln(3);

        // Cursos denegados por falta de docente
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 6, toPDF('CURSOS DENEGADOS POR FALTA DE DOCENTE'), 0, 1);
        drawTable(
            $pdf,
            ['N°', 'Código de Curso', 'Nombre del Curso', 'Solicitudes'],
            $cursosSinDocente,
            [10, 40, 110, 30]
        );
        $pdf->Ln(3);

        // Cursos denegados por falta de solicitudes
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 6, toPDF('CURSOS DENEGADOS POR FALTA DE SOLICITUDES'), 0, 1);
        drawTable(
            $pdf,
            ['N°', 'Código de Curso', 'Nombre del Curso', 'Solicitudes'],
            $cursosSinSolicitudes,
            [10, 40, 110, 30]
        );
        $pdf->Ln(3);

        // Observaciones y firma
        $pdf->SetFont('Arial', '', 9);
        $pdf->MultiCell(
            0,
            6,
            toPDF(
                "OBSERVACIONES:\n"
                    . "- Los cursos aperturados han cumplido con el mínimo de solicitudes establecidas ({$minSolicitudes}) y serán desarrollados de manera intensiva durante el periodo {$anio}-{$numPer}.\n"
                    . "- Los cursos que no han sido aperturados podrán ser reprogramados en próximos ciclos si se cumplen las condiciones necesarias."
            )
        );
        $pdf->Ln(8);
        $pdf->Cell(0, 6, toPDF('Atentamente,'), 0, 1);
        $pdf->Ln(12);
        $pdf->Cell(0, 6, toPDF('Facultad de Ingeniería de Sistemas e Informática'), 0, 1);
        $pdf->Cell(0, 6, toPDF('Universidad Nacional de la Amazonía Peruana'), 0, 1);

        // Guardar en disco y registrar en BD
        $filename = "resolucion_{$anio}_{$numPer}.pdf";
        $dir      = __DIR__ . '/../../uploads/resoluciones/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $pdf->Output($dir . $filename, 'F');

        $this->periodoModel->saveResolucion($id, $filename);
    }


    /**
     * Descarga la resolución existente o la crea si falta.
     */
    public function resolucion(int $id)
    {
        $p = $this->periodoModel->getById($id);
        if (!$p || $p['estado'] !== 'cerrado') {
            $_SESSION['error'] = $p
                ? 'La resolución solo está disponible tras el cierre del periodo.'
                : 'Periodo no encontrado.';
            header('Location: ' . BASE_URL . 'admin/periodos.php');
            exit;
        }

        if ($doc = $this->periodoModel->getResolucion($id)) {
            header("Location: " . BASE_URL . "uploads/resoluciones/{$doc}");
            exit;
        }

        // Si no existe aún: lo generamos en caliente
        $this->generateResolucionPDF($id);
        header("Location: " . BASE_URL . "uploads/resoluciones/{$this->periodoModel->getResolucion($id)}");
        exit;
    }

    public function export(): void
    {
        $periodoId = (int) ($_GET['id'] ?? 0);

        // Obtener datos del periodo
        $periodo = $this->periodoModel->getPeriodoLabel($periodoId);
        if (!$periodo) {
            header('Location: periodos.php?error=Periodo no encontrado');
            exit;
        }

        $anio     = $periodo['anio'];
        $ciclo    = $periodo['periodo'];
        $nombreZip = "solicitudes_{$anio}-{$ciclo}.zip";

        // Obtener todas las solicitudes del periodo agrupadas por curso
        $solicitudes = $this->solicitudModel->documentosAgrupadosPorCurso($periodoId); // Este método lo defines tú

        if (empty($solicitudes)) {
            header('Location: periodos.php?error=No hay solicitudes en este periodo');
            exit;
        }

        $tmp = tempnam(sys_get_temp_dir(), 'solzip_');
        $zip = new ZipArchive();
        $zip->open($tmp, ZipArchive::CREATE);

        foreach ($solicitudes as $cursoCodigo => $documentos) {
            foreach ($documentos as $doc) {
                $nombreCurso = preg_replace('~[^A-Za-z0-9]+~', '_', strtoupper($doc['nombre_curso']));
                $nombreArchivo = "{$doc['codigo_estudiante']}_{$cursoCodigo}_{$nombreCurso}.pdf";
                $rutaInterna = "{$cursoCodigo}/{$nombreArchivo}";

                $zip->addFromString($rutaInterna, $doc['documento']);
            }
        }

        $zip->close();

        // Descargar ZIP
        header('Content-Type: application/zip');
        header("Content-Disposition: attachment; filename=\"{$nombreZip}\"");
        header('Content-Length: ' . filesize($tmp));
        readfile($tmp);
        unlink($tmp);
        exit;
    }
    // (Aquí irían los métodos create, store, edit, update y delete sin cambios sustanciales)
}
