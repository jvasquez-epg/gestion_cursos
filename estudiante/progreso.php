<?php
// estudiante/progreso.php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/controllers/ProgresoController.php';

$pdo = conectar();
$controller = new ProgresoController($pdo);
$controller->dashboard();
