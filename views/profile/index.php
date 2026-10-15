<?php

declare(strict_types=1);

/**
 * Devioz OS — Vista de Configuración de Cuenta y Perfil
 * Estilo Visual: Bitrix24 SaaS Moderno (Light Palette, Clean Tabs, Enriched Panels)
 */

$user = $user ?? auth()->user();
$isAdmin = $user->isAdmin();
$hasCustomAvatar = !empty($user->avatar) && str_starts_with($user->avatar, '/uploads/avatars/');
?>

<div class="container-fluid px-2 px-md-4 py-3">
    <!-- Breadcrumb de Navegación Bitrix24 -->
    <nav class="gl-breadcrumbs d-flex align-items-center gap-2 mb-2" aria-label="Breadcrumb">
        <a href="<?= url('/dashboard') ?>" class="gl-breadcrumb-item d-flex align-items-center gap-1 text-decoration-none" style="color: rgba(255, 255, 255, 0.85) !important;">
            <i class="fa-solid fa-house" style="font-size: 0.82rem; color: rgba(255, 255, 255, 0.85) !important;"></i>
            <span class="gl-breadcrumb-text" style="color: rgba(255, 255, 255, 0.85) !important; text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);">Inicio</span>
        </a>
        <span class="gl-breadcrumb-separator" style="color: rgba(255, 255, 255, 0.6) !important;">›</span>
        <span class="gl-breadcrumb-current fw-semibold text-white" style="color: #FFFFFF !important; text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);">Perfil y Ajustes</span>
    </nav>

    <!-- Cabecera de la Sección Estilo Bitrix24 -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-0 page-header-title d-flex align-items-center gap-2 text-white" style="font-size: 22px; color: #FFFFFF !important; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.65), 0 2px 8px rgba(0, 0, 0, 0.35) !important;">
                <i class="fa-solid fa-user-gear" style="color: #00ACE3;"></i>
                <span>Configuración de Cuenta y Perfil</span>
            </h2>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= url('/dashboard') ?>" class="btn btn-bitrix-ghost btn-sm px-3">
                <i class="fa-solid fa-arrow-left me-1"></i>
                <span>Volver al Panel</span>
            </a>
            <?php if ($user->isDeveloper()): ?>
                <a href="<?= url('/my-project') ?>" class="btn btn-bitrix-secondary btn-sm px-3">
                    <i class="fa-solid fa-briefcase me-1"></i>
                    <span>Mi Proyecto</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4">
        <!-- ========================================================================= -->
        <!-- COLUMNA IZQUIERDA: TARJETA DE IDENTIDAD Y RESUMEN BITRIX24                 -->
        <!-- ========================================================================= -->
        <div class="col-lg-4 col-xl-3">
            <!-- Tarjeta Visual del Perfil -->
            <div class="bx-card text-center mb-4">
                <!-- Avatar con Badge Flotante Estilizado y Disparador de Carga -->
                <div class="bx-avatar-card mb-3">
                    <img src="<?= e($user->avatarUrl()) ?>" 
                         alt="<?= e($user->name) ?>" 
                         id="sidebarProfilePreview"
                         class="bx-avatar-frame"
                         width="110" 
                         height="110"
                         loading="lazy">
                    
                    <!-- Indicador de Estado Activo Flotante (Verde Bitrix) -->
                    <span class="bx-avatar-status-dot" title="Cuenta Activa y Verificada" data-bs-toggle="tooltip"></span>

                    <!-- Disparador de Carga de Cámara (Azul Bitrix) -->
                    <label for="avatarInput" 
                           class="bx-avatar-cam-btn" 
                           title="Subir o cambiar foto de perfil"
                           data-bs-toggle="tooltip">
                        <i class="fa-solid fa-camera" style="font-size: 0.85rem;"></i>
                    </label>
                </div>

                <h5 class="fw-bold mb-1 text-truncate" style="font-size: 18px; color: #263238;"><?= e($user->name) ?></h5>
                <p class="small mb-3 text-truncate" style="font-size: 13.5px; color: #78909C;"><?= e($user->email) ?></p>

                <!-- Badges de Rol y Sede -->
                <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap mb-3">
                    <span class="<?= $isAdmin ? 'bx-role-badge-admin' : 'bx-role-badge-dev' ?>">
                        <i class="fa-solid <?= $isAdmin ? 'fa-user-shield' : 'fa-code' ?> me-1"></i>
                        <?= $isAdmin ? 'Administrador' : 'Desarrollador' ?>
                    </span>
                    <?php if ($user->sede): ?>
                        <span class="bx-sede-badge">
                            <i class="fa-solid fa-location-dot me-1" style="color: #78909C;"></i>
                            <?= e($user->sede) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <?php if ($user->headline): ?>
                    <div class="p-2 rounded-2 small mb-3 fst-italic" style="background: #F4F7F9; border: 1px solid #E0E8ED; color: #263238; font-size: 13px;">
                        "<?= e($user->headline) ?>"
                    </div>
                <?php endif; ?>

                <?php if ($hasCustomAvatar): ?>
                    <form action="<?= url('/profile/avatar/remove') ?>" method="POST" class="m-0" onsubmit="return confirm('¿Deseas restablecer el avatar a la versión predeterminada?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100 py-1" style="font-size: 12px; border-radius: 6px;">
                            <i class="fa-solid fa-trash me-1"></i>
                            <span>Restablecer avatar inicial</span>
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <!-- Resumen de Métricas de Cuenta Bitrix24 -->
            <div class="bx-card">
                <div class="fw-bold text-uppercase mb-3" style="font-size: 11px; letter-spacing: 0.05em; color: #78909C;">
                    Resumen de Cuenta
                </div>
                <div class="d-flex flex-column gap-2.5">
                    <div class="d-flex justify-content-between align-items-center pb-2 border-bottom" style="border-color: #E0E8ED !important;">
                        <span style="font-size: 13px; color: #78909C;">Estado:</span>
                        <span class="bx-status-active">
                            <i class="fa-solid fa-circle-check"></i> Activa
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pb-2 border-bottom" style="border-color: #E0E8ED !important;">
                        <span style="font-size: 13px; color: #78909C;">Miembro desde:</span>
                        <span class="fw-semibold" style="font-size: 13px; color: #263238;">
                            <?= !empty($user->created_at) ? date('d/m/Y', strtotime((string)$user->created_at)) : '—' ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span style="font-size: 13px; color: #78909C;">Último acceso:</span>
                        <span class="fw-semibold" style="font-size: 13px; color: #263238;">
                            <?= !empty($user->last_login_at) ? date('d/m/Y H:i', strtotime((string)$user->last_login_at)) : 'Hoy' ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Tarjeta de Tema Visual y Fondo (Configuración Bitrix24 Air) -->
            <div class="bx-card mt-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="fw-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.05em; color: #78909C;">
                        Tema Visual de Espacio
                    </div>
                    <i class="fa-solid fa-palette text-warning" style="font-size: 0.95rem;"></i>
                </div>
                <p class="small text-secondary mb-3" style="font-size: 12px; line-height: 1.45;">
                    Personaliza la imagen de fondo y atmósfera de tu espacio de trabajo corporativo con la colección oficial Bitrix24.
                </p>
                <button type="button" class="btn btn-bitrix-secondary btn-sm w-100 py-2 d-flex align-items-center justify-content-center gap-2" data-theme-modal-trigger>
                    <i class="fa-solid fa-palette text-warning"></i>
                    <span class="fw-semibold">Cambiar tema visual</span>
                </button>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- COLUMNA DERECHA: PESTAÑAS Y PANELES BITRIX24                               -->
        <!-- ========================================================================= -->
        <div class="col-lg-8 col-xl-9">
            <!-- Pestañas Horizontales Estilo Bitrix24 (Borde Inferior Azul #00ACE3) -->
            <ul class="bx-tabs-nav nav" id="profileTabsNav" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="bx-tab-link active" id="tab-personal-btn" data-bs-toggle="tab" data-bs-target="#tab-personal" type="button" role="tab" aria-controls="tab-personal" aria-selected="true">
                        <i class="fa-solid fa-id-card"></i>
                        <span>Datos Personales</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="bx-tab-link" id="tab-security-btn" data-bs-toggle="tab" data-bs-target="#tab-security" type="button" role="tab" aria-controls="tab-security" aria-selected="false">
                        <i class="fa-solid fa-lock"></i>
                        <span>Seguridad y Contraseña</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="bx-tab-link" id="tab-avatar-btn" data-bs-toggle="tab" data-bs-target="#tab-avatar" type="button" role="tab" aria-controls="tab-avatar" aria-selected="false">
                        <i class="fa-solid fa-camera"></i>
                        <span>Foto de Perfil</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="bx-tab-link" id="tab-system-btn" data-bs-toggle="tab" data-bs-target="#tab-system" type="button" role="tab" aria-controls="tab-system" aria-selected="false">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>Estado de Cuenta</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="bx-tab-link" id="tab-theme-btn" data-bs-toggle="tab" data-bs-target="#tab-theme" type="button" role="tab" aria-controls="tab-theme" aria-selected="false">
                        <i class="fa-solid fa-palette text-warning"></i>
                        <span>Tema Visual</span>
                    </button>
                </li>
            </ul>

            <!-- Contenido Dinámico de las Pestañas -->
            <div class="tab-content" id="profileTabsContent">
                <!-- ========================================== -->
                <!-- PESTAÑA 1: DATOS PERSONALES                -->
                <!-- ========================================== -->
                <div class="tab-pane fade show active" id="tab-personal" role="tabpanel" aria-labelledby="tab-personal-btn">
                    <div class="bx-panel">
                        <div class="bx-panel-header">
                            <div>
                                <h3 class="bx-panel-title">Información Personal y Profesional</h3>
                                <p class="bx-panel-subtitle">Actualiza tus nombres, correo y detalles visibles para supervisores y compañeros de proyecto.</p>
                            </div>
                            <span class="badge" style="background: rgba(0, 172, 227, 0.1); color: #00ACE3; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 6px;">
                                <i class="fa-solid fa-pen-to-square me-1"></i> Editable
                            </span>
                        </div>

                        <form action="<?= url('/profile/update') ?>" method="POST">
                            <?= csrf_field() ?>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="bx-form-label">Nombre Completo *</label>
                                    <input type="text" 
                                           name="name" 
                                           id="name" 
                                           class="form-control bx-form-input" 
                                           value="<?= e($user->name) ?>" 
                                           required 
                                           minlength="3" 
                                           maxlength="150" 
                                           placeholder="Ej: Jhon Julca">
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="bx-form-label">Correo Electrónico *</label>
                                    <input type="email" 
                                           name="email" 
                                           id="email" 
                                           class="form-control bx-form-input" 
                                           value="<?= e($user->email) ?>" 
                                           required 
                                           placeholder="usuario@devioz.org">
                                </div>

                                <div class="col-md-12">
                                    <label for="sede" class="bx-form-label">Sede Académica * <span class="text-danger fw-semibold">(Obligatorio)</span></label>
                                    <select name="sede" id="sede" class="form-select bx-form-input" required>
                                        <option value="" disabled <?= empty($user->sede) ? 'selected' : '' ?>>— Selecciona tu Sede Académica —</option>
                                        <option value="Cercado" <?= $user->sede === 'Cercado' ? 'selected' : '' ?>>Cercado</option>
                                        <option value="Lima Norte" <?= $user->sede === 'Lima Norte' ? 'selected' : '' ?>>Lima Norte</option>
                                        <option value="Villa" <?= $user->sede === 'Villa' ? 'selected' : '' ?>>Villa</option>
                                    </select>
                                    <div class="mt-1" style="font-size: 12px; color: #78909C;">
                                        Indica tu sede para coordinaciones de equipo y supervisión con tu líder de sede asignado.
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label for="bio" class="bx-form-label">Biografía / Presentación Breve</label>
                                    <textarea name="bio" 
                                              id="bio" 
                                              class="form-control bx-form-input" 
                                              rows="3" 
                                              maxlength="500" 
                                              placeholder="Comparte brevemente tus intereses tecnológicos, proyectos o metas profesionales..."><?= e($user->bio ?? '') ?></textarea>
                                    <div class="mt-1" style="font-size: 12px; color: #78909C;">
                                        Máximo 500 caracteres. Visible en el directorio del equipo y fichas de proyecto.
                                    </div>
                                </div>

                                <div class="col-12 text-sm-end text-center mt-4">
                                    <button type="submit" class="btn btn-bitrix-primary btn-sm px-4">
                                        <i class="fa-solid fa-floppy-disk me-1"></i>
                                        <span>Guardar Cambios</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- PESTAÑA 2: SEGURIDAD Y CONTRASEÑA          -->
                <!-- ========================================== -->
                <div class="tab-pane fade" id="tab-security" role="tabpanel" aria-labelledby="tab-security-btn">
                    <div class="bx-panel">
                        <div class="bx-panel-header">
                            <div>
                                <h3 class="bx-panel-title">Seguridad y Modificación de Contraseña</h3>
                                <p class="bx-panel-subtitle">Protege tu cuenta actualizando periódicamente tu clave de acceso al entorno.</p>
                            </div>
                            <span class="badge" style="background: rgba(245, 158, 11, 0.1); color: #d97706; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 6px;">
                                <i class="fa-solid fa-shield-halved me-1"></i> Protección Activa
                            </span>
                        </div>

                        <form action="<?= url('/profile/password') ?>" method="POST">
                            <?= csrf_field() ?>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label for="current_password" class="bx-form-label">Contraseña Actual *</label>
                                    <div class="input-group">
                                        <input type="password" 
                                               name="current_password" 
                                               id="current_password" 
                                               class="form-control bx-form-input" 
                                               required 
                                               placeholder="Ingresa tu contraseña actual">
                                        <button class="btn btn-outline-secondary bx-input-group-btn toggle-password-btn" type="button" data-target="current_password" title="Mostrar/ocultar">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="new_password" class="bx-form-label">Nueva Contraseña *</label>
                                    <div class="input-group">
                                        <input type="password" 
                                               name="new_password" 
                                               id="new_password" 
                                               class="form-control bx-form-input" 
                                               required 
                                               minlength="6" 
                                               placeholder="Mínimo 6 caracteres">
                                        <button class="btn btn-outline-secondary bx-input-group-btn toggle-password-btn" type="button" data-target="new_password" title="Mostrar/ocultar">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="new_password_confirmation" class="bx-form-label">Confirmar Nueva Contraseña *</label>
                                    <div class="input-group">
                                        <input type="password" 
                                               name="new_password_confirmation" 
                                               id="new_password_confirmation" 
                                               class="form-control bx-form-input" 
                                               required 
                                               minlength="6" 
                                               placeholder="Repite la nueva contraseña">
                                        <button class="btn btn-outline-secondary bx-input-group-btn toggle-password-btn" type="button" data-target="new_password_confirmation" title="Mostrar/ocultar">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="bx-alert-box">
                                        <i class="fa-solid fa-circle-info" style="color: #00ACE3; font-size: 1rem;"></i>
                                        <span>Recomendación: Usa una combinación de letras mayúsculas, minúsculas, números y caracteres especiales para máxima solidez.</span>
                                    </div>
                                </div>

                                <div class="col-12 text-sm-end text-center mt-4">
                                    <button type="submit" class="btn btn-bitrix-primary btn-sm px-4">
                                        <i class="fa-solid fa-key me-1"></i>
                                        <span>Modificar Contraseña</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- PESTAÑA 3: FOTO DE PERFIL / AVATAR         -->
                <!-- ========================================== -->
                <div class="tab-pane fade" id="tab-avatar" role="tabpanel" aria-labelledby="tab-avatar-btn">
                    <div class="bx-panel">
                        <div class="bx-panel-header">
                            <div>
                                <h3 class="bx-panel-title">Foto de Perfil y Avatar</h3>
                                <p class="bx-panel-subtitle">Sube una fotografía o avatar para personalizar tu cuenta en todo el sistema.</p>
                            </div>
                        </div>

                        <form action="<?= url('/profile/avatar') ?>" method="POST" enctype="multipart/form-data" id="avatarUploadForm">
                            <?= csrf_field() ?>
                            <div class="row align-items-center g-4">
                                <div class="col-sm-auto text-center">
                                    <!-- Preview en Tiempo Real -->
                                    <div class="bx-avatar-card">
                                        <img src="<?= e($user->avatarUrl()) ?>" 
                                             alt="Vista previa" 
                                             id="avatarPreviewImg"
                                             class="bx-avatar-frame" 
                                             width="95" 
                                             height="95"
                                             loading="lazy">
                                        <span class="bx-avatar-status-dot"></span>
                                    </div>
                                </div>
                                <div class="col-sm">
                                    <label for="avatarInput" class="bx-form-label">Seleccionar archivo de imagen:</label>
                                    <input type="file" 
                                           name="avatar" 
                                           id="avatarInput" 
                                           class="form-control bx-form-input" 
                                           accept="image/png, image/jpeg, image/webp" 
                                           required>
                                    <div class="mt-2" style="font-size: 12px; color: #78909C;">
                                        Formatos admitidos: <strong>JPG, PNG o WEBP</strong>. Tamaño máximo: <strong>5 MB</strong>. Proporción cuadrada 1:1 recomendada.
                                    </div>
                                </div>
                                <div class="col-12 text-sm-end text-center mt-4">
                                    <button type="submit" class="btn btn-bitrix-primary btn-sm px-4" id="btnSaveAvatar">
                                        <i class="fa-solid fa-cloud-arrow-up me-1"></i>
                                        <span>Guardar Foto de Perfil</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- PESTAÑA 4: DETALLES Y ESTADO DE CUENTA     -->
                <!-- ========================================== -->
                <div class="tab-pane fade" id="tab-system" role="tabpanel" aria-labelledby="tab-system-btn">
                    <div class="bx-panel">
                        <div class="bx-panel-header">
                            <div>
                                <h3 class="bx-panel-title">Información del Sistema y Trazabilidad</h3>
                                <p class="bx-panel-subtitle">Datos administrativos, permisos y estado de sesión en la plataforma Devioz.</p>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-6 col-md-3">
                                <div class="bx-tile">
                                    <div class="bx-tile-label">Rol en la plataforma</div>
                                    <div class="bx-tile-val text-truncate"><?= $isAdmin ? 'Administrador' : 'Desarrollador' ?></div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="bx-tile">
                                    <div class="bx-tile-label">Estado operacional</div>
                                    <div class="bx-tile-val text-truncate d-flex align-items-center gap-1" style="color: #5ea300;">
                                        <i class="fa-solid fa-circle-check"></i>
                                        <span>Activa</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="bx-tile">
                                    <div class="bx-tile-label">Miembro desde</div>
                                    <div class="bx-tile-val text-truncate">
                                        <?= !empty($user->created_at) ? date('d/m/Y', strtotime((string)$user->created_at)) : '—' ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="bx-tile">
                                    <div class="bx-tile-label">Último acceso</div>
                                    <div class="bx-tile-val text-truncate">
                                        <?= !empty($user->last_login_at) ? date('d/m/Y H:i', strtotime((string)$user->last_login_at)) : 'Hoy' ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- PESTAÑA 5: TEMA VISUAL (BITRIX24 AIR)      -->
                <!-- ========================================== -->
                <div class="tab-pane fade" id="tab-theme" role="tabpanel" aria-labelledby="tab-theme-btn">
                    <div class="bx-panel">
                        <div class="bx-panel-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <h3 class="bx-panel-title">Fondo de Pantalla y Tema Visual</h3>
                                <p class="bx-panel-subtitle">Personaliza la atmósfera de tu espacio de trabajo (Bitrix24 Air). El tema seleccionado se guarda automáticamente para todas tus sesiones.</p>
                            </div>
                            <button type="button" class="btn btn-bitrix-secondary btn-sm px-3" data-theme-modal-trigger>
                                <i class="fa-solid fa-palette text-warning me-1"></i>
                                <span>Abrir Galería Completa</span>
                            </button>
                        </div>

                        <!-- Galería de Fondos de Pantalla Rápida -->
                        <div class="bitrix-theme-grid mt-2" style="max-height: none; padding: 0.5rem 0 !important;">
                            <!-- 1. Amanecer en el Prado -->
                            <div class="bitrix-theme-card" data-theme-key="meadow" title="Amanecer en el Prado" style="background-image: url('<?= asset('images/wallpapers/meadow.webp') ?>');">
                                <span class="bitrix-theme-check-icon"><i class="fa-solid fa-check"></i></span>
                            </div>

                            <!-- 2. Saturno Video -->
                            <div class="bitrix-theme-card" data-theme-key="saturn" title="Saturno (video)" style="background-image: url('<?= asset('images/wallpapers/saturn.webp') ?>');">
                                <div class="bitrix-theme-play-btn"><i class="fa-solid fa-play"></i></div>
                                <span class="bitrix-theme-video-title">Saturno (video)</span>
                                <span class="bitrix-theme-check-icon"><i class="fa-solid fa-check"></i></span>
                            </div>

                            <!-- 3. Bosque Halloween -->
                            <div class="bitrix-theme-card" data-theme-key="halloween" title="Bosque Encantado Halloween" style="background-image: url('<?= asset('images/wallpapers/halloween.webp') ?>');">
                                <span class="bitrix-theme-check-icon"><i class="fa-solid fa-check"></i></span>
                            </div>

                            <!-- 4. Bosque Nevado -->
                            <div class="bitrix-theme-card" data-theme-key="winter" title="Bosque Nevado Invernal" style="background-image: url('<?= asset('images/wallpapers/winter.webp') ?>');">
                                <span class="bitrix-theme-check-icon"><i class="fa-solid fa-check"></i></span>
                            </div>

                            <!-- 5. Flor Cósmica (Por defecto) -->
                            <div class="bitrix-theme-card active" data-theme-key="vibecode" title="Flor Cósmica de Cristal" style="background: radial-gradient(at 85% 15%, #6366f1 0px, transparent 55%), radial-gradient(at 15% 85%, #00ace3 0px, transparent 55%), linear-gradient(135deg, #090e24 0%, #151b45 50%, #201a4e 100%);">
                                <span class="bitrix-theme-badge-default">Por defecto</span>
                                <span class="bitrix-theme-check-icon"><i class="fa-solid fa-check"></i></span>
                            </div>

                            <!-- 6. Nebulosa Púrpura -->
                            <div class="bitrix-theme-card" data-theme-key="nebula" title="Nebulosa Púrpura" style="background-image: url('<?= asset('images/wallpapers/nebula.webp') ?>');">
                                <span class="bitrix-theme-check-icon"><i class="fa-solid fa-check"></i></span>
                            </div>

                            <!-- 7. Medusa de Cristal -->
                            <div class="bitrix-theme-card" data-theme-key="jellyfish" title="Medusa de Cristal (video)" style="background-image: url('<?= asset('images/wallpapers/jellyfish.webp') ?>');">
                                <div class="bitrix-theme-play-btn"><i class="fa-solid fa-play"></i></div>
                                <span class="bitrix-theme-check-icon"><i class="fa-solid fa-check"></i></span>
                            </div>

                            <!-- 8. Tierra desde el Espacio -->
                            <div class="bitrix-theme-card" data-theme-key="earth" title="Tierra desde el Espacio" style="background-image: url('<?= asset('images/wallpapers/earth.webp') ?>');">
                                <span class="bitrix-theme-check-icon"><i class="fa-solid fa-check"></i></span>
                            </div>
                        </div>

                        <div class="mt-3 p-3 rounded-3 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: #F4F7F9; border: 1px solid #E0E8ED;">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa-solid fa-circle-info text-info fs-5"></i>
                                <span class="small text-secondary" style="font-size: 13px;">Haz clic en cualquier miniatura para aplicar el tema en vivo. También puedes subir tu propio fondo personalizado desde la galería.</span>
                            </div>
                            <button type="button" class="btn btn-bitrix-save-lime btn-sm px-4" data-theme-modal-trigger>
                                <i class="fa-solid fa-images me-1"></i> Ver Todos (16 Fondos)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- Script Interactivo: Tabs Hash Sync, Preview de Avatar y Password Ojito   -->
<!-- ========================================================================= -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Sincronización de Hash de URL con Pestañas Bitrix24
    const hashToTab = {
        '#seccion-personal': '#tab-personal-btn',
        '#seccion-seguridad': '#tab-security-btn',
        '#seguridad': '#tab-security-btn',
        '#seccion-avatar': '#tab-avatar-btn',
        '#seccion-cuenta': '#tab-system-btn',
        '#seccion-tema': '#tab-theme-btn',
        '#tema': '#tab-theme-btn'
    };

    if (window.location.hash && hashToTab[window.location.hash]) {
        const btnId = hashToTab[window.location.hash];
        const tabBtn = document.querySelector(btnId);
        if (tabBtn) {
            const bsTab = bootstrap.Tab.getOrCreateInstance(tabBtn);
            bsTab.show();
        }
    }

    // 2. Preview en tiempo real del archivo de imagen seleccionado
    const avatarInput = document.getElementById('avatarInput');
    const avatarPreviewImg = document.getElementById('avatarPreviewImg');
    const sidebarProfilePreview = document.getElementById('sidebarProfilePreview');

    if (avatarInput && avatarPreviewImg) {
        avatarInput.addEventListener('change', (e) => {
            const file = e.target.files?.[0];
            if (file) {
                if (!file.type.startsWith('image/')) {
                    alert('Por favor selecciona un archivo de imagen válido (JPG, PNG o WEBP).');
                    avatarInput.value = '';
                    return;
                }
                if (file.size > 5 * 1024 * 1024) {
                    alert('El archivo seleccionado excede el tamaño máximo permitido de 5MB.');
                    avatarInput.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = (event) => {
                    avatarPreviewImg.src = event.target.result;
                    if (sidebarProfilePreview) {
                        sidebarProfilePreview.src = event.target.result;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // 3. Alternar visibilidad de campos de contraseña (ojito)
    document.querySelectorAll('.toggle-password-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-target');
            const targetInput = document.getElementById(targetId);
            const icon = btn.querySelector('i');
            if (!targetInput || !icon) return;

            if (targetInput.type === 'password') {
                targetInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                targetInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
});
</script>
