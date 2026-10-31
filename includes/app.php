<?php
/**
 * Arranque común: configuración, sesión segura, conexión PDO y utilidades.
 * Incluir al inicio de cada página:  require __DIR__ . '/../includes/app.php';
 */
declare(strict_types=1);

$CONFIG = require __DIR__ . '/../config/config.php';
date_default_timezone_set($CONFIG['timezone']);

if ($CONFIG['debug']) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

// ---------------------------------------------------------------- Sesión
if (session_status() === PHP_SESSION_NONE && PHP_SAPI !== 'cli') {
    session_name($CONFIG['session_name']);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => $CONFIG['base_url'] ?: '/',
        'secure'   => !empty($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ---------------------------------------------------------------- Base de datos
function db(): PDO
{
    static $pdo = null;
    global $CONFIG;
    if ($pdo === null) {
        $c = $CONFIG['db'];
        $dsn = "mysql:host={$c['host']};port={$c['port']};dbname={$c['name']};charset={$c['charset']}";
        $pdo = new PDO($dsn, $c['user'], $c['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        $pdo->exec("SET time_zone = '" . (new DateTime())->format('P') . "'");
    }
    return $pdo;
}

// ---------------------------------------------------------------- Utilidades
function e($v): string
{
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    global $CONFIG;
    return rtrim($CONFIG['base_url'], '/') . '/' . ltrim($path, '/');
}

function redirect(string $to): void
{
    header('Location: ' . $to);
    exit;
}

/** Construye un query string a partir del GET actual con reemplazos (null = quitar). */
function qs(array $cambios = []): string
{
    $q = array_merge($_GET, $cambios);
    $q = array_filter($q, fn($v) => $v !== null && $v !== '');
    return $q ? '?' . http_build_query($q) : '?';
}

function flash(string $tipo, string $msg): void
{
    $_SESSION['flash'][] = ['tipo' => $tipo, 'msg' => $msg];
}

/** URL de retorno tras un POST, conservando filtros/cursor (campo oculto _qs). */
function volver_a(string $pagina): string
{
    $qs = $_POST['_qs'] ?? '';
    if (!is_string($qs) || !preg_match('/^\?[^\r\n#]*$/', $qs)) $qs = '';
    return url($pagina) . $qs;
}

function flashes(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function post(string $k, $def = '')
{
    $v = $_POST[$k] ?? $def;
    return is_string($v) ? trim($v) : $v;
}

function dinero($monto, string $moneda = 'USD'): string
{
    $sim = ['USD' => '$', 'VES' => 'Bs.', 'EUR' => '€', 'COP' => 'COL$'][$moneda] ?? $moneda . ' ';
    return $sim . ' ' . number_format((float)$monto, 2, ',', '.');
}

function fecha_corta(?string $f, bool $hora = false): string
{
    if (!$f) return '—';
    $ts = strtotime($f);
    return $hora ? date('d/m/Y H:i', $ts) : date('d/m/Y', $ts);
}

function config_get(string $clave, string $def = ''): string
{
    static $cache = null;
    if ($cache === null) {
        try {
            $cache = db()->query('SELECT clave, valor FROM configuracion')->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Throwable $e) {
            $cache = [];
        }
    }
    return (string)($cache[$clave] ?? $def);
}

// ---------------------------------------------------------------- CSRF
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $t = $_POST['_csrf'] ?? '';
    if (!is_string($t) || !hash_equals(csrf_token(), $t)) {
        http_response_code(419);
        exit('Token de seguridad inválido o expirado. Recarga la página e inténtalo de nuevo.');
    }
}

// ---------------------------------------------------------------- Auditoría
function auditar(string $accion, string $entidad, ?int $entidadId = null, string $detalle = ''): void
{
    try {
        $st = db()->prepare('INSERT INTO auditoria (usuario_id, accion, entidad, entidad_id, detalle, ip) VALUES (?,?,?,?,?,?)');
        $st->execute([usuario_id(), $accion, $entidad, $entidadId, mb_substr($detalle, 0, 500), client_ip()]);
    } catch (Throwable $e) {
        error_log('Auditoría falló: ' . $e->getMessage());
    }
}

require __DIR__ . '/auth.php';
require __DIR__ . '/paginacion.php';
require __DIR__ . '/uploads.php';
