<?php
/*
 * config/conexion.php
 * Devuelve una única instancia PDO para toda la aplicación.
 * Características:
 *   - Función conectar(): conexión persistente y reutilizable.
 *   - Configura opciones seguras para el entorno MySQL (charset utf8mb4, manejo de errores).
 *   - Define $pdo global para compatibilidad retroactiva.
 * Uso: Incluir en cualquier archivo que requiera acceso a la base de datos.
 * Autor: ASI-GRUPO 5
 * Año: 2025
 */
require_once __DIR__ . '/config.php'; // solo para BASE_URL u otras constantes

function conectar(): PDO
{
    static $pdo = null;               // reutiliza la misma conexión

    if ($pdo === null) {
        // ► Credenciales de tu servidor MySQL
        $host = 'localhost';
        $db   = 'gestion-nivelacion-vacacional';
        $user = 'root';
        $pass = '';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $pdo = new PDO($dsn, $user, $pass, $options);
    }

    return $pdo;
}

// ► Para compatibilidad con scripts viejos que esperan $pdo global
$pdo = conectar();
