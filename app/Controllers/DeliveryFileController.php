<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Delivery;
use App\Policies\ProjectPolicy;

class DeliveryFileController extends BaseController
{
    public function download(string $deliveryId, string $fileIndex): void
    {
        $delivery = Delivery::find((int)$deliveryId);
        if (!$delivery || !ctype_digit($fileIndex)) {
            echo \App\Core\View::renderError(404, "El archivo solicitado no existe.");
            exit;
        }

        $project = $delivery->project();
        if (!$project || !ProjectPolicy::allows($this->user(), 'download', $project)) {
            echo \App\Core\View::renderError(403, "No tienes permiso para descargar este archivo.");
            exit;
        }

        $files = $delivery->files;
        $file = $files[(int)$fileIndex] ?? null;
        $storedPath = is_array($file) ? (string)($file['path'] ?? '') : '';
        $projectDirectory = 'deliveries/' . (int)$project->id . '/';
        if (str_starts_with($storedPath, '/private-uploads/' . $projectDirectory)) {
            $storageRoot = (string)config('app.upload.private_path');
            $expectedPrefix = '/private-uploads/' . $projectDirectory;
        } elseif (str_starts_with($storedPath, '/uploads/' . $projectDirectory)) {
            // Compatibilidad con entregas almacenadas antes de habilitar el disco privado.
            $storageRoot = (string)config('app.upload.path');
            $expectedPrefix = '/uploads/' . $projectDirectory;
        } else {
            echo \App\Core\View::renderError(404, "El archivo solicitado no existe.");
            exit;
        }

        $storedName = basename(substr($storedPath, strlen($expectedPrefix)));
        if ($storedName === '' || $storedName === '.' || $storedName === '..') {
            echo \App\Core\View::renderError(404, "El archivo solicitado no existe.");
            exit;
        }

        $uploadRoot = realpath($storageRoot);
        $filePath = $uploadRoot === false ? false : realpath($uploadRoot . '/' . $projectDirectory . $storedName);
        $deliveryDirectory = $uploadRoot === false ? '' : realpath($uploadRoot . '/deliveries/' . (int)$project->id);

        if ($filePath === false || $deliveryDirectory === false || !is_file($filePath)
            || !str_starts_with($filePath, $deliveryDirectory . DIRECTORY_SEPARATOR)) {
            echo \App\Core\View::renderError(404, "El archivo solicitado no existe.");
            exit;
        }

        $downloadName = basename((string)($file['original_name'] ?? $file['name'] ?? $storedName));
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = $finfo ? finfo_file($finfo, $filePath) : false;
        if ($finfo) {
            finfo_close($finfo);
        }

        header('Content-Type: ' . ($mimeType ?: 'application/octet-stream'));
        header('Content-Length: ' . (string)filesize($filePath));
        header('Content-Disposition: attachment; filename="archivo"; filename*=UTF-8\'\'' . rawurlencode($downloadName));
        header('X-Content-Type-Options: nosniff');
        readfile($filePath);
        exit;
    }
}
