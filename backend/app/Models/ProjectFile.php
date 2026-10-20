<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Devioz 24 — Modelo de Archivo Técnico Adjunto
 */
class ProjectFile extends Model
{
    protected static string $table = 'project_files';

    protected array $casts = [
        'size'         => 'int',
        'is_encrypted' => 'bool',
    ];

    public function project(): ?Project
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function uploader(): ?User
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function formattedSize(): string
    {
        return human_size((int)$this->size);
    }
}
