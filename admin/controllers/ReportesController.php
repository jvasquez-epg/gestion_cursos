<?php
// admin/controllers/ReportesController.php

declare(strict_types=1);

require_once __DIR__ . '/../models/ReportesModel.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Fpdf\Fpdf;

class ReportesController
{
    private PDO $pdo;
    private ReportesModel $model;

    public function __construct(PDO $pdo)
    {
        session_start();
        if (!isset($_SESSION['usuario_rol']) || !in_array($_SESSION['usuario_rol'], ['administrador', 'administrativo'], true)) {
            header('Location: ' . BASE_URL . 'login.php');
            exit;
        }

        $this->pdo = $pdo;
        $this->model = new ReportesModel($pdo);
    }

    public function index(): void
    {
        $periodos = $this->model->listarPeriodos();
        $periodoId = isset($_GET['periodo_id']) ? (int) $_GET['periodo_id'] : $this->model->getUltimoPeriodoId();
        $datos = $this->model->getDatosReportes($periodoId);


        include __DIR__ . '/../views/reportes_dashboard.php';
    }

    public function getMinimoSolicitudes(int $periodoId): int
    {
        $stmt = $this->pdo->prepare("SELECT minimo_solicitudes FROM periodos WHERE id = ?");
        $stmt->execute([$periodoId]);
        return (int) $stmt->fetchColumn() ?: 8;
    }

    private function recortar(string $texto, int $largo): string
    {
        return (strlen($texto) > $largo) ? substr($texto, 0, $largo - 3) . '...' : $texto;
    }


    public function ver(): void
    {
        $tipo = $_GET['tipo'] ?? '';
        $periodoId = (int) ($_GET['periodo_id'] ?? 0);


        $permitidos = ['resolucion_final', 'denegacion', 'apertura', 'historico', 'catalogo'];
        if (!in_array($tipo, $permitidos, true)) {
            http_response_code(400);
            echo 'Reporte no válido';
            return;
        }

        switch ($tipo) {
            case 'denegacion':
                $titulo = 'Acta de Denegación de Cursos';
                break;
            case 'apertura':
                $titulo = 'Acta de Apertura de Cursos';
                break;
            case 'resolucion_final':
                $titulo = 'Resolución Final del Periodo';
                break;
            case 'historico':
                $titulo = 'Histórico de Solicitudes';
                break;
            case 'catalogo':
                $titulo = 'Catálogo de Cursos';
                break;
            default:
                http_response_code(400);
                echo 'Reporte no válido';
                return;
        }

        $pdfUrl = "reportes.php?action=descargar&tipo={$tipo}&periodo_id={$periodoId}&inline=1";

        include __DIR__ . '/../views/reportes_ver_pdf.php';
    }

