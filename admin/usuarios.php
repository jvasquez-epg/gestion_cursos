<?php
// admin/usuarios.php

session_start();

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/controllers/UsuarioController.php';

// Sólo administradores o administrativos
if (empty($_SESSION['usuario_rol']) || !in_array($_SESSION['usuario_rol'], ['administrador', 'administrativo'], true)) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

// Conexión a la base de datos
$controller = new UsuarioController($pdo);

// Parámetros filtrados
$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING) ?: 'dashboard';
$rol    = filter_input(INPUT_GET, 'rol', FILTER_SANITIZE_STRING);
$id     = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Roles permitidos para esta sección
$validRoles = ['administrador', 'administrativo', 'estudiante'];

// Si llega rol pero no es válido, redirigimos
if ($rol !== null && !in_array($rol, $validRoles, true)) {
    header('Location: ' . BASE_URL . 'admin/usuarios.php');
    exit;
}

switch ($action) {
    case 'dashboard':
        $controller->dashboard();
        break;

    case 'list':
        if (!$rol) {
            header('Location: ' . BASE_URL . 'admin/usuarios.php');
            exit;
        }
        $controller->index($rol);
        break;

    case 'create':
        if (!$rol || $rol === 'estudiante') {
            $_SESSION['error'] = 'No puedes crear estudiantes desde aquí.';
            header('Location: ' . BASE_URL . 'admin/usuarios.php?action=list&rol=estudiante');
            exit;
        }
        $controller->create($rol);
        break;

    case 'edit':
        if (!$rol || !$id || $rol === 'estudiante') {
            $_SESSION['error'] = 'No puedes editar estudiantes desde aquí.';
            header('Location: ' . BASE_URL . 'admin/usuarios.php?action=list&rol=estudiante');
            exit;
        }
        $controller->edit($id, $rol);
        break;

    case 'store':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->store($_POST);
        } else {
            header('Location: ' . BASE_URL . 'admin/usuarios.php');
        }
        break;

    case 'update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
            $controller->update($id, $_POST);
        } else {
            header('Location: ' . BASE_URL . 'admin/usuarios.php');
        }
        break;

    case 'delete':
        // Sólo si viene un ID y no es estudiante
        if ($id && $rol !== 'estudiante') {
            $controller->delete($id, $rol);
        } else {
            $_SESSION['error'] = 'No puedes eliminar estudiantes desde aquí.';
            header('Location: ' . BASE_URL . 'admin/usuarios.php?action=list&rol=estudiante');
        }
        break;

    default:
        // Acción desconocida: mostramos dashboard
        $controller->dashboard();
        break;
}
