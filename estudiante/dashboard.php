<?php
// estudiante/dashboard.php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/controllers/DashboardController.php';

$ctrl = new DashboardController($pdo);
$ctrl->index();
