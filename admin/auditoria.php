<?php
require __DIR__ . '/../includes/app.php';
requiere_rol('superadmin');
$pdo = db();

$entidades = ['usuarios', 'rubros', 'productos', 'entregas', 'publicaciones', 'estatutos', 'configuracion'];
$usuarios = $pdo->query('SELECT id, nombre FROM usuarios ORDER BY nombre')->fetchAll();

$f = filtro_fechas('semana');
$where = [];
$params = [];
aplicar_fechas($f, 'a.creado_en', $where, $params);
$entF = $_GET['entidad'] ?? '';
if (in_array($entF, $entidades, true)) { $where[] = 'a.entidad = :ent'; $params['ent'] = $entF; }
$usrF = (int)($_GET['usuario'] ?? 0);
if ($usrF) { $where[] = 'a.usuario_id = :usr'; $params['usr'] = $usrF; }
$q = trim((string)($_GET['q'] ?? ''));
if ($q !== '') { $where[] = '(a.detalle LIKE :q1 OR a.accion LIKE :q2)'; $params['q1'] = $params['q2'] = "%$q%"; }

$res = paginar_cursor([
    'select' => 'SELECT a.*, u.nombre AS usuario FROM auditoria a LEFT JOIN usuarios u ON u.id = a.usuario_id',
    'where' => $where, 'params' => $params,
    'col' => 'a.creado_en', 'key' => 'creado_en', 'id' => 'a.id',
    'limite' => 25,
]);

$colores = ['crear' => 'success', 'editar' => 'primary', 'eliminar' => 'danger', 'login' => 'info', 'logout' => 'secondary'];
$titulo = 'Auditoría';
$seccion = 'auditoria';
ob_start(); ?>
<div class="col-6 col-md-auto"><label class="form-label small mb-0">Módulo</label>
    <select name="entidad" class="form-select form-select-sm"><option value="">Todos</option><?php foreach ($entidades as $en): ?><option<?= $entF === $en ? ' selected' : '' ?>><?= $en ?></option><?php endforeach; ?></select></div>
<div class="col-6 col-md-auto"><label class="form-label small mb-0">Usuario</label>
    <select name="usuario" class="form-select form-select-sm"><option value="">Todos</option><?php foreach ($usuarios as $u): ?><option value="<?= $u['id'] ?>"<?= $usrF === (int)$u['id'] ? ' selected' : '' ?>><?= e($u['nombre']) ?></option><?php endforeach; ?></select></div>
<div class="col-12 col-md-3"><label class="form-label small mb-0">Buscar</label><input type="search" name="q" value="<?= e($q) ?>" class="form-control form-control-sm" placeholder="Detalle o acción"></div>
<?php $extra = ob_get_clean();
require __DIR__ . '/layout/header.php';
?>
<div class="card" id="tablaAuditoriaWrap">
    <div class="card-header py-3">
        <h2 class="h6 fw-bold mb-0 d-flex align-items-center">
            <span>Registro de actividad</span>
            <button type="button" class="btn-tour-hint" onclick="TourGuia.mostrarPaso('tabla_auditoria')" title="¿Cómo funciona la auditoría?">?</button>
        </h2>
        <small class="text-secondary">Quién hizo qué y cuándo.</small>
    </div>
    <?= render_filtros($f, $extra) ?>
    <div class="table-responsive">
        <table class="table table-hover table-sm">
            <thead><tr><th class="ps-3">Fecha</th><th>Usuario</th><th>Acción</th><th>Módulo</th><th>Detalle</th><th>IP</th></tr></thead>
            <tbody>
            <?php if (!$res['filas']): ?><tr><td colspan="6"><div class="empty"><i class="bi bi-shield-check"></i>Sin actividad en este periodo.</div></td></tr><?php endif; ?>
            <?php foreach ($res['filas'] as $r): ?>
                <tr>
                    <td class="ps-3 small text-nowrap"><span class="d-inline-flex align-items-center gap-1.5"><i class="bi bi-calendar3 text-emerald-600"></i><?= e(fecha_corta($r['creado_en'], true)) ?></span></td>
                    <td class="small"><?= e($r['usuario'] ?? 'Sistema') ?></td>
                    <td><span class="badge text-bg-<?= $colores[$r['accion']] ?? 'light border' ?>"><?= e($r['accion']) ?></span></td>
                    <td class="small"><?= e($r['entidad']) ?><?= $r['entidad_id'] ? ' #' . (int)$r['entidad_id'] : '' ?></td>
                    <td class="small"><?= e($r['detalle']) ?></td>
                    <td class="small text-secondary"><?= e($r['ip']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= render_paginador($res) ?>
</div>
<?php require __DIR__ . '/layout/footer.php'; ?>
