<?php

declare(strict_types=1);

/**
 * Devioz OS — Modal Selector de Temas Visuales
 * Selector de temas y fondos personalizables de Devioz OS
 */
?>
<div class="modal fade" id="deviozThemeModal" tabindex="-1" aria-labelledby="deviozThemeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered devioz-theme-modal-dialog">
        <div class="modal-content devioz-theme-modal-content">

            <!-- Cabecera del Modal Estilo Devioz -->
            <div class="devioz-theme-modal-header">
                <h5 class="devioz-theme-modal-title" id="deviozThemeModalLabel">Temas visuales</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <!-- Galería de Temas Visuales en Cuadrícula 4 Columnas -->
            <div class="devioz-theme-grid" id="deviozThemesGrid">

                <!-- Fila 1 -->
                <!-- 1. Amanecer en el Prado (Meadow Tree) -->
                <div class="devioz-theme-card"
                     data-theme-key="meadow"
                     title="Amanecer en el Prado"
                     style="background-image: url('<?= asset('images/wallpapers/meadow.webp') ?>');">
                    <span class="devioz-theme-check-icon"><i class="fa-solid fa-check"></i></span>
                </div>

                <!-- 2. Saturno Video (Fluid Purple) -->
                <div class="devioz-theme-card"
                     data-theme-key="saturn"
                     title="Saturno (video)"
                     style="background-image: url('<?= asset('images/wallpapers/saturn.webp') ?>');">
                    <div class="devioz-theme-play-btn"><i class="fa-solid fa-play"></i></div>
                    <span class="devioz-theme-video-title">Saturno (video)</span>
                    <span class="devioz-theme-check-icon"><i class="fa-solid fa-check"></i></span>
                </div>

                <!-- 3. Bosque Halloween -->
                <div class="devioz-theme-card"
                     data-theme-key="halloween"
                     title="Bosque Encantado Halloween"
                     style="background-image: url('<?= asset('images/wallpapers/halloween.webp') ?>');">
                    <span class="devioz-theme-check-icon"><i class="fa-solid fa-check"></i></span>
                </div>

                <!-- 4. Bosque Nevado -->
                <div class="devioz-theme-card"
                     data-theme-key="winter"
                     title="Bosque Nevado Invernal"
                     style="background-image: url('<?= asset('images/wallpapers/winter.webp') ?>');">
                    <span class="devioz-theme-check-icon"><i class="fa-solid fa-check"></i></span>
                </div>

                <!-- Fila 2 -->
                <!-- 5. Flor Cósmica Cristal (Por defecto) -->
                <div class="devioz-theme-card active"
                     data-theme-key="vibecode"
                     title="Flor Cósmica de Cristal (Por defecto)"
                     style="background: radial-gradient(at 85% 15%, #6366f1 0px, transparent 55%), radial-gradient(at 15% 85%, #10B981 0px, transparent 55%), linear-gradient(135deg, #090e24 0%, #151b45 50%, #201a4e 100%);">
                    <span class="devioz-theme-badge-default">Por defecto</span>
                    <span class="devioz-theme-check-icon"><i class="fa-solid fa-check"></i></span>
                </div>

                <!-- 6. Nebulosa Púrpura -->
                <div class="devioz-theme-card"
                     data-theme-key="nebula"
                     title="Nebulosa Púrpura"
                     style="background-image: url('<?= asset('images/wallpapers/nebula.webp') ?>');">
                    <span class="devioz-theme-check-icon"><i class="fa-solid fa-check"></i></span>
                </div>

                <!-- 7. Medusa de Cristal Video -->
                <div class="devioz-theme-card"
                     data-theme-key="jellyfish"
                     title="Medusa de Cristal (video)"
                     style="background-image: url('<?= asset('images/wallpapers/jellyfish.webp') ?>');">
                    <div class="devioz-theme-play-btn"><i class="fa-solid fa-play"></i></div>
                    <span class="devioz-theme-check-icon"><i class="fa-solid fa-check"></i></span>
                </div>

                <!-- 8. Tierra desde el Espacio -->
                <div class="devioz-theme-card"
                     data-theme-key="earth"
                     title="Tierra desde el Espacio"
                     style="background-image: url('<?= asset('images/wallpapers/earth.webp') ?>');">
                    <span class="devioz-theme-check-icon"><i class="fa-solid fa-check"></i></span>
                </div>

                <!-- Fila 3 -->
                <!-- 9. Lago Primaveral Sakura -->
                <div class="devioz-theme-card"
                     data-theme-key="spring"
                     title="Lago Primaveral Sakura"
                     style="background-image: url('<?= asset('images/wallpapers/spring.webp') ?>');">
                    <span class="devioz-theme-check-icon"><i class="fa-solid fa-check"></i></span>
                </div>

                <!-- 10. Rocío en el Césped -->
                <div class="devioz-theme-card"
                     data-theme-key="meadow-dew"
                     title="Rocío en el Césped"
                     style="background-image: url('<?= asset('images/wallpapers/meadow-dew.webp') ?>');">
                    <span class="devioz-theme-check-icon"><i class="fa-solid fa-check"></i></span>
                </div>

                <!-- 11. Seda Azul Marina Video -->
                <div class="devioz-theme-card"
                     data-theme-key="ocean-silk"
                     title="Seda Azul Marina (video)"
                     style="background-image: url('<?= asset('images/wallpapers/ocean-silk.webp') ?>');">
                    <div class="devioz-theme-play-btn"><i class="fa-solid fa-play"></i></div>
                    <span class="devioz-theme-check-icon"><i class="fa-solid fa-check"></i></span>
                </div>

                <!-- 12. Onda Azul -->
                <div class="devioz-theme-card"
                     data-theme-key="devioz-blue"
                     title="Onda Azul"
                     style="background-image: url('<?= asset('images/wallpapers/devioz-blue.webp') ?>');">
                    <span class="devioz-theme-check-icon"><i class="fa-solid fa-check"></i></span>
                </div>

                <!-- Fila 4 -->
                <!-- 13. Lago Alpino en Otoño -->
                <div class="devioz-theme-card"
                     data-theme-key="autumn"
                     title="Lago Alpino en Otoño"
                     style="background-image: url('<?= asset('images/devioz-bg-hd.webp') ?>');">
                    <span class="devioz-theme-check-icon"><i class="fa-solid fa-check"></i></span>
                </div>

                <!-- 14. Cyber Dark Espacial -->
                <div class="devioz-theme-card"
                     data-theme-key="cyber"
                     title="Cyber Dark Espacial"
                     style="background: radial-gradient(at 50% 0%, #1e3a8a 0px, transparent 70%), linear-gradient(180deg, #070a1e 0%, #03040c 100%);">
                    <span class="devioz-theme-check-icon"><i class="fa-solid fa-check"></i></span>
                </div>

                <!-- 15. Aurora Boreal -->
                <div class="devioz-theme-card"
                     data-theme-key="aurora"
                     title="Aurora Boreal Esmeralda"
                     style="background-image: url('<?= asset('images/wallpapers/aurora.webp') ?>');">
                    <span class="devioz-theme-check-icon"><i class="fa-solid fa-check"></i></span>
                </div>

                <!-- 16. Minimal Light Slate -->
                <div class="devioz-theme-card"
                     data-theme-key="light"
                     title="Minimal Light Gris Perla"
                     style="background: linear-gradient(135deg, #E2E8F0 0%, #EDF2F7 50%, #CBD5E1 100%);">
                    <span class="devioz-theme-check-icon"><i class="fa-solid fa-check"></i></span>
                </div>

            </div>

            <!-- Pie del Modal Oficial Devioz -->
            <div class="devioz-theme-modal-footer">
                <!-- Checkbox Predeterminado para todos -->
                <div class="form-check d-flex align-items-center gap-2 m-0">
                    <input class="form-check-input" type="checkbox" id="defaultThemeAllUsers" style="cursor: pointer; width: 16px; height: 16px;">
                    <label class="form-check-label user-select-none d-flex align-items-center gap-1.5" for="defaultThemeAllUsers" style="font-size: 0.82rem; color: #525C69; cursor: pointer;">
                        <span>Tema predeterminado para todos los usuarios</span>
                        <i class="fa-solid fa-lock text-info" style="font-size: 0.70rem;" title="Requiere permisos administrativos"></i>
                    </label>
                </div>

                <!-- Botones de Acción -->
                <div class="d-flex align-items-center gap-3 ms-auto flex-wrap">
                    <button type="button" class="btn btn-devioz-save" id="deviozSaveThemeBtn">
                        GUARDAR
                    </button>
                    <button type="button" class="btn btn-devioz-modal-link" data-bs-dismiss="modal">
                        CANCELAR
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
