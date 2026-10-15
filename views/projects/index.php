<?php

declare(strict_types=1);

/** @var array $projects */
/** @var string $search */
/** @var string $status */
/** @var string $sede */
/** @var array|null $statusCounts */

$statusCounts = $statusCounts ?? [
    'all'         => count($projects),
    'in_progress' => count(array_filter($projects, fn($p) => in_array($p->status, ['assigned', 'in_progress'], true))),
    'review'      => count(array_filter($projects, fn($p) => in_array($p->status, ['pending_review', 'in_review'], true))),
    'approved'    => count(array_filter($projects, fn($p) => in_array($p->status, ['approved', 'completed'], true))),
];

// Buckets de proyectos para el Tablero Kanban SDLC
$kanbanAssigned = [];
$kanbanInProgress = [];
$kanbanReview = [];
$kanbanApproved = [];

foreach ($projects as $p) {
    if ($p->status === 'assigned') {
        $kanbanAssigned[] = $p;
    } elseif ($p->status === 'in_progress') {
        $kanbanInProgress[] = $p;
    } elseif (in_array($p->status, ['pending_review', 'in_review'], true)) {
        $kanbanReview[] = $p;
    } else {
        $kanbanApproved[] = $p;
    }
}
?>

<!-- ========================================================================= -->
<!-- 1. HEADER DE ACCIONES RÁPIDAS (ESTILO BITRIX24 AIR ENTERPRISE)             -->
<!-- ========================================================================= -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
    <div>
        <h4 class="fw-bold mb-0 page-header-title text-white" style="letter-spacing: -0.01em;">
            Proyectos & Entregables SDLC
        </h4>
        <div class="page-header-subtitle text-white-50 small mt-1">
            Gestión de ciclo de vida de software, compuertas QA y entregables para clientes corporativos
        </div>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <button type="button" class="btn btn-ghost btn-sm d-flex align-items-center gap-1" onclick="window.print()" title="Imprimir catálogo">
            <i class="fa-solid fa-file-pdf text-danger"></i>
            <span>Exportar</span>
        </button>

        <?php if (auth()->isAdmin()): ?>
            <a href="<?= url('/admin/review') ?>" class="btn btn-ghost btn-sm d-flex align-items-center gap-1">
                <i class="fa-solid fa-table-columns text-info"></i>
                <span>Revisión QA</span>
            </a>
            <a href="<?= url('/projects/create') ?>" class="btn btn-bitrix-green btn-sm d-flex align-items-center gap-1">
                <i class="fa-solid fa-plus"></i>
                <span>Asignar Proyecto</span>
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 2. SUB-HEADER AIR CON PESTAÑAS UI-COUNTER & SELECTOR DE VISTAS            -->
<!-- ========================================================================= -->
<div class="bitrix-subnav-bar mb-3">
    <!-- Pestañas de Estado con Contadores en Píldora -->
    <div class="bitrix-subnav-tabs">
        <a href="<?= url('/projects') ?>" class="bitrix-subnav-tab <?= empty($status) ? 'active' : '' ?>">
            <span>Todos</span>
            <span class="ui-counter"><?= $statusCounts['all'] ?></span>
        </a>
        <a href="<?= url('/projects?status=in_progress' . ($sede ? "&sede={$sede}" : '')) ?>" class="bitrix-subnav-tab <?= $status === 'in_progress' ? 'active' : '' ?>">
            <span>En Desarrollo</span>
            <span class="ui-counter"><?= $statusCounts['in_progress'] ?></span>
        </a>
        <a href="<?= url('/projects?status=in_review' . ($sede ? "&sede={$sede}" : '')) ?>" class="bitrix-subnav-tab <?= in_array($status, ['in_review', 'pending_review'], true) ? 'active' : '' ?>">
            <span>Auditoría QA</span>
            <span class="ui-counter bg-warning text-dark"><?= $statusCounts['review'] ?></span>
        </a>
        <a href="<?= url('/projects?status=approved' . ($sede ? "&sede={$sede}" : '')) ?>" class="bitrix-subnav-tab <?= in_array($status, ['approved', 'completed'], true) ? 'active' : '' ?>">
            <span>Certificados</span>
            <span class="ui-counter bg-success text-white"><?= $statusCounts['approved'] ?></span>
        </a>
    </div>

    <!-- Selector Multi-Modal de Vistas: Cuadrícula | Kanban | Lista -->
    <div class="bitrix-view-switcher" id="projectsViewSwitcher">
        <button type="button" class="btn-view-toggle active" data-view="grid" title="Cuadrícula Air de Tarjetas">
            <i class="fa-solid fa-grip"></i>
            <span class="d-none d-sm-inline">Cuadrícula</span>
        </button>
        <button type="button" class="btn-view-toggle" data-view="kanban" title="Tablero Kanban por Etapas SDLC">
            <i class="fa-solid fa-table-columns"></i>
            <span class="d-none d-sm-inline">Kanban</span>
        </button>
        <button type="button" class="btn-view-toggle" data-view="list" title="Lista Detallada con Métricas">
            <i class="fa-solid fa-list"></i>
            <span class="d-none d-sm-inline">Lista</span>
        </button>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 3. BARRA DE FILTRO REACTIVO Y BÚSQUEDA INSTANTÁNEA                        -->
