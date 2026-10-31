<?php
require __DIR__ . '/../includes/app.php';
requiere_login();
$pdo = db();
const MONEDAS = ['USD' => 'Dólares (USD)', 'VES' => 'Bolívares (VES)', 'EUR' => 'Euros (EUR)', 'COP' => 'Pesos (COP)'];
const TIPOS_PRODUCTO = ['bolsa' => 'Bolsa', 'combo' => 'Combo'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requiere_escritura();
    $accion = post('accion');
    $id = (int)post('id', 0);
    $volver = volver_a('admin/productos.php');
    try {
        if ($accion === 'guardar') {
            $d = [
                'rubro_id' => (int)post('rubro_id') ?: null,
                'tipo' => post('tipo'),
                'nombre' => post('nombre'),
                'descripcion' => post('descripcion') ?: null,
                'contenido' => post('contenido') ?: null,
                'precio' => str_replace(',', '.', post('precio', '0')),
                'moneda' => post('moneda'),
                'activo' => isset($_POST['activo']) ? 1 : 0,
            ];
            if ($d['nombre'] === '' || mb_strlen($d['nombre']) > 150) throw new RuntimeException('El nombre es obligatorio (máx. 150).');
            if (!isset(TIPOS_PRODUCTO[$d['tipo']])) throw new RuntimeException('Tipo inválido.');
            if (!isset(MONEDAS[$d['moneda']])) throw new RuntimeException('Moneda inválida.');
            if (!is_numeric($d['precio']) || $d['precio'] < 0 || $d['precio'] > 9999999999) throw new RuntimeException('Precio inválido.');
            $d['precio'] = round((float)$d['precio'], 2);

            [$img, $err] = subir_archivo('imagen', 'imagen', 'imagenes');
            if ($err) throw new RuntimeException($err);

            $pdo->beginTransaction();
            if ($id) {
                $st = $pdo->prepare('SELECT precio, moneda, imagen FROM productos WHERE id = ? FOR UPDATE');
                $st->execute([$id]);
                $prev = $st->fetch();
                if (!$prev) throw new RuntimeException('Registro no encontrado.');
                $quitar = isset($_POST['quitar_imagen']);
                $nuevaImg = $img ?: ($quitar ? null : $prev['imagen']);
                $pdo->prepare('UPDATE productos SET rubro_id=?, tipo=?, nombre=?, descripcion=?, contenido=?, precio=?, moneda=?, activo=?, imagen=? WHERE id=?')
                    ->execute([...array_values($d), $nuevaImg, $id]);
                if ((float)$prev['precio'] !== $d['precio'] || $prev['moneda'] !== $d['moneda']) {
                    $pdo->prepare('INSERT INTO historial_precios (producto_id, precio_anterior, precio_nuevo, moneda, usuario_id) VALUES (?,?,?,?,?)')
                        ->execute([$id, $prev['precio'], $d['precio'], $d['moneda'], usuario_id()]);
                }
                $pdo->commit();
                if ($nuevaImg !== $prev['imagen']) borrar_archivo($prev['imagen']);
                auditar('editar', 'productos', $id, "{$d['nombre']} · {$d['moneda']} {$d['precio']}");
                flash('success', ucfirst($d['tipo']) . ' actualizado.');
            } else {
                $pdo->prepare('INSERT INTO productos (rubro_id, tipo, nombre, descripcion, contenido, precio, moneda, activo, imagen) VALUES (?,?,?,?,?,?,?,?,?)')
                    ->execute([...array_values($d), $img]);
                $nuevo = (int)$pdo->lastInsertId();
                $pdo->prepare('INSERT INTO historial_precios (producto_id, precio_anterior, precio_nuevo, moneda, usuario_id) VALUES (?,?,?,?,?)')
                    ->execute([$nuevo, 0, $d['precio'], $d['moneda'], usuario_id()]);
                $pdo->commit();
                auditar('crear', 'productos', $nuevo, "{$d['nombre']} · {$d['moneda']} {$d['precio']}");
                flash('success', ucfirst($d['tipo']) . ' creado.');
            }
        } elseif ($accion === 'estado' && $id) {
            $pdo->prepare('UPDATE productos SET activo = 1 - activo WHERE id = ?')->execute([$id]);
            auditar('cambiar_estado', 'productos', $id);
            flash('success', 'Estado actualizado.');
        } elseif ($accion === 'eliminar' && $id) {
            $st = $pdo->prepare('SELECT imagen, nombre FROM productos WHERE id = ?');
            $st->execute([$id]);
            $p = $st->fetch();
            $pdo->prepare('DELETE FROM productos WHERE id = ?')->execute([$id]);
            if ($p) borrar_archivo($p['imagen']);
            auditar('eliminar', 'productos', $id, $p['nombre'] ?? '');
            flash('success', 'Eliminado.');
        }
    } catch (Throwable $ex) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        if (!empty($img)) borrar_archivo($img);
        flash('danger', $ex instanceof RuntimeException ? $ex->getMessage() : 'Error al guardar.');
        if (!$ex instanceof RuntimeException) error_log($ex->getMessage());
    }
    redirect($volver);
}

