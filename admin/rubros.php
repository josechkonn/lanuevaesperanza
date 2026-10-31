<?php
require __DIR__ . '/../includes/app.php';
requiere_login();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requiere_escritura();
    $accion = post('accion');
    $id = (int)post('id', 0);
    $volver = volver_a('admin/rubros.php');
    try {
        if ($accion === 'guardar') {
            $nombre = post('nombre');
            $desc = post('descripcion');
            $color = preg_match('/^#[0-9a-f]{6}$/i', post('color')) ? post('color') : '#0ea5e9';
            $orden = (int)post('orden', 0);
            $activo = isset($_POST['activo']) ? 1 : 0;
            if ($nombre === '' || mb_strlen($nombre) > 100) throw new RuntimeException('El nombre es obligatorio (máx. 100).');
            if ($id) {
                $pdo->prepare('UPDATE rubros SET nombre=?, descripcion=?, color=?, orden=?, activo=? WHERE id=?')
                    ->execute([$nombre, $desc ?: null, $color, $orden, $activo, $id]);
                auditar('editar', 'rubros', $id, $nombre);
                flash('success', 'Rubro actualizado.');
            } else {
                $pdo->prepare('INSERT INTO rubros (nombre, descripcion, color, orden, activo) VALUES (?,?,?,?,?)')
                    ->execute([$nombre, $desc ?: null, $color, $orden, $activo]);
                auditar('crear', 'rubros', (int)$pdo->lastInsertId(), $nombre);
                flash('success', 'Rubro creado.');
            }
        } elseif ($accion === 'estado' && $id) {
            $pdo->prepare('UPDATE rubros SET activo = 1 - activo WHERE id = ?')->execute([$id]);
            auditar('cambiar_estado', 'rubros', $id);
            flash('success', 'Estado actualizado.');
        } elseif ($accion === 'eliminar' && $id) {
            $pdo->prepare('DELETE FROM rubros WHERE id = ?')->execute([$id]);
            auditar('eliminar', 'rubros', $id);
            flash('success', 'Rubro eliminado. Sus bolsas/combos quedaron "sin rubro".');
        }
    } catch (PDOException $ex) {
        flash('danger', $ex->getCode() === '23000' ? 'Ya existe un rubro con ese nombre.' : 'Error de base de datos.');
        error_log($ex->getMessage());
    } catch (RuntimeException $ex) {
        flash('danger', $ex->getMessage());
    }
    redirect($volver);
}

$f = filtro_fechas('todo');
$where = [];
$params = [];
aplicar_fechas($f, 'r.creado_en', $where, $params);
$q = trim((string)($_GET['q'] ?? ''));
if ($q !== '') {
    $where[] = '(r.nombre LIKE :q1 OR r.descripcion LIKE :q2)';
    $params['q1'] = $params['q2'] = "%$q%";
}
$estF = $_GET['estado'] ?? '';
if ($estF === '1' || $estF === '0') {
    $where[] = 'r.activo = :act';
    $params['act'] = (int)$estF;
}

$res = paginar_cursor([
    'select' => 'SELECT r.*, (SELECT COUNT(*) FROM productos p WHERE p.rubro_id = r.id) AS total_productos FROM rubros r',
    'where' => $where, 'params' => $params,
    'col' => 'r.creado_en', 'key' => 'creado_en', 'id' => 'r.id',
]);

$titulo = 'Rubros';
$seccion = 'rubros';
$qsActual = qs();
ob_start(); ?>
<div class="col-12 col-md-3"><label class="form-label small mb-0">Buscar</label><input type="search" name="q" value="<?= e($q) ?>" class="form-control form-control-sm" placeholder="Nombre o descripción"></div>
<div class="col-6 col-md-auto"><label class="form-label small mb-0">Estado</label>
    <select name="estado" class="form-select form-select-sm"><option value="">Todos</option><option value="1"<?= $estF === '1' ? ' selected' : '' ?>>Activos</option><option value="0"<?= $estF === '0' ? ' selected' : '' ?>>Inactivos</option></select></div>
