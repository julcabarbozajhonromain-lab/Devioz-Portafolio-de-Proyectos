<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Devioz OS — Modelo de Reacciones Interactivas en Dev Lounge
 */
class LoungeReaction extends Model
{
    protected static string $table = 'lounge_reactions';

    public function post(): ?LoungePost
    {
        return $this->belongsTo(LoungePost::class, 'post_id');
    }

    public function user(): ?User
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
