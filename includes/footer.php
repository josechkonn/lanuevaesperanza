<?php
// includes/footer.php - Pie de Página y Chat Comunal Flotante
?>
    </main>

    <!-- FOOTER INSTITUCIONAL Y COMUNAL -->
    <footer class="bg-slate-950 text-slate-400 py-14 border-t border-slate-900 text-xs mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8 mb-10">
                
                <!-- Columna 1: Identidad EPSDC -->
                <div class="md:col-span-5 space-y-3">
                    <div class="flex items-center gap-2.5 text-white font-extrabold text-base">
                        <i class="fa-solid fa-mountain-sun text-amberGold text-xl"></i>
                        <span>EPSDC <?= strtoupper(e($org)) ?></span>
                    </div>
                    <p class="text-slate-400 leading-relaxed max-w-sm text-xs">
                        Empresa de Propiedad Social Directa Comunal. Conectando la Red Comunal Andina, Unidades de Producción Familiar (UPF) y comités campesinos de La Grita y todo el estado Táchira para garantizar soberanía agroalimentaria continua los 365 días del año.
                    </p>
                    <div class="flex items-center gap-2 pt-2 text-[11px] text-slate-400 font-medium">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>Motor Agrícola del Táchira • 65% de la Producción</span>
                    </div>
                </div>

                <!-- Columna 2: Páginas y Catálogos -->
                <div class="md:col-span-3 space-y-2.5">
                    <h4 class="text-white font-black uppercase tracking-wider text-[11px]">Páginas & Catálogos</h4>
                    <ul class="space-y-2">
                        <li><a href="<?= e(url('')) ?>" class="hover:text-white transition flex items-center gap-1.5"><i class="fa-solid fa-house text-slate-500 text-[10px]"></i> Inicio</a></li>
                        <li><a href="<?= e(url('#combos')) ?>" class="hover:text-white transition flex items-center gap-1.5"><i class="fa-solid fa-bag-shopping text-slate-500 text-[10px]"></i> Bolsas y Combos</a></li>
                        <li><a href="<?= e(url('#entregas')) ?>" class="hover:text-amber-300 transition text-amber-400 flex items-center gap-1.5"><i class="fa-solid fa-truck-fast text-[10px]"></i> Rutas de Entrega</a></li>
                        <li><a href="<?= e(url('rubros.php')) ?>" class="hover:text-white transition flex items-center gap-1.5"><i class="fa-solid fa-carrot text-orange-400 text-[10px]"></i> Catálogo de Rubros & Especialidades</a></li>
                        <li><a href="<?= e(url('territorio.php')) ?>" class="hover:text-white transition flex items-center gap-1.5"><i class="fa-solid fa-map-location-dot text-cyan-400 text-[10px]"></i> Territorio, Cuencas & Mapa</a></li>
                        <li><a href="<?= e(url('estatutos.php')) ?>" class="hover:text-white transition flex items-center gap-1.5"><i class="fa-solid fa-landmark text-amber-400 text-[10px]"></i> Marco Comunal & Fondos</a></li>
                    </ul>
                </div>

                <!-- Columna 3: Ubicación y Contacto Comunal -->
                <div class="md:col-span-4 space-y-2.5">
                    <h4 class="text-white font-black uppercase tracking-wider text-[11px]">Sede y Atención</h4>
                    <p class="leading-relaxed"><i class="fa-solid fa-location-dot text-amberGold mr-1.5"></i> <strong>Sede:</strong> Ciudad de La Grita, Aldea Quebrada de San José, Municipio Jáuregui, Estado Táchira.</p>
                    <p><i class="fa-solid fa-phone text-amberGold mr-1.5"></i> <?= e($tel) ?></p>
                    <p><i class="fa-solid fa-envelope text-amberGold mr-1.5"></i> <?= e($email) ?></p>
                    <div class="pt-2">
                        <a href="<?= e(url('admin/login.php')) ?>" class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-slate-200 border border-slate-800 px-3 py-1.5 rounded-xl font-bold transition text-[11px]">
                            <i class="fa-solid fa-shield-halved text-amber-400"></i>
                            <span>Acceso Administrativo</span>
                        </a>
                    </div>
                </div>

            </div>

            <div class="border-t border-slate-900 pt-6 flex flex-col sm:flex-row justify-between items-center text-slate-500 gap-3">
                <p>&copy; <?= date('Y') ?> EPSDC <?= e($org) ?>. RIF J-41298453-0 • Soberanía y Red Comunal.</p>
                <p class="text-[11px]">La Grita • Estado Táchira • Venezuela</p>
            </div>
        </div>
    </footer>

    <!-- WIDGET FLOTANTE DE CHAT COMUNAL & ATENCIÓN (LITERAL G) -->
    <!-- Backdrop oscuro para cuando el chat se expande en modal grande -->
    <div id="comunalChatBackdrop" onclick="toggleExpandChat()" class="hidden fixed inset-0 bg-slate-950/60 backdrop-blur-xs z-40 transition-opacity duration-300"></div>

    <div id="comunalChatWidget" class="fixed bottom-6 right-6 z-50 flex flex-col items-end">
        <!-- Ventana del Chat -->
        <div id="comunalChatWindow" class="hidden mb-4 w-[360px] sm:w-[410px] max-w-[calc(100vw-2rem)] bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden flex flex-col transition-all duration-300 transform origin-bottom-right" style="height: 550px; max-height: calc(100vh - 100px);">
            <!-- Chat Header (Limpio, Humano, Profesional y sin Clichés de IA) -->
            <div class="bg-[#133527] text-white px-4 py-3 flex items-center justify-between border-b border-emerald-950/60 select-none">
                <!-- Izquierda: Identidad y Estado Discreto -->
                <div class="flex items-center gap-2.5 min-w-0 flex-1 mr-2">
                    <div class="relative w-8 h-8 rounded-full bg-emerald-900 border border-emerald-700/60 flex items-center justify-center text-amber-300 shrink-0">
                        <i id="chatHeaderIcon" class="fa-solid fa-mountain-sun text-xs"></i>
                        <span id="chatHeaderStatusDot" class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-emerald-400 border-2 border-[#133527] rounded-full"></span>
                    </div>
                    <div class="min-w-0">
                        <div class="font-bold text-xs sm:text-sm text-white truncate leading-tight"><?= e($org) ?></div>
                        <div class="flex items-center gap-1.5 text-[11px] text-emerald-200/80 truncate mt-0.5">
                            <span id="chatHeaderSubtitle">Orientación Comunal</span>
                            <span id="chatStatePill" class="text-[9px] bg-emerald-950 text-emerald-300 px-1.5 py-0.2 rounded font-medium border border-emerald-800/60">La Grita • Táchira</span>
                        </div>
                    </div>
                </div>

                <!-- Derecha: Acciones Compactas (Espaciadas, sin chocar) -->
                <div class="flex items-center gap-1 shrink-0">
                    <button id="btnResetChat" onclick="reiniciarChatComunal()" type="button" class="w-8 h-8 rounded-lg text-emerald-200 hover:text-white hover:bg-emerald-900/80 transition flex items-center justify-center cursor-pointer" title="Iniciar nueva conversación / Resetear chat">
                        <i class="fa-solid fa-arrows-rotate text-xs"></i>
                    </button>
                    <button id="btnRequestHuman" onclick="solicitarOperadorHumano()" type="button" class="px-2.5 py-1 rounded-lg text-emerald-100 hover:text-white bg-emerald-900/80 hover:bg-emerald-800 border border-emerald-700/60 transition flex items-center gap-1.5 text-xs font-semibold cursor-pointer shadow-xs" title="Hablar con un coordinador de la administración">
                        <i class="fa-solid fa-user-check text-amber-300 text-[11px]"></i>
                        <span class="text-[11px]">Coordinador</span>
                    </button>
                    <button id="btnExpandChat" onclick="toggleExpandChat()" type="button" class="w-8 h-8 rounded-lg text-emerald-200 hover:text-white hover:bg-emerald-900/80 transition flex items-center justify-center cursor-pointer" title="Expandir ventana">
                        <i id="iconExpandChat" class="fa-solid fa-expand text-xs"></i>
                    </button>
                    <button onclick="toggleComunalChat()" type="button" class="w-8 h-8 rounded-lg text-emerald-200 hover:text-white hover:bg-emerald-900/80 transition flex items-center justify-center cursor-pointer" title="Cerrar">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>
            </div>

            <!-- Chat Messages Container -->
            <div id="chatMessages" class="flex-1 p-4 overflow-y-auto space-y-3 bg-slate-50/70 text-xs">
                <!-- Se cargan dinámicamente desde el backend -->
            </div>

            <!-- Indicador de 'Escribiendo...' (Discreto y Minimalista) -->
            <div id="chatTypingIndicator" class="hidden px-4 py-2 bg-slate-50 border-t border-slate-100 flex items-center gap-2 text-[11px] text-slate-500 font-normal">
                <span class="flex gap-1">
                    <span class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce"></span>
                    <span class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce [animation-delay:0.2s]"></span>
                    <span class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce [animation-delay:0.4s]"></span>
                </span>
                <span id="chatTypingLabel">Escribiendo...</span>
            </div>

            <!-- Formulario Rápido Productor en Chat -->
            <div id="chatProductorFormContainer" class="hidden p-4 bg-slate-100 border-t border-slate-200 overflow-y-auto max-h-[320px] text-xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-bold text-slate-800 text-[11px]"><i class="fa-solid fa-pen-to-square text-agro-600 mr-1"></i> Formulario Rápido de Productor / UPF</span>
                    <button onclick="hideProductorChatForm()" class="text-slate-400 hover:text-slate-600 font-bold">&times;</button>
                </div>
                <form id="chatProductorForm" onsubmit="submitChatProductor(event)" class="space-y-2">
                    <input type="text" id="cpf_nombre" required placeholder="Nombre completo o UPF *" class="w-full px-3 py-1.5 bg-white rounded-lg border border-slate-300 text-xs">
                    <input type="text" id="cpf_sector" required placeholder="Sector / Municipio / Aldea *" class="w-full px-3 py-1.5 bg-white rounded-lg border border-slate-300 text-xs">
                    <input type="text" id="cpf_telefono" required placeholder="Teléfono de contacto *" class="w-full px-3 py-1.5 bg-white rounded-lg border border-slate-300 text-xs">
                    <textarea id="cpf_mensaje" required rows="2" placeholder="Rubro cosechado, volumen o solicitud *" class="w-full px-3 py-1.5 bg-white rounded-lg border border-slate-300 text-xs"></textarea>
                    <div class="flex gap-2 pt-1">
                        <button type="submit" class="flex-1 bg-agro-700 hover:bg-agro-800 text-white font-bold py-1.5 rounded-lg text-xs transition">Enviar al Sistema</button>
                        <button type="button" onclick="hideProductorChatForm()" class="px-3 py-1.5 bg-slate-200 text-slate-700 rounded-lg text-xs font-bold">Cancelar</button>
                    </div>
                </form>
            </div>

            <!-- Barra de Píldoras Rápidas (Chips Limpios, Neutros y Profesionales) -->
            <div id="chatQuickChipsBar" class="px-3.5 py-2 bg-slate-50 border-t border-slate-200/80 flex items-center gap-1.5 overflow-x-auto text-nowrap no-scrollbar" style="scrollbar-width: none;">
                <button type="button" onclick="reiniciarChatComunal()" class="px-3 py-1 bg-white hover:bg-slate-100 text-slate-700 rounded-full border border-slate-200 text-[11px] font-medium transition cursor-pointer shadow-2xs flex items-center gap-1.5" title="Iniciar un nuevo chat">
                    <i class="fa-solid fa-arrows-rotate text-[10px] text-emerald-700"></i> Nuevo Chat
                </button>
                <button type="button" onclick="sendQuickPrompt('¿Cuáles combos tienen disponibles y qué precio tiene cada uno?')" class="px-3 py-1 bg-white hover:bg-slate-100 text-slate-700 rounded-full border border-slate-200 text-[11px] font-medium transition cursor-pointer shadow-2xs">
                    📦 Combos y Precios
                </button>
                <button type="button" onclick="sendQuickPrompt('¿Cuáles son las próximas fechas de entrega programadas?')" class="px-3 py-1 bg-white hover:bg-slate-100 text-slate-700 rounded-full border border-slate-200 text-[11px] font-medium transition cursor-pointer shadow-2xs">
                    🚚 Entregas
                </button>
                <button type="button" onclick="sendQuickPrompt('Háblame de las Truchas en Las Porqueras')" class="px-3 py-1 bg-white hover:bg-slate-100 text-slate-700 rounded-full border border-slate-200 text-[11px] font-medium transition cursor-pointer shadow-2xs">
                    🐟 Truchas Las Porqueras
                </button>
                <button type="button" onclick="sendQuickPrompt('Háblame del Café de Pregonero y la Panela de Humuquena')" class="px-3 py-1 bg-white hover:bg-slate-100 text-slate-700 rounded-full border border-slate-200 text-[11px] font-medium transition cursor-pointer shadow-2xs">
                    ☕ Café & Panela
                </button>
                <button type="button" onclick="solicitarOperadorHumano()" class="px-3 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 rounded-full border border-emerald-200 text-[11px] font-semibold transition cursor-pointer shadow-2xs">
                    👨‍🌾 Coordinación Directa
                </button>
            </div>

            <!-- Chat Input Footer -->
            <div class="p-3 bg-white border-t border-slate-200">
                <form id="chatForm" onsubmit="handleChatSubmit(event)" class="flex items-center gap-2">
                    <input type="text" id="chatInput" placeholder="Escribe tu consulta aquí..." class="flex-1 px-3.5 py-2.5 bg-slate-50 hover:bg-white focus:bg-white rounded-xl border border-slate-200 focus:border-slate-400 focus:outline-none text-xs text-slate-800 font-normal transition placeholder:text-slate-400" autocomplete="off">
                    <button type="submit" class="w-9 h-9 rounded-xl bg-[#1b4332] hover:bg-[#2d6a4f] text-white flex items-center justify-center transition shrink-0 cursor-pointer shadow-xs" title="Enviar mensaje">
                        <i class="fa-solid fa-arrow-up text-xs"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- Botón Lanzador Flotante (Limpio y Elegante) -->
        <button id="comunalChatLauncher" onclick="toggleComunalChat()" class="relative bg-[#1b4332] hover:bg-[#2d6a4f] text-white w-14 h-14 rounded-full flex items-center justify-center text-xl shadow-lg transition-transform duration-200 hover:scale-105 active:scale-95 cursor-pointer" title="Abrir atención en línea">
            <i class="fa-solid fa-comments text-lg"></i>
            <span class="absolute top-0 right-0 w-3.5 h-3.5 bg-emerald-500 border-2 border-white rounded-full"></span>
        </button>
    </div>

    <!-- Scripts Globales & Motor de Chat Sincronizado -->
    <script>
        let chatSessionUuid = null;
        let chatCurrentState = 'ia';
        let chatLastMessageId = 0;
        let chatPollingTimer = null;
        let isChatInitialized = false;

        document.addEventListener('DOMContentLoaded', () => {
            initChatSession();
        });

        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            if (menu) menu.classList.toggle('hidden');
        }

        // Obtener o crear UUID persistente del visitante (LocalStorage + Cookies)
        function getVisitorUuid() {
            let uuid = localStorage.getItem('epsdc_chat_uuid');
            if (!uuid || uuid.length < 10) {
                // Intentar leer de cookie
                const match = document.cookie.match(new RegExp('(^| )epsdc_chat_uuid=([^;]+)'));
                if (match && match[2] && match[2].length >= 10) {
                    uuid = match[2];
                }
            }
            if (!uuid || uuid.length < 10) {
                uuid = 'lne_' + Math.random().toString(36).substring(2, 12) + Date.now().toString(36);
            }
            localStorage.setItem('epsdc_chat_uuid', uuid);
            document.cookie = `epsdc_chat_uuid=${uuid}; path=/; max-age=31536000; SameSite=Lax`;
            return uuid;
        }

        async function initChatSession() {
            chatSessionUuid = getVisitorUuid();
            try {
                const res = await fetch(`<?= url("api/chat.php") ?>?action=session_init&session_uuid=${encodeURIComponent(chatSessionUuid)}`);
                const data = await res.json();
                if (!data.ok) return;

                if (data.session && data.session.uuid) {
                    chatSessionUuid = data.session.uuid;
                    localStorage.setItem('epsdc_chat_uuid', chatSessionUuid);
                    document.cookie = `epsdc_chat_uuid=${chatSessionUuid}; path=/; max-age=31536000; SameSite=Lax`;
                }

                chatCurrentState = data.session.estado || 'ia';
                updateChatHeaderUI(chatCurrentState);

                // Renderizar historial de mensajes
                const container = document.getElementById('chatMessages');
                if (container) {
                    container.innerHTML = '';
                    (data.mensajes || []).forEach(m => {
                        appendMessageBubble(m.remitente, m.mensaje, m.autor_nombre, m.creado_en);
                        if (m.id > chatLastMessageId) chatLastMessageId = m.id;
                    });
                    scrollChatToBottom();
                }

                isChatInitialized = true;
            } catch (err) {
                console.warn('Detalle al inicializar sesión de chat:', err);
            }
        }

        let isChatExpanded = false;

        function toggleExpandChat() {
            const chatWin = document.getElementById('comunalChatWindow');
            const backdrop = document.getElementById('comunalChatBackdrop');
            const icon = document.getElementById('iconExpandChat');
            const btn = document.getElementById('btnExpandChat');
            if (!chatWin) return;

            isChatExpanded = !isChatExpanded;

            if (isChatExpanded) {
                chatWin.classList.add('chat-modal-expanded');
                if (backdrop) backdrop.classList.remove('hidden');
                if (icon) {
                    icon.className = 'fa-solid fa-compress text-xs';
                }
                if (btn) btn.title = "Minimizar a tamaño flotante";
            } else {
                chatWin.classList.remove('chat-modal-expanded');
                if (backdrop) backdrop.classList.add('hidden');
                if (icon) {
                    icon.className = 'fa-solid fa-expand text-xs';
                }
                if (btn) btn.title = "Expandir en ventana amplia";
            }
            scrollChatToBottom();
        }

        function sendQuickPrompt(promptText) {
            const input = document.getElementById('chatInput');
            const form = document.getElementById('chatForm');
            if (!input || !form) return;
            input.value = promptText;
            form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
        }

        function formatChatMarkdown(str) {
            if (!str) return '';
            let out = escapeHtml(str);
            // Negrita **texto**
            out = out.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            // Viñetas con punto o guión
            out = out.replace(/^• (.*)$/gm, '<div class="flex items-start gap-1.5 ml-1 my-0.5"><span class="text-emerald-600 font-bold">•</span><span>$1</span></div>');
            out = out.replace(/^- (.*)$/gm, '<div class="flex items-start gap-1.5 ml-1 my-0.5"><span class="text-emerald-600 font-bold">•</span><span>$1</span></div>');
            // Saltos de línea
            out = out.replace(/\n/g, '<br>');
            return out;
        }

        function toggleComunalChat() {
            const chatWin = document.getElementById('comunalChatWindow');
            const backdrop = document.getElementById('comunalChatBackdrop');
            if (!chatWin) return;
            const isHidden = chatWin.classList.contains('hidden');
            if (isHidden) {
                chatWin.classList.remove('hidden');
                scrollChatToBottom();
                setTimeout(() => {
                    const input = document.getElementById('chatInput');
                    if (input) input.focus();
                }, 150);
                startChatPolling();
            } else {
                chatWin.classList.add('hidden');
                if (isChatExpanded) {
                    toggleExpandChat();
                } else if (backdrop) {
                    backdrop.classList.add('hidden');
                }
                stopChatPolling();
            }
        }

        function startChatPolling() {
            stopChatPolling();
            chatPollingTimer = setInterval(pollNewMessages, 3200);
        }

        function stopChatPolling() {
            if (chatPollingTimer) {
                clearInterval(chatPollingTimer);
                chatPollingTimer = null;
            }
        }

        async function pollNewMessages() {
            if (!chatSessionUuid) return;
            try {
                const res = await fetch(`<?= url("api/chat.php") ?>?action=get_messages&session_uuid=${encodeURIComponent(chatSessionUuid)}&after_id=${chatLastMessageId}`);
                const data = await res.json();
                if (!data.ok) return;

                if (data.estado && data.estado !== chatCurrentState) {
                    chatCurrentState = data.estado;
                    updateChatHeaderUI(chatCurrentState);
                }

                if (data.mensajes && data.mensajes.length > 0) {
                    data.mensajes.forEach(m => {
                        appendMessageBubble(m.remitente, m.mensaje, m.autor_nombre, m.creado_en);
                        if (m.id > chatLastMessageId) chatLastMessageId = m.id;
                    });
                    scrollChatToBottom();
                }
            } catch (e) {
                // Silencioso en caso de microcorte
            }
        }

        function updateChatHeaderUI(state) {
            const icon = document.getElementById('chatHeaderIcon');
            const dot = document.getElementById('chatHeaderStatusDot');
            const pill = document.getElementById('chatStatePill');
            const sub = document.getElementById('chatHeaderSubtitle');
            const btnHumano = document.getElementById('btnRequestHuman');

            if (state === 'esperando_humano') {
                if (icon) icon.className = 'fa-solid fa-clock text-xs text-amber-400';
                if (dot) dot.className = 'absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-amber-400 border-2 border-[#0f172a] rounded-full';
                if (pill) {
                    pill.className = 'text-[9px] bg-amber-950/80 text-amber-300 px-1.5 py-0.2 rounded font-medium';
                    pill.innerText = 'Esperando';
                }
                if (sub) sub.innerText = 'Atención humana';
                if (btnHumano) btnHumano.classList.add('hidden');
            } else if (state === 'humano_activo') {
                if (icon) icon.className = 'fa-solid fa-headset text-xs text-emerald-400';
                if (dot) dot.className = 'absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-emerald-500 border-2 border-[#0f172a] rounded-full';
                if (pill) {
                    pill.className = 'text-[9px] bg-emerald-950/80 text-emerald-300 px-1.5 py-0.2 rounded font-medium';
                    pill.innerText = 'En vivo';
                }
                if (sub) sub.innerText = 'Operador Comunal';
                if (btnHumano) btnHumano.classList.add('hidden');
            } else {
                // Modo Orientación Comunal
                if (icon) icon.className = 'fa-solid fa-mountain-sun text-xs text-amber-300';
                if (dot) dot.className = 'absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-emerald-400 border-2 border-[#133527] rounded-full';
                if (pill) {
                    pill.className = 'text-[9px] bg-emerald-950 text-emerald-300 px-1.5 py-0.2 rounded font-medium border border-emerald-800/60';
                    pill.innerText = 'La Grita • Táchira';
                }
                if (sub) sub.innerText = 'Orientación Comunal';
                if (btnHumano) btnHumano.classList.remove('hidden');
            }
        }

        async function solicitarOperadorHumano() {
            if (!chatSessionUuid) return;
            const confirmado = await ComunalModal.confirm({
                title: 'Transferir a Coordinación Comunal',
                message: '¿Deseas transferir la conversación a un vocero o coordinador de la administración? Un integrante de la EPSDC se conectará para coordinar directamente contigo.',
                icon: 'fa-solid fa-user-check text-emerald-700',
                type: 'info',
                confirmText: 'Sí, contactar coordinador',
                cancelText: 'Seguir aquí'
            });
            if (!confirmado) return;

            try {
                showTypingIndicator('Solicitando operador...');
                const formData = new FormData();
                formData.append('action', 'request_human');
                formData.append('session_uuid', chatSessionUuid);

                const res = await fetch('<?= e(url("api/chat.php")) ?>', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                hideTypingIndicator();

                if (data.ok) {
                    chatCurrentState = 'esperando_humano';
                    updateChatHeaderUI(chatCurrentState);
                    if (data.respuesta) {
                        appendMessageBubble('sistema', data.respuesta.mensaje, null, data.respuesta.creado_en);
                        if (data.respuesta.id > chatLastMessageId) chatLastMessageId = data.respuesta.id;
                    }
                    startChatPolling();
                    ComunalModal.toast({
                        title: 'Operador Solicitado',
                        message: 'Tu solicitud fue enviada a la administración comunal. En breve responderán.',
                        type: 'success'
                    });
                }
            } catch (err) {
                hideTypingIndicator();
                ComunalModal.alert({
                    title: 'Operador no disponible',
                    message: 'No se pudo contactar al operador en este instante. El Asistente Virtual continúa disponible para ti.',
                    type: 'warning'
                });
            }
        }

        async function reiniciarChatComunal() {
            const confirmado = await ComunalModal.confirm({
                title: 'Iniciar Nueva Conversación',
                message: '¿Desea reiniciar el chat y comenzar una nueva conversación? Se generará una sesión limpia e independiente.',
                icon: 'fa-solid fa-arrows-rotate text-emerald-700',
                type: 'info',
                confirmText: 'Sí, reiniciar chat',
                cancelText: 'Continuar actual'
            });
            if (!confirmado) return;

            stopChatPolling();

            try {
                showTypingIndicator('Iniciando nueva conversación...');
                const formData = new FormData();
                formData.append('action', 'reset_session');
                formData.append('session_uuid', chatSessionUuid || '');

                const res = await fetch('<?= e(url("api/chat.php")) ?>', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                hideTypingIndicator();

                if (data.ok && data.session) {
                    chatSessionUuid = data.session.uuid;
                    localStorage.setItem('epsdc_chat_uuid', chatSessionUuid);
                    document.cookie = `epsdc_chat_uuid=${chatSessionUuid}; path=/; max-age=31536000; SameSite=Lax`;
                    chatCurrentState = data.session.estado || 'ia';
                    chatLastMessageId = 0;
                    updateChatHeaderUI(chatCurrentState);

                    const container = document.getElementById('chatMessages');
                    if (container) {
                        container.innerHTML = '';
                        (data.mensajes || []).forEach(m => {
                            appendMessageBubble(m.remitente, m.mensaje, m.autor_nombre, m.creado_en);
                            if (m.id > chatLastMessageId) chatLastMessageId = m.id;
                        });
                        scrollChatToBottom();
                    }

                    ComunalModal.toast({
                        title: 'Conversación Reiniciada',
                        message: 'Se ha iniciado un nuevo chat con éxito.',
                        type: 'success'
                    });
                }
            } catch (err) {
                hideTypingIndicator();
                const newUuid = 'lne_' + Math.random().toString(36).substring(2, 12) + Date.now().toString(36);
                localStorage.setItem('epsdc_chat_uuid', newUuid);
                document.cookie = `epsdc_chat_uuid=${newUuid}; path=/; max-age=31536000; SameSite=Lax`;
                chatSessionUuid = newUuid;
                chatLastMessageId = 0;
                initChatSession();
            }
        }

        function scrollChatToBottom() {
            const container = document.getElementById('chatMessages');
            if (container) container.scrollTop = container.scrollHeight;
        }

        function appendMessageBubble(remitente, texto, autorNombre = null, fecha = null) {
            const container = document.getElementById('chatMessages');
            if (!container) return;

            const hora = fecha ? fecha.substring(11, 16) : '';
            let html = '';

            if (remitente === 'visitante') {
                html = `
                    <div class="flex gap-2 justify-end items-end">
                        <div class="bg-gradient-to-br from-emerald-600 via-agro-700 to-agro-800 text-white rounded-2xl rounded-tr-xs p-3 sm:p-3.5 shadow-md max-w-[85%] space-y-1">
                            <div class="leading-relaxed text-slate-50">${formatChatMarkdown(texto)}</div>
                            <div class="text-[9px] text-emerald-200 text-right font-medium flex items-center justify-end gap-1 mt-1">
                                <span>${hora}</span>
                                <i class="fa-solid fa-check text-[8px]"></i>
                            </div>
                        </div>
                    </div>
                `;
            } else if (remitente === 'sistema') {
                html = `
                    <div class="bg-amber-500/10 border border-amber-400/30 text-amber-900 rounded-2xl p-2.5 sm:p-3 text-[11px] font-semibold text-center my-2 shadow-2xs flex items-center justify-center gap-2">
                        <i class="fa-solid fa-bell text-amber-600"></i>
                        <span>${formatChatMarkdown(texto)}</span>
                    </div>
                `;
            } else if (remitente === 'admin') {
                html = `
                    <div class="flex gap-2.5 items-start">
                        <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-emerald-600 to-emerald-500 text-white flex items-center justify-center shrink-0 mt-0.5 text-xs shadow-md">
                            <i class="fa-solid fa-headset"></i>
                        </div>
                        <div class="bg-emerald-50/90 border border-emerald-300/80 text-emerald-950 rounded-2xl rounded-tl-xs p-3 sm:p-3.5 shadow-xs max-w-[88%] space-y-1.5">
                            <div class="flex items-center justify-between gap-3 border-b border-emerald-200/80 pb-1 mb-1">
                                <span class="font-extrabold text-[10px] text-emerald-800 uppercase tracking-wider flex items-center gap-1">
                                    <i class="fa-solid fa-shield-halved text-emerald-600"></i> ${escapeHtml(autorNombre || 'Operador Comunal')}
                                </span>
                                <span class="text-[9px] text-emerald-700 font-bold">${hora}</span>
                            </div>
                            <div class="leading-relaxed text-slate-800 text-xs">${formatChatMarkdown(texto)}</div>
                        </div>
                    </div>
                `;
            } else {
                // Orientación Comunal (Canal Oficial)
                html = `
                    <div class="flex gap-2.5 items-start">
                        <div class="w-8 h-8 rounded-xl bg-emerald-900 border border-emerald-700/70 text-amber-300 flex items-center justify-center shrink-0 mt-0.5 text-xs shadow-xs">
                            <i class="fa-solid fa-mountain-sun"></i>
                        </div>
                        <div class="bg-white border border-slate-200/90 text-slate-800 rounded-2xl rounded-tl-xs p-3 sm:p-3.5 shadow-sm max-w-[88%] space-y-1.5">
                            <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-1 mb-1">
                                <span class="font-extrabold text-[10px] text-emerald-800 uppercase tracking-wider flex items-center gap-1.5">
                                    <i class="fa-solid fa-leaf text-emerald-600"></i> Orientación Comunal
                                </span>
                                <span class="text-[9px] text-slate-400 font-medium">${hora}</span>
                            </div>
                            <div class="leading-relaxed text-slate-700 text-xs">${formatChatMarkdown(texto)}</div>
                        </div>
                    </div>
                `;
            }

            container.insertAdjacentHTML('beforeend', html);
            scrollChatToBottom();
        }

        function showTypingIndicator(label = 'Consultando información de cosecha...') {
            const ind = document.getElementById('chatTypingIndicator');
            const lbl = document.getElementById('chatTypingLabel');
            if (lbl) lbl.innerText = label;
            if (ind) ind.classList.remove('hidden');
            scrollChatToBottom();
        }

        function hideTypingIndicator() {
            const ind = document.getElementById('chatTypingIndicator');
            if (ind) ind.classList.add('hidden');
        }

        async function handleChatSubmit(e) {
            e.preventDefault();
            const input = document.getElementById('chatInput');
            const text = input.value.trim();
            if (!text) return;
            input.value = '';

            // Mostrar inmediatamente en interfaz del visitante
            appendMessageBubble('visitante', text, null, new Date().toISOString());

            showTypingIndicator(chatCurrentState === 'ia' ? 'El asistente IA está pensando...' : 'Enviando al operador...');

            try {
                const formData = new FormData();
                formData.append('action', 'send_message');
                formData.append('session_uuid', chatSessionUuid);
                formData.append('mensaje', text);

                const res = await fetch('<?= e(url("api/chat.php")) ?>', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                hideTypingIndicator();

                if (data.ok) {
                    if (data.estado && data.estado !== chatCurrentState) {
                        chatCurrentState = data.estado;
                        updateChatHeaderUI(chatCurrentState);
                    }

                    if (data.respuesta) {
                        appendMessageBubble(data.respuesta.remitente, data.respuesta.mensaje, null, data.respuesta.creado_en);
                        if (data.respuesta.id > chatLastMessageId) chatLastMessageId = data.respuesta.id;
                    }

                    // Si está esperando operador o con humano, asegurar polling
                    if (chatCurrentState !== 'ia') {
                        startChatPolling();
                    }
                }
            } catch (err) {
                hideTypingIndicator();
                appendMessageBubble('sistema', 'Hubo un detalle de red al enviar el mensaje. Tu texto quedó guardado localmente.');
            }
        }

        function showProductorChatForm() {
            const form = document.getElementById('chatProductorFormContainer');
            if (form) {
                form.classList.remove('hidden');
                document.getElementById('cpf_nombre')?.focus();
            }
        }

        function hideProductorChatForm() {
            const form = document.getElementById('chatProductorFormContainer');
            if (form) form.classList.add('hidden');
        }

        async function submitChatProductor(e) {
            e.preventDefault();
            const nombre = document.getElementById('cpf_nombre').value.trim();
            const sector = document.getElementById('cpf_sector').value.trim();
            const tel = document.getElementById('cpf_telefono').value.trim();
            const msg = document.getElementById('cpf_mensaje').value.trim();

            if (!nombre || !sector || !tel || !msg) {
                ComunalModal.alert({
                    title: 'Campos Incompletos',
                    message: 'Por favor completa todos los campos obligatorios marcados con asterisco (*).',
                    type: 'warning'
                });
                return;
            }

            hideProductorChatForm();
            const textoCompleto = `[Registro de Productor/UPF]\nNombre: ${nombre}\nSector/Comunidad: ${sector}\nTeléfono: ${tel}\nDetalle de Cosecha: ${msg}`;
            
            // Enviar como mensaje al chat
            const input = document.getElementById('chatInput');
            if (input) {
                input.value = textoCompleto;
                handleChatSubmit(new Event('submit'));
            }

            // Actualizar datos del visitante en la sesión
            try {
                const formData = new FormData();
                formData.append('action', 'update_visitor_info');
                formData.append('session_uuid', chatSessionUuid);
                formData.append('nombre', nombre);
                formData.append('telefono', tel);
                fetch('<?= e(url("api/chat.php")) ?>', { method: 'POST', body: formData });
            } catch(e) {}
        }

        function escapeHtml(string) {
            if (!string) return '';
            const div = document.createElement('div');
            div.innerText = string;
            return div.innerHTML;
        }
    </script>

    <style>
    /* Estilos para el Chat Expandido en Pantalla Amplia (Modal Espacioso) */
    .chat-modal-expanded {
        position: fixed !important;
        top: 50% !important;
        left: 50% !important;
        transform: translate(-50%, -50%) !important;
        width: 940px !important;
        max-width: calc(100vw - 2rem) !important;
        height: 86vh !important;
        max-height: 860px !important;
        margin: 0 !important;
        border-radius: 1.75rem !important;
        box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.55), 0 0 0 1px rgba(255, 255, 255, 0.1) !important;
        z-index: 55 !important;
    }
    .chat-modal-expanded #chatMessages {
        font-size: 0.85rem !important;
        padding: 1.5rem !important;
    }
    .chat-modal-expanded #chatMessages .text-xs {
        font-size: 0.825rem !important;
    }
    .chat-modal-expanded #chatInput {
        font-size: 0.85rem !important;
        padding: 0.85rem 1.25rem !important;
    }
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    </style>
</body>
</html>
