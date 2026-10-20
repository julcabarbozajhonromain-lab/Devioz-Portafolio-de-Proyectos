<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Devioz OS — Modelo de Proyecto Corporativo
 */
class Project extends Model
{
    protected static string $table = 'projects';

    protected array $casts = [
        'tech_stack'           => 'json',
        'gallery_images'       => 'json',
        'is_public_in_gallery' => 'bool',
        'final_score'          => 'float',
        'views_count'          => 'int',
    ];

    // --------------------------------------------------------------------------
    // Relaciones
    // --------------------------------------------------------------------------

    public function developer(): ?User
    {
        return $this->belongsTo(User::class, 'developer_id');
    }

    public function assignedBy(): ?User
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function reviewedBy(): ?User
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approvedBy(): ?User
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function milestones(): array
    {
        return $this->hasMany(Milestone::class, 'project_id', 'id', 'order_position ASC');
    }

    public function deliveries(): array
    {
        return $this->hasMany(Delivery::class, 'project_id', 'id', 'created_at DESC');
    }

    public function evaluations(): array
    {
        return $this->hasMany(Evaluation::class, 'project_id', 'id', 'created_at DESC');
    }

    public function files(): array
    {
        return $this->hasMany(ProjectFile::class, 'project_id', 'id', 'created_at DESC');
    }

    public function checkIns(): array
    {
        return $this->hasMany(CheckIn::class, 'project_id', 'id', 'check_in_date DESC, created_at DESC');
    }

    public function comments(): array
    {
        return $this->hasMany(Comment::class, 'project_id', 'id', 'created_at ASC');
    }

    // --------------------------------------------------------------------------
    // Helpers y Lógica de Negocio
    // --------------------------------------------------------------------------

    /**
     * Calcula el porcentaje de avance según hitos completados y sus ponderaciones.
     */
    public function progressPercentage(): int
    {
        $milestones = $this->milestones();
        if (empty($milestones)) {
            return 0;
        }

        $totalWeight = 0;
        $completedWeight = 0;

        foreach ($milestones as $m) {
            $weight = (int)($m->weight ?: 1);
            $totalWeight += $weight;
            if ($m->status === 'completed') {
                $completedWeight += $weight;
            }
        }

        if ($totalWeight === 0) {
            return 0;
        }

        return (int)round(($completedWeight / $totalWeight) * 100);
    }

    /**
     * Retorna la URL de la portada o un degradado SVG por defecto.
     */
    public function coverUrl(): string
    {
        if (!empty($this->cover_image)) {
            if (str_starts_with($this->cover_image, 'http') || str_starts_with($this->cover_image, '//') || str_starts_with($this->cover_image, 'data:')) {
                return $this->cover_image;
            }
            return asset($this->cover_image);
        }

        // Portadas tecnológicas online de alta resolución desde Unsplash según ID/nombre del proyecto
        $curatedCovers = [
            'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=1200&auto=format&fit=crop&q=80', // SaaS & Cloud Management
            'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=1200&auto=format&fit=crop&q=80', // Big Data & Analytics
            'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=1200&auto=format&fit=crop&q=80', // Clean Code & Architecture
            'https://images.unsplash.com/photo-1559526324-4b87b5e36e44?w=1200&auto=format&fit=crop&q=80', // Fintech & Billing API
            'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=1200&auto=format&fit=crop&q=80', // Mobile Apps & Devices
            'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=1200&auto=format&fit=crop&q=80', // Tech Team & Collaboration
        ];

        $idx = abs((int)crc32((string)($this->name ?: 'devioz-' . $this->id))) % count($curatedCovers);
        return $curatedCovers[$idx];
    }

    /**
     * Retorna el nombre de la empresa/cliente solicitante del proyecto.
     */
    public function clientName(): string
    {
        return !empty($this->client_name) ? (string)$this->client_name : 'Cliente Corporativo';
    }

    /**
     * Retorna la categoría formal corporativa del proyecto (En Desarrollo, En Producción, Soporte).
     */
    public function categoryLabel(): string
    {
        if ($this->status === 'completed' || $this->status === 'approved') {
            return 'En Producción';
        }
        if ($this->status === 'archived' || $this->status === 'support') {
            return 'Soporte';
        }
        return 'En Desarrollo';
    }

    /**
     * Retorna el texto amigable del estado actual bajo estándares empresariales.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'assigned'       => 'Asignado',
            'draft'          => 'Borrador',
            'in_progress'    => 'En Desarrollo',
            'pending_review' => 'Pendiente de QA',
            'in_review'      => 'En Auditoría',
            'approved'       => 'Aprobado QA',
            'rejected'       => 'Revisión Solicitada',
            'completed'      => 'En Producción',
            'support'        => 'Soporte',
            'archived'       => 'Soporte',
            default          => ucfirst((string)$this->status),
        };
    }

    /**
     * Retorna el badge de Bootstrap correspondiente al estado.
     */
    public function statusBadge(): string
    {
        $class = match ($this->status) {
            'assigned'       => 'bg-secondary text-white',
            'draft'          => 'bg-dark text-light border border-secondary',
            'in_progress'    => 'bg-primary text-white',
            'pending_review' => 'bg-warning text-dark',
            'in_review'      => 'bg-info text-dark',
            'approved'       => 'bg-success text-white',
            'rejected'       => 'bg-danger text-white',
            'completed'      => 'bg-emerald text-white',
            'support'        => 'bg-secondary text-white',
            'archived'       => 'bg-secondary text-white',
            default          => 'bg-secondary text-white',
        };

        return '<span class="badge ' . $class . ' text-nowrap">' . e($this->statusLabel()) . '</span>';
    }

    /**
     * Genera automáticamente el siguiente código correlativo de proyecto (ej: DEV-2026-001, DEV-2026-027).
     */
    public static function generateNextCode(): string
    {
        $currentYear = date('Y');
        $db = \App\Core\Database::getInstance();
        $prefix = "DEV-{$currentYear}-";
        $rows = $db->fetchAll(
            "SELECT `code` FROM `projects` WHERE `code` LIKE :prefix",
            ['prefix' => "{$prefix}%"]
        );

        $maxNum = 0;
        foreach ($rows as $row) {
            $c = $row['code'] ?? '';
            if (preg_match('/^DEV-\d{4}-(\d+)$/i', $c, $m)) {
                $num = (int)$m[1];
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        $nextNum = $maxNum + 1;
        return sprintf('DEV-%s-%03d', $currentYear, $nextNum);
    }
}
