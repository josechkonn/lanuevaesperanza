<?php
require __DIR__ . '/../includes/app.php';
requiere_login();
$pdo = db();
const ESTADOS_ENTREGA = ['programada' => 'Programada', 'en_camino' => 'En camino', 'entregada' => 'Entregada', 'cancelada' => 'Cancelada'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requiere_escritura();
    $accion = post('accion');
    $id = (int)post('id', 0);
    $volver = volver_a('admin/entregas.php');
    try {
        if ($accion === 'guardar') {
            $fecha = post('fecha_entrega');
            $hora = post('hora');
            $d = [
                'fecha_entrega' => $fecha,
                'hora' => preg_match('/^\d{2}:\d{2}/', $hora) ? substr($hora, 0, 5) : null,
                'sector' => post('sector'),
                'direccion' => post('direccion'),
                'cantidad_bolsas' => (int)post('cantidad_bolsas', 0),
                'producto_id' => (int)post('producto_id') ?: null,
                'responsable' => post('responsable'),
                'telefono' => post('telefono') ?: null,
                'estado' => post('estado'),
                'notas' => post('notas') ?: null,
            ];
            $dt = DateTime::createFromFormat('Y-m-d', $fecha);
            if (!$dt || $dt->format('Y-m-d') !== $fecha) throw new RuntimeException('Fecha inválida.');
            foreach (['sector' => 'El sector', 'direccion' => 'La dirección', 'responsable' => 'El responsable'] as $k => $lbl) {
                if ($d[$k] === '') throw new RuntimeException("$lbl es obligatorio.");
            }
            if ($d['cantidad_bolsas'] < 0 || $d['cantidad_bolsas'] > 1000000) throw new RuntimeException('Cantidad inválida.');
            if (!isset(ESTADOS_ENTREGA[$d['estado']])) throw new RuntimeException('Estado inválido.');

            if ($id) {
                $pdo->prepare('UPDATE entregas SET fecha_entrega=?, hora=?, sector=?, direccion=?, cantidad_bolsas=?, producto_id=?, responsable=?, telefono=?, estado=?, notas=? WHERE id=?')
                    ->execute([...array_values($d), $id]);
                auditar('editar', 'entregas', $id, "{$d['sector']} · {$fecha} · {$d['estado']}");
                flash('success', 'Entrega actualizada.');
            } else {
                $pdo->prepare('INSERT INTO entregas (fecha_entrega, hora, sector, direccion, cantidad_bolsas, producto_id, responsable, telefono, estado, notas, creado_por) VALUES (?,?,?,?,?,?,?,?,?,?,?)')
                    ->execute([...array_values($d), usuario_id()]);
                auditar('crear', 'entregas', (int)$pdo->lastInsertId(), "{$d['sector']} · {$fecha}");
                flash('success', 'Entrega programada.');
            }
        } elseif ($accion === 'estado' && $id) {
            $nuevo = post('estado');
            if (!isset(ESTADOS_ENTREGA[$nuevo])) throw new RuntimeException('Estado inválido.');
            $pdo->prepare('UPDATE entregas SET estado = ? WHERE id = ?')->execute([$nuevo, $id]);
            auditar('cambiar_estado', 'entregas', $id, $nuevo);
            flash('success', 'Estado cambiado a «' . ESTADOS_ENTREGA[$nuevo] . '».');
        } elseif ($accion === 'duplicar' && $id) {
            // Copia la entrega a la semana siguiente (útil para rutas que se repiten)
            $pdo->prepare("INSERT INTO entregas (fecha_entrega, hora, sector, direccion, cantidad_bolsas, producto_id, responsable, telefono, estado, notas, creado_por)
                           SELECT fecha_entrega + INTERVAL 7 DAY, hora, sector, direccion, cantidad_bolsas, producto_id, responsable, telefono, 'programada', notas, ? FROM entregas WHERE id = ?")
                ->execute([usuario_id(), $id]);
            auditar('duplicar', 'entregas', (int)$pdo->lastInsertId(), "copia de #$id (+7 días)");
            flash('success', 'Entrega copiada a la semana siguiente.');
        } elseif ($accion === 'eliminar' && $id) {
            $pdo->prepare('DELETE FROM entregas WHERE id = ?')->execute([$id]);
            auditar('eliminar', 'entregas', $id);
            flash('success', 'Entrega eliminada.');
        }
    } catch (PDOException $ex) {
        flash('danger', 'Error de base de datos.');
        error_log($ex->getMessage());
    } catch (RuntimeException $ex) {
        flash('danger', $ex->getMessage());
    }
    redirect($volver);
}

$productos = $pdo->query("SELECT id, nombre, tipo FROM productos WHERE activo = 1 ORDER BY tipo, nombre")->fetchAll();

// Por defecto: la semana actual, ordenada por fecha ascendente (lo próximo primero)
$f = filtro_fechas('semana');
$where = [];
$params = [];
aplicar_fechas($f, 'e.fecha_entrega', $where, $params, true);
$q = trim((string)($_GET['q'] ?? ''));
if ($q !== '') {
    $where[] = '(e.sector LIKE :q1 OR e.direccion LIKE :q2 OR e.responsable LIKE :q3)';
    $params['q1'] = $params['q2'] = $params['q3'] = "%$q%";
}
// Guardar condiciones base para el resumen de KPIs antes de aplicar el filtro específico por estado
$whereKpi = $where;
$paramsKpi = $params;

$estF = $_GET['estado'] ?? '';
if (isset(ESTADOS_ENTREGA[$estF])) { 
    $where[] = 'e.estado = :est'; 
    $params['est'] = $estF; 
}

$res = paginar_cursor([
    'select' => 'SELECT e.*, p.nombre AS producto, p.tipo AS producto_tipo FROM entregas e LEFT JOIN productos p ON p.id = e.producto_id',
    'where' => $where, 'params' => $params,
    'col' => 'e.fecha_entrega', 'key' => 'fecha_entrega', 'id' => 'e.id',
    'orden' => 'ASC',
]);

// Resumen del periodo filtrado (sin limitar por estado para que los 4 KPIs reflejen siempre los datos reales del backend)
$sqlRes = 'SELECT e.estado, COUNT(*) n, COALESCE(SUM(e.cantidad_bolsas),0) bolsas FROM entregas e'
    . ($whereKpi ? ' WHERE ' . implode(' AND ', $whereKpi) : '') . ' GROUP BY e.estado';
$st = $pdo->prepare($sqlRes);
$st->execute($paramsKpi);
$resumen = array_fill_keys(array_keys(ESTADOS_ENTREGA), ['n' => 0, 'bolsas' => 0]);
foreach ($st->fetchAll() as $r) $resumen[$r['estado']] = $r;

$titulo = 'Entregas de bolsas';
$seccion = 'entregas';
$qsActual = qs();
ob_start(); ?>
<div class="col-12 col-md-4"><label class="form-label small mb-0 fw-semibold text-slate-700">Buscar</label><input type="search" name="q" value="<?= e($q) ?>" class="form-control form-control-sm" placeholder="Sector, dirección o responsable..."></div>
<div class="col-6 col-md-auto"><label class="form-label small mb-0 fw-semibold text-slate-700">Estado</label>
    <select name="estado" class="form-select form-select-sm"><option value="">Todos los estados</option><?php foreach (ESTADOS_ENTREGA as $k => $v): ?><option value="<?= $k ?>"<?= $estF === $k ? ' selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></div>
<?php $extra = ob_get_clean();
require __DIR__ . '/layout/header.php';
?>
<div class="row g-3 mb-4">
    <?php foreach (ESTADOS_ENTREGA as $k => $v): 
        $isSelected = ($estF === $k);
        $toggleUrl = $isSelected ? qs(['estado' => null, 'cursor' => null, 'dir' => null]) : qs(['estado' => $k, 'cursor' => null, 'dir' => null]);
        $icons = [
            'programada' => 'bi-calendar-event',
            'en_camino'   => 'bi-truck',
            'entregada'   => 'bi-check2-circle',
            'cancelada'   => 'bi-x-circle'
        ];
    ?>
        <div class="col-6 col-lg-3">
            <a class="card kpi-card-centered text-decoration-none h-100 <?= $isSelected ? 'kpi-selected' : '' ?> estado-card-<?= $k ?>" href="<?= e($toggleUrl) ?>">
                <div class="card-body p-3 p-xl-4 text-center d-flex flex-column align-items-center justify-content-center">
                    <div class="kpi-icon-pill mb-2.5 estado-icon-<?= $k ?>">
                        <i class="bi <?= $icons[$k] ?>"></i>
                    </div>
                    <div class="kpi-centered-number mb-1"><?= (int)$resumen[$k]['n'] ?></div>
                    <div class="kpi-status-title fw-bold"><?= $v ?></div>
                    <div class="kpi-sub-bolsas small text-muted mt-1">
                        <i class="bi bi-box-seam me-1 text-secondary"></i><?= number_format((float)$resumen[$k]['bolsas'], 0, ',', '.') ?> bolsas
                    </div>
                    <?php if ($isSelected): ?>
                        <span class="badge bg-dark text-white border mt-2 text-[10px] py-1 px-2 rounded-pill">
                            <i class="bi bi-check2 me-1 text-success"></i>Filtro activo (quitar)
                        </span>
                    <?php endif; ?>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
        <div><h2 class="h6 fw-bold mb-0">Cronograma de Distribución y Rutas</h2><small class="text-secondary">Usa las flechas para explorar semanas anteriores o siguientes.</small></div>
        <?php if (puede_editar()): ?><button class="btn btn-primary btn-sm" data-modal-new="#modalEntrega" data-title="Programar entrega"><i class="bi bi-plus-lg"></i> Programar entrega</button><?php endif; ?>
    </div>
    <?= render_filtros($f, $extra) ?>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th>Fecha / Hora</th><th>Sector y Dirección</th><th>Bolsas Asignadas</th><th>Responsable Comunal</th><th>Estado</th><?php if (puede_editar()): ?><th class="text-end">Acciones</th><?php endif; ?></tr></thead>
            <tbody>
            <?php if (!$res['filas']): ?><tr><td colspan="6"><div class="empty"><i class="bi bi-truck"></i>No hay entregas registradas en este periodo.</div></td></tr><?php endif; ?>
            <?php
            $dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
            foreach ($res['filas'] as $r): $ts = strtotime($r['fecha_entrega']); ?>
                <tr<?= $r['fecha_entrega'] === date('Y-m-d') ? ' class="table-info"' : '' ?>>
                    <td class="text-nowrap">
                        <div class="fw-bold text-slate-800 d-inline-flex align-items-center gap-1.5"><i class="bi bi-calendar-event text-emerald-600"></i><?= $dias[(int)date('w', $ts)] ?> <?= date('d/m', $ts) ?></div>
                        <div class="small text-muted ps-4"><?= date('Y', $ts) ?><?= $r['hora'] ? ' · <span class="d-inline-flex align-items-center gap-1"><i class="bi bi-clock text-secondary"></i>' . e(substr($r['hora'], 0, 5)) . '</span>' : '' ?></div>
                    </td>
                    <td>
                        <div class="fw-bold text-slate-900"><?= e($r['sector']) ?></div>
                        <div class="small text-muted"><?= e($r['direccion']) ?></div>
                        <?= $r['notas'] ? '<div class="small fst-italic text-secondary mt-0.5"><i class="bi bi-sticky me-1"></i>' . e(mb_strimwidth($r['notas'], 0, 80, '…')) . '</div>' : '' ?>
                    </td>
                    <td class="text-nowrap">
                        <span class="fs-6 fw-bold text-slate-900"><?= (int)$r['cantidad_bolsas'] ?></span>
                        <div class="small text-muted"><?= e($r['producto'] ?? 'Sin especificar') ?></div>
                    </td>
                    <td>
                        <div class="fw-semibold text-slate-800"><?= e($r['responsable']) ?></div>
                        <?= $r['telefono'] ? '<div class="small"><a href="tel:' . e($r['telefono']) . '" class="text-decoration-none text-muted hover:text-primary"><i class="bi bi-telephone me-1"></i>' . e($r['telefono']) . '</a></div>' : '' ?>
                    </td>
                    <td>
                        <?php if (puede_editar()): ?>
                            <form method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="accion" value="estado"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="_qs" value="<?= e($qsActual) ?>">
                                <select name="estado" class="form-select form-select-sm badge-estado estado-<?= e($r['estado']) ?> border-0" style="width:auto" onchange="this.form.submit()" aria-label="Estado">
                                    <?php foreach (ESTADOS_ENTREGA as $k => $v): ?><option value="<?= $k ?>"<?= $r['estado'] === $k ? ' selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
                                </select></form>
                        <?php else: ?>
                            <span class="badge-estado estado-<?= e($r['estado']) ?>"><?= e(ESTADOS_ENTREGA[$r['estado']]) ?></span>
                        <?php endif; ?>
                    </td>
                    <?php if (puede_editar()): ?>
                    <td class="text-end text-nowrap">
                        <div class="d-flex justify-content-end gap-1.5">
                            <button class="btn-action" title="Editar" data-modal-edit="#modalEntrega" data-title="Editar entrega" data-row='<?= e(json_encode($r)) ?>'><i class="bi bi-pencil"></i></button>
                            <form method="post" class="d-inline" data-confirm="¿Copiar esta entrega a la semana siguiente?"><?= csrf_field() ?><input type="hidden" name="accion" value="duplicar"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="_qs" value="<?= e($qsActual) ?>">
                                <button class="btn-action" title="Copiar a la próxima semana"><i class="bi bi-copy"></i></button></form>
                            <form method="post" class="d-inline" data-confirm="¿Eliminar la entrega a <?= e($r['sector']) ?>?"><?= csrf_field() ?><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="_qs" value="<?= e($qsActual) ?>">
                                <button class="btn-action btn-action-danger" title="Eliminar"><i class="bi bi-trash"></i></button></form>
                        </div>
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
<div class="modal fade" id="modalEntrega" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" method="post" data-defaults='<?= e(json_encode(['fecha_entrega' => date('Y-m-d'), 'estado' => 'programada', 'cantidad_bolsas' => 0])) ?>'>
            <div class="modal-header"><h5 class="modal-title">Entrega</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <?= csrf_field() ?><input type="hidden" name="accion" value="guardar"><input type="hidden" name="id"><input type="hidden" name="_qs" value="<?= e($qsActual) ?>">
                <div class="row g-3">
                    <div class="col-6 col-md-4"><label class="form-label">Fecha *</label><input type="date" name="fecha_entrega" class="form-control" required></div>
                    <div class="col-6 col-md-3"><label class="form-label">Hora</label><input type="time" name="hora" class="form-control"></div>
                    <div class="col-md-5"><label class="form-label">Estado</label><select name="estado" class="form-select"><?php foreach (ESTADOS_ENTREGA as $k => $v): ?><option value="<?= $k ?>"><?= $v ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-5"><label class="form-label">Sector / comunidad *</label><input name="sector" class="form-control" required maxlength="150"></div>
                    <div class="col-md-7"><label class="form-label">Dirección *</label><input name="direccion" class="form-control" required maxlength="255"></div>
                    <div class="col-5 col-md-3"><label class="form-label">Cantidad de bolsas *</label><input type="number" min="0" name="cantidad_bolsas" class="form-control" required></div>
                    <div class="col-7 col-md-4"><label class="form-label">Bolsa / combo</label><select name="producto_id" class="form-select"><option value="">Sin especificar</option><?php foreach ($productos as $p): ?><option value="<?= $p['id'] ?>"><?= e($p['nombre']) ?> (<?= e($p['tipo']) ?>)</option><?php endforeach; ?></select></div>
                    <div class="col-md-5"><label class="form-label">Responsable *</label><input name="responsable" class="form-control" required maxlength="150"></div>
                    <div class="col-md-4"><label class="form-label">Teléfono</label><input name="telefono" class="form-control" maxlength="40"></div>
                    <div class="col-md-8"><label class="form-label">Notas</label><input name="notas" class="form-control"></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
        </form>
    </div>
</div>
<?php endif; ?>
<?php require __DIR__ . '/layout/footer.php'; ?>
