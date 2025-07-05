<?php
/*
 * admin/reportes.php
 * Punto de entrada para la gestión y generación de reportes y resoluciones administrativas.
 * Variables involucradas:
 *   - $pdo           → conexión PDO a la base de datos
 *   - ReportesController → controlador principal de reportes
 * Flujo de acciones:
 *   - 'index'      → Muestra el dashboard principal de reportes por periodo.
 *   - 'ver'        → Visualiza un reporte/resolución en el visor PDF.
 *   - 'descargar'  → Descarga un reporte/resolución en formato PDF.
 * Autor: ASI-GRUPO 5
 * Año: 2025
 */

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
