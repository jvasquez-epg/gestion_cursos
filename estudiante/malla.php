<?php
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../config/conexion.php';
require_once __DIR__.'/controllers/MallaController.php';
(new MallaController(conectar()))->dashboard();
