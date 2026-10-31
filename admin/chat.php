<?php
require __DIR__ . '/../includes/app.php';
requiere_login();

$titulo = 'Atención en Vivo · Chat Comunal';
$seccion = 'chat';
$u = usuario_actual();

require __DIR__ . '/layout/header.php';
?>

<div class="card border-0 shadow-sm overflow-hidden" style="height: calc(100vh - 160px); min-height: 550px;">
    <div class="row g-0 h-100">
        
        <!-- PANEL IZQUIERDO: LISTA DE CONVERSACIONES (WHATSAPP WEB STYLE) -->
        <div class="col-12 col-md-4 col-lg-4 border-end d-flex flex-column h-100 bg-white">
            
            <!-- Barra superior del panel izquierdo -->
            <div class="p-3 border-bottom bg-light">
                <div class="d-flex align-items-center justify-between mb-2">
                    <h2 class="h6 fw-bold mb-0 text-dark">
                        <i class="bi bi-chat-left-text-fill text-primary me-1.5"></i> Conversaciones
                    </h2>
                    <span id="badgeEsperandoHumano" class="badge bg-danger rounded-pill d-none animate__animated animate__pulse animate__infinite">
                        0 esperando
                    </span>
                </div>

                <!-- Buscador de sesiones -->
                <div class="input-group input-group-sm mb-2">
                    <span class="input-group-text bg-white border-end-0 text-secondary"><i class="bi bi-search"></i></span>
                    <input type="text" id="sessionSearchInput" oninput="filtrarSesionesLista()" class="form-control border-start-0" placeholder="Buscar visitante o mensaje...">
                </div>

                <!-- Filtros rápidos -->
                <div class="d-flex gap-1" role="group">
                    <button type="button" onclick="cambiarFiltroSesiones('todos', this)" class="btn btn-xs btn-primary fw-semibold flex-fill filter-btn active">Todas</button>
                    <button type="button" onclick="cambiarFiltroSesiones('humano', this)" class="btn btn-xs btn-outline-danger fw-semibold flex-fill filter-btn">
                        <i class="bi bi-person-fill"></i> Esperando
                    </button>
                    <button type="button" onclick="cambiarFiltroSesiones('ia', this)" class="btn btn-xs btn-outline-secondary fw-semibold flex-fill filter-btn">
                        <i class="bi bi-robot"></i> IA
                    </button>
                </div>
            </div>

            <!-- Lista scrolleable de chats -->
            <div id="sessionsListContainer" class="flex-grow-1 overflow-y-auto" style="overflow-x: hidden;">
                <div class="text-center py-5 text-secondary">
                    <div class="spinner-border spinner-border-sm text-primary mb-2"></div>
                    <div class="small">Cargando conversaciones...</div>
                </div>
            </div>
            
            <!-- Pie de estadísticas rápidas -->
            <div class="p-2.5 bg-light border-top text-center text-secondary small d-flex justify-content-between align-items-center px-3">
                <span id="lblTotalSesiones" class="text-muted" style="font-size: 0.75rem;">0 chats totales</span>
                <span class="text-success fw-bold d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;">
                    <span class="p-1 bg-success rounded-circle"></span> Sincronizado en vivo
                </span>
            </div>
        </div>

        <!-- PANEL DERECHO: VISTA DE MENSAJES Y CHAT ACTIVO -->
        <div class="col-12 col-md-8 col-lg-8 d-flex flex-column h-100 bg-light position-relative">
            
            <!-- Estado vacío cuando no hay chat seleccionado -->
            <div id="noChatSelectedView" class="h-100 d-flex flex-column align-items-center justify-content-center text-center p-4">
                <div class="rounded-circle bg-white p-4 shadow-sm mb-3 border text-primary">
                    <i class="bi bi-chat-heart" style="font-size: 3rem;"></i>
                </div>
                <h3 class="h5 fw-bold text-dark">Sala de Atención en Vivo · EPSDC La Nueva Esperanza</h3>
                <p class="text-secondary small max-w-md mx-auto" style="max-width: 420px;">
                    Selecciona una conversación de la izquierda para responder en tiempo real al usuario o monitorear las respuestas del Asistente Virtual IA.
                </p>
                <div class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill small">
                    <i class="bi bi-shield-check me-1"></i> Modo Sincronizado Tipo WhatsApp
                </div>
            </div>

            <!-- Contenedor del chat seleccionado (Oculto inicialmente) -->
            <div id="activeChatView" class="d-none h-100 d-flex flex-column">
                
                <!-- Header del chat activo -->
                <div class="p-2.5 px-3 bg-white border-bottom d-flex align-items-center justify-content-between shadow-2xs">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="position-relative">
                            <div id="activeChatAvatar" class="avatar bg-primary text-white fw-bold fs-6">V</div>
                            <span id="activeChatOnlineDot" class="position-absolute bottom-0 end-0 p-1 bg-success border border-white rounded-circle"></span>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h3 id="activeChatName" class="h6 fw-bold mb-0 text-dark">Visitante #0000</h3>
                                <span id="activeChatStateBadge" class="badge bg-secondary rounded-pill small" style="font-size: 0.68rem;">IA</span>
                            </div>
                            <div id="activeChatSubtitle" class="text-secondary" style="font-size: 0.72rem;">IP: ... · Conectado</div>
                        </div>
                    </div>

                    <!-- Botones de Acción: Finalizar con IA o Tomar Control -->
                    <div class="d-flex align-items-center gap-2">
                        <button id="btnFinishHandoff" type="button" onclick="finalizarAtencionIA()" class="btn btn-sm btn-success fw-bold d-flex align-items-center gap-1.5 shadow-2xs">
                            <i class="bi bi-robot"></i>
                            <span class="d-none d-sm-inline">Devolver a la IA</span>
                        </button>
                        <button id="btnTakeControl" type="button" onclick="tomarControlHumano()" class="btn btn-sm btn-outline-primary fw-bold d-flex align-items-center gap-1.5 shadow-2xs">
                            <i class="bi bi-person-fill"></i>
                            <span class="d-none d-sm-inline">Tomar Control</span>
                        </button>
                    </div>
                </div>

                <!-- Feed de Mensajes (Estilo WhatsApp) -->
                <div id="chatMessagesFeed" class="flex-grow-1 p-3 overflow-y-auto" style="background-color: #efeae2; background-image: radial-gradient(#00000008 1px, transparent 1px); background-size: 16px 16px;">
                    <!-- Mensajes inyectados dinámicamente -->
                </div>

                <!-- Respuestas Rápidas Predefinidas -->
                <div class="px-3 py-1.5 bg-white border-top border-bottom d-flex gap-1.5 overflow-x-auto text-nowrap" style="scrollbar-width: none;">
                    <span class="badge text-secondary bg-light border align-self-center px-2 py-1">Respuestas rápidas:</span>
                    <button type="button" onclick="insertarPlantilla('¡Hola! Un gusto saludarle. ¿En qué podemos colaborarle hoy?')" class="btn btn-xs btn-outline-secondary rounded-pill">👋 Saludo</button>
                    <button type="button" onclick="insertarPlantilla('¿Perteneces a alguna UPF o Consejo Comunal en específico? Indícanos tu aldea o municipio.')" class="btn btn-xs btn-outline-secondary rounded-pill">🌱 Aldea / UPF</button>
                    <button type="button" onclick="insertarPlantilla('Las rutas de entrega de combos de esta semana están activas en el cronograma. ¿Deseas apartar cupo en algún punto de acopio?')" class="btn btn-xs btn-outline-secondary rounded-pill">📦 Entregas</button>
                    <button type="button" onclick="insertarPlantilla('Hemos registrado tu solicitud en el sistema de la EPSDC. El comité se comunicará contigo.')" class="btn btn-xs btn-outline-secondary rounded-pill">✅ Registro Exitoso</button>
                </div>

                <!-- Barra de Entrada de Mensajes (Estilo WhatsApp) -->
                <div class="p-2.5 bg-white border-top">
                    <form id="adminSendMessageForm" onsubmit="enviarMensajeAdmin(event)" class="d-flex align-items-center gap-2">
                        <input type="text" id="adminMessageInput" class="form-control rounded-pill px-3 py-2 text-sm" placeholder="Escribe un mensaje como operador comunal... (Enter para enviar)" autocomplete="off">
                        <button type="submit" class="btn btn-success rounded-circle d-flex align-items-center justify-content-center shadow-xs" style="width: 42px; height: 42px; flex-shrink: 0;" title="Enviar">
                            <i class="bi bi-send-fill fs-6"></i>
                        </button>
                    </form>
                </div>

            </div>

        </div>

    </div>
