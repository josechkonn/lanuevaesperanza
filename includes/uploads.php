<?php
/**
 * Subida segura de archivos (imágenes y PDF).
 * Los archivos se guardan en /uploads/<carpeta>/ con nombre aleatorio y se valida
 * el tipo MIME real con finfo (no la extensión que envía el navegador).
 */

const TIPOS_SUBIDA = [
    'imagen' => ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'],
    'pdf'    => ['application/pdf' => 'pdf'],
];

/**
 * @return array [ruta_relativa|null, error|null]  (null,null si no se envió archivo)
 */
function subir_archivo(string $campo, string $tipo, string $carpeta): array
{
    global $CONFIG;
    if (empty($_FILES[$campo]) || ($_FILES[$campo]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }
    $f = $_FILES[$campo];
    if ($f['error'] !== UPLOAD_ERR_OK) {
        return [null, 'Error al subir el archivo (código ' . (int)$f['error'] . ').'];
    }
    if ($f['size'] > $CONFIG['upload_max_mb'] * 1024 * 1024) {
        return [null, 'El archivo supera ' . $CONFIG['upload_max_mb'] . ' MB.'];
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $permitidos = TIPOS_SUBIDA[$tipo] ?? [];
    if (!isset($permitidos[$mime])) {
        return [null, 'Tipo de archivo no permitido (' . e($mime) . ').'];
    }
    $carpeta = preg_replace('/[^a-z0-9_-]/i', '', $carpeta);
    $dir = __DIR__ . '/../uploads/' . $carpeta;
    if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
        return [null, 'No se pudo crear la carpeta de subidas.'];
    }
    $nombre = date('Ymd') . '_' . bin2hex(random_bytes(8)) . '.' . $permitidos[$mime];
    if (!move_uploaded_file($f['tmp_name'], "$dir/$nombre")) {
        return [null, 'No se pudo guardar el archivo.'];
    }
    return ["uploads/$carpeta/$nombre", null];
}

function borrar_archivo(?string $ruta): void
{
    if (!$ruta || !str_starts_with($ruta, 'uploads/') || str_contains($ruta, '..')) return;
    $abs = __DIR__ . '/../' . $ruta;
    if (is_file($abs)) @unlink($abs);
}
