<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/conexion.php'; // ← Asegura que se incluya la conexión PDO
require_once __DIR__ . '/controllers/DocenteController.php';

if (session_status() === PHP_SESSION_NONE)
    session_start();

// Solo accesible por administradores
session_start();

$controller = new DocenteController($pdo);

// Enrutamiento simple
$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

switch ($action) {
    case 'create':
        $controller->create();
        break;
    case 'edit':
        if ($id) {
            $controller->edit($id);
        } else {
            header('Location: ' . BASE_URL . 'admin/docentes.php');
        }
        break;
    case 'delete':
        if ($id) {
            $controller->delete($id);
        }
        break;
    case 'store':
        $controller->store($_POST);
        break;
    default:
        $controller->index();
        break;
}
