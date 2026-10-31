/**
 * Comportamiento común del panel y Sistema Centralizado de Diálogos (Alertas y Modales).
 * EPSDC La Nueva Esperanza • Estilo Alpine Emerald & UX Moderna
 */
(function () {
    'use strict';

    // =========================================================================
    // Sistema Centralizado de Diálogos (Modales de Confirmación y Toasts)
    // =========================================================================
    function ensureDialogDOM() {
        if (!document.getElementById('adminCentralDialogModal')) {
            var modalHTML = `
                <div class="modal fade" id="adminCentralDialogModal" tabindex="-1" aria-hidden="true" style="z-index: 1080;">
                    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
                        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden; background: #ffffff;">
                            <div class="modal-body p-4 text-center">
                                <div id="adminDialogIconWrap" class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3 shadow-xs" style="width: 60px; height: 60px; font-size: 1.6rem;">
                                    <i id="adminDialogIcon" class="bi bi-info-circle-fill"></i>
                                </div>
                                <h4 id="adminDialogTitle" class="h6 fw-bold text-dark mb-2 fs-5" style="letter-spacing: -0.02em;"></h4>
                                <p id="adminDialogMessage" class="text-secondary small mb-4 px-2" style="line-height: 1.55;"></p>
                                <div class="d-flex align-items-center justify-content-center gap-2 pt-1">
                                    <button type="button" id="adminDialogBtnCancel" class="btn btn-light border px-3.5 py-2 fw-semibold rounded-pill text-sm" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="button" id="adminDialogBtnConfirm" class="btn btn-primary px-4 py-2 fw-semibold rounded-pill text-sm shadow-xs">Aceptar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="adminToastContainer" style="position: fixed; top: 1.25rem; right: 1.25rem; z-index: 1090; display: flex; flex-direction: column; gap: 0.6rem; pointer-events: none; max-width: 380px; width: 100%;"></div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHTML);
        }
    }

    var dialogResolve = null;

    function showDialog(opts) {
        ensureDialogDOM();
        var modalEl = document.getElementById('adminCentralDialogModal');
        var bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);

        var titleEl = document.getElementById('adminDialogTitle');
        var msgEl = document.getElementById('adminDialogMessage');
        var iconWrap = document.getElementById('adminDialogIconWrap');
        var iconEl = document.getElementById('adminDialogIcon');
        var btnConfirm = document.getElementById('adminDialogBtnConfirm');
        var btnCancel = document.getElementById('adminDialogBtnCancel');

        titleEl.textContent = opts.title || 'Aviso';
        msgEl.textContent = opts.message || '';

        // Estilos según el tipo
        var type = opts.type || 'info';
        if (type === 'danger') {
            iconWrap.style.background = '#fef2f2';
            iconWrap.style.color = '#dc2626';
            iconWrap.style.border = '1px solid #fee2e2';
            iconEl.className = opts.icon || 'bi bi-trash-fill';
            btnConfirm.className = 'btn btn-danger px-4 py-2 fw-semibold rounded-pill text-sm shadow-xs';
        } else if (type === 'warning') {
            iconWrap.style.background = '#fffbeb';
            iconWrap.style.color = '#d97706';
            iconWrap.style.border = '1px solid #fef3c7';
            iconEl.className = opts.icon || 'bi bi-exclamation-triangle-fill';
            btnConfirm.className = 'btn btn-warning text-dark px-4 py-2 fw-semibold rounded-pill text-sm shadow-xs';
        } else if (type === 'success') {
            iconWrap.style.background = '#ecfdf5';
            iconWrap.style.color = '#059669';
            iconWrap.style.border = '1px solid #a7f3d0';
            iconEl.className = opts.icon || 'bi bi-check-circle-fill';
            btnConfirm.className = 'btn btn-success px-4 py-2 fw-semibold rounded-pill text-sm shadow-xs';
        } else {
            iconWrap.style.background = '#eff6ff';
            iconWrap.style.color = '#2563eb';
            iconWrap.style.border = '1px solid #dbeafe';
            iconEl.className = opts.icon || 'bi bi-info-circle-fill';
            btnConfirm.className = 'btn btn-primary px-4 py-2 fw-semibold rounded-pill text-sm shadow-xs';
        }

        btnConfirm.textContent = opts.confirmText || 'Aceptar';

        if (opts.cancelText) {
            btnCancel.textContent = opts.cancelText;
            btnCancel.classList.remove('d-none');
        } else {
            btnCancel.classList.add('d-none');
        }

        return new Promise(function (resolve) {
            dialogResolve = resolve;

            var onConfirm = function () {
                bsModal.hide();
                if (dialogResolve) { dialogResolve(true); dialogResolve = null; }
                cleanup();
            };

            var onHidden = function () {
                if (dialogResolve) { dialogResolve(false); dialogResolve = null; }
                cleanup();
            };

            function cleanup() {
                btnConfirm.removeEventListener('click', onConfirm);
                modalEl.removeEventListener('hidden.bs.modal', onHidden);
            }

            btnConfirm.addEventListener('click', onConfirm);
            modalEl.addEventListener('hidden.bs.modal', onHidden, { once: true });

            bsModal.show();
        });
    }

    function showToast(message, type) {
        ensureDialogDOM();
        var container = document.getElementById('adminToastContainer');
        var toast = document.createElement('div');
        toast.className = 'toast-notification shadow-lg border d-flex align-items-center gap-2.5 p-3 rounded-4';
        toast.style.pointerEvents = 'auto';
        toast.style.background = '#ffffff';
        toast.style.fontSize = '0.85rem';
        toast.style.fontWeight = '500';
        toast.style.transition = 'all 0.25s ease';
        toast.style.transform = 'translateY(-10px)';
        toast.style.opacity = '0';

        var icon = 'bi-info-circle-fill text-primary';
        var borderCol = '#e2e8f0';
        if (type === 'success') {
            icon = 'bi-check-circle-fill text-success';
            borderCol = '#a7f3d0';
        } else if (type === 'danger') {
            icon = 'bi-exclamation-triangle-fill text-danger';
            borderCol = '#fecdd3';
        } else if (type === 'warning') {
            icon = 'bi-exclamation-circle-fill text-warning';
            borderCol = '#fde68a';
        }
        toast.style.borderColor = borderCol;

        toast.innerHTML = `
            <i class="bi ${icon} fs-5 flex-shrink-0"></i>
            <div class="flex-grow-1 text-slate-800" style="line-height: 1.4;">${message}</div>
            <button type="button" class="btn-close shadow-none flex-shrink-0" style="font-size: 0.7rem;"></button>
        `;

        container.appendChild(toast);

        toast.querySelector('.btn-close').addEventListener('click', function () {
            removeToast(toast);
        });

        requestAnimationFrame(function () {
            toast.style.transform = 'translateY(0)';
            toast.style.opacity = '1';
        });

        setTimeout(function () {
            removeToast(toast);
        }, 4000);
    }

    function removeToast(el) {
        if (!el || !el.parentNode) return;
        el.style.opacity = '0';
        el.style.transform = 'translateY(-10px)';
        setTimeout(function () {
            if (el.parentNode) el.parentNode.removeChild(el);
        }, 250);
    }

    // Exponer API global
    window.AdminDialog = {
        confirm: function (opts) {
            var options = typeof opts === 'string' ? { message: opts } : (opts || {});
            return showDialog({
                title: options.title || '¿Confirmar acción?',
                message: options.message || '',
                type: options.type || 'warning',
                confirmText: options.confirmText || 'Confirmar',
                cancelText: options.cancelText || 'Cancelar',
                icon: options.icon
            });
        },
        alert: function (opts) {
            var options = typeof opts === 'string' ? { message: opts } : (opts || {});
            return showDialog({
                title: options.title || 'Aviso del Sistema',
                message: options.message || '',
                type: options.type || 'info',
                confirmText: options.confirmText || 'Entendido',
                cancelText: null,
                icon: options.icon
            });
        },
        toast: showToast
    };

    // =========================================================================
    // Manejo de Formularios y Modales Estándar
    // =========================================================================
    function fillForm(form, data) {
        form.reset();
        form.querySelectorAll('[data-preview]').forEach(function (el) {
            el.innerHTML = '';
        });
        Object.keys(data || {}).forEach(function (k) {
            var fields = form.querySelectorAll('[name="' + k + '"]');
            fields.forEach(function (el) {
                if (el.type === 'file') return;
                if (el.type === 'checkbox') {
                    el.checked = String(data[k]) === '1' || data[k] === true;
                } else if (el.type === 'radio') {
                    el.checked = String(el.value) === String(data[k]);
                } else {
                    el.value = data[k] === null ? '' : data[k];
                }
            });
            var prev = form.querySelector('[data-preview="' + k + '"]');
            if (prev && data[k]) {
                var src = (window.LNE_BASE || '') + '/' + data[k];
                prev.innerHTML = /\.pdf$/i.test(data[k])
                    ? '<a href="' + src + '" target="_blank" class="small"><i class="bi bi-file-earmark-pdf"></i> Ver PDF actual</a>'
                    : '<img src="' + src + '" class="thumb mt-1" alt="">';
            }
        });
    }

    document.addEventListener('click', function (ev) {
        var btn = ev.target.closest('[data-modal-new],[data-modal-edit]');
        if (!btn) return;
        var sel = btn.getAttribute('data-modal-new') || btn.getAttribute('data-modal-edit');
        var modalEl = document.querySelector(sel);
        if (!modalEl) return;
        var form = modalEl.querySelector('form');
        var data = {};
        if (btn.hasAttribute('data-modal-edit')) {
            try { data = JSON.parse(btn.getAttribute('data-row') || '{}'); } catch (e) { data = {}; }
        }
        var defaults = {};
        try { defaults = JSON.parse(form.getAttribute('data-defaults') || '{}'); } catch (e) {}
        fillForm(form, Object.assign({}, defaults, data));
        if (!data.id) { var idf = form.querySelector('[name="id"]'); if (idf) idf.value = ''; }
        var t = modalEl.querySelector('.modal-title');
        if (t && btn.dataset.title) t.textContent = btn.dataset.title;
        modalEl.querySelectorAll('[data-solo-nuevo]').forEach(function (el) { el.classList.toggle('d-none', !!data.id); });
        modalEl.querySelectorAll('[data-solo-editar]').forEach(function (el) { el.classList.toggle('d-none', !data.id); });
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    });

    // Interceptar data-confirm con el diálogo centralizado institucional
    document.addEventListener('submit', function (ev) {
        var f = ev.target;
        var msg = f.getAttribute('data-confirm');
        if (msg && !f.dataset.userConfirmed) {
            ev.preventDefault();
            var isDelete = msg.toLowerCase().includes('eliminar') || msg.toLowerCase().includes('borrar');
            AdminDialog.confirm({
                title: isDelete ? '¿Eliminar registro?' : '¿Confirmar acción?',
                message: msg,
                type: isDelete ? 'danger' : 'warning',
                confirmText: isDelete ? 'Sí, eliminar' : 'Sí, continuar',
                cancelText: 'Cancelar'
            }).then(function (confirmed) {
                if (confirmed) {
                    f.dataset.userConfirmed = 'true';
                    if (typeof f.requestSubmit === 'function') {
                        f.requestSubmit();
                    } else {
                        f.submit();
                    }
                }
            });
            return;
        }

        var b = f.querySelector('button[type="submit"],button:not([type])');
        if (b && f.method.toLowerCase() === 'post') {
            setTimeout(function () {
                b.disabled = true;
                b.insertAdjacentHTML('afterbegin', '<span class="spinner-border spinner-border-sm me-1"></span>');
            }, 0);
        }
    });

    // Auto-ocultar alertas de éxito
    setTimeout(function () {
        document.querySelectorAll('.alert-success.alert-dismissible').forEach(function (a) {
            bootstrap.Alert.getOrCreateInstance(a).close();
        });
    }, 5000);
})();
