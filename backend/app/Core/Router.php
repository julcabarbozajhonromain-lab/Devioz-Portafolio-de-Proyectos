<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Devioz OS — Enrutador HTTP Ligero con Soporte de Middleware y Parámetros Dinámicos
 */
final class Router
{
    private array $routes = [];
    private array $namedRoutes = [];
    private ?string $groupPrefix = '';
    private array $groupMiddlewares = [];

    public function get(string $path, array|callable $handler, array $middlewares = [], ?string $name = null): self
    {
        return $this->addRoute('GET', $path, $handler, $middlewares, $name);
    }

    public function post(string $path, array|callable $handler, array $middlewares = [], ?string $name = null): self
    {
        return $this->addRoute('POST', $path, $handler, $middlewares, $name);
    }

    public function put(string $path, array|callable $handler, array $middlewares = [], ?string $name = null): self
    {
        return $this->addRoute('PUT', $path, $handler, $middlewares, $name);
    }

    public function delete(string $path, array|callable $handler, array $middlewares = [], ?string $name = null): self
    {
        return $this->addRoute('DELETE', $path, $handler, $middlewares, $name);
    }

    public function group(string $prefix, array $middlewares, callable $callback): void
    {
        $previousPrefix = $this->groupPrefix;
        $previousMiddlewares = $this->groupMiddlewares;

        $this->groupPrefix = $previousPrefix . '/' . trim($prefix, '/');
        $this->groupMiddlewares = array_merge($previousMiddlewares, $middlewares);

        $callback($this);

        $this->groupPrefix = $previousPrefix;
        $this->groupMiddlewares = $previousMiddlewares;
    }

    private function addRoute(string $method, string $path, array|callable $handler, array $middlewares = [], ?string $name = null): self
    {
        $fullPath = '/' . trim($this->groupPrefix . '/' . trim($path, '/'), '/');
        $allMiddlewares = array_merge($this->groupMiddlewares, $middlewares);

        $route = [
            'method' => strtoupper($method),
            'path' => $fullPath === '' ? '/' : $fullPath,
            'pattern' => $this->compilePattern($fullPath),
            'handler' => $handler,
            'middlewares' => $allMiddlewares,
        ];

        $this->routes[] = $route;

        if ($name !== null) {
            $this->namedRoutes[$name] = $fullPath;
        }

        return $this;
    }

    /**
     * Convierte patrones como /projects/{id} en expresiones regulares.
     */
    private function compilePattern(string $path): string
    {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . ($pattern === '/' ? '/' : rtrim($pattern, '/')) . '$#';
    }