<!-- ========================================================================= -->
<div class="card-custom p-3 mb-4">
    <form action="<?= url('/projects') ?>" method="GET" class="row g-2 align-items-center" id="projectFilterForm">
        <?php if (!empty($status)): ?>
            <input type="hidden" name="status" value="<?= e($status) ?>">
        <?php endif; ?>
        <div class="col-md-7 col-lg-8">
            <div class="position-relative d-flex align-items-center">
                <i class="fa-solid fa-magnifying-glass position-absolute text-secondary" style="left: 16px; top: 50%; transform: translateY(-50%); font-size: 0.95rem; z-index: 5; pointer-events: none; color: #64748B !important;"></i>
                <input type="text" name="q" id="projectSearchInput" value="<?= e($search) ?>" class="form-control form-control-custom w-100" style="padding-left: 2.85rem !important;" placeholder="Buscar por código, nombre, cliente o desarrollador en tiempo real...">
            </div>
        </div>
        <div class="col-md-3 col-lg-3">
            <select name="sede" id="projectSedeSelect" class="form-select form-select-custom w-100" onchange="document.getElementById('projectFilterForm').submit()">
                <option value="">Todas las Sedes Operativas</option>
                <option value="Cercado" <?= $sede === 'Cercado' ? 'selected' : '' ?>>Cercado Central</option>
                <option value="Lima Norte" <?= $sede === 'Lima Norte' ? 'selected' : '' ?>>Lima Norte</option>
                <option value="Villa" <?= $sede === 'Villa' ? 'selected' : '' ?>>Villa Sur</option>
            </select>
        </div>
        <div class="col-md-2 col-lg-1 text-end">
            <?php if (!empty($search) || !empty($sede) || !empty($status)): ?>
                <a href="<?= url('/projects') ?>" class="btn btn-ghost w-100 py-2 small text-danger" title="Limpiar Filtros">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            <?php else: ?>
                <button type="submit" class="btn btn-ghost w-100 py-2" title="Buscar" aria-label="Buscar">
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- ========================================================================= -->
<!-- 4. CONTENEDOR MULTI-MODAL: 3 VISTAS INTEGRADAS                             -->
<!-- ========================================================================= -->

