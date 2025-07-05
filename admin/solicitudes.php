<?php
/*
 * admin/solicitudes.php
 * Punto de entrada para la gestión y consulta de solicitudes académicas por curso.
 * Variables y contexto:
 *   - $pdo                → conexión PDO a la base de datos
 *   - SolicitudController → controlador de la lógica de solicitudes
 * Flujo principal:
 *   - 'index'    → Muestra el resumen de solicitudes por curso en el periodo activo.
 *   - 'detalle'  → Devuelve (por AJAX) el detalle de alumnos que solicitaron un curso.
 * Acceso restringido según sesión. Incluye control de acceso y mensajes flash.
 * Autor: ASI-GRUPO 5
 * Año: 2025
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/controllers/SolicitudController.php';

// Arranca sesión (para control de acceso y flash messages)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$controller = new SolicitudController($pdo);
$action     = $_GET['action'] ?? 'index';

switch ($action) {
    case 'detalle':
        // Llamada AJAX o partial para detalle de curso
        $cursoId = isset($_GET['curso']) ? (int) $_GET['curso'] : 0;
        $controller->detalle($cursoId);
        break;

    case 'index':
    default:
        // Resumen de solicitudes por curso
        $controller->index();
        break;
}