$rubros = $pdo->query('SELECT id, nombre, color FROM rubros ORDER BY orden, nombre')->fetchAll();

$f = filtro_fechas('todo');
$where = [];
$params = [];
aplicar_fechas($f, 'p.creado_en', $where, $params);
$q = trim((string)($_GET['q'] ?? ''));
if ($q !== '') {
    $where[] = '(p.nombre LIKE :q1 OR p.contenido LIKE :q2)';
    $params['q1'] = $params['q2'] = "%$q%";
}
$tipoF = $_GET['tipo'] ?? '';
if (isset(TIPOS_PRODUCTO[$tipoF])) { $where[] = 'p.tipo = :tipo'; $params['tipo'] = $tipoF; }
$rubroF = (int)($_GET['rubro'] ?? 0);
if ($rubroF) { $where[] = 'p.rubro_id = :rub'; $params['rub'] = $rubroF; }
$estF = $_GET['estado'] ?? '';
if ($estF === '1' || $estF === '0') { $where[] = 'p.activo = :act'; $params['act'] = (int)$estF; }

$res = paginar_cursor([
    'select' => 'SELECT p.*, r.nombre AS rubro, r.color AS rubro_color FROM productos p LEFT JOIN rubros r ON r.id = p.rubro_id',
    'where' => $where, 'params' => $params,
    'col' => 'p.creado_en', 'key' => 'creado_en', 'id' => 'p.id',
]);

$titulo = 'Bolsas y combos';
$seccion = 'productos';
$qsActual = qs();
ob_start(); ?>
<div class="col-12 col-md-3"><label class="form-label small mb-0">Buscar</label><input type="search" name="q" value="<?= e($q) ?>" class="form-control form-control-sm" placeholder="Nombre o contenido"></div>
<div class="col-4 col-md-auto"><label class="form-label small mb-0">Tipo</label>
    <select name="tipo" class="form-select form-select-sm"><option value="">Todos</option><?php foreach (TIPOS_PRODUCTO as $k => $v): ?><option value="<?= $k ?>"<?= $tipoF === $k ? ' selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></div>
<div class="col-4 col-md-auto"><label class="form-label small mb-0">Rubro</label>
    <select name="rubro" class="form-select form-select-sm"><option value="">Todos</option><?php foreach ($rubros as $r): ?><option value="<?= $r['id'] ?>"<?= $rubroF === (int)$r['id'] ? ' selected' : '' ?>><?= e($r['nombre']) ?></option><?php endforeach; ?></select></div>
<div class="col-4 col-md-auto"><label class="form-label small mb-0">Estado</label>
    <select name="estado" class="form-select form-select-sm"><option value="">Todos</option><option value="1"<?= $estF === '1' ? ' selected' : '' ?>>Activos</option><option value="0"<?= $estF === '0' ? ' selected' : '' ?>>Inactivos</option></select></div>
