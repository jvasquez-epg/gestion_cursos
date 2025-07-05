<?php
/*
 * Controlador para el módulo de progreso académico del estudiante.
 * Variables esperadas en sesión:
 *   - usuario_rol: Debe ser 'estudiante' para acceso permitido.
 * Funcionalidad:
 *   - Valida sesión y rol del usuario.
 *   - Obtiene el progreso académico del estudiante desde el modelo.
 *   - Carga la vista correspondiente para mostrar el dashboard de progreso.
 * Uso:
 *   - Incluye el modelo ProgresoModel para obtener los datos necesarios.
 * Autor: ASI-GRUPO 5
 * Año: 2025
 */

require_once __DIR__ . '/../models/ProgresoModel.php';

class ProgresoController {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function dashboard() {
        session_start();

        if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'estudiante') {
            header('Location: ' . BASE_URL . 'login.php');
            exit;
        }

        $estudianteId = $_SESSION['usuario_id'];

        $model = new ProgresoModel($this->pdo);
        $progreso = $model->obtenerPorEstudiante($estudianteId);

        include __DIR__ . '/../views/progreso_dashboard.php';
    }
}
