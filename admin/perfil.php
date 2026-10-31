<?php
require __DIR__ . '/../includes/app.php';
requiere_login();
$pdo = db();
$u = usuario_actual();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $accion = post('accion');
    try {
        if ($accion === 'datos') {
            $nombre = post('nombre');
            if ($nombre === '' || mb_strlen($nombre) > 120) throw new RuntimeException('Nombre inválido.');
            $pdo->prepare('UPDATE usuarios SET nombre = ? WHERE id = ?')->execute([$nombre, $u['id']]);
            $_SESSION['usuario']['nombre'] = $nombre;
            auditar('editar', 'usuarios', $u['id'], 'Actualizó su nombre');
            flash('success', 'Datos actualizados.');
        } elseif ($accion === 'password') {
            $st = $pdo->prepare('SELECT password_hash FROM usuarios WHERE id = ?');
            $st->execute([$u['id']]);
            if (!password_verify((string)($_POST['actual'] ?? ''), (string)$st->fetchColumn())) {
                throw new RuntimeException('La contraseña actual no es correcta.');
            }
            $nueva = (string)($_POST['nueva'] ?? '');
            if ($nueva !== (string)($_POST['confirmar'] ?? '')) throw new RuntimeException('Las contraseñas nuevas no coinciden.');
            if ($err = validar_password($nueva)) throw new RuntimeException($err);
            $pdo->prepare('UPDATE usuarios SET password_hash = ? WHERE id = ?')->execute([password_hash($nueva, PASSWORD_DEFAULT), $u['id']]);
            session_regenerate_id(true);
            auditar('editar', 'usuarios', $u['id'], 'Cambió su contraseña');
            flash('success', 'Contraseña cambiada.');
        }
    } catch (RuntimeException $ex) {
        flash('danger', $ex->getMessage());
    }
    redirect(url('admin/perfil.php'));
}

$titulo = 'Mi perfil';
$seccion = '';
require __DIR__ . '/layout/header.php';
?>
<div class="row g-3" style="max-width:900px">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header py-3"><h2 class="h6 fw-bold mb-0">Mis datos</h2></div>
            <form method="post" class="card-body">
                <?= csrf_field() ?><input type="hidden" name="accion" value="datos">
                <div class="mb-3"><label class="form-label">Nombre</label><input name="nombre" value="<?= e($u['nombre']) ?>" class="form-control" required maxlength="120"></div>
                <div class="mb-3"><label class="form-label">Correo</label><input value="<?= e($u['email']) ?>" class="form-control" disabled></div>
                <div class="mb-3"><label class="form-label">Rol</label><input value="<?= e(ROLES[$u['rol']]) ?>" class="form-control" disabled></div>
                <button class="btn btn-primary">Guardar</button>
            </form>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header py-3"><h2 class="h6 fw-bold mb-0">Cambiar contraseña</h2></div>
            <form method="post" class="card-body">
                <?= csrf_field() ?><input type="hidden" name="accion" value="password">
                <div class="mb-3"><label class="form-label">Contraseña actual</label><input type="password" name="actual" class="form-control" required autocomplete="current-password"></div>
                <div class="mb-3"><label class="form-label">Nueva contraseña</label><input type="password" name="nueva" class="form-control" required minlength="8" autocomplete="new-password"><div class="form-text">Mínimo 8 caracteres, con letras y números.</div></div>
                <div class="mb-3"><label class="form-label">Confirmar nueva</label><input type="password" name="confirmar" class="form-control" required minlength="8" autocomplete="new-password"></div>
                <button class="btn btn-primary">Cambiar contraseña</button>
            </form>
        </div>
    </div>
</div>
<?php require __DIR__ . '/layout/footer.php'; ?>
