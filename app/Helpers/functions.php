<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Session;

/**
 * Devioz OS — Funciones Helper Globales
 */

if (!function_exists('e')) {
    /**
     * Escapa caracteres especiales HTML para prevenir Cross-Site Scripting (XSS).
     */
    function e(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('old')) {
    /**
     * Recupera el valor previo de un campo enviado en formulario.
     */
    function old(string $key, mixed $default = ''): mixed
    {
        return Session::getFlash('old_' . $key) ?? $default;
    }
}

if (!function_exists('config')) {
    /**
     * Obtiene una clave de configuración cargada en config/.
     * Ejemplo: config('app.name'), config('database.driver')
     */
    function config(string $key, mixed $default = null): mixed
    {
        static $configs = [];
        
        $parts = explode('.', $key);
        $file = $parts[0];
        
        if (!isset($configs[$file])) {
            $filePath = dirname(__DIR__, 2) . "/config/{$file}.php";
            if (file_exists($filePath)) {
                $configs[$file] = require $filePath;
            } else {
                $configs[$file] = [];
            }
        }
        
        $data = $configs[$file];
        for ($i = 1; $i < count($parts); $i++) {
            if (!is_array($data) || !array_key_exists($parts[$i], $data)) {
                return $default;
            }
            $data = $data[$parts[$i]];
        }
        
        return $data;
    }
}

if (!function_exists('url')) {
    /**
     * Genera una URL completa y canónica para la aplicación.
     */
    function url(string $path = ''): string
    {
        $baseUrl = rtrim((string)config('app.url', 'http://localhost/Devioz-Portafolio-de-Proyectos/public'), '/');
        $cleanPath = ltrim($path, '/');
        
        return empty($cleanPath) ? $baseUrl : "{$baseUrl}/{$cleanPath}";
    }
}

if (!function_exists('asset')) {
    /**
     * Genera la URL de un asset público con cache-busting automático basado en filemtime.
     */
    function asset(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '//') || str_starts_with($path, 'data:')) {
            return $path;
        }

        $cleanPath = ltrim($path, '/');
        $fullPath = dirname(__DIR__, 2) . "/public/{$cleanPath}";
        $version = file_exists($fullPath) ? (string)filemtime($fullPath) : '1';
        
        return url($cleanPath) . "?v={$version}";
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Retorna el token CSRF activo de la sesión.
     */
    function csrf_token(): string
    {
        return Session::csrf();
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Genera el input hidden con el token CSRF para formularios POST/PUT/DELETE.
     */
    function csrf_field(): string
    {
        $token = csrf_token();
        return '<input type="hidden" name="_csrf_token" value="' . e($token) . '">';
    }
}

if (!function_exists('method_field')) {
    /**
     * Genera el input hidden de spoofing de método HTTP (_method).
     */
    function method_field(string $method): string
    {
        return '<input type="hidden" name="_method" value="' . e(strtoupper($method)) . '">';
    }
}

if (!function_exists('auth')) {
    /**
     * Retorna la instancia o helper de autenticación.
     */
    function auth(): Auth
    {
        return Auth::getInstance();
    }
}

if (!function_exists('session')) {
    /**
     * Retorna la clase Session o interactúa con valores.
     */
    function session(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return Session::class;
        }
        return Session::get($key, $default);
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirige a una URL interna o externa y finaliza la ejecución.
     */
    function redirect(string $path, int $status = 302): never
    {
        $destination = (str_starts_with($path, 'http://') || str_starts_with($path, 'https://'))
            ? $path
            : url($path);

        header("Location: {$destination}", true, $status);
        exit;
    }
}

if (!function_exists('json_response')) {
    /**
     * Emite una respuesta en formato JSON con cabeceras y código de estado.
     */
    function json_response(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}

if (!function_exists('format_date')) {
    /**
     * Formatea una fecha o timestamp a formato legible.
     */
    function format_date(?string $date, string $format = 'd/m/Y'): string
    {
        if (!$date) {
            return '—';
        }
        try {
            $dt = new DateTime($date);
            return $dt->format($format);
        } catch (Throwable) {
            return (string)$date;
        }
    }
}

if (!function_exists('human_size')) {
    /**
     * Convierte bytes en unidades legibles (KB, MB, GB).
     */
    function human_size(int $bytes, int $decimals = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = floor((strlen((string)$bytes) - 1) / 3);
        $factor = min($factor, count($units) - 1);
        
        return sprintf("%.{$decimals}f %s", $bytes / (1024 ** $factor), $units[$factor]);
    }
}

if (!function_exists('getYoutubeId')) {
    /**
     * Extrae el identificador de 11 caracteres de cualquier URL de YouTube.
     */
    function getYoutubeId(string $url): ?string
    {
        if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=|shorts\/))([\w-]{11})/', $url, $m)) {
            return $m[1];
        }
        return null;
    }
}

