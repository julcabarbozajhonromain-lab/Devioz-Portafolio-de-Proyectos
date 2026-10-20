<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Auth;

/**
 * Devioz OS — Modelo de Trazabilidad y Auditoría de Seguridad
 */
class AuditLog extends Model
{
    protected static string $table = 'audit_logs';

    protected array $casts = [
        'changes' => 'json',
    ];

    public function user(): ?User
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Helper estático para registrar eventos en la bitácora de auditoría.
     */
    public static function record(string $action, ?string $modelType = null, ?int $modelId = null, ?array $changes = null, ?int $userId = null): void
    {
        $currentUserId = $userId ?? Auth::getInstance()->id();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

        self::create([
            'user_id'    => $currentUserId,
            'action'     => $action,
            'model_type' => $modelType,
            'model_id'   => $modelId,
            'changes'    => $changes ? json_encode($changes, JSON_UNESCAPED_UNICODE) : null,
            'ip_address' => $ip,
            'user_agent' => $ua,
        ]);
    }
}
