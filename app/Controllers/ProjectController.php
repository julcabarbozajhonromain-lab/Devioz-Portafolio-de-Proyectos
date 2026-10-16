<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Core\Uploader;
use App\Models\AuditLog;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;

/**
 * Devioz OS — Controlador de Gestión de Proyectos
 */
class ProjectController extends BaseController
{
    public function index(): string
    {
        // Regla de negocio: El desarrollador no ve la lista general de proyectos, va a su espacio
        if ($this->auth->isDeveloper()) {
            redirect('/my-project');
        }

        $db = Database::getInstance();

        // Filtros de búsqueda
        $search = trim((string)($_GET['q'] ?? ''));
        $status = trim((string)($_GET['status'] ?? ''));
        $sede = trim((string)($_GET['sede'] ?? ''));

        $sql = "SELECT p.*, u.name as dev_name, u.avatar as dev_avatar 
                FROM `projects` p 
                JOIN `users` u ON p.developer_id = u.id 
                WHERE p.deleted_at IS NULL";
        $params = [];

        if ($search !== '') {
            $searchTerm = "%{$search}%";
            $sql .= " AND (p.name LIKE :q1 OR p.code LIKE :q2 OR u.name LIKE :q3)";
            $params['q1'] = $searchTerm;
            $params['q2'] = $searchTerm;
            $params['q3'] = $searchTerm;
        }

        if ($status !== '') {
            $sql .= " AND p.status = :status";
            $params['status'] = $status;
        }

        if ($sede !== '') {
            $sql .= " AND p.sede = :sede";
            $params['sede'] = $sede;
        }

        $sql .= " ORDER BY p.id DESC";

        $rows = $db->fetchAll($sql, $params);
        $projects = array_map(fn($r) => new Project($r), $rows);

        // Contadores globales por etapa SDLC para las pestañas Bitrix24 ui-counter
        $statusCounts = [
            'all'         => (int)$db->fetchColumn("SELECT COUNT(*) FROM `projects` WHERE deleted_at IS NULL"),
            'in_progress' => (int)$db->fetchColumn("SELECT COUNT(*) FROM `projects` WHERE deleted_at IS NULL AND status IN ('assigned', 'in_progress')"),
            'review'      => (int)$db->fetchColumn("SELECT COUNT(*) FROM `projects` WHERE deleted_at IS NULL AND status IN ('pending_review', 'in_review')"),
            'approved'    => (int)$db->fetchColumn("SELECT COUNT(*) FROM `projects` WHERE deleted_at IS NULL AND status IN ('approved', 'completed')"),
        ];

        return $this->view('projects/index', [
            'title'        => 'Gestión de Proyectos & SDLC — Devioz OS',
            'projects'     => $projects,
            'search'       => $search,
            'status'       => $status,
            'sede'         => $sede,
            'statusCounts' => $statusCounts,
        ]);
    }

    public function show(string $id): string
    {
        $project = Project::find((int)$id);

        if (!$project || $project->deleted_at !== null) {
            echo \App\Core\View::renderError(404, "El proyecto solicitado no existe.");
            exit;
        }

        // La publicación en galería solo habilita la ficha pública, no los datos internos.
        if (!$this->auth->isAdmin() && (int)$project->developer_id !== $this->auth->id()) {
            echo \App\Core\View::renderError(403, "No tienes permiso para ver los detalles de este proyecto.");
            exit;
        }

        $milestones = $project->milestones();
        $deliveries = $project->deliveries();
        $evaluations = $project->evaluations();
        $checkIns = $project->checkIns();
        $comments = $project->comments();
        $files = $project->files();

        return $this->view('projects/show', [
            'title'       => "{$project->name} — Devioz OS",
            'project'     => $project,
            'milestones'  => $milestones,
            'deliveries'  => $deliveries,
            'evaluations' => $evaluations,
            'checkIns'    => $checkIns,
            'comments'    => $comments,
            'files'       => $files,
        ]);
    }