    /**
     * Despacha el request entrante.
     */
    public function dispatch(string $uri, string $requestMethod): void
    {
        $method = strtoupper($requestMethod);

        // Soporte para method spoofing (_method en POST)
        if ($method === 'POST' && isset($_POST['_method'])) {
            $spoofedMethod = strtoupper((string)$_POST['_method']);
            if (in_array($spoofedMethod, ['PUT', 'PATCH', 'DELETE'], true)) {
                $method = $spoofedMethod;
            }
        }

        // Limpieza de Query Strings y base URL
        $parsedUrl = parse_url($uri, PHP_URL_PATH) ?: '/';
        
        // Quitar subcarpetas base si la app está en un subdirectorio (con o sin /public)
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $parentDir = str_replace('\\', '/', dirname($scriptDir));

        $cleanUri = $parsedUrl;
        if ($scriptDir !== '/' && $scriptDir !== '' && str_starts_with($cleanUri, $scriptDir)) {
            $cleanUri = substr($cleanUri, strlen($scriptDir));
        } elseif ($parentDir !== '/' && $parentDir !== '' && $parentDir !== '.' && str_starts_with($cleanUri, $parentDir)) {
            $cleanUri = substr($cleanUri, strlen($parentDir));
        }
        
        $cleanUri = '/' . trim($cleanUri, '/');
        if ($cleanUri === '') {
            $cleanUri = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $cleanUri, $matches)) {
                // Filtrar solo parámetros nombrados
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Ejecutar Middlewares
                $this->runMiddlewares($route['middlewares'], $method);

                // Ejecutar Controlador / Handler
                $this->executeHandler($route['handler'], $params);
                return;
            }
        }

        // Si ninguna ruta coincidió -> 404
        echo View::renderError(404, "La página que buscas no existe o ha sido movida.");
    }

    /**
     * Ejecuta la cadena de middlewares de la ruta.
     */
    private function runMiddlewares(array $middlewares, string $method): void
    {
        $auth = Auth::getInstance();

        // Validar CSRF automáticamente en mutaciones HTTP (POST, PUT, DELETE)
        if (in_array($method, ['POST', 'PUT', 'DELETE'], true) && !in_array('no-csrf', $middlewares, true)) {
            $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
            if (!Session::verifyCsrf($token)) {
                $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                    || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));
                if ($isAjax) {
                    http_response_code(419);
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode([
                        'status' => 419,
                        'error' => 'CSRF Token Expired',
                        'message' => 'Tu sesión o token de seguridad expiró. Por favor intenta nuevamente.'
                    ]);
                    exit;
                }
                http_response_code(419);
                Session::flash('error', 'Tu sesión o token de seguridad expiró. Por favor intenta nuevamente.');
                redirect('/login?session_expired=1');
            }
        }

        foreach ($middlewares as $mw) {
            if ($mw === 'auth') {
                if (!$auth->check()) {
                    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                        || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));
                    if ($isAjax) {
                        http_response_code(401);
                        header('Content-Type: application/json; charset=utf-8');
                        echo json_encode([
                            'status' => 401,
                            'error' => 'Unauthenticated',
                            'message' => 'Sesión expirada o no autenticada.',
                            'redirect' => url('/login?session_expired=1')
                        ]);
                        exit;
                    }
                    Session::flash('warning', 'Debes iniciar sesión para acceder a esta sección.');
                    redirect('/login?session_expired=1');
                }
            } elseif ($mw === 'guest') {
                if ($auth->check()) {
                    redirect('/dashboard');
                }
            } elseif ($mw === 'admin') {
                if (!$auth->check()) {
                    redirect('/login?session_expired=1');
                }
                if (!$auth->isAdmin()) {
                    if ($auth->isDeveloper()) {
                        redirect('/my-project');
                    }
                    echo View::renderError(403, "Se requieren privilegios de Administrador para esta acción.");
                    exit;
                }
            } elseif ($mw === 'developer') {
                if (!$auth->check()) {
                    redirect('/login?session_expired=1');
                }
                if (!$auth->isDeveloper()) {
                    if ($auth->isAdmin()) {
                        redirect('/admin/assignments');
                    }
                    echo View::renderError(403, "Esta sección es exclusiva para Desarrolladores.");
                    exit;
                }
            }
        }
    }

    /**
     * Invoca el callable o [ControllerClass, 'method'] pasando los parámetros capturados.
     */
    private function executeHandler(array|callable $handler, array $params): void
    {
        if (is_callable($handler)) {
            $response = call_user_func_array($handler, $params);
            if (is_string($response)) {
                echo $response;
            }
            return;
        }

        if (is_array($handler) && count($handler) === 2) {
            [$class, $method] = $handler;

            if (!class_exists($class)) {
                throw new RuntimeException("El controlador [{$class}] no existe.");
            }

            $controllerInstance = new $class();

            if (!method_exists($controllerInstance, $method)) {
                throw new RuntimeException("El método [{$method}] no existe en el controlador [{$class}].");
            }

            $response = call_user_func_array([$controllerInstance, $method], $params);
            if (is_string($response)) {
                echo $response;
            }
            return;
        }

        throw new RuntimeException("Handler de ruta no válido.");
    }
}
