<?php

declare(strict_types=1);

$currentUser = auth()->user();
$pageTitle = $title ?? 'Devioz';
$cleanTitle = preg_replace('/\s*[-—]\s*Devioz(?:\s+(?:Classic|OS))?$/iu', '', $pageTitle);
$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '/';
?>

<header class="topbar">
    <!-- Izquierda: Botón Hamburguesa para Móvil (Sin texto Devioz 24, estilo oficial Devioz) -->
    <div class="d-flex align-items-center flex-shrink-0" id="topbarLeftArea">
        <button type="button" class="sidebar-nav-toggle-btn flex-shrink-0 d-lg-none me-2" id="sidebarToggleBtn" aria-label="Abrir barra lateral" title="Abrir barra lateral">
            <i class="fa-solid fa-bars text-white"></i>
        </button>
    </div>

<?php
$isChatMode = str_contains($currentUri, '/chat') || str_contains($currentUri, '/online');
?>

    <!-- Centro: Pestañas Horizontales Devioz Dinámicas (Feed, Messenger, Proyectos, Tableros, etc.) -->
    <nav class="topbar-horizontal-nav d-none d-lg-flex align-items-center gap-1 mx-2 flex-grow-1" id="topbarHorizontalNav" aria-label="Navegación Devioz">
        <div class="topbar-nav-items-wrapper d-flex align-items-center gap-1" id="topbarVisibleNavItems">
            <?php if ($isChatMode): ?>
                <?php $currentChan = $_GET['channel'] ?? ''; ?>
                <a href="<?= url('/chat') ?>" class="topbar-nav-pill topbar-nav-pill-item <?= empty($currentChan) ? 'active' : '' ?>" data-nav-id="chat" data-nav-label="Chats" data-nav-icon="fa-regular fa-comments">
                    <span>Chats</span>
                </a>
                <a href="<?= url('/chat?channel=general') ?>" class="topbar-nav-pill topbar-nav-pill-item <?= $currentChan === 'general' ? 'active' : '' ?>" data-nav-id="task-chats" data-nav-label="Chats de tareas" data-nav-icon="fa-solid fa-list-check">
                    <span>Chats de tareas</span>
                </a>
                <a href="<?= url('/chat?channel=copilot') ?>" class="topbar-nav-pill topbar-nav-pill-item <?= $currentChan === 'copilot' ? 'active' : '' ?>" data-nav-id="copilot" data-nav-label="CoPilot" data-nav-icon="fa-solid fa-wand-magic-sparkles">
                    <span>CoPilot</span>
                </a>
                <a href="<?= url('/projects') ?>" class="topbar-nav-pill topbar-nav-pill-item" data-nav-id="projects" data-nav-label="Proyectos" data-nav-icon="fa-solid fa-briefcase">
                    <span>Proyectos</span>
                </a>
                <a href="<?= url('/chat?channel=news') ?>" class="topbar-nav-pill topbar-nav-pill-item <?= $currentChan === 'news' ? 'active' : '' ?>" data-nav-id="channels" data-nav-label="Canales" data-nav-icon="fa-solid fa-bullhorn">
                    <span>Canales</span>
                </a>
                <a href="<?= url('/lounge') ?>" class="topbar-nav-pill topbar-nav-pill-item" data-nav-id="open-channels" data-nav-label="Canales abiertos" data-nav-icon="fa-solid fa-earth-americas">
                    <span>Canales abiertos</span>
                </a>
            <?php else: ?>
                <a href="<?= url('/dashboard') ?>" class="topbar-nav-pill topbar-nav-pill-item <?= (str_contains($currentUri, '/dashboard') || $currentUri === '/' || str_ends_with(rtrim($currentUri, '/'), '/public')) ? 'active' : '' ?>" data-nav-id="feed" data-nav-label="Feed" data-nav-icon="fa-regular fa-newspaper">
                    <span>Feed</span>
                </a>
                <a href="<?= url('/chat') ?>" class="topbar-nav-pill topbar-nav-pill-item <?= (str_contains($currentUri, '/chat') || str_contains($currentUri, '/online')) ? 'active' : '' ?>" data-nav-id="messenger" data-nav-label="Messenger" data-nav-icon="fa-regular fa-comments">
                    <span>Messenger</span>
                </a>
                <a href="<?= url('/projects') ?>" class="topbar-nav-pill topbar-nav-pill-item <?= (str_contains($currentUri, '/projects') && !str_contains($currentUri, '/admin')) ? 'active' : '' ?>" data-nav-id="projects" data-nav-label="Proyectos" data-nav-icon="fa-solid fa-briefcase">
                    <span>Proyectos</span>
                </a>
                <?php if (auth()->isAdmin()): ?>
                    <a href="<?= url('/admin/review') ?>" class="topbar-nav-pill topbar-nav-pill-item <?= str_contains($currentUri, '/admin/review') ? 'active' : '' ?>" data-nav-id="boards" data-nav-label="Tableros" data-nav-icon="fa-solid fa-table-columns">
                        <span>Tableros</span>
                    </a>
                    <a href="<?= url('/admin/assignments') ?>" class="topbar-nav-pill topbar-nav-pill-item <?= str_contains($currentUri, '/admin/assignments') ? 'active' : '' ?>" data-nav-id="assignments" data-nav-label="Asignaciones" data-nav-icon="fa-solid fa-user-check">
                        <span>Asignaciones</span>
                    </a>
                <?php elseif (auth()->isDeveloper()): ?>
                    <a href="<?= url('/my-project') ?>" class="topbar-nav-pill topbar-nav-pill-item <?= str_contains($currentUri, '/my-project') ? 'active' : '' ?>" data-nav-id="my-project" data-nav-label="Mi Proyecto" data-nav-icon="fa-solid fa-briefcase">
                        <span>Mi Proyecto</span>
                    </a>
                <?php endif; ?>
                <a href="<?= url('/lounge') ?>" class="topbar-nav-pill topbar-nav-pill-item <?= str_contains($currentUri, '/lounge') ? 'active' : '' ?>" data-nav-id="lounge" data-nav-label="Dev Lounge" data-nav-icon="fa-solid fa-mug-hot">
                    <span>Dev Lounge</span>
                </a>
                <a href="<?= url('/reels') ?>" class="topbar-nav-pill topbar-nav-pill-item <?= str_contains($currentUri, '/reels') ? 'active' : '' ?>" data-nav-id="reels" data-nav-label="Reels" data-nav-icon="fa-solid fa-film">
                    <span>Reels</span>
                </a>
            <?php endif; ?>
        </div>

        <!-- Botón Desplegable Permanente "Más ▾" con Contenedor de Elementos Desbordados -->
        <div class="dropdown flex-shrink-0 position-relative" id="topbarMoreNavDropdown">
            <button type="button" class="topbar-nav-pill border-0 d-inline-flex align-items-center gap-1" id="topbarMoreNavBtn" aria-expanded="false" title="Más opciones de navegación">
                <span>Más</span>
                <i class="fa-solid fa-chevron-down ms-1" style="font-size: 0.65rem;"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2 p-0 devioz-more-dropdown" id="topbarMoreDropdownMenu" style="min-width: 230px; z-index: 1060;">
                <!-- Elementos que se desbordan dinámicamente por ancho de pantalla al 100% de zoom -->
                <div id="topbarMoreDynamicItems" class="p-0"></div>
                <div id="topbarMoreDivider" class="dropdown-divider my-1 d-none"></div>

                <div class="devioz-more-header">Ajustes & Personalización</div>
                <a class="devioz-more-item" href="javascript:void(0)" data-theme-modal-trigger>
                    <i class="fa-solid fa-palette text-warning"></i> Configuración de tema
                </a>
                <a class="devioz-more-item" href="javascript:void(0)" onclick="location.reload()">
                    <i class="fa-solid fa-rotate text-secondary"></i> Restablecer menú
                </a>
                <div class="dropdown-divider my-1"></div>
                <div class="devioz-more-header">Módulos Corporativos</div>
                <a class="devioz-more-item" href="<?= url('/dashboard') ?>">
                    <i class="fa-regular fa-newspaper text-info"></i> Feed Principal
                </a>
                <a class="devioz-more-item" href="<?= url('/gallery') ?>">
                    <i class="fa-solid fa-cube text-warning"></i> Galería Enterprise
                </a>
                <a class="devioz-more-item" href="<?= url('/team') ?>">
                    <i class="fa-solid fa-users text-success"></i> Talento & Red
                </a>
                <?php if (auth()->isAdmin()): ?>
                    <a class="devioz-more-item" href="<?= url('/admin/evaluations') ?>">
                        <i class="fa-solid fa-certificate text-warning"></i> Auditorías QA
                    </a>
                <?php elseif (auth()->isDeveloper()): ?>
                    <a class="devioz-more-item" href="<?= url('/my-project') ?>">
                        <i class="fa-solid fa-briefcase text-primary"></i> Mi Proyecto Activo
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Centro / Derecha: Buscador Global Command Palette + Botón Verde + Perfil -->
    <div class="d-flex align-items-center gap-2 flex-shrink-0" id="topbarRightArea">
        <!-- Buscador Global Estilizado Devioz (Ctrl + K) -->
        <button type="button" class="btn gl-topbar-search d-flex align-items-center gap-2" id="commandPaletteTrigger" title="Búsqueda Rápida en Devioz 24 (Ctrl + K)">
            <i class="fa-solid fa-magnifying-glass search-icon-lupa"></i>
            <span class="d-none d-xl-inline search-label">Buscar en Devioz 24...</span>
            <kbd class="gl-topbar-kbd d-none d-xxl-inline">Ctrl K</kbd>
        </button>

        <?php if (auth()->isAdmin()): ?>
            <a href="<?= url('/projects/create') ?>" class="btn btn-sm btn-devioz-green d-none d-xl-inline-flex align-items-center gap-1" title="Nuevo Proyecto SDLC">
                <i class="fa-solid fa-plus"></i>
                <span>Nuevo Proyecto</span>
            </a>
        <?php endif; ?>

        <?php if ($currentUser): ?>
            <!-- Centro de Notificaciones (Devioz Notification Hub) -->
            <div class="dropdown me-1">
                <button type="button" class="btn topbar-icon-btn position-relative" id="topbarNotificationsBtn" data-bs-toggle="dropdown" aria-expanded="false" title="Centro de Notificaciones">
                    <i class="fa-solid fa-bell fs-6"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" id="topbarNotificationsCount" style="font-size: 0.65rem; padding: 0.25em 0.5em;">0</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2 p-0 devioz-notifications-menu" style="width: 320px; max-width: 90vw;">
                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-light rounded-top">
                        <span class="fw-bold small" style="color: #1F2937;"><i class="fa-solid fa-bell me-1 text-primary"></i> Notificaciones</span>
                        <button type="button" class="btn btn-link p-0 text-decoration-none small text-muted" id="markAllNotificationsReadBtn" style="font-size: 0.75rem;">Marcar leídas</button>
                    </div>
                    <div class="devioz-notifications-list" id="deviozNotificationsList" style="max-height: 320px; overflow-y: auto;">
                        <div class="p-3 text-center text-muted small">Cargando notificaciones...</div>
                    </div>
                </div>
            </div>

            <!-- Acceso Rápido al Chat (Devioz Messenger / Chat con Administración) -->
            <button type="button" class="btn topbar-icon-btn position-relative me-1" id="topbarChatBtn" title="<?= $currentUser->isAdmin() ? 'Messenger Corporativo' : 'Chat y Contacto con la Administración' ?>" aria-label="Abrir Chat" data-user-role="<?= e($currentUser->role ?? 'developer') ?>">
                <i class="fa-solid fa-comment-dots fs-6"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-info d-none" id="topbarChatBadge" style="font-size: 0.65rem; padding: 0.25em 0.5em;">0</span>
            </button>

            <?php
                $nameParts = preg_split('/\s+/', trim($currentUser->name ?? ''));
                $userDisplayName = count($nameParts) >= 2 ? ($nameParts[0] . ' ' . $nameParts[1]) : ($currentUser->name ?? 'Usuario');
            ?>
            <!-- Dropdown Usuario Estilo Devioz -->
            <div class="dropdown">
                <button class="btn btn-user-pill d-flex align-items-center gap-2 p-1 pe-2 rounded-pill" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="<?= e($userDisplayName) ?>">
                    <img src="<?= e($currentUser->avatarUrl()) ?>" alt="<?= e($userDisplayName) ?>" class="rounded-circle" width="30" height="30" style="object-fit: cover;" loading="lazy">
                    <span class="d-none d-md-inline small fw-semibold text-truncate" style="max-width: 140px;"><?= e($userDisplayName) ?></span>
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2 p-0 devioz-profile-popover" style="width: 330px; max-width: 92vw; border-radius: 16px; overflow: hidden;">
                    <!-- Cabecera del Usuario -->
                    <div class="p-3 bg-white border-bottom">
                        <div class="d-flex align-items-center gap-3">
                            <a href="<?= url('/profile') ?>" class="text-decoration-none flex-shrink-0">
                                <img src="<?= e($currentUser->avatarUrl()) ?>" alt="<?= e($currentUser->name) ?>" class="rounded-circle" width="50" height="50" style="object-fit: cover; border: 2px solid #10B981;">
                            </a>
                            <div class="min-w-0 flex-grow-1">
                                <a href="<?= url('/profile') ?>" class="fw-bold text-dark text-truncate d-flex align-items-center gap-1 text-decoration-none" style="font-size: 0.95rem;">
                                    <span><?= e($currentUser->name) ?></span>
                                    <i class="fa-solid fa-chevron-right text-muted" style="font-size: 0.70rem;"></i>
                                </a>
                                <div class="mt-1">
                                    <span class="badge" style="background: rgba(16, 185, 129, 0.12); color: #10B981; font-size: 0.68rem; font-weight: 600;">
                                        <?= $currentUser->isAdmin() ? 'Administrator' : 'Developer' ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Barra de Estado de Jornada (Devioz Style) -->
                        <div class="mt-3 p-2 rounded d-flex justify-content-between align-items-center" style="background: #F8FAFB; border: 1px solid #E0E8ED;">
                            <div class="text-secondary small d-flex align-items-center gap-2" style="font-size: 0.75rem;">
                                <span class="rounded-circle flex-shrink-0" id="profileWorkdayDot" style="width: 8px; height: 8px; display: inline-block; background-color: #94A3B8;"></span>
                                <span id="popoverWorkdayLabel">Fuera del trabajo</span> | <strong class="font-monospace text-dark" id="popoverWorkdayClock">00:00:00</strong>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <button type="button" class="btn btn-sm btn-primary px-3 py-1 fw-semibold d-flex align-items-center gap-1" style="background: #059669; border: none; font-size: 0.75rem; border-radius: 6px;" id="popoverQuickWorkdayBtn">
                                    <i class="fa-solid fa-play" id="popoverQuickWorkdayIcon" style="font-size: 0.65rem;"></i>
                                    <span id="popoverQuickWorkdayLabel">Iniciar</span>
                                </button>
                                <button type="button" class="btn btn-sm btn-light border px-2 py-1 text-danger d-none" id="popoverFinishWorkdayBtn" title="Finalizar jornada" style="font-size: 0.72rem; border-radius: 6px;">
                                    <i class="fa-solid fa-stop"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Tarjetas de Acciones Rápidas (Seguridad y Extensiones / CoPilot) -->
                        <div class="row g-2 mt-2">
                            <div class="col-6">
                                <a href="<?= url('/profile') ?>" class="p-2 rounded border d-flex flex-column align-items-center justify-content-center text-decoration-none text-center" style="background: #FAFBFC; transition: all 0.2s ease;">
                                    <i class="fa-solid fa-shield-halved text-success mb-1" style="font-size: 1.1rem;"></i>
                                    <span class="text-dark small fw-semibold" style="font-size: 0.72rem;">Seguridad</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="<?= url('/chat?channel=copilot') ?>" class="p-2 rounded border d-flex flex-column align-items-center justify-content-center text-decoration-none text-center" style="background: #FAFBFC; transition: all 0.2s ease;">
                                    <i class="fa-solid fa-cube text-primary mb-1" style="font-size: 1.1rem;"></i>
                                    <span class="text-dark small fw-semibold" style="font-size: 0.72rem;">Extensiones</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Enlaces Corporativos -->
                    <div class="list-group list-group-flush" style="font-size: 0.80rem;">
                        <a href="<?= url('/profile') ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 px-3">
                            <span class="d-flex align-items-center gap-2 text-dark">
                                <i class="fa-solid fa-mobile-screen text-secondary"></i>
                                <span>Inicio de sesión móvil rápido</span>
                            </span>
                            <i class="fa-solid fa-chevron-right text-muted" style="font-size: 0.65rem;"></i>
                        </a>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                            <span class="d-flex align-items-center gap-2 text-dark">
                                <i class="fa-brands fa-windows text-info"></i>
                                <span>Aplicación Windows</span>
                            </span>
                            <span class="badge bg-primary px-2 py-1" style="font-size: 0.65rem; border-radius: 4px; cursor: pointer;" title="Descargar instalador de escritorio">Descargar</span>
                        </div>
                        <a href="javascript:void(0)" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 px-3" id="popoverThemeToggle" data-theme-modal-trigger>
                            <span class="d-flex align-items-center gap-2 text-dark">
                                <i class="fa-solid fa-palette text-warning"></i>
                                <span>Tema visual (Devioz)</span>
                            </span>
                            <i class="fa-solid fa-chevron-right text-muted" style="font-size: 0.65rem;"></i>
                        </a>
                        <?php if ($currentUser->isAdmin()): ?>
                            <a href="<?= url('/admin/review') ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 px-3">
                                <span class="d-flex align-items-center gap-2 text-dark">
                                    <i class="fa-solid fa-table-columns text-primary"></i>
                                    <span>Tableros de Revisión</span>
                                </span>
                                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 0.65rem;"></i>
                            </a>
                        <?php else: ?>
                            <a href="<?= url('/my-project') ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 px-3">
                                <span class="d-flex align-items-center gap-2 text-dark">
                                    <i class="fa-solid fa-briefcase text-primary"></i>
                                    <span>Mi Proyecto Activo</span>
                                </span>
                                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 0.65rem;"></i>
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Footer: Pulso & Cerrar Sesión -->
                    <div class="p-2 px-3 bg-light border-top d-flex justify-content-between align-items-center" style="font-size: 0.75rem;">
                        <a href="<?= url('/lounge') ?>" class="text-secondary text-decoration-none">
                            <i class="fa-solid fa-heart-pulse text-danger me-1"></i> Pulso de la compañía
                        </a>
                        <form action="<?= url('/logout') ?>" method="POST" class="m-0 p-0">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-link p-0 text-danger text-decoration-none small fw-semibold" style="font-size: 0.75rem;">
                                Cerrar sesión
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <a href="<?= url('/login') ?>" class="btn btn-brand btn-sm">Iniciar Sesión</a>
        <?php endif; ?>
    </div>
</header>
