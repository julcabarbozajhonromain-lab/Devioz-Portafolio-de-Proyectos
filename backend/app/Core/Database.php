<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

/**
 * Devioz OS — Gestor Singleton de Conexión y Consultas a Base de Datos (PDO)
 * Garantiza el uso estricto de Prepared Statements en toda la aplicación.
 */
final class Database
{
    private static ?self $instance = null;
    private PDO $pdo;
    private array $queryLog = [];

    private function __construct()
    {
        $config = config('database');
        
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'] ?? '127.0.0.1',
            $config['port'] ?? 3306,
            $config['database'] ?? 'devioz_classic',
            $config['charset'] ?? 'utf8mb4'
        );

        $options = $config['options'] ?? [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->pdo = new PDO(
                $dsn,
                (string)($config['username'] ?? 'root'),
                (string)($config['password'] ?? ''),
                $options
            );
        } catch (PDOException $e) {
            if (config('app.debug', false)) {
                throw new RuntimeException("Error al conectar a la base de datos: " . $e->getMessage(), (int)$e->getCode(), $e);
            }
            throw new RuntimeException("No se pudo establecer conexión con el motor de base de datos.", 500);
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    /**
     * Ejecuta una consulta preparada con parámetros enlazados.
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        $start = microtime(true);
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            if (config('app.debug', false)) {
                $duration = round((microtime(true) - $start) * 1000, 2);
                $this->queryLog[] = [
                    'sql' => $sql,
                    'params' => $params,
                    'duration_ms' => $duration,
                ];
            }

            return $stmt;
        } catch (PDOException $e) {
            if (config('app.debug', false)) {
                throw new RuntimeException("Error en consulta SQL [{$sql}]: " . $e->getMessage(), (int)$e->getCode(), $e);
            }
            throw new RuntimeException("Ocurrió un error en la ejecución de la consulta a la base de datos.", 500);
        }
    }

    /**
     * Retorna una sola fila como array asociativo o null si no existe.
     */
    public function fetch(string $sql, array $params = []): ?array
    {
        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return $result === false ? null : $result;
    }

    /**
     * Retorna todas las filas resultantes como array asociativo.
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Retorna el valor de una sola columna de la primera fila resultante.
     */
    public function fetchColumn(string $sql, array $params = [], int $column = 0): mixed
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn($column);
    }

    /**
     * Inserta un registro y retorna el ID autonumérico generado.
     */
    public function insert(string $table, array $data): int
    {
        $columns = array_keys($data);
        $escapedColumns = array_map(fn($col) => "`{$col}`", $columns);
        $placeholders = array_map(fn($col) => ":{$col}", $columns);

        $sql = sprintf(
            'INSERT INTO `%s` (%s) VALUES (%s)',
            $table,
            implode(', ', $escapedColumns),
            implode(', ', $placeholders)
        );

        $this->query($sql, $data);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Actualiza registros según una cláusula WHERE y retorna filas afectadas.
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $fields = [];
        $params = [];

        foreach ($data as $column => $value) {
            $paramName = "set_{$column}";
            $fields[] = "`{$column}` = :{$paramName}";
            $params[$paramName] = $value;
        }

        $params = array_merge($params, $whereParams);

        $sql = sprintf(
            'UPDATE `%s` SET %s WHERE %s',
            $table,
            implode(', ', $fields),
            $where
        );

        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Elimina registros según una condición WHERE.
     */
    public function delete(string $table, string $where, array $whereParams = []): int
    {
        $sql = sprintf('DELETE FROM `%s` WHERE %s', $table, $where);
        $stmt = $this->query($sql, $whereParams);
        return $stmt->rowCount();
    }

    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    public function getQueryLog(): array
    {
        return $this->queryLog;
    }

    private function __clone() {}
    public function __wakeup()
    {
        throw new RuntimeException("No se permite deserializar una instancia Singleton de Database.");
    }
}
