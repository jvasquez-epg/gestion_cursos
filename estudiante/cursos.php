<?php
/*
 * estudiante/cursos.php
 * 
 * Punto de entrada para gestionar cursos desde el rol estudiante:
 * - Muestra cursos disponibles según paridad y requisitos
 * - Permite enviar solicitudes individuales o múltiples
 * - Permite cancelar solicitudes realizadas
 * - Determina la acción mediante el parámetro `action`
 * 
 * Autor: ASI-GRUPO 5
 * Año: 2025
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/controllers/CursoController.php';

$pdo        = conectar();
$controller = new CursoController($pdo);
$action     = $_GET['action'] ?? ($_POST['action'] ?? 'dashboard');

switch ($action) {
    case 'solicitar':
        $controller->solicitar();
        break;

    case 'cancelar':
        $controller->cancelar();
        break;

    case 'solicitar_multiple':          // ← NUEVO
        $controller->solicitar_multiple();
        break;

    default:
        $controller->dashboard();
        break;
}
