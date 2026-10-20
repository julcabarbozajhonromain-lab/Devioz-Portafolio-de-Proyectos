<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Core\Uploader;
use App\Models\AuditLog;
use App\Models\CheckIn;
use App\Models\Comment;
use App\Models\Delivery;
use App\Models\Message;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;
use App\Policies\ProjectPolicy;
use App\Services\ProjectStatusTransition;

/**
 * Devioz OS — Controlador del Espacio de Trabajo del Desarrollador (Mi Proyecto)
 */
class MyProjectController extends BaseController
{
    public function index(): string
    {
        $user = $this->user();
        $project = $user->activeProject();

        if (!$project) {
            return $this->view('my-project/empty', [
                'title' => 'Mi Proyecto — Devioz OS',
            ]);
        }

        $milestones = $project->milestones();
        $deliveries = $project->deliveries();
        $evaluations = $project->evaluations();
        $checkIns = $project->checkIns();
        $comments = $project->comments();
        $progress = $project->progressPercentage();
        $assignedLead = $project->assignedBy() ?? User::where('role', 'admin')[0] ?? null;

        return $this->view('my-project/index', [
            'title'        => "{$project->name} — Mi Proyecto",
            'project'      => $project,
            'milestones'   => $milestones,
            'deliveries'   => $deliveries,
            'evaluations'  => $evaluations,
            'checkIns'     => $checkIns,
            'comments'     => $comments,
            'progress'     => $progress,
            'assignedLead' => $assignedLead,
        ]);
    }

    public function completeMilestone(string $id): void
    {
        $milestone = Milestone::find((int)$id);
        if (!$milestone) {
            echo \App\Core\View::renderError(404, "Hito no encontrado.");
            exit;
        }

        $project = $milestone->project();
        if (!$project || !ProjectPolicy::allows($this->user(), 'complete_milestone', $project)) {
            echo \App\Core\View::renderError(403, "No puedes modificar hitos de un proyecto que no te pertenece.");
            exit;
        }

        $milestone->update([
            'status'       => 'completed',
            'completed_at' => date('Y-m-d H:i:s'),
        ]);

        AuditLog::record('milestone.completed', 'Milestone', (int)$milestone->id, [
            'name'       => $milestone->name,
            'project_id' => $project->id,
        ]);

        // Verificar si todos los hitos están completados
        $remaining = Milestone::count("`project_id` = :pid AND `status` != 'completed'", ['pid' => $project->id]);
        if ($remaining === 0) {
            if (!ProjectStatusTransition::apply($project, 'pending_review')) {
                Session::flash('error', 'Los hitos se completaron, pero el estado actual no permite enviar el proyecto a revisiÃ³n.');
                redirect('/my-project');
            }
            Session::flash('success', "¡Felicitaciones! Has completado todos los hitos. Tu proyecto ha entrado a revisión final.");
        } else {
            Session::flash('success', "Hito '{$milestone->name}' marcado como completado.");
        }

        redirect('/my-project');
    }

    public function submitDelivery(string $milestoneId): void
    {
        $milestone = Milestone::find((int)$milestoneId);
        if (!$milestone) {
            echo \App\Core\View::renderError(404, "Hito no encontrado.");
            exit;
        }

        $project = $milestone->project();
        if (!$project || !ProjectPolicy::allows($this->user(), 'submit_delivery', $project)) {
            echo \App\Core\View::renderError(403);
            exit;
        }

        $title = trim((string)($_POST['title'] ?? ''));
        $type = (string)($_POST['type'] ?? 'partial');
        $description = trim((string)($_POST['description'] ?? ''));
        $documentation = trim((string)($_POST['documentation'] ?? ''));
        $videoUrl = trim((string)($_POST['video_url'] ?? ''));

        $validator = $this->validate($_POST, [
            'title' => 'required|min:3|max:255',
            'type'  => 'required|in:partial,final',
        ]);

        if ($validator->fails()) {
            redirect('/my-project');
        }

        // Subir archivos adjuntos si existen
        $uploadedFiles = [];
        if (!empty($_FILES['delivery_files']['name'][0])) {
            $filesCount = count($_FILES['delivery_files']['name']);
            for ($i = 0; $i < $filesCount; $i++) {
                if ($_FILES['delivery_files']['error'][$i] === UPLOAD_ERR_OK) {
                    $fileItem = [
                        'name'     => $_FILES['delivery_files']['name'][$i],
                        'type'     => $_FILES['delivery_files']['type'][$i],
                        'tmp_name' => $_FILES['delivery_files']['tmp_name'][$i],
                        'error'    => $_FILES['delivery_files']['error'][$i],
                        'size'     => $_FILES['delivery_files']['size'][$i],
                    ];
                    try {
                        $meta = Uploader::upload($fileItem, "deliveries/{$project->id}", null, true);
                        $uploadedFiles[] = [
                            'original_name' => $meta['original_name'],
                            'path'          => $meta['path'],
                            'size'          => human_size((int)$meta['size']),
                            'mime'          => $meta['mime_type'],
                        ];
                    } catch (\Throwable $e) {
                        Session::flash('error', 'Error en archivo adjunto: ' . $e->getMessage());
                    }
                }
            }
        }

        $delivery = Delivery::create([
            'project_id'    => $project->id,
            'milestone_id'  => $milestone->id,
            'type'          => $type,
            'title'         => $title,
            'description'   => $description,
            'documentation' => $documentation,
            'video_url'     => $videoUrl !== '' ? $videoUrl : null,
            'files'         => $uploadedFiles,
            'submitted_at'  => date('Y-m-d H:i:s'),
            'status'        => 'pending',
        ]);

        // Cambiar estado del hito a en progreso si estaba pendiente
        if ($milestone->status === 'pending') {
            $milestone->update(['status' => 'in_progress']);
        }

        // Cambiar estado del proyecto a 'in_progress' o 'pending_review'
        if (in_array($project->status, ['assigned', 'rejected'], true)) {
            if (!ProjectStatusTransition::apply($project, 'in_progress')) {
                Session::flash('error', 'El estado del proyecto cambiÃ³ mientras se enviaba la entrega.');
                redirect('/my-project');
            }
        }

        // Notificar al administrador supervisor
        $teacherId = $project->assigned_by ?? 1;
        Message::create([
            'sender_id'   => $this->auth->id(),
            'receiver_id' => $teacherId,
            'project_id'  => $project->id,
            'subject'     => "Nueva Entrega Recibida: {$title}",
            'body'        => "El desarrollador {$this->user()->name} ha subido la entrega para el hito '{$milestone->name}'.",
            'type'        => 'feedback',
            'priority'    => 'normal',
        ]);

        AuditLog::record('delivery.submitted', 'Delivery', (int)$delivery->id, [
            'milestone_id' => $milestone->id,
            'title'        => $title,
        ]);

        Session::flash('success', 'Entrega enviada con éxito. La administración la revisará en breve.');
        redirect('/my-project');
    }

