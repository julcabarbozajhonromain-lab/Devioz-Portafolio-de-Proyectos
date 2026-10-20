<?php

declare(strict_types=1);

namespace App\Controllers;

/**
 * Devioz 24 — Controlador para Previsualización Automática de Enlaces (Open Graph)
 */
class LinkPreviewController extends BaseController
{
    public function preview(): never
    {
        $url = trim((string)($_GET['url'] ?? ''));

        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            $this->json([
                'success' => false,
                'message' => 'URL inválida o no provista.',
            ], 400);
        }

        // Restricción de esquemas permitidos
        $scheme = strtolower((string)parse_url($url, PHP_URL_SCHEME));
        if (!in_array($scheme, ['http', 'https'], true)) {
            $this->json([
                'success' => false,
                'message' => 'Solo se permiten protocolos HTTP y HTTPS.',
            ], 400);
        }

        // Host parse
        $host = (string)parse_url($url, PHP_URL_HOST);

        // Prevenir SSRF en localhost o IPs privadas
        if (in_array(strtolower($host), ['localhost', '127.0.0.1', '::1', '0.0.0.0'], true) || preg_match('/^192\.168\.|^10\.|^172\.(1[6-9]|2[0-9]|3[0-1])\./', $host)) {
            $this->json([
                'success' => false,
                'message' => 'No se permite previsualizar recursos locales internos.',
            ], 403);
        }

        $meta = $this->fetchOpenGraph($url);

        $this->json([
            'success'     => true,
            'url'         => $url,
            'title'       => $meta['title'],
            'description' => $meta['description'],
            'image'       => $meta['image'],
            'site_name'   => $meta['site_name'],
        ]);
    }

    private function fetchOpenGraph(string $url): array
    {
        $parsedHost = parse_url($url, PHP_URL_HOST) ?: 'Devioz';
        $siteName = preg_replace('/^www\./i', '', (string)$parsedHost);

        // Metadata por defecto basada en dominio conocido
        if (str_contains($parsedHost, 'github.com')) {
            $siteName = 'GitHub';
        } elseif (str_contains($parsedHost, 'youtube.com') || str_contains($parsedHost, 'youtu.be')) {
            $siteName = 'YouTube';
        } elseif (str_contains($parsedHost, 'notion.so') || str_contains($parsedHost, 'notion.site')) {
            $siteName = 'Notion';
        } elseif (str_contains($parsedHost, 'gitlab.com')) {
            $siteName = 'GitLab';
        }

        $result = [
            'title'       => $siteName . ' — Enlace de Proyecto',
            'description' => 'Recurso técnico vinculado al repositorio o despliegue del proyecto.',
            'image'       => '',
            'site_name'   => $siteName,
        ];

        // Intentar obtener HTML mediante cURL con timeout corto (2.5s)
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Devioz-Bot/3.0 (+https://devioz.org)');
            // Limitar descarga a primeros 80 KB para velocidad máxima
            curl_setopt($ch, CURLOPT_RANGE, '0-81920');

            $html = curl_exec($ch);
            curl_close($ch);

            if ($html && is_string($html)) {
                // Parsear og:title o <title>
                if (preg_match('/<meta\s+property=["\']og:title["\']\s+content=["\'](.*?)["\']/i', $html, $m)) {
                    $result['title'] = html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                } elseif (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $m)) {
                    $result['title'] = html_entity_decode(trim(strip_tags($m[1])), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                }

                // Parsear og:description o meta description
                if (preg_match('/<meta\s+property=["\']og:description["\']\s+content=["\'](.*?)["\']/i', $html, $m)) {
                    $result['description'] = html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                } elseif (preg_match('/<meta\s+name=["\']description["\']\s+content=["\'](.*?)["\']/i', $html, $m)) {
                    $result['description'] = html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                }

                // Parsear og:image
                if (preg_match('/<meta\s+property=["\']og:image["\']\s+content=["\'](.*?)["\']/i', $html, $m)) {
                    $img = trim($m[1]);
                    if (str_starts_with($img, '//')) {
                        $img = 'https:' . $img;
                    } elseif (!str_starts_with($img, 'http')) {
                        $img = rtrim($url, '/') . '/' . ltrim($img, '/');
                    }
                    $result['image'] = $img;
                }

                // Parsear og:site_name
                if (preg_match('/<meta\s+property=["\']og:site_name["\']\s+content=["\'](.*?)["\']/i', $html, $m)) {
                    $result['site_name'] = html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                }
            }
        }

        return $result;
    }
}

