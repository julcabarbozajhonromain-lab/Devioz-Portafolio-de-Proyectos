<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Models\CheckIn;
use App\Models\Delivery;
use App\Models\Project;
use App\Models\User;

/**
 * Devioz OS — Controlador del Panel de Control (Dashboard)
 */
class DashboardController extends BaseController
{
    public function index(): string
    {
        $user = $this->user();
        $db = Database::getInstance();

        if ($this->auth->isAdmin() || $user->isLider()) {
            $isLider = $user->isLider();
            $sedeFiltro = $isLider ? " AND `sede` = :sede" : "";
            $sedeFiltroUser = $isLider ? " AND `sede` = :sede" : "";
            $params = $isLider ? ['sede' => $user->sede] : [];

            // Métricas exclusivas para Administradores y Líderes
            $totalProjects = $db->fetch("SELECT COUNT(*) as c FROM projects WHERE deleted_at IS NULL {$sedeFiltro}", $params)['c'] ?? 0;
            $inReviewCount = $db->fetch("SELECT COUNT(*) as c FROM projects WHERE status IN ('pending_review', 'in_review') AND deleted_at IS NULL {$sedeFiltro}", $params)['c'] ?? 0;
            $approvedCount = $db->fetch("SELECT COUNT(*) as c FROM projects WHERE status IN ('approved', 'completed') AND deleted_at IS NULL {$sedeFiltro}", $params)['c'] ?? 0;

            // Desarrolladores activos sin proyecto asignado
            $unassignedCount = $db->fetch(
                "SELECT COUNT(*) as total FROM `users` 
                 WHERE `role` = 'developer' 
                   AND `active` = 1 
                   AND `deleted_at` IS NULL {$sedeFiltroUser}
                   AND `id` NOT IN (
                       SELECT `developer_id` FROM `projects` 
                       WHERE `status` NOT IN ('completed', 'archived', 'rejected') 
                         AND `deleted_at` IS NULL
                   )",
                $params
            )['total'] ?? 0;

            // Últimos 5 proyectos modificados
            $recentProjects = $isLider 
                ? Project::whereRaw("sede = :sede AND deleted_at IS NULL", ['sede' => $user->sede], 'updated_at DESC', 5)
                : Project::whereRaw("deleted_at IS NULL", [], 'updated_at DESC', 5);

            // Feed de Actividad (Activity Stream: Check-ins)
            if ($isLider) {
                // Filtrar checkins de usuarios de la sede
                $recentCheckInsRows = $db->fetchAll(
                    "SELECT c.* FROM check_ins c 
                     JOIN users u ON c.user_id = u.id 
                     WHERE u.sede = :sede 
                     ORDER BY c.created_at DESC LIMIT 8",
                    ['sede' => $user->sede]
                );
                $activityStream = array_map(fn($row) => (new CheckIn($row)), $recentCheckInsRows);
            } else {
                $activityStream = CheckIn::whereRaw("1=1", [], 'created_at DESC', 8);
            }

            // Distribución de proyectos por sede para el gráfico
            $sedeStats = \App\Core\Cache::remember('dashboard_sede_stats_' . ($isLider ? md5($user->sede) : 'all'), 60, function() use ($db, $sedeFiltro, $params) {
                return $db->fetchAll(
                    "SELECT sede, COUNT(*) as count FROM projects 
                     WHERE deleted_at IS NULL AND sede IS NOT NULL {$sedeFiltro}
                     GROUP BY sede",
                    $params
                );
            });

            // Distribución de estados para gráfico de dona
            $statusStats = \App\Core\Cache::remember('dashboard_status_stats_' . ($isLider ? md5($user->sede) : 'all'), 60, function() use ($db, $sedeFiltro, $params) {
                return $db->fetchAll(
                    "SELECT status, COUNT(*) as count FROM projects 
                     WHERE deleted_at IS NULL {$sedeFiltro}
                     GROUP BY status",
                    $params
                );
            });

            return $this->view('dashboard/admin', [
                'title'           => $isLider ? 'Panel de Sede: ' . $user->sede : 'Panel de Administración',
                'isLider'         => $isLider,
                'sede'            => $user->sede,
                'totalProjects'   => (int)$totalProjects,
                'inReviewCount'   => (int)$inReviewCount,
                'approvedCount'   => (int)$approvedCount,
                'unassignedCount' => (int)$unassignedCount,
                'recentProjects'  => $recentProjects,
                'activityStream'  => $activityStream,
                'sedeStats'       => $sedeStats,
                'statusStats'     => $statusStats,
            ]);
        }

        // Vista de Desarrollador (Developer)
        $activeProject = $user->activeProject();
        $milestones = [];
        $deliveries = [];
        $progress = 0;
        $completedCount = 0;
        $totalMilestones = 0;

        if ($activeProject) {
            $milestones = $activeProject->milestones();
            $deliveries = array_slice($activeProject->deliveries(), 0, 5);
            $progress = $activeProject->progressPercentage();
            $totalMilestones = count($milestones);
            foreach ($milestones as $m) {
                if ($m->status === 'completed') {
                    $completedCount++;
                }
            }
        }

        // Check-ins realizados hoy por el desarrollador
        $todayCheckIns = $db->fetchAll(
            "SELECT * FROM `check_ins` 
             WHERE `user_id` = :uid AND `check_in_date` = CURDATE()",
            ['uid' => $user->id]
        );

        return $this->view('dashboard/developer', [
            'title'           => 'Mi Panel — Devioz OS',
            'project'         => $activeProject,
            'milestones'      => $milestones,
            'deliveries'      => $deliveries,
            'progress'        => $progress,
            'completedCount'  => $completedCount,
            'totalMilestones' => $totalMilestones,
            'todayCheckIns'   => $todayCheckIns,
        ]);
    }

    /**
     * Endpoint API JSON para alimentar dinámicamente gráficos Chart.js.
     */
    public function stats(): never
    {
        $db = Database::getInstance();

        $checkInsLast7Days = $db->fetchAll(
            "SELECT check_in_date, COUNT(*) as count, SUM(hours_worked) as total_hours 
             FROM check_ins 
             WHERE check_in_date >= CURDATE() - INTERVAL 6 DAY 
             GROUP BY check_in_date 
             ORDER BY check_in_date ASC"
        );

        $projectsByStatus = $db->fetchAll(
            "SELECT status, COUNT(*) as count 
             FROM projects 
             WHERE deleted_at IS NULL 
             GROUP BY status"
        );

        $this->json([
            'activity' => $checkInsLast7Days,
            'statuses' => $projectsByStatus,
        ]);
    }
}
