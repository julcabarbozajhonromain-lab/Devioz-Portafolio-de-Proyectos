<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Devioz OS — Gestor Seguro de Sesiones, Mensajes Flash y Tokens CSRF
 */
final class Session
{
    private const FLASH_KEY = '_flash_messages';
    private const CSRF_KEY = '_csrf_token';

    /**
     * Inicia la sesión PHP con directivas de seguridad robustas.
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.use_strict_mode', '1');
            ini_set('session.cookie_samesite', 'Lax');

            // Habilitar secure flag si la conexión es HTTPS
            if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
                ini_set('session.cookie_secure', '1');
            }

            session_start();

            // Rotar mensajes flash leídos en el request anterior
            self::ageFlashMessages();
        }
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function forget(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * Almacena un mensaje flash para el siguiente request HTTP.
     * Tipos comunes: 'success', 'error', 'warning', 'info'
     */
    public static function flash(string $type, string $message): void
    {
        self::start();
        $_SESSION[self::FLASH_KEY]['new'][$type][] = $message;
    }

    /**
     * Obtiene y consume los mensajes flash del request actual.
     */
    public static function getFlash(?string $type = null): mixed
    {
        self::start();
        $flash = $_SESSION[self::FLASH_KEY]['current'] ?? [];

        if ($type === null) {
            return $flash;
        }

        return $flash[$type] ?? null;
    }

    /**
     * Retorna todos los mensajes flash de tipo éxito, error, etc. como array plano.
     */
    public static function allFlash(): array
    {
        self::start();
        return $_SESSION[self::FLASH_KEY]['current'] ?? [];
    }

    /**
     * Comprueba si existen mensajes flash disponibles en el ciclo actual.
     */
    public static function hasFlash(?string $type = null): bool
    {
        self::start();
        $flash = $_SESSION[self::FLASH_KEY]['current'] ?? [];
        if ($type === null) {
            return !empty($flash);
        }
        return !empty($flash[$type]);
    }

    /**
     * Gestiona el ciclo de vida de los mensajes flash (old vs new).
     */
    private static function ageFlashMessages(): void
    {
        // Los nuevos pasan a actuales
        $_SESSION[self::FLASH_KEY]['current'] = $_SESSION[self::FLASH_KEY]['new'] ?? [];
        // Se vacían los nuevos para acumular los que se creen durante este ciclo
        $_SESSION[self::FLASH_KEY]['new'] = [];
    }

    /**
     * Genera o retorna el token CSRF criptográficamente seguro de la sesión.
     */
    public static function csrf(): string
    {
        self::start();
        if (empty($_SESSION[self::CSRF_KEY])) {
            $_SESSION[self::CSRF_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::CSRF_KEY];
    }

    /**
     * Valida el token CSRF provisto contra el token en sesión mediante hash_equals (timing attack safe).
     */
    public static function verifyCsrf(?string $token): bool
    {
        self::start();
        $sessionToken = self::csrf();
        
        if (empty($token) || !is_string($token)) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    /**
     * Regenera el ID de sesión para prevenir ataques de Session Fixation.
     */
    public static function regenerate(bool $deleteOldSession = true): bool
    {
        self::start();
        return session_regenerate_id($deleteOldSession);
    }

    /**
     * Destruye la sesión completa y borra la cookie del cliente.
     */
    public static function destroy(): void
    {
        self::start();
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
    }
}
