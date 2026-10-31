<?php
require __DIR__ . '/../includes/app.php';
requiere_login();

if (tiene_rol('atencion')) {
    redirect(url('admin/chat.php'));
}

$titulo = 'Dashboard';
$seccion = 'index';

$pdo = db();
$hoy = date('Y-m-d');
$lunes = date('Y-m-d', strtotime('monday this week'));
$domingo = date('Y-m-d', strtotime('sunday this week'));

$kpi = $pdo->prepare(
    "SELECT
        (SELECT COUNT(*) FROM productos WHERE activo = 1 AND tipo = 'bolsa') AS bolsas,
        (SELECT COUNT(*) FROM productos WHERE activo = 1 AND tipo = 'combo') AS combos,
        (SELECT COUNT(*) FROM rubros WHERE activo = 1) AS rubros,
        (SELECT COUNT(*) FROM entregas WHERE fecha_entrega BETWEEN :l1 AND :d1 AND estado <> 'cancelada') AS entregas_semana,
        (SELECT COALESCE(SUM(cantidad_bolsas),0) FROM entregas WHERE fecha_entrega BETWEEN :l2 AND :d2 AND estado <> 'cancelada') AS bolsas_semana,
        (SELECT COALESCE(SUM(cantidad_bolsas),0) FROM entregas WHERE fecha_entrega BETWEEN :l3 AND :d3 AND estado = 'entregada') AS bolsas_entregadas,
        (SELECT COUNT(*) FROM comunicaciones_productores WHERE estado = 'pendiente') AS com_pendientes"
);
$kpi->execute(['l1' => $lunes, 'd1' => $domingo, 'l2' => $lunes, 'd2' => $domingo, 'l3' => $lunes, 'd3' => $domingo]);
$k = $kpi->fetch();

$st = $pdo->prepare(
    "SELECT e.*, p.nombre AS producto FROM entregas e LEFT JOIN productos p ON p.id = e.producto_id
     WHERE e.fecha_entrega BETWEEN ? AND ? ORDER BY e.fecha_entrega, e.hora, e.id"
);
$st->execute([$lunes, $domingo]);
$entregasSemana = $st->fetchAll();

$productos = $pdo->query(
    "SELECT nombre, tipo, precio, moneda FROM productos WHERE activo = 1 ORDER BY tipo, precio LIMIT 8"
)->fetchAll();

$progreso = $k['bolsas_semana'] > 0 ? round($k['bolsas_entregadas'] * 100 / $k['bolsas_semana']) : 0;
$estados = ['programada' => 'Programada', 'en_camino' => 'En camino', 'entregada' => 'Entregada', 'cancelada' => 'Cancelada'];

