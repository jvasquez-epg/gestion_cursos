<?php
/*
 * Punto de entrada: estudiante/solicitudes.php
 *
 * Controlador frontal del módulo de solicitudes académicas del estudiante.
 * Permite visualizar solicitudes realizadas, eliminarlas si corresponde,
 * descargar resoluciones oficiales o paquetes ZIP por periodo.
 *
 * Usa SolicitudesController para manejar las acciones disponibles.
 *
 * Acciones:
 *   - ver                 → Mostrar solicitud individual en PDF
 *   - eliminar            → Eliminar una solicitud (AJAX, JSON)
 *   - descargarZip        → Descargar ZIP de solicitudes por periodo
 *   - descargarResolucion → Descargar resolución del periodo
 *   - dashboard           → Vista general e historial de solicitudes
 *
 * Autor: ASI-GRUPO 5
 * Año: 2025
 */

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
