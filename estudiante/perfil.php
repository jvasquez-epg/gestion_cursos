<?php
// estudiante/perfil.php

session_start();
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/controllers/PerfilController.php';

$controller = new PerfilController($pdo);

// Routing básico según la acción
$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'index':
        $controller->index();
        break;

    case 'actualizar':
        $controller->actualizar();
        break;

    case 'cambiar_contraseña':
        header('Location: cambiar_password.php');
        break;

    default:
        http_response_code(404);
        echo "Página no encontrada.";
        break;
}
