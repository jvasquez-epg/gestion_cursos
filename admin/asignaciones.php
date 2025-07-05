<?php
/*
 * admin/asignaciones.php
 * Punto de entrada para la gestión de asignación de docentes a cursos.
 * Controla las siguientes acciones:
 *   - index:   Muestra la vista principal con cursos para asignación.
 *   - detalle: Retorna el detalle AJAX para asignar/editar docente (modal).
 *   - store:   Procesa alta o edición de una asignación.
 *   - delete:  Elimina la asignación docente-curso.
 * Incluye seguridad de sesión y redirecciones apropiadas.
 * Autor: ASI-GRUPO 5
 * Año: 2025
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/controllers/AsignacionController.php';

if (session_status() === PHP_SESSION_NONE)
    session_start();


$controller = new AsignacionController($pdo);
$action     = $_GET['action'] ?? 'index';

switch ($action) {
    case 'detalle':
        // Partial AJAX: obtener detalle para el modal
        $cursoId = isset($_GET['curso']) ? (int) $_GET['curso'] : 0;
        $controller->detalle($cursoId);
        break;

    case 'store':
        // Guardar nueva asignación o actualización
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->store($_POST);
        } else {
            header('Location: ' . BASE_URL . 'admin/asignaciones.php');
        }
        break;

    case 'delete':
        // Eliminar asignación
        $asigId = isset($_GET['asignacion']) ? (int) $_GET['asignacion'] : 0;
        $controller->delete($asigId);
        break;

    case 'index':
    default:
        // Mostrar la lista de cursos a asignar
        $controller->index();
        break;
}
