<?php
/*
 * Punto de entrada: estudiante/progreso.php
 *
 * Carga el panel de progreso académico del estudiante, donde se muestra
 * el avance en créditos completados respecto a su plan de estudios.
 *
 * Usa ProgresoController para obtener y renderizar la vista.
 *
 * Autor: ASI-GRUPO 5
 * Año: 2025
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/controllers/ProgresoController.php';

$pdo = conectar();
$controller = new ProgresoController($pdo);
$controller->dashboard();
