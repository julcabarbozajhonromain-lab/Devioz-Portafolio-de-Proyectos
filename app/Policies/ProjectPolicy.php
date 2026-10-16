<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

/** Central authorization rules for project-scoped resources. */
final class ProjectPolicy
{
    private const OWNER_ACTIONS = [
        'view',
        'preview',
        'copilot',
        'submit',
        'submit_delivery',
        'complete_milestone',
        'comment',
        'download',
    ];

    private const ADMIN_ACTIONS = [
        'create',
        'update',
        'delete',
        'assign',
        'archive',
        'audit',
        'approve',
        'change_status',
    ];

    public static function allows(?User $user, string $action, Project $project): bool
    {
        if ($user === null || $project->deleted_at !== null) {
            return false;
        }

        if (in_array($action, self::ADMIN_ACTIONS, true)) {
            return $user->role === 'admin';
        }

        if (!in_array($action, self::OWNER_ACTIONS, true)) {
            return false;
        }

        return $user->role === 'admin'
            || ($user->role === 'developer' && (int)$project->developer_id === (int)$user->id);
    }
}
