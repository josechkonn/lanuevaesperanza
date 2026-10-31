/**
 * Comportamiento común del panel.
 *
 * Convención de diálogos (una sola forma para todo el panel):
 *  - <button data-modal-new="#modalId" data-title="Nuevo X">   -> limpia el form y abre el modal
 *  - <button data-modal-edit="#modalId" data-title="Editar X" data-row='{"id":1,...}'>
 *        -> rellena cada campo cuyo name coincida con una clave del JSON
 *  - <form data-confirm="¿Seguro?">  -> pide confirmación antes de enviar
 */
(function () {
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

    document.addEventListener('submit', function (ev) {
        var f = ev.target;
        var msg = f.getAttribute('data-confirm');
        if (msg && !confirm(msg)) { ev.preventDefault(); return; }
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
