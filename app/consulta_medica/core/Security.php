<?php
/**
 * Clase Security - Funciones de seguridad
 * Sistema de Atención Médica - Consulta Externa
 */

namespace Core;

class Security
{
    /**
     * Sanitizar entrada de texto
     */
    public static function sanitize($input)
    {
        if (is_array($input)) {
            return array_map([self::class, 'sanitize'], $input);
        }
        
        if (is_string($input)) {
            // Eliminar caracteres nulos
            $input = str_replace(chr(0), '', $input);
            // Trim espacios
            $input = trim($input);
            // Eliminar tags HTML peligrosos
            $input = strip_tags($input);
        }
        
        return $input;
    }

    /**
     * Escapar para HTML
     */
    public static function escape($value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Hash de contraseña seguro
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Verificar contraseña
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Generar token aleatorio seguro
     */
    public static function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Validar y sanitizar entero
     */
    public static function sanitizeInt($value): ?int
    {
        $filtered = filter_var($value, FILTER_VALIDATE_INT);
        return $filtered !== false ? $filtered : null;
    }

    /**
     * Validar y sanitizar email
     */
    public static function sanitizeEmail($value): ?string
    {
        $filtered = filter_var($value, FILTER_VALIDATE_EMAIL);
        return $filtered !== false ? $filtered : null;
    }

    /**
     * Prevenir ataques de timing
     */
    public static function secureCompare(string $known, string $user): bool
    {
        return hash_equals($known, $user);
    }

    /**
     * Verificar si la solicitud es AJAX
     */
    public static function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Obtener IP del cliente de forma segura
     */
    public static function getClientIp(): string
    {
        $headers = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }

    /**
     * Rate limiting simple basado en sesión
     */
    public static function checkRateLimit(string $action, int $maxAttempts = 5, int $timeWindow = 300): bool
    {
        $key = 'rate_limit_' . $action;
        $attempts = Session::get($key, []);
        $now = time();

        // Limpiar intentos antiguos
        $attempts = array_filter($attempts, fn($time) => ($now - $time) < $timeWindow);

        if (count($attempts) >= $maxAttempts) {
            return false;
        }

        $attempts[] = $now;
        Session::set($key, $attempts);
        return true;
    }

    /**
     * Resetear rate limit
     */
    public static function resetRateLimit(string $action): void
    {
        Session::remove('rate_limit_' . $action);
    }

    /**
     * Verificar método HTTP
     */
    public static function isMethod(string $method): bool
    {
        return strtoupper($_SERVER['REQUEST_METHOD']) === strtoupper($method);
    }

    /**
     * Redirigir de forma segura
     */
    public static function redirect(string $url, int $statusCode = 302): void
    {
        // Validar que la URL sea relativa o del mismo dominio
        if (!self::isSafeRedirect($url)) {
            $url = '/';
        }
        
        header("Location: $url", true, $statusCode);
        exit;
    }

    /**
     * Verificar si la URL de redirección es segura
     */
    public static function isSafeRedirect(string $url): bool
    {
        // URLs relativas son seguras
        if (strpos($url, '/') === 0 && strpos($url, '//') !== 0) {
            return true;
        }

        // Verificar que sea del mismo dominio
        $parsedUrl = parse_url($url);
        if (!isset($parsedUrl['host'])) {
            return true;
        }

        return $parsedUrl['host'] === ($_SERVER['HTTP_HOST'] ?? '');
    }

    /**
     * Establecer cabeceras de seguridad HTTP
     */
    public static function setSecurityHeaders(): void
    {
        // Prevenir clickjacking
        header('X-Frame-Options: SAMEORIGIN');
        
        // Prevenir MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Habilitar filtro XSS del navegador
        header('X-XSS-Protection: 1; mode=block');
        
        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy básica
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; font-src 'self' https://cdnjs.cloudflare.com; img-src 'self' data:;");
    }

    /**
     * Registrar intento de acceso sospechoso
     */
    public static function logSecurityEvent(string $event, array $details = []): void
    {
        $logFile = BASE_PATH . '/logs/security.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip' => self::getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
            'details' => $details
        ];

        file_put_contents($logFile, json_encode($entry) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
