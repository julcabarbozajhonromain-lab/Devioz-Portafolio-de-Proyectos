<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Devioz 24 — Modelo de Entrega de Avance (Delivery)
 */
class Delivery extends Model
{
    protected static string $table = 'deliveries';

    protected array $casts = [
        'files' => 'json',
        'score' => 'float',
    ];

    public function project(): ?Project
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function milestone(): ?Milestone
    {
        return $this->belongsTo(Milestone::class, 'milestone_id');
    }

    public function reviewedBy(): ?User
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function evaluation(): ?Evaluation
    {
        return $this->hasOne(Evaluation::class, 'delivery_id');
    }
}
