<?php
/*
 * Punto de entrada: estudiante/perfil.php
 *
 * Controla la vista de perfil del estudiante. Permite ver y actualizar
 * datos personales y redirige a la interfaz de cambio de contraseña.
 *
 * Acciones manejadas:
 * - index               → Muestra el formulario con los datos actuales
 * - actualizar          → Guarda los cambios de correo y teléfono
 * - cambiar_contraseña  → Redirige a cambiar_password.php
 *
 * Autor: ASI-GRUPO 5
 * Año: 2025
 */

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
