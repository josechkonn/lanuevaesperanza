<?php
require __DIR__ . '/../includes/app.php';
requiere_rol('superadmin');

$pdo = db();

/** ¿Quedaría al menos un superadmin activo si se aplica el cambio a $id? */
function otro_superadmin_activo(int $id): bool
{
    $st = db()->prepare("SELECT COUNT(*) FROM usuarios WHERE rol = 'superadmin' AND activo = 1 AND id <> ?");
    $st->execute([$id]);
    return (int)$st->fetchColumn() > 0;
}

// ------------------------------------------------------------ Acciones (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $accion = post('accion');
    $id = (int)post('id', 0);
    $volver = volver_a('admin/usuarios.php');

    try {
        if ($accion === 'guardar') {
            $nombre = post('nombre');
            $email = mb_strtolower(post('email'));
            $rol = post('rol');
            $activo = isset($_POST['activo']) ? 1 : 0;
            $pass = (string)($_POST['password'] ?? '');

            $errores = [];
            if ($nombre === '' || mb_strlen($nombre) > 120) $errores[] = 'El nombre es obligatorio.';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = 'Correo inválido.';
            if (!isset(ROLES[$rol])) $errores[] = 'Rol inválido.';
            if (!$id || $pass !== '') {
                if ($e = validar_password($pass)) $errores[] = $e;
            }
            if ($id === usuario_id() && ($rol !== 'superadmin' || !$activo)) {
                $errores[] = 'No puedes quitarte el rol de superadmin ni desactivarte a ti mismo.';
            }
            if ($id && ($rol !== 'superadmin' || !$activo) && !otro_superadmin_activo($id)) {
                $st = $pdo->prepare('SELECT rol FROM usuarios WHERE id = ?');
                $st->execute([$id]);
                if ($st->fetchColumn() === 'superadmin') $errores[] = 'Debe quedar al menos un superadmin activo.';
            }
            if ($errores) {
                flash('danger', implode(' ', $errores));
                redirect($volver);
            }

            if ($id) {
                $sql = 'UPDATE usuarios SET nombre = ?, email = ?, rol = ?, activo = ?' . ($pass !== '' ? ', password_hash = ?' : '') . ' WHERE id = ?';
                $prm = [$nombre, $email, $rol, $activo];
                if ($pass !== '') $prm[] = password_hash($pass, PASSWORD_DEFAULT);
                $prm[] = $id;
                $pdo->prepare($sql)->execute($prm);
                auditar('editar', 'usuarios', $id, "$email ($rol)" . ($pass !== '' ? ' + contraseña' : ''));
                flash('success', 'Usuario actualizado.');
            } else {
                $pdo->prepare('INSERT INTO usuarios (nombre, email, password_hash, rol, activo) VALUES (?,?,?,?,?)')
                    ->execute([$nombre, $email, password_hash($pass, PASSWORD_DEFAULT), $rol, $activo]);
                $nuevo = (int)$pdo->lastInsertId();
                auditar('crear', 'usuarios', $nuevo, "$email ($rol)");
                flash('success', 'Usuario creado. Comparte la contraseña de forma segura.');
            }
        } elseif ($accion === 'estado' && $id) {
            if ($id === usuario_id()) throw new RuntimeException('No puedes desactivarte a ti mismo.');
            $st = $pdo->prepare('SELECT rol, activo FROM usuarios WHERE id = ?');
            $st->execute([$id]);
            $u = $st->fetch();
            if (!$u) throw new RuntimeException('Usuario no encontrado.');
            if ($u['activo'] && $u['rol'] === 'superadmin' && !otro_superadmin_activo($id)) {
                throw new RuntimeException('Debe quedar al menos un superadmin activo.');
            }
            $pdo->prepare('UPDATE usuarios SET activo = 1 - activo WHERE id = ?')->execute([$id]);
            auditar($u['activo'] ? 'desactivar' : 'activar', 'usuarios', $id);
            flash('success', $u['activo'] ? 'Usuario desactivado.' : 'Usuario activado.');
        } elseif ($accion === 'eliminar' && $id) {
            if ($id === usuario_id()) throw new RuntimeException('No puedes eliminar tu propia cuenta.');
            $st = $pdo->prepare('SELECT email, rol FROM usuarios WHERE id = ?');
            $st->execute([$id]);
            $u = $st->fetch();
            if (!$u) throw new RuntimeException('Usuario no encontrado.');
            if ($u['rol'] === 'superadmin' && !otro_superadmin_activo($id)) {
                throw new RuntimeException('Debe quedar al menos un superadmin activo.');
            }
            $pdo->prepare('DELETE FROM usuarios WHERE id = ?')->execute([$id]);
            auditar('eliminar', 'usuarios', $id, $u['email']);
            flash('success', 'Usuario eliminado.');
        }
    } catch (PDOException $ex) {
        flash('danger', $ex->getCode() === '23000' ? 'Ese correo ya está registrado.' : 'Error de base de datos.');
        error_log($ex->getMessage());
    } catch (RuntimeException $ex) {
        flash('danger', $ex->getMessage());
    }
    redirect($volver);
}

