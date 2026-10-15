<?php

declare(strict_types=1);

/** @var \App\Models\Project $project */
/** @var \App\Models\User|null $developer */
/** @var array $milestones */
?>

<div class="mb-3">
    <a href="<?= url('/gallery') ?>" class="text-secondary small text-decoration-none">&larr; Volver a la Galería</a>
</div>

<!-- Hero del Proyecto en Portafolio con Alto Contraste -->
<!-- Encabezado Corporativo del Proyecto en Galería (Estilo Bitrix24) -->
<div class="card-custom p-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                <span class="badge font-monospace fw-bold" style="background: rgba(23, 43, 77, 0.08); color: #172B4D; border: 1px solid #DBE2E8; font-size: 0.85rem;"><?= e($project->code) ?></span>
                <span class="badge bg-success">Aprobado</span>
                <span class="badge-sede"><?= e($project->sede) ?></span>
                <?php if ($project->final_score): ?>
                    <span class="badge bg-emerald">QA Score: <?= $project->final_score ?><?= (float)$project->final_score > 10 ? '/100' : '/10' ?></span>
                <?php endif; ?>
            </div>
            <h2 class="fw-bold mb-1 project-hero-title" style="color: #172B4D; font-size: 1.5rem; letter-spacing: -0.01em;"><?= e($project->name) ?></h2>
            <div class="text-secondary small mt-1">
                <span>Cliente / Entidad: <strong class="text-dark"><?= e($project->clientName()) ?></strong></span>
                <span class="mx-2">&bull;</span>
                <span>Publicado en Portafolio Oficial</span>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap align-items-center">
            <?php if ($project->repository_url): ?>
                <a href="<?= e($project->repository_url) ?>" target="_blank" class="btn btn-ghost btn-sm" style="border: 1px solid #DBE2E8; color: #172B4D;">
                    <i class="fa-brands fa-github me-1"></i>
                    <span>Código Fuente</span>
                </a>
            <?php endif; ?>
            <?php if ($project->live_url): ?>
                <a href="<?= e($project->live_url) ?>" target="_blank" class="btn btn-brand btn-sm">
                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>
                    <span>Probar en Vivo</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Contenido Principal -->
    <div class="col-lg-8">
        <!-- Descripción y Video Directo Inline -->
        <div class="card-custom p-4 mb-4">
            <div class="text-secondary"><?= render_markdown($project->description ?: 'No se ha provisto una descripción detallada.') ?></div>

            <div class="mt-4">
                <h6 class="fw-bold small mb-2" style="color: #172B4D;">Stack Tecnológico Empleado</h6>
                <div class="d-flex flex-wrap gap-2">
                    <?php if (!empty($project->tech_stack) && is_array($project->tech_stack)): ?>
                        <?php foreach ($project->tech_stack as $tech): ?>
                            <span class="tech-chip"><?= e($tech) ?></span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Video Demostrativo Directo Inline (Sin modales ni clics extra) -->
            <?php if (!empty($project->video_url)): ?>
                <div class="mt-4 pt-3 border-top" style="border-color: var(--border-color) !important;">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="fw-bold small mb-0 d-flex align-items-center gap-2" style="color: #172B4D;">
                            <svg width="18" height="18" class="text-danger" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            <span>Demostración en Video / Sustentación</span>
                        </h6>
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 font-monospace" style="font-size: 0.68rem;">REPRODUCTOR DIRECTO</span>
                    </div>

                    <div class="ratio ratio-16x9 rounded-3 overflow-hidden shadow-lg border border-secondary border-opacity-25" style="background: #000;">
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

        <!-- Galería de Capturas de Pantalla -->
        <?php 
            $gallery = is_array($project->gallery_images) ? $project->gallery_images : (json_decode($project->gallery_images ?? '[]', true) ?: []);
            if (!empty($gallery)): 
        ?>
            <div class="card-custom p-4 mb-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold mb-0 d-flex align-items-center gap-2" style="color: #172B4D;">
                        <i class="fa-solid fa-images text-emerald"></i>
                        <span>Capturas del Sistema y Vistas de Pantalla</span>
                    </h5>
                    <span class="badge bg-secondary font-monospace" style="font-size: 0.68rem;"><?= count($gallery) ?> Vistas</span>
                </div>
                <div class="row g-3">
                    <?php foreach ($gallery as $imgUrl): ?>
                        <div class="col-sm-6">
                            <a href="<?= e(asset($imgUrl)) ?>" target="_blank" rel="noopener noreferrer" class="d-block rounded-3 overflow-hidden border border-secondary border-opacity-25 hover-lift position-relative" style="height: 190px;" title="Ver captura en tamaño completo">
                                <img src="<?= e(asset($imgUrl)) ?>" alt="Captura de Pantalla" class="w-100 h-100" style="object-fit: cover;" loading="lazy">
                                <div class="position-absolute bottom-0 start-0 end-0 p-2 text-center text-white small" style="background: rgba(0,0,0,0.6); backdrop-filter: blur(2px); font-size: 0.72rem;">
                                    <i class="fa-solid fa-up-right-from-square me-1"></i> Ampliar vista
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Documentación Técnica -->
        <?php if (!empty($project->documentation)): ?>
            <div class="card-custom p-4 mb-4">
                <h5 class="fw-bold mb-3" style="color: #172B4D;">Documentación y Alcance</h5>
                <div class="p-3 rounded font-monospace small text-secondary" style="background: #F4F5F7; border: 1px solid #DBE2E8; white-space: pre-wrap;">
                    <?= e($project->documentation) ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Hitos Completados -->
        <div class="card-custom p-4">
            <h5 class="fw-bold mb-3" style="color: #172B4D;">Hitos del Ciclo de Vida</h5>
            <div class="milestone-timeline">
                <?php foreach ($milestones as $m): ?>
                    <div class="milestone-item completed">
                        <div class="milestone-dot">
                            <svg width="10" height="10" fill="none" stroke="#fff" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div class="card-custom p-3">
                            <div class="fw-bold small" style="color: #172B4D;"><?= e($m->name) ?></div>
                            <div class="small text-secondary"><?= e($m->description ?: 'Hito validado y aprobado.') ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Columna Lateral: Perfil del Autor -->
    <div class="col-lg-4">
        <div class="card-custom p-4 mb-4">
            <h6 class="fw-bold small mb-3" style="color: #172B4D;">Autor del Software</h6>
            <div class="text-center mb-3">
                <img src="<?= e($developer ? $developer->avatarUrl() : '') ?>" class="rounded-circle border mb-2" width="72" height="72" style="object-fit: cover;" loading="lazy" alt="Avatar">
                <h5 class="fw-bold mb-0" style="color: #172B4D;"><?= e($developer ? $developer->name : 'Desarrollador Devioz') ?></h5>
                <span class="badge-sede mt-1"><?= e($developer ? $developer->sede : 'Sede') ?></span>
            </div>
            <p class="text-secondary small text-center mb-0">
                <?= e($developer ? ($developer->bio ?: $developer->headline ?: 'Desarrollador del equipo de ingeniería de Devioz.') : '') ?>
            </p>
        </div>

        <div class="card-custom p-3 text-center">
            <span class="text-secondary small">Vistas del Proyecto:</span>
            <div class="fs-4 fw-bold text-emerald"><?= (int)$project->views_count ?></div>
        </div>
    </div>
</div>
