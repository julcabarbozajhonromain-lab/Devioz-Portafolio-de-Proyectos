<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Devioz 24 — Sistema de Almacenamiento en Caché de Alto Rendimiento (File & In-Memory Cache)
 * Permite caching de catálogos, sedes, estadísticas y consultas recurrentes para evitar sobrecarga de BD.
 */
final class Cache
{
    private static array $memoryStore = [];
    private static ?string $cachePath = null;

    private static function init(): void
    {
        if (self::$cachePath === null) {
            self::$cachePath = DEVIOZ_BACKEND . '/storage/framework/cache';
            if (!is_dir(self::$cachePath)) {
                @mkdir(self::$cachePath, 0775, true);
            }
        }
    }

    /**
     * Obtiene un valor de la caché si no ha expirado.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        // 1. Verificar caché en memoria de ejecución rápida
        if (array_key_exists($key, self::$memoryStore)) {
            $item = self::$memoryStore[$key];
            if ($item['expires_at'] === 0 || $item['expires_at'] > time()) {
                return $item['value'];
            }
            unset(self::$memoryStore[$key]);
        }

        // 2. Verificar caché en archivo
        self::init();
        $filePath = self::getFilePath($key);
        if (!file_exists($filePath)) {
            return $default;
        }

        $raw = @file_get_contents($filePath);
        if ($raw === false) {
            return $default;
        }

        $payload = @unserialize($raw);
        if (!is_array($payload) || !isset($payload['expires_at'], $payload['value'])) {
            @unlink($filePath);
            return $default;
        }

        if ($payload['expires_at'] !== 0 && $payload['expires_at'] < time()) {
            @unlink($filePath);
            return $default;
        }

        // Guardar en memoria para subsecuentes consultas en el mismo ciclo
        self::$memoryStore[$key] = $payload;

        return $payload['value'];
    }

    /**
     * Guarda un elemento en la caché.
     *
     * @param string $key Clave única
     * @param mixed $value Valor a almacenar
     * @param int $ttlSeconds Tiempo de vida en segundos (0 = para siempre)
     */
    public static function set(string $key, mixed $value, int $ttlSeconds = 3600): bool
    {
        self::init();
        $expiresAt = $ttlSeconds > 0 ? time() + $ttlSeconds : 0;
        $payload = [
            'expires_at' => $expiresAt,
            'value'      => $value,
        ];

        // Guardar en memoria
        self::$memoryStore[$key] = $payload;

        // Guardar en disco
        $filePath = self::getFilePath($key);
        return (bool)@file_put_contents($filePath, serialize($payload), LOCK_EX);
    }

    /**
     * Obtiene un valor o ejecuta el callback y almacena el resultado si no existía.
     */
    public static function remember(string $key, int $ttlSeconds, callable $callback): mixed
    {
        $cached = self::get($key);
        if ($cached !== null) {
            return $cached;
        }

        $fresh = $callback();
        self::set($key, $fresh, $ttlSeconds);
        return $fresh;
    }

    /**
     * Elimina un elemento de la caché.
     */
    public static function forget(string $key): bool
    {
        unset(self::$memoryStore[$key]);
        self::init();
        $filePath = self::getFilePath($key);
        if (file_exists($filePath)) {
            return @unlink($filePath);
        }
        return true;
    }

    /**
     * Limpia completamente todos los elementos cacheados.
     */
    public static function flush(): bool
    {
        self::$memoryStore = [];
        self::init();
        $files = glob(self::$cachePath . '/*.cache');
        if ($files) {
            foreach ($files as $f) {
                @unlink($f);
            }
        }
        return true;
    }

    private static function getFilePath(string $key): string
    {
        $hash = sha1($key);
        return self::$cachePath . '/' . $hash . '.cache';
    }
}
