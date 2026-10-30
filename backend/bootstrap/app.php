<?php

declare(strict_types=1);

/**
 * Devioz 24 — Archivo de Arranque y Bootstrap de la Aplicación
 */

// 1. Autoloader PSR-4 Nativo (Funciona con o sin composer dump-autoload)
if (!defined('DEVIOZ_ROOT')) {
    define('DEVIOZ_ROOT', dirname(__DIR__, 2));
    define('DEVIOZ_BACKEND', dirname(__DIR__));
    define('DEVIOZ_FRONTEND', DEVIOZ_ROOT . '/frontend');
    define('DEVIOZ_PUBLIC', DEVIOZ_FRONTEND . '/public');
    define('DEVIOZ_VIEWS', DEVIOZ_FRONTEND . '/views');
    define('DEVIOZ_DATABASE', DEVIOZ_ROOT . '/database');
}

spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    $baseDir = DEVIOZ_BACKEND . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// 2. Cargar Helpers Globales
$helpersFile = DEVIOZ_BACKEND . '/app/Helpers/functions.php';
if (file_exists($helpersFile)) {
    require_once $helpersFile;
}

// 3. Cargador de Archivo de Entorno .env
$envFile = DEVIOZ_ROOT . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// 4. Configuración de Zona Horaria y Manejadores Globales de Error (Devioz 24 Enterprise)
date_default_timezone_set(config('app.timezone', 'America/Lima'));

if (config('app.debug', false)) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Interceptar errores de PHP y convertirlos a ErrorException
set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
});

// Manejador global de excepciones (500) con log estructurado y protección de trazas
set_exception_handler(function (\Throwable $e) {
    $logDir = DEVIOZ_BACKEND . '/storage/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0775, true);
    }
    $logFile = $logDir . '/laravel.log';
    $date = date('Y-m-d H:i:s');
    $logEntry = sprintf(
        "[%s] %s.CRITICAL: %s in %s:%d\nStack trace:\n%s\n\n",
        $date,
        config('app.env', 'production'),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    );
    @file_put_contents($logFile, $logEntry, FILE_APPEND);

    http_response_code(500);

    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $isAjax = str_contains($uri, '/api/')
        || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'status' => 500,
            'error' => 'Internal Server Error',
            'message' => config('app.debug', false)
                ? 'Error 500: ' . $e->getMessage() . ' en ' . basename($e->getFile()) . ':' . $e->getLine()
                : 'Ocurrió un error inesperado al procesar la solicitud. El incidente ha sido registrado por el sistema.'
        ]);
        exit;
    }

    if (config('app.debug', false)) {
        echo \App\Core\View::renderError(500, "Excepción capturada: " . $e->getMessage());
    } else {
        echo \App\Core\View::renderError(500, "Ocurrió un problema imprevisto al procesar la solicitud. El incidente ha sido registrado.");
    }
    exit;
});

// 5. Inicializar Sesión Segura
\App\Core\Session::start();

// 6. Configurar Enrutador
$router = new \App\Core\Router();

// -----------------------------------------------------------------------------
// Rutas Públicas y de Autenticación
// -----------------------------------------------------------------------------
$router->get('/', [\App\Controllers\HomeController::class, 'index']);

$router->get('/login', [\App\Controllers\AuthController::class, 'showLogin'], ['guest']);
$router->post('/login', [\App\Controllers\AuthController::class, 'login'], ['guest']);
$router->get('/register', [\App\Controllers\AuthController::class, 'showRegister'], ['guest']);
$router->post('/register', [\App\Controllers\AuthController::class, 'register'], ['guest']);
$router->get('/auth/google', [\App\Controllers\AuthController::class, 'oauthGoogle'], ['guest']);
$router->get('/auth/github', [\App\Controllers\AuthController::class, 'oauthGithub'], ['guest']);
$router->post('/logout', [\App\Controllers\AuthController::class, 'logout'], ['auth']);

// -----------------------------------------------------------------------------
// Dashboard y API Stats
// -----------------------------------------------------------------------------
$router->get('/dashboard', [\App\Controllers\DashboardController::class, 'index'], ['auth']);
$router->get('/api/stats', [\App\Controllers\DashboardController::class, 'stats'], ['auth']);

