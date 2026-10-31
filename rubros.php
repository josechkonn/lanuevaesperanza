<?php
// rubros.php - Catálogo Agronómico de los 39 Rubros Cosechados
require __DIR__ . '/includes/app.php';

$pageTitle = 'Catálogo de los 39 Rubros Cosechados • Soberanía Andina';
require __DIR__ . '/includes/header.php';
?>

<!-- BANNER HERO DE PÁGINA: 39 RUBROS (Diseño Editorial y Montaña Andina) -->
<div class="bg-[#0a1c14] text-white py-14 border-b border-emerald-900/60 relative overflow-hidden">
    <!-- Curvas topográficas andinas -->
    <div class="absolute inset-0 opacity-[0.06] pointer-events-none overflow-hidden flex items-center justify-center">
        <svg class="w-[140%] h-[140%]" viewBox="0 0 1200 800" fill="none" stroke="currentColor">
            <path d="M-100,200 C200,100 400,350 700,200 C1000,50 1100,280 1300,180" stroke-width="1.5" />
            <path d="M-100,400 C180,260 480,500 800,360 C1100,220 1200,450 1300,360" stroke-width="1.5" />
        </svg>
    </div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="max-w-3xl">
                <div class="inline-flex items-center gap-2 bg-emerald-950/80 border border-emerald-500/30 px-3.5 py-1.5 rounded-full text-emerald-300 text-xs font-semibold uppercase tracking-wider mb-3">
                    <i class="fa-solid fa-seedling text-amber-400"></i> Biodiversidad Agroalimentaria • Táchira, Venezuela
                </div>
                <h1 class="text-3xl sm:text-5xl font-serif font-extrabold text-white tracking-tight">
                    Catálogo de los 39 Rubros Cosechados
                </h1>
                <p class="text-emerald-100/80 text-xs sm:text-sm md:text-base mt-3 leading-relaxed font-normal">
                    Caracterización agronómica integral: <strong>qué rubros producimos</strong>, <strong>en qué forma los cultivamos</strong> (terrazas agroecológicas y riego por gravedad), <strong>ciclos biológicos</strong> y <strong>cosecha continua los 365 días del año</strong> gracias a los manantiales de la cordillera andina.
                </p>
            </div>
            <div class="flex flex-col sm:flex-row md:flex-col gap-2 shrink-0">
                <a href="<?= e(url('index.php#combos')) ?>" class="bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold px-5 py-3 rounded-xl text-xs uppercase tracking-wider transition text-center shadow-xs active:scale-95">
                    <i class="fa-solid fa-bag-shopping mr-1.5"></i> Ver Combos Disponibles
                </a>
                <a href="<?= e(url('index.php#productores')) ?>" class="bg-white/5 hover:bg-white/10 text-white font-medium px-5 py-3 rounded-xl text-xs uppercase tracking-wider transition text-center border border-white/15 active:scale-95">
                    <i class="fa-solid fa-handshake-angle mr-1.5"></i> Registrar Cosecha / UPF
                </a>
            </div>
        </div>
    </div>
</div>

<!-- SECCIÓN PRINCIPAL: FILTROS, BUSCADOR Y CATÁLOGO PAGINADO -->
<section id="rubros-catalogo" class="py-16 bg-[#faf8f5] border-t border-stone-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Barra Superior: Filtros de Categoría -->
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8 gap-4">
            <div class="flex flex-wrap items-center gap-2">
                <button onclick="filterRubros('todos')" class="rubro-btn active bg-[#133527] text-white px-4 py-2 rounded-xl text-xs font-bold shadow-xs transition">Todos los Rubros (42)</button>
                <button onclick="filterRubros('emblema')" class="rubro-btn bg-amber-100/80 border border-amber-300 text-amber-950 hover:bg-amber-200/80 px-4 py-2 rounded-xl text-xs font-black transition">🌟 Rubros Emblema (Truchas, Café, Panela)</button>
                <button onclick="filterRubros('hortalizas')" class="rubro-btn bg-white border border-stone-300 text-slate-700 hover:bg-stone-50 px-4 py-2 rounded-xl text-xs font-bold transition">Hortalizas</button>
                <button onclick="filterRubros('tuberculos')" class="rubro-btn bg-white border border-stone-300 text-slate-700 hover:bg-stone-50 px-4 py-2 rounded-xl text-xs font-bold transition">Tubérculos y Raíces</button>
                <button onclick="filterRubros('frutas')" class="rubro-btn bg-white border border-stone-300 text-slate-700 hover:bg-stone-50 px-4 py-2 rounded-xl text-xs font-bold transition">Frutas</button>
                <button onclick="filterRubros('alinos')" class="rubro-btn bg-white border border-stone-300 text-slate-700 hover:bg-stone-50 px-4 py-2 rounded-xl text-xs font-bold transition">Aliños y Hierbas</button>
            </div>

            <!-- Badges de Garantía Agroecológica -->
            <div class="flex flex-wrap items-center gap-2 text-[11px] font-bold text-slate-600">
                <span class="bg-sky-50 text-sky-900 border border-sky-200 px-3 py-1.5 rounded-xl flex items-center gap-1.5 shadow-2xs">
                    <i class="fa-solid fa-droplet text-sky-600"></i> Agua Continua 365 Días
                </span>
                <span class="bg-emerald-50 text-emerald-900 border border-emerald-200 px-3 py-1.5 rounded-xl flex items-center gap-1.5 shadow-2xs">
                    <i class="fa-solid fa-mountain text-emerald-700"></i> Alta Montaña Andina
                </span>
            </div>
        </div>

        <!-- Buscador en Vivo -->
        <div class="mb-8">
            <div class="relative max-w-2xl">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-3.5 text-slate-400 text-sm"></i>
                <input type="text" id="searchRubroInput" onkeyup="searchRubros()" placeholder="Buscar rubro por nombre, aldea (Pattueca, Zumbador, etc.), forma de siembra o tiempo..." class="w-full pl-11 pr-4 py-3 bg-white rounded-2xl border border-stone-300 focus:outline-none focus:ring-2 focus:ring-emerald-700 text-xs font-medium shadow-xs">
            </div>
        </div>

        <!-- Cuadrícula Dinámica de Tarjetas (Grid) -->
        <div id="rubrosGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <!-- Renderizado dinámicamente con JS -->
        </div>

        <!-- Barra de Paginación y Densidad -->
        <div id="rubrosPaginationContainer" class="mt-10 pt-6 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div id="rubrosPaginationInfo" class="text-xs text-slate-500 font-bold order-2 sm:order-1">
                Cargando rubros...
            </div>
            
            <div id="rubrosPaginationNav" class="flex flex-wrap items-center justify-center gap-1.5 order-1 sm:order-2">
                <!-- Botones de página generados por JS -->
            </div>

            <div class="flex items-center gap-2 text-xs font-bold text-slate-500 order-3">
                <span class="text-[11px] uppercase tracking-wider text-slate-400">Por página:</span>
                <button onclick="setRubrosPerPage(8)" id="btnPageSize8" class="px-2.5 py-1 rounded-lg bg-agro-700 text-white text-[11px] font-bold transition shadow-2xs">8</button>
                <button onclick="setRubrosPerPage(12)" id="btnPageSize12" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 text-[11px] font-bold transition shadow-2xs">12</button>
                <button onclick="setRubrosPerPage('all')" id="btnPageSizeAll" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 text-[11px] font-bold transition shadow-2xs">Todos</button>
            </div>
        </div>

    </div>