<!-- VISTA 1: CUADRÍCULA AIR DE TARJETAS (DEFAULT) -->
<div id="viewContainerGrid" class="projects-view-pane">
    <div class="row g-4" id="projectGridRow">
        <?php foreach ($projects as $p): ?>
            <?php 
                $dev = $p->developer(); 
                $searchIndex = strtolower(e($p->code . ' ' . $p->name . ' ' . $p->clientName() . ' ' . ($dev ? $dev->name : '') . ' ' . $p->sede));
            ?>
            <div class="col-md-6 col-xl-4 project-item-card" data-search="<?= $searchIndex ?>">
                <div class="card-custom h-100 hover-lift d-flex flex-column overflow-hidden" style="border: 1px solid #DBE2E8;">
                    <div style="height: 140px; position: relative; overflow: hidden; cursor: pointer;" class="p-3 d-flex justify-content-between align-items-start" data-sidepanel-project="<?= $p->id ?>" title="Clic para inspección rápida (SidePanel)">
                        <img src="<?= e($p->coverUrl()) ?>" alt="<?= e($p->name) ?>" class="position-absolute top-0 start-0 w-100 h-100" style="object-fit: cover;" loading="lazy">
                        <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(to bottom, rgba(23, 43, 77, 0.45) 0%, rgba(23, 43, 77, 0.82) 100%);"></div>
                        <span class="badge bg-dark border border-secondary text-white fw-bold position-relative font-monospace" style="z-index: 2;"><?= e($p->code) ?></span>
                        <div class="position-relative d-flex gap-1 align-items-center" style="z-index: 2;">
                            <?= $p->statusBadge() ?>
                            <?php if ($p->final_score): ?>
                                <span class="badge bg-success" style="font-size: 0.65rem;">QA: <?= $p->final_score ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card-body-custom flex-grow-1 d-flex flex-column justify-content-between p-3">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                <span class="bx-client-badge py-1 px-2" style="font-size: 0.78rem;" title="Cliente Corporativo">
                                    <i class="fa-solid fa-briefcase me-1"></i>
                                    <span><?= e($p->clientName()) ?></span>
                                </span>
                                <span class="badge-sede"><?= e($p->sede ?: 'Sin Sede') ?></span>
                                <?php if ($p->is_public_in_gallery): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.65rem;">En Galería</span>
                                <?php endif; ?>
                            </div>
                            <h5 class="fw-bold mb-2" style="color: #172B4D; font-size: 1.05rem; cursor: pointer;" data-sidepanel-project="<?= $p->id ?>"><?= e($p->name) ?></h5>
                            <p class="text-secondary small mb-3 text-truncate-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; font-size: 0.82rem;">
                                <?= e($p->description ?: 'Proyecto de consultoría de software enterprise.') ?>
                            </p>
                        </div>

                        <div>
                            <!-- Desarrollador Responsable -->
                            <div class="d-flex align-items-center gap-2 p-2 rounded-2 my-2" style="background: #F8FAFB; border: 1px solid #E0E8ED;">
                                <img src="<?= e($dev ? $dev->avatarUrl() : '') ?>" class="rounded-circle" width="28" height="28" loading="lazy" alt="Avatar" style="object-fit: cover;">
                                <div class="overflow-hidden min-w-0">
                                    <div class="fw-semibold small text-truncate" style="color: #172B4D; font-size: 0.78rem;"><?= e($dev ? $dev->name : 'No asignado') ?></div>
                                    <div class="text-muted text-truncate" style="font-size: 0.68rem;"><?= e($dev ? $dev->email : '') ?></div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-2">
                                <div class="small text-secondary" style="font-size: 0.78rem;">
                                    Avance: <strong class="text-emerald"><?= $p->progressPercentage() ?>%</strong>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-ghost btn-sm border" data-sidepanel-project="<?= $p->id ?>" title="Inspección Rápida (SidePanel)" style="color: #00ACE3;">
                                        <i class="fa-solid fa-table-columns"></i>
                                    </button>
                                    <a href="<?= url("/projects/{$p->id}") ?>" class="btn btn-brand btn-sm">Ver Proyecto</a>
                                    <?php if (auth()->isAdmin()): ?>
                                        <a href="<?= url("/projects/{$p->id}/edit") ?>" class="btn btn-ghost btn-sm" title="Editar" aria-label="Editar proyecto">
                                            <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- VISTA 2: TABLERO KANBAN SDLC (COLUMNAS POR ETAPA) -->
