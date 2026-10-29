<?php

declare(strict_types=1);

$currentUser = auth()->user();
if (!$currentUser) return;
?>

<!-- ========================================================================= -->
<!-- DEVIOZ ENTERPRISE SUITE: DOCK FLOTANTE, CHAT DIRECTO Y VIDEOLLAMADA     -->
<!-- ========================================================================= -->

<!-- 1. DOCK LATERAL DERECHO FLOTANTE (ESTILO DEVIOZ CRM & COLLABORATION) -->
<aside class="devioz-floating-dock" id="deviozFloatingDock" aria-label="Centro de Contacto Devioz">
    <!-- Botón 1: Chat Directo 1 a 1 -->
    <button type="button" class="devioz-dock-btn" id="deviozDockChatBtn" title="Chat Directo con la Administración" aria-label="Abrir chat directo">
        <i class="fa-solid fa-comments"></i>
        <span class="devioz-dock-badge d-none" id="deviozDockChatBadge">0</span>
        <span class="devioz-dock-tooltip">Chat con Administración</span>
    </button>

    <!-- Botón 2: Solicitar Llamada / Contacto Inmediato -->
    <button type="button" class="devioz-dock-btn" id="deviozDockCallBtn" title="Solicitar Llamada o Contacto Inmediato" aria-label="Solicitar contacto">
        <i class="fa-solid fa-phone"></i>
        <span class="devioz-dock-tooltip">Pedir Contacto Urgente</span>
    </button>

    <!-- Botón 3: Iniciar Videollamada HD Devioz Meet -->
    <button type="button" class="devioz-dock-btn devioz-dock-btn-video" id="deviozDockVideoBtn" title="Videollamada Técnica Devioz Meet" aria-label="Iniciar videollamada">
        <i class="fa-solid fa-video"></i>
        <span class="devioz-dock-tooltip">Videollamada en Vivo</span>
    </button>
</aside>

<!-- 2. DRAWER LATERAL DESLIZABLE DE CHAT 1 A 1 (DEVIOZ CHAT DRAWER) -->
<div class="devioz-chat-drawer-backdrop" id="deviozChatBackdrop"></div>
<div class="devioz-chat-drawer" id="deviozChatDrawer" role="dialog" aria-modal="true" aria-labelledby="deviozChatContactName">
    <!-- Cabecera del Chat Devioz -->
    <div class="devioz-chat-header">
        <div class="d-flex align-items-center gap-2 min-w-0">
            <div class="position-relative flex-shrink-0">
                <img src="<?= asset('images/devioz-icon.png') ?>" alt="Avatar" class="rounded-circle devioz-chat-avatar" id="deviozChatContactAvatar" width="40" height="40" style="object-fit: cover;">
                <span class="devioz-chat-status-indicator" title="En línea"></span>
            </div>
            <div class="min-w-0">
                <div class="fw-bold text-dark text-truncate small mb-0" id="deviozChatContactName">Administración TI</div>
                <div class="small text-secondary text-truncate" style="font-size: 0.72rem;" id="deviozChatContactRole">Lead Técnico & Auditor QA</div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-1 ms-auto flex-shrink-0">
            <!-- Botón Llamada en cabecera -->
            <button type="button" class="btn btn-sm btn-ghost p-1 rounded-circle" id="deviozChatHeaderCallBtn" title="Solicitar llamada urgente" aria-label="Solicitar llamada urgente">
                <i class="fa-solid fa-phone text-primary" style="font-size: 0.85rem;"></i>
            </button>
            <!-- Botón Video en cabecera -->
            <button type="button" class="btn btn-sm btn-ghost p-1 rounded-circle" id="deviozChatHeaderVideoBtn" title="Videollamada Devioz Meet" aria-label="Videollamada Devioz Meet">
                <i class="fa-solid fa-video text-success" style="font-size: 0.85rem;"></i>
            </button>
            <!-- Cerrar Drawer -->
            <button type="button" class="btn btn-sm btn-ghost p-1 rounded-circle" id="deviozChatCloseBtn" title="Cerrar chat" aria-label="Cerrar chat">
                <i class="fa-solid fa-xmark text-secondary" style="font-size: 1rem;"></i>
            </button>
        </div>
    </div>

    <!-- Banner informativo de proyecto activo -->
    <div class="devioz-chat-project-banner d-none" id="deviozChatProjectBanner">
        <span class="badge bg-primary me-1" id="deviozChatProjectCode">DEV</span>
        <span class="small text-truncate" id="deviozChatProjectName">Proyecto Activo</span>
    </div>

    <!-- Cuerpo de Mensajes -->
    <div class="devioz-chat-body" id="deviozChatMessagesBody">
        <div class="text-center py-4 text-muted small">
            <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
            <div>Cargando historial de conversación...</div>
        </div>
    </div>

    <!-- Sugerencias / Chips de Preguntas Frecuentes -->
    <div class="devioz-chat-quick-chips">
        <button type="button" class="devioz-chip-btn" data-chat-text="Tengo una consulta técnica sobre el hito actual.">Consulta de hito</button>
        <button type="button" class="devioz-chip-btn" data-chat-text="Solicito revisión y dictamen de la última entrega realizada.">Revisión de entrega</button>
        <button type="button" class="devioz-chip-btn" data-chat-text="¿Podemos coordinar una breve reunión técnica por Devioz Meet?">Coordinar Meet</button>
    </div>

    <!-- Pie del Chat: Input y Enviar -->
    <div class="devioz-chat-footer">
        <form class="devioz-chat-input-form" id="deviozChatForm">
            <textarea class="devioz-chat-input" id="deviozChatInput" rows="1" placeholder="Escribe un mensaje para la administración..." autocomplete="off"></textarea>
            <button type="submit" class="devioz-chat-send-btn" id="deviozChatSendBtn" aria-label="Enviar mensaje">
                <i class="fa-solid fa-paper-plane"></i>
            </button>
        </form>
        <div class="text-center text-muted" style="font-size: 0.65rem; margin-top: 4px;">
            DEVIOZ 24 &bull; COMUNICACIÓN OFICIAL CIFRADA
        </div>
    </div>
