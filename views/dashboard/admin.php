<?php

declare(strict_types=1);

/** @var bool $isLider */
/** @var string $sede */
/** @var int $totalProjects */
/** @var int $inReviewCount */
/** @var int $approvedCount */
/** @var int $unassignedCount */
/** @var array $recentProjects */
/** @var array $activityStream */
/** @var array $sedeStats */
/** @var array $statusStats */
?>

<!-- Barra Superior de Acciones Rápidas (Estilo Bitrix24 CRM) -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center gap-2">
        <?php if ($isLider): ?>
            <span class="badge bg-info bg-opacity-10 text-info px-2 py-1" style="border: 1px solid rgba(14, 165, 233, 0.25); font-size: 0.8rem;">
                <i class="fa-solid fa-location-dot me-1"></i> Sede: <?= e($sede) ?>
            </span>
        <?php endif; ?>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="<?= url('/admin/review') ?>" class="btn btn-ghost btn-sm text-nowrap d-flex align-items-center gap-1">
            <i class="fa-solid fa-table-columns"></i>
            <span>Kanban Revisión</span>
        </a>
        <a href="<?= url('/projects/create') ?>" class="btn btn-brand btn-sm text-nowrap d-flex align-items-center gap-1">
            <i class="fa-solid fa-plus"></i>
            <span>Asignar Proyecto</span>
        </a>
    </div>
</div>


<!-- 4 Tarjetas de Métricas (KPIs) -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="min-w-0 flex-grow-1" style="max-width: calc(100% - 48px);">
                <div class="stat-value"><?= $totalProjects ?></div>
                <div class="stat-label text-truncate" title="Total de Proyectos">Total de Proyectos</div>
            </div>
            <div class="stat-icon" style="background: rgba(59, 130, 246, 0.15); color: #3b82f6;">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="min-w-0 flex-grow-1" style="max-width: calc(100% - 48px);">
                <div class="stat-value text-warning"><?= $inReviewCount ?></div>
                <div class="stat-label text-truncate" title="En Revisión Pendiente">En Revisión Pendiente</div>
            </div>
            <div class="stat-icon" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="min-w-0 flex-grow-1" style="max-width: calc(100% - 48px);">
                <div class="stat-value text-emerald"><?= $approvedCount ?></div>
                <div class="stat-label text-truncate" title="Proyectos Aprobados">Proyectos Aprobados</div>
            </div>
            <div class="stat-icon">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="min-w-0 flex-grow-1" style="max-width: calc(100% - 48px);">
                <div class="stat-value text-info"><?= $unassignedCount ?></div>
                <div class="stat-label text-truncate" title="Desarrolladores Disponibles">Desarrolladores Disponibles</div>
            </div>
            <div class="stat-icon" style="background: rgba(168, 85, 247, 0.15); color: #a855f7;">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos y Actividad -->
<div class="row g-4 mb-4">
    <!-- Gráfico de Horas y Actividad -->
    <div class="col-12 col-xl-8">
        <div class="card-custom h-100">
            <div class="card-header-custom">
                <div class="fw-semibold" style="color: var(--text-primary);">Actividad de Desarrollo (Últimos 7 Días)</div>
                <span class="badge-brand">Reportes Standup</span>
            </div>
            <div class="card-body-custom" style="height: 280px; position: relative; min-width: 0; overflow: hidden;">
                <canvas id="dashboardActivityChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Distribución por Sedes -->
    <div class="col-12 col-xl-4">
        <div class="card-custom h-100">
            <div class="card-header-custom">
                <div class="fw-semibold" style="color: var(--text-primary);">Proyectos por Sede</div>
            </div>
            <div class="card-body-custom d-flex flex-column justify-content-around">
                <?php foreach ($sedeStats as $st): ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center small fw-medium mb-1 gap-2">
                            <span class="text-truncate" style="color: var(--text-primary);"><?= e($st['sede'] ?: 'Sin Asignar') ?></span>
                            <span class="text-secondary text-nowrap flex-shrink-0"><?= (int)$st['count'] ?> proyectos</span>
                        </div>
                        <div class="progress" style="height: 8px; background: var(--bg-card-hover);">
                            <div class="progress-bar bg-emerald" style="width: <?= min(100, (int)$st['count'] * 20) ?>%;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($sedeStats)): ?>
                    <p class="text-secondary small mb-0 text-center py-4">No hay datos de distribución registrados.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Proyectos Recientes y Check-ins -->
