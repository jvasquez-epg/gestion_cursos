<?php
// admin/asignaciones.php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/controllers/AsignacionController.php';



$controller = new AsignacionController($pdo);
$action     = $_GET['action'] ?? 'index';

switch ($action) {
    case 'detalle':
        // Partial AJAX: obtener detalle para el modal
        $cursoId = isset($_GET['curso']) ? (int) $_GET['curso'] : 0;
        $controller->detalle($cursoId);
        break;

    case 'store':
        // Guardar nueva asignación o actualización
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->store($_POST);
        } else {
            header('Location: ' . BASE_URL . 'admin/asignaciones.php');
        }
        break;

    case 'delete':
        // Eliminar asignación
        $asigId = isset($_GET['asignacion']) ? (int) $_GET['asignacion'] : 0;
        $controller->delete($asigId);
        break;

    case 'index':
    default:
        // Mostrar la lista de cursos a asignar
        $controller->index();
        break;
}
