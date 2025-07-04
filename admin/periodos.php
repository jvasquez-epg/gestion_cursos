<?php
// admin/periodos.php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/controllers/PeriodoController.php';

session_start();

$controller = new PeriodoController($pdo);
// 1) Capturamos acción e ID al inicio:
$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING) ?: 'index';
$id     = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;

switch ($action) {
    case 'create':
        $controller->create();
        break;

    case 'store':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->store($_POST);
        } else {
            header('Location: ' . BASE_URL . 'admin/periodos.php');
            exit;
        }
        break;

    case 'edit':
        $controller->edit($id);
        break;

    case 'update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->update($_POST);
        } else {
            header('Location: ' . BASE_URL . 'admin/periodos.php');
            exit;
        }
        break;

    case 'delete':
        $controller->delete($id);
        break;

    // ——————————
    // NUEVO: generar resolución PDF
    case 'resolucion':
        if ($id) {
            $controller->resolucion($id);
        } else {
            header('Location: ' . BASE_URL . 'admin/periodos.php');
            exit;
        }
        break;
    case 'export':
        if ($id) {
            $controller->export();
        } else {
            header('Location: ' . BASE_URL . 'admin/periodos.php');
            exit;
        }
        break;


    default:
        // index o dashboard
        $controller->index();
        break;
}