<div id="viewContainerKanban" class="projects-view-pane d-none">
    <div class="bitrix-kanban-board">
        <!-- Columna 1: Asignación Inicial -->
        <div class="bitrix-kanban-col">
            <div class="bitrix-kanban-col-header" style="border-top: 3px solid #00ACE3;">
                <span style="color: #172B4D;">Asignación & Inicio</span>
                <span class="ui-counter bg-primary text-white"><?= count($kanbanAssigned) ?></span>
            </div>
            <div class="bitrix-kanban-col-body">
                <?php foreach ($kanbanAssigned as $p): ?>
                    <?php $dev = $p->developer(); ?>
                    <div class="bitrix-kanban-card project-item-card" data-search="<?= strtolower(e($p->code . ' ' . $p->name . ' ' . $p->clientName())) ?>" data-sidepanel-project="<?= $p->id ?>" role="button" title="Inspección rápida (SidePanel)">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge font-monospace text-dark bg-light border" style="font-size: 0.68rem;"><?= e($p->code) ?></span>
                            <span class="badge-sede" style="font-size: 0.65rem;"><?= e($p->sede) ?></span>
                        </div>
                        <div class="fw-bold small text-dark mb-1 text-truncate-2"><?= e($p->name) ?></div>
                        <div class="text-secondary small mb-2" style="font-size: 0.72rem;"><i class="fa-solid fa-briefcase me-1"></i><?= e($p->clientName()) ?></div>
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <div class="d-flex align-items-center gap-1">
                                <img src="<?= e($dev ? $dev->avatarUrl() : '') ?>" class="rounded-circle" width="20" height="20" alt="Avatar">
                                <span class="text-secondary text-truncate" style="font-size: 0.7rem; max-width: 90px;"><?= e($dev ? $dev->name : 'Sin dev') ?></span>
                            </div>
                            <span class="small fw-bold text-primary" style="font-size: 0.72rem;"><?= $p->progressPercentage() ?>%</span>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($kanbanAssigned)): ?>
                    <div class="kanban-empty-placeholder">
                        <i class="fa-solid fa-inbox mb-2 fs-5 opacity-75 d-block"></i>
                        <span>Sin proyectos en asignación</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Columna 2: En Desarrollo & Sprints -->
        <div class="bitrix-kanban-col">
            <div class="bitrix-kanban-col-header" style="border-top: 3px solid #3b82f6;">
                <span style="color: #FFFFFF;">En Desarrollo</span>
                <span class="ui-counter bg-primary text-white"><?= count($kanbanInProgress) ?></span>
            </div>
            <div class="bitrix-kanban-col-body">
                <?php foreach ($kanbanInProgress as $p): ?>
                    <?php $dev = $p->developer(); ?>
                    <div class="bitrix-kanban-card project-item-card" data-search="<?= strtolower(e($p->code . ' ' . $p->name . ' ' . $p->clientName())) ?>" data-sidepanel-project="<?= $p->id ?>" role="button" title="Inspección rápida (SidePanel)">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge font-monospace text-dark bg-light border" style="font-size: 0.68rem;"><?= e($p->code) ?></span>
                            <span class="badge-sede" style="font-size: 0.65rem;"><?= e($p->sede) ?></span>
                        </div>
                        <div class="fw-bold small text-dark mb-1 text-truncate-2"><?= e($p->name) ?></div>
                        <div class="text-secondary small mb-2" style="font-size: 0.72rem;"><i class="fa-solid fa-briefcase me-1"></i><?= e($p->clientName()) ?></div>
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <div class="d-flex align-items-center gap-1">
                                <img src="<?= e($dev ? $dev->avatarUrl() : '') ?>" class="rounded-circle" width="20" height="20" alt="Avatar">
                                <span class="text-secondary text-truncate" style="font-size: 0.7rem; max-width: 90px;"><?= e($dev ? $dev->name : 'Sin dev') ?></span>
                            </div>
                            <span class="small fw-bold text-primary" style="font-size: 0.72rem;"><?= $p->progressPercentage() ?>%</span>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($kanbanInProgress)): ?>
                    <div class="kanban-empty-placeholder">
                        <i class="fa-solid fa-spinner mb-2 fs-5 opacity-75 d-block"></i>
                        <span>Sin proyectos en desarrollo</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Columna 3: Auditoría QA & Dictamen -->
        <div class="bitrix-kanban-col">
            <div class="bitrix-kanban-col-header" style="border-top: 3px solid #f59e0b;">
                <span style="color: #FFFFFF;">Auditoría QA</span>
                <span class="ui-counter bg-warning text-dark"><?= count($kanbanReview) ?></span>
            </div>
            <div class="bitrix-kanban-col-body">
                <?php foreach ($kanbanReview as $p): ?>
                    <?php $dev = $p->developer(); ?>
                    <div class="bitrix-kanban-card project-item-card" data-search="<?= strtolower(e($p->code . ' ' . $p->name . ' ' . $p->clientName())) ?>" data-sidepanel-project="<?= $p->id ?>" role="button" title="Inspección rápida (SidePanel)">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge font-monospace text-dark bg-light border" style="font-size: 0.68rem;"><?= e($p->code) ?></span>
                            <span class="badge bg-warning bg-opacity-25 text-warning fw-bold" style="font-size: 0.65rem;">Revisión QA</span>
                        </div>
                        <div class="fw-bold small text-dark mb-1 text-truncate-2"><?= e($p->name) ?></div>
                        <div class="text-secondary small mb-2" style="font-size: 0.72rem;"><i class="fa-solid fa-briefcase me-1"></i><?= e($p->clientName()) ?></div>
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <div class="d-flex align-items-center gap-1">
                                <img src="<?= e($dev ? $dev->avatarUrl() : '') ?>" class="rounded-circle" width="20" height="20" alt="Avatar">
                                <span class="text-secondary text-truncate" style="font-size: 0.7rem; max-width: 90px;"><?= e($dev ? $dev->name : 'Sin dev') ?></span>
                            </div>
                            <span class="small fw-bold text-warning" style="font-size: 0.72rem;"><?= $p->progressPercentage() ?>%</span>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($kanbanReview)): ?>
                    <div class="kanban-empty-placeholder">
                        <i class="fa-solid fa-certificate mb-2 fs-5 opacity-75 d-block"></i>
                        <span>Sin entregas pendientes de QA</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Columna 4: Aprobado & Release Certificado -->
        <div class="bitrix-kanban-col">
            <div class="bitrix-kanban-col-header" style="border-top: 3px solid #7BD500;">
                <span style="color: #FFFFFF;">Certificado & Release</span>
                <span class="ui-counter bg-success text-white"><?= count($kanbanApproved) ?></span>
            </div>
            <div class="bitrix-kanban-col-body">
                <?php foreach ($kanbanApproved as $p): ?>
                    <?php $dev = $p->developer(); ?>
                    <div class="bitrix-kanban-card project-item-card" data-search="<?= strtolower(e($p->code . ' ' . $p->name . ' ' . $p->clientName())) ?>" data-sidepanel-project="<?= $p->id ?>" role="button" title="Inspección rápida (SidePanel)">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge font-monospace text-dark bg-light border" style="font-size: 0.68rem;"><?= e($p->code) ?></span>
                            <span class="badge bg-success" style="font-size: 0.65rem;">Score: <?= $p->final_score ?: '100' ?></span>
                        </div>
                        <div class="fw-bold small text-dark mb-1 text-truncate-2"><?= e($p->name) ?></div>
                        <div class="text-secondary small mb-2" style="font-size: 0.72rem;"><i class="fa-solid fa-briefcase me-1"></i><?= e($p->clientName()) ?></div>
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <div class="d-flex align-items-center gap-1">
                                <img src="<?= e($dev ? $dev->avatarUrl() : '') ?>" class="rounded-circle" width="20" height="20" alt="Avatar">
                                <span class="text-secondary text-truncate" style="font-size: 0.7rem; max-width: 90px;"><?= e($dev ? $dev->name : 'Sin dev') ?></span>
                            </div>
                            <span class="badge bg-success-subtle text-success" style="font-size: 0.68rem;"><i class="fa-solid fa-certificate me-1"></i>ISO 25010</span>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($kanbanApproved)): ?>
                    <div class="kanban-empty-placeholder">
                        <i class="fa-solid fa-shield-halved mb-2 fs-5 opacity-75 d-block"></i>
                        <span>Sin proyectos certificados aún</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- VISTA 3: LISTA COMPACTA ENTERPRISE (TABLE) -->
