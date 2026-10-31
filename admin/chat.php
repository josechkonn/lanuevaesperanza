<?php
require __DIR__ . '/../includes/app.php';
requiere_login();

$titulo = 'Atención en Vivo · Chat Comunal';
$seccion = 'chat';
$u = usuario_actual();

require __DIR__ . '/layout/header.php';
?>

<div class="card chat-main-wrapper border shadow-sm overflow-hidden" style="height: calc(100vh - 130px); min-height: 620px; border-color: #e2e8f0; border-radius: 16px;">
    <div class="row g-0 h-100 flex-nowrap">
        
        <!-- PANEL IZQUIERDO: LISTA DE CONVERSACIONES -->
        <div class="col-12 col-md-5 col-lg-4 border-end d-flex flex-column h-100 bg-white" style="min-width: 320px; max-width: 400px; flex-shrink: 0;">
            
            <!-- Barra superior del panel izquierdo -->
            <div class="pt-4 pb-3.5 px-4 border-bottom bg-white flex-shrink-0">
                <div class="d-flex align-items-center justify-content-between mb-3 mt-1">
                    <h2 class="h6 fw-bold mb-0 text-slate-800 d-flex align-items-center gap-2" style="font-size: 1.05rem; letter-spacing: -0.01em;">
                        <i class="bi bi-chat-left-dots-fill text-emerald-600"></i> Conversaciones
                    </h2>
                    <span id="badgeEsperandoHumano" class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill d-none animate__animated animate__pulse animate__infinite" style="font-size: 0.72rem; padding: 0.35rem 0.65rem;">
                        0 esperando
                    </span>
                </div>

                <!-- Buscador de sesiones -->
                <div class="input-group input-group-sm mb-3 rounded-pill overflow-hidden border" style="border-color: #e2e8f0;">
                    <span class="input-group-text bg-slate-50 border-0 text-slate-400 ps-3 pe-2"><i class="bi bi-search"></i></span>
                    <input type="text" id="sessionSearchInput" oninput="filtrarSesionesLista()" class="form-control bg-slate-50 border-0 shadow-none ps-1 py-2 text-sm" placeholder="Buscar visitante o mensaje...">
                </div>

                <!-- Filtros rápidos segmentados -->
                <div class="chat-filter-pills d-flex p-1 rounded-3 gap-1">
                    <button type="button" onclick="cambiarFiltroSesiones('todos', this)" class="btn btn-sm btn-filter flex-fill active" data-filter="todos">Todas</button>
                    <button type="button" onclick="cambiarFiltroSesiones('humano', this)" class="btn btn-sm btn-filter flex-fill" data-filter="humano">
                        <i class="bi bi-person-fill me-1 text-danger"></i> Esperando
                    </button>
                    <button type="button" onclick="cambiarFiltroSesiones('ia', this)" class="btn btn-sm btn-filter flex-fill" data-filter="ia">
                        <i class="bi bi-robot me-1 text-secondary"></i> IA
                    </button>
                </div>
            </div>

            <!-- Lista scrolleable de chats -->
            <div id="sessionsListContainer" class="flex-grow-1 overflow-y-auto" style="flex: 1 1 0; min-height: 0; overflow-x: hidden;">
                <div class="text-center py-5 text-secondary">
                    <div class="spinner-border spinner-border-sm text-emerald-600 mb-2"></div>
                    <div class="small text-slate-500">Cargando conversaciones...</div>
                </div>
            </div>
            
            <!-- Pie de estadísticas rápidas -->
            <div class="p-2.5 bg-slate-50 border-top text-secondary small d-flex justify-content-between align-items-center px-3 flex-shrink-0">
                <span id="lblTotalSesiones" class="text-slate-500" style="font-size: 0.75rem;">0 chats totales</span>
                <span class="text-emerald-700 fw-semibold d-inline-flex align-items-center gap-1.5" style="font-size: 0.75rem;">
                    <span class="status-pulse-dot"></span> Sincronizado en vivo
                </span>
            </div>
        </div>

        <!-- PANEL DERECHO: VISTA DE MENSAJES Y CHAT ACTIVO -->
        <div class="col d-flex flex-column h-100 bg-slate-50 position-relative overflow-hidden" style="flex: 1 1 0; min-width: 0;">
            
            <!-- Estado vacío cuando no hay chat seleccionado -->
            <div id="noChatSelectedView" class="h-100 d-flex flex-column align-items-center justify-content-center text-center p-4">
                <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 shadow-xs" style="width: 80px; height: 80px; background: #ecfdf5; border: 1px solid #a7f3d0;">
                    <i class="bi bi-chat-heart text-emerald-600" style="font-size: 2.25rem;"></i>
                </div>
                <h3 class="h5 fw-bold text-slate-800 mb-1">Mesa de Atención en Vivo</h3>
                <p class="text-slate-500 small mx-auto mb-3" style="max-width: 400px; line-height: 1.5;">
                    Seleccione una conversación en la lista lateral para responder en tiempo real o supervisar las respuestas automáticas de la IA.
                </p>
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill bg-white border border-slate-200 text-slate-600 small shadow-2xs">
                    <span class="p-1 bg-emerald-500 rounded-circle"></span>
                    <span class="fw-semibold" style="font-size: 0.75rem;">Canal seguro sincronizado en tiempo real</span>
                </div>
            </div>

            <!-- Contenedor del chat seleccionado (Oculto inicialmente) -->
            <div id="activeChatView" class="d-none h-100 d-flex flex-column overflow-hidden" style="min-height: 0;">
                
                <!-- Header del chat activo -->
                <div class="chat-header px-4 py-2.5 bg-white border-bottom d-flex align-items-center justify-content-between flex-shrink-0" style="min-height: 68px;">
                    <div class="d-flex align-items-center">
                        <div class="position-relative me-3">
                            <div id="activeChatAvatar" class="avatar-circle-header fw-bold">V</div>
                            <span id="activeChatOnlineDot" class="position-absolute bottom-0 end-0 p-1 bg-success border border-2 border-white rounded-circle"></span>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h3 id="activeChatName" class="h6 fw-bold mb-0 text-slate-800">Visitante #0000</h3>
                                <span id="activeChatStateBadge" class="badge bg-secondary-subtle text-secondary rounded-pill small" style="font-size: 0.68rem;">IA</span>
                            </div>
                            <div id="activeChatSubtitle" class="text-slate-400" style="font-size: 0.72rem;">IP: ... · Conectado</div>
                        </div>
                    </div>

                    <!-- Botones de Acción: Finalizar con IA o Tomar Control -->
                    <div class="d-flex align-items-center gap-2">
                        <button id="btnFinishHandoff" type="button" onclick="finalizarAtencionIA()" class="btn-handoff d-flex align-items-center gap-1.5">
                            <i class="bi bi-robot"></i>
                            <span class="d-none d-sm-inline">Devolver a la IA</span>
                        </button>
                        <button id="btnTakeControl" type="button" onclick="tomarControlHumano()" class="btn-take-control d-flex align-items-center gap-1.5">
                            <i class="bi bi-person-fill"></i>
                            <span class="d-none d-sm-inline">Tomar Control</span>
                        </button>
                    </div>
                </div>

                <!-- Feed de Mensajes -->
                <div id="chatMessagesFeed" class="chat-feed flex-grow-1 p-3 p-md-4 overflow-y-auto" style="flex: 1 1 0; min-height: 0; background-color: #f8fafc; background-image: radial-gradient(#cbd5e1 0.75px, transparent 0.75px); background-size: 20px 20px;">
                    <!-- Mensajes inyectados dinámicamente -->
                </div>

                <!-- Respuestas Rápidas Predefinidas -->
                <div class="chat-quick-replies px-3 py-2 bg-white border-top border-bottom d-flex align-items-center gap-2 overflow-x-auto flex-shrink-0" style="scrollbar-width: thin;">
                    <span class="text-slate-400 small fw-semibold text-nowrap d-flex align-items-center gap-1" style="font-size: 0.72rem;">
                        <i class="bi bi-lightning-charge-fill text-amber-500"></i> Respuestas rápidas:
                    </span>
                    <button type="button" onclick="insertarPlantilla('¡Hola! Un saludo cordial. ¿En qué podemos colaborarle hoy desde la EPSDC?')" class="quick-reply-chip">👋 Saludo</button>
                    <button type="button" onclick="insertarPlantilla('¿Perteneces a alguna UPF o Consejo Comunal en específico? Indícanos tu aldea o municipio.')" class="quick-reply-chip">🌱 Aldea / UPF</button>
                    <button type="button" onclick="insertarPlantilla('Las rutas de entrega de combos de esta semana están activas en el cronograma. ¿Deseas apartar cupo en algún punto de acopio?')" class="quick-reply-chip">📦 Entregas</button>
                    <button type="button" onclick="insertarPlantilla('Hemos registrado tu solicitud en el sistema de la EPSDC. El comité se comunicará contigo a la brevedad.')" class="quick-reply-chip">✅ Registro</button>
                </div>

                <!-- Barra de Entrada de Mensajes -->
                <div class="chat-input-bar p-3 bg-white border-top flex-shrink-0">
                    <form id="adminSendMessageForm" onsubmit="enviarMensajeAdmin(event)" class="d-flex align-items-center gap-2">
                        <input type="text" id="adminMessageInput" class="form-control rounded-pill px-3 py-2 text-sm chat-input" placeholder="Escribe un mensaje como operador comunal... (Enter para enviar)" autocomplete="off">
                        <button type="submit" class="btn-emerald-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 42px; height: 42px;" title="Enviar">
                            <i class="bi bi-send-fill fs-6"></i>
                        </button>
                    </form>
                </div>

            </div>

        </div>

    </div>
