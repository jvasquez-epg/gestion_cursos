<?php
/*
 * Punto de entrada: estudiante/malla.php
 *
 * Muestra la malla curricular del estudiante autenticado.
 * Llama al controlador correspondiente para obtener los datos y renderizar la vista.
 *
 * Requiere:
 * - config.php       → definición de constantes globales (BASE_URL, etc.)
 * - conexion.php     → función conectar() que retorna instancia PDO
 * - MallaController  → controlador que gestiona la lógica de la malla
 *
 * Autor: ASI-GRUPO 5
 * Año: 2025
 */

require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../config/conexion.php';
require_once __DIR__.'/controllers/MallaController.php';
(new MallaController(conectar()))->dashboard();
