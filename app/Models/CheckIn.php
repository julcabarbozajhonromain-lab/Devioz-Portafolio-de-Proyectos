<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Devioz OS — Modelo de Check-In Diario (Daily Standup)
 */
class CheckIn extends Model
{
    protected static string $table = 'check_ins';

    protected array $casts = [
        'hours_worked'     => 'float',
        'story_points'     => 'float',
        'patterns_applied' => 'json',
        'metrics'          => 'json',
    ];

    public function project(): ?Project
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function user(): ?User
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function windowLabel(): string
    {
        return match ($this->window) {
            'morning'   => 'Mañana (08:00 - 12:00)',
            'midday'    => 'Mediodía (12:00 - 15:00)',
            'afternoon' => 'Tarde (15:00 - 19:00)',
            default     => ucfirst((string)$this->window),
        };
    }
}
