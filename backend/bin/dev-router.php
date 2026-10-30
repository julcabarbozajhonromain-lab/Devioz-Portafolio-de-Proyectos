<?php

declare(strict_types=1);

$publicRoot = realpath(dirname(__DIR__, 2) . '/frontend/public');
$requestPath = rawurldecode((string)(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'));
$assetPath = preg_replace('#^/public(?=/|$)#', '', $requestPath) ?: '/';
$candidate = realpath($publicRoot . DIRECTORY_SEPARATOR . ltrim($assetPath, '/\\'));

if (
    $candidate !== false
    && is_file($candidate)
    && str_starts_with($candidate, $publicRoot . DIRECTORY_SEPARATOR)
) {
    return false;
}

require $publicRoot . '/index.php';

