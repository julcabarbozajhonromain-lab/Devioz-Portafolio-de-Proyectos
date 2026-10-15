<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

/**
 * Devioz OS — Sistema Centralizado de Autenticación y Autorización
 */
final class Auth
{
    private static ?self $instance = null;
    private const SESSION_USER_ID = '_auth_user_id';
    private ?User $cachedUser = null;

    private function __construct() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Intenta autenticar a un usuario por email y contraseña.
     */
    public function attempt(string $email, string $password, bool $remember = false): bool
    {
        $db = Database::getInstance();
        $userRow = $db->fetch(
            "SELECT * FROM `users` WHERE `email` = :email AND `active` = 1 AND `deleted_at` IS NULL LIMIT 1",
            ['email' => trim($email)]
        );

        if (!$userRow) {
            return false;
        }

        if (!password_verify($password, $userRow['password'])) {
            return false;
        }

        // Login exitoso: regenerar ID de sesión para prevenir fijación de sesión
        Session::regenerate(true);
        Session::set(self::SESSION_USER_ID, (int)$userRow['id']);

        // Actualizar último acceso e IP
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $db->update(
            'users',
            [
                'last_login_at' => date('Y-m-d H:i:s'),
                'last_login_ip' => $ip,
            ],
            '`id` = :id',
            ['id' => $userRow['id']]
        );

        // Registrar en log de auditoría
        $db->insert('audit_logs', [
            'user_id' => $userRow['id'],
            'action' => 'user.login',
            'model_type' => 'User',
            'model_id' => $userRow['id'],
            'changes' => json_encode(['ip' => $ip, 'role' => $userRow['role']]),
            'ip_address' => $ip,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        ]);

        $this->cachedUser = new User($userRow);
        return true;
    }

    /**
     * Cierra la sesión activa del usuario.
     */
    public function logout(): void
    {
        $user = $this->user();
        if ($user) {
            $db = Database::getInstance();
            $db->insert('audit_logs', [
                'user_id' => $user->id,
                'action' => 'user.logout',
                'model_type' => 'User',
                'model_id' => $user->id,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            ]);
        }

        $this->cachedUser = null;
        Session::forget(self::SESSION_USER_ID);
        Session::destroy();
    }

    /**
     * Retorna la entidad del usuario autenticado o null.
     */
    public function user(): ?User
    {
        if ($this->cachedUser !== null) {
            return $this->cachedUser;
        }

        $userId = Session::get(self::SESSION_USER_ID);
        if (!$userId) {
            return null;
        }

        $user = User::find((int)$userId);
        if ($user && $user->active) {
            $this->cachedUser = $user;
            return $this->cachedUser;
        }

        // Si fue desactivado o borrado, invalidar sesión
        $this->logout();
        return null;
    }

    public function id(): ?int
    {
        $user = $this->user();
        return $user ? (int)$user->id : null;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function guest(): bool
    {
        return !$this->check();
    }

    public function isAdmin(): bool
    {
        $user = $this->user();
        return $user !== null && $user->role === 'admin';
    }

    public function isDeveloper(): bool
    {
        $user = $this->user();
        return $user !== null && $user->role === 'developer';
    }

    /**
     * Genera un hash seguro para contraseñas.
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    }
}