if (!function_exists('getEmbedUrl')) {
    /**
     * Retorna la URL de incrustación (iframe embed) adecuada para YouTube, Loom o Vimeo.
     */
    function getEmbedUrl(string $url, bool $autoplay = false): string
    {
        $ytId = getYoutubeId($url);
        if ($ytId) {
            $auto = $autoplay ? '&autoplay=1' : '';
            return "https://www.youtube-nocookie.com/embed/{$ytId}?rel=0&modestbranding=1&iv_load_policy=3&playsinline=1{$auto}";
        }
        if (preg_match('/loom\.com\/(?:share|embed)\/([a-zA-Z0-9]+)/', $url, $m)) {
            $auto = $autoplay ? '?autoplay=1' : '';
            return "https://www.loom.com/embed/{$m[1]}{$auto}";
        }
        if (preg_match('/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|)(\d+)/', $url, $m)) {
            $auto = $autoplay ? '?autoplay=1' : '';
            return "https://player.vimeo.com/video/{$m[3]}{$auto}";
        }
        return $url;
    }
}

if (!function_exists('getReelThumbnail')) {
    /**
     * Retorna la miniatura para un reel/video (YouTube thumbnail, imagen de portada o logotipo por defecto).
     */
    function getReelThumbnail(string $url, ?string $fallbackCover = null): string
    {
        $ytId = getYoutubeId($url);
        if ($ytId) {
            return "https://img.youtube.com/vi/{$ytId}/hqdefault.jpg";
        }
        if ($fallbackCover && !empty($fallbackCover)) {
            return $fallbackCover;
        }
        return asset('images/devioz-logo.webp');
    }
}

if (!function_exists('cache')) {
    /**
     * Helper de acceso a la caché de Devioz OS.
     */
    function cache(?string $key = null, mixed $value = null, int $ttl = 3600): mixed
    {
        if ($key === null) {
            return \App\Core\Cache::class;
        }
        if ($value !== null) {
            return \App\Core\Cache::set($key, $value, $ttl);
        }
        return \App\Core\Cache::get($key);
    }
}