// -----------------------------------------------------------------------------
// Configuración de Perfil y Cuenta
// -----------------------------------------------------------------------------
$router->get('/profile', [\App\Controllers\ProfileController::class, 'index'], ['auth']);
$router->get('/settings', [\App\Controllers\ProfileController::class, 'index'], ['auth']);
$router->post('/profile/update', [\App\Controllers\ProfileController::class, 'update'], ['auth']);
$router->post('/profile/password', [\App\Controllers\ProfileController::class, 'updatePassword'], ['auth']);
$router->post('/profile/avatar', [\App\Controllers\ProfileController::class, 'updateAvatar'], ['auth']);
$router->post('/profile/avatar/remove', [\App\Controllers\ProfileController::class, 'removeAvatar'], ['auth']);

// -----------------------------------------------------------------------------
// Espacio del Desarrollador: /my-project
// -----------------------------------------------------------------------------
$router->get('/my-project', [\App\Controllers\MyProjectController::class, 'index'], ['auth', 'developer']);
$router->post('/my-project/milestone/{id}/complete', [\App\Controllers\MyProjectController::class, 'completeMilestone'], ['auth', 'developer']);
$router->post('/my-project/delivery/{milestoneId}', [\App\Controllers\MyProjectController::class, 'submitDelivery'], ['auth', 'developer']);
$router->post('/my-project/check-in', [\App\Controllers\MyProjectController::class, 'checkIn'], ['auth', 'developer']);
$router->post('/my-project/comment', [\App\Controllers\MyProjectController::class, 'addComment'], ['auth']);
$router->get('/deliveries/{deliveryId}/files/{fileIndex}', [\App\Controllers\DeliveryFileController::class, 'download'], ['auth']);

// -----------------------------------------------------------------------------
// Gestión de Proyectos
// -----------------------------------------------------------------------------
$router->get('/projects', [\App\Controllers\ProjectController::class, 'index'], ['auth']);
$router->get('/projects/create', [\App\Controllers\ProjectController::class, 'create'], ['auth', 'admin']);
$router->post('/projects', [\App\Controllers\ProjectController::class, 'store'], ['auth', 'admin']);
$router->get('/projects/{id}', [\App\Controllers\ProjectController::class, 'show'], ['auth']);
$router->get('/projects/{id}/edit', [\App\Controllers\ProjectController::class, 'edit'], ['auth', 'admin']);
$router->post('/projects/{id}/update', [\App\Controllers\ProjectController::class, 'update'], ['auth', 'admin']);
$router->post('/projects/{id}/submit', [\App\Controllers\ProjectController::class, 'submit'], ['auth']);
$router->get('/projects/{id}/wizard', [\App\Controllers\ProjectController::class, 'wizard'], ['auth']);
$router->post('/projects/{id}/delete', [\App\Controllers\ProjectController::class, 'destroy'], ['auth', 'admin']);

// -----------------------------------------------------------------------------
// Administración y Revisión Técnica
// -----------------------------------------------------------------------------
$router->get('/admin/review', [\App\Controllers\AdminController::class, 'review'], ['auth', 'admin']);
$router->post('/admin/review/{id}/status', [\App\Controllers\AdminController::class, 'updateStatus'], ['auth', 'admin']);
$router->post('/admin/projects/{id}/approve', [\App\Controllers\AdminController::class, 'approve'], ['auth', 'admin']);
$router->post('/admin/projects/{id}/reject', [\App\Controllers\AdminController::class, 'reject'], ['auth', 'admin']);
$router->post('/admin/projects/{id}/archive', [\App\Controllers\AdminController::class, 'archive'], ['auth', 'admin']);
$router->get('/admin/assignments', [\App\Controllers\AdminController::class, 'assignments'], ['auth', 'admin']);
$router->post('/admin/projects/{id}/reassign', [\App\Controllers\AdminController::class, 'reassign'], ['auth', 'admin']);
$router->post('/admin/projects/{id}/unassign', [\App\Controllers\AdminController::class, 'unassign'], ['auth', 'admin']);
$router->post('/admin/projects/{id}/delete', [\App\Controllers\ProjectController::class, 'destroy'], ['auth', 'admin']);
$router->get('/admin/evaluations', [\App\Controllers\AdminController::class, 'evaluations'], ['auth', 'admin']);
$router->post('/admin/evaluations/{deliveryId}/grade', [\App\Controllers\AdminController::class, 'gradeDelivery'], ['auth', 'admin']);
$router->get('/admin/quality-audits/create/{deliveryId}', [\App\Controllers\AdminController::class, 'createQualityAudit'], ['auth', 'admin']);
$router->post('/admin/quality-audits/{deliveryId}', [\App\Controllers\AdminController::class, 'storeQualityAudit'], ['auth', 'admin']);
$router->get('/admin/quality-audits/{auditId}', [\App\Controllers\AdminController::class, 'qualityAuditReport'], ['auth', 'admin']);

