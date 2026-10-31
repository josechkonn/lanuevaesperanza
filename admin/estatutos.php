<?php
require __DIR__ . '/../includes/app.php';
requiere_login();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requiere_escritura();
    $accion = post('accion');
    $id = (int)post('id', 0);
    $volver = volver_a('admin/estatutos.php');
    $pdf = null;
    try {
        if ($accion === 'guardar') {
            $titulo = post('titulo');
            $contenido = post('contenido') ?: null;
            $orden = (int)post('orden', 0);
            $publicado = isset($_POST['publicado']) ? 1 : 0;
            if ($titulo === '' || mb_strlen($titulo) > 200) throw new RuntimeException('El título es obligatorio (máx. 200).');

            [$pdf, $err] = subir_archivo('archivo_pdf', 'pdf', 'documentos');
            if ($err) throw new RuntimeException($err);
            if (!$id && !$pdf && !$contenido) throw new RuntimeException('Agrega el texto del estatuto o sube un PDF.');

            if ($id) {
                $st = $pdo->prepare('SELECT archivo_pdf FROM estatutos WHERE id = ?');
                $st->execute([$id]);
                $actual = $st->fetchColumn();
                $nuevo = $pdf ?: (isset($_POST['quitar_pdf']) ? null : $actual);
                $pdo->prepare('UPDATE estatutos SET titulo=?, contenido=?, orden=?, publicado=?, archivo_pdf=? WHERE id=?')
                    ->execute([$titulo, $contenido, $orden, $publicado, $nuevo, $id]);
                if ($nuevo !== $actual) borrar_archivo($actual ?: null);
                auditar('editar', 'estatutos', $id, $titulo);
                flash('success', 'Estatuto actualizado.');
            } else {
                $pdo->prepare('INSERT INTO estatutos (titulo, contenido, orden, publicado, archivo_pdf) VALUES (?,?,?,?,?)')
                    ->execute([$titulo, $contenido, $orden, $publicado, $pdf]);
                auditar('crear', 'estatutos', (int)$pdo->lastInsertId(), $titulo);
                flash('success', 'Estatuto creado.');
            }
        } elseif ($accion === 'estado' && $id) {
            $pdo->prepare('UPDATE estatutos SET publicado = 1 - publicado WHERE id = ?')->execute([$id]);
            auditar('cambiar_estado', 'estatutos', $id);
            flash('success', 'Visibilidad actualizada.');
        } elseif ($accion === 'eliminar' && $id) {
            $st = $pdo->prepare('SELECT archivo_pdf FROM estatutos WHERE id = ?');
            $st->execute([$id]);
            $actual = $st->fetchColumn();
            $pdo->prepare('DELETE FROM estatutos WHERE id = ?')->execute([$id]);
            borrar_archivo($actual ?: null);
            auditar('eliminar', 'estatutos', $id);
            flash('success', 'Estatuto eliminado.');
        }
    } catch (Throwable $ex) {
        if ($pdf) borrar_archivo($pdf);
        flash('danger', $ex instanceof RuntimeException ? $ex->getMessage() : 'Error al guardar.');
        if (!$ex instanceof RuntimeException) error_log($ex->getMessage());
    }
    redirect($volver);
}

$f = filtro_fechas('todo');
$where = [];
$params = [];
aplicar_fechas($f, 's.creado_en', $where, $params);
$q = trim((string)($_GET['q'] ?? ''));
if ($q !== '') {
    $where[] = '(s.titulo LIKE :q1 OR s.contenido LIKE :q2)';
    $params['q1'] = $params['q2'] = "%$q%";
}

$res = paginar_cursor([
    'select' => 'SELECT s.* FROM estatutos s',
    'where' => $where, 'params' => $params,
    'col' => 's.creado_en', 'key' => 'creado_en', 'id' => 's.id',
]);

