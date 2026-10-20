<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class QualityAudit extends Model
{
    protected static string $table = 'quality_audits';

    protected array $casts = [
        'criteria' => 'json',
        'score' => 'float',
    ];

    public function findings(): array
    {
        return Database::getInstance()->fetchAll(
            'SELECT * FROM quality_audit_findings WHERE audit_id = :audit_id ORDER BY FIELD(severity, "critical", "high", "medium", "low", "info"), created_at DESC',
            ['audit_id' => $this->id]
        );
    }

    public function toolRuns(): array
    {
        return Database::getInstance()->fetchAll(
            'SELECT * FROM quality_tool_runs WHERE audit_id = :audit_id ORDER BY created_at DESC',
            ['audit_id' => $this->id]
        );
    }
}