<div id="viewContainerList" class="projects-view-pane d-none">
    <div class="card-custom p-0 overflow-hidden" style="border: 1px solid #DBE2E8;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="projectsListTable">
                <thead style="background: #F8FAFB;">
                    <tr class="text-secondary small border-bottom" style="border-color: #DBE2E8 !important; font-size: 0.76rem; letter-spacing: 0.03em;">
                        <th class="ps-3 py-3" style="min-width: 110px;">CÓDIGO</th>
                        <th style="min-width: 220px;">PROYECTO & CLIENTE</th>
                        <th style="min-width: 170px;">DESARROLLADOR</th>
                        <th style="min-width: 110px;">SEDE</th>
                        <th style="min-width: 120px;">ESTADO SDLC</th>
                        <th style="min-width: 100px;">AVANCE %</th>
                        <th style="min-width: 100px;">CALIDAD QA</th>
                        <th class="text-end pe-3" style="min-width: 110px;">ACCIÓN</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $p): ?>
                        <?php 
                            $dev = $p->developer(); 
                            $searchIndex = strtolower(e($p->code . ' ' . $p->name . ' ' . $p->clientName() . ' ' . ($dev ? $dev->name : '') . ' ' . $p->sede));
                        ?>
                        <tr class="project-item-card border-bottom" data-search="<?= $searchIndex ?>" style="border-color: #DBE2E8 !important;">
                            <td class="ps-3">
                                <span class="font-monospace fw-bold" style="color: #00ACE3; font-size: 0.82rem;"><?= e($p->code) ?></span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark text-truncate" style="max-width: 260px; font-size: 0.85rem;"><?= e($p->name) ?></div>
                                <div class="text-secondary small d-flex align-items-center gap-1" style="font-size: 0.72rem;">
                                    <i class="fa-solid fa-briefcase opacity-75"></i>
                                    <span><?= e($p->clientName()) ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?= e($dev ? $dev->avatarUrl() : '') ?>" class="rounded-circle" width="26" height="26" alt="Avatar" style="object-fit: cover;">
                                    <div class="overflow-hidden min-w-0">
                                        <div class="fw-semibold text-dark text-truncate small" style="font-size: 0.78rem;"><?= e($dev ? $dev->name : 'No asignado') ?></div>
                                        <div class="text-muted text-truncate" style="font-size: 0.68rem;"><?= e($dev ? $dev->email : '') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge-sede" style="font-size: 0.7rem;"><?= e($p->sede ?: 'Sin Sede') ?></span>
                            </td>
                            <td>
                                <?= $p->statusBadge() ?>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px; width: 60px; background: #E2E8F0;">
                                        <div class="progress-bar bg-emerald rounded" style="width: <?= $p->progressPercentage() ?>%;"></div>
                                    </div>
                                    <span class="small font-monospace fw-bold" style="font-size: 0.74rem;"><?= $p->progressPercentage() ?>%</span>
                                </div>
                            </td>
                            <td>
                                <?php if ($p->final_score): ?>
                                    <span class="badge bg-success" style="font-size: 0.72rem;">QA: <?= $p->final_score ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.70rem;">Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-3">
                                <div class="d-inline-flex gap-1">
                                    <button type="button" class="btn btn-ghost btn-sm py-1 px-2 border" data-sidepanel-project="<?= $p->id ?>" title="Inspección Rápida (SidePanel)" style="font-size: 0.75rem; color: #00ACE3;">
                                        <i class="fa-solid fa-table-columns"></i>
                                    </button>
                                    <a href="<?= url("/projects/{$p->id}") ?>" class="btn btn-bitrix-primary btn-sm py-1 px-2" style="font-size: 0.75rem;">Ver</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (empty($projects)): ?>
    <div class="card-custom p-5 text-center mt-3">
        <i class="fa-solid fa-folder-open text-muted fs-1 mb-2"></i>
        <h6 class="fw-bold text-dark mb-1">No se encontraron proyectos</h6>
        <p class="text-secondary small mb-3">No hay registros que coincidan con los filtros seleccionados.</p>
        <a href="<?= url('/projects') ?>" class="btn btn-brand btn-sm">Limpiar Filtros</a>
    </div>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- JAVASCRIPT: SELECTOR MULTI-MODAL DE VISTAS & BÚSQUEDA INSTANTÁNEA         -->
