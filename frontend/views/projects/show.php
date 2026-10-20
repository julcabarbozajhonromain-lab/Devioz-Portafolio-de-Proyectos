<?php

declare(strict_types=1);

/** @var \App\Models\Project $project */
/** @var array $milestones */
/** @var array $deliveries */
/** @var array $evaluations */
/** @var array $checkIns */
/** @var array $comments */
/** @var array $files */

$developer = $project->developer();
$assignedBy = $project->assignedBy();
$reviewedBy = $project->reviewedBy();

// Cálculo de Métricas de Calidad ISO/IEC 25010 & OWASP Top 10
$latestQualityAudit = $latestQualityAudit ?? null;
$isCertified = $latestQualityAudit && $latestQualityAudit['gate_status'] === 'passed' && $latestQualityAudit['status'] === 'passed';
$baseScore = $latestQualityAudit['score'] ?? null;
$isoMetrics = [];
$owaspChecklist = [];?>

<!-- Encabezado Corporativo del Proyecto (Estilo Devioz) -->
<div class="card-custom p-4 mb-4 project-header-card">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                <span class="badge font-monospace fw-bold" style="background: rgba(31, 41, 55, 0.08); color: #1F2937; border: 1px solid #DBE2E8; font-size: 0.85rem;"><?= e($project->code) ?></span>
                <span class="ui-client-badge py-1 px-2" style="font-size: 0.82rem;">
                    <i class="fa-solid fa-briefcase me-1"></i>
                    <span><?= e($project->clientName()) ?></span>
                </span>
                <?= $project->statusBadge() ?>
                <span class="badge-sede"><?= e($project->sede) ?></span>

                <?php if ($isCertified): ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2" style="font-size: 0.78rem;">
                        <i class="fa-solid fa-certificate me-1"></i> Auditoría QA aprobada
                    </span>
                <?php endif; ?>

                <?php if ($project->final_score): ?>
                    <span class="badge bg-success whitespace-nowrap">QA Score: <?= $project->final_score ?><?= (float)$project->final_score > 10 ? '/100' : '/10' ?></span>
                <?php endif; ?>
            </div>
            <h2 class="fw-bold mb-1 project-hero-title" style="color: #1F2937; font-size: 1.55rem; letter-spacing: -0.01em;"><?= e($project->name) ?></h2>
            <div class="text-secondary small d-flex align-items-center gap-2 mt-1 flex-wrap">
                <span>Fecha de Asignación: <strong class="text-dark"><?= format_date($project->assigned_at) ?></strong></span>
                <span>&bull;</span>
                <span>Categoría: <strong class="text-dark"><?= e($project->category ?: 'Enterprise Consulting') ?></strong></span>
                <span>&bull;</span>
                <span>Normativa: <strong class="text-dark">ISO 25010 / ISO 27001 / OWASP Top 10</strong></span>
            </div>
        </div>

        <div class="d-flex gap-2 flex-wrap align-items-center">
            <!-- Botón de Exportación de Dossier para el Cliente (Print/PDF) -->
            <button type="button" class="btn btn-ghost btn-sm d-flex align-items-center gap-1" onclick="window.print()" title="Imprimir o Exportar Dossier de Auditoría para el Cliente">
                <i class="fa-solid fa-file-pdf text-danger"></i>
                <span>Dossier Auditoría</span>
            </button>

            <?php if ($project->repository_url): ?>
                <a href="<?= e($project->repository_url) ?>" target="_blank" class="btn btn-ghost btn-sm d-flex align-items-center gap-1" title="Ver código fuente en GitHub">
                    <i class="fa-brands fa-github text-dark"></i>
                    <span>Código Fuente</span>
                </a>
            <?php endif; ?>

            <?php if ($project->live_url): ?>
                <a href="<?= e($project->live_url) ?>" target="_blank" class="btn btn-brand btn-sm d-flex align-items-center gap-1" title="Abrir despliegue en producción">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    <span>Demo en Vivo</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Columna Principal: Contenido, Métricas Normativas y Hitos -->
    <div class="col-lg-8">

        <!-- ========================================================================= -->
        <section class="card-custom p-4 mb-4">
            <h5 class="fw-bold mb-1">Auditoría de calidad y seguridad</h5>
            <p class="text-secondary small">ISO/IEC 25010:2023 · OWASP Top 10:2025 · Dictamen interno basado en evidencias</p>
            <div class="alert alert-info small">Esta revisión interna no es una certificación ISO ni garantiza ausencia de defectos.</div>
            <?php if (!$latestQualityAudit): ?>
                <p class="text-secondary mb-0">No existe todavía un dictamen QA basado en evidencias para este proyecto.</p>
            <?php else: ?>
                <div class="row g-3 mb-3"><div class="col-md-4"><span class="small text-secondary">Compuerta</span><div class="fw-bold"><?= e($latestQualityAudit['gate_status']) ?></div></div><div class="col-md-4"><span class="small text-secondary">Score documentado</span><div class="fw-bold"><?= $latestQualityAudit['score'] !== null ? number_format((float)$latestQualityAudit['score'], 1) . '/100' : 'No disponible' ?></div></div><div class="col-md-4"><span class="small text-secondary">Release / commit</span><div><?= e($latestQualityAudit['release_version'] ?: 'Sin versión') ?> · <code><?= e($latestQualityAudit['commit_sha'] ?: 'sin SHA') ?></code></div></div></div>
                <h6 class="fw-bold">ISO/IEC 25010:2023 · atributos evaluados</h6><div class="table-responsive mb-3"><table class="table table-sm"><thead><tr><th>Atributo</th><th>Valoración</th><th>Evidencia</th></tr></thead><tbody><?php foreach (($latestQualityAudit['criteria']['iso'] ?? []) as $item): ?><tr><td><?= e($item['label']) ?></td><td><?= isset($item['rating']) && $item['rating'] !== null ? (int)$item['rating'] . '/4' : 'Sin evaluar' ?></td><td><?= e($item['evidence'] ?: 'Sin evidencia') ?></td></tr><?php endforeach; ?></tbody></table></div>
                <h6 class="fw-bold">OWASP Top 10</h6><div class="table-responsive mb-3"><table class="table table-sm"><thead><tr><th>Categoría</th><th>Resultado</th><th>Evidencia</th></tr></thead><tbody><?php foreach (($latestQualityAudit['criteria']['owasp']['items'] ?? []) as $item): ?><tr><td><?= e($item['label']) ?></td><td><?= e($item['result']) ?></td><td><?= e($item['evidence'] ?: 'Sin evidencia') ?></td></tr><?php endforeach; ?></tbody></table></div>
                <?php if (!empty($latestQualityAudit['findings'])): ?><h6 class="fw-bold">Hallazgos abiertos</h6><ul><?php foreach ($latestQualityAudit['findings'] as $finding): ?><li><strong><?= e(strtoupper($finding['severity'])) ?> · <?= e($finding['title']) ?>:</strong> <?= e($finding['description']) ?> (<?= e($finding['status']) ?>)</li><?php endforeach; ?></ul><?php endif; ?>
                <p class="small text-secondary mb-0"><?= nl2br(e($latestQualityAudit['feedback'] ?: 'Sin conclusión adicional.')) ?></p>
            <?php endif; ?>
        </section>
        <!-- 2. DESCRIPCIÓN GENERAL Y PATRONES DE ARQUITECTURA                         -->
        <!-- ========================================================================= -->
        <div class="card-custom p-4 mb-4">
            <h5 class="fw-bold mb-3" style="color: #1F2937;">Descripción y Alcance Ejecutivo</h5>
            <div class="text-secondary"><?= render_markdown($project->description ?: 'No se ha provisto una descripción detallada.') ?></div>

            <!-- Stack Tecnológico -->
            <div class="mt-4">
                <h6 class="fw-bold small mb-2" style="color: #1F2937;">Tecnologías y Patrones Aplicados</h6>
                <div class="d-flex flex-wrap gap-2">
                    <?php if (!empty($project->tech_stack) && is_array($project->tech_stack)): ?>
                        <?php foreach ($project->tech_stack as $tech): ?>
                            <span class="tech-chip"><?= e($tech) ?></span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="text-secondary small">No especificado.</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Video Demostrativo Directo Inline -->
            <?php if (!empty($project->video_url)): ?>
                <div class="mt-4 pt-3 border-top" style="border-color: #DBE2E8 !important;">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="fw-bold small mb-0 d-flex align-items-center gap-2" style="color: #1F2937;">
                            <svg width="18" height="18" class="text-danger" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            <span>Video de Sustentación / Demo del Software</span>
                        </h6>
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 font-monospace" style="font-size: 0.68rem;">REPRODUCTOR DIRECTO</span>
                    </div>

                    <div class="ratio ratio-16x9 rounded-3 overflow-hidden shadow-sm border" style="background: #000; border-color: #DBE2E8 !important;">
                        <iframe
                            src="<?= e(getEmbedUrl($project->video_url, false)) ?>"
                            title="<?= e($project->name) ?>"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen
                            loading="lazy">
                        </iframe>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- ========================================================================= -->
        <!-- 3. GALERÍA DE CAPTURAS DE PANTALLA Y ENTORNOS                             -->
        <!-- ========================================================================= -->
        <?php
            $gallery = is_array($project->gallery_images) ? $project->gallery_images : (json_decode($project->gallery_images ?? '[]', true) ?: []);
            if (!empty($gallery)):
        ?>
            <div class="card-custom p-4 mb-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold mb-0 d-flex align-items-center gap-2" style="color: #1F2937;">
                        <i class="fa-solid fa-images text-emerald"></i>
                        <span>Capturas del Sistema y Entornos de Ejecución</span>
                    </h5>
                    <span class="badge bg-secondary font-monospace" style="font-size: 0.68rem;"><?= count($gallery) ?> Vistas</span>
                </div>
                <div class="row g-3">
                    <?php foreach ($gallery as $imgUrl): ?>
                        <div class="col-sm-6">
                            <a href="<?= e(asset($imgUrl)) ?>" target="_blank" rel="noopener noreferrer" class="d-block rounded-3 overflow-hidden border border-secondary border-opacity-25 hover-lift position-relative" style="height: 190px;" title="Ver captura en tamaño completo">
                                <img src="<?= e(asset($imgUrl)) ?>" alt="Captura de Pantalla" class="w-100 h-100" style="object-fit: cover;" loading="lazy">
                                <div class="position-absolute bottom-0 start-0 end-0 p-2 text-center text-white small" style="background: rgba(0,0,0,0.6); backdrop-filter: blur(2px); font-size: 0.72rem;">
                                    <i class="fa-solid fa-up-right-from-square me-1"></i> Ampliar captura
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- ========================================================================= -->
        <!-- 4. ASISTENTE COPILOT LABVA AI • CALIDAD, APPSEC & GOBERNANZA SDLC        -->
        <!-- ========================================================================= -->
        <div class="card-custom p-4 mb-4" id="copilotProjectSuite" style="border: 1px solid #DBE2E8; background: #FFFFFF;">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(22, 163, 74, 0.15)); color: #10B981;">
                        <i class="fa-solid fa-wand-magic-sparkles fs-5"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="fw-bold mb-0" style="color: #1F2937;">CoPilot LABVA AI</h5>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" style="font-size: 0.65rem;">ASISTENTE SDLC & QA</span>
                        </div>
                        <div class="text-secondary small" style="font-size: 0.74rem;">Auditoría heurística, gobernanza ISO 25010 y mitigación OWASP en tiempo real.</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge font-monospace" style="background: rgba(22, 163, 74, 0.1); color: #438A00; border: 1px solid rgba(22, 163, 74, 0.25); font-size: 0.68rem;">
                        <i class="fa-solid fa-circle text-success me-1" style="font-size: 0.5rem;"></i> Motor Activo
                    </span>
                    <button type="button" class="btn btn-sm btn-ghost p-1" id="copilotToggleCollapse" title="Minimizar / Expandir CoPilot" aria-label="Minimizar panel">
                        <i class="fa-solid fa-chevron-up text-secondary" id="copilotToggleIcon"></i>
                    </button>
                </div>
            </div>

            <div id="copilotSuiteBody">
                <!-- Prompts Rápidos (Chips de Acciones Heurísticas con 1 Clic) -->
                <div class="mb-3">
                    <div class="text-secondary small mb-2 fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.03em; text-transform: uppercase;">
                        Acciones Heurísticas con 1 Clic:
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="copilot-chip-btn" data-copilot-mode="audit_iso25010">
                            <i class="fa-solid fa-chart-radar text-primary"></i>
                            <span>Auditoría ISO 25010: marco de referencia</span>
                        </button>
                        <button type="button" class="copilot-chip-btn" data-copilot-mode="security_owasp">
                            <i class="fa-solid fa-shield-halved text-success"></i>
                            <span>Mitigación OWASP Top 10</span>
                        </button>
                        <button type="button" class="copilot-chip-btn" data-copilot-mode="sonar_qa">
                            <i class="fa-solid fa-code text-warning"></i>
                            <span>Quality Gate SonarQube</span>
                        </button>
                        <button type="button" class="copilot-chip-btn" data-copilot-mode="release_notes">
                            <i class="fa-solid fa-file-contract text-info"></i>
                            <span>Dossier de Release al Cliente</span>
                        </button>
                    </div>
                </div>

                <!-- Área de Visualización del Dictamen CoPilot -->
                <div class="rounded p-3 mb-3 position-relative" id="copilotOutputContainer" style="background: #F8FAFB; border: 1px solid #E0E8ED; min-height: 120px; max-height: 480px; overflow-y: auto;">
                    <div id="copilotEmptyState" class="text-center py-4 text-secondary">
                        <i class="fa-solid fa-brain fs-3 mb-2 opacity-50" style="color: #10B981;"></i>
                        <div class="fw-semibold small text-dark">CoPilot LABVA AI listo para asistir</div>
                        <div class="small text-muted" style="font-size: 0.75rem;">Haz clic en una de las acciones arriba o escribe una consulta técnica específica para este proyecto.</div>
                    </div>
                    <div id="copilotLoadingState" class="text-center py-4 d-none">
                        <div class="spinner-border spinner-border-sm text-primary mb-2" role="status" style="color: #10B981 !important;"></div>
                        <div class="small text-secondary fw-medium" id="copilotLoadingMsg">Procesando diagnóstico de ingeniería de software con LABVA AI...</div>
                    </div>
                    <div id="copilotResultContent" class="copilot-markdown-content d-none"></div>
                </div>

                <!-- Barra de Acciones del Resultado -->
                <div class="d-none align-items-center justify-content-between mb-3 pt-2 border-top" id="copilotResultActions">
                    <span class="small text-muted font-monospace" style="font-size: 0.68rem;" id="copilotEngineInfo">Modelo: Devioz Heuristic Engine</span>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-ghost border" id="copilotCopyBtn" title="Copiar reporte al portapapeles" style="font-size: 0.75rem; color: #1F2937;">
                            <i class="fa-regular fa-copy me-1"></i> <span id="copilotCopyBtnText">Copiar</span>
                        </button>
                        <button type="button" class="btn btn-sm btn-ghost border" id="copilotInsertCommentBtn" title="Insertar en observaciones técnicas" style="font-size: 0.75rem; color: #10B981;">
                            <i class="fa-solid fa-arrow-down-to-bracket me-1"></i> Insertar en Observaciones
                        </button>
                    </div>
                </div>

                <!-- Formulario Interactivo de Consulta -->
                <form id="copilotForm" class="d-flex gap-2">
                    <div class="position-relative flex-grow-1">
                        <input type="text" id="copilotInput" class="form-control form-control-custom w-100" placeholder="Pregunta sobre arquitectura, pruebas o gobernanza de este proyecto..." autocomplete="off">
                    </div>
                    <button type="submit" class="btn btn-brand btn-sm d-flex align-items-center gap-1" id="copilotSubmitBtn">
                        <span>Consultar</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- 5. HITOS Y AVANCE DEL PROYECTO (SDLC TIMELINE)                            -->
        <!-- ========================================================================= -->
        <div class="card-custom p-4 mb-4">
            <h5 class="fw-bold mb-3" style="color: #1F2937;">Cronograma de Hitos y Entregables (SDLC)</h5>
            <div class="milestone-timeline">
                <?php foreach ($milestones as $m): ?>
                    <div class="milestone-item <?= $m->status ?>">
                        <div class="milestone-dot"></div>
                        <div class="card-custom p-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold small" style="color: #1F2937;"><?= e($m->name) ?></span>
                                <span class="small text-secondary"><?= format_date($m->due_date) ?></span>
                            </div>
                            <p class="text-secondary small mb-0"><?= e($m->description) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- 5. FEEDBACK Y OBSERVACIONES TÉCNICAS                                      -->
        <!-- ========================================================================= -->
        <div class="card-custom p-4">
            <h5 class="fw-bold mb-3" style="color: #1F2937;">Feedback y Observaciones de Arquitectura</h5>
            <div class="d-flex flex-column gap-3 mb-4">
                <?php foreach ($comments as $com): ?>
                    <?php $u = $com->user(); ?>
                    <div class="p-3 rounded" style="background: #F8FAFB; border: 1px solid #DBE2E8;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold small" style="color: #1F2937;"><?= e($u ? $u->name : 'Usuario') ?></span>
                            <span class="text-secondary small"><?= format_date($com->created_at, 'd/m/Y H:i') ?></span>
                        </div>
                        <p class="text-secondary small mb-0"><?= nl2br(e($com->content)) ?></p>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($comments)): ?>
                    <p class="text-secondary small mb-0">No hay observaciones registradas en este proyecto aún.</p>
                <?php endif; ?>
            </div>

            <!-- Formulario de Feedback -->
            <form action="<?= url('/my-project/comment') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="project_id" value="<?= $project->id ?>">
                <div class="mb-3">
                    <textarea name="content" rows="2" class="form-control form-control-custom w-100" placeholder="Escribe una observación técnica sobre la entrega..." required></textarea>
                </div>
                <button type="submit" class="btn btn-brand btn-sm">Publicar Observación</button>
            </form>
        </div>
    </div>

    <!-- Columna Lateral: Desarrollador, Dictamen QA y Sellos de Garantía -->
    <div class="col-lg-4">
        <!-- Tarjeta del Desarrollador Responsable -->
        <div class="card-custom p-3 mb-4">
            <h6 class="fw-bold small mb-3" style="color: #1F2937;">Desarrollador Responsable</h6>
            <div class="d-flex align-items-center gap-3">
                <img src="<?= e($developer ? $developer->avatarUrl() : '') ?>" class="rounded-circle" width="46" height="46" style="object-fit: cover;" loading="lazy" alt="Avatar">
                <div>
                    <div class="fw-bold" style="color: #1F2937;"><?= e($developer ? $developer->name : 'No asignado') ?></div>
                    <div class="text-secondary small"><?= e($developer ? $developer->email : '') ?></div>
                    <span class="badge-sede mt-1"><?= e($developer ? $developer->sede : 'Sin sede') ?></span>
                </div>
            </div>
        </div>

        <div class="card-custom p-3 mb-4">
            <h6 class="fw-bold mb-2">Estado del Quality Gate</h6>
            <?php if ($latestQualityAudit): ?><div class="fw-semibold"><?= e($latestQualityAudit['gate_status']) ?></div><div class="small text-secondary">Dictamen interno · <?= format_date($latestQualityAudit['created_at']) ?></div><?php else: ?><p class="small text-secondary mb-0">No hay una auditoría QA registrada.</p><?php endif; ?>
        </div>
        <!-- Acciones de Administración (Si es Admin) -->
        <?php if (auth()->isAdmin()): ?>
            <div class="card-custom p-3 mb-4">
                <h6 class="fw-bold small mb-3" style="color: #1F2937;">Dictamen de Calidad & QA Lead</h6>

                <!-- Aprobar Proyecto -->
                <form action="<?= url("/admin/projects/{$project->id}/approve") ?>" method="POST" class="mb-2">
                    <?= csrf_field() ?>
                    <div class="mb-2">
                        <label class="form-label-custom small">Score de Calidad QA (0 - 100)</label>
                        <div class="small text-secondary mb-2">La aprobación utiliza el score del último Quality Gate aprobado; no se puede establecer manualmente.</div>
                    </div>
                    <button type="submit" class="btn btn-brand btn-sm w-100 justify-content-center" <?= !$isCertified ? 'disabled title="Se requiere un Quality Gate aprobado"' : '' ?> data-confirm="¿Confirmas aprobar el proyecto según el dictamen QA registrado?">
                        Aprobar proyecto con Quality Gate aprobado
                    </button>
                </form>

                <!-- Observar / Rechazar Proyecto -->
                <button type="button" class="btn btn-ghost text-danger btn-sm w-100 justify-content-center mt-2" data-bs-toggle="collapse" data-bs-target="#rejectCollapse">
                    Observar / Rechazar
                </button>

                <div class="collapse mt-2" id="rejectCollapse">
                    <form action="<?= url("/admin/projects/{$project->id}/reject") ?>" method="POST">
                        <?= csrf_field() ?>
                        <div class="mb-2">
                            <label class="form-label-custom small">Motivo de la Observación</label>
                            <textarea name="rejection_reason" rows="3" class="form-control form-control-custom py-1" placeholder="Describe los cambios requeridos..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger btn-sm w-100">Confirmar Observación</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- Datos del Proyecto -->
        <div class="card-custom p-3">
            <h6 class="fw-bold small mb-3" style="color: #1F2937;">Información del Registro</h6>
            <div class="d-flex flex-column gap-2 small">
                <div class="d-flex justify-content-between">
                    <span class="text-secondary">Empresa / Cliente:</span>
                    <span class="fw-semibold text-truncate" style="color: #10B981; max-width: 170px;"><?= e($project->clientName()) ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-secondary">Fecha de Asignación:</span>
                    <span class="text-dark fw-medium"><?= format_date($project->assigned_at) ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-secondary">Administrador Asignado:</span>
                    <span class="text-dark fw-medium"><?= e($assignedBy ? $assignedBy->name : 'Tech Lead TI') ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-secondary">Visitas en Galería:</span>
                    <span class="text-success fw-bold"><?= (int)$project->views_count ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- INICIALIZACIÓN DEL RADAR CHART ISO/IEC 25010 (SPIDER CHART ENTERPRISE)    -->
