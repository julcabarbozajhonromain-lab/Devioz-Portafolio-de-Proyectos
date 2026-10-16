<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\Evaluation;
use App\Models\Message;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;
use App\Services\QualityAuditService;
use InvalidArgumentException;

/**
 * Devioz OS — Controlador de Administración y Gestión TI
 */
class AdminController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->auth->isAdmin()) {
            if ($this->auth->isDeveloper()) {
                redirect('/my-project');
            }
            echo \App\Core\View::renderError(403, "Acceso restringido únicamente a administradores.");
            exit;
        }
    }

    /**
     * Tablero Kanban de Revisión (4 Columnas)
     */
    public function review(): string
    {
        $db = Database::getInstance();

        $fetchProjectsByStatus = function(string|array $status) use ($db) {
            $inClause = is_array($status) ? "'" . implode("','", $status) . "'" : "'{$status}'";
            $sql = "SELECT p.*, u.name as dev_name, u.avatar as dev_avatar, u.sede as dev_sede,
                           (SELECT COUNT(*) FROM deliveries WHERE project_id = p.id) as deliveries_count,
                           (SELECT COUNT(*) FROM project_files WHERE project_id = p.id) as files_count
                    FROM projects p
                    LEFT JOIN users u ON p.developer_id = u.id
                    WHERE p.status IN ({$inClause}) AND p.deleted_at IS NULL
                    ORDER BY p.updated_at DESC";
            $rows = $db->fetchAll($sql);
            return array_map(fn($r) => new Project($r), $rows);
        };

        $pendingProjects  = $fetchProjectsByStatus(['pending_review', 'assigned']);
        $inReviewProjects = $fetchProjectsByStatus('in_review');
        $approvedProjects = $fetchProjectsByStatus('approved');
        $rejectedProjects = $fetchProjectsByStatus('rejected');

        return $this->view('admin/review', [
            'title'            => 'Tablero Kanban de Revisión — Devioz OS',
            'pendingProjects'  => $pendingProjects,
            'inReviewProjects' => $inReviewProjects,
            'approvedProjects' => $approvedProjects,
            'rejectedProjects' => $rejectedProjects,
        ]);
    }

    /**
     * Endpoint API para actualizar el estado tras soltar una tarjeta en el Kanban (Drag & Drop)
     */
    public function updateStatus(string $id): never
    {
        $project = Project::find((int)$id);
        if (!$project) {
            $this->json(['error' => 'Proyecto no encontrado'], 404);
        }

        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $newStatus = (string)($input['status'] ?? '');

        $allowedStatuses = ['assigned', 'in_progress', 'pending_review', 'in_review', 'approved', 'rejected', 'completed', 'archived'];
        if (!in_array($newStatus, $allowedStatuses, true)) {
            $this->json(['error' => 'Estado destino no válido'], 422);
        }

        $data = [
            'status'      => $newStatus,
            'reviewed_by' => $this->auth->id(),
        ];

        if ($newStatus === 'in_review' && empty($project->reviewed_by)) {
            $data['reviewed_by'] = $this->auth->id();
        } elseif ($newStatus === 'approved') {
            $data['approved_by'] = $this->auth->id();
            $data['approved_at'] = date('Y-m-d H:i:s');
            $data['is_public_in_gallery'] = 1;
        } elseif ($newStatus === 'rejected') {
            $data['rejected_at'] = date('Y-m-d H:i:s');
        }

        $project->update($data);

        AuditLog::record('project.status_changed', 'Project', (int)$project->id, [
            'from' => $project->status,
            'to'   => $newStatus,
        ]);

        $this->json([
            'success'     => true,
            'new_status'  => $newStatus,
            'statusBadge' => $project->statusBadge(),
        ]);
    }

    public function approve(string $id): void
    {
        $project = Project::find((int)$id);
        if (!$project) {
            echo \App\Core\View::renderError(404);
            exit;
        }

        $audit = Database::getInstance()->fetch(
            "SELECT * FROM quality_audits WHERE project_id = :project_id AND gate_status = 'passed' AND status = 'passed' ORDER BY reviewed_at DESC, id DESC LIMIT 1",
            ['project_id' => $project->id]
        );
        if (!$audit || $audit['score'] === null) {
            Session::flash('error', 'No se puede aprobar el proyecto: requiere una auditoría QA aprobada con evidencias.');
            redirect('/admin/review');
        }
        $score = (float)$audit['score'];

        $project->update([
            'status'               => 'approved',
            'approved_by'          => $this->auth->id(),
            'approved_at'          => date('Y-m-d H:i:s'),
            'final_score'          => $score,
            'is_public_in_gallery' => 1,
        ]);

        // Notificar al desarrollador
        Message::create([
            'sender_id'   => $this->auth->id(),
            'receiver_id' => $project->developer_id,
            'project_id'  => $project->id,
            'subject'     => "¡Proyecto Aprobado por Auditoría QA! {$project->code}",
            'body'        => "Felicitaciones, tu proyecto '{$project->name}' ha sido formalmente APROBADO con QA Score de {$score} y publicado en el portafolio corporativo.",
            'type'        => 'alert',
            'priority'    => 'high',
        ]);

        AuditLog::record('project.approved', 'Project', (int)$project->id, [
            'score' => $score,
        ]);

        Session::flash('success', "Proyecto {$project->code} aprobado por Auditoría QA y publicado con éxito.");
        redirect('/admin/review');
    }

    public function reject(string $id): void
    {
        $project = Project::find((int)$id);
        if (!$project) {
            echo \App\Core\View::renderError(404);
            exit;
        }

        $reason = trim((string)($_POST['rejection_reason'] ?? ''));

        $validator = $this->validate($_POST, [
            'rejection_reason' => 'required|min:10|max:1000',
        ]);

        if ($validator->fails()) {
            redirect('/admin/review');
        }

        $project->update([
            'status'           => 'rejected',
            'rejection_reason' => $reason,
            'rejected_at'      => date('Y-m-d H:i:s'),
            'reviewed_by'      => $this->auth->id(),
        ]);

        Message::create([
            'sender_id'   => $this->auth->id(),
            'receiver_id' => $project->developer_id,
            'project_id'  => $project->id,
            'subject'     => "Observación en Proyecto: {$project->code}",
            'body'        => "Tu entrega ha sido observada con el siguiente motivo:\n\n{$reason}\n\nPor favor realiza los ajustes necesarios y vuelve a enviar a revisión.",
            'type'        => 'feedback',
            'priority'    => 'urgent',
        ]);

        AuditLog::record('project.rejected', 'Project', (int)$project->id, [
            'reason' => $reason,
        ]);

        Session::flash('warning', "Proyecto {$project->code} marcado como rechazado/observado.");
        redirect('/admin/review');
    }

    public function archive(string $id): void
    {
        $project = Project::find((int)$id);
        if ($project) {
            $project->update([
                'status'      => 'archived',
                'archived_at' => date('Y-m-d H:i:s'),
            ]);
            Session::flash('info', "Proyecto {$project->code} archivado.");
        }

        redirect('/admin/review');
    }

    /**
     * Panel de Asignaciones (Desarrolladores con y sin proyecto)
     */
    public function assignments(): string
    {
        $db = Database::getInstance();

        // 1. Desarrolladores SIN proyecto activo
        $unassigned = $db->fetchAll(
            "SELECT u.* FROM `users` u 
             WHERE u.role = 'developer' 
               AND u.active = 1 
               AND u.deleted_at IS NULL 
               AND u.id NOT IN (
                   SELECT developer_id FROM projects 
                   WHERE status NOT IN ('completed', 'archived', 'rejected') 
                     AND deleted_at IS NULL
                     AND developer_id IS NOT NULL
               )
             ORDER BY u.sede, u.name ASC"
        );

        // 2. Desarrolladores CON proyecto activo
        $assigned = $db->fetchAll(
            "SELECT u.id as dev_id, u.name as dev_name, u.email as dev_email, u.sede as dev_sede, u.avatar,
                    p.id as project_id, p.code as project_code, p.name as project_name, p.status as project_status,
                    p.assigned_at, p.tech_stack
             FROM users u
             JOIN projects p ON p.developer_id = u.id
             WHERE u.role = 'developer'
               AND p.status NOT IN ('completed', 'archived', 'rejected')
               AND p.deleted_at IS NULL
             ORDER BY p.assigned_at DESC"
        );

        // 3. Proyectos SIN desarrollador asignado (En espera / Pool de proyectos)
        $unassignedProjects = $db->fetchAll(
            "SELECT p.id as project_id, p.code as project_code, p.name as project_name, p.status as project_status,
                    p.sede as project_sede, p.created_at, p.assigned_at, p.tech_stack
             FROM projects p
             WHERE p.developer_id IS NULL 
               AND p.deleted_at IS NULL
             ORDER BY p.created_at DESC"
        );

        return $this->view('admin/assignments', [
            'title'              => 'Panel de Asignación de Proyectos — Devioz OS',
            'unassigned'         => array_map(fn($r) => new User($r), $unassigned),
            'assigned'           => $assigned,
            'unassignedProjects' => $unassignedProjects,
        ]);
    }

    /**
     * Reasignar proyecto a otro desarrollador disponible
     */
    public function reassign(string $id): void
    {
        $project = Project::find((int)$id);
        if (!$project) {
            Session::flash('error', 'Proyecto no encontrado.');
            redirect('/admin/assignments');
        }

        $newDeveloperId = (int)($_POST['developer_id'] ?? 0);
        if ($newDeveloperId <= 0) {
            Session::flash('error', 'Debes seleccionar un desarrollador válido para la reasignación.');
            redirect('/admin/assignments');
        }

        $newDev = User::find($newDeveloperId);
        if (!$newDev || $newDev->role !== 'developer' || !(bool)$newDev->active || $newDev->deleted_at !== null) {
            Session::flash('error', 'El desarrollador seleccionado no es válido o está inactivo.');
            redirect('/admin/assignments');
        }

        // Verificar que el desarrollador de destino no tenga otro proyecto activo
        $db = Database::getInstance();
        $existing = $db->fetch(
            "SELECT id, code, name FROM projects 
             WHERE developer_id = ? 
               AND id != ? 
               AND status NOT IN ('completed', 'archived', 'rejected') 
               AND deleted_at IS NULL LIMIT 1",
            [$newDeveloperId, $project->id]
        );

        if ($existing) {
            Session::flash('error', "El desarrollador {$newDev->name} ya tiene un proyecto activo asignado ({$existing['code']}).");
            redirect('/admin/assignments');
        }

        $oldDevId = $project->developer_id;
        $oldDev = $oldDevId ? User::find((int)$oldDevId) : null;

        // Actualizar la asignación manteniendo historial e hitos
        $updateData = [
            'developer_id' => $newDeveloperId,
            'assigned_by'  => $this->auth->id(),
            'assigned_at'  => date('Y-m-d H:i:s'),
        ];
        if (in_array($project->status, ['draft', 'archived'])) {
            $updateData['status'] = 'assigned';
        }

        $project->update($updateData);

        AuditLog::record('project.reassigned', 'Project', (int)$project->id, [
            'code'             => $project->code,
            'old_developer'    => $oldDev ? $oldDev->name : 'Ninguno (Pool)',
            'old_developer_id' => $oldDevId,
            'new_developer'    => $newDev->name,
            'new_developer_id' => $newDeveloperId,
        ]);

        $fromText = $oldDev ? $oldDev->name : 'el pool de espera';
        Session::flash('success', "Proyecto {$project->code} reasignado exitosamente de {$fromText} a {$newDev->name}.");

        $redirect = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : '/admin/assignments';
        redirect($redirect);
    }

    /**
     * Desasignar proyecto (liberar al desarrollador y mover proyecto al pool de espera)
     */
    public function unassign(string $id): void
    {
        $project = Project::find((int)$id);
        if (!$project) {
            Session::flash('error', 'Proyecto no encontrado.');
            redirect('/admin/assignments');
        }

        $oldDevId = $project->developer_id;
        $oldDev = $oldDevId ? User::find((int)$oldDevId) : null;

        // Desasignar: developer_id a NULL, status a 'draft'
        $project->update([
            'developer_id' => null,
            'status'       => 'draft',
        ]);

        AuditLog::record('project.unassigned', 'Project', (int)$project->id, [
            'code'             => $project->code,
            'old_developer'    => $oldDev ? $oldDev->name : 'N/A',
            'old_developer_id' => $oldDevId,
        ]);

        $devName = $oldDev ? $oldDev->name : 'el desarrollador';
        Session::flash('info', "El proyecto {$project->code} ha sido desasignado de {$devName}. El desarrollador ahora está disponible y el proyecto se encuentra en espera.");

        $redirect = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : '/admin/assignments';
        redirect($redirect);
    }

    /**
     * Auditorías & QA y Dictámenes de Calidad
     */
    public function evaluations(): string
    {
        $db = Database::getInstance();

        $evaluations = $db->fetchAll(
            "SELECT e.*, p.name as project_name, p.code as project_code, u.name as evaluator_name, dev.name as developer_name
             FROM evaluations e
             JOIN projects p ON e.project_id = p.id
             JOIN users u ON e.evaluator_id = u.id
             JOIN users dev ON p.developer_id = dev.id
             ORDER BY e.created_at DESC"
        );

        $pendingDeliveries = $db->fetchAll(
            "SELECT d.*, p.name as project_name, p.code as project_code, m.name as milestone_name, dev.name as developer_name
             FROM deliveries d
             JOIN projects p ON d.project_id = p.id
             LEFT JOIN milestones m ON d.milestone_id = m.id
             JOIN users dev ON p.developer_id = dev.id
             WHERE d.status = 'pending'
             ORDER BY d.submitted_at ASC"
        );

        $qualityAudits = $db->fetchAll(
            "SELECT qa.*, p.name AS project_name, p.code AS project_code, u.name AS evaluator_name
             FROM quality_audits qa JOIN projects p ON p.id = qa.project_id JOIN users u ON u.id = qa.evaluator_id
             ORDER BY qa.created_at DESC LIMIT 100"
        );

        return $this->view('admin/evaluations', [
            'title'             => 'Auditorías Técnicas & QA — Devioz',
            'evaluations'       => $evaluations,
            'pendingDeliveries' => $pendingDeliveries,
            'qualityAudits'     => $qualityAudits,
        ]);
    }

    /**
     * Guarda el dictamen de calidad y QA score de una entrega
     */
    public function gradeDelivery(string $deliveryId): void
    {
        $db = Database::getInstance();
        $deliveryRow = $db->fetch("SELECT * FROM deliveries WHERE id = :id", ['id' => $deliveryId]);

        if (!$deliveryRow) {
            echo \App\Core\View::renderError(404);
            exit;
        }

        $score = (float)($_POST['score'] ?? 0);
        $feedback = trim((string)($_POST['feedback'] ?? ''));
        // Soporta tanto escala 0-10 (>= 6.0) como escala 0-100 (>= 60.0)
        $status = ($score >= 60.0 || ($score >= 6.0 && $score <= 10.0)) ? 'approved' : 'rejected';

        $validator = $this->validate($_POST, [
            'score'    => 'required|numeric',
            'feedback' => 'required|min:5',
        ]);

        if ($validator->fails()) {
            redirect('/admin/evaluations');
        }

        $db->update(
            'deliveries',
            [
                'score'       => $score,
                'feedback'    => $feedback,
                'status'      => $status,
                'reviewed_by' => $this->auth->id(),
                'reviewed_at' => date('Y-m-d H:i:s'),
            ],
            '`id` = :id',
            ['id' => $deliveryId]
        );

        // Si fue aprobada, marcar el hito correspondiente como completado
        if ($status === 'approved' && !empty($deliveryRow['milestone_id'])) {
            $db->update(
                'milestones',
                [
                    'status'       => 'completed',
                    'completed_at' => date('Y-m-d H:i:s'),
                ],
                '`id` = :mid',
                ['mid' => $deliveryRow['milestone_id']]
            );
        }

        // Registrar dictamen formal de auditoría
        Evaluation::create([
            'project_id'   => $deliveryRow['project_id'],
            'delivery_id'  => $deliveryRow['id'],
            'evaluator_id' => $this->auth->id(),
            'score'        => $score,
            'criteria'     => ['qa_score' => $score],
            'feedback'     => $feedback,
            'evaluated_at' => date('Y-m-d H:i:s'),
        ]);

        Session::flash('success', "Dictamen emitido con éxito. Score de Calidad QA: {$score}");
        redirect('/admin/evaluations');
    }
}
