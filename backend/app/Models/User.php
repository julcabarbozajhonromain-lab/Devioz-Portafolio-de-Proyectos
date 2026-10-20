<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Devioz 24 — Modelo de Usuario (Administradores y Desarrolladores)
 */
class User extends Model
{
    protected static string $table = 'users';

    protected array $casts = [
        'active' => 'bool',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isDeveloper(): bool
    {
        return $this->role === 'developer';
    }

    public function isLider(): bool
    {
        return $this->role === 'lider';
    }

    public function projects(): array
    {
        return $this->hasMany(Project::class, 'developer_id');
    }

    public function assignedProjects(): array
    {
        return $this->hasMany(Project::class, 'assigned_by');
    }

    public function evaluations(): array
    {
        return $this->hasMany(Evaluation::class, 'evaluator_id');
    }

    public function messages(): array
    {
        return $this->hasMany(Message::class, 'receiver_id', 'id', 'created_at DESC');
    }

    /**
     * Retorna el proyecto actualmente en curso del desarrollador (1 desarrollador = 1 proyecto activo).
     */
    public function activeProject(): ?Project
    {
        $db = self::getDb();
        $row = $db->fetch(
            "SELECT * FROM `projects` 
             WHERE `developer_id` = :dev_id 
               AND `status` NOT IN ('completed', 'archived', 'rejected') 
               AND `deleted_at` IS NULL 
             LIMIT 1",
            ['dev_id' => $this->id]
        );

        return $row ? new Project($row) : null;
    }

    /**
     * Retorna los proyectos completados que forman parte del portafolio.
     */
    public function completedProjects(): array
    {
        return Project::where('developer_id', $this->id, '=', 'completed_at DESC');
    }

    /**
     * Genera la URL del avatar o genera uno dinámico vectorial nítido con las iniciales (100% offline).
     */
    public function avatarUrl(): string
    {
        if (!empty($this->avatar)) {
            if (str_starts_with($this->avatar, 'http://') || str_starts_with($this->avatar, 'https://') || str_starts_with($this->avatar, '//') || str_starts_with($this->avatar, 'data:')) {
                return $this->avatar;
            }

            $clean = ltrim($this->avatar, '/');
            $fullPath = DEVIOZ_PUBLIC . '/' . $clean;
            if (file_exists($fullPath)) {
                return asset($this->avatar);
            }
        }

        // Generador offline de avatar SVG vectorial nítido (cero fallos de red)
        $initials = '';
        $words = preg_split('/\s+/', trim($this->name ?: 'Devioz User'));
        if (!empty($words[0])) {
            $initials .= mb_strtoupper(mb_substr($words[0], 0, 1));
        }
        if (!empty($words[1])) {
            $initials .= mb_strtoupper(mb_substr($words[1], 0, 1));
        }
        if (empty($initials)) {
            $initials = 'D';
        }

        // Paleta de gradientes refinados acorde a Devioz
        $palette = [
            ['#10b981', '#059669'], // Esmeralda
            ['#10b981', '#047857'], // Verde esmeralda
            ['#6366f1', '#4f46e5'], // Índigo
            ['#8b5cf6', '#7c3aed'], // Violeta
            ['#3b82f6', '#2563eb'], // Azul Royal
            ['#f59e0b', '#d97706'], // Ámbar
        ];
        $idx = abs(crc32($this->name ?: 'devioz')) % count($palette);
        [$c1, $c2] = $palette[$idx];

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">'
            . '<defs><linearGradient id="g_' . $idx . '" x1="0%" y1="0%" x2="100%" y2="100%">'
            . '<stop offset="0%" stop-color="' . $c1 . '"/>'
            . '<stop offset="100%" stop-color="' . $c2 . '"/>'
            . '</linearGradient></defs>'
            . '<circle cx="50" cy="50" r="50" fill="url(#g_' . $idx . ')"/>'
            . '<text x="50" y="55" font-family="-apple-system, BlinkMacSystemFont, \'Plus Jakarta Sans\', sans-serif" font-size="38" font-weight="700" fill="#ffffff" dominant-baseline="middle" text-anchor="middle" letter-spacing="1">' . htmlspecialchars($initials) . '</text>'
            . '</svg>';

        return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
    }
}
