<?php

/**
 * Configuración de la Base de Datos
 * Sistema de Atención Médica - Consulta Externa
 */

// Prevenir acceso directo
if (!defined('BASE_PATH')) {
    die('Acceso no permitido');
}

return [
    // Configuración de conexión MySQL
    'host' => getenv('DB_HOST') ?: 'localhost',
    'port' => getenv('DB_PORT') ?: '3306',
    'database' => getenv('DB_DATABASE') ?: (getenv('DB_NAME') ?: 'consulta_medica'),
    'username' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASSWORD') ?: (getenv('DB_PASS') ?: 'root'),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',

    // Opciones PDO
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ]
];
