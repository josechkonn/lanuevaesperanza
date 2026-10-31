/**
 * ComunalModal - Sistema Centralizado de Modales, Confirmaciones, Alertas y Toasts
 * EPSDC La Nueva Esperanza • Diseño UI/UX Editorial y Moderno
 */
(function(window) {
    'use strict';

    // Inyectar contenedor de modales y toasts en el DOM si no existe
    function ensureContainers() {
        if (!document.getElementById('comunal-modal-root')) {
            const root = document.createElement('div');
            root.id = 'comunal-modal-root';
            root.className = 'comunal-dialog-system';
            root.innerHTML = `
                <div id="comunal-modal-backdrop" class="fixed inset-0 z-[120] bg-slate-950/60 backdrop-blur-sm hidden opacity-0 transition-opacity duration-200 flex items-center justify-center p-4">
                    <div id="comunal-modal-card" class="bg-white rounded-2xl shadow-2xl border border-slate-200/90 w-full max-w-md overflow-hidden transform scale-95 opacity-0 transition-all duration-200 flex flex-col">
                        <div class="p-6">
                            <div class="flex items-start gap-4">
                                <div id="comunal-modal-icon-wrap" class="w-12 h-12 rounded-2xl shrink-0 flex items-center justify-center text-lg shadow-xs">
                                    <i id="comunal-modal-icon" class="fa-solid fa-info"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 id="comunal-modal-title" class="text-base font-extrabold text-slate-900 tracking-tight leading-snug"></h3>
                                    <div id="comunal-modal-desc" class="text-xs sm:text-sm text-slate-600 mt-2 leading-relaxed"></div>
                                </div>
                            </div>
                        </div>
                        <div id="comunal-modal-actions" class="bg-slate-50/80 px-6 py-3.5 border-t border-slate-100 flex items-center justify-end gap-2.5">
                            <button type="button" id="comunal-modal-btn-cancel" class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition shadow-xs">Cancelar</button>
                            <button type="button" id="comunal-modal-btn-confirm" class="px-4 py-2.5 rounded-xl text-white font-bold text-xs transition shadow-sm">Aceptar</button>
                        </div>
                    </div>
                </div>
                <div id="comunal-toast-container" class="fixed top-24 right-6 z-[140] flex flex-col gap-2.5 max-w-sm pointer-events-none w-full px-4 sm:px-0"></div>
            `;
            document.body.appendChild(root);
        }
    }

    const typeThemes = {
        info: {
            iconWrap: 'bg-slate-100 text-slate-800 border border-slate-200',
            icon: 'fa-solid fa-headset',
            confirmBtn: 'bg-slate-900 hover:bg-slate-800 text-white'
        },
        success: {
            iconWrap: 'bg-emerald-50 text-emerald-700 border border-emerald-200',
            icon: 'fa-solid fa-circle-check',
            confirmBtn: 'bg-emerald-700 hover:bg-emerald-800 text-white'
        },
        warning: {
            iconWrap: 'bg-amber-50 text-amber-700 border border-amber-200',
            icon: 'fa-solid fa-triangle-exclamation',
            confirmBtn: 'bg-amber-600 hover:bg-amber-700 text-white'
        },
        danger: {
            iconWrap: 'bg-rose-50 text-rose-700 border border-rose-200',
            icon: 'fa-solid fa-circle-exclamation',
            confirmBtn: 'bg-rose-600 hover:bg-rose-700 text-white'
        }
    };

    let activeModalResolve = null;
    let keyHandler = null;

    function closeModal(result) {
        const backdrop = document.getElementById('comunal-modal-backdrop');
        const card = document.getElementById('comunal-modal-card');
        if (!backdrop || !card) return;

        card.classList.remove('scale-100', 'opacity-100');
        card.classList.add('scale-95', 'opacity-0');
        backdrop.classList.remove('opacity-100');
        backdrop.classList.add('opacity-0');

        if (keyHandler) {
            document.removeEventListener('keydown', keyHandler);
            keyHandler = null;
        }

        setTimeout(() => {
            backdrop.classList.add('hidden');
            if (activeModalResolve) {
                activeModalResolve(result);
                activeModalResolve = null;
            }
        }, 180);
    }

    function openModal({ title, message, type = 'info', icon = null, confirmText = 'Aceptar', cancelText = null }) {
        ensureContainers();
        const backdrop = document.getElementById('comunal-modal-backdrop');
        const card = document.getElementById('comunal-modal-card');
        const titleEl = document.getElementById('comunal-modal-title');
        const descEl = document.getElementById('comunal-modal-desc');
        const iconWrap = document.getElementById('comunal-modal-icon-wrap');
        const iconEl = document.getElementById('comunal-modal-icon');
        const btnConfirm = document.getElementById('comunal-modal-btn-confirm');
        const btnCancel = document.getElementById('comunal-modal-btn-cancel');

        const theme = typeThemes[type] || typeThemes.info;

        titleEl.textContent = title || 'Aviso';
        descEl.innerHTML = message || '';

        iconWrap.className = `w-12 h-12 rounded-2xl shrink-0 flex items-center justify-center text-lg shadow-xs ${theme.iconWrap}`;
        iconEl.className = icon || theme.icon;

        btnConfirm.textContent = confirmText;
        btnConfirm.className = `px-4 py-2.5 rounded-xl font-bold text-xs transition shadow-sm ${theme.confirmBtn}`;

        if (cancelText) {
            btnCancel.textContent = cancelText;
            btnCancel.classList.remove('hidden');
        } else {
            btnCancel.classList.add('hidden');
        }

        return new Promise((resolve) => {
            activeModalResolve = resolve;

            // Limpiar eventos anteriores
            const newConfirm = btnConfirm.cloneNode(true);
            btnConfirm.parentNode.replaceChild(newConfirm, btnConfirm);
            newConfirm.addEventListener('click', () => closeModal(true));

            const newCancel = btnCancel.cloneNode(true);
            btnCancel.parentNode.replaceChild(newCancel, btnCancel);
            newCancel.addEventListener('click', () => closeModal(false));

            backdrop.onclick = (e) => {
                if (e.target === backdrop) closeModal(false);
            };

            keyHandler = (e) => {
                if (e.key === 'Escape') closeModal(false);
            };
            document.addEventListener('keydown', keyHandler);

            // Mostrar modal con animación suave
            backdrop.classList.remove('hidden');
            void backdrop.offsetWidth; // Forzar reflow
            backdrop.classList.remove('opacity-0');
            backdrop.classList.add('opacity-100');
            card.classList.remove('scale-95', 'opacity-0');
            card.classList.add('scale-100', 'opacity-100');

            setTimeout(() => newConfirm.focus(), 50);
        });
    }

    const ComunalModal = {
        /**
         * Diálogo de confirmación (reemplaza confirm() nativo)
         */
        confirm(options) {
            const opts = typeof options === 'string' ? { message: options } : options;
            return openModal({
                title: opts.title || 'Confirmación Requerida',
                message: opts.message,
                type: opts.type || 'info',
                icon: opts.icon,
                confirmText: opts.confirmText || 'Confirmar',
                cancelText: opts.cancelText || 'Cancelar'
            });
        },

        /**
         * Diálogo de aviso / alerta (reemplaza alert() nativo)
         */
        alert(options) {
            const opts = typeof options === 'string' ? { message: options } : options;
            return openModal({
                title: opts.title || 'Aviso',
                message: opts.message,
                type: opts.type || 'info',
                icon: opts.icon,
                confirmText: opts.btnText || opts.confirmText || 'Entendido',
                cancelText: null
            });
        },

        /**
         * Diálogo de éxito
         */
        success(options) {
            const opts = typeof options === 'string' ? { message: options } : options;
            return openModal({
                title: opts.title || 'Operación Exitosa',
                message: opts.message,
                type: 'success',
                icon: opts.icon || 'fa-solid fa-circle-check',
                confirmText: opts.btnText || 'Aceptar',
                cancelText: null
            });
        },

        /**
         * Notificación Toast no intrusiva
         */
        toast({ message, title = null, type = 'info', duration = 4000, icon = null }) {
            ensureContainers();
            const container = document.getElementById('comunal-toast-container');
            const theme = typeThemes[type] || typeThemes.info;

            const toast = document.createElement('div');
            toast.className = 'pointer-events-auto bg-slate-900/95 backdrop-blur-md text-white rounded-2xl px-4 py-3 shadow-2xl border border-slate-700/80 flex items-center gap-3 transition-all duration-300 transform';
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-12px)';

            const defaultIcons = {
                info: 'fa-solid fa-circle-info text-cyan-400',
                success: 'fa-solid fa-circle-check text-emerald-400',
                warning: 'fa-solid fa-triangle-exclamation text-amber-400',
                danger: 'fa-solid fa-circle-exclamation text-rose-400'
            };

            const iconClass = icon || defaultIcons[type] || defaultIcons.info;

            toast.innerHTML = `
                <div class="text-base shrink-0"><i class="${iconClass}"></i></div>
                <div class="flex-1 min-w-0">
                    ${title ? `<div class="text-xs font-bold text-white tracking-tight leading-tight">${title}</div>` : ''}
                    <div class="text-[11px] sm:text-xs text-slate-200 leading-snug mt-0.5">${message}</div>
                </div>
                <button type="button" class="text-slate-400 hover:text-white text-xs shrink-0 p-1 cursor-pointer" onclick="this.parentElement.remove()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;

            container.appendChild(toast);
            void toast.offsetWidth;
            
            // Animación suave de entrada
            setTimeout(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translateY(0)';
            }, 30);

            if (duration > 0) {
                setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateY(-12px)';
                    setTimeout(() => toast.remove(), 320);
                }, duration);
            }
        }
    };

    window.ComunalModal = ComunalModal;

    // Sobreescritura segura opcional o fallback cuando esté listo el DOM
    document.addEventListener('DOMContentLoaded', ensureContainers);

})(window);