<?php $extra = ob_get_clean();
require __DIR__ . '/layout/header.php';
?>
<div class="card" id="tablaRubrosWrap">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
        <div>
            <h2 class="h6 fw-bold mb-0 d-flex align-items-center">
                <span>Rubros</span>
                <button type="button" class="btn-tour-hint" onclick="TourGuia.mostrarPaso('tabla_rubros')" title="¿Qué es este catálogo?">?</button>
            </h2>
            <small class="text-secondary">Categorías o tipos de bolsas y combos.</small>
        </div>
        <?php if (puede_editar()): ?><button id="btnNuevoRubro" class="btn btn-primary btn-sm" data-modal-new="#modalRubro" data-title="Nuevo rubro"><i class="bi bi-plus-lg"></i> Nuevo rubro</button><?php endif; ?>
    </div>
    <?= render_filtros($f, $extra) ?>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th>Rubro</th><th>Bolsas/Combos</th><th>Orden</th><th>Estado</th><th>Creado</th><?php if (puede_editar()): ?><th class="text-end">Acciones</th><?php endif; ?></tr></thead>
            <tbody>
            <?php if (!$res['filas']): ?><tr><td colspan="6"><div class="empty"><i class="bi bi-tags"></i>No hay rubros con esos filtros.</div></td></tr><?php endif; ?>
            <?php foreach ($res['filas'] as $r): ?>
                <tr>
                    <td><span class="dot me-2" style="background:<?= e($r['color']) ?>"></span><strong><?= e($r['nombre']) ?></strong><div class="small text-secondary"><?= e($r['descripcion']) ?></div></td>
                    <td><a href="<?= e(url('admin/productos.php?rubro=' . $r['id'])) ?>" class="text-decoration-none"><?= (int)$r['total_productos'] ?></a></td>
                    <td><?= (int)$r['orden'] ?></td>
                    <td><?= $r['activo'] ? '<span class="badge text-bg-success">Activo</span>' : '<span class="badge text-bg-secondary">Inactivo</span>' ?></td>
                    <td class="small text-nowrap"><span class="d-inline-flex align-items-center gap-1.5"><i class="bi bi-calendar3 text-emerald-600"></i><?= e(fecha_corta($r['creado_en'])) ?></span></td>
                    <?php if (puede_editar()): ?>
                    <td class="text-end text-nowrap">
                        <button class="btn btn-sm btn-light border" data-modal-edit="#modalRubro" data-title="Editar rubro" data-row='<?= e(json_encode($r)) ?>'><i class="bi bi-pencil"></i></button>
                        <form method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="accion" value="estado"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="_qs" value="<?= e($qsActual) ?>">
                            <button class="btn btn-sm btn-light border" title="Activar/Desactivar"><i class="bi <?= $r['activo'] ? 'bi-toggle-on text-success' : 'bi-toggle-off' ?>"></i></button></form>
                        <form method="post" class="d-inline" data-confirm="¿Eliminar el rubro «<?= e($r['nombre']) ?>»? Sus <?= (int)$r['total_productos'] ?> bolsas/combos quedarán sin rubro.">
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
<div class="modal fade" id="modalRubro" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="post" data-defaults='{"color":"#0ea5e9","orden":0,"activo":1}'>
            <div class="modal-header"><h5 class="modal-title">Rubro</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <?= csrf_field() ?><input type="hidden" name="accion" value="guardar"><input type="hidden" name="id"><input type="hidden" name="_qs" value="<?= e($qsActual) ?>">
                <div class="mb-3"><label class="form-label">Nombre *</label><input name="nombre" class="form-control" required maxlength="100" placeholder="Ej: Bolsa básica, Combo familiar…"></div>
                <div class="mb-3"><label class="form-label">Descripción</label><input name="descripcion" class="form-control" maxlength="255"></div>
                <div class="row g-3 mb-3">
                    <div class="col-6"><label class="form-label">Color</label><input type="color" name="color" class="form-control form-control-color w-100"></div>
                    <div class="col-6"><label class="form-label">Orden</label><input type="number" name="orden" class="form-control"></div>
                </div>
                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="activo" value="1" id="rActivo"><label class="form-check-label" for="rActivo">Activo</label></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
        </form>
    </div>
</div>
<?php endif; ?>
<?php require __DIR__ . '/layout/footer.php'; ?>