<?php $extra = ob_get_clean();
require __DIR__ . '/layout/header.php';
?>
<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
        <div><h2 class="h6 fw-bold mb-0">Bolsas y combos</h2><small class="text-secondary">Cada cambio de precio queda guardado en el historial.</small></div>
        <?php if (puede_editar()): ?>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm" data-modal-new="#modalProducto" data-title="Nuevo combo" onclick="setTimeout(()=>document.querySelector('#modalProducto [name=tipo]').value='combo',10)"><i class="bi bi-box-seam"></i> Nuevo combo</button>
                <button class="btn btn-primary btn-sm" data-modal-new="#modalProducto" data-title="Nueva bolsa"><i class="bi bi-bag-plus"></i> Nueva bolsa</button>
            </div>
        <?php endif; ?>
    </div>
    <?= render_filtros($f, $extra) ?>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th></th><th>Nombre</th><th>Tipo</th><th>Rubro</th><th class="text-end">Precio</th><th>Estado</th><th>Creado</th><?php if (puede_editar()): ?><th class="text-end">Acciones</th><?php endif; ?></tr></thead>
            <tbody>
            <?php if (!$res['filas']): ?><tr><td colspan="8"><div class="empty"><i class="bi bi-bag"></i>No hay bolsas ni combos con esos filtros.</div></td></tr><?php endif; ?>
            <?php foreach ($res['filas'] as $r): $items = array_filter(array_map('trim', explode("\n", (string)$r['contenido']))); ?>
                <tr>
                    <td style="width:56px"><?= $r['imagen'] ? '<img src="' . e(url($r['imagen'])) . '" class="thumb" alt="">' : '<span class="thumb d-grid place-items-center bg-light text-secondary text-center pt-2"><i class="bi bi-image"></i></span>' ?></td>
                    <td><strong><?= e($r['nombre']) ?></strong><div class="small text-secondary"><?= count($items) ?> artículo(s)<?= $items ? ': ' . e(mb_strimwidth(implode(', ', $items), 0, 60, '…')) : '' ?></div></td>
                    <td><span class="badge <?= $r['tipo'] === 'combo' ? 'text-bg-primary' : 'text-bg-info' ?>"><?= e(TIPOS_PRODUCTO[$r['tipo']]) ?></span></td>
                    <td><?= $r['rubro'] ? '<span class="dot me-1" style="background:' . e($r['rubro_color']) . '"></span>' . e($r['rubro']) : '<span class="text-secondary">—</span>' ?></td>
                    <td class="text-end fw-bold text-nowrap"><?= e(dinero($r['precio'], $r['moneda'])) ?></td>
                    <td><?= $r['activo'] ? '<span class="badge text-bg-success">Activo</span>' : '<span class="badge text-bg-secondary">Inactivo</span>' ?></td>
                    <td class="small text-nowrap"><span class="d-inline-flex align-items-center gap-1.5"><i class="bi bi-calendar3 text-emerald-600"></i><?= e(fecha_corta($r['creado_en'])) ?></span></td>
                    <?php if (puede_editar()): ?>
                    <td class="text-end text-nowrap">
                        <button class="btn btn-sm btn-light border" data-modal-edit="#modalProducto" data-title="Editar <?= e($r['tipo']) ?>" data-row='<?= e(json_encode($r)) ?>'><i class="bi bi-pencil"></i></button>
                        <a class="btn btn-sm btn-light border" title="Historial de precios" href="<?= e(url('admin/historial_precios.php?producto=' . $r['id'])) ?>"><i class="bi bi-clock-history"></i></a>
                        <form method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="accion" value="estado"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="_qs" value="<?= e($qsActual) ?>">
                            <button class="btn btn-sm btn-light border" title="Activar/Desactivar"><i class="bi <?= $r['activo'] ? 'bi-toggle-on text-success' : 'bi-toggle-off' ?>"></i></button></form>
                        <form method="post" class="d-inline" data-confirm="¿Eliminar «<?= e($r['nombre']) ?>»? También se borrará su historial de precios.">
                            <?= csrf_field() ?><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="_qs" value="<?= e($qsActual) ?>">
                            <button class="btn btn-sm btn-light border text-danger"><i class="bi bi-trash"></i></button></form>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= render_paginador($res) ?>
</div>

<?php if (puede_editar()): ?>
<div class="modal fade" id="modalProducto" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" method="post" enctype="multipart/form-data" data-defaults='<?= e(json_encode(['tipo' => 'bolsa', 'moneda' => config_get('moneda_defecto', 'USD'), 'activo' => 1, 'precio' => '0.00'])) ?>'>
            <div class="modal-header"><h5 class="modal-title">Bolsa / Combo</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <?= csrf_field() ?><input type="hidden" name="accion" value="guardar"><input type="hidden" name="id"><input type="hidden" name="_qs" value="<?= e($qsActual) ?>">
                <div class="row g-3">
                    <div class="col-md-8"><label class="form-label">Nombre *</label><input name="nombre" class="form-control" required maxlength="150"></div>
                    <div class="col-md-4"><label class="form-label">Tipo *</label><select name="tipo" class="form-select"><?php foreach (TIPOS_PRODUCTO as $k => $v): ?><option value="<?= $k ?>"><?= $v ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-4"><label class="form-label">Rubro</label><select name="rubro_id" class="form-select"><option value="">Sin rubro</option><?php foreach ($rubros as $r): ?><option value="<?= $r['id'] ?>"><?= e($r['nombre']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-4"><label class="form-label">Precio *</label><input type="number" step="0.01" min="0" name="precio" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label">Moneda</label><select name="moneda" class="form-select"><?php foreach (MONEDAS as $k => $v): ?><option value="<?= $k ?>"><?= $v ?></option><?php endforeach; ?></select></div>
                    <div class="col-12"><label class="form-label">Descripción</label><input name="descripcion" class="form-control"></div>
                    <div class="col-md-7"><label class="form-label">¿Qué trae? <small class="text-secondary">(un artículo por línea)</small></label><textarea name="contenido" rows="6" class="form-control" placeholder="2 kg Arroz&#10;1 kg Harina de maíz&#10;1 L Aceite"></textarea></div>
                    <div class="col-md-5">
                        <label class="form-label">Imagen</label><input type="file" name="imagen" accept="image/*" class="form-control">
                        <div data-preview="imagen"></div>
                        <div class="form-check mt-2" data-solo-editar><input class="form-check-input" type="checkbox" name="quitar_imagen" id="pQuitar"><label class="form-check-label small" for="pQuitar">Quitar imagen actual</label></div>
                        <div class="form-check form-switch mt-3"><input class="form-check-input" type="checkbox" name="activo" value="1" id="pActivo"><label class="form-check-label" for="pActivo">Visible / activo</label></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
        </form>
    </div>
</div>
<?php endif; ?>
<?php require __DIR__ . '/layout/footer.php'; ?>