</div>

<style>
/* Estilos WhatsApp Web para el Panel de Chat */
.btn-xs {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}
.shadow-2xs {
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.session-item {
    transition: background-color 0.15s ease;
    cursor: pointer;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}
.session-item:hover {
    background-color: #f8fafc;
}
.session-item.active {
    background-color: #e2f1e8 !important;
    border-left: 4px solid #16a34a;
}
.msg-bubble-visitor {
    background-color: #ffffff;
    color: #1e293b;
    border-radius: 14px 14px 14px 3px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    max-width: 78%;
}
.msg-bubble-admin {
    background-color: #d9fdd3;
    color: #0f172a;
    border-radius: 14px 14px 3px 14px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    max-width: 78%;
}
.msg-bubble-ia {
    background-color: #f1f5f9;
    color: #334155;
    border: 1px solid #e2e8f0;
    border-radius: 14px 14px 14px 3px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    max-width: 82%;
}
.msg-bubble-system {
    background-color: #ffe4e6;
    color: #9f1239;
    font-size: 0.74rem;
    font-weight: 600;
    border-radius: 12px;
    padding: 6px 12px;
    max-width: 90%;
    margin: 8px auto;
    text-align: center;
    border: 1px solid #fecdd3;
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
                <i class="bi bi-chat-square-dots fs-3 d-block mb-2 text-muted"></i>
                <div class="small fw-semibold">No se encontraron conversaciones</div>
                <div class="text-muted" style="font-size: 0.72rem;">Los mensajes del chat público aparecerán aquí.</div>
            </div>
        `;
        return;
    }

    let html = '';
    filtered.forEach(s => {
        const isActive = (s.id == activeSessionId) ? 'active' : '';
        const initial = (s.nombre_visitante ? s.nombre_visitante.replace('Visitante #', 'V') : 'V').substring(0, 2);
        
        // Determinar badge de estado
        let badgeEstado = '';
        if (s.estado === 'esperando_humano') {
            badgeEstado = '<span class="badge bg-danger rounded-pill px-2 py-0.5 animate__animated animate__pulse animate__infinite" style="font-size:0.65rem;">¡Requiere Humano!</span>';
        } else if (s.estado === 'humano_activo') {
            badgeEstado = '<span class="badge bg-success rounded-pill px-2 py-0.5" style="font-size:0.65rem;">En Vivo (Humano)</span>';
        } else {
            badgeEstado = '<span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-0.5" style="font-size:0.65rem;">IA Activa</span>';
        }

        // Badge no leídos
        const unread = parseInt(s.no_leidos_admin || 0);
        const unreadBadge = unread > 0 ? `<span class="badge bg-danger rounded-pill ms-auto">${unread}</span>` : '';

        // Formato de hora
        const hora = s.actualizado_en ? s.actualizado_en.substring(11, 16) : '';

        html += `
            <div onclick="seleccionarChat(${s.id})" class="session-item p-3 d-flex align-items-center gap-2.5 ${isActive}" data-id="${s.id}">
                <div class="avatar bg-primary-subtle text-primary fw-bold rounded-circle flex-shrink-0 d-flex align-items-center justify-center" style="width: 44px; height: 44px; font-size: 0.85rem;">
                    ${initial}
                </div>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="d-flex justify-content-between align-items-center mb-0.5">
                        <span class="fw-bold text-dark text-truncate text-sm" style="max-width: 140px;">${escapeHtml(s.nombre_visitante || 'Visitante')}</span>
                        <span class="text-muted" style="font-size: 0.7rem;">${hora}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="text-secondary text-truncate small me-2" style="font-size: 0.75rem; max-width: 160px;">
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
    document.querySelectorAll('.filter-btn').forEach(b => {
        b.classList.remove('active', 'btn-primary', 'btn-danger', 'btn-secondary');
        if (b.innerText.includes('Todas')) b.classList.add('btn-outline-primary');
        if (b.innerText.includes('Esperando')) b.classList.add('btn-outline-danger');
        if (b.innerText.includes('IA')) b.classList.add('btn-outline-secondary');
    });

    btn.classList.add('active');
    if (filtro === 'todos') {
        btn.classList.add('btn-primary');
        btn.classList.remove('btn-outline-primary');
    } else if (filtro === 'humano') {
        btn.classList.add('btn-danger');
        btn.classList.remove('btn-outline-danger');
    } else {
        btn.classList.add('btn-secondary');
        btn.classList.remove('btn-outline-secondary');
    }

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
        document.getElementById('activeChatSubtitle').innerText = `IP: ${s.ip || 'Local'} · Sesión #${s.id} · Estado: ${s.estado}`;

        const stateBadge = document.getElementById('activeChatStateBadge');
        const btnFinish = document.getElementById('btnFinishHandoff');
        const btnTake = document.getElementById('btnTakeControl');

        if (s.estado === 'esperando_humano') {
            stateBadge.className = 'badge bg-danger rounded-pill';
            stateBadge.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Esperando Operador';
            btnFinish.classList.remove('d-none');
            btnTake.classList.add('d-none');
        } else if (s.estado === 'humano_activo') {
            stateBadge.className = 'badge bg-success rounded-pill';
            stateBadge.innerHTML = '<i class="bi bi-person-check-fill me-1"></i> En Atención Humana';
            btnFinish.classList.remove('d-none');
            btnTake.classList.add('d-none');
        } else {
            stateBadge.className = 'badge bg-secondary rounded-pill';
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

        if (m.remitente === 'sistema') {
            html += `
                <div class="msg-bubble-system">
                    <i class="bi bi-info-circle-fill me-1"></i> ${escapeHtml(m.mensaje)}
                </div>
            `;
        } else if (m.remitente === 'admin') {
            html += `
                <div class="d-flex justify-content-end mb-2.5">
                    <div class="msg-bubble-admin p-2.5 px-3">
                        <div class="d-flex align-items-center justify-content-between gap-3 mb-1">
                            <span class="fw-bold text-success" style="font-size: 0.68rem;"><i class="bi bi-shield-fill-check"></i> ${escapeHtml(m.autor_nombre || 'Operador')}</span>
                            <span class="text-muted" style="font-size: 0.65rem;">${hora} <i class="bi bi-check2-all text-primary"></i></span>
                        </div>
                        <div class="text-sm leading-normal whitespace-pre-wrap">${escapeHtml(m.mensaje)}</div>
                    </div>
                </div>
            `;
        } else if (m.remitente === 'ia') {
            html += `
                <div class="d-flex justify-content-start mb-2.5">
                    <div class="msg-bubble-ia p-2.5 px-3">
                        <div class="d-flex align-items-center justify-content-between gap-3 mb-1">
                            <span class="fw-bold text-primary" style="font-size: 0.68rem;"><i class="bi bi-robot"></i> Asistente Virtual IA</span>
                            <span class="text-muted" style="font-size: 0.65rem;">${hora}</span>
                        </div>
                        <div class="text-sm leading-normal whitespace-pre-wrap">${escapeHtml(m.mensaje)}</div>
                    </div>
                </div>
            `;
        } else {
            // Visitante
            html += `
                <div class="d-flex justify-content-start mb-2.5">
                    <div class="msg-bubble-visitor p-2.5 px-3">
                        <div class="d-flex align-items-center justify-content-between gap-3 mb-1">
                            <span class="fw-bold text-dark" style="font-size: 0.68rem;"><i class="bi bi-person-fill text-secondary"></i> Usuario / Visitante</span>
                            <span class="text-muted" style="font-size: 0.65rem;">${hora}</span>
                        </div>
                        <div class="text-sm leading-normal whitespace-pre-wrap">${escapeHtml(m.mensaje)}</div>
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
        alert('Error al enviar mensaje');
    }
}

async function finalizarAtencionIA() {
    if (!activeSessionId) return;
    if (!confirm('¿Deseas finalizar la atención humana y devolver esta conversación al Asistente Virtual IA?')) return;

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
            await cargarMensajesActivo(activeSessionId, true);
            cargarSesiones(false);
        }
    } catch (err) {
        alert('Error al finalizar sesión');
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
