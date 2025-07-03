<?php
// admin/solicitudes.php

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