</section>

<!-- MODAL FICHA AGRONÓMICA DETALLADA -->
<div id="rubroModal" class="fixed inset-0 z-50 hidden bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl relative max-h-[90vh] overflow-y-auto">
        <button onclick="closeRubroModal()" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 transition text-2xl font-bold" aria-label="Cerrar modal">&times;</button>
        <div id="rubroModalBody">
            <!-- Inyectado dinámicamente con JS -->
        </div>
    </div>
</div>

<!-- SCRIPTS DE DATOS Y PAGINACIÓN DE LOS 39 RUBROS -->
<script>
    const RUBROS_LISTA = [
        {
            id: 1,
            name: 'PAPA',
            category: 'tuberculos',
            icon: 'fa-box',
            forma: 'Terrazas andinas de alta montaña, aporque manual, rotación de suelos con abono orgánico fermentado.',
            tiempo: '90 - 120 días (ciclo corto a medio)',
            meses: 'Enero a Diciembre (Cosecha activa escalonada continua los 365 días del año)',
            aldeas: ['Pattueca', 'Llano Grande', 'El Zumbador', 'Mesa de Vargas', 'La Pradera'],
            altitud: '2.000 - 3.100 msnm',
            agua_riego: 'Gravedad por acequias tradicionales alimentadas de deshielos y nacientes andinas.',
            desc: 'Papa única de páramo (variedades Granola, Única y Amarilla). Textura firme, alto almidón y resistencia superior para transporte nacional.'
        },
        {
            id: 2,
            name: 'CEBOLLA',
            category: 'hortalizas',
            icon: 'fa-seedling',
            forma: 'Camellones tecnificados con riego por aspersión y gravedad desde quebradas cristalinas, canteros aireados y deshierbe manual.',
            tiempo: '90 - 110 días',
            meses: 'Enero a Diciembre (Siembras escalonadas quincenales todo el año)',
            aldeas: ['Sabana Grande', 'El Zumbador', 'La Pradera', 'Caricuena'],
            altitud: '1.600 - 2.400 msnm',
            agua_riego: 'Agua pura de microcuencas de cabecera con flujo constante todo el año.',
            desc: 'Cebolla blanca y roja de cabeza compacta, excelente picor, pungencia y durabilidad en anaquel sin conservantes químicos.'
        },
        {
            id: 3,
            name: 'ZANAHORIA',
            category: 'hortalizas',
            icon: 'fa-carrot',
            forma: 'Canteros profundos en suelos arenoso-limosos, aclareo manual riguroso y protección con mulch vegetal.',
            tiempo: '85 - 100 días',
            meses: 'Enero a Diciembre (Cosecha semanal permanente 365 días)',
            aldeas: ['Pattueca', 'Caricuena', 'Mesa de Vargas', 'Sabana Grande'],
            altitud: '1.800 - 2.700 msnm',
            agua_riego: 'Sistemas presurizados por gravedad natural desde reservorios de manantial.',
            desc: 'Zanahorias dulces, crujientes, de intenso color anaranjado y ricas en carotenos, cosechadas al punto óptimo de terneza.'
        },
        {
            id: 4,
            name: 'REPOLLO MORADO',
            category: 'hortalizas',
            icon: 'fa-spa',
            forma: 'Siembra en curvas a nivel con cobertura vegetal viva, microaspersión y control biológico de plagas con bioinsumos comunales.',
            tiempo: '75 - 90 días',
            meses: 'Enero a Diciembre (Cosecha ininterrumpida los 365 días)',
            aldeas: ['El Batallón', 'Guanare', 'El Pajarito', 'Seboruco'],
            altitud: '1.700 - 2.500 msnm',
            agua_riego: 'Tomas directas de torrenteras limpias de ladera con agua fría de montaña.',
            desc: 'Cabezas esféricas densas y compactas, tonalidad morada vibrante, abundante en antocianinas y antioxidantes naturales.'
        },
        {
            id: 5,
            name: 'TOMATE',
            category: 'hortalizas',
            icon: 'fa-apple-whole',
            forma: 'Tutorado en espaldera bajo condiciones protegidas e invernaderos rústicos andinos con biofertilización líquida foliar.',
            tiempo: '70 - 85 días al primer corte (producción continua 90 días)',
            meses: 'Enero a Diciembre (Ciclos continuos solapados los 365 días del año)',
            aldeas: ['Santa Rosa', 'Santa Filomena', 'El Boquerón', 'Seboruco', 'Calichal'],
            altitud: '900 - 1.800 msnm',
            agua_riego: 'Riego por goteo con filtrado biológico desde fuentes fluviales de Jáuregui y Seboruco.',
            desc: 'Tomate perita y manzana de pulpa carnosa, óptimo balance acidez-dulzor y firmeza ideal para distribución a granel.'
        },
        {
            id: 6,
            name: 'PIMENTÓN',
            category: 'hortalizas',
            icon: 'fa-pepper-hot',
            forma: 'Camellones altos con enmiendas de estiércol compostado, tutorado de caña brava y micorrizas andinas en raíz.',
            tiempo: '75 - 90 días',
            meses: 'Enero a Diciembre (Cosecha activa regular los 365 días)',
            aldeas: ['Santa Rosa', 'Las Copas', 'Vegones', 'Seboruco'],
            altitud: '900 - 1.700 msnm',
            agua_riego: 'Agua de quebradas de alta pendiente con excelente oxigenación.',
            desc: 'Pimentón rojo y verde cuatro cascos, paredes gruesas, excelente brillo y gran contenido de vitamina C.'
        },
        {
            id: 7,
            name: 'APIO VERDURA',
            category: 'tuberculos',
            icon: 'fa-circle-nodes',
            forma: 'Cultivo ancestral andino en pendientes pronunciadas húmedas, abundante materia orgánica y labranza mínima conservacionista.',
            tiempo: '180 - 240 días',
            meses: 'Enero a Diciembre (Producción continua gracias a la humedad perenne)',
            aldeas: ['Guanare', 'Caricuena', 'Pattueca', 'El Batallón'],
            altitud: '1.600 - 2.300 msnm',
            agua_riego: 'Humedad ambiental de neblina andina combinada con agua de manantial.',
            desc: 'Apio amarillo criollo de exquisito aroma y textura aterciopelada al cocinarse, patrimonio gastronómico del páramo tachirense.'
        },
        {
            id: 8,
            name: 'REMOLACHA',
            category: 'hortalizas',
            icon: 'fa-circle-dot',
            forma: 'Canteros andinos con compost de lombriz, riego por goteo y mulch orgánico para mantener la frescura de la raíz.',
            tiempo: '60 - 75 días',
            meses: 'Enero a Diciembre (Cosecha continua todo el año)',
            aldeas: ['Sabana Grande', 'Llano Grande', 'El Zumbador', 'Pattueca'],
            altitud: '1.800 - 2.600 msnm',
            agua_riego: 'Tomas hídricas de vertientes montañosas protegidas.',
            desc: 'Tubérculo globular de pulpa bermellón intensa, dulce, rico en betanina, hierro y minerales revitalizantes.'
        },
        {
            id: 9,
            name: 'AJO CRIOLLO',
            category: 'alinos',
            icon: 'fa-clover',
            forma: 'Bancales sobreelevados con ceniza volcánica y compost maduro, desinfección térmica de dientes antes de siembra.',
            tiempo: '120 - 150 días',
            meses: 'Enero a Diciembre (Secado tradicional andino y abastecimiento perenne)',
            aldeas: ['El Zumbador', 'Mesa de Vargas', 'Pattueca', 'El Batallón'],
            altitud: '2.100 - 2.900 msnm',
            agua_riego: 'Gravedad de cabecera con bajo requerimiento en etapa de llenado de bulbo.',
            desc: 'Ajo morado y blanco andino de diente firme, aroma potente e inigualable sabor concentrado.'
        },
        {
            id: 10,
            name: 'CEBOLLÍN',
            category: 'alinos',
            icon: 'fa-leaf',
            forma: 'Siembra en surcos tupidos con aporque frecuente y enriquecimiento continuo con té de compost.',
            tiempo: '45 - 60 días (cortes continuos)',
            meses: 'Enero a Diciembre (Cortes semanales los 365 días del año)',
            aldeas: ['La Pradera', 'Sabana Grande', 'Quebrada de San José', 'Caricuena'],
            altitud: '1.400 - 2.200 msnm',
            agua_riego: 'Riego diario por aspersión con agua de manantial.',
            desc: 'Tallos tiernos, crujientes, de base blanca pura y follaje verde intenso, esencial para la cocina venezolana.'
        },
        {
            id: 11,
            name: 'CILANTRO',
            category: 'alinos',
            icon: 'fa-cannabis',
            forma: 'Canteros sombreados por árboles andinos, siembra al voleo densa y cosecha manual de raíz entera.',
            tiempo: '35 - 45 días (ciclo superrápido)',
            meses: 'Enero a Diciembre (Siembras escalonadas cada 10 días los 365 días)',
            aldeas: ['Quebrada de San José', 'La Pradera', 'Seboruco', 'Las Copas'],
            altitud: '1.200 - 2.000 msnm',
            agua_riego: 'Microaspersión suave con agua limpia no clorada.',
            desc: 'Cilantro criollo de hojas finas, aroma penetrante, frescura inconfundible y raíz limpia lista para consumo.'
        },
        {
            id: 12,
            name: 'PEREJIL',
            category: 'alinos',
            icon: 'fa-spa',
            forma: 'Linderos y canteros biológicos con compost andino y protección contra viento con setos vivos.',
            tiempo: '60 - 75 días (múltiples cortes)',
            meses: 'Enero a Diciembre (Cortes permanentes todo el año)',
            aldeas: ['Quebrada de San José', 'Llano Grande', 'Pattueca'],
            altitud: '1.400 - 2.400 msnm',
            agua_riego: 'Aspersión regular matutina con agua de naciente.',
            desc: 'Perejil liso y crespo, textura crujiente, verde esmeralda y rico en clorofila y minerales.'
        },
        {
            id: 13,
            name: 'LECHUGA CRIOLLA',
            category: 'hortalizas',
            icon: 'fa-feather-pointed',
            forma: 'Camellones acolchados con paja de páramo, trasplante en horas frescas y sombra parcial de neblina.',
            tiempo: '45 - 55 días',
            meses: 'Enero a Diciembre (Producción continua ininterrumpida)',
            aldeas: ['Sabana Grande', 'Caricuena', 'La Pradera', 'El Batallón'],
            altitud: '1.500 - 2.300 msnm',
            agua_riego: 'Riego por aspersión fina con agua fresca de montaña.',
            desc: 'Hojas tiernas y rizadas de consistencia suave y refrescante, libre de pesticidas sintéticos.'
        },
        {
            id: 14,
            name: 'LECHUGA AMERICANA',
            category: 'hortalizas',
            icon: 'fa-shield-halved',
            forma: 'Cultivo sobre camellones elevados con enmiendas orgánicas y protección contra impacto de lluvia fuerte.',
            tiempo: '60 - 70 días',
            meses: 'Enero a Diciembre (Cosecha programada semanal)',
            aldeas: ['El Zumbador', 'Pattueca', 'Mesa de Vargas'],
            altitud: '1.900 - 2.600 msnm',
            agua_riego: 'Goteo y aspersión controlada desde reservorios comunales.',
            desc: 'Cabezas compactas, firmes, crocantes y con excelente vida útil postcosecha para transporte a Caracas y el centro del país.'
        },
        {
            id: 15,
            name: 'BRÓCOLI',
            category: 'hortalizas',
            icon: 'fa-tree',
            forma: 'Terrazas de alta cota con alta humedad ambiental, abonos verdes de leguminosas y deshierbe ecológico.',
            tiempo: '70 - 85 días',
            meses: 'Enero a Diciembre (365 días favorecido por el frío montañoso)',
            aldeas: ['El Batallón', 'Guanare', 'El Pajarito', 'Pattueca'],
            altitud: '2.000 - 2.800 msnm',
            agua_riego: 'Riego por goteo con aguas vírgenes de páramo.',
            desc: 'Inflorescencias compactas de color verde azulado oscuro, pellas pesadas y ricas en sulforafano y antioxidantes.'
        },
        {
            id: 16,
            name: 'COLIFLOR',
            category: 'hortalizas',
            icon: 'fa-certificate',
            forma: 'Canteros profundos con protección de la pella mediante cobertura de sus propias hojas para mantener su blancura.',
            tiempo: '75 - 90 días',
            meses: 'Enero a Diciembre (Cosechas continuas todo el año)',
            aldeas: ['El Batallón', 'Pattueca', 'Llano Grande', 'El Zumbador'],
            altitud: '1.900 - 2.700 msnm',
            agua_riego: 'Agua pura de cabecera con temperatura fría ideal para la crucífera.',
            desc: 'Pella marfil inmaculada, densa, de grano fino y sabor delicado, sin amargor.'
        },
        {
            id: 17,
            name: 'CALABACÍN',
            category: 'hortalizas',
            icon: 'fa-cubes-stacked',
            forma: 'Cultivo en espaldera baja o suelo con acolchado vegetal, polinización natural favorecida por abejas de montaña.',
            tiempo: '45 - 55 días (recolección continua cada 3 días)',
            meses: 'Enero a Diciembre (Cosecha semanal permanente 365 días)',
            aldeas: ['Santa Rosa', 'Las Copas', 'Quebrada de San José', 'Seboruco'],
            altitud: '1.100 - 1.800 msnm',
            agua_riego: 'Riego regular por surco y goteo desde quebradas caudalosas.',
            desc: 'Frutos cilíndricos rectos, piel verde brillante con moteado claro, pulpa tierna y semillas diminutas.'
        },
        {
            id: 18,
            name: 'PEPINO',
            category: 'hortalizas',
            icon: 'fa-ellipsis',
            forma: 'Tutorado vertical en caña brava, podas de brotes laterales y aplicaciones de bioestimulantes de algas.',
            tiempo: '50 - 60 días',
            meses: 'Enero a Diciembre (Producción continua escalonada)',
            aldeas: ['Santa Rosa', 'El Boquerón', 'Seboruco', 'Calichal'],
            altitud: '900 - 1.700 msnm',
            agua_riego: 'Suministro constante por gravedad de agua limpia no estancada.',
            desc: 'Pepino crujiente, piel delgada, fresco y con alto contenido de agua purificada por la naturaleza andina.'
        },
        {
            id: 19,
            name: 'ACELGA',
            category: 'hortalizas',
            icon: 'fa-shield-heart',
            forma: 'Surcos perennes abonados con humus de lombriz, cortes periódicos de pencas exteriores respetando el corazón.',
            tiempo: '50 - 65 días (cortes periódicos durante meses)',
            meses: 'Enero a Diciembre (Cortes semanales los 365 días del año)',
            aldeas: ['Quebrada de San José', 'Caricuena', 'Sabana Grande'],
            altitud: '1.400 - 2.200 msnm',
            agua_riego: 'Riego por aspersión y surco con agua de manantial.',
            desc: 'Pencas blancas anchas y carnosas con hojas verde lustroso, ricas en hierro, magnesio y ácido fólico.'
        },
        {
            id: 20,
            name: 'ESPINACA',
            category: 'hortalizas',
            icon: 'fa-fan',
            forma: 'Bancales en media sombra andina, alta densidad de siembra y recolección antes de las horas de sol fuerte.',
            tiempo: '40 - 50 días',
            meses: 'Enero a Diciembre (Producción constante en microclimas fríos)',
            aldeas: ['El Zumbador', 'Pattueca', 'Guanare', 'El Batallón'],
            altitud: '1.800 - 2.600 msnm',
            agua_riego: 'Microaspersión fina con agua cristalina de páramo.',
            desc: 'Hojas carnosas de color verde oscuro brillante, textura suave y alto contenido de hierro asimilable.'
        },
        {
            id: 21,
            name: 'HIERBABUENA',
            category: 'alinos',
            icon: 'fa-heart',
            forma: 'Canteros húmedos bordeando acequias de ladera, multiplicación por estolones y podas de estimulación.',
            tiempo: 'Perenne con cortes mensuales',
            meses: 'Enero a Diciembre (Cortes quincenales todo el año)',
            aldeas: ['Quebrada de San José', 'La Pradera', 'Vegones'],
            altitud: '1.200 - 2.000 msnm',
            agua_riego: 'Flujo constante de acequias comunales.',
            desc: 'Aroma fresco balsámico inconfundible, rica en aceites esenciales digestivos y medicinales.'
        },
        {
            id: 22,
            name: 'MENTA',
            category: 'alinos',
            icon: 'fa-gem',
            forma: 'Cultivo en suelos arcillo-limosos muy húmedos, linderos de manantiales y control de malezas manual.',
            tiempo: 'Perenne con cosechas periódicas',
            meses: 'Enero a Diciembre (Abastecimiento perenne 365 días)',
            aldeas: ['Quebrada de San José', 'Pattueca', 'Caricuena'],
            altitud: '1.300 - 2.100 msnm',
            agua_riego: 'Humedad perenne de bordes de quebrada.',
            desc: 'Concentración extraordinaria de mentol natural, ideal para infusiones, gastronomía y fitoterapia.'
        },
        {
            id: 23,
            name: 'ORÉGANO ANDINO',
            category: 'alinos',
            icon: 'fa-sun',
            forma: 'Terrazas bien drenadas en vertientes con buena insolación matinal, podas de floración para vigorizar hojas.',
            tiempo: '60 - 75 días al corte',
            meses: 'Enero a Diciembre (Secado tradicional y fresco todo el año)',
            aldeas: ['La Pradera', 'Seboruco', 'Calichal', 'Santa Filomena'],
            altitud: '1.000 - 1.800 msnm',
            agua_riego: 'Riego moderado por goteo con agua limpia de río.',
            desc: 'Orégano orejón y criollo de hoja gruesa, aroma penetrante característico que realza cualquier preparación.'
        },
        {
            id: 24,
            name: 'ROMERO',
            category: 'alinos',
            icon: 'fa-water',
            forma: 'Setos rústicos en laderas pedregosas, drenaje perfecto y cero aplicación de químicos sintéticos.',
            tiempo: 'Perenne leñoso',
            meses: 'Enero a Diciembre (Cosecha continua los 365 días del año)',
            aldeas: ['El Zumbador', 'Quebrada de San José', 'Sabana Grande'],
            altitud: '1.500 - 2.400 msnm',
            agua_riego: 'Riego esporádico por gravedad; cultivo rústico altamente resistente.',
            desc: 'Follaje acicular muy aromático, rico en ácido rosmarínico y antioxidantes de larga duración.'
        },
        {
            id: 25,
            name: 'TOMILLO',
            category: 'alinos',
            icon: 'fa-snowflake',
            forma: 'Pequeñas terrazas protegidas con piedras de río que acumulan calor diurno, poda de despunte.',
            tiempo: 'Perenne aromático',
            meses: 'Enero a Diciembre (Disponibilidad los 365 días del año)',
            aldeas: ['Quebrada de San José', 'Pattueca', 'Mesa de Vargas'],
            altitud: '1.600 - 2.500 msnm',
            agua_riego: 'Microgoteo con agua de manantial.',
            desc: 'Hojas diminutas de increíble concentración aromática y propiedades antisépticas comprobadas.'
        },
        {
            id: 26,
            name: 'ALBAHACA',
            category: 'alinos',
            icon: 'fa-fire-flame-curved',
            forma: 'Valles medios abrigados de Jáuregui, canteros protegidos del frío nocturno y despunte de espigas florales.',
            tiempo: '40 - 55 días',
            meses: 'Enero a Diciembre (Cosechas continuas todo el año)',
            aldeas: ['Santa Rosa', 'Las Copas', 'Seboruco'],
            altitud: '900 - 1.500 msnm',
            agua_riego: 'Riego regular con agua temperada de manantial.',
            desc: 'Albahaca genovesa y morada, hojas anchas tiernas, aroma anisado dulce incomparable.'
        },
        {
            id: 27,
            name: 'AJÍ DULCE',
            category: 'alinos',
            icon: 'fa-bell',
            forma: 'Cultivo tutorado en camellones altos con abundante compost maduro, cosecha selectiva por coloración.',
            tiempo: '80 - 100 días',
            meses: 'Enero a Diciembre (Cosecha semanal constante los 365 días)',
            aldeas: ['Santa Rosa', 'Vegones', 'Seboruco', 'Calichal'],
            altitud: '800 - 1.500 msnm',
            agua_riego: 'Riego por surco con agua limpia de montaña.',
            desc: 'Ají dulce criollo oriental y tachirense, campana carnosa, aroma perfumado sin picor, base del sofrito nacional.'
        },
        {
            id: 28,
            name: 'YUCA DULCE',
            category: 'tuberculos',
            icon: 'fa-cubes',
            forma: 'Terrenos sueltos franco-arenosos de piedemonte y valles templados, siembra en estacas seleccionadas.',
            tiempo: '8 - 10 meses',
            meses: 'Enero a Diciembre (Cosecha escalonada permanente todo el año)',
            aldeas: ['Seboruco', 'Santa Filomena', 'Calichal', 'Vegones'],
            altitud: '700 - 1.300 msnm',
            agua_riego: 'Riego por gravedad en época seca; aprovecha la alta precipitación andina.',
            desc: 'Raíces gruesas de corteza marrón clara, pulpa blanca sin hebras, cocción rápida mantequillosa y suave.'
        },
        {
            id: 29,
            name: 'OCUMO',
            category: 'tuberculos',
            icon: 'fa-shapes',
            forma: 'Quebradas y cañadas húmedas con alta fertilidad natural, sombra de platanales y abono orgánico.',
            tiempo: '9 - 11 meses',
            meses: 'Enero a Diciembre (Arrimado constante los 365 días)',
            aldeas: ['Seboruco', 'Santa Rosa', 'El Boquerón', 'Calichal'],
            altitud: '700 - 1.400 msnm',
            agua_riego: 'Humedad perenne de orillas de cursos de agua andinos.',
            desc: 'Ocumo blanco y morado de masa densa y suave, altamente digerible y energético.'
        },
        {
            id: 30,
            name: 'ÑAME',
            category: 'tuberculos',
            icon: 'fa-hockey-puck',
            forma: 'Camellones altos con tutores de bambú, suelo profundo descompactado y cobertura vegetal espesa.',
            tiempo: '8 - 10 meses',
            meses: 'Enero a Diciembre (Cosechas continuas de valles medios)',
            aldeas: ['Seboruco', 'Calichal', 'Vegones'],
            altitud: '700 - 1.300 msnm',
            agua_riego: 'Riego por gravedad con agua de vertiente.',
            desc: 'Ñame criollo de pulpa blanca arenosa, excelente consistencia para sopas y hervidos tradicionales.'
        },
        {
            id: 31,
            name: 'BATATA DULCE',
            category: 'tuberculos',
            icon: 'fa-egg',
            forma: 'Lomos de tierra suelta con rotación de cultivos, siembra por guías vegetativas vigorosas.',
            tiempo: '100 - 120 días',
            meses: 'Enero a Diciembre (Producción continua todo el año)',
            aldeas: ['Santa Rosa', 'Seboruco', 'Calichal', 'Las Copas'],
            altitud: '800 - 1.500 msnm',
            agua_riego: 'Riego por surco con agua de deshielo y lluvias andinas.',
            desc: 'Batata morada y anaranjada, cremosa, rica en provitamina A y azúcares naturales saludables.'
        },
        {
            id: 32,
            name: 'PLÁTANO MACHO',
            category: 'frutas',
            icon: 'fa-lemon',
            forma: 'Plantaciones asociadas a laderas de media montaña, deshoje fitosanitario semanal y deshije selectivo.',
            tiempo: '10 - 12 meses (producción continua por hijos)',
            meses: 'Enero a Diciembre (Corte ininterrumpido semanal los 365 días del año)',
            aldeas: ['Seboruco', 'Calichal', 'Santa Rosa', 'El Boquerón', 'Vegones'],
            altitud: '700 - 1.300 msnm',
            agua_riego: 'Aguas de escorrentía montañosa y quebradas de cuenca baja.',
            desc: 'Plátano verde y maduro de racimos pesados, dedos largos y pulpa firme rica en potasio y almidones.'
        },
        {
            id: 33,
            name: 'CAMBUR GUINEO Y MANZANO',
            category: 'frutas',
            icon: 'fa-bowl-food',
            forma: 'Policultivos agroforestales en pendientes con cobertura viva y biofertilización con lixiviado de compost.',
            tiempo: '8 - 10 meses (cosecha continua)',
            meses: 'Enero a Diciembre (Corte semanal los 365 días)',
            aldeas: ['Seboruco', 'Calichal', 'Vegones', 'Santa Filomena'],
            altitud: '700 - 1.400 msnm',
            agua_riego: 'Fuentes naturales de gravedad directa.',
            desc: 'Fruta de mesa dulce, cremosa y aromática, maduración natural sin carburo ni aceleradores artificiales.'
        },
        {
            id: 34,
            name: 'GUAYABA',
            category: 'frutas',
            icon: 'fa-apple-whole',
            forma: 'Huertos andinos tradicionales en valles medios, podas de aclareo y control manual de frutos.',
            tiempo: 'Cosecha continua por temporadas escalonadas',
            meses: 'Enero a Diciembre (Producción regular continua todo el año)',
            aldeas: ['Seboruco', 'Santa Rosa', 'Calichal', 'Michelena'],
            altitud: '800 - 1.500 msnm',
            agua_riego: 'Riego de ladera con agua limpia de río.',
            desc: 'Guayaba criolla rosada y blanca, piel perfumada, pulpa jugosa con alto contenido de vitamina C.'
        },
        {
            id: 35,
            name: 'LECHOSA / PAPAYA',
            category: 'frutas',
            icon: 'fa-droplet',
            forma: 'Cultivo en terrazas con buen drenaje, aporque continuo y aplicaciones foliares de bioinsumos.',
            tiempo: '7 - 9 meses al inicio de cosecha (continua durante 18 meses)',
            meses: 'Enero a Diciembre (Recolección quincenal continua 365 días)',
            aldeas: ['Santa Rosa', 'Seboruco', 'Calichal', 'El Boquerón'],
            altitud: '700 - 1.300 msnm',
            agua_riego: 'Riego controlado por goteo con agua de manantial.',
            desc: 'Frutos grandes de pulpa color salmón intenso, dulce, carminativa y rica en papaína digestiva.'
        },
        {
            id: 36,
            name: 'PARCHITA / MARACUYÁ',
            category: 'frutas',
            icon: 'fa-circle-half-stroke',
            forma: 'Espalderas en emparrillado de alambre galvanizado, podas de producción y polinización manual y por abejorros xilocopa.',
            tiempo: '7 - 9 meses (producción regular 2 años)',
            meses: 'Enero a Diciembre (Cosecha semanal los 365 días del año)',
            aldeas: ['Seboruco', 'Calichal', 'Vegones', 'Santa Rosa'],
            altitud: '800 - 1.500 msnm',
            agua_riego: 'Riego por microgoteo con agua de vertiente andina.',
            desc: 'Fruto globular amarillo y morado, pulpa dorada muy aromática con alto rendimiento en jugo natural.'
        },
        {
            id: 37,
            name: 'CHAYOTA / GUATILA',
            category: 'hortalizas',
            icon: 'fa-cloud',
            forma: 'Emparrados tradicionales sobre acequias y taludes andinos, recolección manual cuidadosa.',
            tiempo: '80 - 100 días al inicio (cosecha constante durante meses)',
            meses: 'Enero a Diciembre (Corte ininterrumpido semanal los 365 días)',
            aldeas: ['El Boquerón', 'Vegones', 'Santa Filomena', 'Michelena'],
            altitud: '1.100 - 1.800 msnm',
            agua_riego: 'Alimentada por el flujo constante de quebradas y acequias de ladera.',
            desc: 'Chayota verde tierna, piel lisa o con espinas suaves, consistencia acuosa crujiente y liviana para dietas saludables.'
        },
        {
            id: 38,
            name: 'MANDARINA',
            category: 'frutas',
            icon: 'fa-circle',
            forma: 'Parcelas en pendiente de valles medios, cobertura de leguminosas fijadoras de nitrógeno y podas de formación sanitaria.',
            tiempo: 'Perenne con ciclos escalonados',
            meses: 'Enero a Diciembre (Cosecha sostenida por variedades todo el año)',
            aldeas: ['Los Naranjos', 'La San Juana', 'Seboruco', 'Calichal'],
            altitud: '700 - 1.400 msnm',
            agua_riego: 'Riego por gravedad en terrazas de ladera con agua de manantial.',
            desc: 'Mandarina Dancy y Clementina de fácil pelado, gajos jugosos, aroma exquisito y dulzura natural.'
        },
        {
            id: 39,
            name: 'BERENJENA',
            category: 'hortalizas',
            icon: 'fa-egg',
            forma: 'Cultivo tutorado en camellones altos con enmiendas de compost, deshoje basal y trampas agroecológicas.',
            tiempo: '65 - 80 días',
            meses: 'Enero a Diciembre (Cosecha semanal continua los 365 días del año)',
            aldeas: ['Santa Rosa', 'Vegones', 'Calichal', 'Seboruco'],
            altitud: '800 - 1.600 msnm',
            agua_riego: 'Riego por goteo y surco con agua limpia de cabecera.',
            desc: 'Berenjena negra y jaspeada, piel brillante y firme, pulpa blanca carnosa sin amargor, ideal para asados y guisos.'
        },
        {
            id: 40,
            name: 'TRUCHA ARCOÍRIS DE LAS PORQUERAS',
            category: 'emblema',
            icon: 'fa-fish',
            forma: 'Truchicultura andina en estanques artesanales con agua de deshielo y manantiales directos de cabecera.',
            tiempo: '8 - 12 meses (crecimiento óptimo)',
            meses: 'Enero a Diciembre (Cosecha permanente los 365 días del año)',
            aldeas: ['Las Porqueras (Jáuregui)', 'El Batallón', 'P.N. Juan Pablo Peñaloza'],
            altitud: '2.300 - 2.800 msnm',
            agua_riego: 'Aguas cristalinas gélidas (9°C a 14°C) con oxigenación natural constante por gravedad.',
            desc: 'Trucha arcoíris fresca de alta montaña. Carne firme, rosada o blanca según alimentación natural, alto contenido de Omega-3 y proteína limpia sin antibióticos.'
        },
        {
            id: 41,
            name: 'CAFÉ ESPECIAL DE PREGONERO',
            category: 'emblema',
            icon: 'fa-mug-hot',
            forma: 'Cultivo agroecológico bajo dosel de sombra forestal (guamo y bucare), recolección manual grano a grano en cereza madura.',
            tiempo: 'Cosecha principal Noviembre - Febrero y ciclo de traviesa.',
            meses: 'Disponible todo el año en grano tostado, molido y pergamino.',
            aldeas: ['Pregonero (Uribante)', 'Laguna de García', 'Río Arriba', 'Los Naranjos', 'Potosí'],
            altitud: '1.450 - 1.950 msnm',
            agua_riego: 'Riego natural de neblina andina y quebradas de cuenca alta del río Uribante.',
            desc: 'Café Arábica de estricta altura (variedades Caturra, Borbón y Típica). Taza limpia con fragancia floral, acidez cítrica brillante, cuerpo sedoso y notas a chocolate y panela.'
        },
        {
            id: 42,
            name: 'PANELA ARTESANAL DE HUMUQUENA',
            category: 'emblema',
            icon: 'fa-cubes-stacked',
            forma: 'Molienda en trapiches campesinos tradicionales, clarificación con mucílago vegetal natural de guásimo y cocción en hornos de leña.',
            tiempo: '12 - 14 meses ciclo de caña con moliendas quincenales continuas.',
            meses: 'Enero a Diciembre (Producción continua de trapiche los 365 días)',
            aldeas: ['Humuquena (San Judas Tadeo)', 'Trapiches Paneleros', 'La Vega', 'El Boquerón'],
            altitud: '800 - 1.500 msnm',
            agua_riego: 'Gravedad por tomas de río y humedad de piedemonte andino.',
            desc: 'Panela pura tradicional en bloque y granulada. 100% natural sin blanqueadores químicos ni sulfitos. Fuente directa de energía, hierro, fósforo y calcio para las familias venezolanas.'
        }
    ];

    // Estado de Paginación y Filtrado
    let rubrosCurrentList = [...RUBROS_LISTA];
    let rubrosCurrentPage = 1;
    let rubrosPerPage = 8;

    window.addEventListener('DOMContentLoaded', () => {
        renderRubrosGrid(RUBROS_LISTA);
    });

    function setRubrosPerPage(val) {
        rubrosPerPage = val;
        rubrosCurrentPage = 1;
        ['8', '12', 'All'].forEach(k => {
            const b = document.getElementById('btnPageSize' + k);
            if (b) {
                b.classList.remove('bg-agro-700', 'text-white');
                b.classList.add('bg-white', 'text-slate-600', 'border', 'border-slate-200');
            }
        });
        const activeKey = val === 'all' ? 'All' : val;
        const activeBtn = document.getElementById('btnPageSize' + activeKey);
        if (activeBtn) {
            activeBtn.classList.remove('bg-white', 'text-slate-600', 'border', 'border-slate-200');
            activeBtn.classList.add('bg-agro-700', 'text-white');
        }
        renderRubrosGrid(null, false);
    }

    function changeRubrosPage(page) {
        const total = rubrosCurrentList.length;
        const perPage = rubrosPerPage === 'all' ? total : parseInt(rubrosPerPage);
        const totalPages = Math.ceil(total / perPage);
        if (page < 1 || page > totalPages) return;
        rubrosCurrentPage = page;
        renderRubrosGrid(null, true);
    }

    function renderRubrosGrid(newList = null, scrollUp = false) {
        if (newList !== null) {
            rubrosCurrentList = newList;
            rubrosCurrentPage = 1;
        }

        const container = document.getElementById('rubrosGrid');
        if (!container) return;
        container.innerHTML = '';

        const paginationContainer = document.getElementById('rubrosPaginationContainer');
        const total = rubrosCurrentList.length;

        if (total === 0) {
            container.innerHTML = '<div class="col-span-full text-center py-14 text-slate-400 font-bold bg-white rounded-3xl border border-slate-200 p-8">No se encontraron rubros con los criterios de búsqueda seleccionados.</div>';
            if (paginationContainer) paginationContainer.classList.add('hidden');
            return;
        }

        if (paginationContainer) paginationContainer.classList.remove('hidden');

        const perPage = rubrosPerPage === 'all' ? total : parseInt(rubrosPerPage);
        const totalPages = Math.ceil(total / perPage);
        if (rubrosCurrentPage > totalPages) rubrosCurrentPage = 1;

        const startIdx = (rubrosCurrentPage - 1) * perPage;
        const endIdx = Math.min(startIdx + perPage, total);
        const paginatedItems = rubrosCurrentList.slice(startIdx, endIdx);

        paginatedItems.forEach(rubro => {
            const aldeasBadges = rubro.aldeas.slice(0, 3).map(a => `<span class="bg-amber-50 text-amber-950 border border-amber-300/80 text-[10px] font-bold px-2 py-0.5 rounded-md shadow-2xs">${a}</span>`).join(' ');
            const moreAldeas = rubro.aldeas.length > 3 ? `<span class="bg-stone-100 text-stone-600 text-[9px] font-bold px-1.5 py-0.5 rounded-md">+${rubro.aldeas.length - 3}</span>` : '';

            const card = `
                <div class="bg-white border border-stone-200/90 hover:border-emerald-700/60 rounded-3xl p-6 shadow-xs hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 flex flex-col justify-between group h-full">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3.5">
                            <span class="bg-amber-100 text-amber-950 border border-amber-300/80 text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wider shadow-2xs">
                                #${rubro.id} • ${rubro.category}
                            </span>
                            <span class="bg-sky-50 text-sky-900 border border-sky-200/80 text-[10px] font-bold px-2.5 py-0.5 rounded-full flex items-center gap-1 shadow-2xs">
                                <i class="fa-solid fa-droplet text-sky-600 text-[9px]"></i> 365 Días
                            </span>
                        </div>

                        <div class="flex items-center gap-3.5 mb-3.5">
                            <div class="w-12 h-12 bg-emerald-50 border border-emerald-200/80 text-emerald-800 rounded-2xl flex items-center justify-center text-xl font-bold group-hover:bg-[#133527] group-hover:text-white transition shrink-0 shadow-2xs">
                                <i class="fa-solid ${rubro.icon}"></i>
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-serif font-extrabold text-slate-900 text-base leading-tight group-hover:text-emerald-900 transition truncate">${rubro.name}</h4>
                                <span class="text-[10px] text-slate-400 font-semibold block mt-0.5">${rubro.altitud}</span>
                            </div>
                        </div>

                        <p class="text-xs text-slate-600 font-normal line-clamp-2 mb-4 leading-relaxed">${rubro.desc}</p>

                        <div class="space-y-2.5 pt-3.5 border-t border-stone-100 text-[11px]">
                            <div>
                                <span class="font-bold text-slate-400 uppercase text-[9px] block tracking-wider mb-0.5"><i class="fa-solid fa-seedling text-emerald-700 mr-1"></i>Forma de Cultivo:</span>
                                <p class="text-slate-700 font-semibold line-clamp-2 leading-snug">${rubro.forma}</p>
                            </div>

                            <div class="flex items-center justify-between text-[10px] bg-[#faf8f5] p-2.5 rounded-xl border border-stone-200/80">
                                <div>
                                    <span class="text-slate-400 block font-bold text-[9px]"><i class="fa-solid fa-clock text-amber-600 mr-1"></i>Ciclo / Tiempo:</span>
                                    <span class="font-bold text-slate-800">${rubro.tiempo}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-slate-400 block font-bold text-[9px]"><i class="fa-solid fa-calendar-check text-emerald-700 mr-1"></i>Cosecha:</span>
                                    <span class="font-bold text-emerald-800">365 Días (Ene-Dic)</span>
                                </div>
                            </div>

                            <div>
                                <span class="font-bold text-slate-400 uppercase text-[9px] block tracking-wider mb-1.5"><i class="fa-solid fa-location-dot text-rose-500 mr-1"></i>Aldeas Productoras:</span>
                                <div class="flex flex-wrap items-center gap-1.5">
                                    ${aldeasBadges}
                                    ${moreAldeas}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-stone-100 mt-4">
                        <button onclick="openRubroModal(${rubro.id})" class="w-full bg-[#faf8f5] hover:bg-[#133527] hover:text-white hover:border-[#133527] border border-stone-300 text-slate-700 font-bold py-2.5 rounded-xl transition text-center text-xs flex items-center justify-center gap-1.5 shadow-2xs">
                            <i class="fa-solid fa-circle-info text-xs"></i> Ver Ficha Agronómica
                        </button>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', card);
        });

        renderRubrosPaginationUI(startIdx + 1, endIdx, total, totalPages);

        if (scrollUp) {
            const section = document.getElementById('rubros-catalogo');
            if (section) section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function renderRubrosPaginationUI(start, end, total, totalPages) {
        const infoEl = document.getElementById('rubrosPaginationInfo');
        const navEl = document.getElementById('rubrosPaginationNav');

        if (infoEl) {
            infoEl.innerHTML = `Mostrando <strong class="text-slate-900">${start}-${end}</strong> de <strong class="text-slate-900">${total}</strong> rubros`;
        }

        if (!navEl) return;
        navEl.innerHTML = '';

        if (totalPages <= 1) return;

        const prevDisabled = rubrosCurrentPage === 1;
        navEl.insertAdjacentHTML('beforeend', `
            <button onclick="changeRubrosPage(${rubrosCurrentPage - 1})" ${prevDisabled ? 'disabled' : ''} class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition flex items-center gap-1 shadow-2xs">
                <i class="fa-solid fa-chevron-left text-[10px]"></i> Anterior
            </button>
        `);

        for (let i = 1; i <= totalPages; i++) {
            const isActive = i === rubrosCurrentPage;
            navEl.insertAdjacentHTML('beforeend', `
                <button onclick="changeRubrosPage(${i})" class="w-8 h-8 rounded-xl text-xs font-bold transition flex items-center justify-center ${isActive ? 'bg-[#133527] text-white shadow-sm font-black' : 'bg-white border border-stone-200 text-slate-700 hover:bg-stone-50 shadow-2xs'}">
                    ${i}
                </button>
            `);
        }

        const nextDisabled = rubrosCurrentPage === totalPages;
        navEl.insertAdjacentHTML('beforeend', `
            <button onclick="changeRubrosPage(${rubrosCurrentPage + 1})" ${nextDisabled ? 'disabled' : ''} class="px-3 py-1.5 rounded-xl border border-stone-200 bg-white text-xs font-bold text-slate-700 hover:bg-stone-50 disabled:opacity-40 disabled:cursor-not-allowed transition flex items-center gap-1 shadow-2xs">
                Siguiente <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </button>
        `);
    }

    function filterRubros(cat) {
        document.querySelectorAll('.rubro-btn').forEach(b => {
            b.classList.remove('active', 'bg-[#133527]', 'bg-agro-700', 'text-white');
            b.classList.add('bg-white', 'text-slate-700');
        });
        if (window.event && window.event.target && window.event.target.classList) {
            window.event.target.classList.add('active', 'bg-[#133527]', 'text-white');
            window.event.target.classList.remove('bg-white', 'text-slate-700');
        }

        if (cat === 'todos') {
            renderRubrosGrid(RUBROS_LISTA);
        } else {
            const filtered = RUBROS_LISTA.filter(r => r.category === cat);
            renderRubrosGrid(filtered);
        }
    }

    function searchRubros() {
        const query = document.getElementById('searchRubroInput').value.toLowerCase().trim();
        if (!query) {
            renderRubrosGrid(RUBROS_LISTA);
            return;
        }
        const filtered = RUBROS_LISTA.filter(r => {
            const matchName = r.name.toLowerCase().includes(query);
            const matchDesc = r.desc.toLowerCase().includes(query);
            const matchCat = r.category.toLowerCase().includes(query);
            const matchForma = r.forma.toLowerCase().includes(query);
            const matchTiempo = r.tiempo.toLowerCase().includes(query);
            const matchAldea = r.aldeas.some(a => a.toLowerCase().includes(query));
            return matchName || matchDesc || matchCat || matchForma || matchTiempo || matchAldea;
        });
        renderRubrosGrid(filtered);
    }

    function openRubroModal(id) {
        const rubro = RUBROS_LISTA.find(r => r.id === id);
        if (!rubro) return;

        const modal = document.getElementById('rubroModal');
        const body = document.getElementById('rubroModalBody');
        if (!modal || !body) return;

        const aldeasHtml = rubro.aldeas.map(a => `<span class="bg-amber-100 text-amber-900 border border-amber-300 font-bold text-xs px-3 py-1 rounded-lg flex items-center gap-1.5"><i class="fa-solid fa-location-dot text-amber-600 text-[10px]"></i> ${a}</span>`).join(' ');

        body.innerHTML = `
            <div class="space-y-6">
                <div class="flex items-start gap-4">
                    <div class="w-16 h-16 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center justify-center text-3xl font-bold shrink-0 shadow-xs">
                        <i class="fa-solid ${rubro.icon}"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="bg-amber-100 text-amber-900 text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wider border border-amber-300">
                                Rubro #${rubro.id} • ${rubro.category.toUpperCase()}
                            </span>
                            <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2.5 py-0.5 rounded-full flex items-center gap-1 border border-emerald-200">
                                <i class="fa-solid fa-check"></i> Producción Activa
                            </span>
                        </div>
                        <h3 class="font-serif text-2xl font-extrabold text-slate-900">${rubro.name}</h3>
                        <p class="text-xs text-slate-500 font-semibold"><i class="fa-solid fa-mountain mr-1 text-emerald-700"></i> Altitud Óptima: ${rubro.altitud}</p>
                    </div>
                </div>

                <p class="text-sm text-slate-600 leading-relaxed bg-[#faf8f5] p-4 rounded-2xl border border-stone-200">
                    ${rubro.desc}
                </p>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="bg-emerald-50/70 border border-emerald-200 p-4 rounded-2xl">
                        <span class="text-[10px] font-bold uppercase text-emerald-900 block tracking-wider mb-1"><i class="fa-solid fa-tractor mr-1.5 text-emerald-700"></i> Forma de Cultivo y Suelo</span>
                        <p class="text-xs text-slate-700 font-medium leading-relaxed">${rubro.forma}</p>
                    </div>
                    <div class="bg-sky-50/70 border border-sky-200 p-4 rounded-2xl">
                        <span class="text-[10px] font-bold uppercase text-sky-900 block tracking-wider mb-1"><i class="fa-solid fa-faucet-drip mr-1.5 text-sky-600"></i> Régimen Hídrico (365 Días)</span>
                        <p class="text-xs text-slate-700 font-medium leading-relaxed">${rubro.agua_riego}</p>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="bg-stone-50 border border-stone-200 p-3.5 rounded-2xl">
                        <span class="text-[10px] font-bold uppercase text-slate-400 block tracking-wider mb-0.5"><i class="fa-solid fa-stopwatch mr-1.5 text-amber-600"></i> Ciclo Biológico</span>
                        <p class="text-xs font-bold text-slate-800">${rubro.tiempo}</p>
                    </div>
                    <div class="bg-emerald-50 border border-emerald-200 p-3.5 rounded-2xl">
                        <span class="text-[10px] font-bold uppercase text-emerald-900 block tracking-wider mb-0.5"><i class="fa-solid fa-calendar-days mr-1.5 text-emerald-700"></i> Calendario de Cosecha</span>
                        <p class="text-xs font-extrabold text-emerald-800">${rubro.meses}</p>
                    </div>
                </div>

                <div>
                    <span class="text-[11px] font-bold uppercase text-slate-500 block tracking-wider mb-2.5">
                        <i class="fa-solid fa-map-location-dot text-rose-500 mr-1.5"></i> Aldeas Productoras Donde se Cosecha:
                    </span>
                    <div class="flex flex-wrap gap-2">
                        ${aldeasHtml}
                    </div>
                </div>

                <div class="pt-4 border-t border-stone-200 flex flex-col sm:flex-row gap-3">
                    <button onclick="consultarRubroEnChat('${rubro.name}')" class="flex-1 bg-[#133527] hover:bg-[#1b4332] text-white font-bold py-3.5 rounded-xl transition text-center text-xs flex items-center justify-center gap-2 shadow-xs">
                        <i class="fa-solid fa-comments"></i> Consultar Disponibilidad en Chat
                    </button>
                    <button onclick="closeRubroModal()" class="px-5 py-3.5 bg-stone-100 hover:bg-stone-200 text-slate-700 font-bold rounded-xl transition text-xs">
                        Cerrar
                    </button>
                </div>
            </div>
        `;

        modal.classList.remove('hidden');
    }

    function closeRubroModal() {
        const modal = document.getElementById('rubroModal');
        if (modal) modal.classList.add('hidden');
    }

    function consultarRubroEnChat(rubroName) {
        closeRubroModal();
        toggleComunalChat();
        setTimeout(() => {
            const input = document.getElementById('chatInput');
            if (input) {
                input.value = `Hola, quisiera consultar disponibilidad y arrime del rubro: ${rubroName}`;
                input.focus();
            }
        }, 200);
    }
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
