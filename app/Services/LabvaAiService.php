<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Project;
use App\Models\User;
use PDO;

/**
 * Devioz OS — LABVA AI Service
 * Sistema de Inteligencia Artificial Dual-Provider con Fallback Automático:
 * 1. Groq Cloud LPU API (Llama 3.3 70B / 3.1 8B — ultra rápido y estable)
 * 2. Google Gemini API (Gemini 1.5 Flash — respaldo secundario)
 * 3. Local Knowledge Engine (Motor de base de datos en tiempo real — 0 caídas garantizado)
 */
class LabvaAiService
{
    private string $groqKey;
    private string $groqModel;
    private string $geminiKey;
    private string $geminiModel;

    public function __construct()
    {
        $this->groqKey = trim((string)(getenv('GROQ_API_KEY') ?: ''));
        $this->groqModel = trim((string)(getenv('GROQ_MODEL') ?: 'llama-3.3-70b-versatile'));
        $this->geminiKey = trim((string)(getenv('GEMINI_API_KEY') ?: ''));
        $this->geminiModel = trim((string)(getenv('GEMINI_MODEL') ?: 'gemini-1.5-flash'));
    }

    /**
     * Responde una consulta de usuario utilizando el mejor proveedor disponible con fallback.
     */
    public function ask(string $message, User $currentUser): array
    {
        $message = trim($message);
        if ($message === '') {
            return [
                'reply' => 'Hola, soy LABVA. Formula una consulta sobre proyectos, desarrolladores, sedes, rankings o requerimientos de Devioz.',
                'provider' => 'local',
            ];
        }

        $systemPrompt = $this->buildSystemContext($currentUser);

        // 1. Intentar con Groq API si tiene API Key configurada
        if (!empty($this->groqKey)) {
            $groqReply = $this->callGroq($message, $systemPrompt);
            if ($groqReply !== null && trim($groqReply) !== '') {
                return [
                    'reply' => $this->sanitizeText($groqReply),
                    'provider' => 'groq',
                    'model' => $this->groqModel,
                ];
            }
        }

        // 2. Intentar con Gemini API si Groq falló o no está configurado
        if (!empty($this->geminiKey)) {
            $geminiReply = $this->callGemini($message, $systemPrompt);
            if ($geminiReply !== null && trim($geminiReply) !== '') {
                return [
                    'reply' => $this->sanitizeText($geminiReply),
                    'provider' => 'gemini',
                    'model' => $this->geminiModel,
                ];
            }
        }

        // 3. Fallback Garantizado: Motor de Conocimiento Local (Base de datos MySQL en vivo)
        $localReply = $this->resolveLocalKnowledge($message, $currentUser);
        return [
            'reply' => $this->sanitizeText($localReply),
            'provider' => 'local',
            'model' => 'Devioz Knowledge Engine',
        ];
    }

