<?php
/**
 * Layout del panel. Antes de incluirlo, definir:
 *   $titulo  (string)  título de la página
 *   $seccion (string)  clave del menú activo
 */
$u = usuario_actual();
$org = config_get('nombre_organizacion', $CONFIG['app_name']);
$logo = config_get('logo');

if ($u && $u['rol'] === 'atencion') {
    $menu = [
        'Atención' => [
            ['chat', 'admin/chat.php', 'bi-chat-dots-fill', 'Atención en Vivo / Chat', null],
        ],
    ];
} else {
    $menu = [
        'General' => [
            ['index', 'admin/index.php', 'bi-speedometer2', 'Dashboard', null],
        ],
        'Catálogo' => [
            ['rubros', 'admin/rubros.php', 'bi-tags', 'Rubros', null],
            ['productos', 'admin/productos.php', 'bi-bag-heart', 'Bolsas y combos', null],
            ['precios', 'admin/historial_precios.php', 'bi-graph-up-arrow', 'Historial de precios', null],
        ],
        'Operaciones' => [
            ['entregas', 'admin/entregas.php', 'bi-truck', 'Entregas', null],
            ['productores', 'admin/productores.php', 'bi-person-lines-fill', 'UPF y Productores (Literal G)', null],
            ['chat', 'admin/chat.php', 'bi-chat-dots-fill', 'Atención en Vivo / Chat', null],
        ],
        'Contenido' => [
            ['publicaciones', 'admin/publicaciones.php', 'bi-megaphone', 'Información', null],
            ['estatutos', 'admin/estatutos.php', 'bi-journal-text', 'Estatutos', null],
        ],
        'Administración' => [
            ['usuarios', 'admin/usuarios.php', 'bi-people', 'Usuarios', ['superadmin']],
            ['configuracion', 'admin/configuracion.php', 'bi-gear', 'Configuración', ['superadmin']],
            ['auditoria', 'admin/auditoria.php', 'bi-shield-check', 'Auditoría', ['superadmin']],
        ],
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($titulo ?? 'Panel') ?> · <?= e($org) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= e(url('assets/css/admin.css')) ?>?v=2" rel="stylesheet">
</head>
<body>
<div class="layout">
    <aside class="sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="sidebar">
        <div class="sidebar-brand">
            <?php if ($logo): ?>
                <img src="<?= e(url($logo)) ?>" alt="" class="brand-logo">
            <?php else: ?>
                <span class="brand-icon"><i class="bi bi-tree-fill"></i></span>
            <?php endif; ?>
            <div class="d-flex flex-column min-w-0">
                <span class="brand-text text-truncate"><?= e($org) ?></span>
                <span class="brand-subtext">Panel Comunal • La Grita</span>
            </div>
            <button type="button" class="btn-close btn-close-white d-lg-none ms-auto" data-bs-dismiss="offcanvas" data-bs-target="#sidebar"></button>
        </div>
        <nav class="sidebar-nav">
            <?php foreach ($menu as $grupo => $items):
                $visibles = array_filter($items, fn($i) => $i[4] === null || tiene_rol(...$i[4]));
                if (!$visibles) continue; ?>
                <div class="nav-group"><?= e($grupo) ?></div>
                <?php foreach ($visibles as [$clave, $href, $icono, $texto]): ?>
                    <a href="<?= e(url($href)) ?>" class="nav-link<?= ($seccion ?? '') === $clave ? ' active' : '' ?>">
                        <i class="bi <?= $icono ?>"></i><span><?= e($texto) ?></span>
                    </a>
                <?php endforeach; ?>
            <?php endforeach; ?>
            <div class="nav-group">Sitio</div>
            <a href="<?= e(url('')) ?>" target="_blank" class="nav-link"><i class="bi bi-box-arrow-up-right"></i><span>Ver portal público</span></a>
        </nav>
    </aside>

    <div class="main">
        <header class="topbar">
            <button class="btn btn-light d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-label="Menú"><i class="bi bi-list"></i></button>
            <h1 class="page-title"><?= e($titulo ?? '') ?></h1>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <a href="<?= e(url('index.php')) ?>" target="_blank" class="topbar-portal-btn d-none d-sm-inline-flex align-items-center gap-1.5 text-decoration-none">
                    <i class="bi bi-box-arrow-up-right text-primary"></i>
                    <span>Portal público</span>
                </a>
                <div class="dropdown">
                    <button class="btn topbar-user-btn dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                        <span class="avatar"><?= e(mb_strtoupper(mb_substr($u['nombre'], 0, 1))) ?></span>
                        <span class="d-none d-sm-inline text-start lh-sm">
                            <span class="d-block fw-semibold small text-dark"><?= e($u['nombre']) ?></span>
                            <span class="d-block text-secondary" style="font-size:.72rem"><?= e(ROLES[$u['rol']]) ?></span>
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-1">
                        <li><a class="dropdown-item" href="<?= e(url('admin/perfil.php')) ?>"><i class="bi bi-person-circle me-2 text-primary"></i>Mi perfil</a></li>
                        <li><a class="dropdown-item d-sm-none" href="<?= e(url('index.php')) ?>" target="_blank"><i class="bi bi-box-arrow-up-right me-2 text-secondary"></i>Portal público</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="post" action="<?= e(url('admin/logout.php')) ?>">
                                <?= csrf_field() ?>
                                <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="content">
            <?php foreach (flashes() as $f): ?>
                <div class="alert alert-<?= e($f['tipo']) ?> alert-dismissible fade show" role="alert">
                    <?= e($f['msg']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endforeach; ?>
            <?php if (!puede_editar() && !tiene_rol('atencion')): ?>
                <div class="alert alert-info py-2 small"><i class="bi bi-eye me-1"></i>Estás en modo <strong>solo lectura</strong>.</div>
            <?php endif; ?>