<!-- ========================================================================= -->
<script>
(function() {
    function initIsoRadar() {
        const canvas = document.getElementById('isoRadarCanvas');
        if (!canvas || typeof Chart === 'undefined') return;

        const isoLabels = <?= json_encode(array_column($isoMetrics, 'name')) ?>;
        const isoScores = <?= json_encode(array_map(fn($m) => (float)$m['score'], $isoMetrics)) ?>;
        const benchmark = [100, 100, 100, 100, 100, 100, 100, 100];

        const shortLabels = [
            'Funcional',
            'Rendimiento',
            'APIs & Compat',
            'Usabilidad',
            'Fiabilidad',
            'Seguridad',
            'Mantenibilidad',
            'PaaS Ready'
        ];

        const radarChart = new Chart(canvas, {
            type: 'radar',
            data: {
                labels: shortLabels,
                datasets: [
                    {
                        label: 'Score Evaluado',
                        data: isoScores,
                        fill: true,
                        backgroundColor: 'rgba(16, 185, 129, 0.22)',
                        borderColor: '#10B981',
                        pointBackgroundColor: '#10B981',
                        pointBorderColor: '#ffffff',
                        pointHoverBackgroundColor: '#ffffff',
                        pointHoverBorderColor: '#10B981',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        borderWidth: 2
                    },
                    {
                        label: 'Meta Enterprise',
                        data: benchmark,
                        fill: false,
                        borderColor: 'rgba(22, 163, 74, 0.65)',
                        borderDash: [4, 4],
                        pointRadius: 0,
                        borderWidth: 1.5
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        angleLines: {
                            color: 'rgba(219, 226, 232, 0.8)'
                        },
                        grid: {
                            color: 'rgba(219, 226, 232, 0.6)'
                        },
                        pointLabels: {
                            font: {
                                family: '"Open Sans", sans-serif',
                                size: 10,
                                weight: '600'
                            },
                            color: '#5E6C84'
                        },
                        suggestedMin: 40,
                        suggestedMax: 100,
                        ticks: {
                            stepSize: 20,
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1F2937',
                        titleFont: { family: '"Open Sans", sans-serif', size: 11, weight: 'bold' },
                        bodyFont: { family: '"Open Sans", sans-serif', size: 11 },
                        padding: 8,
                        cornerRadius: 6,
                        callbacks: {
                            title: function(items) {
                                const idx = items[0].dataIndex;
                                return isoLabels[idx] || shortLabels[idx];
                            },
                            label: function(context) {
                                return ` ${context.dataset.label}: ${context.raw}%`;
                            }
                        }
                    }
                }
            }
        });

        // Micro-interacción: Resaltar punto al pasar mouse por las tarjetas laterales y alternar giro en click/touch
        document.querySelectorAll('.iso-dimension-card').forEach((card) => {
            card.addEventListener('mouseenter', function() {
                const idx = parseInt(this.getAttribute('data-iso-idx'));
                if (!isNaN(idx) && radarChart) {
                    radarChart.setActiveElements([
                        { datasetIndex: 0, index: idx }
                    ]);
                    radarChart.update();
                }
            });
            card.addEventListener('mouseleave', function() {
                if (radarChart) {
                    radarChart.setActiveElements([]);
                    radarChart.update();
                }
            });
            card.addEventListener('click', function() {
                this.classList.toggle('flipped');
            });
        });
    }

    if (document.readyState === 'complete') {
        initIsoRadar();
        initProjectCopilot();
    } else {
        window.addEventListener('load', () => {
            initIsoRadar();
            initProjectCopilot();
        });
    }

    /**
     * =========================================================================
     * INICIALIZADOR DE COPILOT LABVA AI EN FICHA TÉCNICA
     * =========================================================================
     */
    function initProjectCopilot() {
        const suiteBody = document.getElementById('copilotSuiteBody');
        const toggleBtn = document.getElementById('copilotToggleCollapse');
        const toggleIcon = document.getElementById('copilotToggleIcon');
        const chips = document.querySelectorAll('.copilot-chip-btn');
        const form = document.getElementById('copilotForm');
        const input = document.getElementById('copilotInput');
        const submitBtn = document.getElementById('copilotSubmitBtn');
        const emptyState = document.getElementById('copilotEmptyState');
        const loadingState = document.getElementById('copilotLoadingState');
        const resultContent = document.getElementById('copilotResultContent');
        const actionsBar = document.getElementById('copilotResultActions');
        const engineInfo = document.getElementById('copilotEngineInfo');
        const copyBtn = document.getElementById('copilotCopyBtn');
        const copyBtnText = document.getElementById('copilotCopyBtnText');
        const insertCommentBtn = document.getElementById('copilotInsertCommentBtn');

        if (!form || !resultContent) return;

        let currentCopilotRaw = '';

        // Minimizar / Expandir panel
        if (toggleBtn && suiteBody && toggleIcon) {
            toggleBtn.addEventListener('click', () => {
                const isHidden = suiteBody.classList.toggle('d-none');
                toggleIcon.className = isHidden ? 'fa-solid fa-chevron-down text-secondary' : 'fa-solid fa-chevron-up text-secondary';
            });
        }

        // Petición al CoPilot
        function queryCopilot(mode, message) {
            if (emptyState) emptyState.classList.add('d-none');
            if (resultContent) resultContent.classList.add('d-none');
            if (actionsBar) actionsBar.classList.replace('d-flex', 'd-none') || actionsBar.classList.add('d-none');
            if (loadingState) loadingState.classList.remove('d-none');

            // Deshabilitar controles
            if (submitBtn) submitBtn.disabled = true;
            chips.forEach(c => c.disabled = true);

            fetch('<?= url("/api/projects/{$project->id}/copilot") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '<?= csrf_token() ?>'
                },
                body: JSON.stringify({ mode, message })
            })
            .then(res => {
                if (!res.ok) throw new Error('Error de comunicación con el motor AI: ' + res.status);
                return res.json();
            })
            .then(data => {
                if (!data.success) throw new Error(data.error || 'Respuesta vacía');
                currentCopilotRaw = data.reply || '';

                if (loadingState) loadingState.classList.add('d-none');
                if (resultContent) {
                    resultContent.innerHTML = renderMarkdown(currentCopilotRaw);
                    resultContent.classList.remove('d-none');
                }
                if (actionsBar) {
                    actionsBar.classList.remove('d-none');
                    actionsBar.classList.add('d-flex');
                }
                if (engineInfo) {
                    engineInfo.textContent = `Modelo: ${data.model || 'Devioz OS'} (${data.provider || 'local'})`;
                }
            })
            .catch(err => {
                console.error('[CoPilot AI Error]:', err);
                if (loadingState) loadingState.classList.add('d-none');
                if (resultContent) {
                    resultContent.innerHTML = `
                        <div class="alert alert-danger py-2 small mb-0">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i>
                            <strong>No se pudo procesar la solicitud:</strong> ${err.message || 'Error desconocido'}
                        </div>
                    `;
                    resultContent.classList.remove('d-none');
                }
            })
            .finally(() => {
                if (submitBtn) submitBtn.disabled = false;
                chips.forEach(c => c.disabled = false);
            });
        }

        // Eventos en chips heurísticos
        chips.forEach(chip => {
            chip.addEventListener('click', function() {
                chips.forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                const mode = this.getAttribute('data-copilot-mode');
                queryCopilot(mode, '');
            });
        });

        // Envío de formulario interactivo
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const val = input.value.trim();
            if (!val) return;
            chips.forEach(c => c.classList.remove('active'));
            queryCopilot('chat', val);
            input.value = '';
        });

        // Botón copiar al portapapeles
        if (copyBtn && copyBtnText) {
            copyBtn.addEventListener('click', () => {
                if (!currentCopilotRaw) return;
                navigator.clipboard.writeText(currentCopilotRaw).then(() => {
                    copyBtnText.textContent = '¡Copiado!';
                    copyBtn.classList.add('text-success');
                    setTimeout(() => {
                        copyBtnText.textContent = 'Copiar';
                        copyBtn.classList.remove('text-success');
                    }, 2000);
                });
            });
        }

        // Botón insertar en observaciones
        if (insertCommentBtn) {
            insertCommentBtn.addEventListener('click', () => {
                if (!currentCopilotRaw) return;
                const commentArea = document.querySelector('textarea[name="content"]');
                if (commentArea) {
                    const prefix = commentArea.value.trim() ? commentArea.value.trim() + "\n\n" : "";
                    commentArea.value = prefix + "[Recomendación CoPilot LABVA AI]:\n" + currentCopilotRaw;
                    commentArea.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    commentArea.focus();
                    commentArea.style.borderColor = '#10B981';
                    setTimeout(() => { commentArea.style.borderColor = ''; }, 1500);
                }
            });
        }

        // Renderizador simple y seguro de Markdown
        function renderMarkdown(md) {
            if (!md) return '';
            let s = md.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            // Bloques de código
            s = s.replace(/```([a-z0-9_-]*)\n([\s\S]*?)```/gi, (m, lang, code) => `<pre><code>${code}</code></pre>`);
            // Código en línea
            s = s.replace(/`([^`]+)`/g, '<code>$1</code>');
            // Títulos
            s = s.replace(/^### (.*$)/gim, '<h6 class="fw-bold mt-3 mb-1" style="color: #1F2937;">$1</h6>');
            s = s.replace(/^## (.*$)/gim, '<h5 class="fw-bold mt-3 mb-2" style="color: #1F2937;">$1</h5>');
            s = s.replace(/^# (.*$)/gim, '<h4 class="fw-bold mt-3 mb-2" style="color: #1F2937;">$1</h4>');
            // Negritas
            s = s.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
            // Cursivas
            s = s.replace(/\*([^*]+)\*/g, '<em>$1</em>');
            // Viñetas
            s = s.replace(/^[•\-] (.*$)/gim, '<li class="mb-1">$1</li>');
            s = s.replace(/((?:<li class="mb-1">.*<\/li>\s*)+)/g, '<ul class="ps-3 mb-2">$1</ul>');
            // Saltos de línea
            s = s.replace(/\n\n/g, '<p class="mb-2"></p>');
            s = s.replace(/\n/g, '<br>');
            return s;
        }
    }
})();
</script>

