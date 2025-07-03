<?php
// estudiante/solicitudes.php
// ─────────────────────────────────────────────────────────
// Front-controller simple para el módulo “Solicitudes”
// ─────────────────────────────────────────────────────────
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/../config/conexion.php';

require_once __DIR__ . '/controllers/SolicitudesController.php';

$pdo         = conectar();
$controller  = new SolicitudesController($pdo);

/* Acción solicitada (GET o POST) */
$action = $_GET['action'] ?? ($_POST['action'] ?? 'dashboard');

/* Enrutado minimalista */
switch ($action) {
    case 'ver':                  // PDF inline
        $controller->ver();
        break;

    case 'eliminar':             // POST via fetch → JSON
        $controller->eliminar();
        break;

    case 'descargarZip':         // ZIP de solicitudes
        $controller->descargarZip();
        break;

    case 'descargarResolucion':  // PDF de resolución
        $controller->descargarResolucion();
        break;

    default:                     // Lista e historial
        $controller->dashboard();
        break;
}
