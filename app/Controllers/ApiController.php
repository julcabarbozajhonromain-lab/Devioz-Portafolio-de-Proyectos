<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Models\CheckIn;
use App\Models\Delivery;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;
use PDO;

/**
 * Devioz OS — Controlador de APIs Internas
 * Proporciona endpoints para Búsqueda Global Spotlight (Ctrl+K) y el Asistente Virtual Devioz Bot
 */
class ApiController extends BaseController
{
    /**
     * Búsqueda Global para Command Palette (Ctrl + K)
     */
    public function search(): never
    {
        $q = trim($_GET['q'] ?? '');

        if ($q === '') {
            // Mostrar elementos recientes por defecto
            $projects = Project::whereRaw('1=1', [], 'updated_at DESC', 5);
            $users = User::whereRaw('active = 1', [], 'name ASC', 5);
        } else {
            $term = '%' . $q . '%';
            $projects = Project::whereRaw(
                'name LIKE ? OR code LIKE ? OR description LIKE ? OR sede LIKE ?',
                [$term, $term, $term, $term],
                'updated_at DESC',
                6
            );

            $users = User::whereRaw(
                'active = 1 AND (name LIKE ? OR email LIKE ? OR headline LIKE ? OR sede LIKE ?)',
                [$term, $term, $term, $term],
                'name ASC',
                6
            );
        }

        $formattedProjects = array_map(fn($p) => [
            'id'          => $p->id,
            'code'        => $p->code,
            'name'        => $p->name,
            'sede'        => $p->sede,
            'status'      => $p->status,
            'final_score' => $p->final_score,
        ], $projects);

        $formattedUsers = array_map(fn($u) => [
            'id'     => $u->id,
            'name'   => $u->name,
            'email'  => $u->email,
            'role'   => $u->role,
            'sede'   => $u->sede,
            'avatar' => $u->avatarUrl(),
        ], $users);

        $this->json([
            'projects' => $formattedProjects,
            'users'    => $formattedUsers,
        ]);
    }

    /**
     * Asistente Inteligente Virtual Devioz Bot (Procesa lenguaje natural en tiempo real)
     */
    public function bot(): never
    {
        $currentUser = $this->user();
        if ($currentUser === null) {
            $this->json(['error' => 'Debes iniciar sesión para consultar LABVA.'], 401);
        }

        $raw = file_get_contents('php://input');
        $body = json_decode($raw, true);
        if (!is_array($body) && !empty($raw)) {
            $trimmed = trim($raw, " '\"\t\n\r");
            $body = json_decode($trimmed, true);
        }
        if (!is_array($body)) {
            $body = $_POST;
        }
        $message = trim((string)($body['message'] ?? ''));

        if (mb_strlen($message) > 2000) {
            $this->json(['error' => 'La consulta no puede superar los 2000 caracteres.'], 422);
        }

        if ($message === '') {
            $this->json(['reply' => 'Hola, soy LABVA. Escribe una consulta sobre proyectos, desarrolladores, sedes o requerimientos técnicos.']);
        }

        $aiService = new \App\Services\LabvaAiService();
        $response = $aiService->ask($message, $currentUser);
        $this->json([
            'reply'    => $response['reply'],
            'provider' => $response['provider'] ?? 'local',
            'model'    => $response['model'] ?? 'Devioz Knowledge Engine',
        ]);
    }

    /**
     * Motor de resolución de intención y consultas dinámicas
     */
    private function resolveBotQuery(string $rawQuery): string
    {
        $rawClean = trim($rawQuery);
        $q = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ', '¿', '?', '¡', '!', ',', '.', ';'],
            ['a', 'e', 'i', 'o', 'u', 'n', 'a', 'e', 'i', 'o', 'u', 'n', '', '', '', '', '', '', ''],
            $rawQuery
        );
        $q = strtolower(trim($q));

        $currentUser = auth()->user();
        $userGreetingName = $currentUser ? ' ' . explode(' ', $currentUser->name)[0] : '';

