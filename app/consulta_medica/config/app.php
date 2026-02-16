<?php

/**
 * Configuración General de la Aplicación
 * Sistema de Atención Médica - Consulta Externa
 */

// Prevenir acceso directo
if (!defined('BASE_PATH')) {
    die('Acceso no permitido');
}

return [
    // Información de la aplicación
    'name' => 'Sistema de Consulta Médica Externa',
    'version' => '1.0.0',
    'environment' => getenv('APP_ENV') ?: 'development',

    // URL base (ajustar según configuración local)
    'base_url' => getenv('APP_URL') ?: 'http://localhost:8080/',

    // Configuración de sesión
    'session' => [
        'name' => 'consulta_medica_session',
        'lifetime' => 7200, // 2 horas
        'path' => '/',
        'domain' => '',
        'secure' => false, // Cambiar a true en producción con HTTPS
        'httponly' => true,
        'samesite' => 'Strict'
    ],

    // Configuración de seguridad
    'security' => [
        'csrf_token_name' => 'csrf_token',
        'csrf_token_lifetime' => 3600, // 1 hora
        'password_min_length' => 8,
        'max_login_attempts' => 5,
        'lockout_time' => 900, // 15 minutos
        'hash_algo' => PASSWORD_BCRYPT,
        'hash_cost' => 10
    ],

    // Configuración de paginación
    'pagination' => [
        'per_page' => 10,
        'max_per_page' => 100
    ],

    // Configuración de logs
    'logging' => [
        'enabled' => true,
        'path' => BASE_PATH . '/logs',
        'level' => 'debug' // debug, info, warning, error
    ],

    // Zona horaria
    'timezone' => 'America/Lima',

    // Formato de fechas
    'date_format' => 'd/m/Y',
    'datetime_format' => 'd/m/Y H:i:s',
    'time_format' => 'H:i'
];
