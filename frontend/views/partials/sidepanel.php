<?php

declare(strict_types=1);

$currentUser = auth()->user();
if (!$currentUser) return;
?>

<!-- ========================================================================= -->
<!-- Devioz SIDEPANEL SLIDER (CAJÓN LATERAL DESLIZANTE)       -->
<!-- ========================================================================= -->

<!-- 1. Telón de Fondo Desenfundado (Backdrop) -->
<div class="devioz-sidepanel-backdrop" id="deviozSidepanelBackdrop"></div>

<!-- 2. Cajón Lateral Deslizante Principal (Slider) -->
<aside class="devioz-sidepanel-slider" id="deviozSidepanelSlider" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="spProjectTitle">
    <!-- Cabecera del SidePanel -->
    <div class="devioz-sidepanel-header">
        <div class="d-flex align-items-center gap-2 flex-wrap min-w-0">
            <span class="badge font-monospace fw-bold" id="spProjectCode" style="background: rgba(31, 41, 55, 0.08); color: #1F2937; border: 1px solid #DBE2E8; font-size: 0.82rem;">#DEV-000</span>
            <span id="spProjectStatusBadge"></span>
            <span class="ui-client-badge py-1 px-2" style="font-size: 0.75rem;" id="spProjectClient">
                <i class="fa-solid fa-briefcase me-1"></i>
                <span id="spClientNameText">Cliente Corporativo</span>
            </span>
        </div>
        <div class="d-flex align-items-center gap-2 ms-auto flex-shrink-0">
            <a href="#" id="spFullPageLink" class="btn btn-sm btn-ghost p-1 px-2 rounded d-flex align-items-center gap-1" style="font-size: 0.75rem; border: 1px solid #DBE2E8; color: #1F2937;" title="Abrir ficha completa del proyecto">
                <i class="fa-solid fa-up-right-from-square"></i>
                <span class="d-none d-sm-inline">Página Completa</span>
            </a>
            <button type="button" class="btn btn-sm btn-ghost p-1 rounded-circle" id="spCloseBtn" title="Cerrar panel (ESC)" aria-label="Cerrar panel">
                <i class="fa-solid fa-xmark text-secondary fs-5"></i>
            </button>
        </div>
    </div>

    <!-- Cuerpo del SidePanel -->
    <div class="devioz-sidepanel-body" id="deviozSidepanelBody">
        <!-- Estado Cargando (Spinner) -->
        <div id="spLoadingState" class="text-center py-5">
            <div class="spinner-border text-primary mb-2" role="status" style="width: 2rem; height: 2rem; color: #10B981 !important;"></div>
            <div class="text-secondary small">Cargando inspección de proyecto...</div>
        </div>

        <!-- Contenido Dinámico del Proyecto -->
        <div id="spContentState" class="d-none">
            <!-- Título del Proyecto -->
            <h4 class="fw-bold mb-2" id="spProjectTitle" style="color: #1F2937; font-size: 1.35rem; letter-spacing: -0.01em;"></h4>

            <!-- Stack Tecnológico -->
            <div class="d-flex flex-wrap gap-1 mb-3" id="spTechStack"></div>

            <!-- Fila de KPIs Rápidos (Progreso, Score QA, Sede) -->
            <div class="row g-2 mb-4">
                <div class="col-sm-4">
                    <div class="p-2 rounded text-center" style="background: #F8FAFB; border: 1px solid #E0E8ED;">
                        <div class="text-secondary small" style="font-size: 0.68rem;">Avance SDLC</div>
                        <div class="fw-bold font-monospace" style="color: #10B981; font-size: 1.1rem;" id="spProgressText">0%</div>
                        <div class="progress mt-1" style="height: 4px; background: #E2E8F0;">
                            <div class="progress-bar bg-emerald rounded" id="spProgressBar" style="width: 0%;"></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="p-2 rounded text-center" style="background: #F8FAFB; border: 1px solid #E0E8ED;">
                        <div class="text-secondary small" style="font-size: 0.68rem;">Dictamen QA</div>
                        <div class="fw-bold" style="color: #16A34A; font-size: 1.1rem;" id="spScoreText">—</div>
                        <span class="badge bg-success-subtle text-success p-0 px-1 font-monospace" style="font-size: 0.62rem;" id="spScoreBadge">ISO 25010</span>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="p-2 rounded text-center" style="background: #F8FAFB; border: 1px solid #E0E8ED;">
                        <div class="text-secondary small" style="font-size: 0.68rem;">Sede Operativa</div>
                        <div class="fw-bold text-dark text-truncate" style="font-size: 0.95rem;" id="spSedeText">—</div>
                        <span class="text-muted" style="font-size: 0.62rem;">Devioz Hub</span>
                    </div>
                </div>
            </div>

            <!-- Video Demostrativo Directo (Si existe) -->
            <div class="mb-4 d-none" id="spVideoWrapper">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold small text-dark d-flex align-items-center gap-1" style="font-size: 0.78rem;">
                        <i class="fa-solid fa-play text-danger"></i> Video Reel de Sustentación
                    </span>
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25" style="font-size: 0.62rem;">DEMO EN VIVO</span>
                </div>
                <div class="ratio ratio-16x9 rounded overflow-hidden border shadow-sm" style="background: #000; border-color: #DBE2E8 !important;">
                    <iframe id="spVideoIframe" src="" title="Video demo" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe>
                </div>
            </div>

            <!-- Tarjeta del Desarrollador Responsable -->
            <div class="p-3 rounded mb-4 d-flex align-items-center justify-content-between" style="background: #F8FAFB; border: 1px solid #E0E8ED;" id="spDeveloperWrapper">
                <div class="d-flex align-items-center gap-2 min-w-0">
                    <img src="" id="spDevAvatar" class="rounded-circle flex-shrink-0" width="36" height="36" style="object-fit: cover;" alt="Avatar">
                    <div class="min-w-0">
                        <div class="fw-semibold text-dark text-truncate small" id="spDevName">Desarrollador Asignado</div>
                        <div class="text-secondary text-truncate" style="font-size: 0.7rem;" id="spDevEmail">email@devioz.io</div>
                    </div>
                </div>
                <span class="badge-sede" id="spDevSede">Sede</span>
            </div>

            <!-- Descripción y Alcance Funcional -->
            <div class="mb-4">
                <h6 class="fw-bold small mb-2 text-uppercase" style="letter-spacing: 0.04em; color: #5E6C84; font-size: 0.75rem;">
                    <i class="fa-solid fa-file-lines me-1 text-primary"></i> Alcance Ejecutivo & Arquitectura
                </h6>
                <div class="p-3 rounded text-secondary small" style="background: #FFFFFF; border: 1px solid #E0E8ED; line-height: 1.6;" id="spDescriptionContent">
                </div>
            </div>

            <!-- Cronograma de Hitos SDLC -->
            <div class="mb-4">
                <h6 class="fw-bold small mb-2 text-uppercase" style="letter-spacing: 0.04em; color: #5E6C84; font-size: 0.75rem;">
                    <i class="fa-solid fa-list-check me-1 text-success"></i> Hitos y Entregables del Ciclo de Vida
                </h6>
                <div class="d-flex flex-column gap-2" id="spMilestonesContainer"></div>
            </div>
        </div>
    </div>

    <!-- Pie del SidePanel con Acciones Rápidas -->
    <div class="devioz-sidepanel-footer">
        <div class="d-flex align-items-center gap-2" id="spExternalLinks">
            <a href="#" target="_blank" class="btn btn-sm btn-ghost p-1 px-2 border d-none" id="spRepoLink" style="font-size: 0.75rem; color: #1F2937;">
                <i class="fa-brands fa-github me-1"></i> Repositorio
            </a>
            <a href="#" target="_blank" class="btn btn-sm btn-ghost p-1 px-2 border d-none" id="spDemoLink" style="font-size: 0.75rem; color: #1F2937;">
                <i class="fa-solid fa-arrow-up-right-from-square me-1 text-emerald"></i> Demo
            </a>
        </div>
        <div class="ms-auto d-flex gap-2">
            <a href="#" id="spPrimaryActionBtn" class="btn btn-brand btn-sm">
                <span>Ver Ficha Completa</span>
                <i class="fa-solid fa-chevron-right ms-1"></i>
            </a>
        </div>
    </div>
</aside>

