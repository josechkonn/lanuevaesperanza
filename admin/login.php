<?php
require __DIR__ . '/../includes/app.php';

if (usuario_actual()) {
    redirect(url('admin/index.php'));
}

$error = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = post('email');
    [$ok, $error] = intentar_login($email, (string)($_POST['password'] ?? ''));
    if ($ok) {
        $next = $_GET['next'] ?? '';
        // Solo redirecciones internas al panel (evita open-redirect)
        $base = url('admin/');
        if (!is_string($next) || !str_starts_with($next, $base) || str_contains(substr($next, 1), '//')) {
            $next = url('admin/index.php');
        }
        redirect($next);
    }
}
$org = config_get('nombre_organizacion', $CONFIG['app_name']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión · <?= e($org) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= e(url('assets/css/admin.css')) ?>?v=1" rel="stylesheet">
</head>
<body>
<div class="auth-wrap">
    <div class="card auth-card">
        <div class="card-body p-4 p-sm-5">
            <div class="text-center mb-4">
                <span class="brand-icon mx-auto mb-3" style="width:56px;height:56px;font-size:1.5rem"><i class="bi bi-heart-fill"></i></span>
                <h1 class="h4 fw-bold mb-1"><?= e($org) ?></h1>
                <p class="text-secondary small mb-0">Panel de administración</p>
            </div>
            <?php foreach (flashes() as $f): ?>
                <div class="alert alert-<?= e($f['tipo']) ?> py-2 small"><?= e($f['msg']) ?></div>
            <?php endforeach; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger py-2 small" role="alert"><i class="bi bi-exclamation-circle me-1"></i><?= e($error) ?></div>
            <?php endif; ?>
            <form method="post" novalidate>
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="email" class="form-label small fw-semibold">Correo electrónico</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" value="<?= e($email) ?>" required autofocus autocomplete="username">
                    </div>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label small fw-semibold">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
                        <button class="btn btn-outline-secondary" type="button" onclick="var p=document.getElementById('password');p.type=p.type==='password'?'text':'password'" aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">Entrar</button>
            </form>
            <p class="text-center text-secondary small mt-4 mb-0">Las cuentas las crea el administrador.</p>
        </div>
    </div>
</div>
</body>
</html>
