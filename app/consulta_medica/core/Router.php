<?php
/**
 * Clase Router - Enrutador de la aplicación
 * Sistema de Atención Médica - Consulta Externa
 */

namespace Core;

class Router
{
    private array $routes = [];
    private array $params = [];

    /**
     * Agregar ruta GET
     */
    public function get(string $route, array $handler): self
    {
        $this->addRoute('GET', $route, $handler);
        return $this;
    }

    /**
     * Agregar ruta POST
     */
    public function post(string $route, array $handler): self
    {
        $this->addRoute('POST', $route, $handler);
        return $this;
    }

    /**
     * Agregar ruta PUT
     */
    public function put(string $route, array $handler): self
    {
        $this->addRoute('PUT', $route, $handler);
        return $this;
    }

    /**
     * Agregar ruta DELETE
     */
    public function delete(string $route, array $handler): self
    {
        $this->addRoute('DELETE', $route, $handler);
        return $this;
    }

    /**
     * Agregar ruta al registro
     */
    private function addRoute(string $method, string $route, array $handler): void
    {
        // Convertir ruta a expresión regular
        $route = preg_replace('/\//', '\\/', $route);
        $route = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-z0-9-]+)', $route);
        $route = preg_replace('/\{([a-z]+):([^\}]+)\}/', '(?P<\1>\2)', $route);
        $route = '/^' . $route . '$/i';

        $this->routes[$method][$route] = $handler;
    }

    /**
     * Despachar la solicitud
     */
    public function dispatch(string $uri, string $method): void
    {
        // Limpiar URI
        $uri = $this->sanitizeUri($uri);
        $method = strtoupper($method);

        // Buscar ruta coincidente
        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $route => $handler) {
                if (preg_match($route, $uri, $matches)) {
                    // Extraer parámetros nombrados
                    $this->params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                    
                    // Ejecutar controlador
                    $this->executeHandler($handler);
                    return;
                }
            }
        }

        // Ruta no encontrada
        $this->handleNotFound();
    }

    /**
     * Sanitizar URI
     */
    private function sanitizeUri(string $uri): string
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = trim($uri, '/');
        $uri = filter_var($uri, FILTER_SANITIZE_URL);
        return $uri ?: '';
    }

    /**
     * Ejecutar handler del controlador
     */
    private function executeHandler(array $handler): void
    {
        [$controllerName, $action] = $handler;
        $controllerClass = "App\\Controllers\\{$controllerName}";

        if (!class_exists($controllerClass)) {
            throw new \Exception("Controlador {$controllerClass} no encontrado");
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $action)) {
            throw new \Exception("Método {$action} no encontrado en {$controllerClass}");
        }

        // Ejecutar acción con parámetros
        call_user_func_array([$controller, $action], $this->params);
    }

    /**
     * Manejar ruta no encontrada
     */
    private function handleNotFound(): void
    {
        http_response_code(404);
        require BASE_PATH . '/app/views/errors/404.php';
        exit;
    }

    /**
     * Obtener parámetros de la ruta
     */
    public function getParams(): array
    {
        return $this->params;
    }
}
