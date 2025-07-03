<?php
/**
 * Dashboard Controller - Estudiante
 * 
 * Controlador principal para el dashboard de estudiantes que maneja
 * la visualización de estadísticas, solicitudes y asignaciones de cursos.
 * 
 * @author Sistema Académico
 * @version 1.0
 * @since 2025
 */

declare(strict_types=1);

require_once __DIR__ . '/../models/DashboardModel.php';

class DashboardController
{
    private DashboardModel $model;
    private int $userId;
    private int $mallaId;

    /**
     * Constructor del controlador
     * 
     * Inicializa la sesión, valida el usuario y obtiene la malla curricular
     * 
     * @param PDO $pdo Conexión a la base de datos
     * @throws Exception Si hay errores en la inicialización
     */
    public function __construct(PDO $pdo)
    {
        $this->initializeSession();
        $this->validateUserSession();
        $this->setUserData($pdo);
        $this->model = new DashboardModel($pdo, $this->userId, $this->mallaId);
    }

    /**
     * Acción principal del dashboard
     * 
     * Renderiza la vista principal con todas las estadísticas y datos necesarios
     * 
     * @return void
     */
    public function index(): void
    {
        try {
            $dashboardData = $this->prepareDashboardData();
            $this->renderDashboard($dashboardData);
        } catch (Exception $e) {
            error_log("Error en Dashboard: " . $e->getMessage());
            $this->renderErrorDashboard('Error interno del sistema. Por favor, contacte al administrador.');
        }
    }

    /**
     * Inicializa la sesión si no está activa
     * 
     * @return void
     */
    private function initializeSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Valida que el usuario esté autenticado
     * 
     * @return void
     */
    private function validateUserSession(): void
    {
        if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
            $this->redirectToLogin();
        }
    }

    /**
     * Establece los datos del usuario desde la base de datos
     * 
     * @param PDO $pdo Conexión a la base de datos
     * @return void
     * @throws Exception Si no se encuentran los datos del estudiante
     */
    private function setUserData(PDO $pdo): void
    {
        $this->userId = (int) $_SESSION['usuario_id'];
        
        $stmt = $pdo->prepare("SELECT malla_id FROM estudiantes WHERE id = ?");
        $stmt->execute([$this->userId]);
        $result = $stmt->fetchColumn();
        
        if ($result === false) {
            throw new Exception("No se encontraron datos del estudiante con ID: {$this->userId}");
        }
        
        $this->mallaId = (int) $result;
    }

    /**
     * Prepara todos los datos necesarios para el dashboard
     * 
     * @return array Datos estructurados para el dashboard
     */
    private function prepareDashboardData(): array
    {
        $periodo = $this->model->getPeriodoActivo();
        
        if (!$periodo) {
            return $this->getEmptyDashboardData();
        }

        $periodoId = $periodo['id'];
        $fase = $this->model->getFase($periodo);
        $params = $this->model->getParamPeriodo($periodoId);

        return [
            'periodo' => $periodo,
            'fase' => $fase,
            'params' => $params,
            'menuTotal' => $this->model->countCursosMenu(),
            'stats' => $this->model->getSolicitudesStats($periodoId),
            'detalleCursos' => $this->model->getDetalleSolicitudes($periodoId),
            'asignaciones' => $this->model->getAsignaciones($periodoId),
            'aperturados' => $this->model->getCursosAperturados($periodoId),
            'resumenFinal' => $this->getResumenFinalData($fase, $params, $periodoId)
        ];
    }

    /**
     * Obtiene datos del resumen final si está disponible
     * 
     * @param string|null $fase Fase actual del periodo
     * @param array $params Parámetros del periodo
     * @param int $periodoId ID del periodo
     * @return array Datos del resumen final
     */
    private function getResumenFinalData(?string $fase, array $params, int $periodoId): array
    {
        $resumenData = [
            'mostrar' => false,
            'datos' => []
        ];

        // Solo mostrar resumen final si el período está finalizado
        if ($fase !== 'finalizado') {
            return $resumenData;
        }

        try {
            $hoy = new DateTime();
            
            // Si no hay fecha de fin de asignación, intentar obtenerla de la base de datos
            if (empty($params['fin_asignacion_docentes'])) {
                error_log("Fecha de fin de asignación no disponible para período {$periodoId}");
                return $resumenData;
            }

            $finAsignacion = new DateTime($params['fin_asignacion_docentes']);
            $limiteVisualizacion = (clone $finAsignacion)->modify('+7 days');

            // Verificar si estamos dentro del período de visualización (7 días después del fin)
            if ($hoy <= $limiteVisualizacion) {
                $resumenData['mostrar'] = true;
                $resumenData['datos'] = $this->model->getResumenFinalAsignaciones($periodoId);
                
                // Log para debugging
                error_log("Mostrando resumen final. Fin asignación: {$finAsignacion->format('Y-m-d H:i:s')}, " .
                         "Límite: {$limiteVisualizacion->format('Y-m-d H:i:s')}, " .
                         "Hoy: {$hoy->format('Y-m-d H:i:s')}");
            } else {
                // Log cuando ya no se debe mostrar
                error_log("Resumen final ya no disponible. Límite expirado: {$limiteVisualizacion->format('Y-m-d H:i:s')}");
            }
        } catch (Exception $e) {
            error_log("Error calculando resumen final para período {$periodoId}: " . $e->getMessage());
        }

        return $resumenData;
    }

    /**
     * Retorna estructura de datos vacía para el dashboard
     * 
     * @return array Estructura vacía del dashboard
     */
    private function getEmptyDashboardData(): array
    {
        return [
            'periodo' => null,
            'fase' => null,
            'params' => [],
            'menuTotal' => 0,
            'stats' => ['count' => 0, 'creditos' => 0],
            'detalleCursos' => [],
            'asignaciones' => [],
            'aperturados' => [],
            'resumenFinal' => ['mostrar' => false, 'datos' => []],
            'error' => 'No hay período activo en este momento.'
        ];
    }

    /**
     * Renderiza la vista del dashboard con los datos proporcionados
     * 
     * @param array $data Datos para renderizar
     * @return void
     */
    private function renderDashboard(array $data): void
    {
        // Extraer variables para la vista
        extract($data);
        
        // Variables adicionales para compatibilidad con la vista existente
        $asigs = $data['asignaciones'] ?? [];
        $mostrarResumenFinal = $data['resumenFinal']['mostrar'] ?? false;
        $resumenFinal = $data['resumenFinal']['datos'] ?? [];
        $error = $data['error'] ?? null;

        require_once __DIR__ . '/../views/dashboard.php';
    }

    /**
     * Renderiza el dashboard con mensaje de error
     * 
     * @param string $errorMessage Mensaje de error a mostrar
     * @return void
     */
    private function renderErrorDashboard(string $errorMessage): void
    {
        $emptyData = $this->getEmptyDashboardData();
        $emptyData['error'] = $errorMessage;
        $this->renderDashboard($emptyData);
    }

    /**
     * Redirige al usuario a la página de login
     * 
     * @return void
     * @codeCoverageIgnore
     */
    private function redirectToLogin(): void
    {
        header('Location: ../login.php');
        exit;
    }
}