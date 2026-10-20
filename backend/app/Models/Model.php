<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use RuntimeException;

/**
 * Devioz OS — Modelo Base Activo (Active Record / Data Mapper Híbrido)
 * Proporciona métodos CRUD, relaciones tipadas (belongsTo, hasMany, hasOne) y casting automático.
 */
abstract class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';
    protected array $casts = [];
    protected array $attributes = [];
    protected array $original = [];

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
        $this->original = $this->attributes;
    }

    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
        return $this;
    }

    public function __get(string $key): mixed
    {
        return $this->getAttribute($key);
    }

    public function __set(string $key, mixed $value): void
    {
        $this->setAttribute($key, $value);
    }

    public function __isset(string $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    public function getAttribute(string $key): mixed
    {
        $val = $this->attributes[$key] ?? null;

        if (isset($this->casts[$key])) {
            return $this->castAttribute($this->casts[$key], $val);
        }

        return $val;
    }

    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function toArray(): array
    {
        $result = [];
        foreach ($this->attributes as $key => $val) {
            $result[$key] = $this->getAttribute($key);
        }
        return $result;
    }

    protected function castAttribute(string $type, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'json', 'array' => is_string($value) ? (json_decode($value, true) ?: []) : (array)$value,
            'bool', 'boolean' => (bool)$value,
            'int', 'integer' => (int)$value,
            'float', 'decimal' => (float)$value,
            'string' => (string)$value,
            default => $value,
        };
    }

    protected static function getDb(): Database
    {
        return Database::getInstance();
    }

    public static function getTableName(): string
    {
        if (!empty(static::$table)) {
            return static::$table;
        }

        // Convierte App\Models\UserProfile a user_profiles
        $short = (new \ReflectionClass(static::class))->getShortName();
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $short)) . 's';
    }

    public static function find(int $id): ?static
    {
        $table = static::getTableName();
        $pk = static::$primaryKey;
        $row = self::getDb()->fetch("SELECT * FROM `{$table}` WHERE `{$pk}` = :id LIMIT 1", ['id' => $id]);

        return $row ? new static($row) : null;
    }

    public static function all(string $orderBy = 'id DESC'): array
    {
        $table = static::getTableName();
        $rows = self::getDb()->fetchAll("SELECT * FROM `{$table}` ORDER BY {$orderBy}");

        return array_map(fn($row) => new static($row), $rows);
    }

    public static function where(string $column, mixed $value = null, string $operator = '=', string $orderBy = 'id DESC'): array
    {
        // Soporte defensivo si se pasa una condición SQL completa en el primer argumento
        if ($value === null && (str_contains($column, ' ') || str_contains($column, '='))) {
            return static::whereRaw($column, [], $orderBy);
        }

        $table = static::getTableName();
        $paramName = "col_" . preg_replace('/[^a-zA-Z0-9]/', '', $column);
        $sql = "SELECT * FROM `{$table}` WHERE `{$column}` {$operator} :{$paramName} ORDER BY {$orderBy}";
        $rows = self::getDb()->fetchAll($sql, [$paramName => $value]);

        return array_map(fn($row) => new static($row), $rows);
    }

    public static function whereRaw(string $sqlWhere, array $params = [], string $orderBy = 'id DESC', ?int $limit = null): array
    {
        $table = static::getTableName();
        $sql = "SELECT * FROM `{$table}` WHERE {$sqlWhere} ORDER BY {$orderBy}";
        if ($limit !== null) {
            $sql .= " LIMIT {$limit}";
        }
        $rows = self::getDb()->fetchAll($sql, $params);

        return array_map(fn($row) => new static($row), $rows);
    }

    public static function findWhere(string $column, mixed $value, string $operator = '='): ?static
    {
        $results = static::where($column, $value, $operator, 'id ASC');
        return $results[0] ?? null;
    }

    public static function findBy(string $column, mixed $value): ?static
    {
        return static::findWhere($column, $value);
    }

    public static function count(string $where = '1=1', array $params = []): int
    {
        $table = static::getTableName();
        $row = self::getDb()->fetch("SELECT COUNT(*) as total FROM `{$table}` WHERE {$where}", $params);
        return (int)($row['total'] ?? 0);
    }

    public static function create(array $data): static
    {
        $instance = new static();
        $table = static::getTableName();

        // Serializar casts de JSON si aplican
        $payload = [];
        foreach ($data as $key => $val) {
            if (isset($instance->casts[$key]) && in_array($instance->casts[$key], ['json', 'array'], true)) {
                $payload[$key] = is_string($val) ? $val : json_encode($val, JSON_UNESCAPED_UNICODE);
            } else {
                $payload[$key] = $val;
            }
        }

        $id = self::getDb()->insert($table, $payload);
        return static::find($id);
    }

    public function update(array $data): bool
    {
        $pk = static::$primaryKey;
        $id = $this->getAttribute($pk);

        if (!$id) {
            throw new RuntimeException("No se puede actualizar un modelo sin clave primaria.");
        }

        $payload = [];
        foreach ($data as $key => $val) {
            if (isset($this->casts[$key]) && in_array($this->casts[$key], ['json', 'array'], true)) {
                $payload[$key] = is_string($val) ? $val : json_encode($val, JSON_UNESCAPED_UNICODE);
            } else {
                $payload[$key] = $val;
            }
            $this->setAttribute($key, $val);
        }

        $table = static::getTableName();
        self::getDb()->update($table, $payload, "`{$pk}` = :id", ['id' => $id]);
        return true;
    }

    public function delete(): bool
    {
        $pk = static::$primaryKey;
        $id = $this->getAttribute($pk);

        if (!$id) {
            return false;
        }

        $table = static::getTableName();
        self::getDb()->delete($table, "`{$pk}` = :id", ['id' => $id]);
        return true;
    }

    // --------------------------------------------------------------------------
    // Métodos de Relaciones
    // --------------------------------------------------------------------------

    protected function belongsTo(string $relatedClass, string $foreignKey, string $ownerKey = 'id'): ?Model
    {
        $foreignValue = $this->getAttribute($foreignKey);
        if (!$foreignValue) {
            return null;
        }

        /** @var Model $relatedClass */
        return $relatedClass::find((int)$foreignValue);
    }

    protected function hasMany(string $relatedClass, string $foreignKey, string $localKey = 'id', string $orderBy = 'id ASC'): array
    {
        $localValue = $this->getAttribute($localKey);
        if (!$localValue) {
            return [];
        }

        /** @var Model $relatedClass */
        return $relatedClass::where($foreignKey, $localValue, '=', $orderBy);
    }

    protected function hasOne(string $relatedClass, string $foreignKey, string $localKey = 'id'): ?Model
    {
        $results = $this->hasMany($relatedClass, $foreignKey, $localKey);
        return $results[0] ?? null;
    }
}
