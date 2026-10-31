<?php
/**
 * Script de inicialización de datos de prueba para La Nueva Esperanza.
 * Solo ejecutable por CLI.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Solo CLI');
}

require __DIR__ . '/../includes/app.php';
$pdo = db();

echo "Sembrando datos de demostración para La Nueva Esperanza...\n";

// 1. Configuración de la organización
$configuracion = [
    'nombre_organizacion' => 'La Nueva Esperanza',
    'lema' => 'Llevando bienestar, seguridad alimentaria y esperanza a cada familia',
    'telefono' => '+58 412 1234567',
    'email_contacto' => 'contacto@lanuevaesperanza.org',
    'direccion' => 'Calle Principal, Edificio Comunitario, Piso 1',
    'moneda_defecto' => 'USD'
];
foreach ($configuracion as $k => $v) {
    $st = $pdo->prepare('INSERT INTO configuracion (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)');
    $st->execute([$k, $v]);
}

// 2. Rubros
$rubros = [
    ['Bolsas Básicas', 'Productos esenciales de la canasta alimentaria para el sustento diario', '#0ea5e9', 1],
    ['Combos Familiares', 'Contenido reforzado pensado para familias numerosas', '#10b981', 2],
    ['Combos Proteicos', 'Suplementos de proteínas, granos seleccionados y lácteos', '#f59e0b', 3],
    ['Combos Especiales', 'Ediciones de temporada con víveres e higiene personal', '#8b5cf6', 4],
];
$rubroIds = [];
foreach ($rubros as [$nom, $desc, $color, $orden]) {
    $st = $pdo->prepare('INSERT INTO rubros (nombre, descripcion, color, orden, activo) VALUES (?, ?, ?, ?, 1) ON DUPLICATE KEY UPDATE descripcion = VALUES(descripcion), color = VALUES(color), orden = VALUES(orden)');
    $st->execute([$nom, $desc, $color, $orden]);
    $stId = $pdo->prepare('SELECT id FROM rubros WHERE nombre = ?');
    $stId->execute([$nom]);
    $rubroIds[$nom] = (int)$stId->fetchColumn();
}

// 3. Productos (Bolsas y Combos)
$adminId = $pdo->query("SELECT id FROM usuarios WHERE rol = 'superadmin' LIMIT 1")->fetchColumn() ?: null;

$productos = [
    [
        'rubro' => 'Bolsas Básicas',
        'tipo' => 'bolsa',
        'nombre' => 'Bolsa Nutricional Solidaria',
        'descripcion' => 'Paquete esencial con los productos básicos de consumo diario.',
        'contenido' => "2 kg Harina de Maíz Blanco\n2 kg Arroz Tradicional\n1 kg Pasta Larga\n1 L Aceite Vegetal\n1 kg Azúcar Refinada\n500 g Caraotas Negras\n1 kg Sal Marina",
        'precio' => 12.50,
        'moneda' => 'USD'
    ],
    [
        'rubro' => 'Combos Familiares',
        'tipo' => 'combo',
        'nombre' => 'Combo Familiar Completo',
        'descripcion' => 'Mayor rendimiento con variedad de harinas, granos y enlatados.',
        'contenido' => "4 kg Harina de Maíz\n3 kg Arroz Primera Calidad\n2 kg Pasta Corta y Larga\n2 L Aceite de Soya\n2 kg Azúcar\n1 kg Lentejas Seleccionadas\n1 kg Harina de Trigo\n2 Latas de Atún en Aceite",
        'precio' => 24.00,
        'moneda' => 'USD'
    ],
    [
        'rubro' => 'Combos Proteicos',
        'tipo' => 'combo',
        'nombre' => 'Combo Proteína y Granos',
        'descripcion' => 'Enfocado en fuentes nutritivas de proteínas y cereales.',
        'contenido' => "1 kg Caraotas Negras\n1 kg Lentejas\n1 kg Frijoles Rojos\n4 Latas de Sardinas en Tomate\n2 Latas de Atún\n1 kg Leche en Polvo Completa\n500 g Avena en Hojuelas",
        'precio' => 18.50,
        'moneda' => 'USD'
    ],
    [
        'rubro' => 'Combos Especiales',
        'tipo' => 'combo',
        'nombre' => 'Mega Combo Despensa Mensual',
        'descripcion' => 'La opción más completa con más de 15 artículos de víveres esenciales.',
        'contenido' => "5 kg Harina de Maíz\n4 kg Arroz\n3 kg Pasta Surtida\n2 L Aceite Vegetal\n2 kg Azúcar\n2 kg Granos Mixtos\n2 kg Harina de Trigo Todo Uso\n1 kg Leche en Polvo\n4 Latas de Enlatados Varios\n1 Café Molido 250g",
        'precio' => 38.00,
        'moneda' => 'USD'
    ]
];

$productoIds = [];
foreach ($productos as $p) {
    $rubroId = $rubroIds[$p['rubro']] ?? null;
    $st = $pdo->prepare('SELECT id, precio, moneda FROM productos WHERE nombre = ?');
    $st->execute([$p['nombre']]);
    $existente = $st->fetch();

    if ($existente) {
        $prodId = (int)$existente['id'];
        $pdo->prepare('UPDATE productos SET rubro_id = ?, tipo = ?, descripcion = ?, contenido = ?, precio = ?, moneda = ?, activo = 1 WHERE id = ?')
            ->execute([$rubroId, $p['tipo'], $p['descripcion'], $p['contenido'], $p['precio'], $p['moneda'], $prodId]);
    } else {
        $stIns = $pdo->prepare('INSERT INTO productos (rubro_id, tipo, nombre, descripcion, contenido, precio, moneda, activo) VALUES (?, ?, ?, ?, ?, ?, ?, 1)');
        $stIns->execute([$rubroId, $p['tipo'], $p['nombre'], $p['descripcion'], $p['contenido'], $p['precio'], $p['moneda']]);
        $prodId = (int)$pdo->lastInsertId();
        // Historial inicial
        $pdo->prepare('INSERT INTO historial_precios (producto_id, precio_anterior, precio_nuevo, moneda, usuario_id) VALUES (?, 0, ?, ?, ?)')
            ->execute([$prodId, $p['precio'], $p['moneda'], $adminId]);
    }
    $productoIds[$p['nombre']] = $prodId;
}

// 4. Entregas de la semana actual
$hoy = new DateTimeImmutable('today');
$lunes = $hoy->modify('monday this week');

$entregas = [
    [
        'fecha' => $lunes->format('Y-m-d'),
        'hora' => '08:30:00',
        'sector' => 'Sector Bella Vista - Zona Norte',
        'direccion' => 'Cancha Techada al lado del Módulo Asistencial',
        'cantidad' => 120,
        'producto' => 'Bolsa Nutricional Solidaria',
        'responsable' => 'Carmen Rivas (Consejo Comunal)',
        'telefono' => '0414-9876543',
        'estado' => 'entregada',
        'notas' => 'Entrega finalizada con éxito y firmas de recepción completadas.'
    ],
    [
        'fecha' => $lunes->modify('+2 days')->format('Y-m-d'),
        'hora' => '09:00:00',
        'sector' => 'Comunidad La Esperanza Alta',
        'direccion' => 'Casa Comunal Sector 2, frente a la Plaza Bolívar',
        'cantidad' => 85,
        'producto' => 'Combo Familiar Completo',
        'responsable' => 'Marcos Peña (Coordinador de Ruta)',
        'telefono' => '0412-5551234',
        'estado' => 'en_camino',
        'notas' => 'Cargamento listo en camión de despacho.'
    ],
    [
        'fecha' => $lunes->modify('+4 days')->format('Y-m-d'),
        'hora' => '10:00:00',
        'sector' => 'Sector El Valle - Manzana D',
        'direccion' => 'Escuela Básica Bolivariana Los Libertadores',
        'cantidad' => 150,
        'producto' => 'Bolsa Nutricional Solidaria',
        'responsable' => 'Yolanda González',
        'telefono' => '0416-7778899',
        'estado' => 'programada',
        'notas' => 'Confirmar lista de beneficiarios el día previo.'
    ],
    [
        'fecha' => $lunes->modify('+5 days')->format('Y-m-d'),
        'hora' => '08:00:00',
        'sector' => 'Urbanización San Francisco',
        'direccion' => 'Estacionamiento Central del Centro Cívico',
        'cantidad' => 200,
        'producto' => 'Combo Proteína y Granos',
        'responsable' => 'Héctor Mendoza',
        'telefono' => '0424-3334455',
        'estado' => 'programada',
        'notas' => 'Distribución organizada por números de ticket.'
    ],
];

// Limpiamos entregas de esta semana si existían para no duplicar en repetidas ejecuciones
$pdo->prepare('DELETE FROM entregas WHERE fecha_entrega BETWEEN ? AND ?')
    ->execute([$lunes->format('Y-m-d'), $lunes->modify('+6 days')->format('Y-m-d')]);

foreach ($entregas as $en) {
    $prodId = $productoIds[$en['producto']] ?? null;
    $pdo->prepare('INSERT INTO entregas (fecha_entrega, hora, sector, direccion, cantidad_bolsas, producto_id, responsable, telefono, estado, notas, creado_por) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
        ->execute([$en['fecha'], $en['hora'], $en['sector'], $en['direccion'], $en['cantidad'], $prodId, $en['responsable'], $en['telefono'], $en['estado'], $en['notas'], $adminId]);
}

// 5. Estatutos y reglamentos
$estatutos = [
    [
        'titulo' => 'Capítulo I: De la Naturaleza, Objeto y Principios de la Organización',
        'contenido' => "Artículo 1. La Nueva Esperanza es una iniciativa social comunitaria orientada a garantizar el acceso digno, transparente y continuo a bolsas de alimentos y combos solidarios a precio de costo para las familias de los sectores beneficiarios.\n\nArtículo 2. Son principios fundamentales: la equidad en la distribución, la transparencia en los costos, la puntualidad en el cronograma semanal y la rendición pública de cuentas.",
        'orden' => 1
    ],
    [
        'titulo' => 'Capítulo II: Criterios de Asignación y Registro de Beneficiarios',
        'contenido' => "Artículo 3. El derecho a la adquisición de bolsas y combos se asigna a través de los censos comunitarios actualizados periódicamente por los responsables de cada sector.\n\nArtículo 4. Ningún beneficiario pagará montos superiores a las tarifas oficiales publicadas en este portal. Cualquier cobro adicional no autorizado debe ser reportado a los coordinadores.",
        'orden' => 2
    ],
    [
        'titulo' => 'Capítulo III: De la Programación y Rutas de Entrega Semanal',
        'contenido' => "Artículo 5. Las rutas de entrega son publicadas semanalmente en este sitio con indicación de fecha, horario estimado, sector de acopio y cantidad asignada.\n\nArtículo 6. El responsable designado de cada sector verificará el precintado de los combos y la coincidencia de los rubros contenidos con lo aprobado por la directiva.",
        'orden' => 3
    ]
];

foreach ($estatutos as $est) {
    $st = $pdo->prepare('SELECT id FROM estatutos WHERE titulo = ?');
    $st->execute([$est['titulo']]);
    if (!$st->fetch()) {
        $pdo->prepare('INSERT INTO estatutos (titulo, contenido, orden, publicado) VALUES (?, ?, ?, 1)')
            ->execute([$est['titulo'], $est['contenido'], $est['orden']]);
    }
}

// 6. Publicaciones / Noticias
$noticias = [
    [
        'titulo' => 'Cronograma de distribución de bolsas y combos para esta semana',
        'resumen' => 'Conoce las fechas, horas y puntos de entrega programados en las distintas comunidades.',
        'contenido' => "Nos complace anunciar el cronograma oficial de distribución para esta semana. Nuestros equipos de logística y los consejos comunales han coordinado los puntos de entrega en Bella Vista, La Esperanza Alta, El Valle y San Francisco.\n\nRecordamos a todos los vecinos verificar los rubros correspondientes a su sector y acudir con su identificación correspondiente en los horarios pautados.",
    ],
    [
        'titulo' => 'Actualización de rubros y nuevos combos proteicos disponibles',
        'resumen' => 'Hemos incorporado más fuentes de proteína y granos seleccionados en nuestros combos mensuales.',
        'contenido' => "Gracias a alianzas directas con productores locales, logramos incorporar al catálogo el nuevo Combo Proteína y Granos, además de mantener estables los precios de la Bolsa Nutricional Solidaria.\n\nPuedes consultar el detalle exacto de artículos contenidos y los precios vigentes en la sección correspondiente de este sitio.",
    ]
];

foreach ($noticias as $not) {
    $st = $pdo->prepare('SELECT id FROM publicaciones WHERE titulo = ?');
    $st->execute([$not['titulo']]);
    if (!$st->fetch()) {
        $pdo->prepare('INSERT INTO publicaciones (titulo, resumen, contenido, publicado, fecha_publicacion, creado_por) VALUES (?, ?, ?, 1, NOW(), ?)')
            ->execute([$not['titulo'], $not['resumen'], $not['contenido'], $adminId]);
    }
}

echo "¡Datos sembrados correctamente!\n";
