<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Models\Project;

/**
 * Devioz 24 — Galería y Portafolio Público de Proyectos
 */
class GalleryController extends BaseController
{
    public function index(): string
    {
        $db = Database::getInstance();

        $search = trim((string)($_GET['q'] ?? ''));
        $sede = trim((string)($_GET['sede'] ?? ''));

        $sql = "SELECT p.*, u.name as dev_name, u.avatar as dev_avatar 
                FROM `projects` p 
                JOIN `users` u ON p.developer_id = u.id 
                WHERE p.is_public_in_gallery = 1 AND p.deleted_at IS NULL";
        $params = [];

        if ($search !== '') {
            $searchTerm = "%{$search}%";
            $sql .= " AND (p.name LIKE :q1 OR p.code LIKE :q2 OR p.description LIKE :q3)";
            $params['q1'] = $searchTerm;
            $params['q2'] = $searchTerm;
            $params['q3'] = $searchTerm;
        }

        if ($sede !== '') {
            $sql .= " AND p.sede = :sede";
            $params['sede'] = $sede;
        }

        $sql .= " ORDER BY (p.status = 'completed') DESC, p.final_score DESC, p.id DESC";

        $rows = $db->fetchAll($sql, $params);
        $projects = array_map(fn($r) => new Project($r), $rows);

        return $this->view('gallery/index', [
            'title'    => 'Galería Pública de Proyectos — Devioz 24',
            'projects' => $projects,
            'search'   => $search,
            'sede'     => $sede,
        ]);
    }

    public function show(string $slug): string
    {
        $db = Database::getInstance();
        $row = $db->fetch(
            "SELECT * FROM `projects` WHERE `slug` = :slug AND `is_public_in_gallery` = 1 AND `deleted_at` IS NULL LIMIT 1",
            ['slug' => $slug]
        );

        if (!$row) {
            echo \App\Core\View::renderError(404, "El proyecto no se encuentra disponible en la galería.");
            exit;
        }

        $project = new Project($row);

        // Incrementar contador de visitas
        $db->query("UPDATE `projects` SET `views_count` = `views_count` + 1 WHERE `id` = :id", ['id' => $project->id]);

        $developer = $project->developer();
        $milestones = $project->milestones();

        return $this->view('gallery/show', [
            'title'      => "{$project->name} — Portafolio Devioz",
            'project'    => $project,
            'developer'  => $developer,
            'milestones' => $milestones,
        ]);
    }
}
