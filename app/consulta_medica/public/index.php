<?php

/**
 * Punto de Entrada Principal
 * Sistema de Atención Médica - Consulta Externa
 */

// Cargar bootstrap
require_once dirname(__DIR__) . '/core/bootstrap.php';

// Cargar rutas
$router = require BASE_PATH . '/config/routes.php';

// Obtener URI limpia de la ruta base
$baseDir = getenv('APP_BASE_PATH') ?: '';
$baseDir = rtrim($baseDir, '/');
$uri = $_SERVER['REQUEST_URI'] ?? '/';

// Remover la base del path si existe
if ($baseDir !== '' && strpos($uri, $baseDir) === 0) {
    $uri = substr($uri, strlen($baseDir));
}

// Remover query string
$uri = explode('?', $uri)[0];

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Despachar la solicitud
try {
    $router->dispatch($uri, $method);
} catch (\Exception $e) {
    // Log del error
    app_log('error', $e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);

    // Mostrar página de error
    http_response_code(500);

    $appConfig = require BASE_PATH . '/config/app.php';
    if ($appConfig['environment'] === 'development') {
        echo "<h1>Error de Aplicación</h1>";
        echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>Archivo:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
        echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        require BASE_PATH . '/app/views/errors/500.php';
    }
}
