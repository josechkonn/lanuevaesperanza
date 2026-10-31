<?php
// index.php - Portal Principal: EPSDC La Nueva Esperanza
require __DIR__ . '/includes/app.php';

$pdo = db();
$org = config_get('nombre_organizacion', 'La Nueva Esperanza');
$lema = config_get('lema', 'Caracterización Territorial y Soberanía Agroalimentaria');
$tel = config_get('telefono', '+58 412 1234567');
$email = config_get('email_contacto', 'contacto@lanuevaesperanza.org');
$dir = config_get('direccion', 'Estado Táchira, Andes Venezolanos');
$logo = config_get('logo');

// Procesamiento de formulario de comunicación de productores (Literal G del Objeto Social)
$errorProductor = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_comunicacion_productor'])) {
    csrf_check();
    $nombre = post('nombre');
    $sector = post('sector_comunidad');
    $telefono = post('telefono');
    $correo = post('correo') ?: null;
    $tipo = post('tipo');
    $asunto = post('asunto');
    $mensaje = post('mensaje');

    try {
        if ($nombre === '' || mb_strlen($nombre) > 150) throw new RuntimeException('El nombre o unidad de producción es obligatorio.');
        if ($sector === '' || mb_strlen($sector) > 150) throw new RuntimeException('El sector o comunidad es obligatorio.');
        if ($telefono === '' || mb_strlen($telefono) > 40) throw new RuntimeException('El número telefónico de contacto es obligatorio.');
        if ($asunto === '' || mb_strlen($asunto) > 200) throw new RuntimeException('El asunto es obligatorio.');
        if ($mensaje === '') throw new RuntimeException('Por favor detalla tu mensaje.');
        if (!in_array($tipo, ['oferta_rubro', 'requerimiento_tecnico', 'sugerencia_observacion', 'otro'], true)) {
            $tipo = 'oferta_rubro';
        }

        [$adj, $errAdj] = subir_archivo('adjunto', 'imagen', 'imagenes');
        if ($errAdj) {
            [$adj, $errAdj2] = subir_archivo('adjunto', 'pdf', 'documentos');
            if ($errAdj2 && !str_contains($errAdj, 'Tipo de archivo')) throw new RuntimeException($errAdj);
        }

        $pdo->prepare(
            'INSERT INTO comunicaciones_productores (nombre, sector_comunidad, telefono, correo, tipo, asunto, mensaje, adjunto)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([$nombre, $sector, $telefono, $correo, $tipo, $asunto, $mensaje, $adj]);

        $idNuevo = (int)$pdo->lastInsertId();
        auditar('comunicacion_productor', 'comunicaciones_productores', $idNuevo, "$nombre ($tipo) - $asunto");
        flash('success', "¡Excelente, $nombre! Tu comunicación #$idNuevo ha sido registrada exitosamente. El equipo de la EPSDC se pondrá en contacto contigo.");
        redirect(url('#productores'));
    } catch (Throwable $ex) {
        $errorProductor = $ex->getMessage();
    }
}

// 1. Entregas de la semana en curso
$lunes = date('Y-m-d', strtotime('monday this week'));
$domingo = date('Y-m-d', strtotime('sunday this week'));
$stEnt = $pdo->prepare(
    "SELECT e.*, p.nombre AS producto, p.tipo AS producto_tipo
     FROM entregas e
     LEFT JOIN productos p ON p.id = e.producto_id
     WHERE e.fecha_entrega BETWEEN ? AND ? AND e.estado <> 'cancelada'
     ORDER BY e.fecha_entrega ASC, e.hora ASC"
);
$stEnt->execute([$lunes, $domingo]);
$entregasSemana = $stEnt->fetchAll();

// 2. Productos activos (bolsas y combos agrícolas)
$productosDB = $pdo->query(
    "SELECT p.*, r.nombre AS rubro, r.color AS rubro_color
     FROM productos p
     LEFT JOIN rubros r ON r.id = p.rubro_id
     WHERE p.activo = 1
     ORDER BY p.tipo ASC, p.precio ASC"
)->fetchAll();

// 3. Publicaciones y noticias recientes
$publicacionesDB = $pdo->query(
    "SELECT pb.*, u.nombre AS autor
     FROM publicaciones pb
     LEFT JOIN usuarios u ON u.id = pb.creado_por
     WHERE pb.publicado = 1
     ORDER BY pb.fecha_publicacion DESC
     LIMIT 3"
)->fetchAll();

$pageTitle = 'Inicio • Soberanía Agroalimentaria y Red Comunal';
require __DIR__ . '/includes/header.php';
?>