    public function create(): string
    {
        // Solo administradores pueden crear y asignar
        if (!$this->auth->isAdmin()) {
            echo \App\Core\View::renderError(403, "Solo los administradores pueden crear proyectos.");
            exit;
        }

        $db = Database::getInstance();
        // Obtener desarrolladores sin proyecto activo
        $availableDevelopers = $db->fetchAll(
            "SELECT * FROM `users` 
             WHERE `role` = 'developer' 
               AND `active` = 1 
               AND `deleted_at` IS NULL 
               AND `id` NOT IN (
                   SELECT `developer_id` FROM `projects` 
                   WHERE `status` NOT IN ('completed', 'archived', 'rejected') 
                     AND `deleted_at` IS NULL
               )
             ORDER BY `name` ASC"
        );

        $nextCode = Project::generateNextCode();

        return $this->view('projects/create', [
            'title'      => 'Asignar Nuevo Proyecto — Devioz OS',
            'developers' => $availableDevelopers,
            'nextCode'   => $nextCode,
        ]);
    }

    public function store(): void
    {
        if (!$this->auth->isAdmin()) {
            echo \App\Core\View::renderError(403);
            exit;
        }

        $developerId = (int)($_POST['developer_id'] ?? 0);
        $name = trim((string)($_POST['name'] ?? ''));
        $code = strtoupper(trim((string)($_POST['code'] ?? '')));

        // Si no se proporcionó código o ya existe por concurrencia, autogenerar correlativo
        if (empty($code) || Project::findBy('code', $code) !== null) {
            $code = Project::generateNextCode();
            $_POST['code'] = $code;
        }

        $sede = (string)($_POST['sede'] ?? '');
        $description = trim((string)($_POST['description'] ?? ''));
        $techStackInput = trim((string)($_POST['tech_stack'] ?? ''));

        $validator = $this->validate($_POST, [
            'developer_id' => 'required|numeric',
            'name'         => 'required|min:3|max:255',
            'code'         => 'required|min:3|max:50|unique:projects,code',
            'sede'         => 'required|in:Cercado,Lima Norte,Villa',
        ]);

        if ($validator->fails()) {
            redirect('/projects/create');
        }

        // Regla estricta: 1 Desarrollador = 1 Proyecto Activo
        $developer = User::find($developerId);
        if (!$developer || $developer->activeProject() !== null) {
            Session::flash('error', 'El desarrollador seleccionado ya cuenta con un proyecto activo.');
            redirect('/projects/create');
        }

        // Extraer tech stack automáticamente del contexto de la descripción
        $techStack = !empty($techStackInput)
            ? array_values(array_filter(array_map('trim', explode(',', $techStackInput))))
            : extract_tech_stack($description);

        // Generar slug URL friendly
        $slugBase = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        $slug = "{$slugBase}-" . strtolower($code);

        $clientName = trim((string)($_POST['client_name'] ?? 'Cliente Corporativo')) ?: 'Cliente Corporativo';

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $project = Project::create([
                'code'           => $code,
                'name'           => $name,
                'client_name'    => $clientName,
                'slug'           => $slug,
                'description'    => $description,
                'status'         => 'assigned',
                'sede'           => $sede,
                'tech_stack'     => $techStack,
                'developer_id'   => $developerId,
                'assigned_by'    => $this->auth->id(),
                'assigned_at'    => date('Y-m-d H:i:s'),
            ]);

            // Generar hitos básicos si se enviaron en el formulario
            $milestoneNames = $_POST['milestone_names'] ?? [];
            $milestoneDates = $_POST['milestone_dates'] ?? [];

            if (!empty($milestoneNames) && is_array($milestoneNames)) {
                foreach ($milestoneNames as $index => $mName) {
                    $mNameTrimmed = trim((string)$mName);
                    if ($mNameTrimmed !== '') {
                        $dueDate = $milestoneDates[$index] ?? date('Y-m-d', strtotime('+7 days'));
                        Milestone::create([
                            'project_id'     => $project->id,
                            'name'           => $mNameTrimmed,
                            'due_date'       => $dueDate,
                            'status'         => 'pending',
                            'order_position' => $index + 1,
                            'weight'         => 1,
                        ]);
                    }
                }
            } else {
                // Hito por defecto para inicializar el proyecto
                Milestone::create([
                    'project_id'     => $project->id,
                    'name'           => 'Hito 1: Requerimientos y Diseño Inicial',
                    'due_date'       => date('Y-m-d', strtotime('+14 days')),
                    'status'         => 'in_progress',
                    'order_position' => 1,
                    'weight'         => 1,
                ]);
            }

            AuditLog::record('project.assigned', 'Project', (int)$project->id, [
                'code'         => $code,
                'developer_id' => $developerId,
                'name'         => $name,
            ]);

            $db->commit();
            Session::flash('success', "Proyecto {$code} asignado con éxito a {$developer->name}.");
            redirect("/projects/{$project->id}");
        } catch (\Throwable $e) {
            $db->rollBack();
            Session::flash('error', 'Error al crear el proyecto: ' . $e->getMessage());
            redirect('/projects/create');
        }
    }

    public function edit(string $id): string
    {
        if (!$this->auth->isAdmin()) {
            echo \App\Core\View::renderError(403);
            exit;
        }

        $project = Project::find((int)$id);
        if (!$project) {
            echo \App\Core\View::renderError(404);
            exit;
        }

        $developers = User::whereRaw("role = 'developer' AND active = 1 AND deleted_at IS NULL", [], 'name ASC');

        return $this->view('projects/edit', [
            'title'      => "Editar {$project->code} — Devioz OS",
            'project'    => $project,
            'developers' => $developers,
        ]);
    }

    public function update(string $id): void
    {
        $project = Project::find((int)$id);
        if (!$project) {
            echo \App\Core\View::renderError(404);
            exit;
        }

        $techStackInput = trim((string)($_POST['tech_stack'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $techStack = !empty($techStackInput)
            ? array_values(array_filter(array_map('trim', explode(',', $techStackInput))))
            : (!empty($description) ? extract_tech_stack($description) : ($project->tech_stack ?: []));

        $data = [
            'name'           => trim((string)($_POST['name'] ?? $project->name)),
            'client_name'    => trim((string)($_POST['client_name'] ?? $project->clientName())),
            'description'    => trim((string)($_POST['description'] ?? '')),
            'documentation'  => trim((string)($_POST['documentation'] ?? '')),
            'repository_url' => trim((string)($_POST['repository_url'] ?? '')),
            'live_url'       => trim((string)($_POST['live_url'] ?? '')),
            'video_url'      => trim((string)($_POST['video_url'] ?? '')),
            'sede'           => trim((string)($_POST['sede'] ?? $project->sede)),
            'tech_stack'     => $techStack,
        ];

        // Si es administrador, permitir cambiar o desasignar el desarrollador
        if ($this->auth->isAdmin() && isset($_POST['developer_id'])) {
            $postedDev = trim((string)$_POST['developer_id']);
            if ($postedDev === '' || $postedDev === '0') {
                $data['developer_id'] = null;
                if ($project->developer_id !== null) {
                    $data['status'] = 'draft';
                }
            } else {
                $newDevId = (int)$postedDev;
                if ($newDevId !== (int)$project->developer_id) {
                    $data['developer_id'] = $newDevId;
                    if (empty($project->developer_id)) {
                        $data['assigned_at'] = date('Y-m-d H:i:s');
                        $data['assigned_by'] = $this->auth->id();
                    }
                    if (in_array($project->status, ['draft', 'archived'])) {
                        $data['status'] = 'assigned';
                    }
                }
            }
        }

        // Procesar subida de imagen de portada si existe
        if (!empty($_FILES['cover_image']['name'])) {
            try {
                $uploadResult = Uploader::upload($_FILES['cover_image'], 'covers');
                $data['cover_image'] = $uploadResult['path'];
            } catch (\Throwable $e) {
                Session::flash('error', 'Fallo al subir portada: ' . $e->getMessage());
            }
        }

        $project->update($data);

        AuditLog::record('project.updated', 'Project', (int)$project->id, [
            'code' => $project->code,
        ]);

        Session::flash('success', 'Proyecto actualizado satisfactoriamente.');
        redirect("/projects/{$project->id}");
    }

    public function submit(string $id): void
    {
        $project = Project::find((int)$id);
        if (!$project) {
            echo \App\Core\View::renderError(404);
            exit;
        }

        // Si es developer, debe ser su propio proyecto
        if ($this->auth->isDeveloper() && (int)$project->developer_id !== $this->auth->id()) {
            echo \App\Core\View::renderError(403);
            exit;
        }

        $project->update([
            'status'       => 'pending_review',
            'submitted_at' => date('Y-m-d H:i:s'),
        ]);

        AuditLog::record('project.submitted', 'Project', (int)$project->id, [
            'code' => $project->code,
        ]);

        Session::flash('success', 'El proyecto ha sido enviado a la administración para su revisión formal.');
        redirect($this->auth->isDeveloper() ? '/my-project' : "/projects/{$project->id}");
    }

    public function wizard(string $id): string
    {
        if (!$this->auth->isAdmin()) {
            echo \App\Core\View::renderError(403, "Solo los administradores pueden usar el asistente de edición.");
            exit;
        }

        $project = Project::find((int)$id);
        if (!$project) {
            echo \App\Core\View::renderError(404);
            exit;
        }

        return $this->view('projects/wizard', [
            'title'   => "Asistente de Proyecto: {$project->code}",
            'project' => $project,
        ]);
    }

    public function destroy(string $id): void
    {
        if (!$this->auth->isAdmin()) {
            echo \App\Core\View::renderError(403);
            exit;
        }

        $project = Project::find((int)$id);
        if ($project) {
            $project->update(['deleted_at' => date('Y-m-d H:i:s')]);
            AuditLog::record('project.deleted', 'Project', (int)$project->id, ['code' => $project->code]);
            Session::flash('success', 'Proyecto archivado correctamente.');
        }

        $redirect = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : '/projects';
        redirect($redirect);
    }

    /**
     * API JSON para el Bitrix SidePanel Slider (Previsualización e Inspección Rápida)
     */
    public function previewApi(string $id): void
    {
        $project = Project::find((int)$id);

        if (!$project || $project->deleted_at !== null) {
            http_response_code(404);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'error' => 'Proyecto no encontrado']);
            exit;
        }

        if (!$this->auth->isAdmin() && (int)$project->developer_id !== $this->auth->id()) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'error' => 'Acceso restringido']);
            exit;
        }

        $dev = $project->developer();
        $milestones = array_map(function($m) {
            return [
                'name'     => $m->name,
                'status'   => $m->status,
                'due_date' => format_date($m->due_date),
            ];
        }, $project->milestones());

        $techStack = is_array($project->tech_stack) ? $project->tech_stack : (json_decode($project->tech_stack ?? '[]', true) ?: []);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'data' => [
                'id'                 => $project->id,
                'code'               => $project->code,
                'name'               => $project->name,
                'client_name'        => $project->clientName(),
                'description'        => render_markdown($project->description ?: 'Sin descripción detallada registrada.'),
                'sede'               => $project->sede ?: 'Cercado',
                'status'             => $project->status,
                'status_label'       => $project->statusLabel(),
                'status_badge_html'  => $project->statusBadge(),
                'progress'           => $project->progressPercentage(),
                'final_score'        => $project->final_score,
                'repository_url'     => $project->repository_url,
                'live_url'           => $project->live_url,
                'video_url'          => !empty($project->video_url) ? getEmbedUrl($project->video_url, false) : null,
                'cover_url'          => $project->coverUrl(),
                'tech_stack'         => $techStack,
                'developer'          => $dev ? [
                    'name'   => $dev->name,
                    'avatar' => $dev->avatarUrl(),
                    'sede'   => $dev->sede,
                    'email'  => $dev->email,
                ] : null,
                'milestones'         => $milestones,
                'views_count'        => (int)$project->views_count,
                'url'                => url("/projects/{$project->id}"),
            ]
        ]);
        exit;
    }

    /**
     * API JSON para el Asistente CoPilot LABVA AI integrado en la Ficha Técnica
     */
    public function copilotApi(string $id): void
    {
        $project = Project::find((int)$id);

        if (!$project || $project->deleted_at !== null) {
            http_response_code(404);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'error' => 'Proyecto no encontrado']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            $input = $_POST;
        }

        $message = trim((string)($input['message'] ?? ''));
        $mode = trim((string)($input['mode'] ?? 'chat'));

        $aiService = new \App\Services\LabvaAiService();
        $response = $aiService->askProjectCopilot($project, $message, $mode);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success'  => true,
            'reply'    => $response['reply'],
            'mode'     => $mode,
            'provider' => $response['provider'] ?? 'local',
            'model'    => $response['model'] ?? 'Devioz Heuristic Engine',
        ]);
        exit;
    }
}

