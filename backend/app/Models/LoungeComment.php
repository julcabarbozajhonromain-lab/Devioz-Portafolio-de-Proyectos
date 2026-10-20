<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Devioz 24 — Modelo de Comentarios en Publicaciones del Dev Lounge
 */
class LoungeComment extends Model
{
    protected static string $table = 'lounge_comments';

    public function post(): ?LoungePost
    {
        return $this->belongsTo(LoungePost::class, 'post_id');
    }

    public function user(): ?User
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
