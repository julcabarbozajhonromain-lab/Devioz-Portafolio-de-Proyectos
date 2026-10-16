<?php

declare(strict_types=1);

/** @var \App\Models\Project|null $project */
/** @var array $milestones */
/** @var array $deliveries */
/** @var int $progress */
/** @var int $completedCount */
/** @var int $totalMilestones */
/** @var array $todayCheckIns */
?>

<?php if ($project): ?>
    <div class="d-flex justify-content-end mb-3">
        <a href="<?= url('/my-project') ?>" class="btn btn-brand btn-sm d-flex align-items-center gap-2">
            <span>Ir al Espacio de Trabajo</span>
            <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>
<?php endif; ?>

<?php if (!$project): ?>
    <!-- Estado Vacío: Desarrollador sin proyecto asignado -->
    <div class="card-custom p-5 text-center my-4">
        <div class="stat-icon mx-auto mb-3" style="width: 64px; height: 64px; font-size: 1.8rem;">
            <i class="fa-solid fa-thumbtack" aria-hidden="true"></i>
        </div>
        <h4 class="fw-bold mb-2" style="color: var(--text-primary);">No tienes un proyecto asignado actualmente</h4>
        <p class="text-secondary mx-auto mb-4" style="max-width: 480px;">
            La administración te asignará un proyecto en breve. Mientras tanto, puedes explorar la galería de proyectos destacados o el directorio del equipo.
        </p>
        <div class="d-flex justify-content-center gap-3">
            <a href="<?= url('/gallery') ?>" class="btn btn-brand">Explorar Galería de Proyectos</a>
            <a href="<?= url('/team') ?>" class="btn btn-ghost">Ver Directorio</a>
        </div>
    </div>
