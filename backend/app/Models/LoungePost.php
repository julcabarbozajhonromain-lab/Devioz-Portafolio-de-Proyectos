<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Devioz 24 — Modelo de Publicación en Dev Lounge (Muro de Comunidad de Consultoría)
 */
class LoungePost extends Model
{
    protected static string $table = 'lounge_posts';

    protected array $casts = [
        'likes_count' => 'int',
    ];

    public function user(): ?User
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Retorna los comentarios asociados a la publicación ordenados cronológicamente
     * @return LoungeComment[]
     */
    public function comments(): array
    {
        return LoungeComment::where('post_id', (int)$this->id, '=', 'created_at ASC');
    }

    /**
     * Retorna la cantidad de comentarios
     */
    public function commentsCount(): int
    {
        $db = self::getDb();
        $row = $db->fetch("SELECT COUNT(*) as c FROM `lounge_comments` WHERE `post_id` = :pid", ['pid' => (int)$this->id]);
        return (int)($row['c'] ?? 0);
    }

    /**
     * Retorna el conteo agrupado por tipo de reacción
     */
    public function reactionsSummary(): array
    {
        $db = self::getDb();
        $rows = $db->fetchAll(
            "SELECT `reaction_type`, COUNT(*) as count 
             FROM `lounge_reactions` 
             WHERE `post_id` = :pid 
             GROUP BY `reaction_type`",
            ['pid' => (int)$this->id]
        );

        $summary = [
            'like'   => 0,
            'rocket' => 0,
            'idea'   => 0,
            'fire'   => 0,
            'heart'  => 0,
        ];

        foreach ($rows as $row) {
            $type = $row['reaction_type'];
            if (isset($summary[$type])) {
                $summary[$type] = (int)$row['count'];
            }
        }

        return $summary;
    }

    /**
     * Retorna el número total de reacciones
     */
    public function totalReactions(): int
    {
        $db = self::getDb();
        $row = $db->fetch("SELECT COUNT(*) as c FROM `lounge_reactions` WHERE `post_id` = :pid", ['pid' => (int)$this->id]);
        $reactionsCount = (int)($row['c'] ?? 0);
        return max($reactionsCount, (int)$this->likes_count);
    }

    /**
     * Retorna la reacción del usuario activo si existe
     */
    public function userReaction(int $userId): ?string
    {
        if ($userId <= 0) return null;
        $db = self::getDb();
        $row = $db->fetch(
            "SELECT `reaction_type` FROM `lounge_reactions` WHERE `post_id` = :pid AND `user_id` = :uid LIMIT 1",
            ['pid' => (int)$this->id, 'uid' => $userId]
        );
        return $row ? (string)$row['reaction_type'] : null;
    }

    /**
     * Badge visual según categoría técnica de consultoría
     */
    public function categoryBadge(): string
    {
        return match ($this->category) {
            'cloud_devops'   => '<span class="badge" style="background: rgba(14, 165, 233, 0.15); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.35);">Cloud & DevOps</span>',
            'data_analytics' => '<span class="badge" style="background: rgba(168, 85, 247, 0.15); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.35);">Data & Analytics</span>',
            'servers_infra'  => '<span class="badge" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35);">Servidores & Infra</span>',
            'software_dev'   => '<span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35);">Desarrollo & Software</span>',
            'tip'            => '<span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35);">Tip Técnico</span>',
            'idea'           => '<span class="badge" style="background: rgba(168, 85, 247, 0.15); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.35);">Idea / Arquitectura</span>',
            'blocker'        => '<span class="badge whitespace-nowrap" style="background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.35); white-space: nowrap;">Consultas & Escalabilidad</span>',
            'win'            => '<span class="badge whitespace-nowrap" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35); white-space: nowrap;">Logro / Avance</span>',
            default          => '<span class="badge bg-secondary whitespace-nowrap" style="white-space: nowrap;">Consultoría Devioz</span>',
        };
    }

    /**
     * Retorna array de tags tecnológicos
     */
    public function tagsArray(): array
    {
        if (empty($this->tech_tags)) return [];
        $tags = explode(',', $this->tech_tags);
        return array_filter(array_map('trim', $tags));
    }

    /**
     * Determina si tiene vista previa de enlace activa
     */
    public function hasLinkPreview(): bool
    {
        return !empty($this->link_url);
    }

    /**
     * Extrae ID de YouTube si aplica
     */
    public function youTubeId(): ?string
    {
        if (empty($this->link_url)) return null;
        if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=|shorts\/))([\w-]{11})/', $this->link_url, $m)) {
            return $m[1];
        }
        return null;
    }
}
