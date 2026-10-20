<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\LoungeComment;
use App\Models\LoungePost;
use App\Models\LoungeReaction;
use App\Models\User;

/**
 * Devioz 24 — Controlador de Dev Lounge (Comunidad de Consultoría Tecnológica)
 * Intercambio de arquitectura cloud, data pipelines, DevOps, snippets, links enriquecidos y debate técnico.
 */
class LoungeController extends BaseController
{
    /**
     * Muestra el muro de la comunidad con filtros de categorías de consultoría
     */
    public function index(): string
    {
        $category = $_GET['category'] ?? 'all';
        $validCategories = [
            'cloud_devops',
            'data_analytics',
            'servers_infra',
            'software_dev',
            'tip',
            'idea',
            'win',
            'blocker'
        ];

        if (in_array($category, $validCategories, true)) {
            $posts = LoungePost::where('category', $category, '=', 'created_at DESC');
        } else {
            $posts = LoungePost::all('created_at DESC');
            $category = 'all';
        }

        // Estadísticas rápidas por categoría para los badges del feed
        $counts = [
            'all'            => LoungePost::count(),
            'cloud_devops'   => LoungePost::count("category = 'cloud_devops'"),
            'data_analytics' => LoungePost::count("category = 'data_analytics'"),
            'servers_infra'  => LoungePost::count("category = 'servers_infra'"),
            'software_dev'   => LoungePost::count("category = 'software_dev'"),
            'tip'            => LoungePost::count("category = 'tip'"),
            'idea'           => LoungePost::count("category = 'idea'"),
            'win'            => LoungePost::count("category = 'win'"),
            'blocker'        => LoungePost::count("category = 'blocker'"),
        ];

        $currentUserId = $this->auth->id() ?? 0;

        return $this->view('lounge/index', [
            'title'           => 'Dev Lounge — Comunidad de Consultoría Devioz',
            'posts'           => $posts,
            'currentCategory' => $category,
            'counts'          => $counts,
            'currentUserId'   => $currentUserId,
        ]);
    }

    /**
     * Guarda una nueva publicación en el Dev Lounge (con soporte para Enlaces, Tags y Snippets)
     */
    public function store(): never
    {
        $userId = $this->auth->id();
        if (!$userId) {
            flash('error', 'Debes iniciar sesión para publicar en el Lounge.');
            redirect('/login');
        }

        $validator = $this->validate($_POST, [
            'title'        => 'required|min:4|max:255',
            'category'     => 'required',
            'content'      => 'required|min:10',
            'code_snippet' => 'max:5000',
        ]);

        if ($validator->fails()) {
            redirect('/lounge');
        }

        $linkUrl = !empty($_POST['link_url']) ? trim($_POST['link_url']) : null;
        $linkTitle = !empty($_POST['link_title']) ? sanitize($_POST['link_title']) : null;
        $linkDesc = !empty($_POST['link_description']) ? sanitize($_POST['link_description']) : null;
        $linkImage = !empty($_POST['link_image']) ? trim($_POST['link_image']) : null;
        $linkDomain = !empty($_POST['link_domain']) ? sanitize($_POST['link_domain']) : null;

        // Auto-enriquecer el enlace si el usuario no proporcionó metadatos manuales
        if ($linkUrl && filter_var($linkUrl, FILTER_VALIDATE_URL) && (!$linkTitle || !$linkImage)) {
            $parsedMeta = $this->extractLinkMetadata($linkUrl);
            $linkTitle = $linkTitle ?: $parsedMeta['title'];
            $linkDesc = $linkDesc ?: $parsedMeta['description'];
            $linkImage = $linkImage ?: $parsedMeta['image'];
            $linkDomain = $linkDomain ?: $parsedMeta['domain'];
        }

        LoungePost::create([
            'user_id'          => $userId,
            'category'         => sanitize($_POST['category']),
            'tech_tags'        => !empty($_POST['tech_tags']) ? sanitize($_POST['tech_tags']) : null,
            'title'            => sanitize($_POST['title']),
            'content'          => trim($_POST['content']),
            'code_snippet'     => !empty($_POST['code_snippet']) ? trim($_POST['code_snippet']) : null,
            'link_url'         => $linkUrl,
            'link_title'       => $linkTitle,
            'link_description' => $linkDesc,
            'link_image'       => $linkImage,
            'link_domain'      => $linkDomain,
            'likes_count'      => 0,
        ]);

        flash('success', '¡Tu publicación y enlace han sido compartidos con la comunidad!');
        redirect('/lounge');
    }

