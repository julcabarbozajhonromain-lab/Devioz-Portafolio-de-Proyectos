<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Project;

/** Applies the project's workflow rules with an optimistic status check. */
final class ProjectStatusTransition
{
    private const TRANSITIONS = [
        'draft' => ['assigned'],
        'assigned' => ['in_progress', 'pending_review', 'draft'],
        'in_progress' => ['pending_review', 'draft'],
        'pending_review' => ['in_review', 'approved', 'rejected', 'archived'],
        'in_review' => ['approved', 'rejected', 'archived'],
        'rejected' => ['in_progress', 'pending_review', 'archived'],
        'approved' => ['archived'],
        'completed' => ['archived'],
        'archived' => ['assigned'],
    ];

    public static function allows(string $from, string $to): bool
    {
        return $from === $to || in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    /**
     * Changes state only if the state read by the caller is still current.
     * Extra attributes are persisted atomically with the state transition.
     */
    public static function apply(Project $project, string $to, array $extra = []): bool
    {
        $from = (string)$project->status;
        if (!self::allows($from, $to)) {
            return false;
        }

        if ($from === $to) {
            return true;
        }

        $data = array_merge($extra, ['status' => $to]);
        $updated = Database::getInstance()->update(
            'projects',
            $data,
            '`id` = :id AND `status` = :expected_status AND `deleted_at` IS NULL',
            ['id' => $project->id, 'expected_status' => $from]
        );

        if ($updated !== 1) {
            return false;
        }

        $project->fill($data);
        return true;
    }
}
