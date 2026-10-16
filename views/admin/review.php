<?php

declare(strict_types=1);

/** @var array $pendingProjects */
/** @var array $inReviewProjects */
/** @var array $approvedProjects */
/** @var array $rejectedProjects */

// Consolidar todos los proyectos para extraer sedes únicas dinámicamente
$allProjects = array_merge($pendingProjects, $inReviewProjects, $approvedProjects, $rejectedProjects);
$availableSedes = [];
foreach ($allProjects as $proj) {
    if (!empty($proj->sede) && !in_array($proj->sede, $availableSedes, true)) {
        $availableSedes[] = $proj->sede;
    }
}
sort($availableSedes);
?>

<div class="gitlab-board-page" id="gitlabBoardContainer">

    <!-- Encabezado del tablero de revisión -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
        <div>
            <h4 class="fw-bold mb-0 page-header-title text-white">Tablero de Revisión & Auditoría QA</h4>
            <div class="page-header-subtitle text-white-50 small mt-1">
                Flujo de aprobación SDLC & compuertas de calidad ISO/IEC 25010
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge-liquid-glass">
                <i class="fa-solid fa-list-check me-1 text-info"></i> Total: <?= count($allProjects) ?> proyectos
            </span>
        </div>
    </div>

    <!-- 2. Barra de Controles y Filtros Estilo GitLab Enterprise -->
    <div class="gl-controls-bar mb-4">
        <!-- Grupo Izquierdo: Selector de Ámbito (Sedes) + Buscador Rápido -->
        <div class="gl-controls-left">
            <!-- Dropdown de Sedes -->
            <div class="dropdown gl-scope-dropdown">
                <button class="btn gl-control-btn dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="sedeFilterBtn">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="opacity-75">
                        <circle cx="18" cy="18" r="3"/>
                        <circle cx="6" cy="6" r="3"/>
                        <path d="M6 9v12m12-3v-3a4 4 0 00-4-4H6"/>
                    </svg>
                    <span id="currentSedeLabel" class="text-truncate" style="max-width: 140px;">Todas las Sedes</span>
                </button>
                <ul class="dropdown-menu gl-dropdown-menu shadow-lg" aria-labelledby="sedeFilterBtn">
                    <li>
                        <button type="button" class="dropdown-item gl-dropdown-item active" data-filter-sede="all">
                            <span class="fw-semibold">Todas las Sedes</span>
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill ms-2"><?= count($allProjects) ?></span>
                        </button>
                    </li>
                    <?php if (!empty($availableSedes)): ?>
                        <li><hr class="dropdown-divider my-1 opacity-25"></li>
                        <?php foreach ($availableSedes as $s): ?>
                            <li>
                                <button type="button" class="dropdown-item gl-dropdown-item" data-filter-sede="<?= e($s) ?>">
                                    <span><?= e($s) ?></span>
                                </button>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Caja de Búsqueda Instantánea con Ícono de Filtro -->
            <div class="gl-search-box">
                <svg class="gl-search-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <input type="text"
                       id="glBoardSearchInput"
                       class="gl-search-input"
                       placeholder="Buscar proyectos por código o nombre..."
                       aria-label="Buscar o filtrar proyectos"
                       autocomplete="off">
                <button type="button" class="gl-search-clear d-none" id="glBoardSearchClear" title="Limpiar filtro">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Grupo Derecho: Acciones -->
        <div class="gl-controls-right">
            <a href="<?= url('/admin/evaluations') ?>" class="btn gl-btn-secondary" title="Auditorías técnicas y dictámenes de calidad QA">
                <span>Auditorías & QA</span>
            </a>
            <a href="<?= url('/projects/create') ?>" class="btn gl-btn-primary" title="Asignar un nuevo proyecto corporativo enterprise">
                <span>+ Asignar Proyecto</span>
            </a>
        </div>
    </div>

    <!-- 3. Tablero Kanban de 4 Columnas -->
    <div class="gl-board-grid kanban-board">
        <!-- ========================================== -->
        <!-- COLUMNA 1: BACKLOG / PENDIENTES           -->
        <!-- ========================================== -->
        <div class="gl-column kanban-column" data-column-status="pending_review">
            <!-- Cabecera Chevron Devioz -->
            <div class="ui-chevron-header ui-chevron-blue">
                <div class="ui-chevron-title">
                    <span>Backlog / Pendientes</span>
                    <span class="ui-chevron-counter"><?= count($pendingProjects) ?></span>
                </div>
                <a href="<?= url('/projects/create') ?>" class="ui-chevron-add" title="Asignar nuevo proyecto">+</a>
            </div>
            <!-- Sub-barra Liquid Glass estilo Devioz -->
            <div class="ui-chevron-subbar">
                <span>Fase Inicial SDLC</span>
                <span><?= count($pendingProjects) ?> items</span>
            </div>

            <!-- Contenedor de Tarjetas (SortableJS Target) -->
            <div class="gl-cards-container kanban-cards-wrapper" data-status="pending_review">
                <?php foreach ($pendingProjects as $p): ?>
                    <?= \App\Core\View::partial('partials/kanban-card', ['p' => $p]) ?>
                <?php endforeach; ?>
                <?php if (empty($pendingProjects)): ?>
                    <div class="kanban-empty-placeholder">
                        <i class="fa-solid fa-inbox mb-2 fs-5 opacity-75 d-block"></i>
                        <span>No hay proyectos pendientes</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- COLUMNA 2: EN REVISIÓN ACTIVA              -->
        <!-- ========================================== -->
        <div class="gl-column kanban-column" data-column-status="in_review">
            <!-- Cabecera Chevron Devioz -->
            <div class="ui-chevron-header ui-chevron-cyan">
                <div class="ui-chevron-title">
                    <span>En Revisión Activa</span>
                    <span class="ui-chevron-counter"><?= count($inReviewProjects) ?></span>
                </div>
                <span class="ui-chevron-add opacity-50" style="cursor: default;" title="Fase en proceso">+</span>
            </div>
            <!-- Sub-barra Liquid Glass estilo Devioz -->
            <div class="ui-chevron-subbar">
                <span>Auditoría en Curso</span>
                <span><?= count($inReviewProjects) ?> items</span>
            </div>

            <!-- Contenedor de Tarjetas (SortableJS Target) -->
            <div class="gl-cards-container kanban-cards-wrapper" data-status="in_review">
                <?php foreach ($inReviewProjects as $p): ?>
                    <?= \App\Core\View::partial('partials/kanban-card', ['p' => $p]) ?>
                <?php endforeach; ?>
                <?php if (empty($inReviewProjects)): ?>
                    <div class="kanban-empty-placeholder">
                        <i class="fa-solid fa-spinner mb-2 fs-5 opacity-75 d-block"></i>
                        <span>No hay proyectos en revisión</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- COLUMNA 3: APROBADOS / CONCLUIDOS          -->
        <!-- ========================================== -->
        <div class="gl-column kanban-column" data-column-status="approved">
            <!-- Cabecera Chevron Devioz -->
            <div class="ui-chevron-header ui-chevron-emerald">
                <div class="ui-chevron-title">
                    <span>Aprobados / Concluidos</span>
                    <span class="ui-chevron-counter"><?= count($approvedProjects) ?></span>
                </div>
                <span class="ui-chevron-add opacity-50" style="cursor: default;" title="Fase de culminación">+</span>
            </div>
            <!-- Sub-barra Liquid Glass estilo Devioz -->
            <div class="ui-chevron-subbar">
                <span>Quality Gate OK</span>
                <span><?= count($approvedProjects) ?> items</span>
            </div>

            <!-- Contenedor de Tarjetas (SortableJS Target) -->
            <div class="gl-cards-container kanban-cards-wrapper" data-status="approved">
                <?php foreach ($approvedProjects as $p): ?>
                    <?= \App\Core\View::partial('partials/kanban-card', ['p' => $p]) ?>
                <?php endforeach; ?>
                <?php if (empty($approvedProjects)): ?>
                    <div class="kanban-empty-placeholder">
                        <i class="fa-solid fa-certificate mb-2 fs-5 opacity-75 d-block"></i>
                        <span>No hay proyectos concluidos</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- COLUMNA 4: OBSERVADOS / RECHAZADOS         -->
        <!-- ========================================== -->
        <div class="gl-column kanban-column" data-column-status="rejected">
            <!-- Cabecera Chevron Devioz -->
            <div class="ui-chevron-header ui-chevron-amber">
                <div class="ui-chevron-title">
                    <span>Observados / Rechazados</span>
                    <span class="ui-chevron-counter"><?= count($rejectedProjects) ?></span>
                </div>
                <span class="ui-chevron-add opacity-50" style="cursor: default;" title="Fase de observación">+</span>
            </div>
            <!-- Sub-barra Liquid Glass estilo Devioz -->
            <div class="ui-chevron-subbar">
                <span>Revisión Solicitada</span>
                <span><?= count($rejectedProjects) ?> items</span>
            </div>

            <!-- Contenedor de Tarjetas (SortableJS Target) -->
            <div class="gl-cards-container kanban-cards-wrapper" data-status="rejected">
                <?php foreach ($rejectedProjects as $p): ?>
                    <?= \App\Core\View::partial('partials/kanban-card', ['p' => $p]) ?>
                <?php endforeach; ?>
                <?php if (empty($rejectedProjects)): ?>
                    <div class="kanban-empty-placeholder">
                        <i class="fa-solid fa-shield-halved mb-2 fs-5 opacity-75 d-block"></i>
                        <span>No hay proyectos observados</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- Script Interactivo: Filtro en Vivo, Filtro por Sede y Pantalla Completa  -->