<!-- 1. HERO MONUMENTAL ANDINO (Estándar Editorial de Alta Gama • Sin Estética de IA) -->
<section id="inicio" class="relative bg-[#0a1c14] text-white overflow-hidden pt-16 pb-16 lg:pt-20 lg:pb-20">
    <!-- Curvas topográficas andinas en SVG orgánico (Curvas de nivel de alta montaña, no matrices de puntos) -->
    <div class="absolute inset-0 opacity-[0.07] pointer-events-none overflow-hidden flex items-center justify-center">
        <svg class="w-[140%] h-[140%] min-w-[1000px]" viewBox="0 0 1200 800" fill="none" stroke="currentColor">
            <path d="M-100,200 C200,100 400,350 700,200 C1000,50 1100,280 1300,180" stroke-width="1.5" />
            <path d="M-100,300 C150,180 450,420 750,280 C1050,140 1150,360 1300,270" stroke-width="1.5" />
            <path d="M-100,400 C180,260 480,500 800,360 C1100,220 1200,450 1300,360" stroke-width="1.5" />
            <path d="M-100,500 C220,340 520,580 850,440 C1150,300 1250,540 1300,450" stroke-width="1.5" />
            <path d="M-100,600 C250,420 560,660 900,520 C1200,380 1280,620 1300,540" stroke-width="1.5" />
        </svg>
    </div>

    <!-- Resplandor solar suave de montaña -->
    <div class="absolute top-0 right-1/4 w-[500px] h-[500px] bg-emerald-600/10 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-[400px] h-[400px] bg-amber-500/10 rounded-full blur-[100px] pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid lg:grid-cols-12 gap-12 lg:gap-14 items-center">
            
            <!-- Columna Izquierda: Mensaje Editorial y Acciones -->
            <div class="lg:col-span-7 space-y-7 text-center lg:text-left">
                
                <!-- Badge de Denominación de Origen Campesina -->
                <div class="inline-flex items-center gap-2.5 bg-emerald-950/90 border border-emerald-600/40 text-emerald-200 px-4 py-1.5 rounded-full text-xs font-semibold tracking-wide shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                    <span>Macizo Montañoso Andino • La Grita, Estado Táchira</span>
                    <span class="text-emerald-400/60 hidden sm:inline">•</span>
                    <span class="text-amber-300 font-bold hidden sm:inline">2.000 a 3.100 msnm</span>
                </div>
                
                <!-- Titular Monumental con Tipografía Serif y Énfasis Humano -->
                <div>
                    <h1 class="text-4xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight leading-[1.08] text-white">
                        Soberanía <span class="font-serif italic text-amber-300 font-normal">Agroalimentaria</span>
                        <span class="block text-2xl sm:text-4xl lg:text-5xl font-sans font-light text-emerald-100/90 mt-2">y Red Comunal Andina</span>
                    </h1>
                    
                    <!-- Anotación Manuscrita Humana (Caveat Font) -->
                    <div class="mt-4 flex items-center justify-center lg:justify-start gap-2">
                        <span class="font-hand text-2xl sm:text-3xl text-amber-300 -rotate-3 inline-block font-semibold">
                            ¡Cosecha viva todo el año en las altas cumbres!
                        </span>
                        <svg class="w-8 h-8 text-amber-300/80 -rotate-12 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </div>
                </div>
                
                <p class="text-emerald-100/80 text-base sm:text-lg max-w-2xl font-normal leading-relaxed mx-auto lg:mx-0">
                    <?= e($lema) ?>. Producción y arrime de alimentos frescos los <strong>365 días del año</strong> en las terrazas del norte del Táchira, articulando a las Unidades de Producción Familiar (UPF) sin intermediarios especulativos.
                </p>

                <!-- Llamadas a la Acción Principales -->
                <div class="flex flex-col sm:flex-row gap-3.5 justify-center lg:justify-start pt-2">
                    <a href="#combos" class="bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold px-7 py-3.5 rounded-xl shadow-lg transition-transform active:scale-95 text-center flex items-center justify-center gap-2.5 text-xs uppercase tracking-wider">
                        <i class="fa-solid fa-bag-shopping text-sm text-slate-900"></i>
                        <span>Bolsas y Combos Familiares</span>
                    </a>
                    <a href="#entregas" class="bg-emerald-900/90 hover:bg-emerald-800 text-white font-bold px-7 py-3.5 rounded-xl shadow-md transition-transform active:scale-95 text-center flex items-center justify-center gap-2.5 text-xs uppercase tracking-wider border border-emerald-600/50">
                        <i class="fa-solid fa-truck-fast text-sm text-amber-300"></i>
                        <span>Cronograma de Entregas</span>
                    </a>
                    <a href="#productores" class="bg-white/5 hover:bg-white/10 text-emerald-100 font-semibold px-6 py-3.5 rounded-xl border border-emerald-500/30 transition text-center flex items-center justify-center gap-2 text-xs uppercase tracking-wider">
                        <i class="fa-solid fa-handshake-angle text-sm text-amber-300"></i>
                        <span>Portal UPF</span>
                    </a>
                </div>

                <!-- Métricas Fácticas Territoriales (Diseño Campesino Editorial) -->
                <div class="pt-8 grid grid-cols-3 gap-3.5 border-t border-emerald-900/80 max-w-lg mx-auto lg:mx-0">
                    <div class="bg-emerald-950/60 border border-emerald-800/60 rounded-2xl p-4 text-center sm:text-left transition hover:border-emerald-700">
                        <div class="text-2xl sm:text-3xl font-serif font-extrabold text-amber-300">65%</div>
                        <div class="text-[11px] text-emerald-200/90 font-medium mt-1 leading-snug">Motor Agrícola del Táchira</div>
                    </div>
                    <div class="bg-emerald-950/60 border border-emerald-800/60 rounded-2xl p-4 text-center sm:text-left transition hover:border-emerald-700">
                        <div class="text-2xl sm:text-3xl font-serif font-extrabold text-emerald-300">365D</div>
                        <div class="text-[11px] text-emerald-200/90 font-medium mt-1 leading-snug">Riego por Gravedad Andino</div>
                    </div>
                    <div class="bg-emerald-950/60 border border-emerald-800/60 rounded-2xl p-4 text-center sm:text-left transition hover:border-emerald-700">
                        <div class="text-2xl sm:text-3xl font-serif font-extrabold text-amber-300">39</div>
                        <div class="text-[11px] text-emerald-200/90 font-medium mt-1 leading-snug">Rubros de Alta Montaña</div>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Tarjeta de Identidad Territorial de La Grita -->
            <div class="lg:col-span-5">
                <div class="bg-emerald-950/80 p-6 sm:p-7 rounded-3xl border border-emerald-700/60 shadow-2xl relative backdrop-blur-md">
                    <div class="space-y-4 text-white">
                        <div class="flex justify-between items-center border-b border-emerald-800/80 pb-3">
                            <span class="bg-amber-500/20 text-amber-300 border border-amber-500/30 font-bold text-[10px] px-3 py-1 rounded-full uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fa-solid fa-mountain-sun text-amber-400"></i> Sede: La Grita
                            </span>
                            <span class="text-xs font-semibold text-emerald-200"><i class="fa-solid fa-location-dot text-amber-400 mr-1"></i> Táchira • Los Andes</span>
                        </div>
                        
                        <div>
                            <span class="text-[10px] font-bold text-amber-400 uppercase tracking-wider block">Red Comunal Agraria</span>
                            <h3 class="text-xl sm:text-2xl font-serif font-bold text-white mt-0.5">Eje Productor del Norte y los Andes</h3>
                        </div>
                        
                        <div class="space-y-2.5 text-xs text-emerald-100">
                            <div class="bg-emerald-900/60 border border-emerald-800/80 p-3.5 rounded-xl flex items-center justify-between">
                                <span class="flex items-center gap-2"><i class="fa-solid fa-mountain text-amber-300"></i><strong>Jáuregui & Vargas (Táchira):</strong></span>
                                <span class="text-amber-200 font-bold bg-emerald-950 px-2.5 py-0.5 rounded-md text-[11px] border border-emerald-800">Papa, Cebolla, Zanahoria</span>
                            </div>
                            <div class="bg-emerald-900/60 border border-emerald-800/80 p-3.5 rounded-xl flex items-center justify-between">
                                <span class="flex items-center gap-2"><i class="fa-solid fa-seedling text-amber-300"></i><strong>Mérida & Trujillo:</strong></span>
                                <span class="text-amber-200 font-bold bg-emerald-950 px-2.5 py-0.5 rounded-md text-[11px] border border-emerald-800">Ajo Morado, Café, Tomate</span>
                            </div>
                            <div class="bg-emerald-900/60 border border-emerald-800/80 p-3.5 rounded-xl flex items-center justify-between">
                                <span class="flex items-center gap-2"><i class="fa-solid fa-fish text-sky-300"></i><strong>Rubros Emblema:</strong></span>
                                <span class="text-sky-200 font-bold bg-emerald-950 px-2.5 py-0.5 rounded-md text-[11px] border border-emerald-800">Truchas, Café & Panela</span>
                            </div>
                            <div class="bg-emerald-900/60 border border-emerald-800/80 p-3.5 rounded-xl flex items-center justify-between">
                                <span class="flex items-center gap-2"><i class="fa-solid fa-ship text-emerald-300"></i><strong>Conexión Multimodal:</strong></span>
                                <span class="text-emerald-200 font-bold bg-emerald-950 px-2.5 py-0.5 rounded-md text-[11px] border border-emerald-800">Puerto La Ceiba & Frontera</span>
                            </div>
                        </div>

                        <div class="pt-3 flex gap-2">
                            <a href="<?= e(url('rubros.php')) ?>" class="flex-1 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl transition text-center text-xs flex items-center justify-center gap-1.5 shadow-sm">
                                <i class="fa-solid fa-carrot"></i> Ver 39 Rubros
                            </a>
                            <a href="<?= e(url('territorio.php')) ?>" class="flex-1 py-2.5 bg-emerald-900/80 hover:bg-emerald-800 text-white font-semibold rounded-xl transition text-center text-xs flex items-center justify-center gap-1.5 border border-emerald-700/60">
                                <i class="fa-solid fa-map-location-dot text-amber-300"></i> Territorio & Mapa
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- 2. KINETIC MARQUEE STREAM: RUBROS DE LA TIERRA ANDINA (Ley de Densidad Visual) -->
<div class="relative z-20 bg-[#0e2a1f] border-y border-emerald-800/80 py-4 overflow-hidden select-none shadow-sm">
    <div class="flex items-center gap-8 whitespace-nowrap animate-marquee w-max">
        <div class="flex items-center gap-8 text-xs font-bold uppercase tracking-wider text-emerald-100">
            <span class="inline-flex items-center gap-2"><span class="text-base">🥔</span> Papa Granola de Páramo (2.800 msnm)</span>
            <span class="text-amber-400 font-black">•</span>
            <span class="inline-flex items-center gap-2"><span class="text-base">🐟</span> Truchas de Las Porqueras</span>
            <span class="text-amber-400 font-black">•</span>
            <span class="inline-flex items-center gap-2"><span class="text-base">☕</span> Café de Especialidad de Pregonero</span>
            <span class="text-amber-400 font-black">•</span>
            <span class="inline-flex items-center gap-2"><span class="text-base">🍯</span> Panela Artesanal de Humuquena</span>
            <span class="text-amber-400 font-black">•</span>
            <span class="inline-flex items-center gap-2"><span class="text-base">🥕</span> Zanahoria Criolla</span>
            <span class="text-amber-400 font-black">•</span>
            <span class="inline-flex items-center gap-2"><span class="text-base">🧄</span> Ajo Morado Autóctono</span>
            <span class="text-amber-400 font-black">•</span>
            <span class="inline-flex items-center gap-2"><span class="text-base">🥬</span> Hortalizas de Riego Continuo</span>
            <span class="text-amber-400 font-black">•</span>
            <span class="inline-flex items-center gap-2"><span class="text-base">💧</span> 365 Días de Agua de Deshielo</span>
            <span class="text-amber-400 font-black">•</span>
            <span class="inline-flex items-center gap-2"><span class="text-base">🚜</span> 12 Municipios Articulados</span>
            <span class="text-amber-400 font-black">•</span>
            <span class="inline-flex items-center gap-2"><span class="text-base">⛰️</span> Aldea Quebrada de San José</span>
        </div>
        <div class="flex items-center gap-8 text-xs font-bold uppercase tracking-wider text-emerald-100" aria-hidden="true">
            <span class="inline-flex items-center gap-2"><span class="text-base">🥔</span> Papa Granola de Páramo (2.800 msnm)</span>
            <span class="text-amber-400 font-black">•</span>
            <span class="inline-flex items-center gap-2"><span class="text-base">🐟</span> Truchas de Las Porqueras</span>
            <span class="text-amber-400 font-black">•</span>
            <span class="inline-flex items-center gap-2"><span class="text-base">☕</span> Café de Especialidad de Pregonero</span>
            <span class="text-amber-400 font-black">•</span>
            <span class="inline-flex items-center gap-2"><span class="text-base">🍯</span> Panela Artesanal de Humuquena</span>
            <span class="text-amber-400 font-black">•</span>
            <span class="inline-flex items-center gap-2"><span class="text-base">🥕</span> Zanahoria Criolla</span>
            <span class="text-amber-400 font-black">•</span>
            <span class="inline-flex items-center gap-2"><span class="text-base">🧄</span> Ajo Morado Autóctono</span>
            <span class="text-amber-400 font-black">•</span>
            <span class="inline-flex items-center gap-2"><span class="text-base">🥬</span> Hortalizas de Riego Continuo</span>
            <span class="text-amber-400 font-black">•</span>
            <span class="inline-flex items-center gap-2"><span class="text-base">💧</span> 365 Días de Agua de Deshielo</span>
            <span class="text-amber-400 font-black">•</span>
            <span class="inline-flex items-center gap-2"><span class="text-base">🚜</span> 12 Municipios Articulados</span>
            <span class="text-amber-400 font-black">•</span>
            <span class="inline-flex items-center gap-2"><span class="text-base">⛰️</span> Aldea Quebrada de San José</span>
        </div>
    </div>