// -----------------------------------------------------------------------------
// Equipo, Talento y Gestión de Consultores / Programadores (CRUD Devioz)
// -----------------------------------------------------------------------------
$router->get('/team', [\App\Controllers\TeamController::class, 'index'], ['auth']);
$router->post('/team', [\App\Controllers\TeamController::class, 'store'], ['auth', 'admin']);
$router->post('/team/store', [\App\Controllers\TeamController::class, 'store'], ['auth', 'admin']);
$router->get('/api/team/{id}', [\App\Controllers\TeamController::class, 'showJson'], ['auth', 'admin']);
$router->post('/team/{id}/update', [\App\Controllers\TeamController::class, 'update'], ['auth', 'admin']);
$router->post('/team/{id}/role', [\App\Controllers\TeamController::class, 'updateRole'], ['auth', 'admin']);
$router->post('/team/{id}/delete', [\App\Controllers\TeamController::class, 'destroy'], ['auth', 'admin']);
$router->post('/team/{id}/restore', [\App\Controllers\TeamController::class, 'restore'], ['auth', 'admin']);
$router->get('/gallery', [\App\Controllers\GalleryController::class, 'index']);
$router->get('/gallery/{slug}', [\App\Controllers\GalleryController::class, 'show']);
$router->get('/reels', [\App\Controllers\HomeController::class, 'reels']);

// -----------------------------------------------------------------------------
// Dev Lounge (Comunidad de Desarrolladores e Intercambio de Ideas)
// -----------------------------------------------------------------------------
$router->get('/lounge', [\App\Controllers\LoungeController::class, 'index'], ['auth']);
$router->post('/lounge/store', [\App\Controllers\LoungeController::class, 'store'], ['auth']);
$router->post('/lounge/{id}/like', [\App\Controllers\LoungeController::class, 'like'], ['auth']);
$router->post('/lounge/{id}/react', [\App\Controllers\LoungeController::class, 'react'], ['auth']);
$router->post('/lounge/{id}/comments', [\App\Controllers\LoungeController::class, 'storeComment'], ['auth']);
$router->get('/lounge/preview-link', [\App\Controllers\LoungeController::class, 'previewLink'], ['auth']);

// -----------------------------------------------------------------------------
// APIs Internas: Búsqueda Global (Ctrl+K), Link Preview y Asistente Devioz
// -----------------------------------------------------------------------------
$router->get('/api/search', [\App\Controllers\ApiController::class, 'search']);
$router->get('/api/projects/{id}/preview', [\App\Controllers\ProjectController::class, 'previewApi'], ['auth']);
$router->post('/api/projects/{id}/copilot', [\App\Controllers\ProjectController::class, 'copilotApi'], ['auth']);
$router->get('/api/link-preview', [\App\Controllers\LinkPreviewController::class, 'preview'], ['auth']);
$router->post('/api/bot', [\App\Controllers\ApiController::class, 'bot'], ['auth']);
$router->post('/api/bot/feedback', [\App\Controllers\ApiController::class, 'botFeedback'], ['auth']);
$router->get('/api/bot/feedback/summary', [\App\Controllers\ApiController::class, 'botFeedbackSummary'], ['auth', 'admin']);

// -----------------------------------------------------------------------------
// Devioz Suite: Chat Directo 1 a 1, Interconexión/Llamadas y Notificaciones
// -----------------------------------------------------------------------------
$router->get('/chat', [\App\Controllers\ChatController::class, 'index'], ['auth']);
$router->get('/online', [\App\Controllers\ChatController::class, 'index'], ['auth']);
$router->get('/api/chat/conversation', [\App\Controllers\ChatController::class, 'conversation'], ['auth']);
$router->post('/api/chat/send', [\App\Controllers\ChatController::class, 'send'], ['auth', 'no-csrf']);
$router->post('/api/chat/request-call', [\App\Controllers\ChatController::class, 'requestCall'], ['auth', 'no-csrf']);
$router->get('/api/chat/stream', [\App\Controllers\ChatController::class, 'stream'], ['auth']);
$router->get('/api/notifications', [\App\Controllers\ChatController::class, 'notifications'], ['auth']);
$router->post('/api/notifications/mark-read', [\App\Controllers\ChatController::class, 'markRead'], ['auth', 'no-csrf']);

return $router; 
