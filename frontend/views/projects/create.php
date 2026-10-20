<?php

declare(strict_types=1);

/** @var array $developers */
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1 page-header-title text-white" style="color: #ffffff !important; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.65), 0 2px 8px rgba(0, 0, 0, 0.35) !important;">Asignar Nuevo Proyecto</h3>
        <p class="page-header-subtitle text-white-50 small mb-0" style="color: rgba(255, 255, 255, 0.85) !important;">Configura el proyecto corporativo y define el cronograma de hitos iniciales</p>
    </div>
    <a href="<?= url('/projects') ?>" class="btn btn-ghost d-inline-flex align-items-center gap-1">&larr; Volver al Catálogo</a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card-custom p-4">
            <form action="<?= url('/projects') ?>" method="POST">
                <?= csrf_field() ?>

                <div class="row g-3 mb-3">
                    <!-- Selección de Desarrollador (Solo Nombre y Apellido) -->
                    <div class="col-md-6">
                        <label for="developer_id" class="form-label-custom">Desarrollador Asignado *</label>
                        <select name="developer_id" id="developer_id" class="form-select form-select-custom w-100" required>
                            <option value="" disabled selected>Selecciona un desarrollador disponible</option>
                            <?php foreach ($developers as $dev): ?>
                                <option value="<?= $dev['id'] ?>" data-sede="<?= e($dev['sede'] ?? '') ?>">
                                    <?= e($dev['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Código Único del Proyecto (Autogenerado Secuencial - Sin asterisco y Readonly) -->
                    <div class="col-md-3">
                        <label for="code" class="form-label-custom d-flex align-items-center justify-content-between">
                            <span>Código del Proyecto</span>
                            <span class="badge bg-primary-subtle text-primary font-monospace" style="font-size: 0.65rem;">Auto</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted" style="font-size: 0.85rem;">
                                <i class="fa-solid fa-barcode"></i>
                            </span>
                            <input type="text" name="code" id="code" class="form-control form-control-custom font-monospace fw-bold border-start-0" value="<?= e($nextCode ?? old('code', 'DEV-' . date('Y') . '-001')) ?>" readonly style="background-color: #F8FAFC !important; color: #059669 !important; cursor: not-allowed;" title="Código generado automáticamente por Devioz OS">
                        </div>
                    </div>

                    <!-- Sede Operativa Devioz -->
                    <div class="col-md-3">
                        <label for="sede" class="form-label-custom">Sede Operativa *</label>
                        <select name="sede" id="sede" class="form-select form-select-custom w-100" required>
                            <option value="Cercado">Cercado</option>
                            <option value="Lima Norte">Lima Norte</option>
                            <option value="Villa">Villa</option>
                        </select>
                    </div>
                </div>

                <!-- Nombre del Proyecto y Cliente -->
                <div class="row g-3 mb-3">
                    <div class="col-md-7">
                        <label for="name" class="form-label-custom">Nombre del Proyecto *</label>
                        <input type="text" name="name" id="name" class="form-control form-control-custom w-100" placeholder="Ej: Sistema de Monitoreo IoT y Telemetría" value="<?= e(old('name')) ?>" required>
                    </div>
                    <div class="col-md-5">
                        <label for="client_name" class="form-label-custom">Empresa / Cliente Solicitante</label>
                        <input type="text" name="client_name" id="client_name" class="form-control form-control-custom w-100" placeholder="Ej: BCP, Rímac, Falabella..." value="<?= e(old('client_name', 'Cliente Corporativo')) ?>">
                    </div>
                </div>

                <!-- Descripción y Alcance -->
                <div class="mb-4">
                    <label for="description" class="form-label-custom mb-2">Descripción y Alcance</label>
                    <textarea name="description" id="description" rows="5" class="form-control form-control-custom w-100" placeholder="Describe el alcance funcional, arquitectura y objetivos del software corporativo..."><?= e(old('description')) ?></textarea>
                </div>

                <!-- Cronograma de Hitos Iniciales -->
                <div class="p-3 rounded mb-4" style="background: var(--bg-card-hover); border: 1px solid var(--border-color);">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-dark mb-0">Hitos Iniciales del Proyecto (1 a 12)</h6>
                        <button type="button" class="btn btn-brand-outline btn-sm" id="addMilestoneBtn">
                            + Agregar Hito
                        </button>
                    </div>

                    <div id="milestonesContainer" class="d-flex flex-column gap-2">
                        <!-- Hito 1 por Defecto -->
                        <div class="row g-2 milestone-row">
                            <div class="col-md-7">
                                <input type="text" name="milestone_names[]" class="form-control form-control-custom" value="Hito 1: Especificación de Requerimientos y Arquitectura" required>
                            </div>
                            <div class="col-md-4">
                                <input type="date" name="milestone_dates[]" class="form-control form-control-custom" value="<?= date('Y-m-d', strtotime('+14 days')) ?>" required>
                            </div>
                            <div class="col-md-1 text-center">
                                <button type="button" class="btn btn-ghost text-danger btn-sm p-2 remove-milestone-btn" title="Eliminar">&times;</button>
                            </div>
                        </div>

                        <!-- Hito 2 por Defecto -->
                        <div class="row g-2 milestone-row">
                            <div class="col-md-7">
                                <input type="text" name="milestone_names[]" class="form-control form-control-custom" value="Hito 2: Prototipado UI/UX y Modelado de Base de Datos" required>
                            </div>
                            <div class="col-md-4">
                                <input type="date" name="milestone_dates[]" class="form-control form-control-custom" value="<?= date('Y-m-d', strtotime('+28 days')) ?>" required>
                            </div>
                            <div class="col-md-1 text-center">
                                <button type="button" class="btn btn-ghost text-danger btn-sm p-2 remove-milestone-btn" title="Eliminar">&times;</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-brand px-4 py-2">
                        <span>Asignar Proyecto al Desarrollador</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const devSelect = document.getElementById('developer_id');
    const sedeSelect = document.getElementById('sede');
    const container = document.getElementById('milestonesContainer');
    const addBtn = document.getElementById('addMilestoneBtn');

    // Auto-seleccionar la sede del desarrollador al elegirlo
    if (devSelect && sedeSelect) {
        devSelect.addEventListener('change', () => {
            const selectedOption = devSelect.options[devSelect.selectedIndex];
            const devSede = selectedOption.getAttribute('data-sede');
            if (devSede && ['Cercado', 'Lima Norte', 'Villa'].includes(devSede)) {
                sedeSelect.value = devSede;
            }
        });
    }

    // Agregar hito dinámicamente
    if (addBtn && container) {
        addBtn.addEventListener('click', () => {
            const rows = container.querySelectorAll('.milestone-row');
            if (rows.length >= 12) {
                alert('El máximo permitido es de 12 hitos por proyecto.');
                return;
            }

            const step = rows.length + 1;
            const row = document.createElement('div');
            row.className = 'row g-2 milestone-row';
            row.innerHTML = `
                <div class="col-md-7">
                    <input type="text" name="milestone_names[]" class="form-control form-control-custom" value="Hito ${step}: Avance y Desarrollo" required>
                </div>
                <div class="col-md-4">
                    <input type="date" name="milestone_dates[]" class="form-control form-control-custom" value="<?= date('Y-m-d', strtotime('+42 days')) ?>" required>
                </div>
                <div class="col-md-1 text-center">
                    <button type="button" class="btn btn-ghost text-danger btn-sm p-2 remove-milestone-btn" title="Eliminar">&times;</button>
                </div>
            `;
            container.appendChild(row);
            bindRemoveButtons();
        });
    }

    function bindRemoveButtons() {
        document.querySelectorAll('.remove-milestone-btn').forEach(btn => {
            btn.onclick = () => {
                if (container.querySelectorAll('.milestone-row').length > 1) {
                    btn.closest('.milestone-row').remove();
                } else {
                    alert('Debe existir al menos un hito en el proyecto.');
                }
            };
        });
    }

    bindRemoveButtons();
});
</script>