<div class="row g-4">
    <!-- Tabla de Proyectos Recientes -->
    <div class="col-12 col-xl-8">
        <div class="card-custom">
            <div class="card-header-custom">
                <div class="fw-semibold" style="color: #172B4D;">Últimos Proyectos Actualizados</div>
                <a href="<?= url('/projects') ?>" class="small text-emerald text-decoration-none fw-semibold">Ver todos &rarr;</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle" style="--bs-table-bg: transparent;">
                    <thead>
                        <tr class="text-secondary small border-bottom" style="border-color: var(--border-color) !important;">
                            <th style="white-space: nowrap;">CÓDIGO</th>
                            <th style="white-space: nowrap;">PROYECTO</th>
                            <th style="white-space: nowrap;">DESARROLLADOR</th>
                            <th style="white-space: nowrap;">SEDE</th>
                            <th style="white-space: nowrap;">ESTADO</th>
                            <th class="text-end" style="white-space: nowrap;">ACCIÓN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentProjects as $p): ?>
                            <?php $dev = $p->developer(); ?>
                            <tr class="border-bottom" style="border-color: var(--border-color) !important;">
                                <td class="fw-bold text-emerald font-monospace small text-nowrap"><?= e($p->code) ?></td>
                                <td>
                                    <div class="fw-medium" style="color: #172B4D;"><?= e($p->name) ?></div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2 min-w-0">
                                        <img src="<?= e($dev ? $dev->avatarUrl() : '') ?>" class="rounded-circle flex-shrink-0" width="24" height="24" loading="lazy" alt="Avatar" style="object-fit: cover;">
                                        <span class="small text-secondary text-truncate" style="max-width: 140px;"><?= e($dev ? $dev->name : '—') ?></span>
                                    </div>
                                </td>
                                <td><span class="badge-sede text-nowrap"><?= e($p->sede ?: '—') ?></span></td>
                                <td class="text-nowrap"><?= $p->statusBadge() ?></td>
                                <td class="text-end">
                                    <a href="<?= url("/projects/{$p->id}") ?>" class="btn btn-ghost btn-sm py-1 px-2 text-nowrap">Ver</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recentProjects)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-secondary small">No hay proyectos registrados aún.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Feed de Actividad (Muro) -->
    <div class="col-12 col-xl-4">
        <div class="card-custom">
            <div class="card-header-custom">
                <div class="fw-semibold" style="color: var(--text-primary);">Muro de Actividad</div>
                <span class="small text-secondary">Feed</span>
            </div>
            <div class="card-body-custom p-0">
                <div class="list-group list-group-flush" style="--bs-list-group-bg: transparent;">
                    <?php foreach ($activityStream as $ci): ?>
                        <?php $dev = $ci->user(); ?>
                        <div class="list-group-item border-bottom p-3" style="border-color: var(--border-color) !important;">
                            <div class="d-flex align-items-center justify-content-between mb-1 gap-2">
                                <div class="d-flex align-items-center gap-2 min-w-0">
                                    <img src="<?= e($dev ? $dev->avatarUrl() : '') ?>" class="rounded-circle flex-shrink-0" width="22" height="22" loading="lazy" alt="Avatar" style="object-fit: cover;">
                                    <span class="fw-medium small text-truncate" style="color: var(--text-primary);"><?= e($dev ? $dev->name : 'Desarrollador') ?></span>
                                </div>
                                <span class="badge bg-secondary p-1 flex-shrink-0 text-nowrap" style="font-size: 0.65rem;"><?= e($ci->windowLabel()) ?></span>
                            </div>
                            <p class="text-secondary small mb-1 text-truncate" title="<?= e($ci->content) ?>"><?= e($ci->content) ?></p>
                            <div class="d-flex justify-content-between text-muted" style="font-size: 0.72rem;">
                                <span><i class="fas fa-clock text-info me-1"></i><?= (float)$ci->hours_worked ?> hrs</span>
                                <span><?= format_date($ci->check_in_date) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($activityStream)): ?>
                        <p class="text-secondary small text-center py-4 mb-0">No hay actividad reciente.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
