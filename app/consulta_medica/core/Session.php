<?php
/**
 * Clase Session - Manejo seguro de sesiones
 * Sistema de Atención Médica - Consulta Externa
 */

namespace Core;

class Session
{
    private static bool $started = false;
    private static array $config;

    /**
     * Iniciar sesión de forma segura
     */
    public static function start(): void
    {
        if (self::$started) {
            return;
        }

        self::$config = require BASE_PATH . '/config/app.php';
        $sessionConfig = self::$config['session'];

        // Configurar parámetros de sesión seguros
        ini_set('session.use_strict_mode', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.use_trans_sid', 0);

        session_name($sessionConfig['name']);
        
        session_set_cookie_params([
            'lifetime' => $sessionConfig['lifetime'],
            'path' => $sessionConfig['path'],
            'domain' => $sessionConfig['domain'],
            'secure' => $sessionConfig['secure'],
            'httponly' => $sessionConfig['httponly'],
            'samesite' => $sessionConfig['samesite']
        ]);

        session_start();
        self::$started = true;

        // Regenerar ID de sesión periódicamente
        self::regenerateIfNeeded();
    }

    /**
     * Regenerar ID de sesión si es necesario
     */
    private static function regenerateIfNeeded(): void
    {
        $regenerateInterval = 300; // 5 minutos
        
        if (!isset($_SESSION['_last_regeneration'])) {
            self::regenerate();
            return;
        }

        if (time() - $_SESSION['_last_regeneration'] > $regenerateInterval) {
            self::regenerate();
        }
    }

    /**
     * Regenerar ID de sesión
     */
    public static function regenerate(): void
    {
        session_regenerate_id(true);
        $_SESSION['_last_regeneration'] = time();
    }

    /**
     * Establecer valor en sesión
     */
    public static function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Obtener valor de sesión
     */
    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Verificar si existe clave en sesión
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Eliminar valor de sesión
     */
    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Destruir sesión completamente
     */
    public static function destroy(): void
    {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
        self::$started = false;
    }

    /**
     * Verificar si el usuario está autenticado
     */
    public static function isAuthenticated(): bool
    {
        return self::has('user_id') && self::has('user_authenticated');
    }

    /**
     * Obtener ID del usuario autenticado
     */
    public static function getUserId(): ?int
    {
        return self::isAuthenticated() ? (int) self::get('user_id') : null;
    }

    /**
     * Obtener datos del usuario autenticado
     */
    public static function getUser(): ?array
    {
        return self::isAuthenticated() ? self::get('user_data') : null;
    }

    /**
     * Establecer usuario autenticado
     */
    public static function setUser(array $user): void
    {
        self::regenerate();
        self::set('user_id', $user['id']);
        self::set('user_authenticated', true);
        self::set('user_data', $user);
        self::set('login_time', time());
    }

    /**
     * Cerrar sesión de usuario
     */
    public static function logout(): void
    {
        self::remove('user_id');
        self::remove('user_authenticated');
        self::remove('user_data');
        self::remove('login_time');
        self::regenerate();
    }

    /**
     * Generar token CSRF
     */
    public static function generateCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        self::set('csrf_token', $token);
        self::set('csrf_token_time', time());
        return $token;
    }

    /**
     * Validar token CSRF
     */
    public static function validateCsrfToken(string $token): bool
    {
        $storedToken = self::get('csrf_token');
        $tokenTime = self::get('csrf_token_time', 0);
        
        // Verificar que el token existe y no ha expirado (1 hora)
        if (!$storedToken || (time() - $tokenTime) > 3600) {
            return false;
        }

        return hash_equals($storedToken, $token);
    }

    /**
     * Obtener token CSRF actual o generar uno nuevo
     */
    public static function getCsrfToken(): string
    {
        $token = self::get('csrf_token');
        $tokenTime = self::get('csrf_token_time', 0);

        if (!$token || (time() - $tokenTime) > 3600) {
            return self::generateCsrfToken();
        }

        return $token;
    }
}
