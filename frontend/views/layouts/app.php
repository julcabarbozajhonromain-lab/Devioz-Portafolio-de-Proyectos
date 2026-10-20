<?php

declare(strict_types=1);

use App\Core\View;
$appName = (string) config('app.name', 'Devioz 24');
?>
<!DOCTYPE html>
<html lang="es" data-theme="light" data-ui-mode="devioz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta name="app-url" content="<?= url() ?>">
    <title><?= e(isset($title) ? $title . ' — ' . $appName : $appName . ' — Plataforma Corporativa') ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= asset('images/devioz-icon.png') ?>">

    <!-- Tipografía Devioz Oficial: Open Sans, Helvetica Neue, Helvetica, Arial, sans-serif -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Font Awesome 6.5.1 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Estilos Customizados Devioz OS v3.0 -->
    <link rel="stylesheet" href="<?= asset('css/custom.css') ?>">

    <!-- Pre-hidratación inmediata de Sidebar y Tema Visual para evitar parpadeos -->
    <script>
        (function() {
            document.documentElement.setAttribute('data-theme', 'light');
            if (localStorage.getItem('devioz_sidebar_collapsed') === 'true' && window.innerWidth >= 992) {
                document.documentElement.classList.add('sidebar-collapsed');
            }
            const savedTheme = localStorage.getItem('devioz_visual_theme') || 'vibecode';
            document.documentElement.classList.add('theme-' + savedTheme);
        })();
    </script>
