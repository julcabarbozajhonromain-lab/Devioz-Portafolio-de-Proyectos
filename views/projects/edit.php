<?php

declare(strict_types=1);

/** @var \App\Models\Project $project */
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center gap-2">
        <span class="badge font-monospace fw-bold" style="background: rgba(31, 41, 55, 0.08); color: #1F2937; border: 1px solid #DBE2E8; font-size: 0.85rem;"><?= e($project->code) ?></span>
        <span class="fw-semibold" style="color: var(--text-primary);"><?= e($project->name) ?></span>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url("/projects/{$project->id}/wizard") ?>" class="btn btn-ghost btn-sm">Asistente Wizard</a>
        <a href="<?= url("/projects/{$project->id}") ?>" class="btn btn-ghost btn-sm">&larr; Volver</a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card-custom p-4">
            <form action="<?= url("/projects/{$project->id}/update") ?>" method="POST" enctype="multipart/form-data" id="projectUpdateForm">
                <?= csrf_field() ?>

                <div class="row g-3 mb-3">
                    <div class="col-md-7">
                        <label for="name" class="form-label-custom">Nombre del Proyecto</label>
                        <input type="text" name="name" id="name" class="form-control form-control-custom" value="<?= e($project->name) ?>" required>
                    </div>
                    <div class="col-md-5">
                        <label for="client_name" class="form-label-custom">Empresa / Cliente Solicitante</label>
                        <input type="text" name="client_name" id="client_name" class="form-control form-control-custom" value="<?= e($project->clientName()) ?>">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-7">
                        <label for="developer_id" class="form-label-custom">Desarrollador Asignado</label>
                        <select name="developer_id" id="developer_id" class="form-select form-control-custom">
                            <option value="" <?= empty($project->developer_id) ? 'selected' : '' ?>>-- Sin Asignar / En Espera --</option>
                            <?php if (!empty($developers)): ?>
                                <?php foreach ($developers as $dev): ?>
                                    <option value="<?= $dev->id ?>" <?= ((int)$project->developer_id === (int)$dev->id) ? 'selected' : '' ?>>
                                        <?= e($dev->name) ?> <?= $dev->sede ? '(' . e($dev->sede) . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="form-text" style="font-size: 0.73rem; color: #626F86;">
                            Reasigna a otro ingeniero o selecciona "Sin Asignar" para liberar al desarrollador actual.
                        </div>
                    </div>
                    <div class="col-md-5">
                        <label for="sede" class="form-label-custom">Sede Operativa</label>
                        <select name="sede" id="sede" class="form-select form-control-custom">
                            <option value="Lima Norte" <?= ($project->sede === 'Lima Norte') ? 'selected' : '' ?>>Lima Norte</option>
                            <option value="Cercado" <?= ($project->sede === 'Cercado') ? 'selected' : '' ?>>Cercado</option>
                            <option value="Villa" <?= ($project->sede === 'Villa') ? 'selected' : '' ?>>Villa</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label-custom">Descripción Ejecutiva</label>
                    <textarea name="description" id="description" rows="3" class="form-control form-control-custom"><?= e($project->description) ?></textarea>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="repository_url" class="form-label-custom">URL del Repositorio de Código</label>
                        <input type="url" name="repository_url" id="repository_url" class="form-control form-control-custom" value="<?= e($project->repository_url) ?>" placeholder="https://github.com/...">
                    </div>
                    <div class="col-md-6">
                        <label for="live_url" class="form-label-custom">URL de Producción / Demo</label>
                        <input type="url" name="live_url" id="live_url" class="form-control form-control-custom" value="<?= e($project->live_url) ?>" placeholder="https://demo.devioz.io">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <label for="video_url" class="form-label-custom">URL de Video (YouTube / Loom)</label>
                        <input type="url" name="video_url" id="video_url" class="form-control form-control-custom" value="<?= e($project->video_url) ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="documentation" class="form-label-custom">Documentación Técnica (Markdown)</label>
                    <textarea name="documentation" id="documentation" rows="6" class="form-control form-control-custom font-monospace" placeholder="# Documentación Técnica..."><?= e($project->documentation) ?></textarea>
                </div>

                <div class="mb-4">
                    <label for="cover_image" class="form-label-custom">Actualizar Imagen de Portada</label>
                    <input type="file" name="cover_image" id="cover_image" class="form-control form-control-custom" accept="image/*" data-preview="coverPreview">
                    <div class="mt-2">
                        <img id="coverPreview" src="<?= e($project->coverUrl()) ?>" alt="Portada" class="rounded border" style="max-height: 120px; object-fit: cover;" loading="lazy">
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-3 border-top" style="border-color: var(--border-color) !important;">
                    <button type="submit" form="projectArchiveForm" class="btn btn-ghost text-danger" data-confirm="¿Deseas archivar este proyecto?">
                        <i class="fa-solid fa-box-archive me-1"></i> Archivar Proyecto
                    </button>

                    <button type="submit" class="btn btn-brand px-4">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Cambios
                    </button>
                </div>
            </form>

            <!-- Formulario de archivo independiente (evita anidamiento inválido) -->
            <form id="projectArchiveForm" action="<?= url("/projects/{$project->id}/delete") ?>" method="POST" class="d-none">
                <?= csrf_field() ?>
            </form>
        </div>
    </div>
</div>

