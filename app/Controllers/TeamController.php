<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Models\User;

/**
 * Devioz OS — Directorio de Equipo y Desarrolladores
 */
class TeamController extends BaseController
{
    public function index(): string
    {
        $db = Database::getInstance();

        $search = trim((string)($_GET['q'] ?? ''));
        $sede = trim((string)($_GET['sede'] ?? ''));
        $role = trim((string)($_GET['role'] ?? ''));

        $sql = "SELECT u.*, 
                       p.id as project_id, p.code as project_code, p.name as project_name, p.status as project_status 
                FROM `users` u 
                LEFT JOIN `projects` p ON p.developer_id = u.id AND p.status NOT IN ('completed', 'archived', 'rejected') AND p.deleted_at IS NULL
                WHERE u.active = 1 AND u.deleted_at IS NULL";
        $params = [];

        if ($search !== '') {
            $searchTerm = "%{$search}%";
            $sql .= " AND (u.name LIKE :q1 OR u.email LIKE :q2 OR u.headline LIKE :q3)";
            $params['q1'] = $searchTerm;
            $params['q2'] = $searchTerm;
            $params['q3'] = $searchTerm;
        }

        if ($sede !== '') {
            $sql .= " AND u.sede = :sede";
            $params['sede'] = $sede;
        }

        if ($role !== '') {
            $sql .= " AND u.role = :role";
            $params['role'] = $role;
        }

        $sql .= " ORDER BY (u.role = 'admin') DESC, u.sede, u.name ASC";

        $members = $db->fetchAll($sql, $params);

        return $this->view('team/index', [
            'title'   => 'Directorio del Equipo — Devioz OS',
            'members' => $members,
            'search'  => $search,
            'sede'    => $sede,
            'role'    => $role,
        ]);
    }
}