    public function descargar(): void
    {
        $tipo = $_GET['tipo'] ?? '';
        $periodoId = (int) ($_GET['periodo_id'] ?? 0);
        $inline = isset($_GET['inline']);
        $minimo = $this->model->getMinimoSolicitudes($periodoId);


        if ($tipo === 'denegacion') {
            $datos = $this->model->getCursosDenegados($periodoId);
            if (empty($datos)) {
                echo 'No hay cursos denegados en este periodo.';
                return;
            }

            $stmt = $this->pdo->prepare("SELECT anio, periodo FROM periodos WHERE id = ? LIMIT 1");
            $stmt->execute([$periodoId]);
            $p = $stmt->fetch(PDO::FETCH_ASSOC);
            $labelPeriodo = $p ? "{$p['anio']}-{$p['periodo']}" : "Periodo ID $periodoId";

            $pdf = new FPDF('P', 'mm', 'A4');
            $pdf->AddPage();

            // Logo + encabezado en una misma línea
            $logoPath = __DIR__ . '/../../assets/img/logo_fisi_color.jpg';
            if (file_exists($logoPath)) {
                $pdf->Image($logoPath, 15, 10, 18); // más pequeño y a la izquierda
            }

            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(35); // espacio para el logo
            $pdf->Cell(0, 10, utf8_decode('FACULTAD DE INGENIERÍA DE SISTEMAS E INFORMÁTICA'), 0, 1, 'L');
            $pdf->Ln(3);

            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell(0, 10, utf8_decode('Acta de Denegación de Cursos'), 0, 1, 'C');

            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(0, 8, utf8_decode("Periodo: $labelPeriodo"), 0, 1, 'C');
            $pdf->Cell(0, 8, utf8_decode("Mínimo de solicitudes requeridas: $minimo"), 0, 1, 'C');

            $pdf->Ln(4);

            // Texto formal
            $pdf->SetFont('Arial', '', 10);
            $intro = "La presente acta detalla los cursos solicitados por los estudiantes durante el periodo académico $labelPeriodo que han sido denegados. "
                . "Las causas de denegación incluyen el número insuficiente de solicitudes y la falta de asignación de docente responsable.";
            $pdf->MultiCell(0, 6, utf8_decode($intro));
            $pdf->Ln(5);

            // Encabezado de tabla
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetFillColor(230, 230, 230);
            $pdf->Cell(10, 8, utf8_decode('N°'), 1, 0, 'C', true);
            $pdf->Cell(30, 8, utf8_decode('Código'), 1, 0, 'C', true);
            $pdf->Cell(80, 8, utf8_decode('Curso'), 1, 0, 'C', true);
            $pdf->Cell(20, 8, utf8_decode('Solic.'), 1, 0, 'C', true);
            $pdf->Cell(50, 8, utf8_decode('Estado'), 1, 1, 'C', true);

            // Contenido
            $pdf->SetFont('Arial', '', 9);
            $n = 1;
            foreach ($datos as $row) {
                $estado = $row['estado_denegacion']; // no truncamos
                $pdf->Cell(10, 7, $n++, 1, 0, 'C');
                $pdf->Cell(30, 7, utf8_decode($row['codigo_curso']), 1);
                $pdf->Cell(80, 7, utf8_decode(mb_strimwidth($row['nombre_curso'], 0, 60, '...')), 1);
                $pdf->Cell(20, 7, $row['total_solicitudes'], 1, 0, 'C');
                $pdf->Cell(50, 7, utf8_decode($estado), 1, 1);
            }

            // Pie institucional
            $pdf->Ln(8);
            $pdf->SetFont('Arial', 'I', 9);
            $pdf->Cell(0, 6, utf8_decode('Documento generado automáticamente por el sistema de gestión académica.'), 0, 1, 'C');

            $nombreArchivo = "acta_denegacion_{$labelPeriodo}.pdf";
            $modo = $inline ? 'I' : 'D';
            $pdf->Output($modo, $nombreArchivo);
            return;
        }

        if ($tipo === 'apertura') {
            $datos = $this->model->getActaApertura($periodoId);
            if (empty($datos)) {
                echo 'No hay cursos aperturados en este periodo.';
                return;
            }

            // Obtener label del periodo
            $info = $this->model->getPeriodoLabel($periodoId);
            $labelPeriodo = $info ? "{$info['anio']}-{$info['periodo']}" : "Desconocido";

            // Crear PDF
            $pdf = new FPDF('P', 'mm', 'A4');
            $pdf->AddPage();

            // Logo y encabezado
            $logoPath = __DIR__ . '/../../assets/img/logo_fisi_color.jpg';
            if (file_exists($logoPath)) {
                $pdf->Image($logoPath, 15, 10, 18); // más pequeño
            }
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(35); // espacio a la derecha del logo
            $pdf->Cell(0, 10, utf8_decode('FACULTAD DE INGENIERÍA DE SISTEMAS E INFORMÁTICA'), 0, 1, 'L');
            $pdf->Ln(3);

            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell(0, 10, utf8_decode('Acta de Apertura de Cursos'), 0, 1, 'C');

            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(0, 8, utf8_decode("Periodo: $labelPeriodo"), 0, 1, 'C');

            // Asume que $minimo ya fue obtenido:
            $minimo = $this->model->getMinimoSolicitudes($periodoId);
            $pdf->Cell(0, 8, utf8_decode("Mínimo de solicitudes requeridas: $minimo"), 0, 1, 'C');

            $pdf->Ln(4);


            // Texto introductorio
            $pdf->SetFont('Arial', '', 10);
            $texto = "La presente acta consigna los cursos que han sido aperturados para el periodo académico $labelPeriodo, "
                . "asignados oficialmente a docentes responsables, de acuerdo con las solicitudes de los estudiantes y la programación académica.";
            $pdf->MultiCell(0, 6, utf8_decode($texto));
            $pdf->Ln(5);

            // Tabla
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetFillColor(230, 230, 230);
            $pdf->Cell(10, 8, utf8_decode('Nº'), 1, 0, 'C', true);
            $pdf->Cell(25, 8, utf8_decode('Código'), 1, 0, 'C', true);
            $pdf->Cell(60, 8, 'Curso', 1, 0, 'C', true);
            $pdf->Cell(60, 8, 'Docente', 1, 0, 'C', true);
            $pdf->Cell(25, 8, 'Solicitudes', 1, 1, 'C', true);

            $pdf->SetFont('Arial', '', 9);
            $i = 1;
            foreach ($datos as $row) {
                $pdf->Cell(10, 7, $i++, 1);
                $pdf->Cell(25, 7, $row['codigo_curso'], 1);
                $pdf->Cell(60, 7, utf8_decode($this->recortar($row['nombre_curso'], 35)), 1);
                $pdf->Cell(60, 7, utf8_decode($this->recortar($row['docente'], 35)), 1);
                $pdf->Cell(25, 7, $row['total_solicitudes'], 1, 1);
            }

            // Salida del PDF
            $nombreArchivo = "acta_apertura_periodo_{$periodoId}.pdf";
            if (ob_get_length()) ob_end_clean();
            $modo = $inline ? 'I' : 'D';
            $pdf->Output($modo, $nombreArchivo);
            return;
        }

        if ($tipo === 'historico') {
            $datos = $this->model->getHistoricoSolicitudes($periodoId);
            if (empty($datos)) {
                echo 'No hay solicitudes registradas en este periodo.';
                return;
            }

            $stmt = $this->pdo->prepare("SELECT anio, periodo FROM periodos WHERE id = ?");
            $stmt->execute([$periodoId]);
            $p = $stmt->fetch(PDO::FETCH_ASSOC);
            $labelPeriodo = $p ? "{$p['anio']}-{$p['periodo']}" : "Periodo ID $periodoId";

            $pdf = new FPDF('L', 'mm', 'A4'); // ← orientación horizontal
            $pdf->AddPage();

            // Logo y encabezado
            $logoPath = __DIR__ . '/../../assets/img/logo_fisi_color.jpg';
            if (file_exists($logoPath)) {
                $pdf->Image($logoPath, 10, 10, 20);
            }

            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(30);
            $pdf->Cell(0, 10, utf8_decode('FACULTAD DE INGENIERÍA DE SISTEMAS E INFORMÁTICA'), 0, 1, 'L');
            $pdf->Ln(3);

            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell(0, 10, utf8_decode('Histórico de Solicitudes de Cursos'), 0, 1, 'C');

            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(0, 8, utf8_decode("Periodo: $labelPeriodo"), 0, 1, 'C');

            $pdf->Ln(4);

            // Texto formal
            $pdf->SetFont('Arial', '', 10);
            $intro = "Este documento detalla el histórico de solicitudes de asignaturas realizadas por los estudiantes durante el periodo académico $labelPeriodo. "
                . "Incluye la identificación del estudiante, el curso solicitado y la fecha de registro de la solicitud.";
            $pdf->MultiCell(0, 6, utf8_decode($intro));
            $pdf->Ln(5);

            // Encabezado tabla
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetFillColor(230, 230, 230);
            $pdf->Cell(10, 8, utf8_decode('N°'), 1, 0, 'C', true);
            $pdf->Cell(30, 8, utf8_decode('Código'), 1, 0, 'C', true);
            $pdf->Cell(100, 8, 'Estudiante', 1, 0, 'C', true);
            $pdf->Cell(90, 8, 'Curso', 1, 0, 'C', true);
            $pdf->Cell(35, 8, 'Fecha solicitud', 1, 1, 'C', true);

            // Cuerpo tabla
            $pdf->SetFont('Arial', '', 9);
            $n = 1;
            foreach ($datos as $row) {
                $pdf->Cell(10, 7, $n++, 1, 0, 'C');
                $pdf->Cell(30, 7, $row['codigo_estudiante'], 1);
                $pdf->Cell(100, 7, utf8_decode($this->recortar($row['estudiante'], 38)), 1);
                $pdf->Cell(90, 7, utf8_decode($this->recortar($row['curso'], 55)), 1);
                $pdf->Cell(35, 7, $row['fecha_solicitud'], 1, 1);
            }

            $pdf->Ln(6);
            $pdf->SetFont('Arial', 'I', 9);
            $pdf->Cell(0, 6, utf8_decode('Documento generado automáticamente por el sistema de gestión académica.'), 0, 1, 'C');

            if (ob_get_length()) ob_end_clean();
            $modo = $inline ? 'I' : 'D';
            $pdf->Output($modo, "historico_solicitudes_{$labelPeriodo}.pdf");
            return;
        }
        if ($tipo === 'catalogo') {
            $datos = $this->model->getCatalogoCursos($periodoId);
            if (empty($datos)) {
                echo 'No hay cursos disponibles para el catálogo en este periodo.';
                return;
            }

            // Obtener label del periodo
            $info = $this->model->getPeriodoLabel($periodoId);
            $labelPeriodo = $info ? "{$info['anio']}-{$info['periodo']}" : "Desconocido";

            $pdf = new FPDF('L', 'mm', 'A4'); // L = Landscape
            $pdf->AddPage();

            // Logo
            $logoPath = __DIR__ . '/../../assets/img/logo_fisi_color.jpg';
            if (file_exists($logoPath)) {
                $pdf->Image($logoPath, 10, 10, 22); // pequeño, lado izquierdo
            }

            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(35);
            $pdf->Cell(0, 10, utf8_decode('FACULTAD DE INGENIERÍA DE SISTEMAS E INFORMÁTICA'), 0, 1, 'C');
            $pdf->Ln(3);

            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell(0, 10, utf8_decode('Catálogo de Cursos por Periodo'), 0, 1, 'C');

            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(0, 8, utf8_decode("Periodo: $labelPeriodo"), 0, 1, 'C');
            $pdf->Ln(2);

            // Texto formal descriptivo
            $pdf->SetFont('Arial', '', 10);
            $texto = "El presente catálogo contiene los cursos académicos disponibles para el periodo $labelPeriodo. "
                . "Los cursos han sido filtrados según el ciclo correspondiente al periodo: ciclos pares, impares o todos, "
                . "de acuerdo con la política académica vigente.";
            $pdf->MultiCell(0, 6, utf8_decode($texto));
            $pdf->Ln(4);

            // Tabla
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetFillColor(230, 230, 230);
            $pdf->Cell(12, 8, utf8_decode('N°'), 1, 0, 'C', true);
            $pdf->Cell(40, 8, utf8_decode('Código'), 1, 0, 'C', true);
            $pdf->Cell(160, 8, 'Nombre del Curso', 1, 0, 'C', true);
            $pdf->Cell(25, 8, 'Ciclo', 1, 0, 'C', true);
            $pdf->Cell(25, 8, utf8_decode('Créditos'), 1, 1, 'C', true);

            $pdf->SetFont('Arial', '', 9);
            $n = 1;
            foreach ($datos as $row) {
                $pdf->Cell(12, 7, $n++, 1, 0, 'C');
                $pdf->Cell(40, 7, utf8_decode($row['codigo']), 1);
                $nombreCurso = utf8_decode(mb_strimwidth($row['nombre'] ?? '', 0, 95, '...'));
                $pdf->Cell(160, 7, $nombreCurso, 1);
                $pdf->Cell(25, 7, $row['ciclo'], 1, 0, 'C');
                $pdf->Cell(25, 7, $row['creditos'], 1, 1, 'C');
            }

            // Pie de página
            $pdf->Ln(6);
            $pdf->SetFont('Arial', 'I', 9);
            $pdf->Cell(0, 6, utf8_decode('Documento generado automáticamente por el sistema de gestión académica.'), 0, 1, 'C');

            $nombreArchivo = "catalogo_cursos_{$labelPeriodo}.pdf";
            if (ob_get_length()) ob_end_clean();
            $modo = $inline ? 'I' : 'D';
            $pdf->Output($modo, $nombreArchivo);
            return;
        }

        if ($tipo === 'resolucion_final') {
            $stmt = $this->pdo->prepare("SELECT anio, periodo FROM periodos WHERE id = ? LIMIT 1");
            $stmt->execute([$periodoId]);
            $p = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$p) {
                echo 'Periodo no encontrado.';
                return;
            }

            $anio = $p['anio'];
            $periodo = $p['periodo'];
            $archivo = __DIR__ . "/../../uploads/resoluciones/resolucion_{$anio}_{$periodo}.pdf";

            if (!file_exists($archivo)) {
                echo 'La resolución final para este periodo no está disponible.';
                return;
            }

            $nombreArchivo = "resolucion_final_{$anio}_{$periodo}.pdf";
            $modo = $inline ? 'I' : 'D';

            header('Content-Type: application/pdf');
            header('Content-Disposition: ' . ($modo === 'I' ? 'inline' : 'attachment') . "; filename=\"$nombreArchivo\"");
            header('Content-Length: ' . filesize($archivo));
            readfile($archivo);
            return;
        }
    }
}
