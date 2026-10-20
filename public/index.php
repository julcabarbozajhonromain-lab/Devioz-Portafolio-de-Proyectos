<?php

declare(strict_types=1);

require dirname(__DIR__) . '/backend/bootstrap/app.php';

$uri = $_SERVER['REQUEST_URI'] ?? '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$router->dispatch($uri, $method);
