<?php
require __DIR__ . '/../includes/app.php';
requiere_login();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requiere_escritura();
    $accion = post('accion');
    $id = (int)post('id', 0);
    $volver = volver_a('admin/publicaciones.php');
    $img = null;
    try {
        if ($accion === 'guardar') {
            $titulo = post('titulo');
            $resumen = post('resumen') ?: null;
            $contenido = post('contenido') ?: null;
            $publicado = isset($_POST['publicado']) ? 1 : 0;
            $fp = str_replace('T', ' ', post('fecha_publicacion'));
            if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}/', $fp)) $fp = date('Y-m-d H:i');
            if ($titulo === '' || mb_strlen($titulo) > 200) throw new RuntimeException('El título es obligatorio (máx. 200).');

            [$img, $err] = subir_archivo('imagen', 'imagen', 'imagenes');
            if ($err) throw new RuntimeException($err);

            if ($id) {
                $st = $pdo->prepare('SELECT imagen FROM publicaciones WHERE id = ?');
                $st->execute([$id]);
                $actual = $st->fetchColumn();
                $nueva = $img ?: (isset($_POST['quitar_imagen']) ? null : $actual);
                $pdo->prepare('UPDATE publicaciones SET titulo=?, resumen=?, contenido=?, publicado=?, fecha_publicacion=?, imagen=? WHERE id=?')
                    ->execute([$titulo, $resumen, $contenido, $publicado, $fp, $nueva, $id]);
                if ($nueva !== $actual) borrar_archivo($actual ?: null);
                auditar('editar', 'publicaciones', $id, $titulo);
                flash('success', 'Publicación actualizada.');
            } else {
                $pdo->prepare('INSERT INTO publicaciones (titulo, resumen, contenido, publicado, fecha_publicacion, imagen, creado_por) VALUES (?,?,?,?,?,?,?)')
                    ->execute([$titulo, $resumen, $contenido, $publicado, $fp, $img, usuario_id()]);
                auditar('crear', 'publicaciones', (int)$pdo->lastInsertId(), $titulo);
                flash('success', 'Publicación creada.');
            }
        } elseif ($accion === 'estado' && $id) {
            $pdo->prepare('UPDATE publicaciones SET publicado = 1 - publicado WHERE id = ?')->execute([$id]);
            auditar('cambiar_estado', 'publicaciones', $id);
            flash('success', 'Visibilidad actualizada.');
        } elseif ($accion === 'eliminar' && $id) {
            $st = $pdo->prepare('SELECT imagen FROM publicaciones WHERE id = ?');
            $st->execute([$id]);
            $actual = $st->fetchColumn();
            $pdo->prepare('DELETE FROM publicaciones WHERE id = ?')->execute([$id]);
            borrar_archivo($actual ?: null);
            auditar('eliminar', 'publicaciones', $id);
            flash('success', 'Publicación eliminada.');
        }
    } catch (Throwable $ex) {
        if ($img) borrar_archivo($img);
        flash('danger', $ex instanceof RuntimeException ? $ex->getMessage() : 'Error al guardar.');
        if (!$ex instanceof RuntimeException) error_log($ex->getMessage());
    }
    redirect($volver);
}

$f = filtro_fechas('todo');
$where = [];
$params = [];
aplicar_fechas($f, 'pb.fecha_publicacion', $where, $params);
$q = trim((string)($_GET['q'] ?? ''));
if ($q !== '') {
    $where[] = '(pb.titulo LIKE :q1 OR pb.contenido LIKE :q2)';
    $params['q1'] = $params['q2'] = "%$q%";
}
$estF = $_GET['estado'] ?? '';
if ($estF === '1' || $estF === '0') { $where[] = 'pb.publicado = :pub'; $params['pub'] = (int)$estF; }

$res = paginar_cursor([
    'select' => 'SELECT pb.*, u.nombre AS autor FROM publicaciones pb LEFT JOIN usuarios u ON u.id = pb.creado_por',
    'where' => $where, 'params' => $params,
    'col' => 'pb.fecha_publicacion', 'key' => 'fecha_publicacion', 'id' => 'pb.id',
]);

$titulo = 'Información';
$seccion = 'publicaciones';
$qsActual = qs();
ob_start(); ?>
<div class="col-12 col-md-3"><label class="form-label small mb-0">Buscar</label><input type="search" name="q" value="<?= e($q) ?>" class="form-control form-control-sm" placeholder="Título o contenido"></div>
<div class="col-6 col-md-auto"><label class="form-label small mb-0">Estado</label>
    <select name="estado" class="form-select form-select-sm"><option value="">Todos</option><option value="1"<?= $estF === '1' ? ' selected' : '' ?>>Publicadas</option><option value="0"<?= $estF === '0' ? ' selected' : '' ?>>Borradores</option></select></div>