require __DIR__ . '/layout/header.php';
?>
<?php if (!empty($k['com_pendientes']) && $k['com_pendientes'] > 0): ?>
    <div class="alert alert-warning d-flex align-items-center justify-content-between p-3 mb-4 rounded-3 shadow-sm border-warning-subtle" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-bell-fill fs-4 text-warning"></i>
            <div>
                <strong>Tienes <?= (int)$k['com_pendientes'] ?> comunicación(es) pendiente(s) de productores</strong> (ofertas de rubro, requerimientos o sugerencias).
            </div>
        </div>
        <a href="<?= e(url('admin/productores.php?estado=pendiente')) ?>" class="btn btn-sm btn-warning text-dark fw-bold">
            Ver y Responder <i class="bi bi-arrow-right"></i>
        </a>
    </div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <?php foreach ([
        ['Bolsas activas', $k['bolsas'], 'bi-bag-heart', '#eff6ff', '#2563eb', 'Catálogo oficial de bolsas'],
        ['Combos activos', $k['combos'], 'bi-box-seam', '#f5f3ff', '#7c3aed', 'Combos nutricionales activos'],
        ['Entregas esta semana', $k['entregas_semana'], 'bi-truck', '#fffbeb', '#d97706', 'Rutas de distribución'],
        ['Bolsas a repartir', number_format((float)$k['bolsas_semana'], 0, ',', '.'), 'bi-basket', '#ecfdf5', '#059669', 'Meta semanal programada'],
    ] as [$lbl, $val, $ico, $bg, $fg, $sub]): ?>
        <div class="col-6 col-xl-3">
            <div class="card kpi-card-centered h-100">
                <div class="card-body p-3 p-xl-4 text-center d-flex flex-column align-items-center justify-content-center">
                    <div class="kpi-icon-pill mb-2.5" style="background:<?= $bg ?>;color:<?= $fg ?>">
                        <i class="bi <?= $ico ?>"></i>
                    </div>
                    <div class="kpi-centered-number mb-1"><?= e($val) ?></div>
                    <div class="kpi-status-title fw-bold"><?= e($lbl) ?></div>
                    <div class="kpi-sub-bolsas small text-muted mt-1"><?= e($sub) ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-3">
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between py-3">
                <div>
                    <h2 class="h6 fw-bold mb-0">Entregas de la semana</h2>
                    <small class="text-secondary"><?= fecha_corta($lunes) ?> – <?= fecha_corta($domingo) ?></small>
                </div>
                <a href="<?= e(url('admin/entregas.php?rango=semana')) ?>" class="btn btn-sm btn-outline-primary">Ver todas</a>
            </div>
            <div class="px-3 pt-3">
                <div class="d-flex justify-content-between small mb-1"><span>Progreso de bolsas entregadas</span><strong><?= $progreso ?>%</strong></div>
                <div class="progress mb-3" style="height:8px"><div class="progress-bar bg-success" style="width:<?= $progreso ?>%"></div></div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead><tr><th>Día</th><th>Sector</th><th>Bolsas</th><th>Responsable</th><th>Estado</th></tr></thead>
                    <tbody>
                    <?php if (!$entregasSemana): ?>
                        <tr><td colspan="5"><div class="empty"><i class="bi bi-calendar-x"></i>No hay entregas programadas esta semana.</div></td></tr>
                    <?php endif; ?>
                    <?php foreach ($entregasSemana as $en): ?>
                        <tr<?= $en['fecha_entrega'] === $hoy ? ' class="table-info"' : '' ?>>
                            <td class="text-nowrap">
                                <span class="d-inline-flex align-items-center gap-1.5"><i class="bi bi-calendar3 text-emerald-600"></i><?= e(fecha_corta($en['fecha_entrega'])) ?></span>
                                <?= $en['hora'] ? ' <small class="text-secondary ps-1.5"><i class="bi bi-clock me-1"></i>' . e(substr($en['hora'], 0, 5)) . '</small>' : '' ?>
                            </td>
                            <td><strong><?= e($en['sector']) ?></strong><div class="small text-secondary text-truncate" style="max-width:240px"><?= e($en['direccion']) ?></div></td>
                            <td><?= (int)$en['cantidad_bolsas'] ?> <small class="text-secondary"><?= e($en['producto'] ?? '') ?></small></td>
                            <td><?= e($en['responsable']) ?></td>
                            <td><span class="badge-estado estado-<?= e($en['estado']) ?>"><?= e($estados[$en['estado']]) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between py-3">
                <h2 class="h6 fw-bold mb-0">Precios vigentes</h2>
                <a href="<?= e(url('admin/productos.php')) ?>" class="btn btn-sm btn-outline-primary">Gestionar</a>
            </div>
            <ul class="list-group list-group-flush">
                <?php if (!$productos): ?>
                    <li class="list-group-item"><div class="empty"><i class="bi bi-bag"></i>Aún no hay bolsas ni combos.</div></li>
                <?php endif; ?>
                <?php foreach ($productos as $p): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><span class="badge text-bg-light border me-1"><?= e(ucfirst($p['tipo'])) ?></span><?= e($p['nombre']) ?></span>
                        <strong><?= e(dinero($p['precio'], $p['moneda'])) ?></strong>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
<?php require __DIR__ . '/layout/footer.php'; ?>