</div>

<!-- 3. BOLSAS Y COMBOS AGRÍCOLAS (INVERSIÓN RADICAL A LIENZO CREMA CÁLIDO) -->
<section id="combos" class="py-20 lg:py-24 bg-[#faf8f5] relative z-10 shadow-sm border-t border-amber-900/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-12 gap-6">
            <div>
                <span class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-emerald-900 bg-emerald-100/80 px-3.5 py-1 rounded-full border border-emerald-300/80 mb-3">
                    <i class="fa-solid fa-basket-shopping text-emerald-700"></i> Distribución Social Directa
                </span>
                <h2 class="text-3xl sm:text-5xl font-serif font-extrabold text-slate-900 tracking-tight">
                    Bolsas y Combos Familiares
                </h2>
                <div class="flex items-center gap-2 mt-2">
                    <p class="text-slate-600 text-xs sm:text-sm max-w-2xl leading-relaxed">
                        Combinaciones armadas en galpón comunal con peso exacto y productos frescos cosechados en el día.
                    </p>
                    <span class="font-hand text-lg text-emerald-700 font-bold hidden sm:inline -rotate-2">
                        • Cero intermediación especulativa
                    </span>
                </div>
            </div>

            <!-- Controles del Carrusel -->
            <div class="flex items-center gap-2 shrink-0">
                <button onclick="scrollCombos(-1)" id="btnComboPrev" aria-label="Combo anterior" class="w-11 h-11 rounded-2xl bg-white border border-slate-300 text-slate-700 hover:bg-emerald-900 hover:text-white hover:border-emerald-900 transition flex items-center justify-center shadow-xs disabled:opacity-30 disabled:cursor-not-allowed">
                    <i class="fa-solid fa-arrow-left text-sm"></i>
                </button>
                <button onclick="scrollCombos(1)" id="btnComboNext" aria-label="Combo siguiente" class="w-11 h-11 rounded-2xl bg-white border border-slate-300 text-slate-700 hover:bg-emerald-900 hover:text-white hover:border-emerald-900 transition flex items-center justify-center shadow-xs disabled:opacity-30 disabled:cursor-not-allowed">
                    <i class="fa-solid fa-arrow-right text-sm"></i>
                </button>
            </div>
        </div>

        <!-- Dynamic Combos Carousel Track -->
        <div id="combosCarouselTrack" class="flex gap-6 overflow-x-auto snap-x snap-mandatory scroll-smooth pb-4 pt-2 no-scrollbar px-1">
            <?php if (!$productosDB): ?>
                <div class="w-full text-center py-12 text-slate-400 font-bold bg-slate-50 rounded-2xl p-8 border border-slate-200">
                    No hay combos publicados en la base de datos en este momento.
                </div>
            <?php endif; ?>

            <?php foreach ($productosDB as $p):
                $items = array_filter(array_map('trim', explode("\n", (string)$p['contenido'])));
            ?>
                <div class="combo-card w-[86vw] sm:w-[350px] lg:w-[380px] shrink-0 snap-start bg-white border border-slate-200/90 hover:border-slate-300 rounded-3xl p-6 sm:p-7 shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group h-full">
                    <div>
                        <?php if ($p['imagen']): ?>
                            <img src="<?= e(url($p['imagen'])) ?>" alt="<?= e($p['nombre']) ?>" class="w-full h-44 object-cover rounded-2xl mb-4 border border-slate-100 group-hover:scale-[1.01] transition-transform duration-300 shadow-2xs">
                        <?php endif; ?>

                        <div class="flex justify-between items-center mb-3">
                            <span class="bg-slate-100 text-slate-800 border border-slate-200 text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wider">
                                <?= e($p['rubro'] ?: ucfirst($p['tipo'])) ?>
                            </span>
                            <span class="bg-emerald-50 text-emerald-800 border border-emerald-200 text-[10px] font-semibold px-2.5 py-0.5 rounded-full flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Disponible
                            </span>
                        </div>
                        <h3 class="text-xl font-extrabold text-slate-900 mb-2 leading-snug"><?= e($p['nombre']) ?></h3>
                        <?php if ($p['descripcion']): ?>
                            <p class="text-xs text-slate-500 mb-3.5 leading-relaxed"><?= e($p['descripcion']) ?></p>
                        <?php endif; ?>

                        <?php if ($items): ?>
                            <div class="text-xs text-slate-600 mb-4 bg-slate-50/80 p-3.5 rounded-2xl border border-slate-100">
                                <strong class="block text-slate-800 mb-2 font-bold text-[11px]"><i class="fa-solid fa-list-check text-emerald-600 mr-1.5"></i> Contenido incluido:</strong>
                                <ul class="space-y-1.5">
                                    <?php foreach ($items as $it): ?>
                                        <li class="flex items-center gap-2 text-slate-700">
                                            <i class="fa-solid fa-circle-check text-emerald-600 text-[10px] shrink-0"></i> <span><?= e($it) ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="pt-4 border-t border-slate-100">
                        <div class="flex justify-between items-baseline mb-4">
                            <span class="text-xs text-slate-500 font-medium">Precio Oficial</span>
                            <span class="text-3xl font-extrabold text-slate-900 tracking-tight"><?= e(dinero($p['precio'], $p['moneda'])) ?></span>
                        </div>

                        <a href="#entregas" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-3.5 rounded-xl transition text-center flex items-center justify-center gap-2 text-xs uppercase tracking-wider shadow-xs active:scale-95">
                            <i class="fa-solid fa-truck-fast text-xs text-slate-400"></i> Ver Puntos de Distribución
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Carousel Dots & Info Bar -->
        <div id="combosCarouselDotsBar" class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500 border-t border-slate-100 pt-5">
            <span id="combosCarouselInfo" class="font-medium text-slate-500 flex items-center gap-1.5">
                <i class="fa-solid fa-hand-pointer text-slate-400"></i> Desliza o usa las flechas para explorar los combos disponibles
            </span>
            <div id="combosCarouselDots" class="flex items-center gap-1.5">
                <!-- Dots generados con JS -->
            </div>
        </div>

    </div>
