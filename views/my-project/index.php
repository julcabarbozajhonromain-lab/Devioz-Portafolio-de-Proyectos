<?php

declare(strict_types=1);

/** @var \App\Models\Project $project */
/** @var array $milestones */
/** @var array $deliveries */
/** @var array $evaluations */
/** @var array $checkIns */
/** @var array $comments */
/** @var int $progress */
?>

<!-- Encabezado Corporativo del Proyecto (Estilo Bitrix24) -->
<div class="card-custom p-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                <span class="badge font-monospace fw-bold" style="background: rgba(23, 43, 77, 0.08); color: #172B4D; border: 1px solid #DBE2E8; font-size: 0.85rem;"><?= e($project->code) ?></span>
                <?= $project->statusBadge() ?>
                <span class="badge-sede"><?= e($project->sede) ?></span>
            </div>
            <h3 class="fw-bold mb-1 project-hero-title" style="color: #172B4D; font-size: 1.45rem; letter-spacing: -0.01em;"><?= e($project->name) ?></h3>
            <div class="text-secondary small">
                <span>Cliente: <strong class="text-dark"><?= e($project->clientName()) ?></strong></span>
                <span class="mx-2">&bull;</span>
                <span>Asignado el: <strong><?= format_date($project->assigned_at) ?></strong></span>
            </div>
        </div>
        <div class="text-md-end p-3 rounded-3" style="background: #F4F7F9; border: 1px solid #E0E8ED;">
            <span class="text-secondary small d-block">Avance Ponderado:</span>
            <div class="fs-4 fw-bold" style="color: #00ACE3;"><?= $progress ?>%</div>
        </div>
    </div>

    <!-- Barra de Progreso Bitrix -->
    <div class="progress" style="height: 6px; border-radius: 999px; background: #E0E8ED;">
        <div class="progress-bar" style="width: <?= $progress ?>%; background: #00ACE3;"></div>
    </div>

    <!-- Ficha de Contacto Directo del Lead / Administrador Asignado (Estilo Bitrix24) -->
    <div class="mt-3 pt-3 border-top d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3" style="border-color: #E0E8ED !important;">
        <div class="d-flex align-items-center gap-3">
            <div class="position-relative">
                <img src="<?= e($assignedLead ? $assignedLead->avatarUrl() : asset('images/devioz-icon.png')) ?>" 
                     alt="<?= e($assignedLead ? $assignedLead->name : 'Lead Técnico') ?>" 
                     class="rounded-circle border" 
                     width="42" height="42" style="object-fit: cover;">
                <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-white rounded-circle" title="En línea"></span>
            </div>
            <div>
                <div class="small text-secondary" style="font-size: 0.70rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700;">Responsable &bull; Lead Asignado</div>
                <div class="fw-bold" style="color: #172B4D; font-size: 0.95rem;"><?= e($assignedLead ? $assignedLead->name : 'Elena Rostova') ?></div>
                <div class="text-secondary small d-flex align-items-center gap-2" style="font-size: 0.75rem;">
                    <span><i class="fa-solid fa-shield-halved text-primary me-1"></i> Auditoría QA</span>
                    <span>&bull;</span>
                    <span class="text-success fw-semibold"><i class="fa-solid fa-circle text-success me-1" style="font-size: 0.45rem;"></i> En Línea</span>
                </div>
            </div>
        </div>

        <!-- Botones de Interconexión Bitrix24 -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="btn btn-sm btn-brand d-flex align-items-center gap-2" id="projectOpenChatBtn" data-contact-id="<?= $assignedLead ? $assignedLead->id : '' ?>">
                <i class="fa-solid fa-comments"></i>
                <span>Chat Directo</span>
            </button>
            <button type="button" class="btn btn-sm btn-brand-outline d-flex align-items-center gap-2" id="projectRequestCallBtn" data-contact-id="<?= $assignedLead ? $assignedLead->id : '' ?>" data-project-id="<?= $project->id ?>">
                <i class="fa-solid fa-phone"></i>
                <span>Pedir Contacto</span>
            </button>
            <button type="button" class="btn btn-sm btn-outline-success d-flex align-items-center gap-2" id="projectLaunchVideoBtn" data-project-code="<?= e($project->code) ?>">
                <i class="fa-solid fa-video"></i>
                <span class="d-none d-sm-inline">Bitrix Meet</span>
            </button>
        </div>
    </div>
