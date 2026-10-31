<?php
/**
 * Crea (o restablece) una cuenta SUPERADMIN.  Solo línea de comandos.
 *
 *   php database/crear_admin.php "Nombre" correo@dominio.com "Contraseña123"
 *
 * Una base de datos nueva no tiene usuarios: sin este paso nadie puede iniciar sesión.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Solo CLI');
}
require __DIR__ . '/../includes/app.php';

[$_, $nombre, $email, $pass] = array_pad($argv, 4, null);
if (!$nombre || !$email || !$pass) {
    fwrite(STDERR, "Uso: php database/crear_admin.php \"Nombre\" correo@dominio.com \"Contraseña123\"\n");
    exit(1);
}
$email = mb_strtolower(trim($email));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Correo inválido\n");
    exit(1);
}
if ($err = validar_password($pass)) {
    fwrite(STDERR, $err . "\n");
    exit(1);
}

$hash = password_hash($pass, PASSWORD_DEFAULT);
$st = db()->prepare(
    "INSERT INTO usuarios (nombre, email, password_hash, rol, activo) VALUES (?,?,?, 'superadmin', 1)
     ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), password_hash = VALUES(password_hash), rol = 'superadmin', activo = 1"
);
$st->execute([$nombre, $email, $hash]);
echo "Superadmin listo: $email\n";
