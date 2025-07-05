<?php
/*
 * estudiante/dashboard.php
 * 
 * Entrada principal al panel del estudiante:
 * - Muestra información del periodo activo
 * - Determina la fase del proceso (envío, asignación o cierre)
 * - Presenta resumen de solicitudes, asignaciones y cursos disponibles
 * - Usa DashboardController para el flujo
 * 
 * Autor: ASI-GRUPO 5
 * Año: 2025
 */
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/controllers/DashboardController.php';

$ctrl = new DashboardController($pdo);
$ctrl->index();
