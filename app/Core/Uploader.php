<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Devioz OS — Gestor de Almacenamiento Seguro de Archivos (Uploads)
 */
final class Uploader
{
    private static array $allowedMimes = [
        'pdf'  => 'application/pdf',
        'zip'  => ['application/zip', 'application/x-zip-compressed', 'multipart/x-zip'],
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls'  => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt'  => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'jpg'  => ['image/jpeg', 'image/pjpeg'],
        'jpeg' => ['image/jpeg', 'image/pjpeg'],
        'png'  => 'image/png',
        'webp' => 'image/webp',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
    ];

    /**
     * Procesa y almacena un archivo subido vía $_FILES.
     *
     * @param array $file Estructura $_FILES['campo']
     * @param string $subDirectory Subcarpeta dentro de public/uploads/ (ej: 'deliveries', 'covers')
     * @param int|null $maxSizeBytes Tamaño máximo permitido (por defecto 500MB)
     * @return array Metadatos del archivo persistido
     */
    public static function upload(array $file, string $subDirectory = 'files', ?int $maxSizeBytes = null): array
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new RuntimeException("Estructura de archivo inválida.");
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new RuntimeException("El archivo excede el tamaño máximo permitido por el servidor.");
            case UPLOAD_ERR_PARTIAL:
                throw new RuntimeException("La subida del archivo fue interrumpida.");
            case UPLOAD_ERR_NO_FILE:
                throw new RuntimeException("No se seleccionó ningún archivo para subir.");
            default:
                throw new RuntimeException("Error desconocido en la carga del archivo.");
        }

        $maxSize = $maxSizeBytes ?? config('app.upload.max_size', 524288000);
        if ($file['size'] > $maxSize) {
            $formattedLimit = human_size($maxSize);
            throw new RuntimeException("El archivo supera el tamaño máximo permitido ({$formattedLimit}).");
        }

        // Inspección real de MIME Type en servidor con finfo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $originalName = pathinfo($file['name'], PATHINFO_FILENAME);
        $originalExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Validar extensión y mime permitido
        if (!isset(self::$allowedMimes[$originalExt])) {
            throw new RuntimeException("La extensión de archivo .{$originalExt} no está permitida.");
        }

        $expectedMime = self::$allowedMimes[$originalExt];
        $isMimeValid = is_array($expectedMime)
            ? in_array($mimeType, $expectedMime, true)
            : ($mimeType === $expectedMime);

        // Fallback permisivo para archivos office en ciertos entornos de SO
        if (!$isMimeValid && !str_starts_with($mimeType, 'application/')) {
            throw new RuntimeException("El contenido del archivo no coincide con su extensión reportada ({$mimeType}).");
        }

        // Generar hash SHA-256 para integridad y deduplicación
        $sha256 = hash_file('sha256', $file['tmp_name']);

        // Nombre de archivo seguro y único
        $sanitizedName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalName);
        $storedName = sprintf('%s_%s.%s', $sanitizedName, bin2hex(random_bytes(8)), $originalExt);

        $uploadRoot = config('app.upload.path', dirname(__DIR__, 2) . '/public/uploads');
        $targetDirectory = rtrim($uploadRoot, '/\\') . '/' . trim($subDirectory, '/\\');

        if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0755, true) && !is_dir($targetDirectory)) {
            throw new RuntimeException("No se pudo crear el directorio de destino: {$targetDirectory}");
        }

        $destinationPath = $targetDirectory . '/' . $storedName;

        if (!move_uploaded_file($file['tmp_name'], $destinationPath)) {
            throw new RuntimeException("Fallo al mover el archivo al directorio de almacenamiento.");
        }

        $relativePath = '/uploads/' . trim($subDirectory, '/') . '/' . $storedName;

        return [
            'original_name' => $file['name'],
            'stored_name'   => $storedName,
            'disk'          => 'local',
            'path'          => $relativePath,
            'full_path'     => $destinationPath,
            'mime_type'     => $mimeType,
            'size'          => (int)$file['size'],
            'hash'          => $sha256,
        ];
    }
}
