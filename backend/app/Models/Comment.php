<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Devioz OS — Modelo de Comentario e Hilos de Proyecto
 */
class Comment extends Model
{
    protected static string $table = 'comments';

    public function project(): ?Project
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function user(): ?User
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
