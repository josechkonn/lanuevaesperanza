<?php
/**
 * Configuración general. Ajustar en producción.
 */
return [
    'db' => [
        'host'    => '127.0.0.1',
        'port'    => 3306,
        'name'    => 'lanuevaesperanza',
        'user'    => 'root',
        'pass'    => '',
        'charset' => 'utf8mb4',
    ],
    // Ruta web base del proyecto (sin barra final). Ej: '' si está en la raíz del dominio.
    'base_url'      => '/lanuevaesperanza',
    'timezone'      => 'America/Caracas',
    'app_name'      => 'La Nueva Esperanza',
    'debug'         => true,
    // Seguridad
    'session_name'  => 'LNE_SESSID',
    'session_idle'  => 60 * 60 * 2,   // 2 horas de inactividad
    'login_max_intentos' => 5,         // por IP/email
    'login_ventana_min'  => 15,        // minutos de bloqueo
    // Subidas
    'upload_max_mb' => 8,
    'por_pagina'    => 15,
];
