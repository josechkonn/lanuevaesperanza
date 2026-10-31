<?php
/**
 * Autenticación y autorización por roles.
 *
 * Roles:
 *   superadmin -> todo, incluida la gestión de usuarios, configuración y auditoría
 *   editor     -> crear/editar/eliminar contenido, precios y entregas
 *   lector     -> solo lectura
 */

const ROLES = [
    'superadmin' => 'Superadmin',
    'editor'     => 'Editor',
    'lector'     => 'Lector',
];

function usuario_actual(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

function usuario_id(): ?int
{
    return isset($_SESSION['usuario']['id']) ? (int)$_SESSION['usuario']['id'] : null;
}

function tiene_rol(string ...$roles): bool
{
    $u = usuario_actual();
    return $u !== null && in_array($u['rol'], $roles, true);
}

/** ¿Puede escribir (crear/editar/eliminar) contenido? */
function puede_editar(): bool
{
    return tiene_rol('superadmin', 'editor');
}

function requiere_login(): void
{
    global $CONFIG;
    $u = usuario_actual();
    if (!$u) {
        redirect(url('admin/login.php?next=' . urlencode($_SERVER['REQUEST_URI'] ?? '')));
    }
    // Expiración por inactividad
    $ultimo = $_SESSION['ultima_actividad'] ?? time();
    if (time() - $ultimo > $CONFIG['session_idle']) {
        cerrar_sesion();
        session_start();
        flash('warning', 'Tu sesión expiró por inactividad.');
        redirect(url('admin/login.php'));
    }
    $_SESSION['ultima_actividad'] = time();

    // Revalida cada 5 minutos que la cuenta siga activa y con el mismo rol
    if (time() - ($_SESSION['revalidado'] ?? 0) > 300) {
        $st = db()->prepare('SELECT id, nombre, email, rol, activo FROM usuarios WHERE id = ?');
        $st->execute([$u['id']]);
        $fila = $st->fetch();
        if (!$fila || !$fila['activo']) {
            cerrar_sesion();
            session_start();
            flash('danger', 'Tu cuenta fue desactivada.');
            redirect(url('admin/login.php'));
        }
        unset($fila['activo']);
        $_SESSION['usuario'] = $fila;
        $_SESSION['revalidado'] = time();
    }
}

function requiere_rol(string ...$roles): void
{
    requiere_login();
    if (!tiene_rol(...$roles)) {
        http_response_code(403);
        flash('danger', 'No tienes permisos para acceder a esa sección.');
        redirect(url('admin/index.php'));
    }
}

/** Para manejar POST de escritura: login + rol editor/superadmin + CSRF. */
function requiere_escritura(): void
{
    requiere_login();
    csrf_check();
    if (!puede_editar()) {
        flash('danger', 'Tu rol es de solo lectura.');
        redirect($_SERVER['HTTP_REFERER'] ?? url('admin/index.php'));
    }
}

function login_bloqueado(string $email): bool
{
    global $CONFIG;
    $st = db()->prepare(
        'SELECT COUNT(*) FROM login_intentos
         WHERE exitoso = 0 AND creado_en > (NOW() - INTERVAL ? MINUTE) AND (ip = ? OR email = ?)'
    );
    $st->execute([$CONFIG['login_ventana_min'], client_ip(), $email]);
    return (int)$st->fetchColumn() >= $CONFIG['login_max_intentos'];
}

function intentar_login(string $email, string $password): array
{
    $email = mb_strtolower(trim($email));
    if (login_bloqueado($email)) {
        return [false, 'Demasiados intentos fallidos. Espera unos minutos e inténtalo de nuevo.'];
    }

    $st = db()->prepare('SELECT * FROM usuarios WHERE email = ? LIMIT 1');
    $st->execute([$email]);
    $u = $st->fetch();

    $ok = $u && password_verify($password, $u['password_hash']);
    db()->prepare('INSERT INTO login_intentos (email, ip, exitoso) VALUES (?,?,?)')
        ->execute([$email, client_ip(), $ok && $u['activo'] ? 1 : 0]);

    if (!$ok) {
        return [false, 'Correo o contraseña incorrectos.'];
    }
    if (!$u['activo']) {
        return [false, 'Tu cuenta está desactivada. Contacta al administrador.'];
    }

    if (password_needs_rehash($u['password_hash'], PASSWORD_DEFAULT)) {
        db()->prepare('UPDATE usuarios SET password_hash = ? WHERE id = ?')
            ->execute([password_hash($password, PASSWORD_DEFAULT), $u['id']]);
    }

    session_regenerate_id(true);
    $_SESSION['usuario'] = [
        'id' => (int)$u['id'], 'nombre' => $u['nombre'], 'email' => $u['email'], 'rol' => $u['rol'],
    ];
    $_SESSION['ultima_actividad'] = time();
    $_SESSION['revalidado'] = time();
    unset($_SESSION['csrf']);

    db()->prepare('UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?')->execute([$u['id']]);
    auditar('login', 'usuarios', (int)$u['id'], 'Inicio de sesión');
    return [true, ''];
}

function cerrar_sesion(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function validar_password(string $p): ?string
{
    if (mb_strlen($p) < 8) return 'La contraseña debe tener al menos 8 caracteres.';
    if (!preg_match('/[A-Za-z]/', $p) || !preg_match('/\d/', $p)) return 'La contraseña debe combinar letras y números.';
    return null;
}