if (!function_exists('render_markdown')) {
    /**
     * Renderiza texto plano y formato Markdown a HTML seguro con formateo de bloques de código.
     */
    function render_markdown(?string $text): string
    {
        if (empty($text)) {
            return '';
        }

        // 1. Extraer y proteger bloques de código cercados ```lang ... ```
        $codeBlocks = [];
        $text = preg_replace_callback('/```([a-zA-Z0-9_-]*)\r?\n([\s\S]*?)```/', function ($matches) use (&$codeBlocks) {
            $key = '%%CODEBLOCK_' . count($codeBlocks) . '%%';
            $lang = htmlspecialchars(trim($matches[1]), ENT_QUOTES, 'UTF-8');
            $code = htmlspecialchars($matches[2], ENT_QUOTES, 'UTF-8');
            $codeBlocks[$key] = '<pre class="code-block"><code' . ($lang ? ' class="language-' . $lang . '"' : '') . '>' . $code . '</code></pre>';
            return $key;
        }, $text);

        // 2. Extraer y proteger código inline `...`
        $inlineCodes = [];
        $text = preg_replace_callback('/`([^`\n]+)`/', function ($matches) use (&$inlineCodes) {
            $key = '%%INLINECODE_' . count($inlineCodes) . '%%';
            $code = htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8');
            $inlineCodes[$key] = '<code>' . $code . '</code>';
            return $key;
        }, $text);

        // 3. Escapar el resto del texto para prevenir XSS
        $text = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        // 4. Encabezados
        $text = preg_replace('/^### (.*?)$/m', '<h3>$1</h3>', $text);
        $text = preg_replace('/^## (.*?)$/m', '<h2>$1</h2>', $text);
        $text = preg_replace('/^# (.*?)$/m', '<h1>$1</h1>', $text);

        // 5. Negritas y Cursivas
        $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
        $text = preg_replace('/\*([^\*\n]+)\*/', '<em>$1</em>', $text);

        // 6. Citas / Blockquotes
        $text = preg_replace('/^&gt; (.*?)$/m', '<blockquote>$1</blockquote>', $text);

        // 7. Enlaces [texto](url)
        $text = preg_replace('/\[(.*?)\]\((https?:\/\/[^\s\)]+)\)/', '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>', $text);

        // 8. Listas no ordenadas (- item o * item)
        $text = preg_replace_callback('/(?:^\s*[-*]\s+(.*?)(?:\r?\n|$))+/m', function ($matches) {
            $items = preg_split('/\r?\n/', trim($matches[0]));
            $li = '';
            foreach ($items as $item) {
                $clean = preg_replace('/^\s*[-*]\s+/', '', $item);
                if ($clean !== '') {
                    $li .= '<li>' . $clean . '</li>';
                }
            }
            return '<ul>' . $li . '</ul>';
        }, $text);

        // 9. Párrafos y saltos de línea (respetando bloques)
        $lines = preg_split('/\r?\n\r?\n/', $text);
        $parsed = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;
            if (preg_match('/^<(?:h[1-6]|ul|ol|blockquote|div|pre)/', $line) || str_starts_with($line, '%%CODEBLOCK_')) {
                $parsed[] = $line;
            } else {
                $parsed[] = '<p>' . nl2br($line) . '</p>';
            }
        }
        $html = implode("\n", $parsed);

        // 10. Restaurar bloques de código y código inline
        foreach ($codeBlocks as $key => $block) {
            $html = str_replace($key, $block, $html);
        }
        foreach ($inlineCodes as $key => $code) {
            $html = str_replace($key, $code, $html);
        }

        return '<div class="markdown-content">' . $html . '</div>';
    }
}

if (!function_exists('extract_tech_stack')) {
    /**
     * Extrae automáticamente un array de tecnologías del texto de la descripción del proyecto.
     */
    function extract_tech_stack(string $text): array
    {
        if (empty($text)) {
            return [];
        }

        $keywords = [
            'PHP 8.2', 'PHP', 'Laravel', 'Symfony', 'CodeIgniter',
            'React', 'React.js', 'Next.js', 'Vue.js', 'Vue', 'Angular',
            'Node.js', 'Express', 'TypeScript', 'JavaScript', 'HTML5', 'CSS3',
            'Bootstrap 5', 'Bootstrap', 'Tailwind CSS', 'Tailwind',
            'Python', 'Django', 'Flask', 'FastAPI',
            'Java', 'Spring Boot', 'Spring', 'Kotlin',
            'C#', '.NET', 'ASP.NET',
            'MySQL 8.0', 'MySQL', 'PostgreSQL', 'SQLite', 'MongoDB', 'Redis',
            'Docker', 'Kubernetes', 'AWS', 'Azure', 'GCP', 'Google Cloud',
            'REST API', 'GraphQL', 'Microservicios', 'Git', 'GitHub', 'CI/CD'
        ];

        $found = [];
        foreach ($keywords as $kw) {
            $pattern = '/\b' . preg_quote($kw, '/') . '\b/i';
            if (preg_match($pattern, $text)) {
                $found[] = $kw;
            }
        }

        if (in_array('PHP 8.2', $found, true) && ($key = array_search('PHP', $found, true)) !== false) {
            unset($found[$key]);
        }
        if (in_array('Bootstrap 5', $found, true) && ($key = array_search('Bootstrap', $found, true)) !== false) {
            unset($found[$key]);
        }
        if (in_array('MySQL 8.0', $found, true) && ($key = array_search('MySQL', $found, true)) !== false) {
            unset($found[$key]);
        }

        return array_values(array_unique($found));
    }
}