// ------------------------------------------------------------ Listado
$f = filtro_fechas('todo');
$where = [];
$params = [];
aplicar_fechas($f, 'u.creado_en', $where, $params);

$q = trim((string)($_GET['q'] ?? ''));
if ($q !== '') {
    $where[] = '(u.nombre LIKE :q1 OR u.email LIKE :q2)';
    $params['q1'] = $params['q2'] = "%$q%";
}
$rolF = $_GET['rol'] ?? '';
if (isset(ROLES[$rolF])) {
    $where[] = 'u.rol = :rol';
    $params['rol'] = $rolF;
}
$estF = $_GET['estado'] ?? '';
if ($estF === '1' || $estF === '0') {
    $where[] = 'u.activo = :act';
    $params['act'] = (int)$estF;
}

$res = paginar_cursor([
    'select' => 'SELECT u.id, u.nombre, u.email, u.rol, u.activo, u.ultimo_login, u.creado_en FROM usuarios u',
    'where' => $where, 'params' => $params,
    'col' => 'u.creado_en', 'key' => 'creado_en', 'id' => 'u.id',
]);

$titulo = 'Usuarios';
$seccion = 'usuarios';
$qsActual = qs();

ob_start(); ?>
<div class="col-12 col-md-3"><label class="form-label small mb-0">Buscar</label><input type="search" name="q" value="<?= e($q) ?>" class="form-control form-control-sm" placeholder="Nombre o correo"></div>
<div class="col-6 col-md-auto"><label class="form-label small mb-0">Rol</label>
    <select name="rol" class="form-select form-select-sm"><option value="">Todos</option>
        <?php foreach (ROLES as $k => $v): ?><option value="<?= $k ?>"<?= $rolF === $k ? ' selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
    </select></div>
<div class="col-6 col-md-auto"><label class="form-label small mb-0">Estado</label>
    <select name="estado" class="form-select form-select-sm"><option value="">Todos</option><option value="1"<?= $estF === '1' ? ' selected' : '' ?>>Activos</option><option value="0"<?= $estF === '0' ? ' selected' : '' ?>>Inactivos</option></select></div>
<?php $extra = ob_get_clean();