<!-- ========================================================================= -->
<script>
(function() {
    function initProjectsViewEngine() {
        const switcher = document.getElementById('projectsViewSwitcher');
        const viewGrid = document.getElementById('viewContainerGrid');
        const viewKanban = document.getElementById('viewContainerKanban');
        const viewList = document.getElementById('viewContainerList');
        const searchInput = document.getElementById('projectSearchInput');

        if (!switcher || !viewGrid || !viewKanban || !viewList) return;

        const views = {
            grid: viewGrid,
            kanban: viewKanban,
            list: viewList
        };

        // 1. Conmutador de Vistas
        function setView(viewName) {
            Object.keys(views).forEach(key => {
                if (key === viewName) {
                    views[key].classList.remove('d-none');
                } else {
                    views[key].classList.add('d-none');
                }
            });

            switcher.querySelectorAll('.btn-view-toggle').forEach(btn => {
                if (btn.getAttribute('data-view') === viewName) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });

            localStorage.setItem('devioz_projects_view_mode', viewName);
        }

        switcher.querySelectorAll('.btn-view-toggle').forEach(btn => {
            btn.addEventListener('click', function() {
                const targetView = this.getAttribute('data-view');
                setView(targetView);
            });
        });

        // Restaurar preferencia del usuario o usar grid por defecto
        const savedView = localStorage.getItem('devioz_projects_view_mode') || 'grid';
        if (views[savedView]) {
            setView(savedView);
        }

        // 2. Búsqueda Reactiva Instantánea (Client-Side Filter)
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const term = this.value.trim().toLowerCase();
                const items = document.querySelectorAll('.project-item-card');

                items.forEach(item => {
                    const searchData = item.getAttribute('data-search') || '';
                    if (term === '' || searchData.includes(term)) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        }
    }

    if (document.readyState === 'complete') {
        initProjectsViewEngine();
    } else {
        window.addEventListener('load', initProjectsViewEngine);
    }
})();
</script>
