<?php

declare(strict_types=1);

/**
 * Devioz OS — Punto de Entrada Principal (Front Controller)
 */

define('DEVIOZ_START', microtime(true));

/** @var \App\Core\Router $router */
$router = require dirname(__DIR__) . '/bootstrap/app.php';

$uri = $_SERVER['REQUEST_URI'] ?? '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$router->dispatch($uri, $method);