</head>
<body>
    <script>
        (function() {
            if (document.documentElement.classList.contains('sidebar-collapsed')) {
                document.body.classList.add('sidebar-collapsed');
            }
            const savedTheme = localStorage.getItem('devioz_visual_theme') || 'vibecode';
            document.body.classList.add('theme-' + savedTheme);
        })();
    </script>
    <!-- Enlace de accesibilidad para lectores de pantalla y navegación por teclado (WCAG) -->
    <a href="#main-content" class="skip-to-content-link">Saltar al contenido principal</a>

    <div class="app-wrapper">
        <!-- Barra Lateral (Sidebar) -->
        <?= View::partial('partials/sidebar') ?>

        <!-- Contenedor Principal -->
        <div class="main-wrapper">
            <!-- Barra Superior (Topbar) -->
            <?= View::partial('partials/navbar', ['title' => $title ?? $appName]) ?>

            <!-- Área de Contenido Dinámico -->
            <main class="main-content" id="main-content" tabindex="-1">
                <!-- Mensajes Flash de Sesión -->
                <?= View::partial('partials/flash') ?>

                <!-- Vista Específica -->
                <?= $content ?>
            </main>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 1. VIDEO LIGHTBOX MODAL FLOTANTE (FASE 1: CERO PESTAÑAS EXTERNAS)          -->
    <!-- ========================================================================= -->
    <div class="modal fade" id="videoLightboxModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content video-lightbox-content">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-semibold text-white d-flex align-items-center gap-2" id="videoLightboxTitle">
                        <span class="badge bg-emerald p-1 px-2">Demo</span> Video de Sustentación
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="video-lightbox-responsive" id="videoLightboxContainer">
                        <!-- Iframe o video player inyectado dinámicamente por app.js -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 2. COMMAND PALETTE (CTRL + K SPOTLIGHT) (FASE 3)                          -->
    <!-- ========================================================================= -->
    <div class="command-palette-backdrop" id="commandPaletteBackdrop">
        <div class="command-palette-card">
            <div class="command-palette-header">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="text-secondary">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" id="commandPaletteInput" class="command-palette-input" placeholder="Buscar proyecto, desarrollador, sede o hitos... (ESC para salir)" autocomplete="off">
                <span class="badge bg-dark border border-secondary text-secondary">ESC</span>
            </div>
            <div class="command-palette-results" id="commandPaletteResults">
                <div class="p-3 text-secondary small text-center">Escribe para buscar en tiempo real...</div>
            </div>
            <div class="command-palette-footer d-flex justify-content-between align-items-center px-3 py-2 border-top border-secondary border-opacity-25 text-secondary" style="font-size: 0.72rem;">
                <span><kbd class="bg-dark text-white border border-secondary">↑</kbd> <kbd class="bg-dark text-white border border-secondary">↓</kbd> Navegar</span>
                <span><kbd class="bg-dark text-white border border-secondary">↵</kbd> Abrir</span>
                <span><kbd class="bg-dark text-white border border-secondary">Ctrl</kbd> + <kbd class="bg-dark text-white border border-secondary">K</kbd> Alternar</span>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 3. ASISTENTE LABVA FLOTANTE (BOT REACTIVO DUAL ENGINE EN TIEMPO REAL)     -->
    <!-- ========================================================================= -->
    <div class="bot-launchers-container" id="botLaunchersContainer">
        <button type="button" class="bot-single-launcher-btn" id="botAvatarSofiaBtn" title="Conversemos con LABVA" aria-label="Abrir asistente LABVA">
            <div class="bot-launcher-avatar-wrap">
                <div class="labva-orb-container state-idle" id="labvaLauncherOrb" style="width: 38px; height: 38px;">
                    <svg class="labva-orb-svg" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                        <circle class="labva-aura" cx="50" cy="50" r="47"></circle>
                        <circle class="labva-sphere" cx="50" cy="50" r="44"></circle>
                        <ellipse class="labva-specular" cx="50" cy="18" rx="22" ry="7"></ellipse>
                        <g class="labva-eyes-wrap">
                            <rect class="labva-eye eye-left" x="52" y="34" width="7.5" height="15" rx="3.75"></rect>
                            <rect class="labva-eye eye-right" x="65" y="33" width="7.5" height="14" rx="3.75"></rect>
                        </g>
                    </svg>
                </div>
                <span class="bot-status-indicator"></span>
            </div>
            <span class="bot-launcher-label">Conversemos con LABVA</span>
        </button>
    </div>

    <div class="devioz-bot-window" id="deviozBotWindow">
        <!-- Cabecera de la ventana estilo LABVA AI -->
        <div class="bot-window-header">
            <div class="d-flex align-items-center gap-2">
                <div class="labva-orb-container state-idle" id="labvaHeaderOrb" style="width: 38px; height: 38px;">
                    <svg class="labva-orb-svg" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                        <circle class="labva-aura" cx="50" cy="50" r="47"></circle>
                        <circle class="labva-sphere" cx="50" cy="50" r="44"></circle>
                        <ellipse class="labva-specular" cx="50" cy="18" rx="22" ry="7"></ellipse>
                        <g class="labva-eyes-wrap">
                            <rect class="labva-eye eye-left" x="52" y="34" width="7.5" height="15" rx="3.75"></rect>
                            <rect class="labva-eye eye-right" x="65" y="33" width="7.5" height="14" rx="3.75"></rect>
                        </g>
                    </svg>
                </div>
                <div>
                    <div class="fw-bold text-white small d-flex align-items-center gap-1" id="botHeaderTitle">
                        <span>LABVA</span>
                        <span class="badge" style="background-color: #10B981; font-size: 0.6rem; padding: 2px 6px;">AI</span>
                    </div>
                    <div class="bot-header-status" id="labvaStatusText"><span class="bot-status-dot-inline">●</span> En línea &bull; Asistente <?= e($appName) ?></div>
                </div>
            </div>
            <button type="button" class="btn-close-bot" id="deviozBotClose" aria-label="Cerrar chat">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Cuerpo de conversación con orbe a la izquierda del mensaje -->
        <div class="bot-window-body" id="deviozBotBody">
            <div class="bot-message-row">
                <div class="labva-orb-container state-idle flex-shrink-0" style="width: 32px; height: 32px;">
                    <svg class="labva-orb-svg" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                        <circle class="labva-sphere" cx="50" cy="50" r="44"></circle>
                        <g class="labva-eyes-wrap">
                            <rect class="labva-eye eye-left" x="52" y="34" width="7.5" height="15" rx="3.75"></rect>
                            <rect class="labva-eye eye-right" x="65" y="33" width="7.5" height="14" rx="3.75"></rect>
                        </g>
                    </svg>
                </div>
                <div class="bot-speech-bubble" id="botInitialBubble">
                    ¡Hola! Soy <strong>LABVA</strong>, el asistente de inteligencia operativa en <?= e($appName) ?>. ¿En qué te puedo apoyar hoy? Puedes consultarme sobre proyectos activos, auditorías QA o talento técnico corporativo.
                </div>
            </div>

            <div class="bot-suggestions mt-2 d-flex flex-wrap gap-1 ps-4">
                <button type="button" class="bot-chip-btn" data-bot-prompt="¿Cuántos proyectos hay y cuál es su estado actual?">Proyectos activos</button>
                <button type="button" class="bot-chip-btn" data-bot-prompt="¿Hay talento técnico disponible para asignación?">Talento disponible</button>
                <button type="button" class="bot-chip-btn" data-bot-prompt="¿Cuáles son los proyectos con mayores scores de calidad QA?">Top Scores QA</button>
                <button type="button" class="bot-chip-btn" data-bot-prompt="¿Cuáles son los horarios de los check-ins diarios?"><i class="fa-solid fa-clock me-1"></i> Check-ins diarios</button>
                <button type="button" class="bot-chip-btn" data-bot-prompt="¿Cuáles son los criterios y estándares de evaluación QA?">Criterios de Calidad QA</button>
            </div>
        </div>

        <!-- Pie con Input Pill y Botón Send circular color azul -->
        <div class="bot-window-footer">
            <form class="bot-input-pill-wrapper" id="deviozBotForm">
                <input type="text" id="deviozBotInput" class="bot-input-field" placeholder="Pregúntame algo sobre proyectos o talento..." autocomplete="off">
                <button type="submit" class="bot-send-btn" aria-label="Enviar mensaje">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </button>
            </form>
            <div class="bot-footer-caption">
                DEVIOZ OS AI &bull; ASISTENCIA TÉCNICA Y OPERATIVA
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 4. SUITE DE COMUNICACIÓN E INTERCONEXIÓN DEVIOZ (CHAT, DOCK, MEET)      -->
    <!-- ========================================================================= -->
    <?= View::partial('partials/communication-suite') ?>

    <!-- ========================================================================= -->
    <!-- 5. Devioz SIDEPANEL SLIDER (CAJÓN DE INSPECCIÓN RÁPIDA)  -->
    <!-- ========================================================================= -->
    <?= View::partial('partials/sidepanel') ?>

    <!-- ========================================================================= -->
    <!-- 6. SELECTOR DE TEMAS VISUALES Devioz                                -->
    <!-- ========================================================================= -->
    <?= View::partial('partials/theme-modal') ?>


    <!-- Bootstrap 5.3.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- SortableJS para Drag & Drop en Kanban -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

    <!-- Chart.js para Estadísticas de Dashboard -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

    <!-- Canvas Confetti para Celebraciones de Aprobación -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>

    <!-- JavaScript Vanilla de la Aplicación -->
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