    public function checkIn(): void
    {
        $user = $this->user();
        $project = $user->activeProject();

        if (!$project) {
            echo \App\Core\View::renderError(403);
            exit;
        }

        $window = (string)($_POST['window'] ?? 'morning');
        $content = trim((string)($_POST['content'] ?? ''));
        $hours = (float)($_POST['hours_worked'] ?? 0);
        $blockers = trim((string)($_POST['blockers'] ?? ''));
        $nextSteps = trim((string)($_POST['next_steps'] ?? ''));
        $patternsInput = trim((string)($_POST['patterns_applied'] ?? ''));

        $validator = $this->validate($_POST, [
            'window'       => 'required|in:morning,midday,afternoon',
            'content'      => 'required|min:10',
            'hours_worked' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            redirect('/my-project');
        }

        $db = Database::getInstance();
        $today = date('Y-m-d');

        // Validar unicidad (user_id, window, check_in_date)
        $existing = $db->fetch(
            "SELECT id FROM `check_ins` WHERE `user_id` = :uid AND `window` = :win AND `check_in_date` = :dt",
            ['uid' => $user->id, 'win' => $window, 'dt' => $today]
        );

        $patterns = array_values(array_filter(array_map('trim', explode(',', $patternsInput))));

        if ($existing) {
            // Actualizar reporte existente de la misma ventana
            $db->update(
                'check_ins',
                [
                    'content'          => $content,
                    'hours_worked'     => $hours,
                    'blockers'         => $blockers !== '' ? $blockers : null,
                    'next_steps'       => $nextSteps !== '' ? $nextSteps : null,
                    'patterns_applied' => json_encode($patterns, JSON_UNESCAPED_UNICODE),
                ],
                '`id` = :id',
                ['id' => $existing['id']]
            );
            Session::flash('success', 'Tu check-in de hoy ha sido actualizado.');
        } else {
            CheckIn::create([
                'project_id'       => $project->id,
                'user_id'          => $user->id,
                'window'           => $window,
                'content'          => $content,
                'hours_worked'     => $hours,
                'blockers'         => $blockers !== '' ? $blockers : null,
                'next_steps'       => $nextSteps !== '' ? $nextSteps : null,
                'patterns_applied' => $patterns,
                'check_in_date'    => $today,
            ]);
            Session::flash('success', '¡Check-in diario registrado exitosamente!');
        }

        AuditLog::record('checkin.submitted', 'CheckIn', null, [
            'window' => $window,
            'hours'  => $hours,
        ]);

        redirect('/my-project');
    }

    public function addComment(): void
    {
        $user = $this->user();
        $projectId = (int)($_POST['project_id'] ?? 0);
        $content = trim((string)($_POST['content'] ?? ''));

        if ($content === '' || $projectId <= 0) {
            redirect('/my-project');
        }

        $project = Project::find($projectId);
        if (!$project || !ProjectPolicy::allows($user, 'comment', $project)) {
            echo \App\Core\View::renderError(403, "No tienes permiso para comentar en este proyecto.");
            exit;
        }

        Comment::create([
            'project_id' => $projectId,
            'user_id'    => $user->id,
            'content'    => $content,
        ]);

        Session::flash('success', 'Comentario publicado.');
        redirect($this->auth->isDeveloper() ? '/my-project' : "/projects/{$projectId}");
    }
}
