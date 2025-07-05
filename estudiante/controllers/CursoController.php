<?php
/*
 * Controlador para la gestión de cursos y solicitudes del estudiante.
 * Incluye:
 *   - Ver dashboard con cursos disponibles y solicitudes actuales.
 *   - Registro y cancelación de solicitudes vía AJAX.
 *   - Soporte para solicitudes múltiples.
 *   - Validaciones de periodo activo y fase de envío.
 *   - Manejo de límites de cursos y créditos permitidos.
 * Autor: ASI-GRUPO 5
 * Año: 2025
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/conexion.php';

require_once __DIR__ . '/../models/CursoModel.php';
require_once __DIR__ . '/../models/SolicitudModel.php';
require_once __DIR__ . '/../models/PeriodoModel.php';

class CursoController
{
    private PDO            $pdo;
    private CursoModel     $cursoModel;
    private SolicitudModel $solicitudModel;
    private PeriodoModel   $periodoModel;

    public function __construct(PDO $pdo)
    {
        session_start();
        if (
            !isset($_SESSION['usuario_rol']) ||
            $_SESSION['usuario_rol'] !== 'estudiante'
        ) {
            header('Location: ' . BASE_URL . 'login.php');
            exit;
        }

        $this->pdo            = $pdo;
        $this->cursoModel     = new CursoModel($pdo);
        $this->solicitudModel = new SolicitudModel($pdo);
        $this->periodoModel   = new PeriodoModel($pdo);
    }

    /** DASHBOARD principal */
    public function dashboard(): void
    {
        $periodo = $this->periodoModel->getActivo();

        /*──────────────────────────────────────────
          ▸ Sin periodo activo ─ mostrar aviso
        ──────────────────────────────────────────*/
        if (!$periodo) {
            $mensaje = 'No existe un periodo activo.';
            include __DIR__ . '/../views/cursos_inaccesible.php';
            return;
        }

        /*──────────────────────────────────────────
          ▸ Fuera de rango de envío ─ mostrar aviso
        ──────────────────────────────────────────*/
        if (!$this->periodoModel->enviosHabilitados($periodo)) {
            $mensaje = 'El envío de solicitudes está cerrado.';
            include __DIR__ . '/../views/cursos_inaccesible.php';
            return;
        }

        $estudianteId = (int) $_SESSION['usuario_id'];

        /* Solicitudes ya registradas (array) */
        $cursosSolicitados = $this->solicitudModel->byEstudiante(
            $estudianteId,
            (int) $periodo['id']
        );

        $creditosUtilizados = array_sum(
            array_column($cursosSolicitados, 'creditos')
        );

        $maxCursos   = (int) $periodo['maximo_cursos'];
        $maxCreditos = (int) $periodo['maximo_creditos'];

        /* Cursos aún disponibles, filtrando paridad */
        $cursosDisponibles = $this->cursoModel->disponibles(
            $estudianteId,
            (int) $periodo['id'],
            $this->paridadDesdePeriodo((int) $periodo['periodo'])
        );


        /* Renderizar vista normal */
        extract([
            'periodo'           => $periodo,
            'cursosDisponibles' => $cursosDisponibles,
            'cursosSolicitados' => $cursosSolicitados,
            'creditosUtilizados'=> $creditosUtilizados,
            'maxCursos'         => $maxCursos,
            'maxCreditos'       => $maxCreditos
        ]);

        include __DIR__ . '/../views/cursos_dashboard.php';
    }
    /** Registrar solicitud (AJAX) */
    public function solicitar(): void
    {
        header('Content-Type: application/json');

        $payload      = json_decode(file_get_contents('php://input'), true);
        $cursoId      = (int) ($payload['curso_id'] ?? 0);
        $estudianteId = (int) $_SESSION['usuario_id'];

        try {
            $periodo = $this->periodoModel->getActivo();
            if (!$periodo) {
                throw new Exception('No hay periodo activo.');
            }

            $this->solicitudModel->crearConDocumento(
                $estudianteId,
                $cursoId,
                (int) $periodo['id'],
                (int) $periodo['maximo_cursos'],
                (int) $periodo['maximo_creditos']
            );

            echo json_encode(['success' => true]);
        } catch (Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /** Cancelar solicitud (AJAX) */
    public function cancelar(): void
    {
        header('Content-Type: application/json');

        $payload      = json_decode(file_get_contents('php://input'), true);
        $solicitudId  = (int) ($payload['solicitud_id'] ?? 0);
        $estudianteId = (int) $_SESSION['usuario_id'];

        try {
            $this->solicitudModel->eliminar($solicitudId, $estudianteId);
            echo json_encode(['success' => true]);
        } catch (Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    // Al final de la clase CursoController
    public function solicitar_multiple(): void
    {
        header('Content-Type: application/json');
        $payload      = json_decode(file_get_contents('php://input'), true);
        $cursosIds    = $payload['cursos_ids'] ?? [];
        $estudianteId = (int) $_SESSION['usuario_id'];

        try {
            $periodo = $this->periodoModel->getActivo();
            if (!$periodo) {
                throw new Exception('No hay periodo activo.');
            }

            // Itera y crea cada solicitud
            foreach ($cursosIds as $cid) {
                $this->solicitudModel->crearConDocumento(
                    $estudianteId,
                    (int)$cid,
                    (int)$periodo['id'],
                    (int)$periodo['maximo_cursos'],
                    (int)$periodo['maximo_creditos']
                );
            }

            echo json_encode(['success' => true]);
        } catch (Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }


    /** Traduce 1|2|3 a 'par'|'impar'|'todos' */
    private function paridadDesdePeriodo(int $periodo): string
    {
        return $periodo === 1 ? 'par'
             : ($periodo === 2 ? 'impar' : 'todos');
    }
}
