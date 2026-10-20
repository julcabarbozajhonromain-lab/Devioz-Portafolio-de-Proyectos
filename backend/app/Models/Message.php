<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Devioz 24 — Modelo de Mensajería Interna y Notificaciones
 */
class Message extends Model
{
    protected static string $table = 'messages';

    public function sender(): ?User
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): ?User
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function project(): ?Project
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function isRead(): bool
    {
        return !empty($this->read_at);
    }

    public function markAsRead(): void
    {
        if (empty($this->read_at)) {
            $this->update(['read_at' => date('Y-m-d H:i:s')]);
        }
    }
}