    /**
     * Endpoint API para vista previa en tiempo real de URLs compartidas (estilo WhatsApp / Redes Sociales)
     */
    public function previewLink(): never
    {
        $url = trim($_GET['url'] ?? '');
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            $this->json(['success' => false, 'message' => 'URL inválida o vacía.'], 400);
        }

        $meta = $this->extractLinkMetadata($url);
        $this->json(['success' => true, 'preview' => $meta]);
    }

    /**
     * Reacciones interactivas (Like, Rocket, Idea, Fire, Heart) con alternancia y conteo en tiempo real
     */
    public function react(int|string $id): never
    {
        $userId = $this->auth->id();
        if (!$userId) {
            $this->json(['success' => false, 'message' => 'Debes iniciar sesión para reaccionar.'], 401);
        }

        $post = LoungePost::find((int)$id);
        if (!$post) {
            $this->json(['success' => false, 'message' => 'Publicación no encontrada.'], 404);
        }

        // Obtener tipo de reacción solicitada
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $reactionType = $input['type'] ?? 'like';
        $validTypes = ['like', 'rocket', 'idea', 'fire', 'heart'];
        if (!in_array($reactionType, $validTypes, true)) {
            $reactionType = 'like';
        }

        $db = \App\Core\Database::getInstance();
        $existing = $db->fetch(
            "SELECT id, reaction_type FROM `lounge_reactions` WHERE `post_id` = :pid AND `user_id` = :uid LIMIT 1",
            ['pid' => (int)$post->id, 'uid' => $userId]
        );

        $currentUserReaction = null;

        if ($existing) {
            if ($existing['reaction_type'] === $reactionType) {
                // Alternar (toggle off) si presionó la misma reacción
                $db->query("DELETE FROM `lounge_reactions` WHERE `id` = :id", ['id' => $existing['id']]);
                $currentUserReaction = null;
            } else {
                // Cambiar a la nueva reacción
                $db->update('lounge_reactions', ['reaction_type' => $reactionType], '`id` = :id', ['id' => $existing['id']]);
                $currentUserReaction = $reactionType;
            }
        } else {
            // Nueva reacción
            $db->insert('lounge_reactions', [
                'post_id'       => (int)$post->id,
                'user_id'       => $userId,
                'reaction_type' => $reactionType,
            ]);
            $currentUserReaction = $reactionType;
        }

        // Obtener resumen actualizado
        $summary = $post->reactionsSummary();
        $total = $post->totalReactions();
        $post->update(['likes_count' => $total]);

        $this->json([
            'success'              => true,
            'summary'              => $summary,
            'total'                => $total,
            'current_user_reaction'=> $currentUserReaction,
        ]);
    }

    /**
     * Mantiene retrocompatibilidad con endpoint like
     */
    public function like(int|string $id): never
    {
        $this->react($id);
    }

    /**
     * Guarda un comentario en el hilo de la publicación (soporta AJAX o Form Submit)
     */
    public function storeComment(int|string $id): never
    {
        $userId = $this->auth->id();
        if (!$userId) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Debes iniciar sesión para comentar.'], 401);
            }
            redirect('/login');
        }

        $post = LoungePost::find((int)$id);
        if (!$post) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Publicación no encontrada.'], 404);
            }
            redirect('/lounge');
        }

        $content = trim($_POST['content'] ?? '');
        if (mb_strlen($content) < 2) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'El comentario es muy corto.'], 422);
            }
            flash('error', 'El comentario no puede estar vacío.');
            redirect("/lounge#post-{$post->id}");
        }

        $comment = LoungeComment::create([
            'post_id' => (int)$post->id,
            'user_id' => $userId,
            'content' => sanitize($content),
        ]);

        $user = $comment ? $comment->user() : null;

        if ($this->isAjax()) {
            $this->json([
                'success'        => true,
                'comments_count' => $post->commentsCount(),
                'comment'        => [
                    'id'          => $comment->id,
                    'user_name'   => $user ? $user->name : 'Usuario',
                    'user_avatar' => $user ? $user->avatarUrl() : '',
                    'user_role'   => $user && $user->isAdmin() ? 'Administrador' : 'Desarrollador',
                    'user_sede'   => $user ? ($user->sede ?: '') : '',
                    'content'     => e($comment->content),
                    'created_at'  => 'Ahora mismo',
                ]
            ]);
        }

        flash('success', 'Comentario agregado al debate técnico.');
        redirect("/lounge#post-{$post->id}");
    }

    /**
     * Helper para extraer metadatos OpenGraph de enlaces
     */
    private function extractLinkMetadata(string $url): array
    {
        $parsed = parse_url($url);
        $host = strtolower($parsed['host'] ?? '');
        $domain = preg_replace('/^www\./', '', $host);

        $result = [
            'url'         => $url,
            'domain'      => $domain ?: 'web',
            'title'       => '',
            'description' => '',
            'image'       => '',
            'is_youtube'  => false,
            'youtube_id'  => null,
        ];

        // 1. Detección de YouTube
        if (str_contains($host, 'youtube.com') || str_contains($host, 'youtu.be')) {
            if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=|shorts\/))([\w-]{11})/', $url, $m)) {
                $ytId = $m[1];
                $result['is_youtube'] = true;
                $result['youtube_id'] = $ytId;
                $result['title'] = 'Video Técnico en YouTube';
                $result['description'] = 'Demostración de software o tutorial de arquitectura en video.';
                $result['image'] = "https://img.youtube.com/vi/{$ytId}/hqdefault.jpg";
                return $result;
            }
        }

        // 2. Detección de GitHub
        if (str_contains($host, 'github.com')) {
            $path = trim($parsed['path'] ?? '', '/');
            $parts = explode('/', $path);
            if (count($parts) >= 2) {
                $repo = "{$parts[0]}/{$parts[1]}";
                $result['title'] = "GitHub: {$repo}";
                $result['description'] = "Repositorio oficial de código abierto y arquitectura de software.";
                $result['image'] = "https://opengraph.githubassets.com/1/{$repo}";
                return $result;
            }
        }

        // 3. Páginas web generales (OpenGraph scraping con timeout de 2s)
        try {
            $context = stream_context_create([
                'http' => [
                    'timeout'         => 2.0,
                    'user_agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'follow_location' => 1,
                    'max_redirects'   => 2,
                ]
            ]);
            $html = @file_get_contents($url, false, $context, 0, 45000);
            if ($html) {
                if (preg_match('/<meta[^>]*property=["\']og:title["\'][^>]*content=["\']([^"\']+)["\']/i', $html, $m)) {
                    $result['title'] = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                } elseif (preg_match('/<title[^>]*>([^<]+)<\/title>/i', $html, $m)) {
                    $result['title'] = html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                }

                if (preg_match('/<meta[^>]*property=["\']og:description["\'][^>]*content=["\']([^"\']+)["\']/i', $html, $m)) {
                    $result['description'] = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                } elseif (preg_match('/<meta[^>]*name=["\']description["\'][^>]*content=["\']([^"\']+)["\']/i', $html, $m)) {
                    $result['description'] = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                }

                if (preg_match('/<meta[^>]*property=["\']og:image["\'][^>]*content=["\']([^"\']+)["\']/i', $html, $m)) {
                    $img = $m[1];
                    if (!preg_match('/^https?:\/\//i', $img)) {
                        $img = rtrim($url, '/') . '/' . ltrim($img, '/');
                    }
                    $result['image'] = $img;
                }
            }
        } catch (\Throwable $e) {
            // Ignorar silenciosamente
        }

        // Título y preview fallback
        if (empty($result['title'])) {
            $result['title'] = "Recurso compartido de " . ucfirst($domain);
        }
        if (empty($result['image'])) {
            $result['image'] = 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?w=600&auto=format&fit=crop&q=80';
        }

        return $result;
    }

    private function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
