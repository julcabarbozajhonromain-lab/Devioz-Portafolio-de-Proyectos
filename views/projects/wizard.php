<?php

declare(strict_types=1);

/** @var \App\Models\Project $project */
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1" style="color: #172B4D;">Asistente de Proyecto (Wizard)</h3>
        <p class="text-secondary small mb-0">Configura paso a paso los 5 modulos clave de <?= e($project->code) ?></p>
    </div>
    <a href="<?= url("/projects/{$project->id}") ?>" class="btn btn-ghost">&larr; Volver al Proyecto</a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <!-- Barra de Progreso de Pasos -->
        <div class="d-flex justify-content-between mb-4 position-relative">
            <?php
            $steps = [
                1 => 'General',
                2 => 'Cronograma',
                3 => 'Multimedia',
                4 => 'Documentacion',
                5 => 'Resumen',
            ];
            ?>
            <?php foreach ($steps as $num => $label): ?>
                <div class="text-center wizard-step-indicator" data-step="<?= $num ?>" style="z-index: 2; cursor: pointer;">
                    <div class="rounded-circle d-grid place-items-center mx-auto mb-1 fw-bold step-circle <?= $num === 1 ? 'bg-primary text-white' : 'bg-light border text-secondary' ?>" style="width: 36px; height: 36px;">
                        <?= $num ?>
                    </div>
                    <span class="small text-secondary"><?= $label ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Formulario Wizard -->
        <div class="card-custom p-4">
            <form action="<?= url("/projects/{$project->id}/update") ?>" method="POST" id="wizardForm" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <!-- PASO 1: GENERAL -->
                <div class="wizard-pane active" id="wizardStep1">
                    <h5 class="fw-bold mb-3" style="color: #172B4D;">Paso 1: Informacion General</h5>
                    <div class="mb-3">
                        <label class="form-label-custom">Nombre del Proyecto</label>
                        <input type="text" name="name" class="form-control form-control-custom" value="<?= e($project->name) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Descripcion del Proyecto</label>
                        <textarea name="description" rows="4" class="form-control form-control-custom"><?= e($project->description) ?></textarea>
                    </div>
                </div>

                <!-- Paso 2: Cronograma -->
                <div class="wizard-pane d-none" id="wizardStep2">
                    <h5 class="fw-bold mb-3" style="color: #172B4D;">Paso 2: Cronograma de Hitos</h5>
                    <p class="text-secondary small">Revisa el estado de los hitos asociados al proyecto:</p>
                    <ul class="list-group list-group-flush mb-3" style="--bs-list-group-bg: transparent;">
                        <?php foreach ($project->milestones() as $m): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center border-bottom p-2" style="border-color: var(--border-color) !important;">
                                <span class="fw-medium text-dark small"><?= e($m->name) ?></span>
                                <span class="badge bg-secondary"><?= format_date($m->due_date) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- PASO 3: MULTIMEDIA -->
                <div class="wizard-pane d-none" id="wizardStep3">
                    <h5 class="fw-bold mb-3" style="color: #172B4D;">Paso 3: Enlaces y Multimedia</h5>
                    <div class="mb-3">
                        <label class="form-label-custom">URL del Repositorio de Codigo</label>
                        <input type="url" name="repository_url" class="form-control form-control-custom" value="<?= e($project->repository_url) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">URL de Demo / Produccion</label>
                        <input type="url" name="live_url" class="form-control form-control-custom" value="<?= e($project->live_url) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">URL de Video (YouTube / Loom / Vimeo)</label>
                        <input type="url" name="video_url" class="form-control form-control-custom" value="<?= e($project->video_url) ?>">
                    </div>
                </div>

                <!-- PASO 4: DOCUMENTACION -->
                <div class="wizard-pane d-none" id="wizardStep4">
                    <h5 class="fw-bold mb-3" style="color: #172B4D;">Paso 4: Documentacion Tecnica</h5>
                    <div class="mb-3">
                        <label class="form-label-custom">Contenido Markdown</label>
                        <textarea name="documentation" rows="8" class="form-control form-control-custom font-monospace"><?= e($project->documentation) ?></textarea>
                    </div>
                </div>

                <!-- Paso 5: Resumen -->
                <div class="wizard-pane d-none" id="wizardStep5">
                    <h5 class="fw-bold mb-3" style="color: #172B4D;">Paso 5: Resumen y Confirmacion</h5>
                    <div class="p-3 rounded mb-3" style="background: #F8FAFB; border: 1px solid var(--border-color);">
                        <div class="mb-2"><strong class="text-secondary">Codigo:</strong> <span class="fw-bold" style="color: #00ACE3;"><?= e($project->code) ?></span></div>
                        <div class="mb-2"><strong class="text-secondary">Nombre:</strong> <span class="fw-medium text-dark"><?= e($project->name) ?></span></div>
                        <div class="mb-2"><strong class="text-secondary">Sede:</strong> <span class="badge-sede"><?= e($project->sede) ?></span></div>
                        <div class="mb-2"><strong class="text-secondary">Estado:</strong> <?= $project->statusBadge() ?></div>
                    </div>
                    <p class="text-secondary small">Al hacer clic en Guardar Configuracion, se actualizaran los datos del proyecto.</p>
                </div>

                <!-- Botones de Navegacion del Wizard -->
                <div class="d-flex justify-content-between pt-4 border-top mt-4" style="border-color: var(--border-color) !important;">
                    <button type="button" class="btn btn-ghost" id="prevStepBtn" style="display: none;">&larr; Anterior</button>
                    <div class="ms-auto d-flex gap-2">
                        <button type="button" class="btn btn-brand" id="nextStepBtn">Siguiente &rarr;</button>
                        <button type="submit" class="btn btn-brand" id="submitWizardBtn" style="display: none;">Guardar Configuracion</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    let currentStep = 1;
    const totalSteps = 5;

    const prevBtn = document.getElementById('prevStepBtn');
    const nextBtn = document.getElementById('nextStepBtn');
    const submitBtn = document.getElementById('submitWizardBtn');

    function updateWizard(step) {
        document.querySelectorAll('.wizard-pane').forEach((pane, idx) => {
            if (idx + 1 === step) {
                pane.classList.remove('d-none');
            } else {
                pane.classList.add('d-none');
            }
        });

        document.querySelectorAll('.wizard-step-indicator').forEach((indicator, idx) => {
            const circle = indicator.querySelector('.step-circle');
            if (idx + 1 === step) {
                circle.className = 'rounded-circle d-grid place-items-center mx-auto mb-1 fw-bold step-circle bg-primary text-white';
            } else if (idx + 1 < step) {
                circle.className = 'rounded-circle d-grid place-items-center mx-auto mb-1 fw-bold step-circle bg-success text-white';
            } else {
                circle.className = 'rounded-circle d-grid place-items-center mx-auto mb-1 fw-bold step-circle bg-light border text-secondary';
            }
        });

        prevBtn.style.display = step > 1 ? 'inline-flex' : 'none';
        nextBtn.style.display = step < totalSteps ? 'inline-flex' : 'none';
        submitBtn.style.display = step === totalSteps ? 'inline-flex' : 'none';
    }

    nextBtn.addEventListener('click', () => {
        if (currentStep < totalSteps) {
            currentStep++;
            updateWizard(currentStep);
        }
    });

    prevBtn.addEventListener('click', () => {
        if (currentStep > 1) {
            currentStep--;
            updateWizard(currentStep);
        }
    });

    document.querySelectorAll('.wizard-step-indicator').forEach(ind => {
        ind.addEventListener('click', () => {
            currentStep = parseInt(ind.getAttribute('data-step'));
            updateWizard(currentStep);
        });
    });
});
</script>