</section>

<!-- 3. ENTREGAS DE LA SEMANA (CRONOGRAMA DE RUTAS) -->
<section id="entregas" class="py-20 bg-white border-t border-b border-slate-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end mb-12 gap-6">
            <div>
                <span class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-amber-900 bg-amber-100/80 px-3.5 py-1 rounded-full border border-amber-300/80 mb-3">
                    <i class="fa-solid fa-truck-fast text-amber-700"></i> Cronograma Oficial de Distribución Semanal
                </span>
                <h2 class="text-3xl sm:text-5xl font-serif font-extrabold text-slate-900 tracking-tight">
                    Rutas y Puntos de Despacho
                </h2>
                <div class="flex items-center gap-2 mt-2">
                    <p class="text-slate-600 text-xs sm:text-sm max-w-2xl leading-relaxed">
                        Rutas programadas del <strong><?= fecha_corta($lunes) ?></strong> al <strong><?= fecha_corta($domingo) ?></strong>. Despachos directos desde el galpón central en Quebrada de San José.
                    </p>
                    <span class="font-hand text-lg text-emerald-700 font-bold hidden sm:inline -rotate-1">
                        • Horarios exactos
                    </span>
                </div>
            </div>
            <div class="bg-emerald-50 text-emerald-900 px-4 py-2 rounded-2xl text-xs font-semibold border border-emerald-200/80 flex items-center gap-2 shadow-2xs">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Despachos activos en tiempo real</span>
            </div>
        </div>

        <?php if (!$entregasSemana): ?>
            <div class="bg-[#faf8f5] border border-slate-200 rounded-3xl p-12 text-center text-slate-500 shadow-xs">
                <i class="fa-solid fa-calendar-xmark text-4xl text-slate-400 mb-3"></i>
                <h4 class="font-serif font-bold text-lg text-slate-800">No hay entregas programadas para esta semana</h4>
                <p class="text-xs text-slate-500 mt-1">El cronograma de rutas se publicará en breve por la coordinación de la EPSDC.</p>
            </div>
        <?php else: ?>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php
                $diasNombres = ['Dom', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                foreach ($entregasSemana as $ent):
                    $numDia = date('w', strtotime($ent['fecha_entrega']));
                    $diaTexto = $diasNombres[$numDia];
                    $sectorNombre = $ent['sector'] ?? $ent['lugar'] ?? 'Sector Comunal';
                    $cantidadBolsas = (int)($ent['cantidad_bolsas'] ?? $ent['cantidad_disponible'] ?? 0);
                    $horaFmt = !empty($ent['hora']) ? substr($ent['hora'], 0, 5) : '08:00';
                ?>
                    <div class="bg-white border border-slate-200/90 hover:border-emerald-700/60 rounded-3xl p-6 shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group">
                        <div>
                            <div class="flex justify-between items-center mb-3.5">
                                <span class="bg-amber-100 text-amber-950 border border-amber-300/80 text-[10px] font-bold px-3 py-0.5 rounded-full uppercase tracking-wider">
                                    <?= $diaTexto ?> • <?= date('d/m', strtotime($ent['fecha_entrega'])) ?>
                                </span>
                                <span class="text-xs font-semibold text-slate-400">
                                    <i class="fa-regular fa-clock mr-1 text-slate-400"></i><?= e($horaFmt) ?>
                                </span>
                            </div>
                            <h4 class="font-serif font-extrabold text-slate-900 text-lg mb-1.5 leading-snug group-hover:text-emerald-900 transition-colors">
                                <?= e($sectorNombre) ?>
                            </h4>
                            <p class="text-xs text-slate-500 mb-5 leading-relaxed"><?= e($ent['direccion'] ?: 'Punto de acopio comunal central') ?></p>
                        </div>
                        <div class="pt-3.5 border-t border-slate-100 flex items-center justify-between text-xs">
                            <span class="text-slate-600 font-medium"><?= e($ent['producto'] ?: 'Combo Comunal') ?></span>
                            <span class="font-bold text-emerald-900 bg-emerald-100/80 px-2.5 py-0.5 rounded-full border border-emerald-300/80 text-[11px]"><?= $cantidadBolsas ?> cupos</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<!-- 4. GOOGLE MAPS Y UBICACIÓN ESTRATÉGICA (LA GRITA, QUEBRADA DE SAN JOSÉ) -->
<section id="mapa-venezuela" class="py-20 bg-[#0a1c14] text-white relative overflow-hidden border-b border-emerald-950">
    <!-- Curvas topográficas andinas -->
    <div class="absolute inset-0 opacity-[0.05] pointer-events-none overflow-hidden flex items-center justify-center">
        <svg class="w-[140%] h-[140%]" viewBox="0 0 1200 800" fill="none" stroke="currentColor">
            <path d="M-100,200 C200,100 400,350 700,200 C1000,50 1100,280 1300,180" stroke-width="1.5" />
            <path d="M-100,400 C180,260 480,500 800,360 C1100,220 1200,450 1300,360" stroke-width="1.5" />
        </svg>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        
        <div class="text-center max-w-4xl mx-auto mb-14">
            <span class="bg-amber-400/10 text-amber-300 border border-amber-400/30 text-xs font-bold px-4 py-1.5 rounded-full uppercase tracking-wider inline-flex items-center gap-2 mb-3">
                <i class="fa-solid fa-map-location-dot"></i> Georreferenciación Estratégica Nacional • Estado Táchira
            </span>
            <h2 class="text-3xl sm:text-5xl font-serif font-extrabold text-white tracking-tight">
                Mapa de Venezuela y Ubicación Estratégica
            </h2>
            <h3 class="text-base sm:text-xl font-medium text-emerald-200/90 mt-2">
                Ciudad de La Grita • Municipio Jáuregui (Aldea Quebrada de San José)
            </h3>
            <p class="text-emerald-100/80 mt-4 text-xs sm:text-sm md:text-base leading-relaxed font-normal">
                Nuestra sede principal y centro de acopio comunal se ubican en la <strong>Ciudad de La Grita, Municipio Jáuregui (Aldea Quebrada de San José)</strong>, al noroeste del Estado Táchira. Una posición privilegiada en la alta montaña andina con clima fresco de <strong>18°C a 19°C y presencia frecuente de neblina</strong>, aportando cerca del <strong>65% de la producción total de hortalizas y papas</strong> de la entidad.
            </p>
        </div>

        <div class="grid lg:grid-cols-12 gap-8 items-start mb-12">
            
            <!-- Columna Izquierda: Google Maps Iframe -->
            <div class="lg:col-span-7 bg-[#0e2a1f]/90 border border-emerald-800/60 rounded-3xl p-5 sm:p-6 shadow-2xl relative backdrop-blur-xs">
                
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4 pb-4 border-b border-emerald-800/60">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="text-xs font-bold text-emerald-200 uppercase tracking-wider">Cartografía Digital Interactiva</span>
                    </div>
                    <div class="flex flex-wrap gap-1.5 bg-[#0a1c14] p-1 rounded-xl border border-emerald-800/80 text-[11px] font-semibold">
                        <span class="px-3 py-1.5 rounded-lg bg-emerald-800 text-white flex items-center gap-1.5 shadow-xs">
                            <i class="fa-solid fa-map-location-dot text-amber-300"></i> Google Maps En Vivo
                        </span>
                        <a href="<?= e(url('territorio.php')) ?>" class="px-3 py-1.5 rounded-lg text-emerald-200/80 hover:text-white transition flex items-center gap-1.5">
                            <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i> Abrir Territorio Completo
                        </a>
                    </div>
                </div>

                <div class="relative w-full h-[460px] sm:h-[480px] rounded-2xl overflow-hidden border border-emerald-800/60 shadow-2xl bg-slate-950">
                    <iframe 
                        id="gmapIframeHome"
                        src="https://maps.google.com/maps?q=Aldea+Quebrada+de+San+Jos%C3%A9,+La+Grita,+Municipio+J%C3%A1uregui,+T%C3%A1chira,+Venezuela&t=&z=14&ie=UTF8&iwloc=&output=embed" 
                        class="w-full h-full border-0 filter brightness-95 contrast-105" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade"
                        title="Ubicación Google Maps - EPSDC La Nueva Esperanza">
                    </iframe>

                    <div class="absolute top-3 left-3 z-10 bg-[#0a1c14]/95 border border-emerald-700/60 px-3.5 py-2 rounded-xl text-xs text-white backdrop-blur-md shadow-lg flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 shrink-0"></span>
                        <div>
                            <div class="font-bold text-white flex items-center gap-1.5 leading-tight">
                                <i class="fa-solid fa-location-dot text-rose-500 text-xs"></i> Aldea Quebrada de San José
                            </div>
                            <div class="text-[10px] text-emerald-200/70 font-medium">La Grita, Municipio Jáuregui • Estado Táchira</div>
                        </div>
                    </div>

                    <div class="absolute bottom-3 left-3 right-3 z-10 flex flex-wrap items-center justify-between gap-2 pointer-events-none">
                        <div class="bg-[#0a1c14]/90 border border-emerald-700/60 px-3 py-1.5 rounded-xl text-[10px] text-emerald-200/80 font-mono backdrop-blur-md pointer-events-auto hidden sm:flex items-center gap-2">
                            <i class="fa-solid fa-compass text-amber-300"></i>
                            <span>8°08′00″ N, 71°59′00″ O</span>
                        </div>
                        <div class="flex items-center gap-2 pointer-events-auto ml-auto">
                            <a href="https://www.google.com/maps/search/?api=1&query=Aldea+Quebrada+de+San+Jose+La+Grita+Jauregui+Tachira+Venezuela" 
                               target="_blank" 
                               rel="noopener noreferrer" 
                               class="bg-amber-400 hover:bg-amber-300 text-slate-950 text-xs font-bold px-3.5 py-2 rounded-xl shadow-sm flex items-center gap-2 transition active:scale-95">
                                <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                                <span>Abrir en Google Maps</span>
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Columna Derecha: 4 Bento Highlights -->
            <div class="lg:col-span-5 space-y-4">
                
                <div class="bg-[#0e2a1f]/90 border border-emerald-700/50 hover:border-emerald-500/70 p-6 rounded-3xl shadow-sm relative group transition-all duration-300 backdrop-blur-xs">
                    <div class="flex items-start justify-between gap-3 mb-2">
                        <div class="w-10 h-10 rounded-xl bg-amber-400/10 border border-amber-400/30 text-amber-300 flex items-center justify-center text-lg shrink-0">
                            <i class="fa-solid fa-trophy"></i>
                        </div>
                        <span class="bg-amber-400/10 text-amber-300 border border-amber-400/30 font-bold text-xs px-2.5 py-1 rounded-full uppercase tracking-wider">
                            Motor #1 del Táchira
                        </span>
                    </div>
                    <h4 class="font-serif text-xl font-extrabold text-white mt-1">65% de la Producción Agrícola</h4>
                    <p class="text-xs text-emerald-100/70 leading-relaxed mt-2">
                        El <strong>Municipio Jáuregui (La Grita)</strong> se destaca como el <strong>principal motor agrícola del estado Táchira</strong>, aportando cerca del <strong>65% de la producción total de hortalizas, papas y rubros del campo</strong> de toda la entidad.
                    </p>
                </div>

                <div class="bg-[#0e2a1f]/90 border border-emerald-700/50 hover:border-emerald-500/70 p-6 rounded-3xl shadow-sm relative group transition-all duration-300 backdrop-blur-xs">
                    <div class="flex items-start justify-between gap-3 mb-2">
                        <div class="w-10 h-10 rounded-xl bg-sky-400/10 border border-sky-400/30 text-sky-300 flex items-center justify-center text-lg shrink-0">
                            <i class="fa-solid fa-smog"></i>
                        </div>
                        <span class="bg-sky-400/10 text-sky-300 border border-sky-400/30 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">
                            18°C a 19°C Media
                        </span>
                    </div>
                    <h4 class="font-serif text-lg font-extrabold text-white mt-1">Clima Montañoso Andino y Neblina</h4>
                    <p class="text-xs text-emerald-100/70 leading-relaxed mt-2">
                        Gozamos de un <strong>clima montañoso andino fresco</strong>, con una temperatura media anual de aproximadamente <strong>18°C a 19°C</strong> y la <strong>presencia frecuente de neblina andina</strong>.
                    </p>
                </div>

                <div class="bg-[#0e2a1f]/90 border border-emerald-700/50 hover:border-emerald-500/70 p-6 rounded-3xl shadow-sm relative group transition-all duration-300 backdrop-blur-xs">
                    <div class="flex items-start justify-between gap-3 mb-2">
                        <div class="w-10 h-10 rounded-xl bg-emerald-400/10 border border-emerald-400/30 text-emerald-300 flex items-center justify-center text-lg shrink-0">
                            <i class="fa-solid fa-location-crosshairs"></i>
                        </div>
                        <span class="bg-emerald-400/10 text-emerald-300 border border-emerald-400/30 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">
                            Aldea Quebrada de San José
                        </span>
                    </div>
                    <h4 class="font-serif text-lg font-extrabold text-white mt-1">Sede Central y Galpón de Acopio</h4>
                    <p class="text-xs text-emerald-100/70 leading-relaxed mt-2">
                        Asentados en la <strong>Ciudad de La Grita</strong>, dentro de la <strong>Aldea Quebrada de San José</strong>. Articulación directa con las aldeas productoras de la alta montaña andina.
                    </p>
                </div>

                <div class="bg-[#0e2a1f]/90 border border-emerald-700/50 p-5 rounded-3xl shadow-sm flex items-center justify-between gap-4">
                    <div class="space-y-1">
                        <span class="text-[10px] text-amber-300 font-bold uppercase tracking-wider">Geografía Completa</span>
                        <h5 class="font-serif text-sm font-bold text-white">Cuencas, Municipios y Salida al Caribe</h5>
                        <p class="text-[11px] text-emerald-200/70">Puerto La Ceiba y frontera San Antonio / Cúcuta.</p>
                    </div>
                    <a href="<?= e(url('territorio.php')) ?>" class="px-4 py-2.5 bg-emerald-800 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold transition shrink-0 shadow-xs flex items-center gap-1.5 border border-emerald-600/50">
                        <span>Explorar</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>

            </div>

        </div>

    </div>
</section>

<!-- 5. BENTO CARDS DE ENLACE A PÁGINAS ESPECIALIZADAS -->
<section class="py-20 bg-[#faf8f5] border-b border-stone-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto mb-14">
            <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-emerald-900 bg-emerald-100/80 px-3.5 py-1 rounded-full border border-emerald-300/80 mb-3">
                <i class="fa-solid fa-layer-group text-emerald-700"></i> Ecosistema Digital de la EPSDC
            </span>
            <h2 class="text-3xl sm:text-5xl font-serif font-extrabold text-slate-900 tracking-tight">
                Conozca Nuestra Estructura Integral
            </h2>
            <div class="flex items-center justify-center gap-2 mt-2">
                <p class="text-slate-600 text-xs sm:text-sm max-w-2xl leading-relaxed">
                    Explore las secciones dedicadas a nuestra biodiversidad agronómica, desglose territorial por aldeas y el marco institucional comunitario.
                </p>
                <span class="font-hand text-lg text-emerald-700 font-bold hidden sm:inline -rotate-1">
                    • Información 100% verificada
                </span>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            
            <!-- Card 1: 39 Rubros -->
            <div class="bg-white border border-stone-200/90 hover:border-emerald-700/60 rounded-3xl p-8 shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group">
                <div>
                    <div class="w-14 h-14 rounded-2xl bg-amber-50 border border-amber-200/80 text-amber-700 flex items-center justify-center text-2xl font-bold mb-6 shadow-2xs group-hover:scale-105 transition-transform">
                        <i class="fa-solid fa-carrot"></i>
                    </div>
                    <span class="bg-stone-100 text-stone-700 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider border border-stone-200">Biodiversidad</span>
                    <h3 class="font-serif text-2xl font-extrabold text-slate-900 mt-3 mb-2.5 group-hover:text-emerald-900 transition-colors"><?= e('Catálogo de los 39 Rubros') ?></h3>
                    <p class="text-xs text-slate-500 leading-relaxed mb-5">
                        Conozca las características agronómicas de cada rubro: papa granola, zanahoria, cebolla, ajo criollo, tomate y aliños. Formas de cultivo en terrazas andinas y calendario continuo los 365 días.
                    </p>
                    <div class="flex flex-wrap gap-1.5 mb-6">
                        <span class="bg-[#faf8f5] border border-stone-200 text-stone-700 text-[10px] font-semibold px-2.5 py-1 rounded-lg">Papas de Páramo</span>
                        <span class="bg-[#faf8f5] border border-stone-200 text-stone-700 text-[10px] font-semibold px-2.5 py-1 rounded-lg">Hortalizas</span>
                        <span class="bg-[#faf8f5] border border-stone-200 text-stone-700 text-[10px] font-semibold px-2.5 py-1 rounded-lg">Frutas de Valle</span>
                    </div>
                </div>
                <div class="pt-5 border-t border-stone-100">
                    <a href="<?= e(url('rubros.php')) ?>" class="w-full bg-[#133527] hover:bg-[#1b4332] text-white font-bold py-3.5 rounded-xl transition text-center text-xs uppercase tracking-wider flex items-center justify-center gap-2 shadow-xs active:scale-95">
                        <span>Ver Catálogo Paginado</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

            <!-- Card 2: Territorio y Geografía -->
            <div class="bg-white border border-stone-200/90 hover:border-emerald-700/60 rounded-3xl p-8 shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group">
                <div>
                    <div class="w-14 h-14 rounded-2xl bg-emerald-50 border border-emerald-200/80 text-emerald-800 flex items-center justify-center text-2xl font-bold mb-6 shadow-2xs group-hover:scale-105 transition-transform">
                        <i class="fa-solid fa-map-location-dot"></i>
                    </div>
                    <span class="bg-stone-100 text-stone-700 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider border border-stone-200">Georreferenciación</span>
                    <h3 class="font-serif text-2xl font-extrabold text-slate-900 mt-3 mb-2.5 group-hover:text-emerald-900 transition-colors">Territorio, Cuencas & Mapa</h3>
                    <p class="text-xs text-slate-500 leading-relaxed mb-5">
                        Desglose territorial de los 12 municipios articulados (Jáuregui, Uribante, San José de Bolívar, Queniquea, Humuquena...), truchas de Las Porqueras, café de Pregonero y panela artesanal.
                    </p>
                    <div class="flex flex-wrap gap-1.5 mb-6">
                        <span class="bg-[#faf8f5] border border-stone-200 text-stone-700 text-[10px] font-semibold px-2.5 py-1 rounded-lg">12 Municipios</span>
                        <span class="bg-[#faf8f5] border border-stone-200 text-stone-700 text-[10px] font-semibold px-2.5 py-1 rounded-lg">Truchas, Café & Panela</span>
                        <span class="bg-[#faf8f5] border border-stone-200 text-stone-700 text-[10px] font-semibold px-2.5 py-1 rounded-lg">Puerto La Ceiba</span>
                    </div>
                </div>
                <div class="pt-5 border-t border-stone-100">
                    <a href="<?= e(url('territorio.php')) ?>" class="w-full bg-[#133527] hover:bg-[#1b4332] text-white font-bold py-3.5 rounded-xl transition text-center text-xs uppercase tracking-wider flex items-center justify-center gap-2 shadow-xs active:scale-95">
                        <span>Ver Geografía Completa</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

            <!-- Card 3: Marco Comunal y Fondos -->
            <div class="bg-white border border-stone-200/90 hover:border-emerald-700/60 rounded-3xl p-8 shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group">
                <div>
                    <div class="w-14 h-14 rounded-2xl bg-amber-50 border border-amber-200/80 text-amber-800 flex items-center justify-center text-2xl font-bold mb-6 shadow-2xs group-hover:scale-105 transition-transform">
                        <i class="fa-solid fa-landmark"></i>
                    </div>
                    <span class="bg-stone-100 text-stone-700 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider border border-stone-200">Institucionalidad</span>
                    <h3 class="font-serif text-2xl font-extrabold text-slate-900 mt-3 mb-2.5 group-hover:text-emerald-900 transition-colors">Marco Comunal & Fondos</h3>
                    <p class="text-xs text-slate-500 leading-relaxed mb-5">
                        Estatutos de la EPSDC y destino reglamentado de excedentes: 70% Fondo de Mantenimiento Productivo, 20% Atención a Productores Campesinos y 10% Reinversión Comunitaria al Poder Popular.
                    </p>
                    <div class="flex flex-wrap gap-1.5 mb-6">
                        <span class="bg-[#faf8f5] border border-stone-200 text-stone-700 text-[10px] font-semibold px-2.5 py-1 rounded-lg">Fondo 70%</span>
                        <span class="bg-[#faf8f5] border border-stone-200 text-stone-700 text-[10px] font-semibold px-2.5 py-1 rounded-lg">Fondo 20%</span>
                        <span class="bg-[#faf8f5] border border-stone-200 text-stone-700 text-[10px] font-semibold px-2.5 py-1 rounded-lg">Fondo 10%</span>
                    </div>
                </div>
                <div class="pt-5 border-t border-stone-100">
                    <a href="<?= e(url('estatutos.php')) ?>" class="w-full bg-[#133527] hover:bg-[#1b4332] text-white font-bold py-3.5 rounded-xl transition text-center text-xs uppercase tracking-wider flex items-center justify-center gap-2 shadow-xs active:scale-95">
                        <span>Ver Estatutos y Fondos</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

        </div>

    </div>
</section>

<!-- 6. ATENCIÓN Y COMUNICACIÓN CON EL PRODUCTOR / UPF (LITERAL G) -->
<section id="productores" class="py-20 bg-white border-t border-stone-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="bg-white rounded-3xl border border-stone-200 overflow-hidden shadow-2xl">
            <div class="grid lg:grid-cols-12 gap-0">
                
                <!-- Columna Informativa Izquierda -->
                <div class="lg:col-span-5 bg-[#0a1c14] text-white p-8 lg:p-12 flex flex-col justify-between relative overflow-hidden">
                    <!-- Curvas topográficas andinas -->
                    <div class="absolute inset-0 opacity-[0.05] pointer-events-none overflow-hidden flex items-center justify-center">
                        <svg class="w-[140%] h-[140%]" viewBox="0 0 1200 800" fill="none" stroke="currentColor">
                            <path d="M-100,200 C200,100 400,350 700,200 C1000,50 1100,280 1300,180" stroke-width="1.5" />
                            <path d="M-100,400 C180,260 480,500 800,360 C1100,220 1200,450 1300,360" stroke-width="1.5" />
                        </svg>
                    </div>

                    <div class="relative z-10">
                        <span class="bg-amber-400/10 text-amber-300 border border-amber-400/30 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider inline-block mb-4">
                            <i class="fa-solid fa-shield-halved mr-1"></i> Literal G del Objeto Social
                        </span>
                        <h3 class="font-serif text-2xl sm:text-4xl font-extrabold mb-3 text-white leading-tight">Mesa de Comunicación e Intercambio: UPF y Red Comunal</h3>
                        <p class="text-emerald-100/70 text-xs sm:text-sm leading-relaxed mb-8 font-normal">
                            En cumplimiento de los estatutos de la EPSDC y los <strong>Derechos del Productor Comunal</strong>, establecemos este canal telemático directo para la coordinación técnica, recepción y acopio con las Unidades Socio Productivas y UPF concatenadas.
                        </p>

                        <div class="space-y-4 mb-8 text-xs text-emerald-100">
                            <div class="flex items-start gap-3.5 bg-[#0e2a1f]/80 border border-emerald-800/60 p-4 rounded-2xl backdrop-blur-xs">
                                <div class="w-9 h-9 rounded-xl bg-amber-400/10 border border-amber-400/30 text-amber-300 font-bold flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-basket-shopping text-sm"></i>
                                </div>
                                <div>
                                    <strong class="font-serif text-white block text-sm">Oferta de Cosecha Directa</strong>
                                    <span class="text-emerald-100/70 text-xs">Notifique qué rubros o volúmenes tienen disponibles en su UPF para coordinar el arrime e incorporarlo a la distribución comunal.</span>
                                </div>
                            </div>

                            <div class="flex items-start gap-3.5 bg-[#0e2a1f]/80 border border-emerald-800/60 p-4 rounded-2xl backdrop-blur-xs">
                                <div class="w-9 h-9 rounded-xl bg-sky-400/10 border border-sky-400/30 text-sky-300 font-bold flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-tools text-sm"></i>
                                </div>
                                <div>
                                    <strong class="font-serif text-white block text-sm">Requerimientos y Asistencia Técnica</strong>
                                    <span class="text-emerald-100/70 text-xs">Solicite soporte en embalaje, pesado, transporte o especificaciones de calidad agronómica.</span>
                                </div>
                            </div>

                            <div class="flex items-start gap-3.5 bg-[#0e2a1f]/80 border border-emerald-800/60 p-4 rounded-2xl backdrop-blur-xs">
                                <div class="w-9 h-9 rounded-xl bg-emerald-400/10 border border-emerald-400/30 text-emerald-300 font-bold flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-comments text-sm"></i>
                                </div>
                                <div>
                                    <strong class="font-serif text-white block text-sm">Sugerencias y Observaciones</strong>
                                    <span class="text-emerald-100/70 text-xs">Comunicación directa con la junta directiva para optimizar los procesos de acopio y entrega.</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-emerald-800/60 relative z-10">
                        <span class="text-[11px] text-emerald-200/70 block mb-2 font-medium">Canal Telefónico Directo:</span>
                        <div class="bg-[#0e2a1f] border border-emerald-700/60 rounded-2xl p-3.5 text-center shadow-inner">
                            <i class="fa-solid fa-phone text-amber-300 mr-2"></i>
                            <span class="font-bold text-white text-sm"><?= e($tel) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Columna Formulario Derecha -->
                <div class="lg:col-span-7 p-8 lg:p-12 bg-[#faf8f5]">
                    <div class="mb-6">
                        <h4 class="font-serif text-2xl sm:text-3xl font-extrabold text-slate-900">Formulario Oficial de Comunicación: UPF y Red Comunal</h4>
                        <p class="text-xs text-slate-500 mt-1">Su mensaje será registrado con acuse de recibo en el sistema y atendido por el equipo de coordinación de la EPSDC.</p>
                    </div>

                    <form method="post" enctype="multipart/form-data" class="space-y-4 text-xs">
                        <?= csrf_field() ?>
                        <input type="hidden" name="enviar_comunicacion_productor" value="1">

                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Nombre del Productor / Unidad Familiar (UPF) *</label>
                                <input type="text" name="nombre" required maxlength="150" placeholder="Ej. Juan Pérez / UPF La Rinconada" class="w-full px-4 py-2.5 rounded-xl border border-stone-300 bg-white focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/10 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Sector, Parroquia o Aldea *</label>
                                <input type="text" name="sector_comunidad" required maxlength="150" placeholder="Ej. Aldea Pattueca, Jáuregui" class="w-full px-4 py-2.5 rounded-xl border border-stone-300 bg-white focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/10 focus:outline-none transition">
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Teléfono o Móvil de Contacto *</label>
                                <input type="text" name="telefono" required maxlength="40" placeholder="Ej. 0412-1234567" class="w-full px-4 py-2.5 rounded-xl border border-stone-300 bg-white focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/10 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Correo Electrónico (opcional)</label>
                                <input type="email" name="correo" maxlength="160" placeholder="productor@ejemplo.com" class="w-full px-4 py-2.5 rounded-xl border border-stone-300 bg-white focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/10 focus:outline-none transition">
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tipo de Comunicación *</label>
                            <select name="tipo" class="w-full px-4 py-2.5 rounded-xl border border-stone-300 focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/10 focus:outline-none transition bg-white font-medium text-slate-800">
                                <option value="oferta_rubro">🌾 Oferta de Rubros / Cosecha disponible ("Lo que tengo a la mano")</option>
                                <option value="requerimiento_tecnico">🛠️ Requerimiento Técnico / Logístico o de Transporte</option>
                                <option value="sugerencia_observacion">💡 Sugerencia u Observación para la EPSDC</option>
                                <option value="otro">📝 Otra consulta</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Asunto de la Gestión *</label>
                            <input type="text" name="asunto" required maxlength="200" placeholder="Ej. Disponibilidad de 800 kg de papa y zanahoria lista para arrimar" class="w-full px-4 py-2.5 rounded-xl border border-stone-300 bg-white focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/10 focus:outline-none transition">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Detalle del Mensaje y Requerimiento *</label>
                            <textarea name="mensaje" required rows="4" placeholder="Indique cantidades estimadas, fecha aproximada de cosecha, apoyo logístico requerido o su observación para la coordinación..." class="w-full px-4 py-2.5 rounded-xl border border-stone-300 bg-white focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/10 focus:outline-none transition"></textarea>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Adjuntar Foto de Muestra o Documento (opcional)</label>
                            <input type="file" name="adjunto" accept="image/*,application/pdf" class="w-full px-3 py-2 rounded-xl border border-stone-300 bg-white text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-stone-200 file:text-slate-800 hover:file:bg-stone-300">
                            <span class="text-[11px] text-slate-400 mt-1 block">Fotos de la siembra/cosecha o documentos en formato PDF o imagen (máx. 8 MB).</span>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full sm:w-auto bg-[#133527] hover:bg-[#1b4332] text-white font-bold px-8 py-4 rounded-xl shadow-md hover:shadow-xl transition-all duration-300 uppercase tracking-wider text-xs flex items-center justify-center gap-2 active:scale-95">
                                <i class="fa-solid fa-paper-plane text-xs"></i> Enviar Comunicación a la EPSDC
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>

    </div>
</section>

<!-- 7. BOLETÍN INFORMATIVO Y COMUNICADOS (SI EXISTEN EN MYSQL) -->
<?php if ($publicacionesDB): ?>
<section id="noticias" class="py-20 bg-[#faf8f5] border-t border-stone-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto mb-14">
            <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-emerald-900 bg-emerald-100/80 px-3.5 py-1 rounded-full border border-emerald-300/80 mb-3">
                <i class="fa-solid fa-newspaper text-emerald-700"></i> Boletín Informativo
            </span>
            <h2 class="text-3xl sm:text-5xl font-serif font-extrabold text-slate-900 tracking-tight">
                Noticias y Comunicados Oficiales
            </h2>
            <div class="flex items-center justify-center gap-2 mt-2">
                <p class="text-slate-600 text-xs sm:text-sm max-w-2xl leading-relaxed">
                    Información de interés para la comunidad, cronogramas extraordinarios y novedades institucionales.
                </p>
                <span class="font-hand text-lg text-emerald-700 font-bold hidden sm:inline -rotate-1">
                    • Canal oficial
                </span>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <?php foreach ($publicacionesDB as $pub): ?>
                <div class="bg-white border border-stone-200/90 hover:border-emerald-700/60 rounded-3xl p-6 shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group">
                    <div>
                        <?php if ($pub['imagen']): ?>
                            <img src="<?= e(url($pub['imagen'])) ?>" alt="" class="w-full h-48 object-cover rounded-2xl mb-4 border border-stone-100 shadow-2xs group-hover:scale-[1.01] transition-transform duration-300">
                        <?php endif; ?>
                        <div class="text-[11px] text-slate-400 mb-2 flex items-center gap-2">
                            <span><i class="fa-regular fa-calendar"></i> <?= fecha_corta($pub['fecha_publicacion']) ?></span>
                            <?php if ($pub['autor']): ?>
                                <span>• <i class="fa-regular fa-user"></i> <?= e($pub['autor']) ?></span>
                            <?php endif; ?>
                        </div>
                        <h3 class="font-serif text-xl font-extrabold text-slate-900 mb-2 leading-snug group-hover:text-emerald-900 transition-colors"><?= e($pub['titulo']) ?></h3>
                        <p class="text-xs text-slate-600 leading-relaxed mb-4"><?= e($pub['resumen'] ?: mb_strimwidth((string)$pub['contenido'], 0, 140, '...')) ?></p>
                    </div>

                    <?php if ($pub['contenido'] && mb_strlen($pub['contenido']) > mb_strlen((string)$pub['resumen'])): ?>
                        <button type="button" onclick="openPubModal('pub_<?= $pub['id'] ?>')" class="text-emerald-800 hover:text-emerald-950 font-bold text-xs flex items-center gap-1.5 pt-3 border-t border-stone-100 transition-colors">
                            <span>Leer comunicado completo</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Modales de Noticias Oficiales (Ubicados fuera del grid para garantizar cobertura total de viewport sin conflicto con transform) -->
        <?php foreach ($publicacionesDB as $pub): ?>
            <?php if ($pub['contenido'] && mb_strlen($pub['contenido']) > mb_strlen((string)$pub['resumen'])): ?>
                <div id="pub_<?= $pub['id'] ?>" class="pub-modal fixed inset-0 z-[100] hidden bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 sm:p-6" onclick="if(event.target === this) closePubModal('pub_<?= $pub['id'] ?>')">
                    <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl relative max-h-[88vh] overflow-y-auto border border-stone-200">
                        <button type="button" onclick="closePubModal('pub_<?= $pub['id'] ?>')" class="absolute top-5 right-5 w-9 h-9 rounded-full bg-stone-100 hover:bg-stone-200 text-slate-500 hover:text-slate-800 flex items-center justify-center transition-colors shadow-2xs" aria-label="Cerrar comunicado">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                        <div class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider mb-2 flex flex-wrap items-center gap-2">
                            <span class="inline-block w-2 h-2 rounded-full bg-emerald-600"></span>
                            <span>Comunicado Oficial</span>
                            <span class="text-slate-400">• <i class="fa-regular fa-calendar mr-1"></i><?= fecha_corta($pub['fecha_publicacion']) ?></span>
                            <?php if ($pub['autor']): ?>
                                <span class="text-slate-400">• <i class="fa-regular fa-user mr-1"></i><?= e($pub['autor']) ?></span>
                            <?php endif; ?>
                        </div>
                        <h3 class="font-serif text-2xl sm:text-3xl font-extrabold text-slate-900 mb-4 leading-tight"><?= e($pub['titulo']) ?></h3>
                        <?php if ($pub['imagen']): ?>
                            <img src="<?= e(url($pub['imagen'])) ?>" alt="" class="w-full max-h-72 object-cover rounded-2xl mb-5 border border-stone-100 shadow-sm">
                        <?php endif; ?>
                        <div class="text-xs sm:text-sm text-slate-700 leading-relaxed whitespace-pre-line border-t border-stone-100 pt-4"><?= e($pub['contenido']) ?></div>
                        <div class="mt-6 pt-4 border-t border-stone-100 flex justify-end">
                            <button type="button" onclick="closePubModal('pub_<?= $pub['id'] ?>')" class="px-5 py-2.5 bg-stone-100 hover:bg-stone-200 text-slate-700 font-bold rounded-xl text-xs transition">
                                Cerrar comunicado
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>

    </div>
</section>
<?php endif; ?>

<!-- SCRIPTS ESPECÍFICOS DE LA PÁGINA DE INICIO (CARRUSEL DE COMBOS Y MODALES) -->
<script>
    window.addEventListener('DOMContentLoaded', () => {
        initCombosCarousel();
    });

    // Funciones del Carousel de Bolsas y Combos Agrícolas
    function scrollCombos(direction) {
        const track = document.getElementById('combosCarouselTrack');
        if (!track) return;
        const firstCard = track.querySelector('.combo-card');
        const cardWidth = firstCard ? firstCard.offsetWidth + 24 : 360;
        track.scrollBy({ left: direction * cardWidth, behavior: 'smooth' });
    }

    function initCombosCarousel() {
        const track = document.getElementById('combosCarouselTrack');
        const prevBtn = document.getElementById('btnComboPrev');
        const nextBtn = document.getElementById('btnComboNext');
        const dotsContainer = document.getElementById('combosCarouselDots');
        const infoText = document.getElementById('combosCarouselInfo');
        if (!track) return;

        const cards = track.querySelectorAll('.combo-card');
        if (cards.length === 0) {
            if (prevBtn) prevBtn.style.display = 'none';
            if (nextBtn) nextBtn.style.display = 'none';
            const dotsBar = document.getElementById('combosCarouselDotsBar');
            if (dotsBar) dotsBar.style.display = 'none';
            return;
        }

        if (dotsContainer) {
            dotsContainer.innerHTML = '';
            cards.forEach((card, i) => {
                const dot = document.createElement('button');
                dot.setAttribute('aria-label', `Ir al combo ${i + 1}`);
                dot.className = `h-2.5 rounded-full transition-all duration-300 ${i === 0 ? 'bg-agro-700 w-7' : 'bg-slate-300 hover:bg-slate-400 w-2.5'}`;
                dot.onclick = () => {
                    const cardWidth = card.offsetWidth + 24;
                    track.scrollTo({ left: i * cardWidth, behavior: 'smooth' });
                };
                dotsContainer.appendChild(dot);
            });
        }

        function updateCarouselState() {
            const maxScrollLeft = track.scrollWidth - track.clientWidth;
            if (prevBtn) {
                prevBtn.disabled = track.scrollLeft <= 5;
            }
            if (nextBtn) {
                nextBtn.disabled = track.scrollLeft >= maxScrollLeft - 5;
            }

            if (dotsContainer && cards.length > 0) {
                const dots = dotsContainer.children;
                const firstCard = cards[0];
                const cardWidth = firstCard ? firstCard.offsetWidth + 24 : 360;
                const activeIndex = Math.min(Math.round(track.scrollLeft / cardWidth), cards.length - 1);
                
                Array.from(dots).forEach((dot, idx) => {
                    if (idx === activeIndex) {
                        dot.className = 'w-7 h-2.5 rounded-full bg-agro-700 transition-all duration-300';
                    } else {
                        dot.className = 'w-2.5 h-2.5 rounded-full bg-slate-300 hover:bg-slate-400 transition-all duration-300';
                    }
                });

                if (infoText) {
                    infoText.innerHTML = `<i class="fa-solid fa-box-archive text-agro-600"></i> Mostrando <strong>${activeIndex + 1}</strong> de <strong>${cards.length}</strong> combos activos`;
                }
            }
        }

        track.addEventListener('scroll', updateCarouselState, { passive: true });
        window.addEventListener('resize', updateCarouselState);
        updateCarouselState();
    }

    function openPubModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    function closePubModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }

    // Cerrar modal de noticias al presionar Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.pub-modal:not(.hidden)').forEach(modal => {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            });
        }
    });
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
