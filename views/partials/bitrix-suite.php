<?php

declare(strict_types=1);

$currentUser = auth()->user();
if (!$currentUser) return;
?>

<!-- ========================================================================= -->
<!-- BITRIX24 ENTERPRISE SUITE: DOCK FLOTANTE, CHAT DIRECTO Y VIDEOLLAMADA     -->
<!-- ========================================================================= -->

<!-- 1. DOCK LATERAL DERECHO FLOTANTE (ESTILO BITRIX24 CRM & COLLABORATION) -->
<aside class="bitrix-floating-dock" id="bitrixFloatingDock" aria-label="Centro de Contacto Bitrix24">
    <!-- Botón 1: Chat Directo 1 a 1 -->
    <button type="button" class="bitrix-dock-btn" id="bitrixDockChatBtn" title="Chat Directo con la Administración" aria-label="Abrir chat directo">
        <i class="fa-solid fa-comments"></i>
        <span class="bitrix-dock-badge d-none" id="bitrixDockChatBadge">0</span>
        <span class="bitrix-dock-tooltip">Chat con Administración</span>
    </button>

    <!-- Botón 2: Solicitar Llamada / Contacto Inmediato -->
    <button type="button" class="bitrix-dock-btn" id="bitrixDockCallBtn" title="Solicitar Llamada o Contacto Inmediato" aria-label="Solicitar contacto">
        <i class="fa-solid fa-phone"></i>
        <span class="bitrix-dock-tooltip">Pedir Contacto Urgente</span>
    </button>

    <!-- Botón 3: Iniciar Videollamada HD Bitrix Meet -->
    <button type="button" class="bitrix-dock-btn bitrix-dock-btn-video" id="bitrixDockVideoBtn" title="Videollamada Técnica Bitrix Meet" aria-label="Iniciar videollamada">
        <i class="fa-solid fa-video"></i>
        <span class="bitrix-dock-tooltip">Videollamada en Vivo</span>
    </button>
</aside>

<!-- 2. DRAWER LATERAL DESLIZABLE DE CHAT 1 A 1 (BITRIX24 CHAT DRAWER) -->
<div class="bitrix-chat-drawer-backdrop" id="bitrixChatBackdrop"></div>
<div class="bitrix-chat-drawer" id="bitrixChatDrawer" role="dialog" aria-modal="true" aria-labelledby="bitrixChatContactName">
    <!-- Cabecera del Chat Bitrix -->
    <div class="bitrix-chat-header">
        <div class="d-flex align-items-center gap-2 min-w-0">
            <div class="position-relative flex-shrink-0">
                <img src="<?= asset('images/devioz-icon.png') ?>" alt="Avatar" class="rounded-circle bitrix-chat-avatar" id="bitrixChatContactAvatar" width="40" height="40" style="object-fit: cover;">
                <span class="bitrix-chat-status-indicator" title="En línea"></span>
            </div>
            <div class="min-w-0">
                <div class="fw-bold text-dark text-truncate small mb-0" id="bitrixChatContactName">Administración TI</div>
                <div class="small text-secondary text-truncate" style="font-size: 0.72rem;" id="bitrixChatContactRole">Lead Técnico & Auditor QA</div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-1 ms-auto flex-shrink-0">
            <!-- Botón Llamada en cabecera -->
            <button type="button" class="btn btn-sm btn-ghost p-1 rounded-circle" id="bitrixChatHeaderCallBtn" title="Solicitar llamada urgente" aria-label="Solicitar llamada urgente">
                <i class="fa-solid fa-phone text-primary" style="font-size: 0.85rem;"></i>
            </button>
            <!-- Botón Video en cabecera -->
            <button type="button" class="btn btn-sm btn-ghost p-1 rounded-circle" id="bitrixChatHeaderVideoBtn" title="Videollamada Bitrix Meet" aria-label="Videollamada Bitrix Meet">
                <i class="fa-solid fa-video text-success" style="font-size: 0.85rem;"></i>
            </button>
            <!-- Cerrar Drawer -->
            <button type="button" class="btn btn-sm btn-ghost p-1 rounded-circle" id="bitrixChatCloseBtn" title="Cerrar chat" aria-label="Cerrar chat">
                <i class="fa-solid fa-xmark text-secondary" style="font-size: 1rem;"></i>
            </button>
        </div>
    </div>

    <!-- Banner informativo de proyecto activo -->
    <div class="bitrix-chat-project-banner d-none" id="bitrixChatProjectBanner">
        <span class="badge bg-primary me-1" id="bitrixChatProjectCode">DEV</span>
        <span class="small text-truncate" id="bitrixChatProjectName">Proyecto Activo</span>
    </div>

    <!-- Cuerpo de Mensajes -->
    <div class="bitrix-chat-body" id="bitrixChatMessagesBody">
        <div class="text-center py-4 text-muted small">
            <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
            <div>Cargando historial de conversación...</div>
        </div>
    </div>

    <!-- Sugerencias / Chips de Preguntas Frecuentes -->
    <div class="bitrix-chat-quick-chips">
        <button type="button" class="bitrix-chip-btn" data-chat-text="Tengo una consulta técnica sobre el hito actual.">Consulta de hito</button>
        <button type="button" class="bitrix-chip-btn" data-chat-text="Solicito revisión y dictamen de la última entrega realizada.">Revisión de entrega</button>
        <button type="button" class="bitrix-chip-btn" data-chat-text="¿Podemos coordinar una breve reunión técnica por Bitrix Meet?">Coordinar Meet</button>
    </div>

    <!-- Pie del Chat: Input y Enviar -->
    <div class="bitrix-chat-footer">
        <form class="bitrix-chat-input-form" id="bitrixChatForm">
            <textarea class="bitrix-chat-input" id="bitrixChatInput" rows="1" placeholder="Escribe un mensaje para la administración..." autocomplete="off"></textarea>
            <button type="submit" class="bitrix-chat-send-btn" id="bitrixChatSendBtn" aria-label="Enviar mensaje">
                <i class="fa-solid fa-paper-plane"></i>
            </button>
        </form>
        <div class="text-center text-muted" style="font-size: 0.65rem; margin-top: 4px;">
            DEVIOZ OS &bull; COMUNICACIÓN OFICIAL CIFRADA
        </div>
    </div>