require __DIR__ . '/layout/header.php';
?>
<div class="card" id="tablaUsuariosWrap">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
        <div>
            <h2 class="h6 fw-bold mb-0 d-flex align-items-center">
                <span>Cuentas del panel</span>
                <button type="button" class="btn-tour-hint" onclick="TourGuia.mostrarPaso('tabla_usuarios')" title="¿Cómo gestionar usuarios?">?</button>
            </h2>
            <small class="text-secondary">Solo el superadmin crea cuentas: no hay registro público.</small>
        </div>
        <button id="btnNuevoUsuario" class="btn btn-primary btn-sm" data-modal-new="#modalUsuario" data-title="Nuevo usuario"><i class="bi bi-person-plus"></i> Nuevo usuario</button>
    </div>
    <?= render_filtros($f, $extra) ?>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th>Usuario</th><th>Rol</th><th>Estado</th><th>Último acceso</th><th>Creado</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
            <?php if (!$res['filas']): ?>
                <tr><td colspan="6"><div class="empty"><i class="bi bi-people"></i>No hay usuarios con esos filtros.</div></td></tr>
            <?php endif; ?>
            <?php foreach ($res['filas'] as $r): $yo = (int)$r['id'] === usuario_id(); ?>
                <tr>
                    <td><div class="fw-semibold"><?= e($r['nombre']) ?><?= $yo ? ' <span class="badge text-bg-primary">Tú</span>' : '' ?></div><div class="small text-secondary"><?= e($r['email']) ?></div></td>
                    <td><span class="badge text-bg-light border"><?= e(ROLES[$r['rol']]) ?></span></td>
                    <td><?= $r['activo'] ? '<span class="badge text-bg-success">Activo</span>' : '<span class="badge text-bg-secondary">Inactivo</span>' ?></td>
                    <td class="small text-nowrap"><?= $r['ultimo_login'] ? '<span class="d-inline-flex align-items-center gap-1.5"><i class="bi bi-calendar3 text-emerald-600"></i>' . e(fecha_corta($r['ultimo_login'], true)) . '</span>' : '—' ?></td>
                    <td class="small text-nowrap"><span class="d-inline-flex align-items-center gap-1.5"><i class="bi bi-calendar3 text-slate-400"></i><?= e(fecha_corta($r['creado_en'])) ?></span></td>
                    <td class="text-end text-nowrap">
                        <button class="btn btn-sm btn-light border" title="Editar" data-modal-edit="#modalUsuario" data-title="Editar usuario"
                                data-row='<?= e(json_encode(['id' => $r['id'], 'nombre' => $r['nombre'], 'email' => $r['email'], 'rol' => $r['rol'], 'activo' => $r['activo']])) ?>'><i class="bi bi-pencil"></i></button>
                        <?php if (!$yo): ?>
                            <form method="post" class="d-inline" data-confirm="¿<?= $r['activo'] ? 'Desactivar' : 'Activar' ?> a <?= e($r['nombre']) ?>?">
                                <?= csrf_field() ?><input type="hidden" name="accion" value="estado"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="_qs" value="<?= e($qsActual) ?>">
                                <button class="btn btn-sm btn-light border" title="<?= $r['activo'] ? 'Desactivar' : 'Activar' ?>"><i class="bi <?= $r['activo'] ? 'bi-person-dash' : 'bi-person-check' ?>"></i></button>
                            </form>
                            <form method="post" class="d-inline" data-confirm="¿Eliminar definitivamente a <?= e($r['nombre']) ?>?">
                                <?= csrf_field() ?><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="_qs" value="<?= e($qsActual) ?>">
                                <button class="btn btn-sm btn-light border text-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= render_paginador($res) ?>
</div>

<div class="modal fade" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="post" data-defaults='{"rol":"editor","activo":1}'>
            <div class="modal-header"><h5 class="modal-title">Usuario</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <?= csrf_field() ?>
                <input type="hidden" name="accion" value="guardar"><input type="hidden" name="id"><input type="hidden" name="_qs" value="<?= e($qsActual) ?>">
                <div class="mb-3"><label class="form-label">Nombre completo *</label><input name="nombre" class="form-control" required maxlength="120"></div>
                <div class="mb-3"><label class="form-label">Correo *</label><input type="email" name="email" class="form-control" required maxlength="160"></div>
                <div class="mb-3"><label class="form-label">Rol *</label>
                    <select name="rol" class="form-select">
                        <option value="superadmin">Superadmin: todo, incluidos usuarios y configuración</option>
                        <option value="editor">Editor: contenido, precios y entregas</option>
                        <option value="atencion">Atención: único acceso a Atención en Vivo / Chat</option>
                        <option value="lector">Lector: solo puede ver</option>
                    </select></div>
                <div class="mb-3"><label class="form-label">Contraseña <span data-solo-nuevo>*</span></label>
                    <input type="password" name="password" class="form-control" autocomplete="new-password" minlength="8">
                    <div class="form-text" data-solo-editar>Déjala vacía para no cambiarla.</div>
                    <div class="form-text">Mínimo 8 caracteres, con letras y números.</div></div>
                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="activo" value="1" id="uActivo"><label class="form-check-label" for="uActivo">Cuenta activa</label></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
        </form>
    </div>
</div>
<?php require __DIR__ . '/layout/footer.php'; ?>
