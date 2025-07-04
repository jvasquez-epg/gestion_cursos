<?php
// estudiante/controllers/SolicitudesController.php
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/conexion.php';

require_once __DIR__ . '/../models/SolicitudModel.php';
require_once __DIR__ . '/../models/PeriodoModel.php';

class SolicitudesController
{
    private PDO            $pdo;
    private SolicitudModel $solicitudModel;
    private PeriodoModel   $periodoModel;

    public function __construct(PDO $pdo)
    {
        session_start();
        if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'estudiante') {
            header('Location: ' . BASE_URL . 'login.php');
            exit;
        }

        $this->pdo            = $pdo;
        $this->solicitudModel = new SolicitudModel($pdo);
        $this->periodoModel   = new PeriodoModel($pdo);
    }

    /*────────────────────────────────────────────
     * Dashboard principal: solicitudes vigentes +
     * historial resumido por periodo
     *───────────────────────────────────────────*/
    public function dashboard(): void
    {
        $estudianteId = (int) $_SESSION['usuario_id'];
        $periodo      = $this->periodoModel->getActivo();

        $solicitudesActuales = [];
        $puedeEliminar       = false;
        $puedeDescargarZip   = false;

        if ($periodo) {
            $solicitudesActuales = $this->solicitudModel
                ->byEstudiante($estudianteId, (int) $periodo['id']);

            // Se permite eliminar solo mientras el rango de envío siga abierto
            $puedeEliminar = $this->periodoModel->enviosHabilitados($periodo);

            // Zip descargable después de la fecha fin de envío
            $puedeDescargarZip = !$puedeEliminar;
        }

        // Historial agrupado (periodo, cantidad)
        $historial = $this->solicitudModel
            ->resumenPorPeriodo($estudianteId);

        extract(compact(
            'periodo',
            'solicitudesActuales',
            'puedeEliminar',
            'puedeDescargarZip',
            'historial'
        ));

        include __DIR__ . '/../views/solicitudes_dashboard.php';
    }

    /*────────────────────────────────────────────
     * Vista previa del documento PDF
     *───────────────────────────────────────────*/
    public function ver(): void
    {
        $id          = (int)($_GET['id'] ?? 0);
        $estudiante  = (int)$_SESSION['usuario_id'];

        // Traemos también código U, código curso y nombre
        $row = $this->solicitudModel->filaConDocumento($id, $estudiante);
        if (!$row) {
            http_response_code(404);
            echo 'Documento no encontrado.';
            return;
        }

        /* ─── construir nombre amigable ─── */
        $slugCurso = preg_replace('~[^A-Za-z0-9]+~', '_', $row['curso_nombre']);
        $filename  = sprintf(
            '%s_%s_%s.pdf',
            $row['codigo_u'],         // 21131B0671
            $row['curso_codigo'],     // 131B10081
            strtoupper($slugCurso)    // INFORMATICA_I
        );

        header('Content-Type: application/pdf');
        header("Content-Disposition: inline; filename=\"$filename\"");
        echo $row['documento'];       // binario
    }


    /*────────────────────────────────────────────
     * Eliminar solicitud (solo si está permitido)
     *───────────────────────────────────────────*/
    public function eliminar(): void
    {
        header('Content-Type: application/json');

        // ①  Leer JSON o $_POST
        $payload = json_decode(file_get_contents('php://input'), true);
        $id      = (int)($payload['id'] ?? ($_POST['id'] ?? 0));
        $est     = (int)$_SESSION['usuario_id'];

        try {
            /* ②  Solo se puede eliminar dentro del rango de envío */
            $periodo = $this->periodoModel->getActivo();
            if (!$periodo || !$this->periodoModel->enviosHabilitados($periodo)) {
                throw new Exception('Ya no es posible eliminar solicitudes.');
            }

            /* ③  Ejecutar borrado */
            $this->solicitudModel->eliminar($id, $est);

            echo json_encode(['success' => true]);
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }


    /*────────────────────────────────────────────
     * Descargar ZIP de solicitudes (por periodo)
     *───────────────────────────────────────────*/
    public function descargarZip(): void
    {
        $periodoId    = (int) ($_GET['periodo_id'] ?? 0);
        $estudianteId = (int) $_SESSION['usuario_id'];

        // Recopila documentos → crea zip en memoria
        $docs = $this->solicitudModel->documentosPorPeriodo($estudianteId, $periodoId);
        if (!$docs) {
            http_response_code(404);
            echo 'No hay documentos.';
            return;
        }

        $zip = new ZipArchive();
        $tmp = tempnam(sys_get_temp_dir(), 'solzip_');
        $zip->open($tmp, ZipArchive::CREATE);
        foreach ($docs as $d) {
            $zip->addFromString("{$d['codigo']}.pdf", $d['documento']);
        }
        $zip->close();

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="solicitudes_periodo_' . $periodoId . '.zip"');
        readfile($tmp);
        unlink($tmp);
    }

    /*────────────────────────────────────────────
     * Descargar resolución PDF de un periodo
     *───────────────────────────────────────────*/
    public function descargarResolucion(): void
    {
        $periodoId = (int) ($_GET['periodo_id'] ?? 0);

        $datosPeriodo = $this->periodoModel->getPeriodoConEstado($periodoId);
        if (!$datosPeriodo) {
            header("Location: solicitudes.php?error_resolucion=1&anio=XXXX&per=X");
            exit;
        }

        $anio    = $datosPeriodo['anio'];
        $periodo = $datosPeriodo['periodo'];
        $estado  = $datosPeriodo['estado'];

        if ($estado !== 'cerrado') {
            $anioEnc = urlencode((string)$anio);
            $perEnc  = urlencode((string)$periodo);
            header("Location: solicitudes.php?error_resolucion=1&anio={$anioEnc}&per={$perEnc}");
            exit;
        }

        // Ruta del archivo físico
        $filename = "resolucion_{$anio}_{$periodo}.pdf";
        $ruta     = __DIR__ . "/../../uploads/resoluciones/" . $filename;

        if (!file_exists($ruta)) {
            $anioEnc = urlencode((string)$anio);
            $perEnc  = urlencode((string)$periodo);
            header("Location: solicitudes.php?error_resolucion=1&anio={$anioEnc}&per={$perEnc}");
            exit;
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($ruta));
        readfile($ruta);
        exit;
    }
}
