<?php

declare(strict_types=1);

/** @var array $members */
/** @var string $search */
/** @var string $sede */
/** @var string $role */
?>

<!-- Header del Directorio -->
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1 page-header-title text-white">Directorio del Equipo</h3>
        <p class="page-header-subtitle text-white-50 small mb-0">Directorio profesional de ingenieros y consultores de software Devioz</p>
    </div>
</div>

<!-- Filtros de Búsqueda -->
<div class="card-custom p-3 mb-4">
    <form action="<?= url('/team') ?>" method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
            <input type="text" name="q" value="<?= e($search) ?>" class="form-control form-control-custom w-100" placeholder="Buscar por nombre, correo o especialidad...">
        </div>
        <div class="col-md-3">
            <select name="sede" class="form-select form-select-custom w-100">
                <option value="">Todas las Sedes</option>
                <option value="Cercado" <?= $sede === 'Cercado' ? 'selected' : '' ?>>Cercado</option>
                <option value="Lima Norte" <?= $sede === 'Lima Norte' ? 'selected' : '' ?>>Lima Norte</option>
                <option value="Villa" <?= $sede === 'Villa' ? 'selected' : '' ?>>Villa</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="role" class="form-select form-select-custom w-100">
                <option value="">Todos los Roles</option>
                <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Administradores</option>
                <option value="developer" <?= $role === 'developer' ? 'selected' : '' ?>>Desarrolladores</option>
            </select>
        </div>
        <div class="col-md-1 text-end">
            <button type="submit" class="btn btn-ghost w-100 py-2" title="Filtrar" aria-label="Filtrar"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></button>
        </div>
    </form>
</div>

<!-- Grid de Miembros -->
<div class="row g-3">
    <?php foreach ($members as $m): ?>
        <?php
        $avatarUrl = !empty($m['avatar'])
            ? asset($m['avatar'])
            : "https://ui-avatars.com/api/?name=" . urlencode($m['name']) . "&background=10b981&color=ffffff&bold=true";
        ?>
        <div class="col-md-6 col-xl-4">
            <div class="card-custom p-3 h-100 hover-lift d-flex flex-column justify-content-between">
                <div>
                    <!-- Header Card: Avatar + Rol -->
                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                        <div class="d-flex align-items-center gap-3">
                            <img src="<?= e($avatarUrl) ?>" alt="<?= e($m['name']) ?>" class="rounded-circle border" width="46" height="46" style="object-fit: cover;" loading="lazy">
                            <div>
                                <h6 class="fw-bold mb-0" style="color: #172B4D;"><?= e($m['name']) ?></h6>
                                <span class="text-secondary small"><?= e($m['email']) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Badges -->
                    <div class="d-flex align-items-center gap-2 my-2">
                        <span class="badge <?= $m['role'] === 'admin' ? 'bg-danger' : 'bg-emerald' ?> p-1 px-2" style="font-size: 0.68rem;">
                            <?= $m['role'] === 'admin' ? 'Administrador' : 'Desarrollador' ?>
                        </span>
                        <?php if (!empty($m['sede'])): ?>
                            <span class="badge-sede"><?= e($m['sede']) ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Headline / Especialidad -->
                    <p class="text-secondary small mb-3">
                        <?= e($m['headline'] ?: 'Consultor y desarrollador de software.') ?>
                    </p>
                </div>

                <!-- Footer: Proyecto Activo -->
                <div class="pt-2 border-top" style="border-color: var(--border-color) !important;">
                    <?php if (!empty($m['project_id'])): ?>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="overflow-hidden me-2">
                                <div class="text-muted" style="font-size: 0.7rem;">PROYECTO EN CURSO</div>
                                <div class="small fw-semibold text-white text-truncate"><?= e($m['project_name']) ?></div>
                            </div>
                            <a href="<?= url("/projects/{$m['project_id']}") ?>" class="btn btn-ghost btn-sm py-1 px-2 text-nowrap">Ver</a>
                        </div>
                    <?php else: ?>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-secondary small fst-italic">Sin proyecto activo</span>
                            <?php if (auth()->isAdmin() && $m['role'] === 'developer'): ?>
                                <a href="<?= url("/projects/create?dev={$m['id']}") ?>" class="btn btn-brand btn-sm py-1 px-2">Asignar</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($members)): ?>
        <div class="col-12">
            <div class="card-custom p-5 text-center">
                <p class="text-secondary fs-5 mb-0">No se encontraron miembros con los criterios seleccionados.</p>
            </div>
        </div>
    <?php endif; ?>
</div>