$titulo = 'Estatutos';
$seccion = 'estatutos';
$qsActual = qs();
ob_start(); ?>
<div class="col-12 col-md-4"><label class="form-label small mb-0">Buscar</label><input type="search" name="q" value="<?= e($q) ?>" class="form-control form-control-sm" placeholder="Título o texto"></div>
<?php $extra = ob_get_clean();
require __DIR__ . '/layout/header.php';
?>
<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
        <div><h2 class="h6 fw-bold mb-0">Estatutos y reglamentos</h2><small class="text-secondary">Texto, PDF o ambos. El «orden» define cómo aparecen en la página pública.</small></div>
        <?php if (puede_editar()): ?><button class="btn btn-primary btn-sm" data-modal-new="#modalEst" data-title="Nuevo estatuto"><i class="bi bi-plus-lg"></i> Nuevo estatuto</button><?php endif; ?>
    </div>
    <?= render_filtros($f, $extra) ?>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th>Orden</th><th>Título</th><th>PDF</th><th>Estado</th><th>Actualizado</th><?php if (puede_editar()): ?><th class="text-end">Acciones</th><?php endif; ?></tr></thead>
            <tbody>
            <?php if (!$res['filas']): ?><tr><td colspan="6"><div class="empty"><i class="bi bi-journal-text"></i>No hay estatutos registrados.</div></td></tr><?php endif; ?>
            <?php foreach ($res['filas'] as $r): ?>
                <tr>
                    <td><?= (int)$r['orden'] ?></td>
                    <td><strong><?= e($r['titulo']) ?></strong><div class="small text-secondary"><?= e(mb_strimwidth((string)$r['contenido'], 0, 100, '…')) ?></div></td>
                    <td><?= $r['archivo_pdf'] ? '<a href="' . e(url($r['archivo_pdf'])) . '" target="_blank" class="btn btn-sm btn-outline-danger"><i class="bi bi-file-earmark-pdf"></i> Ver</a>' : '<span class="text-secondary">—</span>' ?></td>
                    <td><?= $r['publicado'] ? '<span class="badge text-bg-success">Publicado</span>' : '<span class="badge text-bg-warning">Oculto</span>' ?></td>
                    <td class="small"><?= e(fecha_corta($r['actualizado_en'] ?: $r['creado_en'], true)) ?></td>
                    <?php if (puede_editar()): ?>
                    <td class="text-end text-nowrap">
                        <button class="btn btn-sm btn-light border" data-modal-edit="#modalEst" data-title="Editar estatuto" data-row='<?= e(json_encode($r)) ?>'><i class="bi bi-pencil"></i></button>
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
<div class="modal fade" id="modalEst" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" method="post" enctype="multipart/form-data" data-defaults='{"publicado":1,"orden":0}'>
            <div class="modal-header"><h5 class="modal-title">Estatuto</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <?= csrf_field() ?><input type="hidden" name="accion" value="guardar"><input type="hidden" name="id"><input type="hidden" name="_qs" value="<?= e($qsActual) ?>">
                <div class="row g-3">
                    <div class="col-md-9"><label class="form-label">Título *</label><input name="titulo" class="form-control" required maxlength="200" placeholder="Ej: Capítulo I – De la organización socio productiva"></div>
                    <div class="col-md-3"><label class="form-label">Orden</label><input type="number" name="orden" class="form-control"></div>
                    <div class="col-12"><label class="form-label">Texto</label><textarea name="contenido" rows="10" class="form-control"></textarea></div>
                    <div class="col-md-8"><label class="form-label">Documento PDF</label><input type="file" name="archivo_pdf" accept="application/pdf" class="form-control"><div data-preview="archivo_pdf"></div>
                        <div class="form-check mt-2" data-solo-editar><input class="form-check-input" type="checkbox" name="quitar_pdf" id="estQuitar"><label class="form-check-label small" for="estQuitar">Quitar PDF actual</label></div></div>
                    <div class="col-md-4 d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="publicado" value="1" id="estPub"><label class="form-check-label" for="estPub">Publicado</label></div></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
        </form>
    </div>
</div>
<?php endif; ?>
<?php require __DIR__ . '/layout/footer.php'; ?>
