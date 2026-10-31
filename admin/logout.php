<?php
require __DIR__ . '/../includes/app.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(url('admin/index.php'));
}
csrf_check();
if (usuario_id()) {
    auditar('logout', 'usuarios', usuario_id(), 'Cierre de sesión');
}
cerrar_sesion();
session_start();
flash('success', 'Sesión cerrada correctamente.');
redirect(url('admin/login.php'));