</div>

<!-- 3. MODAL DE VIDEOLLAMADA HD BITRIX MEET (SIN SALIR DEL SISTEMA) -->
<div class="modal fade" id="bitrixVideoModal" tabindex="-1" aria-labelledby="bitrixVideoModalTitle" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-fullscreen-lg-down modal-xl modal-dialog-centered">
        <div class="modal-content bitrix-video-modal-content">
            <div class="modal-header bitrix-video-header py-2 px-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-danger p-1 px-2 d-flex align-items-center gap-1">
                        <span class="spinner-grow spinner-grow-sm" role="status" style="width: 8px; height: 8px;"></span>
                        EN VIVO
                    </span>
                    <h6 class="modal-title fw-semibold text-white mb-0" id="bitrixVideoModalTitle">
                        Sala Técnica — Devioz Bitrix Meet
                    </h6>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-light py-1 px-2" id="copyMeetingLinkBtn" title="Copiar enlace de la reunión">
                        <i class="fa-solid fa-link me-1"></i> Copiar Link
                    </button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Finalizar llamada" id="bitrixVideoCloseBtn"></button>
                </div>
            </div>
            <div class="modal-body p-0 bitrix-video-body" id="bitrixVideoContainer">
                <!-- Iframe embebido de Jitsi inyectado dinámicamente -->
            </div>
        </div>
    </div>
</div>

<!-- 4. MODAL: SOLICITAR LLAMADA / CONTACTO INMEDIATO -->
<div class="modal fade" id="bitrixRequestCallModal" tabindex="-1" aria-labelledby="bitrixRequestCallTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4">
            <div class="modal-header border-bottom py-3">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="bitrixRequestCallTitle">
                    <span class="p-2 rounded-circle" style="background: rgba(0, 172, 227, 0.12); color: #00ACE3;">
                        <i class="fa-solid fa-phone"></i>
                    </span>
                    Solicitar Contacto Inmediato con Administración
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="bitrixRequestCallForm">
                <div class="modal-body p-4">
                    <p class="text-secondary small mb-3">
                        Esta acción enviará una notificación con <strong>prioridad urgente</strong> al lead técnico y abrirá una sala privada de videoconferencia Bitrix Meet para atención directa.
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