    /**
     * Llamada a Groq Cloud API (OpenAI compatible endpoint, ultra veloz)
     */
    private function callGroq(string $userMessage, string $systemPrompt): ?string
    {
        $url = 'https://api.groq.com/openai/v1/chat/completions';
        $payload = [
            'model' => $this->groqModel,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userMessage],
            ],
            'temperature' => 0.35,
            'max_tokens' => 900,
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->groqKey,
            ],
            CURLOPT_TIMEOUT => 6,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && is_string($response)) {
            $data = json_decode($response, true);
            $content = $data['choices'][0]['message']['content'] ?? null;
            if (is_string($content) && trim($content) !== '') {
                return trim($content);
            }
        }

        return null;
    }

    /**
     * Llamada a Google Gemini API (generateContent endpoint)
     */
    private function callGemini(string $userMessage, string $systemPrompt): ?string
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->geminiModel}:generateContent";
        $fullPrompt = "INSTRUCCIONES DEL SISTEMA:\n{$systemPrompt}\n\nCONSULTA DEL USUARIO:\n{$userMessage}";

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [['text' => $fullPrompt]],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.35,
                'maxOutputTokens' => 850,
            ],
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'x-goog-api-key: ' . $this->geminiKey],
            CURLOPT_TIMEOUT => 6,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && is_string($response)) {
            $data = json_decode($response, true);
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            if (is_string($text) && trim($text) !== '') {
                return trim($text);
            }
        }

        return null;
    }

    /**
     * Construye un contexto en tiempo real con la base de datos de Devioz OS
     */
    private function buildSystemContext(User $currentUser): string
    {
        $scope = $this->projectScope($currentUser);
        $isAdmin = $currentUser->isAdmin();
        $totalProjects = Project::count($scope['where'], $scope['params']);
        $approvedCount = Project::count("({$scope['where']}) AND status = 'approved'", $scope['params']);
        $inReviewCount = Project::count("({$scope['where']}) AND (status = 'in_review' OR status = 'pending_review')", $scope['params']);
        $inProgressCount = Project::count("({$scope['where']}) AND status = 'in_progress'", $scope['params']);

        // Listado de proyectos activos
        $projects = Project::whereRaw($scope['where'], $scope['params'], 'id ASC', 20);
        $projSummaries = [];
        foreach ($projects as $p) {
            $dev = $p->developer();
            $devName = $dev ? $dev->name : 'Sin asignar';
            $score = $p->final_score ? "{$p->final_score}/10" : 'Sin calificar';
            $tech = is_array($p->tech_stack) ? implode(', ', $p->tech_stack) : 'N/A';
            $projSummaries[] = "- Código: {$p->code} | Nombre: {$p->name} | Desarrollador: {$devName} | Sede: {$p->sede} | Estado: {$p->status} | Nota: {$score} | Tech: {$tech}";
        }
        $projListStr = implode("\n", $projSummaries);

        // Desarrolladores libres
        $db = Database::getInstance();
        $stmt = $isAdmin ? $db->query("
            SELECT u.name, u.sede
            FROM users u
            WHERE u.role = 'developer' AND u.active = 1
              AND u.id NOT IN (SELECT developer_id FROM projects WHERE deleted_at IS NULL AND status != 'rejected')
            ORDER BY u.name ASC LIMIT 10
        ") : null;
        $freeDevs = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        $freeDevsStr = empty($freeDevs)
            ? "Todos los desarrolladores tienen asignación en curso."
            : implode(', ', array_map(fn($d) => "{$d['name']} ({$d['sede']})", $freeDevs));

        if (!$isAdmin) {
            $freeDevsStr = 'Dato disponible solo para administracion.';
        }

        // Top 5 ranking
        $topProjects = Project::whereRaw("({$scope['where']}) AND final_score IS NOT NULL", $scope['params'], "final_score DESC", 5);
        $rankingStr = implode("\n", array_map(function ($tp, $idx) {
            $d = $tp->developer();
            $dn = $d ? $d->name : 'Dev';
            return ($idx + 1) . ". {$tp->name} ({$tp->code}) - Nota: {$tp->final_score}/100 - Dev: {$dn} ({$tp->sede})";
        }, $topProjects, array_keys($topProjects)));

        return <<<PROMPT
Eres LABVA, el sistema de inteligencia artificial y asistente técnico de Devioz OS.
Tu diseño y personalidad están inspirados en la estética analítica y minimalista de xAI (Grok): eres rápido, preciso, conciso, resolutivo y directo.
Tienes acceso únicamente a información que el rol consultante puede ver. No reveles ni infieras datos fuera de ese alcance.

REGLAS DE RESPUESTA ESTRICTAS:
1. Responde SIEMPRE en español con tono profesional, técnico y analítico.
2. NUNCA UTILICES EMOJIS bajo ninguna circunstancia. Cero emojis en todas las respuestas.
3. Utiliza formato Markdown limpio con negritas y listas de viñetas estructuradas (•).
4. Sé conciso y concreto: evita saludos largos o rellenos decorativos.
5. Puedes sugerir enlaces internos cuando sea pertinente: /projects/{id}, /gallery, /admin/assignments, /my-project.

DATOS EN TIEMPO REAL DE DEVIOZ:
- Alcance autorizado: {$totalProjects} proyectos visibles para el rol {$currentUser->role}
- Proyectos totales: {$totalProjects} (Aprobados: {$approvedCount}, En Revisión: {$inReviewCount}, En Progreso: {$inProgressCount})
- Desarrolladores disponibles sin proyecto: {$freeDevsStr}
- Cuadro de honor (Top ranking de notas):
{$rankingStr}

CATÁLOGO DE PROYECTOS:
{$projListStr}

ESTÁNDARES TÉCNICOS Y OPERATIVOS:
- Standup / Check-in diario: Ventana obligatoria de registro entre 08:00 AM y 10:00 AM (máximo 10:30 AM con tolerancia).
- Escala de auditoría y Score QA: 0 a 100 puntos. Score mínimo aprobatorio: 70/100.
- Sedes autorizadas: San Isidro, Villa, Lima Norte, Lima Centro.
- Política de asignación: 1 desarrollador titular por proyecto.
PROMPT;
    }

    /**
     * Motor de resolución local inteligente (Fallback sin conexión a internet ni API keys)
     */
    private function resolveLocalKnowledge(string $rawQuery, User $currentUser): string
    {
        $scope = $this->projectScope($currentUser);
        $isAdmin = $currentUser->isAdmin();
        $q = strtolower(trim($rawQuery));
        $q = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ', '¿', '?', '¡', '!', ',', '.', ';'],
            ['a', 'e', 'i', 'o', 'u', 'n', 'a', 'e', 'i', 'o', 'u', 'n', '', '', '', '', '', '', ''],
            $q
        );

        // 1. Saludos e Identidad
        if (preg_match('/^(?:hola|buen|buenos|buenas|hey|que tal|saludos|quien eres|quien sos|ayuda|help|comandos|menu|opciones)\b/i', $q)) {
            return "**Hola. Soy LABVA**, el sistema de inteligencia artificial y asistencia técnica en **Devioz OS**.\n\n"
                . "Proceso consultas en tiempo real sobre el portafolio corporativo:\n\n"
                . "• **Proyectos:** Estado, hitos, tecnologías y entregables (ej: *'cómo va DEV-2026-002'* o *'proyectos con react'*).\n"
                . "• **Desarrolladores:** Asignaciones y disponibilidad (ej: *'desarrolladores libres'*).\n"
                . "• **Cuadro de Honor:** Ranking y notas finales (ej: *'ranking de notas'*).\n"
                . "• **Estándares:** Horarios de check-in y escala de rúbrica.\n\n"
                . "¿Qué información requieres analizar?";
        }

        // 2. Ranking de notas
        if (str_contains($q, 'ranking') || str_contains($q, 'mejor') || str_contains($q, 'top') || str_contains($q, 'destacado') || str_contains($q, 'cuadro de honor')) {
            $topProjects = Project::whereRaw("({$scope['where']}) AND final_score IS NOT NULL", $scope['params'], "final_score DESC", 5);
            if (!empty($topProjects)) {
                $lines = [];
                foreach ($topProjects as $idx => $p) {
                    $dev = $p->developer();
                    $devName = $dev ? $dev->name : 'Desarrollador';
                    $pos = $idx + 1;
                    $lines[] = "{$pos}. **{$p->name}** (`{$p->code}`)\n"
                             . "  • **Nota:** `{$p->final_score}/10` &bull; **Dev:** {$devName} ({$p->sede})\n"
                             . "  • [Ver Proyecto](/projects/{$p->id})";
                }
                return "**Cuadro de Honor & Ranking de Proyectos:**\n\n" . implode("\n\n", $lines);
            }
        }

        // 3. Desarrolladores libres / sin proyecto
        if (str_contains($q, 'practicante') || str_contains($q, 'desarrollador') || str_contains($q, 'sin proyecto') || str_contains($q, 'libre') || str_contains($q, 'disponible') || str_contains($q, 'desocupado')) {
            if (!$isAdmin) {
                return "Por permisos, la disponibilidad y los datos del equipo completo solo pueden consultarse con un rol de administraciÃ³n.";
            }
            $db = Database::getInstance();
            $stmt = $db->query("
                SELECT u.name, u.sede, u.email
                FROM users u
                WHERE u.role = 'developer' AND u.active = 1
                  AND u.id NOT IN (SELECT developer_id FROM projects WHERE deleted_at IS NULL AND status != 'rejected')
                ORDER BY u.name ASC LIMIT 8
            ");
            $unassigned = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($unassigned)) {
                return "**Todos los desarrolladores activos cuentan actualmente con proyecto asignado**, cumpliendo la política de 1 desarrollador por requerimiento.";
            }

            $list = array_map(fn($u) => "• **{$u['name']}** &bull; {$u['sede']} &bull; `{$u['email']}`", $unassigned);
            return "**Desarrolladores Disponibles para Asignación:**\n\n"
                . implode("\n", $list) . "\n\n"
                . "Puedes coordinar nuevas asignaciones en [Asignación de Desarrolladores](/admin/assignments).";
        }

        // 4. Estado global de proyectos y métricas
        if (str_contains($q, 'cuantos proyectos') || str_contains($q, 'proyectos activos') || str_contains($q, 'estado de proyecto') || str_contains($q, 'resumen') || str_contains($q, 'proyectos')) {
            $total = Project::count($scope['where'], $scope['params']);
            $approved = Project::count("({$scope['where']}) AND status = 'approved'", $scope['params']);
            $inReview = Project::count("({$scope['where']}) AND (status = 'in_review' OR status = 'pending_review')", $scope['params']);
            $inProgress = Project::count("({$scope['where']}) AND status = 'in_progress'", $scope['params']);
            $assigned = Project::count("({$scope['where']}) AND status = 'assigned'", $scope['params']);
            $devsCount = $isAdmin ? User::count("role = 'developer' AND active = 1") : null;

            return ($isAdmin ? "**Estado Global de Proyectos en Devioz OS:**\n\n" : "**Estado de tus proyectos asignados:**\n\n")
                . "• **Total de Proyectos:** {$total}\n"
                . "• **Aprobados:** {$approved}\n"
                . "• **En Revisión:** {$inReview}\n"
                . "• **En Progreso:** {$inProgress}\n"
                . "• **Asignados:** {$assigned}\n"
                . "• **Desarrolladores Registrados:** {$devsCount}\n\n"
                . "Puedes ver el tablero completo en el [Kanban de Revisión](/admin/review).";
        }

        // 5. Búsqueda por código de proyecto (DEV-2026-XXX)
        if (preg_match('/(?:dev[-_ ]?2026[-_ ]?0*([1-9]\d*)|prj[-_ ]?0*([1-9]\d*)|proyecto\s+0*([1-9]\d*))/i', $q, $matches)) {
            $num = $matches[1] ?: ($matches[2] ?? $matches[3]);
            $codePattern = sprintf('DEV-2026-%03d', (int)$num);
            $projectsByCode = Project::whereRaw("({$scope['where']}) AND code = ?", array_merge($scope['params'], [$codePattern]), 'id ASC', 1);
            $project = $projectsByCode[0] ?? null;
            if ($project) {
                return $this->formatProjectSummary($project);
            }
        }

        // 5. Horarios de Check-In
        if (str_contains($q, 'check-in') || str_contains($q, 'checkin') || str_contains($q, 'horario') || str_contains($q, 'standup') || str_contains($q, 'asistencia')) {
            return "**Normativa de Check-In y Standup Diario:**\n\n"
                . "• **Horario Principal:** 08:00 AM a 10:00 AM de lunes a viernes.\n"
                . "• **Tolerancia Máxima:** Hasta las 10:30 AM (con justificación en bitácora).\n"
                . "• **Contenido Requerido:** Tareas completadas ayer, objetivos de hoy e impedimentos técnicos.\n"
                . "• **Registro:** Disponible desde la sección [Mi Proyecto & Standup](/my-project).";
        }

        // 6. Escala de Score QA
        if (str_contains($q, 'escala') || str_contains($q, 'rubrica') || str_contains($q, 'criterio') || str_contains($q, 'nota') || str_contains($q, 'evaluacion') || str_contains($q, 'qa')) {
            return "**Estándares de Auditoría y Score QA (0 a 100):**\n\n"
                . "• **90 - 100:** Sobresaliente (Arquitectura modular, Clean Code y cobertura de pruebas).\n"
                . "• **80 - 89:** Muy Bueno (Cumplimiento de estándares técnicos y checklist QA).\n"
                . "• **70 - 79:** Aprobado Estándar (Cumplimiento base reglamentario).\n"
                . "• **Menor a 70:** Dictamen Observado (Requiere correcciones técnicas y re-evaluación).";
        }

        // 7. Búsqueda general por nombre de proyecto
        $term = '%' . trim($rawQuery) . '%';
        $foundProj = Project::whereRaw("({$scope['where']}) AND (name LIKE ? OR description LIKE ?)", array_merge($scope['params'], [$term, $term]), "updated_at DESC", 1);
        if (!empty($foundProj)) {
            return $this->formatProjectSummary($foundProj[0]);
        }

        // Fallback genérico
        return "**LABVA Intelligence:**\n\n"
            . "No encontré registros exactos para *'{$rawQuery}'*. Puedes consultar:\n\n"
            . "• Código de proyecto (ej: *'DEV-2026-001'*)\n"
            . "• Desarrolladores disponibles (*'desarrolladores libres'*)\n"
            . "• Ranking de score QA (*'mejores proyectos'*)\n"
            . "• Normativas (*'horarios de check-in'* o *'escala de qa'*)";
    }

    /** Returns the project visibility boundary for the authenticated role. */
    private function projectScope(User $user): array
    {
        if ($user->isAdmin()) {
            return ['where' => 'deleted_at IS NULL', 'params' => []];
        }

        if ($user->isDeveloper()) {
            return [
                'where' => 'deleted_at IS NULL AND developer_id = ?',
                'params' => [$user->id],
            ];
        }

        return ['where' => '1 = 0', 'params' => []];
    }

    private function formatProjectSummary(Project $project): string
    {
        $dev = $project->developer();
        $devName = $dev ? $dev->name : 'Sin asignar';
        $nota = $project->final_score ? "{$project->final_score}/100" : 'En auditoría QA';
        $tech = is_array($project->tech_stack) ? implode(', ', $project->tech_stack) : 'N/A';

        return "**Ficha de Proyecto: {$project->code}**\n\n"
            . "• **Nombre:** {$project->name}\n"
            . "• **Desarrollador Asignado:** {$devName} ({$project->sede})\n"
            . "• **Estado:** {$project->statusLabel()}\n"
            . "• **Score QA:** `{$nota}`\n"
            . "• **Stack:** `{$tech}`\n\n"
            . "[Abrir Ficha de Proyecto](/projects/{$project->id})";
    }

    /**
     * CoPilot Especializado para Ficha Técnica de Proyecto
     */
    public function askProjectCopilot(Project $project, string $message, string $mode = 'chat'): array
    {
        $dev = $project->developer();
        $devName = $dev ? $dev->name : 'No asignado';
        $tech = is_array($project->tech_stack) ? implode(', ', $project->tech_stack) : 'No especificado';
        $client = $project->clientName();
        $audit = null;
        $auditFindings = [];
        try {
            $audit = Database::getInstance()->fetch(
                'SELECT id, status, gate_status, score, criteria, feedback, release_version, commit_sha FROM quality_audits WHERE project_id = :project_id ORDER BY created_at DESC, id DESC LIMIT 1',
                ['project_id' => $project->id]
            );
            if ($audit) {
                $auditFindings = Database::getInstance()->fetchAll(
                    'SELECT severity, title, description, status FROM quality_audit_findings WHERE audit_id = :audit_id ORDER BY FIELD(severity, "critical", "high", "medium", "low", "info")',
                    ['audit_id' => $audit['id']]
                );
            }
        } catch (\Throwable) {
            // The audit tables may not be installed yet; never invent a result.
        }
        $score = $audit && $audit['score'] !== null ? $audit['score'] . '/100 (auditoría interna)' : 'Sin medición QA verificada';
        $auditContext = $audit
            ? json_encode(['status' => $audit['status'], 'gate_status' => $audit['gate_status'], 'score' => $audit['score'], 'criteria' => json_decode((string)$audit['criteria'], true), 'findings' => $auditFindings, 'feedback' => $audit['feedback'], 'release' => $audit['release_version'], 'commit' => $audit['commit_sha']], JSON_UNESCAPED_UNICODE)
            : 'No existe una auditoría registrada. No hay métricas, ejecuciones de herramientas ni hallazgos verificados disponibles.';

        $systemPrompt = <<<PROMPT
Eres CoPilot LABVA AI, el asistente técnico especializado de ingeniería de software de Devioz OS.
Tu función es auditar, analizar y brindar recomendaciones de arquitectura, seguridad (OWASP) y calidad (ISO/IEC 25010) para el proyecto en curso.

DATOS DEL PROYECTO ACTUAL:
- Código: {$project->code}
- Nombre: {$project->name}
- Cliente Corporativo: {$client}
- Sede: {$project->sede}
- Estado SDLC: {$project->statusLabel()} (Avance: {$project->progressPercentage()}%)
- Score QA Dictaminado: {$score}
- Stack Tecnológico: {$tech}
- Desarrollador Responsable: {$devName}
- Repositorio: {$project->repository_url}
- Demo URL: {$project->live_url}
- Último registro QA y evidencias (fuente de verdad): {$auditContext}

REGLAS DE RESPUESTA ESTRICTAS:
1. Responde siempre en español, con estructura clara y recomendaciones prácticas.
2. No inventes puntuaciones, ejecuciones de herramientas, coberturas, hallazgos, conformidad ni certificaciones. Usa solo datos explícitos del contexto.
3. Distingue hechos registrados de hipótesis y recomendaciones. Si falta evidencia, dilo y solicita la medición concreta necesaria.
4. ISO/IEC 25010 y OWASP Top 10 son marcos de evaluación; no declares que el proyecto está certificado por ellos.
5. CoPilot solo recomienda; nunca cambia el dictamen, resuelve hallazgos ni aprueba el Quality Gate.
PROMPT;

        $userPrompt = match ($mode) {
            'audit_iso25010' => "Revisa el registro de evidencias disponible para {$project->code} frente a ISO/IEC 25010:2023. Resume lo evaluado, identifica atributos sin evidencia y recomienda verificaciones. No emitas certificación ni inventes puntuaciones.",
            'security_owasp' => "Revisa el registro disponible frente a OWASP Top 10:2025 para {$project->code}. Separa categorías revisadas, hallazgos registrados y aspectos desconocidos. Propón pruebas defensivas sin afirmar que fueron ejecutadas.",
            'sonar_qa'       => "Informa si existe una ejecución SonarQube en las evidencias disponibles para {$project->code}. Si no existe, no generes métricas ni PASS: enumera cómo conectar la ejecución real y qué reporte adjuntar.",
            'release_notes'  => "Prepara un borrador de release para {$project->code} usando únicamente datos registrados. Marca explícitamente datos pendientes (versión, commit, pruebas, seguridad, despliegue) y no declares certificaciones ni entregas realizadas sin evidencia.",
            default          => !empty($message) ? $message : "Brinda un diagnóstico general del estado de arquitectura y recomendaciones para la siguiente etapa de este proyecto."
        };

        // 1. Intentar con Groq API
        if (!empty($this->groqKey)) {
            $groqReply = $this->callGroq($userPrompt, $systemPrompt);
            if ($groqReply !== null && trim($groqReply) !== '') {
                return [
                    'reply'    => $this->sanitizeText($groqReply),
                    'provider' => 'groq',
                    'model'    => $this->groqModel,
                ];
            }
        }

        // 2. Intentar con Gemini API
        if (!empty($this->geminiKey)) {
            $geminiReply = $this->callGemini($userPrompt, $systemPrompt);
            if ($geminiReply !== null && trim($geminiReply) !== '') {
                return [
                    'reply'    => $this->sanitizeText($geminiReply),
                    'provider' => 'gemini',
                    'model'    => $this->geminiModel,
                ];
            }
        }

        // 3. Fallback Heurístico Local Especializado
        $localReport = $this->generateLocalCopilotReport($project, $mode, $message);
        return [
            'reply'    => $this->sanitizeText($localReport),
            'provider' => 'local',
            'model'    => 'Devioz Heuristic Engine',
        ];
    }

    /**
     * Motor Heurístico Local de Dictamen y Auditoría para Proyectos (0 caídas)
     */
    private function generateLocalCopilotReport(Project $project, string $mode, string $userQuery): string
    {
        $audit = null;
        try {
            $audit = Database::getInstance()->fetch(
                'SELECT id, status, gate_status, score, release_version, commit_sha FROM quality_audits WHERE project_id = :project_id ORDER BY created_at DESC, id DESC LIMIT 1',
                ['project_id' => $project->id]
            );
        } catch (\Throwable) {
            // Without the audit storage, there are no verified QA results to report.
        }

        $reply = "**CoPilot Devioz · análisis basado en registros**\n\n"
            . '**Proyecto:** ' . $project->name . ' (`' . $project->code . "`)\n\n";
        if ($audit) {
            $reply .= '**Auditoría QA #' . $audit['id'] . ':** estado `' . $audit['status'] . '`, compuerta `' . $audit['gate_status'] . '`, score ' . ($audit['score'] === null ? 'no disponible' : $audit['score'] . '/100') . ".\n\n"
                . '**Release / commit:** ' . ($audit['release_version'] ?: 'sin informar') . ' / `' . ($audit['commit_sha'] ?: 'sin informar') . "`.\n\n";
        } else {
            $reply .= "No existe una auditoría QA registrada para este proyecto.\n\n";
        }

        return $reply
            . "El motor local no ejecuta pruebas, SonarQube, SAST/DAST ni análisis ISO/OWASP. No genera métricas ni PASS. CoPilot puede proponer verificaciones, pero la corrección debe validarse ejecutando la herramienta y registrando una nueva auditoría.\n\n"
            . 'Consulta: ' . ($userQuery !== '' ? $userQuery : 'No se recibió una pregunta específica.');
    }
    /**
     * Limpia emojis residuales para cumplir la política de cero emojis
     */
    private function sanitizeText(string $text): string
    {
        $regex = '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F1E0}-\x{1F1FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{FE00}-\x{FE0F}\x{1F900}-\x{1F9FF}\x{1FA70}-\x{1FAFF}]/u';
        return preg_replace($regex, '', $text) ?? $text;
    }
}