<?php $extra = ob_get_clean();
require __DIR__ . '/layout/header.php';
?>
<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
        <div><h2 class="h6 fw-bold mb-0">Información y avisos</h2><small class="text-secondary">Noticias y avisos que se muestran en la página pública.</small></div>
        <?php if (puede_editar()): ?><button class="btn btn-primary btn-sm" data-modal-new="#modalPub" data-title="Nueva publicación"><i class="bi bi-plus-lg"></i> Nueva publicación</button><?php endif; ?>
    </div>
    <?= render_filtros($f, $extra) ?>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th></th><th>Título</th><th>Fecha</th><th>Estado</th><th>Autor</th><?php if (puede_editar()): ?><th class="text-end">Acciones</th><?php endif; ?></tr></thead>
            <tbody>
            <?php if (!$res['filas']): ?><tr><td colspan="6"><div class="empty"><i class="bi bi-megaphone"></i>No hay publicaciones en este periodo.</div></td></tr><?php endif; ?>
            <?php foreach ($res['filas'] as $r):
                $r['fecha_publicacion'] = date('Y-m-d\TH:i', strtotime($r['fecha_publicacion'])); ?>
                <tr>
                    <td style="width:56px"><?= $r['imagen'] ? '<img src="' . e(url($r['imagen'])) . '" class="thumb" alt="">' : '' ?></td>
                    <td><strong><?= e($r['titulo']) ?></strong><div class="small text-secondary"><?= e(mb_strimwidth((string)($r['resumen'] ?: $r['contenido']), 0, 90, '…')) ?></div></td>
                    <td class="small text-nowrap"><?= e(fecha_corta($r['fecha_publicacion'], true)) ?></td>
                    <td><?= $r['publicado'] ? '<span class="badge text-bg-success">Publicada</span>' : '<span class="badge text-bg-warning">Borrador</span>' ?></td>
                    <td class="small"><?= e($r['autor'] ?? '—') ?></td>
                    <?php if (puede_editar()): ?>
                    <td class="text-end text-nowrap">
                        <button class="btn btn-sm btn-light border" data-modal-edit="#modalPub" data-title="Editar publicación" data-row='<?= e(json_encode($r)) ?>'><i class="bi bi-pencil"></i></button>
                        <form method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="accion" value="estado"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="_qs" value="<?= e($qsActual) ?>">
                            <button class="btn btn-sm btn-light border" title="Publicar/Ocultar"><i class="bi <?= $r['publicado'] ? 'bi-eye text-success' : 'bi-eye-slash' ?>"></i></button></form>
                        <form method="post" class="d-inline" data-confirm="¿Eliminar «<?= e($r['titulo']) ?>»?"><?= csrf_field() ?><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="_qs" value="<?= e($qsActual) ?>">
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
<div class="modal fade" id="modalPub" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" method="post" enctype="multipart/form-data" data-defaults='<?= e(json_encode(['publicado' => 1, 'fecha_publicacion' => date('Y-m-d\TH:i')])) ?>'>
            <div class="modal-header"><h5 class="modal-title">Publicación</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <?= csrf_field() ?><input type="hidden" name="accion" value="guardar"><input type="hidden" name="id"><input type="hidden" name="_qs" value="<?= e($qsActual) ?>">
                <div class="row g-3">
                    <div class="col-md-8"><label class="form-label">Título *</label><input name="titulo" class="form-control" required maxlength="200"></div>
                    <div class="col-md-4"><label class="form-label">Fecha de publicación</label><input type="datetime-local" name="fecha_publicacion" class="form-control"></div>
                    <div class="col-12"><label class="form-label">Resumen</label><input name="resumen" class="form-control" maxlength="300"></div>
                    <div class="col-12"><label class="form-label">Contenido</label><textarea name="contenido" rows="7" class="form-control"></textarea></div>
                    <div class="col-md-8"><label class="form-label">Imagen</label><input type="file" name="imagen" accept="image/*" class="form-control"><div data-preview="imagen"></div>
                        <div class="form-check mt-2" data-solo-editar><input class="form-check-input" type="checkbox" name="quitar_imagen" id="pubQuitar"><label class="form-check-label small" for="pubQuitar">Quitar imagen actual</label></div></div>
                    <div class="col-md-4 d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="publicado" value="1" id="pubPub"><label class="form-check-label" for="pubPub">Publicada</label></div></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
        </form>
    </div>
</div>
<?php endif; ?>
<?php require __DIR__ . '/layout/footer.php'; ?>
