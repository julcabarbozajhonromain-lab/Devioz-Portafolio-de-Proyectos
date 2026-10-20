<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Devioz 24 — Motor Ligero de Plantillas y Vistas (View Engine)
 * Soporta layouts anidados, buffers de salida y partials sin dependencias externas.
 */
final class View
{
    private static string $viewsPath;

    public static function init(): void
    {
        self::$viewsPath = DEVIOZ_VIEWS;
    }

    /**
     * Renderiza una vista dentro de un layout especificado.
     *
     * @param string $view Nombre de la vista relativo a views/ (ej: 'dashboard/index')
     * @param array $data Variables que se expondrán en la vista
     * @param string|null $layout Nombre del layout en views/layouts/ ('app', 'auth', o null)
     */
    public static function render(string $view, array $data = [], ?string $layout = 'app'): string
    {
        self::init();

        $viewFile = self::$viewsPath . '/' . ltrim($view, '/') . '.php';

        if (!file_exists($viewFile)) {
            throw new RuntimeException("La vista especificada no existe: {$viewFile}");
        }

        // Renderizar el contenido de la vista
        $content = self::renderTemplate($viewFile, $data);

        // Si no se definió layout, retornar el contenido directamente
        if ($layout === null || $layout === '') {
            return $content;
        }

        $layoutFile = self::$viewsPath . "/layouts/{$layout}.php";
        if (!file_exists($layoutFile)) {
            throw new RuntimeException("El layout especificado no existe: {$layoutFile}");
        }

        // Exponer $content y la data global al layout
        $layoutData = array_merge($data, ['content' => $content]);
        return self::renderTemplate($layoutFile, $layoutData);
    }

    /**
     * Incluye un archivo parcial (ej: 'partials/navbar', 'partials/flash')
     */
    public static function partial(string $partial, array $data = []): string
    {
        self::init();
        $partialFile = self::$viewsPath . '/' . ltrim($partial, '/') . '.php';

        if (!file_exists($partialFile)) {
            return "<!-- Parcial no encontrado: {$partial} -->";
        }

        return self::renderTemplate($partialFile, $data);
    }

    /**
     * Aísla la ejecución del template en un scope limpio y captura el buffer.
     */
    private static function renderTemplate(string $filePath, array $data): string
    {
        extract($data, EXTR_SKIP);

        ob_start();
        try {
            include $filePath;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        return ob_get_clean() ?: '';
    }

    /**
     * Renderiza una pantalla de error HTTP (403, 404, 500).
     */
    public static function renderError(int $code, string $message = ''): string
    {
        http_response_code($code);
        self::init();

        $errorView = self::$viewsPath . "/errors/{$code}.php";
        if (file_exists($errorView)) {
            return self::render("errors/{$code}", ['code' => $code, 'message' => $message], 'app');
        }

        // Fallback estilizado si la vista de error no existe aún
        $defaultMsg = match ($code) {
            403 => 'Acceso denegado. No posees los privilegios necesarios.',
            404 => 'El recurso o página solicitada no fue encontrada.',
            default => 'Ocurrió un error inesperado en el servidor.',
        };
        $displayMsg = !empty($message) ? $message : $defaultMsg;

        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error {$code} — Devioz 24</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #EEF2F4; color: #1F2937; font-family: 'Open Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif; display: grid; place-items: center; min-height: 100vh; }
        .card-error { background: #ffffff; border: 1px solid #DBE2E8; border-radius: 12px; box-shadow: 0 1px 3px rgba(31, 41, 55, 0.05); max-width: 500px; padding: 2.5rem; text-align: center; }
        .btn-brand { background: #10B981; color: #fff; border: none; border-radius: 8px; font-weight: 600; }
        .btn-brand:hover { background: #059669; color: #fff; }
    </style>
</head>
<body>
    <div class="card-error shadow-sm">
        <h1 class="display-3 fw-bold text-danger">{$code}</h1>
        <h4 class="mb-3" style="color: #1F2937;">Atención</h4>
        <p class="mb-4" style="color: #626F86;">{$displayMsg}</p>
        <a href="/" class="btn btn-brand px-4 py-2">Volver al Inicio</a>
    </div>
</body>
</html>
HTML;
    }
}
