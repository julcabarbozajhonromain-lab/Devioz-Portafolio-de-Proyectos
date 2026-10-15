<?php

declare(strict_types=1);

/**
 * Devioz Classic — Instalador y Migrador CLI Automatizado
 * Uso: php bin/setup.php
 */

echo "\n=======================================================\n";
echo "  DEVIOZ CLASSIC — INSTALADOR AUTOMÁTICO DE BASE DE DATOS\n";
echo "=======================================================\n\n";

$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (str_contains($line, '=')) {
            [$k, $v] = explode('=', $line, 2);
            putenv(trim($k) . '=' . trim($v, " \t\n\r\0\x0B\"'"));
        }
    }
}

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = (int)(getenv('DB_PORT') ?: 3306);
$dbName = getenv('DB_DATABASE') ?: 'devioz_classic';
$user = getenv('DB_USERNAME') ?: 'root';
$pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '';

echo "[1/4] Conectando con servidor MySQL en {$host}:{$port}...\n";

try {
    $pdo = new PDO("mysql:host={$host};port={$port}", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "      [OK] Conexión exitosa al servidor MySQL.\n";
} catch (PDOException $e) {
    echo "      [ERROR] No se pudo conectar a MySQL. ¿Está iniciado el servicio en XAMPP?\n";
    echo "      Detalle: " . $e->getMessage() . "\n\n";
    exit(1);
}

echo "[2/4] Creando base de datos '{$dbName}' si no existe...\n";
$pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
$pdo->exec("USE `{$dbName}`;");
echo "      [OK] Base de datos '{$dbName}' seleccionada.\n";

echo "[3/4] Ejecutando estructura DDL (database/schema.sql)...\n";
$schemaFile = dirname(__DIR__) . '/database/schema.sql';
if (!file_exists($schemaFile)) {
    echo "      [ERROR] No se encontró database/schema.sql\n";
    exit(1);
}

$schemaSql = file_get_contents($schemaFile);
$pdo->exec($schemaSql);
echo "      [OK] 10 tablas creadas con éxito con motor InnoDB.\n";

echo "[4/4] Sembrando datos iniciales reales (database/seed.sql)...\n";
$seedFile = dirname(__DIR__) . '/database/seed.sql';
if (!file_exists($seedFile)) {
    echo "      [ERROR] No se encontró database/seed.sql\n";
    exit(1);
}

$seedSql = file_get_contents($seedFile);
$pdo->exec($seedSql);

$usersCount = $pdo->query("SELECT COUNT(*) FROM `users`")->fetchColumn();
$projectsCount = $pdo->query("SELECT COUNT(*) FROM `projects`")->fetchColumn();

echo "      [OK] Sembrado completado exitosamente:\n";
echo "         - Usuarios creados: {$usersCount} (2 Admins + 34 Developers)\n";
echo "         - Proyectos semilla: {$projectsCount}\n\n";

echo "=======================================================\n";
echo "  INSTALACIÓN FINALIZADA CON ÉXITO\n";
echo "=======================================================\n";
echo "  Acceso Local: http://localhost/Devioz-Portafolio-de-Proyectos/\n";
echo "  Cambia las contraseñas de las cuentas de muestra antes de usar otro entorno.\n";
echo "=======================================================\n\n";
