<?php
require __DIR__ . '/../includes/app.php';
requiere_login();
$pdo = db();

$productos = $pdo->query('SELECT id, nombre, tipo FROM productos ORDER BY nombre')->fetchAll();

$f = filtro_fechas('todo');
$where = [];
$params = [];
aplicar_fechas($f, 'h.creado_en', $where, $params);
$prodF = (int)($_GET['producto'] ?? 0);
if ($prodF) { $where[] = 'h.producto_id = :prod'; $params['prod'] = $prodF; }

$res = paginar_cursor([
    'select' => 'SELECT h.*, p.nombre AS producto, p.tipo, u.nombre AS usuario
                 FROM historial_precios h
                 JOIN productos p ON p.id = h.producto_id
                 LEFT JOIN usuarios u ON u.id = h.usuario_id',
    'where' => $where, 'params' => $params,
    'col' => 'h.creado_en', 'key' => 'creado_en', 'id' => 'h.id',
]);

$titulo = 'Historial de precios';
$seccion = 'precios';
ob_start(); ?>
<div class="col-12 col-md-4"><label class="form-label small mb-0">Bolsa / combo</label>
    <select name="producto" class="form-select form-select-sm"><option value="">Todos</option>
        <?php foreach ($productos as $p): ?><option value="<?= $p['id'] ?>"<?= $prodF === (int)$p['id'] ? ' selected' : '' ?>><?= e($p['nombre']) ?> (<?= e($p['tipo']) ?>)</option><?php endforeach; ?>
    </select></div>
<?php $extra = ob_get_clean();
require __DIR__ . '/layout/header.php';
?>
<div class="card">
    <div class="card-header py-3"><h2 class="h6 fw-bold mb-0">Cambios de precio</h2><small class="text-secondary">Registro automático cada vez que se modifica el precio de una bolsa o combo.</small></div>
    <?= render_filtros($f, $extra) ?>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th>Fecha</th><th>Bolsa / combo</th><th class="text-end">Anterior</th><th class="text-end">Nuevo</th><th class="text-end">Variación</th><th>Por</th></tr></thead>
            <tbody>
            <?php if (!$res['filas']): ?><tr><td colspan="6"><div class="empty"><i class="bi bi-graph-up"></i>Sin cambios de precio en este periodo.</div></td></tr><?php endif; ?>
            <?php foreach ($res['filas'] as $r):
                $ant = (float)$r['precio_anterior']; $nue = (float)$r['precio_nuevo'];
                $var = $ant > 0 ? ($nue - $ant) * 100 / $ant : null; ?>
                <tr>
                    <td class="small text-nowrap"><?= e(fecha_corta($r['creado_en'], true)) ?></td>
                    <td><strong><?= e($r['producto']) ?></strong> <span class="badge text-bg-light border"><?= e($r['tipo']) ?></span></td>
                    <td class="text-end text-secondary"><?= $ant > 0 ? e(dinero($ant, $r['moneda'])) : '<small>Precio inicial</small>' ?></td>
                    <td class="text-end fw-bold"><?= e(dinero($nue, $r['moneda'])) ?></td>
                    <td class="text-end"><?php if ($var !== null): ?><span class="badge <?= $var > 0 ? 'text-bg-danger' : ($var < 0 ? 'text-bg-success' : 'text-bg-light') ?>"><?= $var > 0 ? '+' : '' ?><?= number_format($var, 1, ',', '.') ?>%</span><?php else: ?>—<?php endif; ?></td>
                    <td class="small"><?= e($r['usuario'] ?? '—') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= render_paginador($res) ?>
</div>
<?php require __DIR__ . '/layout/footer.php'; ?>
