<?php
/**
 * Clase Controller - Controlador base abstracto
 * Sistema de Atención Médica - Consulta Externa
 */

namespace Core;

abstract class Controller
{
    protected array $data = [];

    /**
     * Renderizar vista
     */
    protected function view(string $view, array $data = []): void
    {
        $this->data = array_merge($this->data, $data);
        
        // Extraer variables para la vista
        extract($this->data);
        
        // Cargar configuración de la app
        $appConfig = require BASE_PATH . '/config/app.php';
        $appName = $appConfig['name'];
        $baseUrl = $appConfig['base_url'];
        
        // Ruta del archivo de vista
        $viewFile = BASE_PATH . '/app/views/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewFile)) {
            throw new \Exception("Vista {$view} no encontrada");
        }

        require $viewFile;
    }

    /**
     * Renderizar vista con layout
     */
    protected function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $this->data = array_merge($this->data, $data);
        
        // Capturar contenido de la vista
        ob_start();
        extract($this->data);
        
        $viewFile = BASE_PATH . '/app/views/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewFile)) {
            throw new \Exception("Vista {$view} no encontrada");
        }
        require $viewFile;
        $content = ob_get_clean();
        
        // Cargar configuración
        $appConfig = require BASE_PATH . '/config/app.php';
        $appName = $appConfig['name'];
        $baseUrl = $appConfig['base_url'];
        
        // Renderizar layout con contenido
        $layoutFile = BASE_PATH . '/app/views/layouts/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            throw new \Exception("Layout {$layout} no encontrado");
        }
        
        extract($this->data);
        require $layoutFile;
    }

    /**
     * Responder con JSON
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Redireccionar a otra URL
     */
    protected function redirect(string $url): void
    {
        $appConfig = require BASE_PATH . '/config/app.php';
        $baseUrl = rtrim($appConfig['base_url'], '/');
        
        if (!preg_match('/^https?:\/\//', $url)) {
            $url = $baseUrl . '/' . ltrim($url, '/');
        }
        
        header("Location: {$url}");
        exit;
    }

    /**
     * Obtener datos POST sanitizados
     */
    protected function getPost(string $key = null, $default = null)
    {
        if ($key === null) {
            return array_map([$this, 'sanitize'], $_POST);
        }
        
        return isset($_POST[$key]) ? $this->sanitize($_POST[$key]) : $default;
    }

    /**
     * Obtener datos GET sanitizados
     */
    protected function getQuery(string $key = null, $default = null)
    {
        if ($key === null) {
            return array_map([$this, 'sanitize'], $_GET);
        }
        
        return isset($_GET[$key]) ? $this->sanitize($_GET[$key]) : $default;
    }

    /**
     * Sanitizar valor
     */
    protected function sanitize($value)
    {
        if (is_array($value)) {
            return array_map([$this, 'sanitize'], $value);
        }
        
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Verificar si es solicitud AJAX
     */
    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Verificar método de solicitud
     */
    protected function isMethod(string $method): bool
    {
        return strtoupper($_SERVER['REQUEST_METHOD']) === strtoupper($method);
    }

    /**
     * Establecer mensaje flash en sesión
     */
    protected function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }

    /**
     * Obtener y limpiar mensaje flash
     */
    protected function getFlash(): ?array
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }
}