        // 1. Saludos, Identidad y Ayuda
        if (preg_match('/^(?:hola|buen|buenos|buenas|hey|que tal|saludos|quien eres|quien sos|ayuda|help|comandos|menu|opciones)\b/i', $q)) {
            return "**¡Hola{$userGreetingName}! Soy LABVA**, tu asistente técnica en **Devioz OS**.\n\n"
                . "Puedo ayudarte con información en tiempo real sobre el portafolio corporativo:\n\n"
                . "• **Proyectos:** Consulta estado, hitos o tecnologías (ej: *'cómo va DEV-2026-005'* o *'proyectos con react'*).\n"
                . "• **Desarrolladores:** Consulta su proyecto actual o historial (ej: *'información de Richar Lopez'*).\n"
                . "• **Disponibilidad:** Conoce quiénes están disponibles (ej: *'desarrolladores sin proyecto'*).\n"
                . "• **Cuadro de Honor:** Revisa el podio (ej: *'ranking de mejores notas'*).\n"
                . "• **Estándares:** Horarios de standups y escala (ej: *'horarios de check-in'* o *'escala de notas'*).\n\n"
                . "¿Qué te gustaría consultar?";
        }

        // 2. Ranking / Cuadro de Honor / Mejores Notas
        if (str_contains($q, 'ranking') || str_contains($q, 'mejor') || str_contains($q, 'top') || str_contains($q, 'destacado') || str_contains($q, 'cuadro de honor')) {
            $topProjects = Project::whereRaw("final_score IS NOT NULL AND deleted_at IS NULL", [], "final_score DESC", 5);
            if (!empty($topProjects)) {
                $medals = ['1.', '2.', '3.', '4.', '5.'];
                $lines = [];
                foreach ($topProjects as $idx => $p) {
                    $dev = $p->developer();
                    $devName = $dev ? $dev->name : 'Desarrollador';
                    $icon = $medals[$idx] ?? '•';
                    $lines[] = "{$icon} **{$p->name}** (`{$p->code}`)\n"
                             . "  • **Nota:** `{$p->final_score}/10` &bull; **Dev:** {$devName} ({$p->sede})\n"
                             . "  • [Ver Proyecto](/projects/{$p->id})";
                }
                return "**Cuadro de Honor & Top Proyectos:**\n\n"
                    . implode("\n\n", $lines) . "\n\n"
                    . "Puedes explorar todas las entregas aprobadas en la [Galería Pública](/gallery).";
            }
        }

