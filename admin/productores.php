<?php
require __DIR__ . '/../includes/app.php';
requiere_login();
$pdo = db();

const TIPOS_COM = [
    'oferta_rubro'           => ['Oferta de rubros / cosecha', 'bi-basket2-fill', '#198754', 'bg-success-subtle text-success-emphasis border border-success-subtle'],
    'requerimiento_tecnico'  => ['Requerimiento técnico / logístico', 'bi-tools', '#d97706', 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'],
    'sugerencia_observacion' => ['Sugerencia u observación', 'bi-chat-dots-fill', '#0dcaf0', 'bg-info-subtle text-info-emphasis border border-info-subtle'],
    'otro'                   => ['Otra consulta', 'bi-envelope-paper-fill', '#6c757d', 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle'],
];

const ESTADOS_COM = [
    'pendiente'   => ['Pendiente', 'warning', 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'],
    'en_revision' => ['En revisión', 'info', 'bg-info-subtle text-info-emphasis border border-info-subtle'],
    'atendido'    => ['Atendido', 'success', 'bg-success-subtle text-success-emphasis border border-success-subtle'],
    'archivado'   => ['Archivado', 'secondary', 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle'],
];

// Procesamiento de acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requiere_escritura();
    $accion = post('accion');
    $id = (int)post('id', 0);
    $volver = volver_a('admin/productores.php');

    try {
        if ($accion === 'actualizar' && $id) {
            $estado = post('estado');
            $respuesta = post('respuesta_interna');
            if (!isset(ESTADOS_COM[$estado])) throw new RuntimeException('Estado no válido.');

            $pdo->prepare(
                'UPDATE comunicaciones_productores
                 SET estado = ?, respuesta_interna = ?, atendido_por = ?, atendido_en = NOW()
                 WHERE id = ?'
            )->execute([$estado, $respuesta ?: null, usuario_id(), $id]);

            auditar('atender', 'comunicaciones_productores', $id, "Estado: $estado");
            flash('success', 'Registro de comunicación actualizado con éxito.');
        } elseif ($accion === 'estado_rapido' && $id) {
            $nuevo = post('estado');
            if (!isset(ESTADOS_COM[$nuevo])) throw new RuntimeException('Estado inválido.');
            $pdo->prepare('UPDATE comunicaciones_productores SET estado = ?, atendido_por = ?, atendido_en = NOW() WHERE id = ?')
                ->execute([$nuevo, usuario_id(), $id]);
            auditar('cambiar_estado', 'comunicaciones_productores', $id, $nuevo);
            flash('success', 'Estado cambiado a «' . ESTADOS_COM[$nuevo][0] . '».');
        } elseif ($accion === 'eliminar' && $id) {
            $st = $pdo->prepare('SELECT adjunto FROM comunicaciones_productores WHERE id = ?');
            $st->execute([$id]);
            $adj = $st->fetchColumn();
            $pdo->prepare('DELETE FROM comunicaciones_productores WHERE id = ?')->execute([$id]);
            if ($adj) borrar_archivo($adj);
            auditar('eliminar', 'comunicaciones_productores', $id);
            flash('success', 'Mensaje de comunicación eliminado.');
        }
    } catch (Throwable $ex) {
        flash('danger', $ex instanceof RuntimeException ? $ex->getMessage() : 'Error al procesar.');
        if (!$ex instanceof RuntimeException) error_log($ex->getMessage());
    }
    redirect($volver);
}

// Filtros y paginación
$f = filtro_fechas('todo');
$where = [];
$params = [];
aplicar_fechas($f, 'cp.creado_en', $where, $params);

$q = trim((string)($_GET['q'] ?? ''));
if ($q !== '') {
    $where[] = '(cp.nombre LIKE :q1 OR cp.sector_comunidad LIKE :q2 OR cp.telefono LIKE :q3 OR cp.asunto LIKE :q4 OR cp.mensaje LIKE :q5)';
    $params['q1'] = $params['q2'] = $params['q3'] = $params['q4'] = $params['q5'] = "%$q%";
}

$tipoF = $_GET['tipo'] ?? '';
if (isset(TIPOS_COM[$tipoF])) {
    $where[] = 'cp.tipo = :tipo';
    $params['tipo'] = $tipoF;
}

$estF = $_GET['estado'] ?? '';
if (isset(ESTADOS_COM[$estF])) {
    $where[] = 'cp.estado = :est';
    $params['est'] = $estF;
}

$res = paginar_cursor([
    'select' => 'SELECT cp.*, u.nombre AS atendido_nombre
                 FROM comunicaciones_productores cp
                 LEFT JOIN usuarios u ON u.id = cp.atendido_por',
    'where'  => $where,
    'params' => $params,
    'col'    => 'cp.creado_en',
    'key'    => 'creado_en',
    'id'     => 'cp.id',
    'orden'  => 'DESC',
]);

// Resumen de KPIs (totales globales para métricas)
$kpiSql = "SELECT
    COUNT(*) AS total,
    SUM(estado = 'pendiente') AS pendientes,
    SUM(tipo = 'oferta_rubro') AS ofertas,
    SUM(tipo = 'requerimiento_tecnico') AS tecnicos
    FROM comunicaciones_productores";
$kpi = $pdo->query($kpiSql)->fetch();

$titulo = 'Atención a UPF y Productores (Literal G)';
$seccion = 'productores';
$qsActual = qs();

ob_start(); ?>
<div class="col-12 col-md-3">
    <label class="form-label small mb-1 fw-semibold text-muted" style="font-size:0.72rem">Buscar</label>
    <input type="search" name="q" value="<?= e($q) ?>" class="form-control form-control-sm" placeholder="Nombre, sector, teléfono...">
</div>
<div class="col-6 col-md-auto">
    <label class="form-label small mb-1 fw-semibold text-muted" style="font-size:0.72rem">Tipo de Gestión</label>
    <select name="tipo" class="form-select form-select-sm">
        <option value="">Todos los tipos</option>
        <?php foreach (TIPOS_COM as $k => [$nom]): ?>
            <option value="<?= $k ?>"<?= $tipoF === $k ? ' selected' : '' ?>><?= e($nom) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div class="col-6 col-md-auto">
    <label class="form-label small mb-1 fw-semibold text-muted" style="font-size:0.72rem">Estado</label>
    <select name="estado" class="form-select form-select-sm">
        <option value="">Todos los estados</option>
        <?php foreach (ESTADOS_COM as $k => [$nom]): ?>
            <option value="<?= $k ?>"<?= $estF === $k ? ' selected' : '' ?>><?= e($nom) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<?php $extra = ob_get_clean();

require __DIR__ . '/layout/header.php';
?>

<!-- KPIs superiores centrados e interactivos -->
<div class="row g-3 mb-4" id="kpiProductoresWrap">
    <!-- Total -->
    <?php $isSelTotal = ($tipoF === '' && $estF === ''); ?>
    <div class="col-6 col-xl-3">
        <a class="card kpi-card-centered text-decoration-none h-100 <?= $isSelTotal ? 'kpi-selected' : '' ?>" href="<?= e(qs(['tipo' => null, 'estado' => null, 'cursor' => null, 'p' => null])) ?>">
            <div class="card-body p-3 p-xl-4 text-center d-flex flex-column align-items-center justify-content-center">
                <div class="kpi-icon-pill mb-2.5" style="background: #e0f2fe; color: #0284c7;">
                    <i class="bi bi-inbox-fill"></i>
                </div>
                <div class="kpi-centered-number mb-1"><?= (int)($kpi['total'] ?? 0) ?></div>
                <div class="kpi-status-title fw-bold d-flex align-items-center justify-content-center">
                    <span>Comunicaciones totales</span>
                    <button type="button" class="btn-tour-hint" onclick="event.preventDefault(); TourGuia.mostrarPaso('kpis_productores')" title="¿Cómo funcionan los KPIs?">?</button>
                </div>
                <div class="kpi-sub-bolsas small text-slate-500 mt-1">
                    Historial registrado
                </div>
            </div>
        </a>
    </div>

    <!-- Pendientes -->
    <?php $isSelPend = ($estF === 'pendiente'); ?>
    <div class="col-6 col-xl-3">
        <a class="card kpi-card-centered text-decoration-none h-100 <?= $isSelPend ? 'kpi-selected' : '' ?>" href="<?= e(qs(['estado' => $isSelPend ? null : 'pendiente', 'cursor' => null, 'p' => null])) ?>">
            <div class="card-body p-3 p-xl-4 text-center d-flex flex-column align-items-center justify-content-center">
                <div class="kpi-icon-pill mb-2.5" style="background: #fef3c7; color: #d97706;">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="kpi-centered-number mb-1"><?= (int)($kpi['pendientes'] ?? 0) ?></div>
                <div class="kpi-status-title fw-bold">Pendientes por responder</div>
                <div class="kpi-sub-bolsas small text-slate-500 mt-1">
                    <?= $isSelPend ? '<span class="text-amber-700 fw-bold"><i class="bi bi-funnel-fill me-1"></i>Filtro activo</span>' : 'Requieren atención' ?>
                </div>
            </div>
        </a>
    </div>

    <!-- Ofertas de cosecha -->
    <?php $isSelOferta = ($tipoF === 'oferta_rubro'); ?>
    <div class="col-6 col-xl-3">
        <a class="card kpi-card-centered text-decoration-none h-100 <?= $isSelOferta ? 'kpi-selected' : '' ?>" href="<?= e(qs(['tipo' => $isSelOferta ? null : 'oferta_rubro', 'cursor' => null, 'p' => null])) ?>">
            <div class="card-body p-3 p-xl-4 text-center d-flex flex-column align-items-center justify-content-center">
                <div class="kpi-icon-pill mb-2.5" style="background: #ecfdf5; color: #059669;">
                    <i class="bi bi-basket2-fill"></i>
                </div>
                <div class="kpi-centered-number mb-1"><?= (int)($kpi['ofertas'] ?? 0) ?></div>
                <div class="kpi-status-title fw-bold">Ofertas de rubros</div>
                <div class="kpi-sub-bolsas small text-slate-500 mt-1">
                    <?= $isSelOferta ? '<span class="text-success fw-bold"><i class="bi bi-funnel-fill me-1"></i>Filtro activo</span>' : 'Arrime y cosechas' ?>
                </div>
            </div>
        </a>
    </div>

    <!-- Requerimientos técnicos -->
    <?php $isSelTec = ($tipoF === 'requerimiento_tecnico'); ?>
    <div class="col-6 col-xl-3">
        <a class="card kpi-card-centered text-decoration-none h-100 <?= $isSelTec ? 'kpi-selected' : '' ?>" href="<?= e(qs(['tipo' => $isSelTec ? null : 'requerimiento_tecnico', 'cursor' => null, 'p' => null])) ?>">
            <div class="card-body p-3 p-xl-4 text-center d-flex flex-column align-items-center justify-content-center">
                <div class="kpi-icon-pill mb-2.5" style="background: #ede9fe; color: #7c3aed;">
                    <i class="bi bi-tools"></i>
                </div>
                <div class="kpi-centered-number mb-1"><?= (int)($kpi['tecnicos'] ?? 0) ?></div>
                <div class="kpi-status-title fw-bold">Requerimientos técnicos</div>
                <div class="kpi-sub-bolsas small text-slate-500 mt-1">
                    <?= $isSelTec ? '<span class="text-purple-700 fw-bold"><i class="bi bi-funnel-fill me-1"></i>Filtro activo</span>' : 'Soporte y logística' ?>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="card shadow-sm border" id="tablaProductoresWrap" style="border-color: #e2e8f0; border-radius: 16px; overflow: hidden;">
    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3 px-3 px-md-4 border-bottom">
        <div>
            <h2 class="h6 fw-bold mb-0 text-slate-800 d-flex align-items-center">
                <span>Comunicaciones e Intercambio con Productores</span>
                <button type="button" class="btn-tour-hint" onclick="TourGuia.mostrarPaso('tabla_productores')" title="¿Cómo gestionar las comunicaciones?">?</button>
            </h2>
            <small class="text-slate-500">Cumplimiento del <strong>Literal G del Objeto</strong> y los derechos estatutarios del productor comunal.</small>
        </div>
    </div>

    <?= render_filtros($f, $extra) ?>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-3 ps-md-4">Fecha</th>
                    <th>Productor / Sector</th>
                    <th>Tipo</th>
                    <th>Asunto y Mensaje</th>
                    <th>WhatsApp</th>
                    <th style="min-width: 145px;">Estado</th>
                    <?php if (puede_editar()): ?>
                    <th class="text-end pe-3 pe-md-4" style="min-width: 110px;">
                        <span>Acciones</span>
                        <button type="button" class="btn-tour-hint" onclick="TourGuia.mostrarPaso('detalle_seguimiento')" title="¿Cómo atender una solicitud?">?</button>
                    </th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php if (!$res['filas']): ?>
                <tr>
                    <td colspan="7" class="py-5">
                        <div class="empty text-center py-4">
                            <i class="bi bi-chat-square-text fs-2 text-slate-400 d-block mb-2"></i>
                            <div class="fw-semibold text-slate-700">No hay comunicaciones registradas con estos filtros.</div>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>

            <?php foreach ($res['filas'] as $r):
                $tipoInfo = TIPOS_COM[$r['tipo']] ?? ['Desconocido', 'bi-question', '#64748b', 'bg-slate-100 text-slate-700 border border-slate-200'];
                $estInfo = ESTADOS_COM[$r['estado']] ?? ['Desconocido', 'secondary', 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle'];
                $telLimpio = preg_replace('/[^0-9]/', '', $r['telefono']);
                $ts = strtotime($r['creado_en']);
                // Mensaje sugerido para responder por WhatsApp
                $msgWa = "Hola " . $r['nombre'] . ", le saludamos de La Nueva Esperanza (EPSDC). Con respecto a su comunicación sobre '" . $r['asunto'] . "': ";
                $linkWa = "https://wa.me/" . $telLimpio . "?text=" . urlencode($msgWa);
            ?>
                <tr>
                    <td class="text-nowrap ps-3 ps-md-4">
                        <div class="fw-semibold text-slate-800 d-inline-flex align-items-center" style="font-size: 0.82rem;">
                            <i class="bi bi-calendar3 text-success me-2"></i><?= date('d/m/Y', $ts) ?>
                        </div>
                        <div class="small text-muted d-inline-flex align-items-center ps-4" style="font-size: 0.72rem;">
                            <i class="bi bi-clock text-secondary me-1.5"></i><?= date('H:i', $ts) ?>
                        </div>
                    </td>
                    <td>
                        <div class="fw-bold text-slate-900"><?= e($r['nombre']) ?></div>
                        <div class="small text-muted d-inline-flex align-items-center mt-0.5">
                            <i class="bi bi-geo-alt text-danger me-1.5"></i><?= e($r['sector_comunidad']) ?>
                        </div>
                    </td>
                    <td>
                        <span class="badge <?= $tipoInfo[3] ?> rounded-pill px-2.5 py-1 d-inline-flex align-items-center fw-medium" style="font-size: 0.74rem;">
                            <i class="bi <?= $tipoInfo[1] ?> me-1.5"></i><?= e($tipoInfo[0]) ?>
                        </span>
                    </td>
                    <td style="max-width: 320px;">
                        <div class="fw-semibold text-slate-800 text-truncate"><?= e($r['asunto']) ?></div>
                        <div class="small text-muted text-truncate"><?= e($r['mensaje']) ?></div>
                        <?php if ($r['adjunto']): ?>
                            <a href="<?= e(url($r['adjunto'])) ?>" target="_blank" class="badge bg-light text-dark border text-decoration-none mt-1 d-inline-flex align-items-center py-1 px-2.5 rounded-pill shadow-2xs">
                                <i class="bi bi-paperclip text-secondary me-1.5"></i>Ver adjunto
                            </a>
                        <?php endif; ?>
                    </td>
                    <td class="text-nowrap">
                        <a href="<?= e($linkWa) ?>" target="_blank" class="btn btn-sm btn-outline-success rounded-pill d-inline-flex align-items-center px-2.5 py-1 fw-semibold shadow-2xs" style="font-size: 0.75rem;">
                            <i class="bi bi-whatsapp me-1.5"></i><?= e($r['telefono']) ?>
                        </a>
                    </td>
                    <td>
                        <?php if (puede_editar()): ?>
                            <form method="post" class="d-inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="accion" value="estado_rapido">
                                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                <input type="hidden" name="_qs" value="<?= e($qsActual) ?>">
                                <select name="estado" class="form-select form-select-sm rounded-pill fw-semibold <?= $estInfo[2] ?>" style="font-size: 0.75rem; min-width: 135px; width: 135px; padding-top: 0.25rem; padding-bottom: 0.25rem;" onchange="this.form.submit()">
                                    <?php foreach (ESTADOS_COM as $k => [$nom]): ?>
                                        <option value="<?= $k ?>"<?= $r['estado'] === $k ? ' selected' : '' ?>><?= e($nom) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        <?php else: ?>
                            <span class="badge rounded-pill <?= $estInfo[2] ?>"><?= e($estInfo[0]) ?></span>
                        <?php endif; ?>
                    </td>
                    <?php if (puede_editar()): ?>
                    <td class="text-end text-nowrap pe-3 pe-md-4">
                        <button class="btn btn-sm btn-light border shadow-2xs me-1 btn-tour-detalle-first" title="Ver detalles y gestionar" data-bs-toggle="modal" data-bs-target="#modalDetalle<?= $r['id'] ?>">
                            <i class="bi bi-eye text-slate-700"></i>
                        </button>
                        <form method="post" class="d-inline" data-confirm="¿Eliminar esta comunicación de <?= e($r['nombre']) ?>?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                            <input type="hidden" name="_qs" value="<?= e($qsActual) ?>">
                            <button class="btn btn-sm btn-light border shadow-2xs text-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                    <?php endif; ?>
                </tr>

                <!-- Modal de Detalle y Seguimiento -->
                <div class="modal fade" id="modalDetalle<?= $r['id'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content border-0 shadow-xl" style="border-radius: 20px; overflow: hidden; background: #ffffff;">
                            
                            <!-- Modal Header con estilo editorial y badge -->
                            <div class="modal-header bg-white py-3.5 px-4 border-bottom d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width: 44px; height: 44px; background: <?= $tipoInfo[2] ?>15; color: <?= $tipoInfo[2] ?>;">
                                        <i class="bi <?= $tipoInfo[1] ?> fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-0.5">
                                            <h5 class="modal-title fw-bold text-slate-900 mb-0 fs-5">Gestión de Comunicación #<?= $r['id'] ?></h5>
                                            <span class="badge <?= $tipoInfo[3] ?> rounded-pill px-2.5 py-1 text-xs fw-semibold">
                                                <i class="bi <?= $tipoInfo[1] ?> me-1"></i><?= e($tipoInfo[0]) ?>
                                            </span>
                                        </div>
                                        <div class="small text-slate-400 d-inline-flex align-items-center gap-2" style="font-size: 0.75rem;">
                                            <span><i class="bi bi-calendar3 me-1 text-slate-400"></i><?= fecha_corta($r['creado_en'], true) ?></span>
                                            <span>•</span>
                                            <span class="text-slate-600 fw-medium">Soporte y Articulación Productiva (Literal G)</span>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>

                            <form method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="accion" value="actualizar">
                                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                <input type="hidden" name="_qs" value="<?= e($qsActual) ?>">

                                <div class="modal-body p-4 bg-slate-50/50">
                                    
                                    <!-- Ficha de Contacto en Cuadrícula Moderna -->
                                    <div class="row g-3 mb-4">
                                        <div class="col-sm-6 col-md-3">
                                            <div class="p-3 bg-white border rounded-3 shadow-2xs h-100" style="border-color: #e2e8f0;">
                                                <div class="text-slate-400 small mb-1 d-flex align-items-center gap-1.5" style="font-size: 0.72rem;">
                                                    <i class="bi bi-person-fill text-emerald-600"></i> Productor / Responsable
                                                </div>
                                                <div class="fw-bold text-slate-900 text-truncate" title="<?= e($r['nombre']) ?>"><?= e($r['nombre']) ?></div>
                                            </div>
                                        </div>

                                        <div class="col-sm-6 col-md-3">
                                            <div class="p-3 bg-white border rounded-3 shadow-2xs h-100" style="border-color: #e2e8f0;">
                                                <div class="text-slate-400 small mb-1 d-flex align-items-center gap-1.5" style="font-size: 0.72rem;">
                                                    <i class="bi bi-geo-alt-fill text-rose-500"></i> Sector / Finca
                                                </div>
                                                <div class="fw-bold text-slate-900 text-truncate" title="<?= e($r['sector_comunidad']) ?>"><?= e($r['sector_comunidad']) ?></div>
                                            </div>
                                        </div>

                                        <div class="col-sm-6 col-md-3">
                                            <div class="p-3 bg-white border rounded-3 shadow-2xs h-100" style="border-color: #e2e8f0;">
                                                <div class="text-slate-400 small mb-1 d-flex align-items-center gap-1.5" style="font-size: 0.72rem;">
                                                    <i class="bi bi-whatsapp text-success"></i> WhatsApp Oficial
                                                </div>
                                                <div>
                                                    <a href="<?= e($linkWa) ?>" target="_blank" class="fw-bold text-success text-decoration-none d-inline-flex align-items-center gap-1 text-truncate" title="Abrir chat en WhatsApp">
                                                        <?= e($r['telefono']) ?> <i class="bi bi-box-arrow-up-right" style="font-size: 0.65rem;"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-6 col-md-3">
                                            <div class="p-3 bg-white border rounded-3 shadow-2xs h-100" style="border-color: #e2e8f0;">
                                                <div class="text-slate-400 small mb-1 d-flex align-items-center gap-1.5" style="font-size: 0.72rem;">
                                                    <i class="bi bi-envelope-at text-primary"></i> Correo Electrónico
                                                </div>
                                                <div class="fw-bold text-slate-800 text-truncate" title="<?= e($r['correo'] ?: 'No suministrado') ?>"><?= e($r['correo'] ?: 'No suministrado') ?></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Mensaje del Productor -->
                                    <div class="bg-white border rounded-3 p-4 mb-4 shadow-2xs" style="border-color: #e2e8f0;">
                                        <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom" style="border-color: #f1f5f9;">
                                            <div class="fw-bold text-slate-900 d-flex align-items-center gap-2">
                                                <i class="bi bi-chat-quote-fill text-emerald-600 fs-5"></i>
                                                <span><?= e($r['asunto']) ?></span>
                                            </div>
                                            <span class="text-slate-400 small" style="font-size: 0.72rem;">
                                                <i class="bi bi-clock me-1"></i><?= fecha_corta($r['creado_en'], true) ?>
                                            </span>
                                        </div>
                                        <div class="text-slate-700 leading-relaxed" style="white-space: pre-line; font-size: 0.88rem;"><?= e($r['mensaje']) ?></div>
                                        
                                        <?php if ($r['adjunto']): ?>
                                            <div class="mt-3 pt-3 border-top d-flex align-items-center justify-content-between" style="border-color: #f1f5f9;">
                                                <div class="small fw-semibold text-slate-700 d-flex align-items-center gap-1.5">
                                                    <i class="bi bi-paperclip text-secondary fs-6"></i> Documento o fotografía adjunta
                                                </div>
                                                <a href="<?= e(url($r['adjunto'])) ?>" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill px-3 py-1 fw-semibold">
                                                    <i class="bi bi-box-arrow-up-right me-1"></i> Abrir archivo adjunto
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Formulario de Seguimiento y Notas Internas -->
                                    <div class="bg-white border rounded-3 p-4 shadow-2xs" style="border-color: #e2e8f0;">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-slate-800 mb-1.5 d-flex align-items-center gap-1.5" style="font-size: 0.84rem;">
                                                    <i class="bi bi-flag-fill text-primary"></i> Estado de la Solicitud / Atención *
                                                </label>
                                                <select name="estado" class="form-select shadow-none" style="border-color: #e2e8f0; border-radius: 10px;">
                                                    <?php foreach (ESTADOS_COM as $k => [$nom]): ?>
                                                        <option value="<?= $k ?>"<?= $r['estado'] === $k ? ' selected' : '' ?>><?= e($nom) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-slate-800 mb-1.5 d-flex align-items-center gap-1.5" style="font-size: 0.84rem;">
                                                    <i class="bi bi-person-check-fill text-success"></i> Responsable de Atención
                                                </label>
                                                <input type="text" class="form-control bg-slate-50 shadow-none text-slate-600" value="<?= e($r['atendido_nombre'] ?? $u['nombre']) ?>" readonly style="border-color: #e2e8f0; border-radius: 10px;">
                                            </div>

                                            <div class="col-12 mt-3">
                                                <label class="form-label fw-bold text-slate-800 mb-1.5 d-flex align-items-center gap-1.5" style="font-size: 0.84rem;">
                                                    <i class="bi bi-pencil-square text-secondary"></i> Respuesta interna y acuerdos pactados (EPSDC)
                                                </label>
                                                <textarea name="respuesta_interna" rows="3" class="form-control shadow-none" style="border-color: #e2e8f0; border-radius: 10px; font-size: 0.875rem;" placeholder="Escriba aquí los acuerdos técnicos tomados, fechas de acopio pactadas en finca o respuesta telefónica dada al productor..."><?= e($r['respuesta_interna']) ?></textarea>
                                                <div class="form-text small text-slate-400 mt-1" style="font-size: 0.74rem;">Estas notas quedan registradas en la auditoría interna de la junta directiva.</div>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <!-- Footer del Modal -->
                                <div class="modal-footer bg-white border-top py-3 px-4 d-flex align-items-center justify-content-between">
                                    <a href="<?= e($linkWa) ?>" target="_blank" class="btn btn-outline-success rounded-pill px-3 py-2 fw-semibold text-sm d-inline-flex align-items-center gap-2">
                                        <i class="bi bi-whatsapp fs-6"></i> Abrir Chat en WhatsApp
                                    </a>
                                    <div class="d-flex align-items-center gap-2">
                                        <button type="button" class="btn btn-light border rounded-pill px-4 py-2 text-sm fw-semibold" data-bs-dismiss="modal">Cerrar</button>
                                        <button type="submit" class="btn btn-success rounded-pill px-4 py-2 text-sm fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs">
                                            <i class="bi bi-check2-circle fs-6"></i> Guardar Seguimiento
                                        </button>
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?= render_paginador($res) ?>
</div>

<?php require __DIR__ . '/layout/footer.php'; ?>