</div>

<style>
/* ==========================================================================
   Estilos Chat Comunal Administrativo
   ========================================================================== */
.chat-main-wrapper {
    background: #ffffff;
}

.text-slate-800 { color: #1e293b; }
.text-slate-600 { color: #475569; }
.text-slate-500 { color: #64748b; }
.text-slate-400 { color: #94a3b8; }
.text-emerald-600 { color: #059669; }
.text-emerald-700 { color: #047857; }
.bg-slate-50 { background-color: #f8fafc; }

.status-pulse-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #10b981;
    display: inline-block;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
    animation: statusPulse 2s infinite;
}

@keyframes statusPulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6); }
    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

.chat-filter-pills {
    background: #f1f5f9;
    border-radius: 10px;
    padding: 3px;
}
.chat-filter-pills .btn-filter {
    border: none;
    background: transparent;
    color: #64748b;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: 8px;
    padding: 0.35rem 0.5rem;
    transition: all 0.15s ease;
}
.chat-filter-pills .btn-filter:hover {
    color: #0f172a;
}
.chat-filter-pills .btn-filter.active {
    background: #ffffff;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}
.chat-filter-pills .btn-filter.active[data-filter="todos"] {
    color: #059669;
}
.chat-filter-pills .btn-filter.active[data-filter="humano"] {
    color: #e11d48;
}
.chat-filter-pills .btn-filter.active[data-filter="ia"] {
    color: #475569;
}

/* Items de conversación en la lista */
.session-item {
    transition: background-color 0.15s ease, border-color 0.15s ease;
    cursor: pointer;
    border-bottom: 1px solid #f1f5f9;
    padding: 12px 16px;
}
.session-item:hover {
    background-color: #f8fafc;
}
.session-item.active {
    background-color: #f0fdf4 !important;
    border-left: 3.5px solid #059669;
}

.avatar-session-list {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    color: #ffffff;
    font-weight: 700;
    font-size: 0.82rem;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 4px rgba(5, 150, 105, 0.15);
}

.avatar-circle-header {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    box-shadow: 0 2px 4px rgba(5, 150, 105, 0.15);
}

/* Burbujas de mensajes */
.msg-bubble-visitor {
    background-color: #ffffff;
    color: #0f172a;
    border: 1px solid #e2e8f0;
    border-radius: 16px 16px 16px 4px;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    max-width: 80%;
    padding: 0.75rem 1rem;
    position: relative;
}

.msg-bubble-admin {
    background-color: #ecfdf5;
    color: #064e3b;
    border: 1px solid #a7f3d0;
    border-radius: 16px 16px 4px 16px;
    box-shadow: 0 1px 3px rgba(5, 150, 105, 0.06);
    max-width: 80%;
    padding: 0.75rem 1rem;
    position: relative;
}

.msg-bubble-ia {
    background-color: #f8fafc;
    color: #1e293b;
    border: 1px solid #cbd5e1;
    border-radius: 16px 16px 4px 16px;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    max-width: 82%;
    padding: 0.75rem 1rem;
    position: relative;
}

.msg-bubble-system {
    background-color: #fef3c7;
    color: #92400e;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: 20px;
    padding: 6px 16px;
    max-width: 85%;
    margin: 10px auto;
    text-align: center;
    border: 1px solid #fde68a;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
}

.chat-msg-content {
    word-break: break-word;
    font-size: 0.84rem;
    line-height: 1.5;
}

.chat-bullet-item {
    display: flex;
    align-items: flex-start;
    gap: 0.4rem;
    margin: 0.25rem 0;
}
.chat-bullet-dot {
    color: #059669;
    font-weight: 700;
    line-height: 1.4;
    user-select: none;
}
.chat-bullet-text {
    flex: 1;
}
.chat-space-sm {
    height: 0.35rem;
}

/* Respuestas rápidas */
.quick-reply-chip {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #334155;
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.35rem 0.75rem;
    border-radius: 9999px;
    white-space: nowrap;
    transition: all 0.15s ease;
    cursor: pointer;
}
.quick-reply-chip:hover {
    background: #ecfdf5;
    border-color: #10b981;
    color: #047857;
    transform: translateY(-1px);
}

/* Botones de acción del header */
.btn-take-control {
    background: #059669;
    color: #ffffff;
    border: 1px solid #047857;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.8rem;
    padding: 0.4rem 0.85rem;
    transition: all 0.15s ease;
}
.btn-take-control:hover {
    background: #047857;
    color: #ffffff;
}

.btn-handoff {
    background: #f8fafc;
    color: #475569;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.8rem;
    padding: 0.4rem 0.85rem;
    transition: all 0.15s ease;
}
.btn-handoff:hover {
    background: #f1f5f9;
    color: #1e293b;
    border-color: #94a3b8;
}

.btn-emerald-primary {
    background: #059669;
    border: 1px solid #047857;
    color: #ffffff;
    transition: all 0.2s ease;
}
.btn-emerald-primary:hover {
    background: #047857;
    color: #ffffff;
    transform: scale(1.04);
}

.chat-input {
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #1e293b;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}
.chat-input:focus {
    background-color: #ffffff;
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
}

/* Scrollbars finos */
#sessionsListContainer::-webkit-scrollbar,
#chatMessagesFeed::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
#sessionsListContainer::-webkit-scrollbar-track,
#chatMessagesFeed::-webkit-scrollbar-track {
    background: transparent;
}
#sessionsListContainer::-webkit-scrollbar-thumb,
#chatMessagesFeed::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
#sessionsListContainer::-webkit-scrollbar-thumb:hover,
#chatMessagesFeed::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>

<script>
let currentFilter = 'todos';
let activeSessionId = null;
let allSessions = [];
let pollingInterval = null;

document.addEventListener('DOMContentLoaded', () => {
    cargarSesiones();
    // Polling cada 3 segundos para sincronización en vivo
    pollingInterval = setInterval(() => {
        cargarSesiones(false);
        if (activeSessionId) {
            cargarMensajesActivo(activeSessionId, false);
        }
    }, 3000);
});

async function cargarSesiones(mostrarSpinner = true) {
    try {
        const res = await fetch(`<?= url("api/chat.php") ?>?action=admin_get_sessions&filtro=${currentFilter}`);
        const data = await res.json();
        if (!data.ok) return;

        allSessions = data.sesiones || [];
        
        // Actualizar contadores
        if (data.totales) {
            document.getElementById('lblTotalSesiones').innerText = `${data.totales.total || 0} chats totales`;
            const badgeH = document.getElementById('badgeEsperandoHumano');
            const espH = parseInt(data.totales.esperando_humano || 0);
            if (espH > 0) {
                badgeH.innerText = `${espH} esperando`;
                badgeH.classList.remove('d-none');
            } else {
                badgeH.classList.add('d-none');
            }
        }

        renderListaSesiones();
    } catch (e) {
        console.error('Error al cargar sesiones:', e);
    }
}

function renderListaSesiones() {
    const container = document.getElementById('sessionsListContainer');
    const query = (document.getElementById('sessionSearchInput')?.value || '').toLowerCase().trim();

    const filtered = allSessions.filter(s => {
        if (!query) return true;
        const nombre = (s.nombre_visitante || '').toLowerCase();
        const ultimo = (s.ultimo_mensaje || '').toLowerCase();
        return nombre.includes(query) || ultimo.includes(query);
    });

    if (filtered.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5 text-secondary p-4">
                <i class="bi bi-chat-square-dots fs-3 d-block mb-2 text-slate-400"></i>
                <div class="small fw-semibold text-slate-700">No se encontraron conversaciones</div>
                <div class="text-slate-400" style="font-size: 0.72rem;">Los mensajes del chat público aparecerán aquí.</div>
            </div>
        `;
        return;
    }

    let html = '';
    filtered.forEach(s => {
        const isActive = (s.id == activeSessionId) ? 'active' : '';
        const initial = (s.nombre_visitante ? s.nombre_visitante.replace('Visitante #', 'V') : 'V').substring(0, 2);
        
        // Determinar badge de estado y gradiente del avatar
        let badgeEstado = '';
        let avatarBg = 'linear-gradient(135deg, #059669 0%, #047857 100%)';
        if (s.estado === 'esperando_humano') {
            avatarBg = 'linear-gradient(135deg, #e11d48 0%, #be123c 100%)';
            badgeEstado = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5 animate__animated animate__pulse animate__infinite" style="font-size:0.65rem;">¡Requiere Humano!</span>';
        } else if (s.estado === 'humano_activo') {
            badgeEstado = '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5" style="font-size:0.65rem;">En Vivo</span>';
        } else {
            badgeEstado = '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-0.5" style="font-size:0.65rem;">IA</span>';
        }

        // Badge no leídos
        const unread = parseInt(s.no_leidos_admin || 0);
        const unreadBadge = unread > 0 ? `<span class="badge bg-danger rounded-pill ms-auto">${unread}</span>` : '';

        // Formato de hora
        const hora = s.actualizado_en ? s.actualizado_en.substring(11, 16) : '';

        html += `
            <div onclick="seleccionarChat(${s.id})" class="session-item d-flex align-items-center ${isActive}" data-id="${s.id}">
                <div class="avatar-session-list flex-shrink-0 me-3" style="background: ${avatarBg};">
                    ${initial}
                </div>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="d-flex justify-content-between align-items-center mb-0.5">
                        <span class="fw-bold text-slate-800 text-truncate text-sm" style="max-width: 140px;">${escapeHtml(s.nombre_visitante || 'Visitante')}</span>
                        <span class="text-slate-400" style="font-size: 0.7rem;">${hora}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="text-slate-500 text-truncate small me-2" style="font-size: 0.75rem; max-width: 155px;">
                            ${escapeHtml(s.ultimo_mensaje || 'Conversación iniciada')}
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            ${badgeEstado}
                            ${unreadBadge}
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

function filtrarSesionesLista() {
    renderListaSesiones();
}

function cambiarFiltroSesiones(filtro, btn) {
    currentFilter = filtro;
    document.querySelectorAll('.btn-filter').forEach(b => {
        b.classList.remove('active');
    });
    btn.classList.add('active');
    cargarSesiones(true);
}

async function seleccionarChat(sesionId) {
    activeSessionId = sesionId;
    renderListaSesiones();

    document.getElementById('noChatSelectedView').classList.add('d-none');
    document.getElementById('activeChatView').classList.remove('d-none');

    await cargarMensajesActivo(sesionId, true);
    setTimeout(() => {
        document.getElementById('adminMessageInput')?.focus();
    }, 100);
}

async function cargarMensajesActivo(sesionId, scrollBottom = false) {
    try {
        const res = await fetch(`<?= url("api/chat.php") ?>?action=admin_get_messages&sesion_id=${sesionId}`);
        const data = await res.json();
        if (!data.ok || !data.sesion) return;

        const s = data.sesion;
        const msgs = data.mensajes || [];

        // Actualizar Header
        document.getElementById('activeChatName').innerText = s.nombre_visitante || `Visitante #${s.id}`;
        document.getElementById('activeChatAvatar').innerText = (s.nombre_visitante || 'V').substring(0, 2);
        document.getElementById('activeChatSubtitle').innerText = `IP: ${s.ip || 'Local'} · Sesión #${s.id}`;

        const stateBadge = document.getElementById('activeChatStateBadge');
        const btnFinish = document.getElementById('btnFinishHandoff');
        const btnTake = document.getElementById('btnTakeControl');

        if (s.estado === 'esperando_humano') {
            stateBadge.className = 'badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill';
            stateBadge.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Esperando Operador';
            btnFinish.classList.remove('d-none');
            btnTake.classList.add('d-none');
        } else if (s.estado === 'humano_activo') {
            stateBadge.className = 'badge bg-success-subtle text-success border border-success-subtle rounded-pill';
            stateBadge.innerHTML = '<i class="bi bi-person-check-fill me-1"></i> En Atención Humana';
            btnFinish.classList.remove('d-none');
            btnTake.classList.add('d-none');
        } else {
            stateBadge.className = 'badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill';
            stateBadge.innerHTML = '<i class="bi bi-robot me-1"></i> Atendido por IA';
            btnFinish.classList.add('d-none');
            btnTake.classList.remove('d-none');
        }

        // Renderizar Mensajes en el feed
        renderMensajesFeed(msgs, scrollBottom);

    } catch (e) {
        console.error('Error al cargar mensajes del chat activo:', e);
    }
}

function renderMensajesFeed(mensajes, forceScroll = false) {
    const feed = document.getElementById('chatMessagesFeed');
    const isAtBottom = feed.scrollHeight - feed.scrollTop <= feed.clientHeight + 100;

    let html = '';
    mensajes.forEach(m => {
        const hora = m.creado_en ? m.creado_en.substring(11, 16) : '';
        const formattedText = formatChatMessage(m.mensaje);

        if (m.remitente === 'sistema') {
            html += `
                <div class="msg-bubble-system">
                    <i class="bi bi-info-circle-fill me-1 text-amber-600"></i> ${formattedText}
                </div>
            `;
        } else if (m.remitente === 'admin') {
            html += `
                <div class="d-flex justify-content-end mb-3">
                    <div class="msg-bubble-admin">
                        <div class="d-flex align-items-center justify-content-between gap-3 mb-1">
                            <span class="fw-bold d-flex align-items-center gap-1" style="font-size: 0.7rem; color: #065f46;">
                                <i class="bi bi-shield-fill-check"></i> ${escapeHtml(m.autor_nombre || 'Operador')}
                            </span>
                            <span class="text-slate-400 d-flex align-items-center gap-1" style="font-size: 0.65rem;">
                                ${hora} <i class="bi bi-check2-all text-emerald-600"></i>
                            </span>
                        </div>
                        <div class="chat-msg-content text-sm leading-relaxed">${formattedText}</div>
                    </div>
                </div>
            `;
        } else if (m.remitente === 'ia') {
            html += `
                <div class="d-flex justify-content-end mb-3">
                    <div class="msg-bubble-ia">
                        <div class="d-flex align-items-center justify-content-between gap-3 mb-1.5 pb-1 border-bottom" style="border-color: #e2e8f0 !important;">
                            <span class="badge bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-pill d-inline-flex align-items-center gap-1.5 py-0.5 px-2" style="font-size: 0.65rem;">
                                <i class="bi bi-robot text-emerald-700"></i> Asistente Virtual IA
                            </span>
                            <span class="text-slate-400" style="font-size: 0.65rem;">${hora}</span>
                        </div>
                        <div class="chat-msg-content text-sm leading-relaxed text-slate-800">${formattedText}</div>
                    </div>
                </div>
            `;
        } else {
            // Visitante
            html += `
                <div class="d-flex justify-content-start mb-3">
                    <div class="msg-bubble-visitor">
                        <div class="d-flex align-items-center justify-content-between gap-3 mb-1">
                            <span class="fw-semibold text-slate-600 d-flex align-items-center gap-1" style="font-size: 0.7rem;">
                                <i class="bi bi-person-fill text-slate-400"></i> Visitante
                            </span>
                            <span class="text-slate-400" style="font-size: 0.65rem;">${hora}</span>
                        </div>
                        <div class="chat-msg-content text-sm leading-relaxed text-slate-800">${formattedText}</div>
                    </div>
                </div>
            `;
        }
    });

    feed.innerHTML = html;

    if (forceScroll || isAtBottom) {
        feed.scrollTop = feed.scrollHeight;
    }
}

function formatChatMessage(text) {
    if (!text) return '';
    let escaped = escapeHtml(text);
    // Bold: **text** -> <strong>text</strong>
    escaped = escaped.replace(/\*\*(.+?)\*\*/g, '<strong class="fw-semibold">$1</strong>');
    // Italic: *text* -> <em>text</em>
    escaped = escaped.replace(/(^|[^\*])\*([^\*]+)\*([^\*]|$)/g, '$1<em>$2</em>$3');
    // Enlaces markdown [texto](url)
    escaped = escaped.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" class="text-emerald-700 fw-semibold text-decoration-underline">$1</a>');

    // Split lines to handle bullets cleanly
    const lines = escaped.split('\n');
    let result = [];

    for (let i = 0; i < lines.length; i++) {
        let line = lines[i].trim();
        if (line.startsWith('•') || line.startsWith('- ') || line.startsWith('* ')) {
            let bulletContent = line.replace(/^[•\-\*]\s*/, '');
            result.push(`<div class="chat-bullet-item"><span class="chat-bullet-dot">•</span><span class="chat-bullet-text">${bulletContent}</span></div>`);
        } else if (line === '') {
            result.push('<div class="chat-space-sm"></div>');
        } else {
            result.push(`<div>${line}</div>`);
        }
    }

    return result.join('');
}

async function enviarMensajeAdmin(e) {
    e.preventDefault();
    if (!activeSessionId) return;

    const input = document.getElementById('adminMessageInput');
    const texto = input.value.trim();
    if (!texto) return;

    input.value = '';

    try {
        const formData = new FormData();
        formData.append('action', 'admin_send_message');
        formData.append('sesion_id', activeSessionId);
        formData.append('mensaje', texto);

        const res = await fetch('<?= e(url("api/chat.php")) ?>', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.ok) {
            await cargarMensajesActivo(activeSessionId, true);
            cargarSesiones(false);
        }
    } catch (err) {
        if (window.AdminDialog) {
            AdminDialog.toast('No se pudo enviar el mensaje.', 'danger');
        }
    }
}

async function finalizarAtencionIA() {
    if (!activeSessionId) return;
    
    let confirmado = false;
    if (window.AdminDialog) {
        confirmado = await AdminDialog.confirm({
            title: 'Devolver conversación a la IA',
            message: '¿Deseas finalizar la atención humana y permitir que el Asistente Virtual IA retome la atención automática con este visitante?',
            type: 'info',
            confirmText: 'Sí, devolver a la IA',
            cancelText: 'Seguir atendiendo'
        });
    } else {
        confirmado = confirm('¿Deseas finalizar la atención humana y devolver esta conversación al Asistente Virtual IA?');
    }
    if (!confirmado) return;

    try {
        const formData = new FormData();
        formData.append('action', 'admin_finish_session');
        formData.append('sesion_id', activeSessionId);

        const res = await fetch('<?= e(url("api/chat.php")) ?>', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.ok) {
            if (window.AdminDialog) {
                AdminDialog.toast('Atención devuelta a la IA con éxito.', 'success');
            }
            await cargarMensajesActivo(activeSessionId, true);
            cargarSesiones(false);
        }
    } catch (err) {
        if (window.AdminDialog) {
            AdminDialog.toast('Error al finalizar la sesión.', 'danger');
        }
    }
}

async function tomarControlHumano() {
    if (!activeSessionId) return;
    document.getElementById('adminMessageInput')?.focus();
    insertarPlantilla('Hola, un saludo cordial. A partir de este momento te atiende un operador humano de la EPSDC. ¿En qué puedo apoyarte?');
}

function insertarPlantilla(texto) {
    const input = document.getElementById('adminMessageInput');
    if (input) {
        input.value = texto;
        input.focus();
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.innerText = text;
    return div.innerHTML;
}
</script>

<?php require __DIR__ . '/layout/footer.php'; ?>
