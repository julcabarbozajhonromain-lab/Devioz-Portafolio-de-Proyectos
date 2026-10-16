<?php

declare(strict_types=1);

/**
 * Migra adjuntos de entregas desde public/uploads a storage privado.
 * Uso: php bin/migrate_delivery_uploads.php [--apply]
 * Sin --apply solo informa el resultado; los archivos originales nunca se eliminan.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$unknownOptions = array_diff(array_slice($argv, 1), ['--apply']);
if ($unknownOptions !== []) {
    fwrite(STDERR, "Opción no reconocida. Uso: php bin/migrate_delivery_uploads.php [--apply]\n");
    exit(2);
}

$apply = in_array('--apply', $argv, true);
$projectRoot = dirname(__DIR__);

spl_autoload_register(static function (string $class) use ($projectRoot): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $classFile = $projectRoot . '/app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($classFile)) {
        require_once $classFile;
    }
});
require_once $projectRoot . '/app/Helpers/functions.php';

$envFile = $projectRoot . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value, " \t\n\r\0\x0B\"'"));
    }
}

$pdo = \App\Core\Database::getInstance()->getConnection();
$publicRoot = realpath($projectRoot . '/public/uploads');
$privateRoot = rtrim((string)config('app.upload.private_path'), '/\\');

if ($publicRoot === false) {
    fwrite(STDERR, "No existe el directorio public/uploads.\n");
    exit(1);
}

$publicBase = realpath($projectRoot . '/public');
$privateCanonical = realpath($privateRoot);
$privateForCheck = $privateCanonical !== false ? $privateCanonical : $privateRoot;
if ($publicBase !== false && str_starts_with(
    strtolower($privateForCheck . DIRECTORY_SEPARATOR),
    strtolower($publicBase . DIRECTORY_SEPARATOR)
)) {
    fwrite(STDERR, "UPLOAD_PRIVATE_PATH debe apuntar fuera de public/.\n");
    exit(1);
}

$rows = $pdo->query("SELECT id, project_id, files FROM deliveries WHERE files IS NOT NULL ORDER BY id")
    ->fetchAll(PDO::FETCH_ASSOC);
$counts = ['ready' => 0, 'already_private' => 0, 'missing' => 0, 'conflict' => 0, 'migrated' => 0];
$plans = [];

foreach ($rows as $row) {
    $files = json_decode((string)$row['files'], true);
    if (!is_array($files)) {
        fwrite(STDERR, "Entrega {$row['id']}: JSON de adjuntos inválido; se omite.\n");
        continue;
    }

    $projectId = (int)$row['project_id'];
    $legacyPrefix = "/uploads/deliveries/{$projectId}/";
    $privatePrefix = "/private-uploads/deliveries/{$projectId}/";
    $deliveryChanges = [];
    $deliveryMissing = false;
    $deliveryConflict = false;

    foreach ($files as $index => $file) {
        if (!is_array($file) || empty($file['path'])) {
            continue;
        }

        $storedPath = (string)$file['path'];
        if (str_starts_with($storedPath, $privatePrefix)) {
            continue;
        }
        if (!str_starts_with($storedPath, $legacyPrefix)) {
            continue;
        }

        $storedName = substr($storedPath, strlen($legacyPrefix));
        if ($storedName === '' || basename($storedName) !== $storedName || str_contains($storedName, "\\")) {
            $deliveryConflict = true;
            continue;
        }

        $legacyDirectory = realpath($publicRoot . "/deliveries/{$projectId}");
        $source = $legacyDirectory === false ? false : realpath($legacyDirectory . '/' . $storedName);
        if ($source === false || !is_file($source)
            || !str_starts_with($source, $legacyDirectory . DIRECTORY_SEPARATOR)) {
            $deliveryMissing = true;
            continue;
        }

        $target = $privateRoot . "/deliveries/{$projectId}/{$storedName}";
        if (is_file($target)) {
            if (!hash_equals((string)hash_file('sha256', $source), (string)hash_file('sha256', $target))) {
                $deliveryConflict = true;
                continue;
            }
        }

        $file['path'] = $privatePrefix . $storedName;
        $deliveryChanges[$index] = $file;
    }

    if ($deliveryConflict || $deliveryMissing) {
        $counts[$deliveryConflict ? 'conflict' : 'missing']++;
        $reason = $deliveryConflict ? 'hay ruta inválida o destino en conflicto' : 'faltan archivos fuente';
        fwrite(STDOUT, "Entrega {$row['id']}: no migrada ({$reason}); se conserva su referencia actual.\n");
        continue;
    }

    if ($deliveryChanges === []) {
        $counts['already_private']++;
        continue;
    }

    $plans[] = ['id' => (int)$row['id'], 'project_id' => $projectId, 'files' => $files, 'changes' => $deliveryChanges];
    $counts['ready']++;
    fwrite(STDOUT, "Entrega {$row['id']}: " . ($apply ? 'migrando' : 'lista para migrar') . " (" . count($deliveryChanges) . " adjunto(s)).\n");
}

if ($apply) {
    foreach ($plans as $plan) {
        $createdTargets = [];
        try {
            $pdo->beginTransaction();
            $statement = $pdo->prepare('SELECT files FROM deliveries WHERE id = :id FOR UPDATE');
            $statement->execute(['id' => $plan['id']]);
            $currentFiles = json_decode((string)$statement->fetchColumn(), true);
            if (!is_array($currentFiles)) {
                throw new RuntimeException('No se pudieron leer los adjuntos actuales.');
            }

            foreach ($plan['changes'] as $index => $file) {
                if (!isset($currentFiles[$index]['path']) || $currentFiles[$index]['path'] !== $plan['files'][$index]['path']) {
                    throw new RuntimeException('Las referencias cambiaron durante la migración; vuelva a ejecutar la simulación.');
                }
            }

            foreach ($plan['changes'] as $index => $file) {
                $legacyPrefix = '/uploads/deliveries/' . $plan['project_id'] . '/';
                $storedName = substr((string)$plan['files'][$index]['path'], strlen($legacyPrefix));
                $sourceDirectory = realpath($publicRoot . '/deliveries/' . $plan['project_id']);
                $source = $sourceDirectory === false ? false : realpath($sourceDirectory . '/' . $storedName);
                if ($source === false || !is_file($source)) {
                    throw new RuntimeException("No se encuentra el archivo fuente {$storedName}.");
                }

                $targetDirectory = $privateRoot . '/deliveries/' . $plan['project_id'];
                if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0755, true) && !is_dir($targetDirectory)) {
                    throw new RuntimeException('No se pudo crear el directorio privado.');
                }
                $target = $targetDirectory . '/' . $storedName;
                if (is_file($target)) {
                    if (!hash_equals((string)hash_file('sha256', $source), (string)hash_file('sha256', $target))) {
                        throw new RuntimeException("El destino {$storedName} contiene datos distintos.");
                    }
                } else {
                    if (!copy($source, $target)) {
                        throw new RuntimeException("No se pudo copiar {$storedName}.");
                    }
                    $createdTargets[] = $target;
                }

                $currentFiles[$index]['path'] = $file['path'];
            }

            $update = $pdo->prepare('UPDATE deliveries SET files = :files WHERE id = :id');
            $update->execute([
                'files' => json_encode($currentFiles, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                'id' => $plan['id'],
            ]);
            $pdo->commit();
            $counts['migrated']++;
        } catch (Throwable $error) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            foreach ($createdTargets as $target) {
                @unlink($target);
            }
            fwrite(STDERR, "Entrega {$plan['id']}: error; se revirtió la referencia y se conservaron los originales. {$error->getMessage()}\n");
            exit(1);
        }
    }
}

fwrite(STDOUT, sprintf(
    "\nModo: %s | listas: %d | ya privadas/sin adjuntos heredados: %d | faltantes: %d | conflictos: %d | migradas: %d\n",
    $apply ? 'APLICAR' : 'SIMULACIÓN',
    $counts['ready'],
    $counts['already_private'],
    $counts['missing'],
    $counts['conflict'],
    $counts['migrated']
));

if (!$apply && $counts['ready'] > 0) {
    fwrite(STDOUT, "Para copiar los archivos y actualizar sus referencias ejecute: php bin/migrate_delivery_uploads.php --apply\n");
}