        // 3. Desarrolladores sin proyecto / disponibles / consulta general
        if (str_contains($q, 'practicante') || str_contains($q, 'desarrollador') || str_contains($q, 'sin proyecto') || str_contains($q, 'libre') || str_contains($q, 'disponible') || str_contains($q, 'no asignado') || str_contains($q, 'desocupado') || str_contains($q, 'alumno')) {
            $db = Database::getInstance();
            $stmt = $db->query("
                SELECT u.id, u.name, u.sede, u.email 
                FROM users u 
                WHERE u.role = 'developer' 
                  AND u.active = 1 
                  AND u.id NOT IN (SELECT developer_id FROM projects WHERE deleted_at IS NULL AND status != 'rejected')
                ORDER BY u.name ASC
                LIMIT 6
            ");
            $unassigned = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $totalCount = $db->query("
                SELECT COUNT(*) FROM users u 
                WHERE u.role = 'developer' 
                  AND u.active = 1 
                  AND u.id NOT IN (SELECT developer_id FROM projects WHERE deleted_at IS NULL AND status != 'rejected')
            ")->fetchColumn();

            if (empty($unassigned)) {
                return "**¡Todos los desarrolladores activos tienen proyecto asignado!**\n\nActualmente se cumple la regla corporativa de 1 desarrollador por proyecto en curso.";
            }

            $list = array_map(fn($u) => "• **{$u['name']}** &bull; {$u['sede']} &bull; `{$u['email']}`", $unassigned);
            return "**Desarrolladores Disponibles para Asignación:**\n\n"
                . "Actualmente hay **{$totalCount} desarrolladores** listos para iniciar proyecto:\n\n"
                . implode("\n", $list) . "\n\n"
                . "Puedes asignarles un nuevo requerimiento desde [Asignaciones](/admin/assignments).";
        }

        // 4. Búsqueda por código de proyecto específico (DEV-2026-XXX o número)
        if (preg_match('/(?:dev[-_ ]?2026[-_ ]?0*([1-9]\d*)|prj[-_ ]?0*([1-9]\d*)|proyecto\s+0*([1-9]\d*))/i', $q, $matches)) {
            $num = $matches[1] ?: ($matches[2] ?? $matches[3]);
            $codePattern = sprintf('DEV-2026-%03d', (int)$num);
            $project = Project::findBy('code', $codePattern);

            if ($project) {
                return $this->formatProjectSummary($project);
            }
        }

        // 5. Horarios de Standups / Check-ins
        if (str_contains($q, 'check-in') || str_contains($q, 'check in') || str_contains($q, 'standup') || str_contains($q, 'horario') || str_contains($q, 'ventana') || str_contains($q, 'marcar') || str_contains($q, 'asistencia')) {
            return "⏰ **Reglamento de Check-ins Diarios (Standups):**\n\n"
                . "Cada desarrollador debe registrar obligatoriamente 3 check-ins por jornada laboral:\n\n"
                . "• **Mañana (08:00 – 10:00):** Planificación del sprint y metas inmediatas.\n"
                . "• **Mediodía (12:00 – 14:00):** Reporte de avance parcial e identificación de bloqueos.\n"
                . "• **Tarde (17:00 – 19:00):** Cierre de jornada, horas trabajadas y patrones aplicados.\n\n"
                . "El registro se realiza desde [Mi Proyecto &rarr; Check-in Standup](/my-project).";
        }

        // 6. Criterios de auditoría y escala de score QA
        if (str_contains($q, 'nota') || str_contains($q, 'calificacion') || str_contains($q, 'escala') || str_contains($q, 'rubrica') || str_contains($q, 'evalua') || str_contains($q, 'aprobar') || str_contains($q, 'qa')) {
            return "**Estándares de Arquitectura & Score QA:**\n\n"
                . "• **Escala Oficial:** Los hitos y entregas se auditan con un **Score QA de 0 a 100** puntos.\n"
                . "• **Score Mínimo Aprobatorio:** **70 / 100** puntos.\n"
                . "• **Criterios de Auditoría:**\n"
                . "  1. Arquitectura y limpieza de código (PSR-12 / Clean Architecture).\n"
                . "  2. Cumplimiento de requerimientos del hito asignado.\n"
                . "  3. Calidad de la documentación técnica y checklist QA.\n"
                . "  4. Sustentación y demo de producto en plataforma.\n\n"
                . "Los auditores gestionan los dictámenes en [Auditorías & QA](/admin/evaluations).";
        }

        // 7. Consultas por sedes
        if (str_contains($q, 'sede') || str_contains($q, 'cercado') || str_contains($q, 'lima norte') || str_contains($q, 'villa')) {
            if (str_contains($q, 'lima norte')) {
                $sedeTarget = 'Lima Norte';
            } elseif (str_contains($q, 'villa')) {
                $sedeTarget = 'Villa';
            } elseif (str_contains($q, 'cercado')) {
                $sedeTarget = 'Cercado';
            } else {
                $cercadoDevs = User::count("role = 'developer' AND active = 1 AND sede = 'Cercado'");
                $norteDevs = User::count("role = 'developer' AND active = 1 AND sede = 'Lima Norte'");
                $villaDevs = User::count("role = 'developer' AND active = 1 AND sede = 'Villa'");
                return "**Sedes de Consultoría Devioz:**\n\n"
                    . "Devioz opera en 3 sedes técnicas integradas:\n\n"
                    . "• **Cercado:** {$cercadoDevs} desarrolladores activos. [Ver Proyectos Cercado](/gallery?sede=Cercado)\n"
                    . "• **Lima Norte:** {$norteDevs} desarrolladores activos. [Ver Proyectos Lima Norte](/gallery?sede=Lima+Norte)\n"
                    . "• **Villa:** {$villaDevs} desarrolladores activos. [Ver Proyectos Villa](/gallery?sede=Villa)";
            }

            $devsSede = User::count("role = 'developer' AND active = 1 AND sede = '{$sedeTarget}'");
            $projSede = Project::count("sede = '{$sedeTarget}'");
            $approvedSede = Project::count("sede = '{$sedeTarget}' AND status = 'approved'");

            return "**Métricas de la Sede {$sedeTarget}:**\n\n"
                . "• **Desarrolladores activos:** {$devsSede}\n"
                . "• **Proyectos asignados:** {$projSede}\n"
                . "• **Proyectos aprobados:** {$approvedSede}\n\n"
                . "Puedes ver todos los proyectos de esta sede en la [Galería Filtrada](/gallery?sede=" . urlencode($sedeTarget) . ").";
        }

        // 8. Requisitos de Proyectos
        if (str_contains($q, 'hora') || str_contains($q, 'requisito') || str_contains($q, 'duracion') || str_contains($q, 'regla') || str_contains($q, '480')) {
            return "**Estándares de Desarrollo en Devioz:**\n\n"
                . "• **Regla 1 a 1:** Cada desarrollador puede tener un solo proyecto activo a la vez.\n"
                . "• **Horas Cronológicas:** Mínimo **480 horas** registradas mediante el módulo de check-in.\n"
                . "• **Aprobación:** Todos los hitos deben estar aprobados con nota ≥ 7.0.\n"
                . "• **Sustentación en Video:** Video demostrativo obligatorio antes de publicar en la galería.";
        }

        // 9. Navegación rápida por módulos
        if (str_contains($q, 'kanban') || str_contains($q, 'tablero')) {
            return "**Tablero Kanban de Revisión:**\n\nPermite a los supervisores arrastrar y gestionar proyectos según su fase de avance.\n\n[Ir al Tablero Kanban](/admin/review)";
        }
        if (str_contains($q, 'reels') || str_contains($q, 'video') || str_contains($q, 'short')) {
            return "**Devioz Reels & Demos Técnicos:**\n\nExplora las sustentaciones en la nueva **Espiral 3D interactiva** o en el feed vertical.\n\n[Ver Devioz Reels](/reels)";
        }
        if (str_contains($q, 'lounge') || str_contains($q, 'comunidad') || str_contains($q, 'foro')) {
            return "**Dev Lounge:**\n\nEspacio social para compartir artículos técnicos, videos, enlaces de WhatsApp/YouTube y debatir con la comunidad.\n\n[Entrar al Dev Lounge](/lounge)";
        }
        if (str_contains($q, 'galeria') || str_contains($q, 'portafolio')) {
            return "**Galería Pública de Proyectos:**\n\nExplora todos los proyectos aprobados y certificados por Devioz.\n\n[Explorar Portafolio](/gallery)";
        }

        // 10. Búsqueda por Tecnología o Stack de Consultoría
        $techMatches = ['aws', 'gcp', 'azure', 'terraform', 'jenkins', 'docker', 'kubernetes', 'react', 'laravel', 'php', 'python', 'mysql', 'pwa', 'node', 'vue', 'tailwind', 'bootstrap'];
        foreach ($techMatches as $tech) {
            if (str_contains($q, $tech)) {
                $term = '%' . $tech . '%';
                $projs = Project::whereRaw("tech_stack LIKE ? OR description LIKE ? OR name LIKE ?", [$term, $term, $term], "updated_at DESC", 4);
                if (!empty($projs)) {
                    $lines = [];
                    foreach ($projs as $p) {
                        $dev = $p->developer();
                        $devName = $dev ? $dev->name : 'Sin asignar';
                        $nota = $p->final_score ? " &bull; Nota: `{$p->final_score}/10`" : "";
                        $lines[] = "• **{$p->name}** (`{$p->code}`) &bull; Dev: {$devName}{$nota}\n  [Ver detalles](/projects/{$p->id})";
                    }
                    return "**Proyectos con tecnología " . strtoupper($tech) . ":**\n\n"
                        . implode("\n\n", $lines) . "\n\n"
                        . "¿Deseas ver más proyectos en el [Portafolio](/gallery)?";
                } else {
                    return "**Tecnología " . strtoupper($tech) . ":**\n\n"
                        . "Actualmente no hay proyectos públicos finalizados con este stack específico, pero está contemplado en las arquitecturas admitidas.\n\n"
                        . "[Explorar Portafolio Completo](/gallery)";
                }
            }
        }

        // 11. Búsqueda de Persona (Administrador o Desarrollador)
        $userMatch = $this->findPersonByName($rawClean);
        if ($userMatch) {
            return $this->formatUserSummary($userMatch);
        }

        // 12. Búsqueda Global de Proyecto por Nombre o Término
        $term = '%' . $rawClean . '%';
        $foundProj = Project::whereRaw("name LIKE ? OR description LIKE ?", [$term, $term], "updated_at DESC", 1);
        if (!empty($foundProj)) {
            return $this->formatProjectSummary($foundProj[0]);
        }

        // 13. Resumen Global de Proyectos
        if (str_contains($q, 'cuanto') || str_contains($q, 'estado') || str_contains($q, 'resumen') || str_contains($q, 'estadistica') || str_contains($q, 'metricas')) {
            $total = Project::count();
            $approved = Project::count("status = 'approved'");
            $inReview = Project::count("status = 'in_review' OR status = 'pending_review'");
            $inProgress = Project::count("status = 'in_progress'");
            $assigned = Project::count("status = 'assigned'");
            $devsCount = User::count("role = 'developer' AND active = 1");

            return "**Estado Global de Proyectos en Devioz OS:**\n\n"
                . "• **Total de Proyectos:** {$total}\n"
                . "• **Aprobados:** {$approved}\n"
                . "• **En Revisión:** {$inReview}\n"
                . "• **En Progreso:** {$inProgress}\n"
                . "• **Asignados:** {$assigned}\n"
                . "• **Desarrolladores Registrados:** {$devsCount}\n\n"
                . "Puedes ver el tablero completo en el [Kanban de Revisión](/admin/review).";
        }

        // 14. Fallback Inteligente y Contextual
        return "**LABVA Asistente Devioz:**\n\n"
            . "No encontré datos exactos para *'{$rawClean}'*, pero puedo ayudarte con estas consultas frecuentes:\n\n"
            . "• **Buscar Proyecto:** *'¿Cómo va DEV-2026-004?'* o *'Proyecto facturación'*\n"
            . "• **Buscar Persona:** *'Información de Elena'* o *'Richar Lopez'*\n"
            . "• **Top de Notas:** *'Cuáles son las mejores notas'* o *'ranking'*\n"
            . "• **Disponibles:** *'¿Quién no tiene proyecto asignado?'*\n"
            . "• **Tecnologías:** *'Proyectos con React'* o *'Proyectos Docker'*\n"
            . "• **Reglamento:** *'Horarios de standup'* o *'criterios de rúbrica'*";
    }

    private function formatProjectSummary(Project $project): string
    {
        $dev = $project->developer();
        $devName = $dev ? $dev->name : 'Sin asignar';
        $milestonesTotal = Milestone::count("project_id = {$project->id}");
        $milestonesDone = Milestone::count("project_id = {$project->id} AND status = 'completed'");
        $statusLabels = [
            'assigned'       => 'Asignado',
            'in_progress'    => 'En Progreso Activo',
            'pending_review' => 'Pendiente de Revisión',
            'in_review'      => 'En Revisión de Administración',
            'approved'       => 'Aprobado',
            'rejected'       => 'Requiere Correcciones',
            'completed'      => 'Finalizado con Éxito',
        ];
        $statusText = $statusLabels[$project->status] ?? $project->status;
        $nota = $project->final_score ? "{$project->final_score}/100" : 'Pendiente de dictamen QA';
        $tech = !empty($project->tech_stack) && is_array($project->tech_stack) ? implode(', ', $project->tech_stack) : 'No especificado';

        return "**Proyecto Encontrado: {$project->code}**\n\n"
            . "• **Nombre:** {$project->name}\n"
            . "• **Desarrollador:** {$devName} ({$project->sede})\n"
            . "• **Estado Actual:** {$statusText}\n"
            . "• **Score QA:** `{$nota}`\n"
            . "• **Stack:** `{$tech}`\n"
            . "• **Avance de Hitos:** {$milestonesDone} de {$milestonesTotal} completados.\n\n"
            . "[Ver Ficha Completa del Proyecto](/projects/{$project->id})";
    }

    private function findPersonByName(string $query): ?User
    {
        // Coincidencia directa para Elena
        if (preg_match('/\belena\b/i', $query)) {
            $elena = User::whereRaw("name LIKE '%Elena%'", [], "id ASC", 1);
            if (!empty($elena)) return $elena[0];
        }

        $stopWords = [
            'como', 'esta', 'quien', 'busca', 'para', 'info', 'sobre', 'practicante', 'desarrollador', 'datos', 
            'hola', 'profesor', 'alumno', 'cuales', 'todas', 'todos', 'usan', 'estas', 'estos',
            'tiene', 'hacer', 'donde', 'cuando', 'cuanto', 'desde', 'hasta', 'este', 'sede', 
            'sedes', 'nota', 'notas', 'lista', 'tabla', 'dime', 'dame', 'aver', 'proyecto',
            'proyectos', 'hora', 'horas', 'standup', 'reels', 'lounge', 'kanban', 'admin'
        ];

        $words = preg_split('/[\s,\.\?\!\:\;]+/', trim($query));
        foreach ($words as $word) {
            $cleanWord = strtolower(trim($word));
            if (mb_strlen($cleanWord) < 4) continue;
            if (in_array($cleanWord, $stopWords)) continue;

            $term = '%' . $cleanWord . '%';
            $user = User::whereRaw("name LIKE ?", [$term], "role DESC, name ASC", 1);
            if (!empty($user)) {
                return $user[0];
            }
        }
        return null;
    }

    private function formatUserSummary(User $user): string
    {
        if ($user->isAdmin()) {
            return "**Administrador / Líder Técnico:**\n\n"
                . "• **Nombre:** {$user->name}\n"
                . "• **Cargo:** Coordinación Técnica & Administración de Proyectos Devioz\n"
                . "• **Email:** `{$user->email}`\n\n"
                . "**Herramientas de Supervisión:**\n"
                . "• [Kanban de Revisión de Entregas](/admin/review)\n"
                . "• [Asignar Desarrolladores a Proyectos](/admin/assignments)\n"
                . "• [Evaluación de Rúbricas y Notas](/admin/evaluations)";
        }

        $activeProject = $user->activeProject();
        $allProjects = $user->projects();
        $projectInfo = "• **Proyecto:** Actualmente disponible (Sin proyecto activo).";

        if ($activeProject) {
            $statusLabels = [
                'assigned'       => 'Asignado',
                'in_progress'    => 'En Progreso Activo',
                'pending_review' => 'Pendiente de Revisión',
                'in_review'      => 'En Revisión de Administración',
                'approved'       => 'Aprobado',
                'rejected'       => 'Requiere Correcciones',
                'completed'      => 'Finalizado',
            ];
            $st = $statusLabels[$activeProject->status] ?? $activeProject->status;
            $nota = $activeProject->final_score ? " &bull; Nota: `{$activeProject->final_score}/10`" : "";
            $projectInfo = "• **Proyecto Activo:** {$activeProject->name} (`{$activeProject->code}`)\n"
                         . "• **Estado:** {$st}{$nota}\n"
                         . "• [Ver Ficha del Proyecto](/projects/{$activeProject->id})";
        } elseif (!empty($allProjects)) {
            $latest = $allProjects[0];
            $nota = $latest->final_score ? " &bull; Nota: `{$latest->final_score}/10`" : "";
            $projectInfo = "• **Proyecto Asignado:** {$latest->name} (`{$latest->code}`)\n"
                         . "• **Estado:** Finalizado / Portafolio{$nota}\n"
                         . "• [Ver Ficha del Proyecto](/projects/{$latest->id})";
        }

        return "**Desarrollador Encontrado:**\n\n"
            . "• **Nombre:** {$user->name}\n"
            . "• **Sede:** {$user->sede}\n"
            . "• **Email:** `{$user->email}`\n\n"
            . "**Estado Técnico:**\n"
            . $projectInfo;
    }

}
