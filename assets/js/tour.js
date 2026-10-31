/**
 * Motor de Guía Interactiva On-Page y Asistente Visual.
 * Mueve la pantalla suavemente, resalta con spotlight el elemento activo
 * y muestra explicaciones claras paso a paso.
 * EPSDC La Nueva Esperanza
 */
(function(window) {
    'use strict';

    let pasosActuales = [];
    let indiceActual = -1;
    let elementoActivo = null;

    function ensureTourDOM() {
        if (!document.getElementById('tourBackdropOverlay')) {
            const backdrop = document.createElement('div');
            backdrop.id = 'tourBackdropOverlay';
            backdrop.className = 'd-none';
            backdrop.onclick = function() {
                TourGuia.cerrar();
            };
            document.body.appendChild(backdrop);
        }

        if (!document.getElementById('tourGuiaCard')) {
            const card = document.createElement('div');
            card.id = 'tourGuiaCard';
            card.className = 'tour-guia-popover d-none';
            card.innerHTML = `
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace" id="tourStepBadge" style="font-size: 0.72rem;">Paso 1 de 4</span>
                        <h6 class="fw-bold text-slate-900 mb-0" id="tourStepTitle" style="font-size: 0.95rem; letter-spacing: -0.01em;"></h6>
                    </div>
                    <button type="button" class="btn-close shadow-none" onclick="TourGuia.cerrar()" aria-label="Cerrar" style="font-size: 0.75rem;"></button>
                </div>
                <div class="text-slate-600 mb-3" id="tourStepContent" style="font-size: 0.85rem; line-height: 1.5;"></div>
                <div class="bg-emerald-50 border border-emerald-200/80 rounded-3 p-2.5 mb-3 d-flex align-items-start gap-2 text-emerald-950" id="tourStepTipWrap" style="font-size: 0.76rem;">
                    <i class="bi bi-lightbulb-fill text-emerald-600 fs-6 shrink-0 mt-0.5"></i>
                    <span id="tourStepTip" style="line-height: 1.45;"></span>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-2.5 border-top border-slate-100">
                    <button type="button" class="btn btn-xs btn-light border rounded-pill px-3 py-1.5 fw-semibold text-slate-700" id="tourBtnPrev" onclick="TourGuia.anterior()">
                        <i class="bi bi-chevron-left me-1"></i> Anterior
                    </button>
                    <div class="d-flex gap-1 align-items-center" id="tourDotsContainer"></div>
                    <button type="button" class="btn btn-xs btn-success rounded-pill px-3.5 py-1.5 fw-semibold shadow-xs" id="tourBtnNext" onclick="TourGuia.siguiente()">
                        Siguiente <i class="bi bi-chevron-right ms-1"></i>
                    </button>
                </div>
            `;
            document.body.appendChild(card);
        }
    }

    function posicionarPopover(targetEl) {
        const popover = document.getElementById('tourGuiaCard');
        if (!popover || !targetEl) return;

        const rect = targetEl.getBoundingClientRect();
        const popWidth = popover.offsetWidth || 400;
        const popHeight = popover.offsetHeight || 260;

        let top = rect.bottom + 14;
        let left = rect.left + (rect.width / 2) - (popWidth / 2);

        // Si se sale de abajo, ubicarlo encima
        if (top + popHeight > window.innerHeight - 15) {
            top = Math.max(15, rect.top - popHeight - 14);
        }

        // Si se sale lateralmente
        if (left < 15) left = 15;
        if (left + popWidth > window.innerWidth - 15) {
            left = window.innerWidth - popWidth - 15;
        }

        popover.style.top = top + 'px';
        popover.style.left = left + 'px';
    }

    const TourGuia = {
        init: function(config) {
            pasosActuales = config.pasos || [];
            ensureTourDOM();

            // Atajo de teclado: ESC cierra la guía
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    TourGuia.cerrar();
                }
            });

            // Reposicionar en resize o scroll
            window.addEventListener('resize', function() {
                if (elementoActivo) posicionarPopover(elementoActivo);
            });
        },

        iniciar: function() {
            if (!pasosActuales.length) {
                if (window.AdminDialog) {
                    AdminDialog.toast('No hay guía configurada para este apartado.', 'info');
                }
                return;
            }
            this.mostrarPaso(0);
        },

        mostrarPaso: function(identificador) {
            ensureTourDOM();
            if (!pasosActuales.length) return;

            let idx = -1;
            if (typeof identificador === 'number') {
                idx = identificador;
            } else {
                idx = pasosActuales.findIndex(p => p.id === identificador);
            }

            if (idx < 0 || idx >= pasosActuales.length) {
                idx = 0;
            }

            indiceActual = idx;
            const paso = pasosActuales[idx];

            // Limpiar elemento activo previo
            if (elementoActivo) {
                elementoActivo.classList.remove('tour-spotlight-active');
                elementoActivo = null;
            }

            // Buscar elemento objetivo
            let target = null;
            if (paso.selector) {
                target = document.querySelector(paso.selector);
            }

            const backdrop = document.getElementById('tourBackdropOverlay');
            const popover = document.getElementById('tourGuiaCard');

            if (backdrop) backdrop.classList.remove('d-none');
            if (popover) popover.classList.remove('d-none');

            // Actualizar contenidos
            document.getElementById('tourStepBadge').textContent = `Paso ${idx + 1} de ${pasosActuales.length}`;
            document.getElementById('tourStepTitle').textContent = paso.titulo || 'Guía';
            document.getElementById('tourStepContent').innerHTML = paso.texto || '';

            const tipWrap = document.getElementById('tourStepTipWrap');
            const tipEl = document.getElementById('tourStepTip');
            if (paso.tip) {
                tipEl.innerHTML = paso.tip;
                tipWrap.classList.remove('d-none');
            } else {
                tipWrap.classList.add('d-none');
            }

            // Botones de navegación
            const btnPrev = document.getElementById('tourBtnPrev');
            if (idx === 0) {
                btnPrev.classList.add('invisible');
            } else {
                btnPrev.classList.remove('invisible');
            }

            const btnNext = document.getElementById('tourBtnNext');
            if (idx === pasosActuales.length - 1) {
                btnNext.innerHTML = '¡Entendido! <i class="bi bi-check2 ms-1"></i>';
                btnNext.className = 'btn btn-xs btn-success rounded-pill px-3.5 py-1.5 fw-semibold shadow-xs';
            } else {
                btnNext.innerHTML = 'Siguiente <i class="bi bi-chevron-right ms-1"></i>';
                btnNext.className = 'btn btn-xs btn-primary rounded-pill px-3.5 py-1.5 fw-semibold shadow-xs';
            }

            // Puntos de avance
            const dotsContainer = document.getElementById('tourDotsContainer');
            if (dotsContainer) {
                dotsContainer.innerHTML = '';
                pasosActuales.forEach((_, pIdx) => {
                    const dot = document.createElement('span');
                    dot.className = 'tour-dot-pill' + (pIdx === idx ? ' active' : '');
                    dot.onclick = () => TourGuia.mostrarPaso(pIdx);
                    dot.style.cursor = 'pointer';
                    dotsContainer.appendChild(dot);
                });
            }

            // Foco y desplazamiento suave
            if (target) {
                elementoActivo = target;
                target.classList.add('tour-spotlight-active');
                target.scrollIntoView({ behavior: 'smooth', block: 'center' });

                setTimeout(() => {
                    posicionarPopover(target);
                }, 300);
            } else {
                // Fallback centrado
                popover.style.top = '50%';
                popover.style.left = '50%';
                popover.style.transform = 'translate(-50%, -50%)';
            }
        },

        siguiente: function() {
            if (indiceActual < pasosActuales.length - 1) {
                this.mostrarPaso(indiceActual + 1);
            } else {
                this.cerrar();
                if (window.AdminDialog) {
                    AdminDialog.toast('¡Recorrido de la página completado con éxito!', 'success');
                }
            }
        },

        anterior: function() {
            if (indiceActual > 0) {
                this.mostrarPaso(indiceActual - 1);
            }
        },

        cerrar: function() {
            if (elementoActivo) {
                elementoActivo.classList.remove('tour-spotlight-active');
                elementoActivo = null;
            }
            const backdrop = document.getElementById('tourBackdropOverlay');
            const popover = document.getElementById('tourGuiaCard');
            if (backdrop) backdrop.classList.add('d-none');
            if (popover) popover.classList.add('d-none');
            indiceActual = -1;
        }
    };

    window.TourGuia = TourGuia;
})(window);
