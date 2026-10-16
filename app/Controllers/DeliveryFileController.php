<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Delivery;

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
        if (!$project || (!$this->auth->isAdmin() && (int)$project->developer_id !== $this->auth->id())) {
            echo \App\Core\View::renderError(403, "No tienes permiso para descargar este archivo.");
            exit;
        }

        $files = $delivery->files;
        $file = $files[(int)$fileIndex] ?? null;
        $expectedPrefix = '/uploads/deliveries/' . (int)$project->id . '/';
        $storedPath = is_array($file) ? (string)($file['path'] ?? '') : '';

        if ($storedPath === '' || !str_starts_with($storedPath, $expectedPrefix)) {
            echo \App\Core\View::renderError(404, "El archivo solicitado no existe.");
            exit;
        }

        $storedName = basename(substr($storedPath, strlen($expectedPrefix)));
        if ($storedName === '' || $storedName === '.' || $storedName === '..') {
            echo \App\Core\View::renderError(404, "El archivo solicitado no existe.");
            exit;
        }

        $uploadRoot = realpath((string)config('app.upload.path'));
        $filePath = $uploadRoot === false ? false : realpath($uploadRoot . '/deliveries/' . (int)$project->id . '/' . $storedName);
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
