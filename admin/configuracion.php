<?php
require __DIR__ . '/../includes/app.php';
requiere_rol('superadmin');
$pdo = db();

$campos = [
    'nombre_organizacion' => ['Nombre de la organización', 'text', 'col-md-6', 'bi-building text-emerald-600', 'Nombre oficial de la EPSDC que se visualiza en encabezados, reportes y comunicados públicos.'],
    'lema'                => ['Lema / eslogan', 'text', 'col-md-6', 'bi-quote text-emerald-600', 'Eslogan de soberanía agroalimentaria comunal.'],
    'telefono'            => ['Teléfono / Móvil institucional (Contacto)', 'text', 'col-md-6', 'bi-whatsapp text-success', 'Número móvil o WhatsApp oficial (+58...). Se muestra en el pie de página, contacto directo y es utilizado por el Asistente IA.'],
    'email_contacto'      => ['Correo electrónico de contacto', 'email', 'col-md-6', 'bi-envelope-at text-primary', 'Correo institucional para recepción de mensajes, trámites y coordinación.'],
    'direccion'           => ['Dirección física y sede comunal', 'textarea', 'col-12', 'bi-geo-alt text-danger', 'Dirección física completa de la sede central y centro de acopio comunal. Se muestra en el pie de página del portal y es utilizada por el Asistente IA para responder consultas de ubicación y georreferenciación.'],
    'moneda_defecto'      => ['Moneda por defecto', 'select', 'col-md-6', 'bi-coin text-amber-600', 'Moneda contable y de fijación de precios en el catálogo de rubros y combos.'],
];

