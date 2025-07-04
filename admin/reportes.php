<?php
// admin/reportes.php

require_once __DIR__ . '/controllers/ReportesController.php';
require_once __DIR__ . '/../config/conexion.php';

$controller = new ReportesController($pdo);

// Acción por GET: index, ver, descargar
$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'ver':
        $controller->ver();
        break;
    case 'descargar':
        $controller->descargar();
        break;
    default:
        $controller->index();
        break;
}