</div>

<!-- Pestañas de Navegación del Proyecto -->
<ul class="nav nav-pills mb-4 gap-2" id="projectTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active rounded-pill px-3 py-2 small fw-semibold" id="milestones-tab" data-bs-toggle="tab" data-bs-target="#tab-milestones" type="button" role="tab">
            Hitos de Entrega (<?= count($milestones) ?>)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link rounded-pill px-3 py-2 small fw-semibold" id="deliveries-tab" data-bs-toggle="tab" data-bs-target="#tab-deliveries" type="button" role="tab">
            Entregas Realizadas (<?= count($deliveries) ?>)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link rounded-pill px-3 py-2 small fw-semibold" id="checkins-tab" data-bs-toggle="tab" data-bs-target="#tab-checkins" type="button" role="tab">
            Standup / Check-in Diario
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link rounded-pill px-3 py-2 small fw-semibold" id="comments-tab" data-bs-toggle="tab" data-bs-target="#tab-comments" type="button" role="tab">
            Comentarios y Feedback (<?= count($comments) ?>)
        </button>
    </li>
</ul>

<!-- Contenido de las Pestañas -->
<div class="tab-content" id="projectTabsContent">
    <!-- PESTAÑA 1: HITOS (TIMELINE) -->
    <div class="tab-pane fade show active" id="tab-milestones" role="tabpanel">
        <div class="card-custom p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <div>
                    <h5 class="fw-bold mb-0" style="color: #172B4D;">Cronograma de Hitos de Desarrollo</h5>
                    <span class="small text-secondary">Secuencia estricta de cumplimiento y ponderación</span>
                </div>
                <!-- Selector de Vistas Estilo Bitrix24: Lista / Gantt / Calendario -->
                <div class="btn-group btn-group-sm rounded-pill p-1 shadow-sm bitrix-view-switcher" role="group" id="milestoneViewSwitcher" style="background: #EDF3F7; border: 1px solid #DBE2E8;">
                    <button type="button" class="btn btn-sm rounded-pill px-3 fw-semibold active" id="viewModeTimelineBtn" data-view="timeline">
                        <i class="fa-solid fa-list-check me-1"></i> Lista
                    </button>
                    <button type="button" class="btn btn-sm rounded-pill px-3 fw-semibold text-secondary" id="viewModeGanttBtn" data-view="gantt">
                        <i class="fa-solid fa-chart-gantt me-1"></i> Gantt
                    </button>
                    <button type="button" class="btn btn-sm rounded-pill px-3 fw-semibold text-secondary" id="viewModeCalendarBtn" data-view="calendar">
                        <i class="fa-solid fa-calendar-days me-1"></i> Calendario
                    </button>
                </div>
            </div>

            <!-- VISTA 1: LISTA CRONOGRAMA SECUENCIAL (TIMELINE ACTUAL) -->
            <div id="milestonesTimelineView">
                <div class="milestone-timeline">
                    <?php foreach ($milestones as $index => $m): ?>
                        <div class="milestone-item <?= $m->status ?>">
                            <div class="milestone-dot">
                                <?php if ($m->status === 'completed'): ?>
                                    <svg width="12" height="12" fill="none" stroke="#fff" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <?php endif; ?>
                            </div>

                            <div class="card-custom p-3">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-2">
                                    <div>
                                        <span class="badge font-monospace fw-semibold me-2" style="background: rgba(23, 43, 77, 0.08); color: #172B4D; border: 1px solid #DBE2E8;">Paso <?= $index + 1 ?></span>
                                        <span class="fw-bold fs-6" style="color: #172B4D;"><?= e($m->name) ?></span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if ($m->isOverdue()): ?>
                                            <span class="badge bg-danger">Atrasado</span>
                                        <?php endif; ?>
                                        <span class="small text-secondary">
                                            Fecha límite: <strong class="text-dark"><?= format_date($m->due_date) ?></strong>
                                        </span>
                                    </div>
                                </div>

                                <?php if (!empty($m->description)): ?>
                                    <p class="text-secondary small mb-3"><?= e($m->description) ?></p>
                                <?php endif; ?>

                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-1">
                                    <div class="small">
                                        Estado: 
                                        <?php if ($m->status === 'completed'): ?>
                                            <span class="text-emerald fw-semibold">Completado el <?= format_date($m->completed_at) ?></span>
                                        <?php elseif ($m->status === 'in_progress'): ?>
                                            <span class="text-primary fw-semibold">En Desarrollo</span>
                                        <?php else: ?>
                                            <span class="text-secondary">Pendiente</span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <!-- Botón Subir Entrega -->
                                        <button type="button" 
                                                class="btn btn-brand btn-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deliveryModal" 
                                                data-milestone-id="<?= $m->id ?>" 
                                                data-milestone-name="<?= e($m->name) ?>">
                                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                            <span>Subir Entrega</span>
                                        </button>

                                        <?php if ($m->status !== 'completed'): ?>
                                            <!-- Formulario Marcar Completado -->
                                            <form action="<?= url("/my-project/milestone/{$m->id}/complete") ?>" method="POST" class="d-inline">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-ghost btn-sm" data-confirm="¿Confirmas que has concluido todos los requisitos de este hito?">
                                                    <span>Marcar Completado</span>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- VISTA 2: DIAGRAMA DE GANTT BITRIX24 -->
            <div id="milestonesGanttView" class="d-none">
                <div class="bitrix-gantt-container p-3 rounded-3" style="background: #F8FAFC; border: 1px solid #E2E8F0;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="small fw-bold text-secondary text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Diagrama de Cronograma Ponderado (Gantt)</span>
                        <div class="d-flex align-items-center gap-3 small text-secondary" style="font-size: 0.75rem;">
                            <span><span class="badge bg-success p-1 me-1">&bull;</span> Concluido</span>
                            <span><span class="badge bg-primary p-1 me-1">&bull;</span> En Curso</span>
                            <span><span class="badge bg-secondary p-1 me-1">&bull;</span> Pendiente</span>
                        </div>
                    </div>

                    <div class="bitrix-gantt-chart">
                        <?php foreach ($milestones as $index => $m): 
                            $statusPercent = ($m->status === 'completed') ? 100 : (($m->status === 'in_progress') ? 50 : 0);
                            $barClass = ($m->status === 'completed') ? 'bg-success' : (($m->status === 'in_progress') ? 'bg-primary' : 'bg-secondary bg-opacity-25');
                            if ($m->isOverdue()) { $barClass = 'bg-danger'; }
                        ?>
                            <div class="bitrix-gantt-row py-2 border-bottom" style="border-color: #E2E8F0 !important;">
                                <div class="row align-items-center g-2">
                                    <div class="col-md-4 col-12">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge font-monospace" style="background: rgba(23,43,77,0.08); color: #172B4D; font-size: 0.7rem;">H<?= $index + 1 ?></span>
                                            <span class="small fw-bold text-truncate text-dark" title="<?= e($m->name) ?>"><?= e($m->name) ?></span>
                                        </div>
                                        <div class="text-secondary small ps-4" style="font-size: 0.70rem;">
                                            Límite: <?= format_date($m->due_date) ?> &bull; Ponderación: <?= (int)$m->weight ?>%
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-9">
                                        <div class="progress" style="height: 14px; border-radius: 999px; background: #E2E8F0;">
                                            <div class="progress-bar <?= $barClass ?> progress-bar-striped <?= $m->status === 'in_progress' ? 'progress-bar-animated' : '' ?>" 
                                                 role="progressbar" 
                                                 style="width: <?= max(12, $statusPercent) ?>%; font-size: 0.65rem; font-weight: 700;">
                                                <?= $statusPercent ?>%
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-3 text-end">
                                        <span class="badge <?= $m->status === 'completed' ? 'bg-success' : ($m->status === 'in_progress' ? 'bg-primary' : 'bg-secondary') ?> rounded-pill" style="font-size: 0.68rem;">
                                            <?= $m->status === 'completed' ? 'Completado' : ($m->status === 'in_progress' ? 'En Curso' : 'Pendiente') ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- VISTA 3: CALENDARIO DE ENTREGAS BITRIX24 -->
            <div id="milestonesCalendarView" class="d-none">
                <div class="row g-3">
                    <?php foreach ($milestones as $index => $m): ?>
                        <div class="col-md-6 col-lg-3">
                            <div class="card-custom p-3 h-100 border position-relative" style="border-left: 4px solid <?= $m->status === 'completed' ? '#10b981' : ($m->status === 'in_progress' ? '#00ACE3' : '#94A3B8') ?> !important;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge font-monospace" style="background: rgba(23,43,77,0.08); color: #172B4D;">Paso <?= $index + 1 ?></span>
                                    <span class="small fw-semibold <?= $m->isOverdue() ? 'text-danger' : 'text-secondary' ?>">
                                        <i class="fa-regular fa-calendar me-1"></i><?= format_date($m->due_date, 'd/m') ?>
                                    </span>
                                </div>
                                <h6 class="fw-bold small mb-2 text-dark"><?= e($m->name) ?></h6>
                                <p class="text-secondary small mb-3 flex-grow-1" style="font-size: 0.75rem;"><?= e($m->description ?: 'Cumplimiento de objetivos técnicos de la etapa.') ?></p>
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                    <span class="small text-muted" style="font-size: 0.70rem;">Peso: <?= (int)$m->weight ?>%</span>
                                    <span class="badge <?= $m->status === 'completed' ? 'bg-success' : ($m->status === 'in_progress' ? 'bg-primary' : 'bg-secondary') ?>" style="font-size: 0.65rem;">
                                        <?= $m->status === 'completed' ? 'Concluido' : ($m->status === 'in_progress' ? 'En Curso' : 'Pendiente') ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- PESTAÑA 2: ENTREGAS -->
    <div class="tab-pane fade" id="tab-deliveries" role="tabpanel">
        <div class="card-custom p-4">
            <h5 class="fw-bold mb-3" style="color: #172B4D;">Historial de Entregas y Auditorías QA</h5>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="--bs-table-bg: transparent;">
                    <thead>
                        <tr class="text-secondary small border-bottom" style="border-color: var(--border-color) !important;">
                            <th>TÍTULO / EVIDENCIA</th>
                            <th>HITO ASOCIADO</th>
                            <th>TIPO</th>
                            <th>FECHA ENVÍO</th>
                            <th class="col-status">ESTADO</th>
                            <th>QA SCORE</th>
                            <th>FEEDBACK DE AUDITORÍA LEAD</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deliveries as $del): ?>
                            <tr class="border-bottom" style="border-color: var(--border-color) !important;">
                                <td>
                                    <div class="fw-semibold" style="color: #172B4D;"><?= e($del->title) ?></div>
                                    <?php if (!empty($del->video_url)): ?>
                                        <button type="button" class="btn btn-link p-0 small text-info text-decoration-none border-0" data-video-url="<?= e($del->video_url) ?>" data-video-title="<?= e($del->title) ?>"><i class="fa-solid fa-play" aria-hidden="true"></i> Ver Video</button>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-secondary"><?= e($del->milestone() ? $del->milestone()->name : 'General') ?></td>
                                <td>
                                    <span class="badge whitespace-nowrap <?= $del->type === 'final' ? 'bg-primary' : 'bg-secondary' ?>">
                                        <?= $del->type === 'final' ? 'Final' : 'Parcial' ?>
                                    </span>
                                </td>
                                <td class="small text-secondary whitespace-nowrap"><?= format_date($del->submitted_at) ?></td>
                                <td>
                                    <?php if ($del->status === 'approved'): ?>
                                        <span class="badge bg-success whitespace-nowrap">Aprobada</span>
                                    <?php elseif ($del->status === 'rejected'): ?>
                                        <span class="badge bg-danger whitespace-nowrap">Observada</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark whitespace-nowrap">Pendiente</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold text-emerald whitespace-nowrap">
                                    <?= $del->score !== null ? number_format((float)$del->score, 1) . ((float)$del->score > 10 ? ' / 100' : ' / 10') : '—' ?>
                                </td>
                                <td class="small text-secondary fst-italic">
                                    <?= e($del->feedback ?: 'En espera de dictamen') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($deliveries)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-secondary small">No hay entregas registradas aún.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- PESTAÑA 3: CHECK-INS (DAILY STANDUP) -->
    <div class="tab-pane fade" id="tab-checkins" role="tabpanel">
        <div class="row g-4">
            <!-- Formulario de Check-In -->
            <div class="col-lg-5">
                <div class="card-custom p-4">
                    <h5 class="fw-bold mb-2" style="color: #172B4D;">Registrar Check-In Diario</h5>
                    <p class="text-secondary small mb-3">Reporta tus avances, horas trabajadas y consultas técnicas o de escalabilidad.</p>

                    <form action="<?= url('/my-project/check-in') ?>" method="POST">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="window" class="form-label-custom">Ventana del Standup</label>
                            <select name="window" id="window" class="form-select form-select-custom w-100" required>
                                <option value="morning">Mañana (08:00 - 12:00)</option>
                                <option value="midday">Mediodía (12:00 - 15:00)</option>
                                <option value="afternoon">Tarde (15:00 - 19:00)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="hours_worked" class="form-label-custom mb-0">Horas Invertidas en la Sesión</label>
                                <button type="button" class="btn btn-link p-0 text-decoration-none small" id="copyTrackerHoursBtn" style="font-size: 0.75rem; color: #00ACE3;">
                                    <i class="fa-solid fa-stopwatch me-1"></i> Copiar de Jornada Bitrix
                                </button>
                            </div>
                            <input type="number" step="0.5" min="0.5" max="12" name="hours_worked" id="hours_worked" class="form-control form-control-custom" value="3.5" required>
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label-custom">¿Qué actividades realizaste?</label>
                            <textarea name="content" id="content" rows="3" class="form-control form-control-custom w-100" placeholder="Ej: Implementé la validación de contraseñas y conecté la vista con el controlador..." required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="blockers" class="form-label-custom">Consultas Técnicas & Escalabilidad (Opcional)</label>
                            <textarea name="blockers" id="blockers" rows="2" class="form-control form-control-custom w-100" placeholder="¿Tienes algún impedimento técnico o consulta de arquitectura?"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="patterns_applied" class="form-label-custom">Patrones Aplicados (Separados por coma)</label>
                            <input type="text" name="patterns_applied" id="patterns_applied" class="form-control form-control-custom" placeholder="Ej: Singleton, MVC, Repository">
                        </div>

                        <button type="submit" class="btn btn-brand w-100 justify-content-center py-2">
                            <span>Guardar Reporte Diario</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Historial de Check-Ins -->
            <div class="col-lg-7">
                <div class="card-custom p-4">
                    <h5 class="fw-bold mb-3" style="color: #172B4D;">Historial de Reportes Standup</h5>
                    <div class="list-group list-group-flush" style="--bs-list-group-bg: transparent;">
                        <?php foreach ($checkIns as $ci): ?>
                            <div class="list-group-item border-bottom p-3" style="border-color: var(--border-color) !important;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="badge bg-secondary"><?= e($ci->windowLabel()) ?></span>
                                    <span class="small text-secondary"><?= format_date($ci->check_in_date) ?> &bull; <?= $ci->hours_worked ?> hrs</span>
                                </div>
                                <p class="small mb-1 text-dark"><?= e($ci->content) ?></p>
                                <?php if (!empty($ci->blockers)): ?>
                                    <div class="small text-danger bg-danger-subtle p-2 rounded mt-1">
                                        <strong>Consulta / Escalabilidad:</strong> <?= e($ci->blockers) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($checkIns)): ?>
                            <p class="text-secondary small text-center py-4 mb-0">No hay reportes de check-in registrados aún.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PESTAÑA 4: COMENTARIOS -->
    <div class="tab-pane fade" id="tab-comments" role="tabpanel">
        <div class="card-custom p-4">
            <h5 class="fw-bold mb-3" style="color: #172B4D;">Hilo de Comunicación y Retroalimentación</h5>

            <!-- Lista de Comentarios -->
            <div class="d-flex flex-column gap-3 mb-4">
                <?php foreach ($comments as $com): ?>
                    <?php $u = $com->user(); ?>
                    <div class="d-flex gap-3 p-3 rounded" style="background: #F4F5F7; border: 1px solid #DBE2E8;">
                        <img src="<?= e($u ? $u->avatarUrl() : '') ?>" class="rounded-circle" width="38" height="38" style="object-fit: cover;" loading="lazy" alt="Avatar">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div>
                                    <span class="fw-bold small" style="color: #172B4D;"><?= e($u ? $u->name : 'Usuario') ?></span>
                                    <span class="badge <?= $u && $u->isAdmin() ? 'bg-danger' : 'bg-emerald' ?> ms-1" style="font-size: 0.65rem;">
                                        <?= $u && $u->isAdmin() ? 'Administrador' : 'Desarrollador' ?>
                                    </span>
                                </div>
                                <span class="text-secondary" style="font-size: 0.72rem;"><?= format_date($com->created_at, 'd/m/Y H:i') ?></span>
                            </div>
                            <p class="text-secondary small mb-0"><?= nl2br(e($com->content)) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($comments)): ?>
                    <p class="text-secondary small text-center py-4 mb-0">No hay comentarios en este proyecto aún.</p>
                <?php endif; ?>
            </div>

            <!-- Formulario para Agregar Comentario -->
            <form action="<?= url('/my-project/comment') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="project_id" value="<?= $project->id ?>">
                <div class="mb-3">
                    <label for="comment_content" class="form-label-custom">Escribe un comentario o consulta para la administración:</label>
                    <textarea name="content" id="comment_content" rows="3" class="form-control form-control-custom w-100" placeholder="Escribe tu mensaje aquí..." required></textarea>
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-brand">Enviar Comentario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Subir Entrega -->
<div class="modal fade" id="deliveryModal" tabindex="-1" aria-labelledby="deliveryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background-color: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-primary);">
            <div class="modal-header" style="border-bottom: 1px solid var(--border-color);">
                <h5 class="modal-title fw-bold" style="color: #172B4D;" id="deliveryModalLabel">Subir Evidencia de Entrega</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="deliveryForm" action="<?= url('/my-project/delivery/1') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="deliveryTitle" class="form-label-custom">Título de la Entrega</label>
                        <input type="text" class="form-control form-control-custom" id="deliveryTitle" name="title" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="deliveryType" class="form-label-custom">Tipo de Entrega</label>
                            <select class="form-select form-select-custom" id="deliveryType" name="type" required>
                                <option value="partial">Avance Parcial</option>
                                <option value="final">Entrega Final del Hito</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="deliveryVideo" class="form-label-custom">URL de Video Demostrativo (Opcional)</label>
                            <input type="url" class="form-control form-control-custom" id="deliveryVideo" name="video_url" placeholder="YouTube, Loom o Vimeo">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="deliveryDesc" class="form-label-custom">Descripción del Avance</label>
                        <textarea class="form-control form-control-custom" id="deliveryDesc" name="description" rows="3" placeholder="Detalla las características y módulos concluidos..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="deliveryFiles" class="form-label-custom">Archivos de Evidencia (ZIP, PDF, Imágenes - Máx 20MB c/u)</label>
                        <input type="file" class="form-control form-control-custom" id="deliveryFiles" name="delivery_files[]" multiple accept=".pdf,.zip,.doc,.docx,.jpg,.jpeg,.png,.webp">
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid var(--border-color);">
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-brand">Confirmar y Enviar Entrega</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const deliveryModal = document.getElementById('deliveryModal');
    if (deliveryModal) {
        deliveryModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const milestoneId = button.getAttribute('data-milestone-id');
            const milestoneName = button.getAttribute('data-milestone-name');

            const modalTitle = deliveryModal.querySelector('.modal-title');
            const form = document.getElementById('deliveryForm');
            const titleInput = document.getElementById('deliveryTitle');

            modalTitle.textContent = `Subir Entrega: ${milestoneName}`;
            form.action = `<?= url('/my-project/delivery/') ?>${milestoneId}`;
            titleInput.value = `Entrega: ${milestoneName}`;
        });
    }
});
</script>
