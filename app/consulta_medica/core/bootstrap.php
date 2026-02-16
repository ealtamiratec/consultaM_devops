<?php
/**
 * Bootstrap - Inicialización de la aplicación
 * Sistema de Atención Médica - Consulta Externa
 */

// Definir ruta base
define('BASE_PATH', dirname(__DIR__));

// Configurar reporte de errores según entorno
$appConfig = require BASE_PATH . '/config/app.php';

if ($appConfig['environment'] === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Configurar zona horaria
date_default_timezone_set($appConfig['timezone']);

// Autoloader simple
spl_autoload_register(function ($class) {
    // Mapeo de namespaces a directorios
    $namespaceMap = [
        'Core\\' => BASE_PATH . '/core/',
        'App\\Controllers\\' => BASE_PATH . '/app/controllers/',
        'App\\Models\\' => BASE_PATH . '/app/models/'
    ];

    foreach ($namespaceMap as $namespace => $directory) {
        if (strpos($class, $namespace) === 0) {
            $relativeClass = substr($class, strlen($namespace));
            $file = $directory . str_replace('\\', '/', $relativeClass) . '.php';
            
            if (file_exists($file)) {
                require_once $file;
                return true;
            }
        }
    }

    return false;
});

// Iniciar sesión segura
\Core\Session::start();

// Establecer cabeceras de seguridad HTTP
\Core\Security::setSecurityHeaders();

// Crear directorio de logs si no existe
$logPath = BASE_PATH . '/logs';
if (!is_dir($logPath)) {
    mkdir($logPath, 0755, true);
}

// Función helper para logging
function app_log(string $level, string $message, array $context = []): void
{
    $appConfig = require BASE_PATH . '/config/app.php';
    
    if (!$appConfig['logging']['enabled']) {
        return;
    }

    $logFile = $appConfig['logging']['path'] . '/app_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? json_encode($context) : '';
    $logEntry = "[{$timestamp}] [{$level}] {$message} {$contextStr}" . PHP_EOL;
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// Función helper para obtener URL base
function base_url(string $path = ''): string
{
    $appConfig = require BASE_PATH . '/config/app.php';
    return rtrim($appConfig['base_url'], '/') . '/' . ltrim($path, '/');
}

// Función helper para escapar HTML
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Función helper para token CSRF
function csrf_token(): string
{
    return \Core\Session::getCsrfToken();
}

// Función helper para campo CSRF en formularios
function csrf_field(): string
{
    $token = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

// Función helper para formatear fecha
function format_date(?string $date, string $format = 'd/m/Y'): string
{
    if (empty($date)) {
        return '';
    }
    $dateObj = new DateTime($date);
    return $dateObj->format($format);
}

// Función helper para formatear fecha y hora
function format_datetime(?string $datetime, string $format = 'd/m/Y H:i'): string
{
    if (empty($datetime)) {
        return '';
    }
    $dateObj = new DateTime($datetime);
    return $dateObj->format($format);
}

// Función helper para verificar autenticación
function is_authenticated(): bool
{
    return \Core\Session::isAuthenticated();
}

// Función helper para obtener usuario actual
function current_user(): ?array
{
    return \Core\Session::getUser();
}

// Función helper para verificar rol
function has_role(string $role): bool
{
    $user = current_user();
    return $user && $user['rol'] === $role;
}

// Función helper para mensajes flash
function flash(): ?array
{
    $flash = \Core\Session::get('flash');
    \Core\Session::remove('flash');
    return $flash;
}

// Función helper para establecer mensaje flash
function set_flash(string $type, string $message): void
{
    \Core\Session::set('flash', [
        'type' => $type,
        'message' => $message
    ]);
}
