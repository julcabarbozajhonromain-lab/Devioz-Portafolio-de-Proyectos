<?php

declare(strict_types=1);

/** @var array $projects */
/** @var string $search */
/** @var string $sede */
?>

<!-- Header de la Galería -->
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1 page-header-title text-white">Galería Enterprise de Proyectos</h3>
        <p class="page-header-subtitle text-white-50 small mb-0">Portafolio oficial de software corporativo desarrollado por ingenieros Devioz</p>
    </div>
</div>

<!-- Filtros de la Galería -->
<div class="card-custom p-3 mb-4">
    <form action="<?= url('/gallery') ?>" method="GET" class="row g-2 align-items-center">
        <div class="col-md-8">
            <input type="text" name="q" value="<?= e($search) ?>" class="form-control form-control-custom w-100" placeholder="Buscar por tecnología, código o nombre del software...">
        </div>
        <div class="col-md-3">
            <select name="sede" class="form-select form-select-custom w-100">
                <option value="">Todas las Sedes</option>
                <option value="Cercado" <?= $sede === 'Cercado' ? 'selected' : '' ?>>Cercado</option>
                <option value="Lima Norte" <?= $sede === 'Lima Norte' ? 'selected' : '' ?>>Lima Norte</option>
                <option value="Villa" <?= $sede === 'Villa' ? 'selected' : '' ?>>Villa</option>
            </select>
        </div>
        <div class="col-md-1 text-end">
            <button type="submit" class="btn btn-ghost w-100 py-2" title="Buscar" aria-label="Buscar"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></button>
        </div>
    </form>
</div>

<!-- Grid de Proyectos en Galería -->
<div class="row g-4">
    <?php foreach ($projects as $p): ?>
        <?php $dev = $p->developer(); ?>
        <div class="col-md-6 col-xl-4">
            <div class="card-custom h-100 hover-lift d-flex flex-column overflow-hidden">
                <!-- Portada del Proyecto -->
                <div style="height: 180px; background: #EEF2F4; position: relative; overflow: hidden;">
                    <img src="<?= e($p->coverUrl()) ?>" alt="<?= e($p->name) ?>" class="w-100 h-100" style="object-fit: cover;" loading="lazy">
                    <div style="position: absolute; top: 12px; left: 12px;">
                        <span class="badge bg-dark border border-secondary text-white fw-bold"><?= e($p->code) ?></span>
                    </div>
                    <?php if ($p->final_score): ?>
                        <div style="position: absolute; top: 12px; right: 12px;">
                            <span class="badge bg-success fw-bold">QA Score: <?= $p->final_score ?>/100</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Cuerpo de la Tarjeta -->
                <div class="card-body-custom flex-grow-1 d-flex flex-column justify-content-between p-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                            <span class="bx-client-badge" title="Empresa / Cliente">
                                <i class="fa-solid fa-briefcase"></i>
                                <span><?= e($p->clientName()) ?></span>
                            </span>
                            <span class="badge-sede"><?= e($p->sede ?: 'Sede') ?></span>
                            <span class="small text-secondary"><i class="fa-solid fa-eye" aria-hidden="true"></i> <?= (int)$p->views_count ?> visitas</span>
                        </div>

                        <h5 class="fw-bold mb-2" style="color: #172B4D;"><?= e($p->name) ?></h5>
                        <p class="text-secondary small mb-3 text-truncate-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            <?= e($p->description ?: 'Proyecto corporativo finalizado.') ?>
                        </p>

                        <!-- Chips Tecnológicos -->
                        <div class="d-flex flex-wrap gap-1 mb-3">
                            <?php if (!empty($p->tech_stack) && is_array($p->tech_stack)): ?>
                                <?php foreach (array_slice($p->tech_stack, 0, 3) as $tech): ?>
                                    <span class="tech-chip" style="font-size: 0.72rem; padding: 2px 6px;"><?= e($tech) ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mt-auto pt-2 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2 overflow-hidden">
                            <img src="<?= e($dev ? $dev->avatarUrl() : '') ?>" class="rounded-circle" width="26" height="26" loading="lazy" alt="Avatar" style="object-fit: cover;">
                            <span class="small text-truncate" style="color: #172B4D; max-width: 120px;"><?= e($dev ? $dev->name : 'Autor') ?></span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <?php if (!empty($p->video_url)): ?>
                                <button type="button" class="btn btn-ghost btn-sm p-1 px-2 text-warning d-flex align-items-center gap-1" title="Ver Video Demo" data-video-url="<?= e($p->video_url) ?>" data-video-title="<?= e($p->name) ?>">
                                    <svg width="15" height="15" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                    <span class="d-none d-sm-inline" style="font-size: 0.72rem;">Demo</span>
                                </button>
                            <?php endif; ?>
                            <a href="<?= url("/gallery/{$p->slug}") ?>" class="btn btn-brand btn-sm">Ver Proyecto</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($projects)): ?>
        <div class="col-12">
            <div class="card-custom p-5 text-center">
                <p class="text-secondary fs-5 mb-0">No hay proyectos aprobados en la galería con los filtros actuales.</p>
            </div>
        </div>
    <?php endif; ?>
</div>