$camposIA = [
    'ai_provider' => ['Proveedor de IA', 'select_ai'],
    'ai_api_key'  => ['API Key de la IA (DeepSeek / OpenRouter / Gemini)', 'password'],
    'ai_model'    => ['Nombre del Modelo', 'text'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $logo = null;
    try {
        $st = $pdo->prepare('INSERT INTO configuracion (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)');
        foreach ($campos as $k => $_) {
            $v = mb_substr(post($k), 0, 500);
            if ($k === 'moneda_defecto' && !in_array($v, ['USD', 'VES', 'EUR', 'COP'], true)) $v = 'USD';
            if ($k === 'email_contacto' && $v !== '' && !filter_var($v, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Correo de contacto inválido.');
            $st->execute([$k, $v]);
        }
        foreach ($camposIA as $k => $_) {
            $v = mb_substr(post($k), 0, 500);
            if ($k === 'ai_provider' && !in_array($v, ['deepseek', 'openrouter', 'gemini', 'local'], true)) $v = 'deepseek';
            $st->execute([$k, $v]);
        }
        [$logo, $err] = subir_archivo('logo', 'imagen', 'imagenes');
        if ($err) throw new RuntimeException($err);
        $actual = config_get('logo');
        if ($logo || isset($_POST['quitar_logo'])) {
            $st->execute(['logo', $logo ?? '']);
            borrar_archivo($actual ?: null);
        }
        auditar('editar', 'configuracion', null, 'Configuración general e IA');
        flash('success', 'Configuración institucional guardada exitosamente.');
    } catch (RuntimeException $ex) {
        if ($logo) borrar_archivo($logo);
        flash('danger', $ex->getMessage());
    }
    redirect(url('admin/configuracion.php'));
}

$titulo = 'Configuración';
$seccion = 'configuracion';
require __DIR__ . '/layout/header.php';

$provActual = config_get('ai_provider', 'openrouter');
$modActual = config_get('ai_model', 'deepseek/deepseek-r1:free');
?>

<div class="container-fluid p-0" style="max-width: 960px;">
    
    <div class="mb-4">
        <h1 class="h4 fw-bold text-slate-800 mb-1">Ajustes y Configuración del Sistema</h1>
        <p class="text-slate-500 small mb-0">Gestione la identidad institucional de la EPSDC, datos de contacto comunal y el motor de Inteligencia Artificial.</p>
    </div>

    <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <!-- TARJETA 1: IDENTIDAD Y CONTACTO INSTITUCIONAL -->
        <div class="card shadow-sm border mb-4" style="border-color: #e2e8f0; border-radius: 18px; overflow: hidden; background: #ffffff;">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center gap-2.5">
                <div class="d-flex align-items-center justify-content-center rounded-circle" style="width: 36px; height: 36px; background: #ecfdf5; color: #059669;">
                    <i class="bi bi-building-gear fs-5"></i>
                </div>
                <div>
                    <h2 class="h6 fw-bold mb-0 text-slate-800">Identidad y Contacto Institucional</h2>
                    <small class="text-slate-500">Datos públicos de la EPSDC reflejados en el portal, pie de página y consultas ciudadanas.</small>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="row g-3.5">
                    <?php foreach ($campos as $k => [$lbl, $tipo, $colClass, $icono, $ayuda]): $v = config_get($k); ?>
                        <div class="<?= e($colClass) ?>">
                            <label class="form-label fw-semibold text-slate-800 d-flex align-items-center gap-1.5 mb-1.5" style="font-size: 0.85rem;">
                                <i class="bi <?= $icono ?>"></i> <?= e($lbl) ?>
                            </label>
                            
                            <?php if ($tipo === 'select'): ?>
                                <select name="<?= $k ?>" class="form-select shadow-none" style="border-color: #e2e8f0; border-radius: 10px;">
                                    <?php foreach (['USD', 'VES', 'EUR', 'COP'] as $m): ?>
                                        <option<?= $v === $m ? ' selected' : '' ?>><?= $m ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php elseif ($tipo === 'textarea'): ?>
                                <textarea name="<?= $k ?>" class="form-control shadow-none" rows="3" maxlength="500" style="border-color: #e2e8f0; border-radius: 10px; font-size: 0.875rem;" placeholder="Ej: Calle Principal, Edificio Comunitario, Piso 1, Aldea Quebrada de San José, Ciudad de La Grita, Municipio Jáuregui, Estado Táchira"><?= e($v) ?></textarea>
                            <?php else: ?>
                                <input type="<?= $tipo ?>" name="<?= $k ?>" value="<?= e($v) ?>" class="form-control shadow-none" maxlength="500" style="border-color: #e2e8f0; border-radius: 10px; font-size: 0.875rem;" placeholder="<?= $k === 'telefono' ? '+58 412 1234567' : '' ?>">
                            <?php endif; ?>

                            <?php if (!empty($ayuda)): ?>
                                <div class="form-text small text-slate-400 mt-1" style="font-size: 0.74rem; line-height: 1.4;"><?= e($ayuda) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <!-- Logo Institucional -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-slate-800 d-flex align-items-center gap-1.5 mb-1.5" style="font-size: 0.85rem;">
                            <i class="bi bi-image text-indigo-600"></i> Logo Institucional
                        </label>
                        <input type="file" name="logo" accept="image/*" class="form-control shadow-none" style="border-color: #e2e8f0; border-radius: 10px; font-size: 0.85rem;">
                        <div class="form-text small text-slate-400 mt-1" style="font-size: 0.74rem;">Formatos admitidos: PNG, JPG o WEBP con fondo transparente preferiblemente.</div>
                        
                        <?php if ($l = config_get('logo')): ?>
                            <div class="d-flex align-items-center gap-3 mt-2.5 p-2 bg-slate-50 border rounded-3" style="border-color: #e2e8f0;">
                                <img src="<?= e(url($l)) ?>" class="rounded" style="max-height: 48px; object-fit: contain;" alt="Logo Actual">
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" name="quitar_logo" id="qLogo">
                                    <label class="form-check-label small text-danger fw-semibold" for="qLogo">Eliminar logo actual</label>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- TARJETA 2: ASISTENTE VIRTUAL IA & MOTOR DE CONECTIVIDAD -->
        <div class="card shadow-sm border mb-4" style="border-color: #e2e8f0; border-radius: 18px; overflow: hidden; background: #ffffff;">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="d-flex align-items-center justify-content-center rounded-circle" style="width: 36px; height: 36px; background: #eff6ff; color: #2563eb;">
                        <i class="bi bi-robot fs-5"></i>
                    </div>
                    <div>
                        <h2 class="h6 fw-bold mb-0 text-slate-800">Asistente Virtual Comunal con Inteligencia Artificial</h2>
                        <small class="text-slate-500">Parámetros de conexión con OpenRouter, DeepSeek o motor local para el chat público.</small>
                    </div>
                </div>

                <!-- Estado del motor en vivo -->
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 small d-inline-flex align-items-center gap-1.5">
                    <span class="p-1 bg-success rounded-circle"></span> Motor Operativo
                </span>
            </div>

            <div class="card-body p-4">
                <div class="row g-3.5">
                    <!-- Proveedor de IA -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-slate-800 d-flex align-items-center gap-1.5 mb-1.5" style="font-size: 0.85rem;">
                            <i class="bi bi-cpu text-secondary"></i> Proveedor de IA
                        </label>
                        <select name="ai_provider" id="aiProviderSelect" class="form-select shadow-none" style="border-color: #e2e8f0; border-radius: 10px;" onchange="actualizarSugerenciasModelos(this.value)">
                            <option value="openrouter"<?= $provActual === 'openrouter' ? ' selected' : '' ?>>OpenRouter (Modelos gratuitos / DeepSeek)</option>
                            <option value="deepseek"<?= $provActual === 'deepseek' ? ' selected' : '' ?>>DeepSeek Oficial (api.deepseek.com)</option>
                            <option value="gemini"<?= $provActual === 'gemini' ? ' selected' : '' ?>>Google Gemini (Flash API)</option>
                            <option value="local"<?= $provActual === 'local' ? ' selected' : '' ?>>Motor Local Inteligente (Sin API Key)</option>
                        </select>
                        <div class="form-text small text-slate-400 mt-1" style="font-size: 0.74rem;">Si no dispone de API Key activa, seleccione el Motor Local.</div>
                    </div>

                    <!-- API Key con Toggle Ver/Ocultar -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-slate-800 d-flex align-items-center gap-1.5 mb-1.5" style="font-size: 0.85rem;">
                            <i class="bi bi-key-fill text-secondary"></i> Clave API (API Key)
                        </label>
                        <div class="input-group">
                            <input type="password" id="aiApiKeyInput" name="ai_api_key" value="<?= e(config_get('ai_api_key', '')) ?>" class="form-control shadow-none border-end-0" style="border-color: #e2e8f0; border-radius: 10px 0 0 10px; font-size: 0.875rem;" placeholder="sk-or-v1-..." autocomplete="off">
                            <button type="button" class="btn btn-light border border-start-0 text-slate-500" style="border-color: #e2e8f0; border-radius: 0 10px 10px 0;" onclick="toggleApiKeyVisibility()" title="Mostrar u ocultar clave">
                                <i id="aiApiKeyToggleIcon" class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="form-text small text-slate-400 mt-1" style="font-size: 0.74rem;">Su clave confidencial se almacena encriptada en la base de datos.</div>
                    </div>

                    <!-- Modelo de IA -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-slate-800 d-flex align-items-center gap-1.5 mb-1.5" style="font-size: 0.85rem;">
                            <i class="bi bi-box text-secondary"></i> Modelo de IA
                        </label>
                        <input type="text" id="aiModelInput" name="ai_model" value="<?= e($modActual) ?>" class="form-control shadow-none" style="border-color: #e2e8f0; border-radius: 10px; font-size: 0.875rem;" placeholder="deepseek/deepseek-r1:free">
                        <div class="form-text small text-slate-400 mt-1" style="font-size: 0.74rem;">Identificador exacto del modelo admitido por el proveedor.</div>
                    </div>

                    <!-- Chips de Sugerencias Rápidas -->
                    <div class="col-12 pt-2 border-top mt-3" style="border-color: #f1f5f9 !important;">
                        <div class="small fw-semibold text-slate-600 mb-2 d-flex align-items-center gap-1.5">
                            <i class="bi bi-lightning-charge-fill text-amber-500"></i> Modelos recomendados (haz clic para auto-completar):
                        </div>
                        <div class="d-flex flex-wrap gap-2" id="modelChipsContainer">
                            <button type="button" onclick="setModelInput('deepseek/deepseek-r1:free', 'openrouter')" class="btn btn-xs btn-outline-secondary rounded-pill py-1 px-2.5">
                                <i class="bi bi-stars text-emerald-600 me-1"></i>deepseek/deepseek-r1:free (OpenRouter)
                            </button>
                            <button type="button" onclick="setModelInput('nvidia/nemotron-3-ultra-550', 'openrouter')" class="btn btn-xs btn-outline-secondary rounded-pill py-1 px-2.5">
                                <i class="bi bi-stars text-emerald-600 me-1"></i>nvidia/nemotron-3-ultra-550 (OpenRouter)
                            </button>
                            <button type="button" onclick="setModelInput('deepseek-chat', 'deepseek')" class="btn btn-xs btn-outline-secondary rounded-pill py-1 px-2.5">
                                <i class="bi bi-robot text-primary me-1"></i>deepseek-chat (DeepSeek V3)
                            </button>
                            <button type="button" onclick="setModelInput('gemini-1.5-flash', 'gemini')" class="btn btn-xs btn-outline-secondary rounded-pill py-1 px-2.5">
                                <i class="bi bi-google text-danger me-1"></i>gemini-1.5-flash (Google)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BARRA INFERIOR DE ACCIONES -->
        <div class="d-flex align-items-center justify-content-between p-3 px-4 bg-white border rounded-4 shadow-sm mb-5" style="border-color: #e2e8f0;">
            <div class="small text-slate-500 d-flex align-items-center gap-2">
                <i class="bi bi-shield-check text-emerald-600 fs-5"></i>
                <span>Los cambios se aplicarán inmediatamente en toda la plataforma comunal.</span>
            </div>
            <button type="submit" class="btn btn-success px-4 py-2.5 rounded-pill shadow-xs fw-bold d-inline-flex align-items-center gap-2">
                <i class="bi bi-floppy-fill"></i> Guardar Configuración
            </button>
        </div>

    </form>
</div>

<script>
function toggleApiKeyVisibility() {
    const input = document.getElementById('aiApiKeyInput');
    const icon = document.getElementById('aiApiKeyToggleIcon');
    if (!input || !icon) return;
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}

function setModelInput(modelName, provider) {
    const modelInput = document.getElementById('aiModelInput');
    const provSelect = document.getElementById('aiProviderSelect');
    if (modelInput) modelInput.value = modelName;
    if (provSelect && provider) provSelect.value = provider;
    if (window.AdminDialog) {
        AdminDialog.toast(`Modelo seleccionado: ${modelName}`, 'info');
    }
}

function actualizarSugerenciasModelos(provider) {
    // Si cambia de proveedor a local, limpiar o advertir
    if (provider === 'local') {
        const modelInput = document.getElementById('aiModelInput');
        if (modelInput) modelInput.value = 'motor-local-comunal';
    }
}
</script>

<?php require __DIR__ . '/layout/footer.php'; ?>