</div>

<!-- 3. MODAL DE VIDEOLLAMADA HD DEVIOZ MEET (SIN SALIR DEL SISTEMA) -->
<div class="modal fade" id="deviozVideoModal" tabindex="-1" aria-labelledby="deviozVideoModalTitle" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-fullscreen-lg-down modal-xl modal-dialog-centered">
        <div class="modal-content devioz-video-modal-content">
            <div class="modal-header devioz-video-header py-2 px-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-danger p-1 px-2 d-flex align-items-center gap-1">
                        <span class="spinner-grow spinner-grow-sm" role="status" style="width: 8px; height: 8px;"></span>
                        EN VIVO
                    </span>
                    <h6 class="modal-title fw-semibold text-white mb-0" id="deviozVideoModalTitle">
                        Sala técnica de videollamada
                    </h6>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-light py-1 px-2" id="copyMeetingLinkBtn" title="Copiar enlace de la reunión">
                        <i class="fa-solid fa-link me-1"></i> Copiar Link
                    </button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Finalizar llamada" id="deviozVideoCloseBtn"></button>
                </div>
            </div>
            <div class="modal-body p-0 devioz-video-body" id="deviozVideoContainer">
                <!-- Iframe embebido de Jitsi inyectado dinámicamente -->
            </div>
        </div>
    </div>
</div>

<!-- 4. MODAL: SOLICITAR LLAMADA / CONTACTO INMEDIATO -->
<div class="modal fade" id="deviozRequestCallModal" tabindex="-1" aria-labelledby="deviozRequestCallTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4">
            <div class="modal-header border-bottom py-3">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="deviozRequestCallTitle">
                    <span class="p-2 rounded-circle" style="background: rgba(16, 185, 129, 0.12); color: #10B981;">
                        <i class="fa-solid fa-phone"></i>
                    </span>
                    Solicitar Contacto Inmediato con Administración
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="deviozRequestCallForm">
                <div class="modal-body p-4">
                    <p class="text-secondary small mb-3">
                        Esta acción enviará una notificación con <strong>prioridad urgente</strong> al lead técnico y abrirá una sala privada de videoconferencia Devioz Meet para atención directa.
                    </p>
                    <div class="mb-3">
                        <label for="callRequestReason" class="form-label-custom">Motivo de la Solicitud / Consulta:</label>
                        <select class="form-select form-select-custom mb-2" id="callRequestQuickSelect">
                            <option value="Revisión técnica y sustentación de arquitectura de hitos">Revisión de arquitectura y sustentación de hitos</option>
                            <option value="Duda bloqueante con entorno o despliegue en servidor">Duda bloqueante con entorno de desarrollo o despliegue</option>
                            <option value="Consulta sobre dictamen de auditoría QA">Consulta sobre dictamen de auditoría QA</option>
                            <option value="Otro motivo personalizado">Otro motivo personalizado...</option>
                        </select>
                        <textarea class="form-control form-control-custom w-100" id="callRequestReason" rows="2" placeholder="Describe brevemente el bloqueo técnico o requerimiento..."></textarea>
                    </div>
                    <div id="callRequestFeedback" class="small" role="status" aria-live="polite"></div>
                </div>
                <div class="modal-footer border-top py-2 px-4">
                    <button type="button" class="btn btn-ghost btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-brand btn-sm d-flex align-items-center gap-2" id="submitCallRequestBtn">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Enviar Solicitud y Crear Sala</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