<!-- ========================================================================= -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('glBoardSearchInput');
    const searchClear = document.getElementById('glBoardSearchClear');
    const sedeLabel = document.getElementById('currentSedeLabel');
    const sedeButtons = document.querySelectorAll('[data-filter-sede]');
    const boardCards = document.querySelectorAll('.gl-card');
    const columns = document.querySelectorAll('.gl-column');
    const fullscreenBtn = document.getElementById('glFullscreenToggleBtn');
    const boardContainer = document.getElementById('gitlabBoardContainer');

    let activeSede = 'all';
    let searchQuery = '';

    // Función de filtrado dinámico en cliente
    function applyBoardFilters() {
        const query = searchQuery.trim().toLowerCase();
        let visibleCounts = {
            pending_review: 0,
            in_review: 0,
            approved: 0,
            rejected: 0
        };

        boardCards.forEach(card => {
            const title = card.getAttribute('data-search-title') || '';
            const code = card.getAttribute('data-search-code') || '';
            const dev = card.getAttribute('data-search-dev') || '';
            const client = card.getAttribute('data-search-client') || '';
            const sede = card.getAttribute('data-search-sede') || '';
            const tech = card.getAttribute('data-search-tech') || '';
            const colStatus = card.closest('.gl-cards-container')?.getAttribute('data-status');

            // Criterio de Sede
            const matchSede = (activeSede === 'all') || (sede === activeSede.toLowerCase());

            // Criterio de Búsqueda
            const matchSearch = !query ||
                title.includes(query) ||
                code.includes(query) ||
                dev.includes(query) ||
                client.includes(query) ||
                sede.includes(query) ||
                tech.includes(query);

            if (matchSede && matchSearch) {
                card.style.display = '';
                if (colStatus && visibleCounts[colStatus] !== undefined) {
                    visibleCounts[colStatus]++;
                }
            } else {
                card.style.display = 'none';
            }
        });

        // Actualizar badges de conteo en cada columna (admite estilos GitLab y Devioz)
        columns.forEach(col => {
            const status = col.getAttribute('data-column-status');
            const countBadge = col.querySelector('.gl-col-counter, .ui-chevron-counter');
            if (countBadge && status && visibleCounts[status] !== undefined) {
                countBadge.textContent = visibleCounts[status];
            }
        });
    }

    // Eventos de Búsqueda
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            searchQuery = this.value;
            if (searchClear) {
                if (searchQuery.length > 0) {
                    searchClear.classList.remove('d-none');
                } else {
                    searchClear.classList.add('d-none');
                }
            }
            applyBoardFilters();
        });
    }

    if (searchClear) {
        searchClear.addEventListener('click', function () {
            if (searchInput) {
                searchInput.value = '';
                searchQuery = '';
                this.classList.add('d-none');
                searchInput.focus();
                applyBoardFilters();
            }
        });
    }

    // Eventos de Filtro de Sede
    sedeButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            sedeButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            activeSede = this.getAttribute('data-filter-sede') || 'all';
            if (sedeLabel) {
                sedeLabel.textContent = (activeSede === 'all') ? 'Todas las Sedes' : activeSede;
            }
            applyBoardFilters();
        });
    });

    // Alternar Pantalla Completa (Modo Tablero Inmersivo)
    if (fullscreenBtn && boardContainer) {
        fullscreenBtn.addEventListener('click', function () {
            const isFull = boardContainer.classList.toggle('gl-board-fullscreen');
            const iconExpand = document.getElementById('glFullscreenIconExpand');
            const iconCompress = document.getElementById('glFullscreenIconCompress');

            if (iconExpand && iconCompress) {
                if (isFull) {
                    iconExpand.classList.add('d-none');
                    iconCompress.classList.remove('d-none');
                } else {
                    iconExpand.classList.remove('d-none');
                    iconCompress.classList.add('d-none');
                }
            }
        });
    }
});
</script>
