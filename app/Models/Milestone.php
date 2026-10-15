<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Devioz OS — Modelo de Hito de Proyecto (Milestone)
 */
class Milestone extends Model
{
    protected static string $table = 'milestones';

    protected array $casts = [
        'order_position' => 'int',
        'weight'         => 'int',
    ];

    public function project(): ?Project
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function deliveries(): array
    {
        return $this->hasMany(Delivery::class, 'milestone_id', 'id', 'created_at DESC');
    }

    public function latestDelivery(): ?Delivery
    {
        $deliveries = $this->deliveries();
        return $deliveries[0] ?? null;
    }

    /**
     * Determina si el hito está atrasado respecto a su fecha límite.
     */
    public function isOverdue(): bool
    {
        if ($this->status === 'completed') {
            return false;
        }

        return strtotime($this->due_date) < strtotime(date('Y-m-d'));
    }
}
