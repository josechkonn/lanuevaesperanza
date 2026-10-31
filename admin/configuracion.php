<?php
require __DIR__ . '/../includes/app.php';
requiere_rol('superadmin');
$pdo = db();

$campos = [
    'nombre_organizacion' => ['Nombre de la organización', 'text', 'col-md-6', 'Nombre oficial de la EPSDC que se visualiza en encabezados y comunicados.'],
    'lema'                => ['Lema / eslogan', 'text', 'col-md-6', 'Eslogan de soberanía agroalimentaria.'],
    'telefono'            => ['Teléfono / Móvil institucional (Contacto)', 'text', 'col-md-6', 'Número móvil o WhatsApp oficial (+58...). Se muestra en el pie de página, contacto directo y es utilizado por el Asistente IA.'],
    'email_contacto'      => ['Correo electrónico de contacto', 'email', 'col-md-6', 'Correo institucional para recepción de mensajes y coordinación.'],
    'direccion'           => ['Dirección física y sede comunal', 'textarea', 'col-12', 'Dirección física completa de la sede central y centro de acopio comunal. Se muestra en el pie de página del portal y es utilizada por el Asistente IA para responder consultas de ubicación y georreferenciación.'],
    'moneda_defecto'      => ['Moneda por defecto', 'select', 'col-md-6', 'Moneda contable y de fijación de precios en el catálogo.'],
];

$camposIA = [
    'ai_provider' => ['Proveedor de IA', 'select_ai'],
    'ai_api_key'  => ['API Key de la IA (DeepSeek / OpenRouter / Gemini)', 'password'],
    'ai_model'    => ['Nombre del Modelo (Ej: deepseek-chat, deepseek/deepseek-r1:free, gemini-1.5-flash)', 'text'],
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
        flash('success', 'Configuración guardada exitosamente.');
    } catch (RuntimeException $ex) {
        if ($logo) borrar_archivo($logo);
        flash('danger', $ex->getMessage());
    }
    redirect(url('admin/configuracion.php'));
}

$titulo = 'Configuración';
$seccion = 'configuracion';
require __DIR__ . '/layout/header.php';
?>
<div class="card" style="max-width:820px">
    <div class="card-header py-3"><h2 class="h6 fw-bold mb-0">Datos de la organización</h2><small class="text-secondary">Se muestran en el panel y en la página pública.</small></div>
    <form method="post" enctype="multipart/form-data" class="card-body">
        <?= csrf_field() ?>
        <div class="row g-3">
            <?php foreach ($campos as $k => [$lbl, $tipo, $colClass, $ayuda]): $v = config_get($k); ?>
                <div class="<?= e($colClass) ?>">
                    <label class="form-label fw-semibold"><?= e($lbl) ?></label>
                    <?php if ($tipo === 'select'): ?>
                        <select name="<?= $k ?>" class="form-select"><?php foreach (['USD', 'VES', 'EUR', 'COP'] as $m): ?><option<?= $v === $m ? ' selected' : '' ?>><?= $m ?></option><?php endforeach; ?></select>
                    <?php elseif ($tipo === 'textarea'): ?>
                        <textarea name="<?= $k ?>" class="form-control" rows="3" maxlength="500" placeholder="Ej: Calle Principal, Edificio Comunitario, Piso 1, Aldea Quebrada de San José, Ciudad de La Grita, Municipio Jáuregui, Estado Táchira"><?= e($v) ?></textarea>
                    <?php else: ?>
                        <input type="<?= $tipo ?>" name="<?= $k ?>" value="<?= e($v) ?>" class="form-control" maxlength="500" placeholder="<?= $k === 'telefono' ? '+58 412 1234567' : '' ?>">
                    <?php endif; ?>
                    <?php if (!empty($ayuda)): ?>
                        <div class="form-text small text-muted"><?= e($ayuda) ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Logo Institucional</label>
                <input type="file" name="logo" accept="image/*" class="form-control">
                <div class="form-text small text-muted">Formatos PNG, JPG o WEBP recomendados.</div>
                <?php if ($l = config_get('logo')): ?>
                    <div class="d-flex align-items-center gap-2 mt-2">
                        <img src="<?= e(url($l)) ?>" class="thumb" alt="Logo">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="quitar_logo" id="qLogo">
                            <label class="form-check-label small" for="qLogo">Quitar logo actual</label>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <hr class="my-4">

        <div class="mb-3">
            <h3 class="h6 fw-bold mb-1 text-primary"><i class="bi bi-robot"></i> Asistente Virtual Comunal con Inteligencia Artificial</h3>
            <small class="text-secondary">Configura el motor de IA que responderá las consultas de los usuarios y productores en el chat público.</small>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Proveedor de IA</label>
                <?php $provActual = config_get('ai_provider', 'deepseek'); ?>
                <select name="ai_provider" class="form-select">
                    <option value="deepseek"<?= $provActual === 'deepseek' ? ' selected' : '' ?>>DeepSeek Oficial (api.deepseek.com)</option>
                    <option value="openrouter"<?= $provActual === 'openrouter' ? ' selected' : '' ?>>OpenRouter (Modelos gratuitos / DeepSeek)</option>
                    <option value="gemini"<?= $provActual === 'gemini' ? ' selected' : '' ?>>Google Gemini (Flash)</option>
                    <option value="local"<?= $provActual === 'local' ? ' selected' : '' ?>>Motor Local Inteligente (Sin API Key)</option>
                </select>
                <div class="form-text">Si no tienes API Key, selecciona el Motor Local.</div>
            </div>

            <div class="col-md-4">
                <label class="form-label">API Key</label>
                <input type="password" name="ai_api_key" value="<?= e(config_get('ai_api_key', '')) ?>" class="form-control" placeholder="sk-..." autocomplete="off">
                <div class="form-text">Tu clave secreta de DeepSeek / OpenRouter / Gemini.</div>
            </div>

            <div class="col-md-4">
                <label class="form-label">Modelo de IA</label>
                <input type="text" name="ai_model" value="<?= e(config_get('ai_model', 'deepseek-chat')) ?>" class="form-control" placeholder="deepseek-chat">
                <div class="form-text">Ej: deepseek-chat, deepseek/deepseek-r1:free, gemini-1.5-flash</div>
            </div>
        </div>

        <div class="mt-4"><button class="btn btn-primary"><i class="bi bi-save"></i> Guardar configuración</button></div>
    </form>
</div>
<?php require __DIR__ . '/layout/footer.php'; ?>