<?php else: ?>
    <!-- Tarjeta Principal del Proyecto Asignado -->
    <div class="card-custom p-4 mb-4">
        <div class="row align-items-center g-3">
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="fw-bold text-emerald fs-5"><?= e($project->code) ?></span>
                    <?= $project->statusBadge() ?>
                    <span class="badge-sede"><?= e($project->sede) ?></span>
                </div>
                <h4 class="fw-bold mb-2" style="color: var(--text-primary);"><?= e($project->name) ?></h4>
                <p class="text-secondary small mb-3"><?= e($project->description) ?></p>

                <!-- Barra de Progreso Ponderado -->
                <div>
                    <div class="d-flex justify-content-between small fw-medium mb-1">
                        <span class="text-secondary">Progreso General del Proyecto</span>
                        <span class="text-emerald fw-bold"><?= $progress ?>% (<?= $completedCount ?> de <?= $totalMilestones ?> hitos completados)</span>
                    </div>
                    <div class="progress" style="height: 10px; background: var(--bg-card-hover);">
                        <div class="progress-bar bg-emerald" role="progressbar" style="width: <?= $progress ?>%;" aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="p-3 rounded text-start" style="background: var(--bg-card-hover); border: 1px solid var(--border-color);">
                    <div class="small text-secondary mb-1">Asignado por / Lead:</div>
                    <div class="fw-semibold small mb-2" style="color: var(--text-primary);"><?= e($project->assignedBy() ? $project->assignedBy()->name : 'Administrador Responsable') ?></div>
                    <a href="<?= url('/my-project') ?>" class="btn btn-brand btn-sm w-100 justify-content-center fw-bold" style="color: #1F2937 !important;">Ver Detalles y Hitos</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tablero Kanban de Hitos (Milestones) -->
    <div class="card-custom p-4 mb-4" style="background-color: var(--bg-main);">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0" style="color: var(--text-primary);">Tablero de Hitos (Mini-Kanban)</h5>
        </div>
        <div class="row g-3">
            <!-- Columna Pendientes -->
            <div class="col-md-4">
                <div class="p-3 rounded h-100" style="background: var(--bg-card); border-top: 3px solid var(--text-muted); box-shadow: var(--shadow-card);">
                    <h6 class="fw-semibold text-muted mb-3 small text-uppercase">Pendiente / Por Hacer</h6>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($milestones as $m): ?>
                            <?php if ($m->status === 'pending'): ?>
                                <div class="p-2 rounded border" style="background: var(--bg-main); border-color: var(--border-color);">
                                    <div class="fw-semibold small" style="color: var(--text-primary);"><?= e($m->name) ?></div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <!-- Columna En Revisión -->
            <div class="col-md-4">
                <div class="p-3 rounded h-100" style="background: var(--bg-card); border-top: 3px solid var(--warning); box-shadow: var(--shadow-card);">
                    <h6 class="fw-semibold text-warning mb-3 small text-uppercase">En Revisión QA</h6>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($milestones as $m): ?>
                            <?php if (in_array($m->status, ['in_progress', 'in_review'])): ?>
                                <div class="p-2 rounded border" style="background: var(--bg-main); border-color: var(--warning);">
                                    <div class="fw-semibold small" style="color: var(--text-primary);"><?= e($m->name) ?></div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <!-- Columna Completado -->
            <div class="col-md-4">
                <div class="p-3 rounded h-100" style="background: var(--bg-card); border-top: 3px solid var(--success); box-shadow: var(--shadow-card);">
                    <h6 class="fw-semibold text-success mb-3 small text-uppercase">Completado</h6>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($milestones as $m): ?>
                            <?php if ($m->status === 'completed'): ?>
                                <div class="p-2 rounded border" style="background: var(--bg-main); border-color: var(--success);">
                                    <div class="fw-semibold small" style="color: var(--text-primary);"><?= e($m->name) ?></div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de Standup Diario y Entregas Recientes -->
    <div class="row g-4">
        <!-- Estado de Check-in del Día -->
        <div class="col-lg-5">
            <div class="card-custom h-100">
                <div class="card-header-custom">
                    <div class="fw-semibold text-white">Reporte Standup de Hoy</div>
                    <span class="badge bg-secondary p-1 px-2" style="font-size: 0.7rem;"><?= date('d/m/Y') ?></span>
                </div>
                <div class="card-body-custom">
                    <p class="text-secondary small mb-3">Registra tu avance en cada una de las 3 ventanas laborales diarias:</p>
                    
                    <div class="d-flex flex-column gap-2 mb-3">
                        <?php
                        $windows = [
                            'morning'   => ['label' => 'Mañana (08:00 - 12:00)', 'icon' => 'fa-cloud-sun'],
                            'midday'    => ['label' => 'Mediodía (12:00 - 15:00)', 'icon' => 'fa-sun'],
                            'afternoon' => ['label' => 'Tarde (15:00 - 19:00)', 'icon' => 'fa-moon'],
                        ];
                        $completedWindows = array_column($todayCheckIns, 'window');
                        ?>
                        <?php foreach ($windows as $winKey => $winData): ?>
                            <?php $isDone = in_array($winKey, $completedWindows, true); ?>
                            <div class="p-2 px-3 rounded d-flex align-items-center justify-content-between" style="background: var(--bg-sidebar); border: 1px solid var(--border-color);">
                                <div class="d-flex align-items-center gap-2 small">
                                    <i class="fa-solid <?= $winData['icon'] ?>" aria-hidden="true"></i>
                                    <span class="<?= $isDone ? 'text-white fw-semibold' : 'text-secondary' ?>"><?= $winData['label'] ?></span>
                                </div>
                                <?php if ($isDone): ?>
                                    <span class="badge bg-emerald p-1 px-2" style="font-size: 0.68rem;">Completado</span>
                                <?php else: ?>
                                    <span class="badge bg-dark text-secondary p-1 px-2" style="font-size: 0.68rem;">Pendiente</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <a href="<?= url('/my-project#checkin') ?>" class="btn btn-brand-outline btn-sm w-100 justify-content-center">
                        <span>Gestionar Check-ins Diarios</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Últimas Entregas Realizadas -->
        <div class="col-lg-7">
            <div class="card-custom h-100">
                <div class="card-header-custom">
                    <div class="fw-semibold text-white">Mis Entregas y Evaluaciones</div>
                    <a href="<?= url('/my-project#deliveries') ?>" class="small text-emerald text-decoration-none">Ver todas &rarr;</a>
                </div>
                <div class="card-body-custom p-0">
                    <div class="list-group list-group-flush" style="--bs-list-group-bg: transparent;">
                        <?php foreach ($deliveries as $del): ?>
                            <div class="list-group-item border-bottom p-3" style="border-color: var(--border-color) !important;">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="fw-semibold small text-white"><?= e($del->title) ?></span>
                                    <?php if ($del->status === 'approved'): ?>
                                        <span class="badge bg-success">Aprobada (<?= $del->score ?>/10)</span>
                                    <?php elseif ($del->status === 'rejected'): ?>
                                        <span class="badge bg-danger">Observada</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">En Revisión</span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($del->feedback)): ?>
                                    <p class="text-secondary small mb-1 fst-italic">"<?= e($del->feedback) ?>"</p>
                                <?php endif; ?>
                                <div class="d-flex justify-content-between text-muted" style="font-size: 0.72rem;">
                                    <span>Hito: <?= e($del->milestone() ? $del->milestone()->name : 'General') ?></span>
                                    <span><?= format_date($del->submitted_at) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($deliveries)): ?>
                            <div class="p-4 text-center text-secondary small">
                                Aún no has enviado entregas para este proyecto. Usa el botón "Subir Entrega" en la pestaña de hitos.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

