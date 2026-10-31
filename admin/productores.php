<?php
require __DIR__ . '/../includes/app.php';
requiere_login();
$pdo = db();

const TIPOS_COM = [
    'oferta_rubro'           => ['Oferta de rubros / cosecha', 'bi-basket2-fill', '#10b981', 'text-bg-success'],
    'requerimiento_tecnico'  => ['Requerimiento técnico / logístico', 'bi-tools', '#f59e0b', 'text-bg-warning'],
    'sugerencia_observacion' => ['Sugerencia u observación', 'bi-chat-dots-fill', '#0ea5e9', 'text-bg-info'],
    'otro'                   => ['Otra consulta', 'bi-envelope-paper-fill', '#64748b', 'text-bg-secondary'],
];

const ESTADOS_COM = [
    'pendiente'   => ['Pendiente', 'warning'],
    'en_revision' => ['En revisión', 'info'],
    'atendido'    => ['Atendido', 'success'],
    'archivado'   => ['Archivado', 'secondary'],
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

// Resumen de KPIs
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
    <label class="form-label small mb-0">Buscar</label>
    <input type="search" name="q" value="<?= e($q) ?>" class="form-control form-control-sm" placeholder="Nombre, sector, teléfono...">
</div>
<div class="col-6 col-md-auto">
    <label class="form-label small mb-0">Tipo de Gestión</label>
    <select name="tipo" class="form-select form-select-sm">
        <option value="">Todos los tipos</option>
        <?php foreach (TIPOS_COM as $k => [$nom]): ?>
            <option value="<?= $k ?>"<?= $tipoF === $k ? ' selected' : '' ?>><?= e($nom) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div class="col-6 col-md-auto">
    <label class="form-label small mb-0">Estado</label>
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

<!-- KPIs superiores -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card kpi h-100">
            <span class="kpi-icon" style="background:#e0f2fe;color:#0369a1"><i class="bi bi-inbox-fill"></i></span>
            <div><div class="kpi-value"><?= (int)($kpi['total'] ?? 0) ?></div><div class="kpi-label">Comunicaciones totales</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi h-100">
            <span class="kpi-icon" style="background:#fef3c7;color:#b45309"><i class="bi bi-clock-history"></i></span>
            <div><div class="kpi-value"><?= (int)($kpi['pendientes'] ?? 0) ?></div><div class="kpi-label">Pendientes por responder</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi h-100">
            <span class="kpi-icon" style="background:#dcfce7;color:#15803d"><i class="bi bi-basket2-fill"></i></span>
            <div><div class="kpi-value"><?= (int)($kpi['ofertas'] ?? 0) ?></div><div class="kpi-label">Ofertas de rubros / cosecha</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi h-100">
            <span class="kpi-icon" style="background:#ede9fe;color:#6d28d9"><i class="bi bi-tools"></i></span>
            <div><div class="kpi-value"><?= (int)($kpi['tecnicos'] ?? 0) ?></div><div class="kpi-label">Requerimientos técnicos</div></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
        <div>
            <h2 class="h6 fw-bold mb-0">Comunicaciones e Intercambio con Productores</h2>
            <small class="text-secondary">Cumplimiento del <strong>Literal G del Objeto</strong> y los derechos estatutarios del productor comunal.</small>
        </div>
    </div>

    <?= render_filtros($f, $extra) ?>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Productor / Sector</th>
                    <th>Tipo</th>
                    <th>Asunto y Mensaje</th>
                    <th>WhatsApp</th>
                    <th>Estado</th>
                    <?php if (puede_editar()): ?><th class="text-end">Acciones</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php if (!$res['filas']): ?>
                <tr>
                    <td colspan="7"><div class="empty"><i class="bi bi-chat-square-text"></i>No hay comunicaciones registradas con estos filtros.</div></td>
                </tr>
            <?php endif; ?>

            <?php foreach ($res['filas'] as $r):
                $tipoInfo = TIPOS_COM[$r['tipo']] ?? ['Desconocido', 'bi-question', '#64748b', 'text-bg-secondary'];
                $estInfo = ESTADOS_COM[$r['estado']] ?? ['Desconocido', 'secondary'];
                $telLimpio = preg_replace('/[^0-9]/', '', $r['telefono']);
                // Mensaje sugerido para responder por WhatsApp
                $msgWa = "Hola " . $r['nombre'] . ", le saludamos de La Nueva Esperanza (EPSDC). Con respecto a su comunicación sobre '" . $r['asunto'] . "': ";
                $linkWa = "https://wa.me/" . $telLimpio . "?text=" . urlencode($msgWa);
            ?>
                <tr>
                    <td class="small text-nowrap"><?= e(fecha_corta($r['creado_en'], true)) ?></td>
                    <td>
                        <div class="fw-bold"><?= e($r['nombre']) ?></div>
                        <div class="small text-secondary"><i class="bi bi-geo-alt"></i> <?= e($r['sector_comunidad']) ?></div>
                    </td>
                    <td>
                        <span class="badge <?= $tipoInfo[3] ?> d-inline-flex align-items-center gap-1">
                            <i class="bi <?= $tipoInfo[1] ?>"></i> <?= e($tipoInfo[0]) ?>
                        </span>
                    </td>
                    <td style="max-width: 300px;">
                        <div class="fw-semibold text-truncate"><?= e($r['asunto']) ?></div>
                        <div class="small text-secondary text-truncate"><?= e($r['mensaje']) ?></div>
                        <?php if ($r['adjunto']): ?>
                            <a href="<?= e(url($r['adjunto'])) ?>" target="_blank" class="badge text-bg-light border text-decoration-none mt-1">
                                <i class="bi bi-paperclip"></i> Ver archivo adjunto
                            </a>
                        <?php endif; ?>
                    </td>
                    <td class="text-nowrap">
                        <a href="<?= e($linkWa) ?>" target="_blank" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-whatsapp"></i> <?= e($r['telefono']) ?>
                        </a>
                    </td>
                    <td>
                        <?php if (puede_editar()): ?>
                            <form method="post" class="d-inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="accion" value="estado_rapido">
                                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                <input type="hidden" name="_qs" value="<?= e($qsActual) ?>">
                                <select name="estado" class="form-select form-select-sm border-0 bg-<?= $estInfo[1] ?>-subtle text-<?= $estInfo[1] ?>-emphasis fw-semibold" onchange="this.form.submit()">
                                    <?php foreach (ESTADOS_COM as $k => [$nom]): ?>
                                        <option value="<?= $k ?>"<?= $r['estado'] === $k ? ' selected' : '' ?>><?= e($nom) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        <?php else: ?>
                            <span class="badge text-bg-<?= $estInfo[1] ?>"><?= e($estInfo[0]) ?></span>
                        <?php endif; ?>
                    </td>
                    <?php if (puede_editar()): ?>
                    <td class="text-end text-nowrap">
                        <button class="btn btn-sm btn-light border" title="Ver detalles y gestionar" data-bs-toggle="modal" data-bs-target="#modalDetalle<?= $r['id'] ?>">
                            <i class="bi bi-eye"></i>
                        </button>
                        <form method="post" class="d-inline" data-confirm="¿Eliminar esta comunicación de <?= e($r['nombre']) ?>?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                            <input type="hidden" name="_qs" value="<?= e($qsActual) ?>">
                            <button class="btn btn-sm btn-light border text-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                    <?php endif; ?>
                </tr>

                <!-- Modal de Detalle y Seguimiento -->
                <div class="modal fade" id="modalDetalle<?= $r['id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold">
                                    <i class="bi <?= $tipoInfo[1] ?> me-1" style="color:<?= $tipoInfo[2] ?>"></i>
                                    Gestión de Comunicación #<?= $r['id'] ?>
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="accion" value="actualizar">
                                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                <input type="hidden" name="_qs" value="<?= e($qsActual) ?>">

                                <div class="modal-body">
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label small text-secondary mb-0">Productor / Responsable</label>
                                            <div class="fw-bold"><?= e($r['nombre']) ?></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small text-secondary mb-0">Sector / Ubicación de Finca</label>
                                            <div class="fw-bold"><?= e($r['sector_comunidad']) ?></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small text-secondary mb-0">Teléfono WhatsApp</label>
                                            <div>
                                                <a href="<?= e($linkWa) ?>" target="_blank" class="text-success fw-bold text-decoration-none">
                                                    <i class="bi bi-whatsapp me-1"></i> <?= e($r['telefono']) ?> (Clic para responder en WhatsApp)
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small text-secondary mb-0">Correo Electrónico</label>
                                            <div><?= e($r['correo'] ?: 'No suministrado') ?></div>
                                        </div>
                                    </div>

                                    <div class="bg-light p-3 rounded mb-3">
                                        <div class="small fw-bold text-dark mb-1">Asunto: <?= e($r['asunto']) ?></div>
                                        <div class="small text-secondary mb-2">Enviado el: <?= fecha_corta($r['creado_en'], true) ?></div>
                                        <hr class="my-2">
                                        <div style="white-space: pre-line;" class="text-dark"><?= e($r['mensaje']) ?></div>
                                        <?php if ($r['adjunto']): ?>
                                            <div class="mt-3 pt-2 border-top">
                                                <div class="small fw-bold mb-1">Archivo / Evidencia adjunta:</div>
                                                <a href="<?= e(url($r['adjunto'])) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-box-arrow-up-right me-1"></i> Abrir archivo / imagen adjunta
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Estado del Seguimiento *</label>
                                            <select name="estado" class="form-select">
                                                <?php foreach (ESTADOS_COM as $k => [$nom]): ?>
                                                    <option value="<?= $k ?>"<?= $r['estado'] === $k ? ' selected' : '' ?>><?= e($nom) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-bold">Respuesta interna / Notas de resolución (EPSDC)</label>
                                            <textarea name="respuesta_interna" rows="3" class="form-control" placeholder="Escriba aquí los acuerdos técnicos tomados, fechas de acopio pactadas o respuesta dada al productor..."><?= e($r['respuesta_interna']) ?></textarea>
                                            <div class="form-text small">Estas notas son de control interno de la directiva de la EPSDC.</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <a href="<?= e($linkWa) ?>" target="_blank" class="btn btn-success me-auto">
                                        <i class="bi bi-whatsapp me-1"></i> Abrir Chat en WhatsApp
                                    </a>
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                                    <button type="submit" class="btn btn-primary">Guardar Seguimiento</button>
                                </div>
                            </form>
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
