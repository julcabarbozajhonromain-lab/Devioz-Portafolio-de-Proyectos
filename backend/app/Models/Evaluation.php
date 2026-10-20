<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Devioz 24 — Modelo de Auditoría y Dictamen de Calidad QA
 */
class Evaluation extends Model
{
    protected static string $table = 'evaluations';

    protected array $casts = [
        'criteria'    => 'json',
        'score'       => 'float',
        'week_number' => 'int',
    ];

    public function project(): ?Project
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function delivery(): ?Delivery
    {
        return $this->belongsTo(Delivery::class, 'delivery_id');
    }

    public function evaluator(): ?User
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }
}
