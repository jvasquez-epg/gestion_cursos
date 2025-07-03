<?php
// admin/periodos.php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/controllers/PeriodoController.php';

// Arranca sesión (para mensajes flash y control de acceso)
session_start();

$controller = new PeriodoController($pdo);
$action = $_GET['action'] ?? 'index';

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
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
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
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $controller->delete($id);
        break;

    default:
        $controller->index();
        break;
}
