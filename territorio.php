<?php
// territorio.php - Caracterización Territorial, Cuencas Hídricas y Geografía Andina
require __DIR__ . '/includes/app.php';

$pageTitle = 'Territorio, Cuencas Hídricas y Mapa Estratégico • Eje Andino';
require __DIR__ . '/includes/header.php';
?>

<!-- BANNER HERO DE PÁGINA: TERRITORIO Y GEOGRAFÍA (Diseño Editorial y Montaña Andina) -->
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
                    <i class="fa-solid fa-map-location-dot text-amber-400"></i> Georreferenciación Estratégica • Estado Táchira
                </div>
                <h1 class="text-3xl sm:text-5xl font-serif font-extrabold text-white tracking-tight">
                    Territorio, Cuencas Hídricas y Eje Andino
                </h1>
                <h2 class="text-base sm:text-lg font-semibold text-emerald-200/90 mt-2">
                    Ciudad de La Grita • Municipio Jáuregui (Aldea Quebrada de San José)
                </h2>
                <p class="text-emerald-100/80 text-xs sm:text-sm md:text-base mt-3 leading-relaxed font-normal">
                    Sede principal y centro de acopio comunal al noroeste del Estado Táchira. El <strong>principal motor agrícola de la entidad</strong> aportando cerca del <strong>65% de la producción de hortalizas y tubérculos</strong>, con clima fresco andino de <strong>18°C a 19°C</strong>, reservas de agua continuas los 365 días del año y salida logística multimodal hacia el Mar Caribe y la frontera.
                </p>
            </div>
            <div class="flex flex-col sm:flex-row md:flex-col gap-2 shrink-0">
                <a href="#mapa-venezuela" class="bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold px-5 py-3 rounded-xl text-xs uppercase tracking-wider transition text-center shadow-xs active:scale-95">
                    <i class="fa-solid fa-earth-americas mr-1.5"></i> Ver Google Maps
                </a>
                <a href="#municipios" class="bg-white/5 hover:bg-white/10 text-white font-medium px-5 py-3 rounded-xl text-xs uppercase tracking-wider transition text-center border border-white/15 active:scale-95">
                    <i class="fa-solid fa-tree-city mr-1.5"></i> Ver Municipios & Aldeas
                </a>
            </div>
        </div>
    </div>
</div>

<!-- SECCIÓN 1: GOOGLE MAPS Y UBICACIÓN ESTRATÉGICA -->
<section id="mapa-venezuela" class="py-20 bg-[#0a1c14] text-white relative overflow-hidden border-b border-emerald-950">
    <!-- Curvas topográficas andinas -->
    <div class="absolute inset-0 opacity-[0.05] pointer-events-none overflow-hidden flex items-center justify-center">
        <svg class="w-[140%] h-[140%]" viewBox="0 0 1200 800" fill="none" stroke="currentColor">
            <path d="M-100,200 C200,100 400,350 700,200 C1000,50 1100,280 1300,180" stroke-width="1.5" />
            <path d="M-100,400 C180,260 480,500 800,360 C1100,220 1200,450 1300,360" stroke-width="1.5" />
        </svg>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        
        <div class="grid lg:grid-cols-12 gap-8 items-start mb-14">
            
            <!-- Columna Izquierda: Google Maps Iframe & Selectores -->
            <div class="lg:col-span-7 bg-[#0e2a1f]/90 border border-emerald-800/60 rounded-3xl p-5 sm:p-7 shadow-2xl relative backdrop-blur-xs">
                
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4 pb-4 border-b border-emerald-800/60">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="text-xs font-bold text-emerald-200 uppercase tracking-wider">Cartografía Digital Interactiva</span>
                    </div>
                    <div class="flex flex-wrap gap-1.5 bg-[#0a1c14] p-1.5 rounded-xl border border-emerald-800/80 text-[11px] font-bold">
                        <button id="btnMapGoogle" onclick="switchMapView('google')" class="px-3.5 py-1.5 rounded-lg bg-emerald-800 text-white transition flex items-center gap-1.5 shadow-sm border border-emerald-600/50">
                            <i class="fa-solid fa-map-location-dot text-amber-300"></i> Google Maps
                        </button>
                        <button id="btnMapNacional" onclick="switchMapView('nacional')" class="px-3.5 py-1.5 rounded-lg text-emerald-200/80 hover:text-white transition flex items-center gap-1.5">
                            <i class="fa-solid fa-earth-americas"></i> Eje Geopolítico
                        </button>
                        <button id="btnMapZoom" onclick="switchMapView('zoom')" class="px-3.5 py-1.5 rounded-lg text-emerald-200/80 hover:text-white transition flex items-center gap-1.5">
                            <i class="fa-solid fa-mountain"></i> Aldeas Jáuregui
                        </button>
                    </div>
                </div>

                <!-- Contenedor 0: Google Maps Iframe -->
                <div id="containerMapGoogle" class="relative">
                    <div class="relative w-full h-[460px] sm:h-[500px] rounded-2xl overflow-hidden border border-emerald-800/80 shadow-2xl bg-slate-950">
                        <iframe 
                            id="gmapIframe"
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
                                <span>8°08′00″ N, 71°59′00″ O • Altitud: 1.440 msnm</span>
                            </div>
                            <div class="flex items-center gap-2 pointer-events-auto ml-auto">
                                <a href="https://www.google.com/maps/search/?api=1&query=Aldea+Quebrada+de+San+Jose+La+Grita+Jauregui+Tachira+Venezuela" 
                                   target="_blank" 
                                   rel="noopener noreferrer" 
                                   class="bg-amber-400 hover:bg-amber-300 text-slate-950 text-xs font-bold px-3.5 py-2 rounded-xl shadow-lg flex items-center gap-2 transition transform active:scale-95">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    <span>Abrir en Google Maps</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contenedor 1: Vista Nacional (SVG) -->
                <div id="containerMapNacional" class="relative hidden">
                    <div class="w-full overflow-hidden rounded-2xl bg-gradient-to-b from-slate-950 to-slate-900 border border-slate-800/60 p-2">
                        <svg viewBox="0 0 800 620" class="w-full h-auto max-h-[500px] mx-auto select-none" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <linearGradient id="venGradT" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#1e293b"/>
                                    <stop offset="100%" stop-color="#0f172a"/>
                                </linearGradient>
                                <linearGradient id="tachiraGradT" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#15803d"/>
                                    <stop offset="100%" stop-color="#166534"/>
                                </linearGradient>
                            </defs>
                            <text x="440" y="45" fill="#38bdf8" font-size="13" font-weight="900" letter-spacing="4" opacity="0.6">MAR CARIBE</text>
                            <path d="M148.2 55.0 L146.7 66.4 L116.1 72.0 L133.1 94.0 L132.5 119.3 L109.5 147.3 L129.2 185.7 L151.7 182.5 L163.4 147.6 L147.2 130.6 L144.6 94.0 L209.5 74.4 L202.3 51.6 L220.6 36.3 L239.3 70.3 L275.8 71.1 L309.7 98.0 L311.7 114.0 L358.5 114.4 L414.2 109.5 L444.0 131.1 L483.9 137.1 L513.1 122.0 L513.7 109.8 L578.3 106.9 L640.7 106.2 L596.5 120.5 L614.3 143.3 L655.9 147.0 L695.4 170.7 L703.8 209.5 L730.9 208.4 L751.3 219.8 L710.0 248.2 L705.5 265.8 L723.3 283.7 L710.4 292.8 L678.3 300.5 L679.4 322.8 L665.2 336.1 L700.5 372.8 L707.4 386.5 L688.3 404.9 L630.1 423.0 L592.6 430.5 L577.5 441.9 L536.1 429.8 L497.5 423.7 L487.8 428.1 L511.1 440.6 L509.0 473.0 L516.2 503.5 L560.4 507.6 L563.2 517.8 L526.0 531.5 L519.9 552.0 L498.4 559.9 L459.7 571.2 L449.6 586.0 L409.1 589.2 L380.4 563.6 L364.5 515.4 L350.6 498.5 L331.7 487.8 L358.1 463.8 L356.4 453.0 L341.5 438.6 L331.1 406.5 L335.1 371.8 L346.8 355.6 L356.2 329.6 L337.7 321.2 L308.0 326.8 L270.5 324.2 L249.4 329.3 L212.7 287.7 L182.5 281.6 L115.5 286.2 L103.0 269.4 L90.2 265.4 L88.4 255.3 L94.6 237.4 L90.5 217.9 L79.0 207.3 L72.3 185.1 L45.4 181.8 L59.8 153.6 L66.2 119.1 L81.3 101.1 L101.5 87.2 L114.7 63.1 L148.2 55.0 Z" fill="url(#venGradT)" stroke="#475569" stroke-width="1.8"/>
                            <path d="M90.5 217.9 L135.0 218.0 L145.0 248.0 L122.0 273.0 L90.2 265.4 L88.4 255.3 L94.6 237.4 Z" fill="url(#tachiraGradT)" stroke="#22c55e" stroke-width="2.5"/>
                            <text x="96" y="260" fill="#86efac" font-size="9" font-weight="900" letter-spacing="1">TÁCHIRA</text>
                            
                            <!-- Rutas Estratégicas -->
                            <path d="M114.4 231.2 Q135 195 162.9 166.4" fill="none" stroke="#38bdf8" stroke-width="2.2" stroke-dasharray="4,4"/>
                            <circle cx="162.9" cy="166.4" r="4.5" fill="#0284c7" stroke="#ffffff" stroke-width="1.5"/>
                            <text x="172" y="167" fill="#38bdf8" font-size="8.5" font-weight="black">Puerto La Ceiba (Mar Caribe)</text>
                            
                            <path d="M114.4 231.2 L90.5 246.2" fill="none" stroke="#4ade80" stroke-width="2.5" stroke-dasharray="3,3"/>
                            <circle cx="90.5" cy="246.2" r="4.5" fill="#16a34a" stroke="#ffffff" stroke-width="1.5"/>
                            <text x="25" y="258" fill="#4ade80" font-size="8.5" font-weight="black">Frontera San Antonio / Cúcuta</text>
                            
                            <!-- Nodo Central La Grita -->
                            <g transform="translate(114.4, 231.2)">
                                <circle cx="0" cy="0" r="10" fill="#fbbf24" opacity="0.6"/>
                                <circle cx="0" cy="0" r="5" fill="#ffffff" stroke="#d97706" stroke-width="2"/>
                                <text x="12" y="4" fill="#fbbf24" font-size="10" font-weight="black">★ La Grita (Quebrada de San José)</text>
                            </g>
                        </svg>
                    </div>
                </div>

                <!-- Contenedor 2: Zoom Jáuregui / Topografía Local -->
                <div id="containerMapZoom" class="hidden">
                    <div class="bg-gradient-to-br from-slate-950 via-agro-950 to-slate-900 border border-agro-500/40 rounded-2xl p-6 relative overflow-hidden">
                        <div class="flex items-center justify-between mb-4 pb-3 border-b border-white/10">
                            <div>
                                <span class="bg-amberGold text-slate-950 text-[10px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider">Topografía Local</span>
                                <h4 class="text-xl font-black text-white mt-1">Municipio Jáuregui (La Grita)</h4>
                            </div>
                            <span class="text-xs font-bold text-amber-300 bg-amber-950/60 border border-amber-500/40 px-3 py-1 rounded-xl">
                                <i class="fa-solid fa-temperature-three-quarters mr-1"></i> 18°C - 19°C Media
                            </span>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-3 mb-4">
                            <div class="bg-white/5 border border-white/10 p-3.5 rounded-xl">
                                <span class="text-[10px] text-amberGold font-black uppercase block tracking-wider"><i class="fa-solid fa-house-flag mr-1"></i>Sede Principal EPSDC:</span>
                                <h5 class="text-sm font-extrabold text-white mt-0.5">Aldea Quebrada de San José</h5>
                                <p class="text-[11px] text-slate-300 mt-1">Ubicada al noroeste del Táchira, en el corazón del macizo andino. Centro neural de articulación con las UPF.</p>
                            </div>
                            <div class="bg-white/5 border border-white/10 p-3.5 rounded-xl">
                                <span class="text-[10px] text-cyan-300 font-black uppercase block tracking-wider"><i class="fa-solid fa-smog mr-1"></i>Microclima & Neblina:</span>
                                <h5 class="text-sm font-extrabold text-white mt-0.5">Manto de Niebla Andina</h5>
                                <p class="text-[11px] text-slate-300 mt-1">Frecuente condensación matutina y vespertina. Modera la radiación solar y favorece cosechas continuas los 365 días.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Columna Derecha: 4 Bento Cards de Georreferenciación -->
            <div class="lg:col-span-5 space-y-4">
                
                <div class="bg-gradient-to-br from-amber-500/20 via-slate-900 to-slate-900 border border-amber-500/40 p-6 rounded-3xl shadow-xl relative overflow-hidden group">
                    <div class="flex items-start justify-between gap-3 mb-2">
                        <div class="w-12 h-12 rounded-2xl bg-amber-400/20 border border-amber-400/40 text-amberGold flex items-center justify-center text-xl shrink-0 group-hover:scale-110 transition">
                            <i class="fa-solid fa-trophy"></i>
                        </div>
                        <span class="bg-amberGold text-slate-950 font-black text-xs px-3 py-1 rounded-full uppercase tracking-wider">
                            Motor #1 del Táchira
                        </span>
                    </div>
                    <h4 class="text-2xl font-black text-white mt-1">65% de la Producción Agrícola</h4>
                    <p class="text-xs text-slate-300 leading-relaxed mt-2">
                        El <strong>Municipio Jáuregui (La Grita)</strong> se destaca como el <strong>principal motor agrícola del estado Táchira</strong>, aportando cerca del <strong>65% de la producción total de hortalizas, papas y rubros del campo</strong> de toda la entidad.
                    </p>
                </div>

                <div class="bg-slate-900/90 border border-slate-800 p-6 rounded-3xl shadow-xl relative">
                    <div class="flex items-start justify-between gap-3 mb-2">
                        <div class="w-12 h-12 rounded-2xl bg-cyan-500/20 border border-cyan-400/40 text-cyan-300 flex items-center justify-center text-xl shrink-0">
                            <i class="fa-solid fa-smog"></i>
                        </div>
                        <span class="bg-cyan-500/20 text-cyan-300 border border-cyan-400/30 text-[10px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider">
                            18°C a 19°C Media
                        </span>
                    </div>
                    <h4 class="text-lg font-black text-white mt-1">Clima Montañoso Andino y Neblina</h4>
                    <p class="text-xs text-slate-300 leading-relaxed mt-2">
                        Gozamos de un <strong>clima montañoso andino fresco</strong>, con una temperatura media anual de aproximadamente <strong>18°C a 19°C</strong> y la <strong>presencia frecuente de neblina andina</strong>. Este manto de niebla reduce la evaporación, manteniendo la tierra húmeda y fértil sin choque térmico.
                    </p>
                </div>

                <div class="bg-slate-900/90 border border-slate-800 p-6 rounded-3xl shadow-xl relative">
                    <div class="flex items-start justify-between gap-3 mb-2">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 border border-emerald-400/40 text-emerald-300 flex items-center justify-center text-xl shrink-0">
                            <i class="fa-solid fa-location-crosshairs"></i>
                        </div>
                        <span class="bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 text-[10px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider">
                            Noroeste del Táchira
                        </span>
                    </div>
                    <h4 class="text-lg font-black text-white mt-1">Aldea Quebrada de San José</h4>
                    <p class="text-xs text-slate-300 leading-relaxed mt-2">
                        Asentados en la <strong>Ciudad de La Grita</strong>, dentro de la <strong>Aldea Quebrada de San José</strong>. Una comunidad campesina organizada, enlazada mediante caminos comunales y redes de gravedad hacia los campos de siembra.
                    </p>
                </div>

                <div class="bg-slate-900/90 border border-slate-800 p-6 rounded-3xl shadow-xl flex items-center justify-between gap-4">
                    <div class="space-y-1">
                        <span class="text-[10px] text-amberGold font-black uppercase tracking-wider">Conectividad Multimodal</span>
                        <h5 class="text-sm font-black text-white">Caribe, Frontera y Caracas</h5>
                        <p class="text-[11px] text-slate-400">Salida marítima por Puerto La Ceiba y terrestre por San Antonio y Troncales 1 y 5.</p>
                    </div>
                    <a href="#logistica-multimodal" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-bold transition shrink-0 border border-white/15">
                        Ver Rutas
                    </a>
                </div>

            </div>

        </div>

    </div>
</section>

<!-- SECCIÓN 2: EJE ANDINO (TÁCHIRA, MÉRIDA, TRUJILLO) -->
<section id="eje-andino" class="py-20 bg-[#0e2a1f] text-white relative overflow-hidden border-b border-emerald-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        
        <div class="text-center max-w-4xl mx-auto mb-14">
            <span class="bg-amber-400/10 text-amber-300 border border-amber-400/30 text-xs font-bold px-4 py-1.5 rounded-full uppercase tracking-wider inline-flex items-center gap-2 mb-3">
                <i class="fa-solid fa-mountain-sun text-amber-300"></i> Articulación Comunal Tri-Estadal
            </span>
            <h2 class="text-3xl sm:text-5xl font-serif font-extrabold text-white tracking-tight">
                Eje Andino de Producción e Intercambio
            </h2>
            <h3 class="text-lg sm:text-2xl font-serif font-bold text-amber-300 mt-2">
                Táchira • Mérida • Trujillo
            </h3>
            <p class="text-emerald-100/80 mt-4 text-xs sm:text-sm md:text-base leading-relaxed font-normal">
                La EPSDC La Nueva Esperanza articula la producción soberana con los tres estados que conforman el <strong>Eje Andino de Venezuela</strong>. Una cordillera interconectada por cuencas hídricas, pisos térmicos complementarios y redes campesinas comunales.
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8 mb-12">
            
            <!-- Táchira -->
            <div class="bg-gradient-to-b from-agro-950/80 to-slate-950 border border-agro-500/40 p-7 rounded-3xl shadow-xl flex flex-col justify-between group hover:border-agro-400 transition-all">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="bg-agro-500/20 text-agro-300 border border-agro-400/40 text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-wider">
                            Sede Central & Producción
                        </span>
                        <i class="fa-solid fa-map-pin text-amberGold text-lg"></i>
                    </div>
                    <h4 class="text-2xl font-black text-white group-hover:text-amberGold transition">Estado Táchira</h4>
                    <span class="text-xs font-bold text-emerald-400 block mt-1">Jáuregui, Uribante, San José de Bolívar, Queniquea, Seboruco, Vargas y Humuquena</span>
                    <p class="text-xs text-slate-300 leading-relaxed mt-3">
                        Corazón de la EPSDC. Alta montaña andina con nacientes en el P.N. Juan Pablo Peñaloza, 65% de la producción de hortalizas del estado, truchas de Las Porqueras, café de Pregonero, panela de Humuquena y nodo logístico fronterizo.
                    </p>
                </div>
                <div class="pt-5 mt-5 border-t border-white/10 flex items-center justify-between">
                    <span class="text-[11px] font-bold text-amberGold flex items-center gap-1.5">
                        <i class="fa-solid fa-layer-group text-xs"></i> 10 Municipios
                    </span>
                    <button type="button" onclick="filterByState('tachira')" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-agro-800/80 hover:bg-agro-700 text-white text-xs font-bold transition border border-agro-600/50 shadow-xs cursor-pointer group-hover:border-amberGold">
                        <span>Ver Municipios Táchira</span>
                        <i class="fa-solid fa-arrow-right text-[10px] text-amberGold group-hover:translate-x-0.5 transition-transform"></i>
                    </button>
                </div>
            </div>

            <!-- Mérida -->
            <div class="bg-gradient-to-b from-slate-950 to-slate-900 border border-slate-800 p-7 rounded-3xl shadow-xl flex flex-col justify-between group hover:border-emerald-500/50 transition-all">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="bg-emerald-500/20 text-emerald-300 border border-emerald-400/40 text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-wider">
                            Alta Montaña & Páramo
                        </span>
                        <i class="fa-solid fa-mountain text-emerald-400 text-lg"></i>
                    </div>
                    <h4 class="text-2xl font-black text-white group-hover:text-emerald-400 transition">Estado Mérida</h4>
                    <span class="text-xs font-bold text-emerald-300 block mt-1">Pueblo Llano, Rangel, Miranda y Cardenal Quintero</span>
                    <p class="text-xs text-slate-300 leading-relaxed mt-3">
                        Tierras altas de páramo a más de 2.500 msnm. Alianza comunal para abastecimiento continuo de papa de semilla certificada, ajo andino morado, fresas, zanahoria y hortalizas de clima frío extremo.
                    </p>
                </div>
                <div class="pt-5 mt-5 border-t border-white/10 flex items-center justify-between">
                    <span class="text-[11px] font-bold text-emerald-300 flex items-center gap-1.5">
                        <i class="fa-solid fa-mountain text-xs"></i> Páramo Andino
                    </span>
                    <button type="button" onclick="filterByState('merida')" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-950/80 hover:bg-emerald-900 text-white text-xs font-bold transition border border-emerald-500/50 shadow-xs cursor-pointer group-hover:border-emerald-400">
                        <span>Ver Municipios Mérida</span>
                        <i class="fa-solid fa-arrow-right text-[10px] text-emerald-300 group-hover:translate-x-0.5 transition-transform"></i>
                    </button>
                </div>
            </div>

            <!-- Trujillo -->
            <div class="bg-gradient-to-b from-slate-950 to-slate-900 border border-slate-800 p-7 rounded-3xl shadow-xl flex flex-col justify-between group hover:border-cyan-500/50 transition-all">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="bg-cyan-500/20 text-cyan-300 border border-cyan-400/40 text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-wider">
                            Valles Fértiles & Salida Fluvial
                        </span>
                        <i class="fa-solid fa-water text-cyan-400 text-lg"></i>
                    </div>
                    <h4 class="text-2xl font-black text-white group-hover:text-cyan-400 transition">Estado Trujillo</h4>
                    <span class="text-xs font-bold text-cyan-300 block mt-1">Boconó, Urdaneta, Carache y La Ceiba</span>
                    <p class="text-xs text-slate-300 leading-relaxed mt-3">
                        Valles templados interandinos y proximidad estratégica con el <strong>Puerto de La Ceiba</strong> en el Lago de Maracaibo. Producción de café especial, tomate, legumbres, plátano y cítricos.
                    </p>
                </div>
                <div class="pt-5 mt-5 border-t border-white/10 flex items-center justify-between">
                    <span class="text-[11px] font-bold text-cyan-300 flex items-center gap-1.5">
                        <i class="fa-solid fa-ship text-xs"></i> Conexión Fluvial
                    </span>
                    <button type="button" onclick="filterByState('trujillo')" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-cyan-950/80 hover:bg-cyan-900 text-white text-xs font-bold transition border border-cyan-500/50 shadow-xs cursor-pointer group-hover:border-cyan-400">
                        <span>Ver Municipios Trujillo</span>
                        <i class="fa-solid fa-arrow-right text-[10px] text-cyan-300 group-hover:translate-x-0.5 transition-transform"></i>
                    </button>
                </div>
            </div>

        </div>

        <!-- Banner Concatenación Comunal Andina -->
        <div class="bg-gradient-to-r from-slate-950 via-agro-950 to-slate-900 p-6 sm:p-8 rounded-3xl border border-agro-500/30 shadow-2xl flex flex-col md:flex-row items-center justify-between gap-6 relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-agro-500/10 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="flex items-start sm:items-center gap-4 relative z-10 text-center sm:text-left">
                <div class="w-14 h-14 rounded-2xl bg-amberGold/15 border border-amberGold/30 text-amberGold flex items-center justify-center shrink-0 shadow-inner">
                    <i class="fa-solid fa-handshake-angle text-2xl"></i>
                </div>
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-1.5 text-[11px] font-extrabold uppercase tracking-wider text-amberGold">
                        <span>Articulación del Poder Popular</span>
                    </div>
                    <h4 class="text-xl sm:text-2xl font-black text-white tracking-tight">
                        Red Comunal Andina de Producción e Intercambio
                    </h4>
                    <p class="text-xs sm:text-sm text-slate-300 font-light max-w-2xl leading-relaxed">
                        ¿Perteneces a una UPF o Consejo Comunal en Mérida, Trujillo o Táchira? Articúlate con la EPSDC para acopio, arrime solidario y distribución justa.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0 relative z-10 w-full sm:w-auto">
                <a href="<?= e(url('#productores')) ?>" class="w-full sm:w-auto px-6 py-3.5 rounded-2xl bg-gradient-to-r from-amberGold to-amber-500 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-xs uppercase tracking-wider transition-all duration-200 shadow-md hover:shadow-lg flex items-center justify-center gap-2 transform active:scale-95">
                    <i class="fa-solid fa-file-signature text-sm"></i>
                    <span>Registrar UPF Andina</span>
                </a>
            </div>
        </div>

    </div>
</section>

<!-- SECCIÓN DESTACADA: RUBROS EMBLEMA Y TRADICIONES PRODUCTIVAS TERRITORIALES -->
<section id="rubros-emblema" class="py-20 bg-[#faf8f5] border-b border-stone-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto mb-14">
            <span class="bg-amber-100 text-amber-950 border border-amber-300 text-xs font-bold px-4 py-1.5 rounded-full uppercase tracking-wider inline-flex items-center gap-2 mb-3">
                <i class="fa-solid fa-award text-amber-700"></i> Denominación y Tradición Andina
            </span>
            <h2 class="text-3xl sm:text-5xl font-serif font-extrabold text-slate-900 tracking-tight">
                Rubros Emblema de Nuestra Red Comunal
            </h2>
            <div class="flex items-center justify-center gap-2 mt-2">
                <p class="text-slate-600 text-xs sm:text-sm mt-3 leading-relaxed max-w-2xl">
                    Cada rincón del macizo montañoso posee una vocación única: aguas cristalinas de páramo para la trucha, laderas empinadas para el café de estricta altura y valles cálidos con trapiches campesinos de panela pura.
                </p>
                <span class="font-hand text-lg text-emerald-700 font-bold hidden sm:inline -rotate-1">
                    • Identidad territorial
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <!-- 1. Truchas en Las Porqueras -->
            <div class="bg-gradient-to-br from-cyan-950 via-slate-900 to-slate-950 text-white rounded-3xl p-7 border border-cyan-500/30 shadow-xl flex flex-col justify-between relative overflow-hidden group hover:border-cyan-400 transition-all duration-300">
                <div class="absolute -right-10 -bottom-10 w-44 h-44 bg-cyan-500/10 rounded-full blur-2xl pointer-events-none"></div>
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="bg-cyan-500/20 text-cyan-300 border border-cyan-400/40 text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-wider">
                            Jáuregui • 2.400 msnm
                        </span>
                        <div class="w-10 h-10 rounded-2xl bg-cyan-500/20 text-cyan-300 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-fish"></i>
                        </div>
                    </div>
                    <h3 class="text-2xl font-black text-white group-hover:text-cyan-300 transition">Las Truchas en Las Porqueras</h3>
                    <div class="text-xs font-bold text-cyan-400 mt-1 flex items-center gap-1.5">
                        <i class="fa-solid fa-water"></i> Truchicultura de Aguas Gélidas de Páramo
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed mt-3">
                        En el sector <strong>Las Porqueras</strong> (vía Páramo del Batallón y La Negra), manantiales de deshielo a temperaturas entre <strong>9°C y 14°C</strong> alimentan criaderos comunales de <strong>Trucha Arcoíris</strong>. Un rubro proteico de pureza incomparable, libre de químicos y criado en flujo hídrico continuo.
                    </p>
                </div>
                <div class="pt-5 mt-5 border-t border-white/10 flex items-center justify-between text-xs">
                    <span class="text-slate-400"><i class="fa-solid fa-map-pin text-cyan-400"></i> Las Porqueras, Jáuregui</span>
                    <span class="badge bg-cyan-500/20 text-cyan-200 border border-cyan-400/30 rounded-pill px-2.5 py-1 font-bold">Proteína Pura</span>
                </div>
            </div>

            <!-- 2. El Café de Pregonero -->
            <div class="bg-gradient-to-br from-amber-950 via-slate-900 to-slate-950 text-white rounded-3xl p-7 border border-amber-600/30 shadow-xl flex flex-col justify-between relative overflow-hidden group hover:border-amber-400 transition-all duration-300">
                <div class="absolute -right-10 -bottom-10 w-44 h-44 bg-amber-500/10 rounded-full blur-2xl pointer-events-none"></div>
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="bg-amber-500/20 text-amber-300 border border-amber-400/40 text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-wider">
                            Uribante • 1.500 - 1.900 msnm
                        </span>
                        <div class="w-10 h-10 rounded-2xl bg-amber-500/20 text-amber-300 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-mug-hot"></i>
                        </div>
                    </div>
                    <h3 class="text-2xl font-black text-white group-hover:text-amber-300 transition">El Café de Pregonero</h3>
                    <div class="text-xs font-bold text-amber-400 mt-1 flex items-center gap-1.5">
                        <i class="fa-solid fa-seedling"></i> Café Arábica de Estricta Altura
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed mt-3">
                        Cultivado en las vertientes montañosas del <strong>Municipio Uribante (capital Pregonero)</strong>. Cosecha manual grano a grano bajo sombra natural. Destaca por su <strong>aroma profundo, acidez cítrica brillante, cuerpo achocolatado</strong> y notas dulces a caramelo andino.
                    </p>
                </div>
                <div class="pt-5 mt-5 border-t border-white/10 flex items-center justify-between text-xs">
                    <span class="text-slate-400"><i class="fa-solid fa-map-pin text-amber-400"></i> Pregonero, Uribante</span>
                    <span class="badge bg-amber-500/20 text-amber-200 border border-amber-400/30 rounded-pill px-2.5 py-1 font-bold">Café Especial</span>
                </div>
            </div>

            <!-- 3. Humuquena La Panela -->
            <div class="bg-gradient-to-br from-emerald-950 via-slate-900 to-slate-950 text-white rounded-3xl p-7 border border-emerald-500/30 shadow-xl flex flex-col justify-between relative overflow-hidden group hover:border-emerald-400 transition-all duration-300">
                <div class="absolute -right-10 -bottom-10 w-44 h-44 bg-emerald-500/10 rounded-full blur-2xl pointer-events-none"></div>
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="bg-emerald-500/20 text-emerald-300 border border-emerald-400/40 text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-wider">
                            Humuquena • Valles Andinos
                        </span>
                        <div class="w-10 h-10 rounded-2xl bg-emerald-500/20 text-emerald-300 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-cubes-stacked"></i>
                        </div>
                    </div>
                    <h3 class="text-2xl font-black text-white group-hover:text-emerald-300 transition">Humuquena: La Panela</h3>
                    <div class="text-xs font-bold text-emerald-400 mt-1 flex items-center gap-1.5">
                        <i class="fa-solid fa-fire"></i> Trapiches Paneleros Campesinos Tradicionales
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed mt-3">
                        En el corazón de <strong>Humuquena (Municipio San Judas Tadeo)</strong>, las moliendas campesinas procesan la caña de azúcar en <strong>trapiches comunales</strong> para extraer el jugo virgen. Panela pura en bloque, batida y granulada, rica en minerales y libre de sulfito o blanqueadores industriales.
                    </p>
                </div>
                <div class="pt-5 mt-5 border-t border-white/10 flex items-center justify-between text-xs">
                    <span class="text-slate-400"><i class="fa-solid fa-map-pin text-emerald-400"></i> Humuquena, San Judas Tadeo</span>
                    <span class="badge bg-emerald-500/20 text-emerald-200 border border-emerald-400/30 rounded-pill px-2.5 py-1 font-bold">100% Artesanal</span>
                </div>
            </div>

        </div>

    </div>
</section>

<!-- SECCIÓN 3: DIRECTORIO DE MUNICIPIOS Y ALDEAS PRODUCTORAS -->
<section id="municipios" class="py-20 bg-white border-t border-b border-stone-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end mb-10 gap-6">
            <div>
                <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-emerald-900 bg-emerald-100/80 px-3.5 py-1 rounded-full border border-emerald-300/80 mb-3">
                    <i class="fa-solid fa-map-location-dot text-emerald-700"></i> Desglose Territorial y Georreferenciación
                </span>
                <h2 class="text-3xl sm:text-5xl font-serif font-extrabold text-slate-900 tracking-tight">
                    Municipios y Aldeas Productoras
                </h2>
                <p class="text-slate-600 text-xs sm:text-sm mt-2 max-w-2xl leading-relaxed">
                    Conozca las comunidades campesinas, parroquias y aldeas específicas donde nuestras Unidades de Producción Familiar (UPF) cosechan activamente bajo prácticas agroecológicas.
                </p>
            </div>

            <!-- Filtros por Zona -->
            <div class="flex flex-wrap items-center gap-2">
                <button onclick="filterMunicipiosByZone('todos')" class="mun-filter-btn active bg-[#133527] text-white px-3.5 py-2 rounded-xl text-xs font-bold shadow-xs transition">Todos (12)</button>
                <button onclick="filterMunicipiosByZone('andino')" class="mun-filter-btn bg-stone-100 text-stone-700 hover:bg-stone-200 px-3.5 py-2 rounded-xl text-xs font-bold transition">Eje Andino</button>
                <button onclick="filterMunicipiosByZone('montana')" class="mun-filter-btn bg-stone-100 text-stone-700 hover:bg-stone-200 px-3.5 py-2 rounded-xl text-xs font-bold transition">Montaña Alta</button>
                <button onclick="filterMunicipiosByZone('piedemonte')" class="mun-filter-btn bg-stone-100 text-stone-700 hover:bg-stone-200 px-3.5 py-2 rounded-xl text-xs font-bold transition">Piedemonte</button>
            </div>
        </div>

        <!-- Buscador de Municipios y Aldeas -->
        <div class="mb-8">
            <div class="relative max-w-2xl">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-3.5 text-slate-400 text-sm"></i>
                <input type="text" id="municipioSearchInput" onkeyup="filterMunicipiosData()" placeholder="Buscar municipio, parroquia, aldea (Pattueca, Quebrada de San José, etc.) o rubro..." class="w-full pl-11 pr-4 py-3 bg-white rounded-2xl border border-stone-300 focus:outline-none focus:ring-2 focus:ring-emerald-700 text-xs font-medium shadow-xs">
            </div>
        </div>

        <!-- Cards de Municipios y Aldeas -->
        <div id="municipiosCardsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Inyectado dinámicamente con JS -->
        </div>

    </div>
</section>

<!-- SECCIÓN 4: RESERVAS HÍDRICAS Y GEOGRAFÍA ANDINA -->
<section id="territorio-cuencas" class="py-20 bg-[#faf8f5] border-b border-stone-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-4xl mx-auto mb-16">
            <span class="bg-emerald-100 text-emerald-900 border border-emerald-300 text-xs font-bold px-3.5 py-1 rounded-full uppercase tracking-wider inline-flex items-center gap-1.5 mb-3">
                <i class="fa-solid fa-mountain-sun text-emerald-700"></i> Macizo Montañoso del Norte del Táchira
            </span>
            <h2 class="text-3xl sm:text-5xl font-serif font-extrabold text-slate-900 mt-2 tracking-tight">
                El Final de la Columna Vertebral del Planeta
            </h2>
            <h3 class="text-xl sm:text-2xl font-serif font-bold text-emerald-900 mt-2">
                Reservas Hídricas Permanentes y Cosecha Continua los 365 Días del Año
            </h3>
            <p class="text-slate-600 mt-5 text-sm sm:text-base leading-relaxed">
                Nuestras unidades socioproductivas y parcelas familiares están situadas en las montañas del norte del estado Táchira: el <strong>final de la cordillera vertebral del planeta Tierra</strong>. Contamos con <strong>reservas hídricas permanentes</strong> nacientes en páramos y parques nacionales, alimentando acequias y sistemas de riego por gravedad que aseguran producción los <strong>365 días del año</strong>.
            </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-16">
            
            <div class="bg-white border border-slate-200/80 hover:border-cyan-500 rounded-3xl p-7 shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between h-full">
                <div>
                    <div class="w-14 h-14 bg-cyan-50 border border-cyan-100 text-cyan-700 rounded-2xl flex items-center justify-center text-2xl mb-5 font-bold shadow-2xs">
                        <i class="fa-solid fa-water"></i>
                    </div>
                    <span class="bg-cyan-50 text-cyan-800 border border-cyan-200/60 text-[10px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider">Agua Inagotable</span>
                    <h4 class="text-lg font-black text-slate-900 mt-2 mb-2 leading-snug">Reservas Hídricas los 365 Días</h4>
                    <p class="text-xs text-slate-600 leading-relaxed font-normal">
                        Nacientes perpetuas del <strong>Parque Nacional Juan Pablo Peñaloza</strong> (Páramos del Batallón y La Negra) y cuencas de los ríos Torbes, Escalante, Grijalba y Uribante. Riego por gravedad sin costos de bombeo.
                    </p>
                </div>
                <div class="mt-4 pt-3.5 border-t border-slate-100 text-[11px] font-bold text-cyan-800 flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-check text-cyan-600"></i> Cosecha activa los 365 días
                </div>
            </div>

            <div class="bg-white border border-slate-200/80 hover:border-agro-500 rounded-3xl p-7 shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between h-full">
                <div>
                    <div class="w-14 h-14 bg-agro-50 border border-agro-100 text-agro-700 rounded-2xl flex items-center justify-center text-2xl mb-5 font-bold shadow-2xs">
                        <i class="fa-solid fa-mountain-sun"></i>
                    </div>
                    <span class="bg-agro-50 text-agro-800 border border-agro-200/60 text-[10px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider">Geografía Sagrada</span>
                    <h4 class="text-lg font-black text-slate-900 mt-2 mb-2 leading-snug">Fin de la Cordillera Vertebral</h4>
                    <p class="text-xs text-slate-600 leading-relaxed font-normal">
                        Cúspide final del arco andino suramericano. Suelos andinos enriquecidos de alta fertilidad natural, terrazas protegidas de la erosión y microclimas frescos que moderan plagas sin necesidad de agroquímicos agresivos.
                    </p>
                </div>
                <div class="mt-4 pt-3.5 border-t border-slate-100 text-[11px] font-bold text-agro-800 flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-check text-agro-600"></i> Macizo montañoso de los Andes
                </div>
            </div>

            <div class="bg-white border border-slate-200/80 hover:border-amber-500 rounded-3xl p-7 shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between h-full">
                <div>
                    <div class="w-14 h-14 bg-amber-50 border border-amber-100 text-amber-800 rounded-2xl flex items-center justify-center text-2xl mb-5 font-bold shadow-2xs">
                        <i class="fa-solid fa-temperature-low"></i>
                    </div>
                    <span class="bg-amber-50 text-amber-900 border border-amber-200/60 text-[10px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider">Multi-Clima</span>
                    <h4 class="text-lg font-black text-slate-900 mt-2 mb-2 leading-snug">Pisos Térmicos Escalonados</h4>
                    <p class="text-xs text-slate-600 leading-relaxed font-normal">
                        Gradiente altitudinal desde <strong>400 msnm</strong> en valles cálidos de piedemonte (plátano, yuca, frutas tropicales) hasta más de <strong>3.100 msnm</strong> en páramos helados (papa negra, zanahoria, fresas y hortalizas finas).
                    </p>
                </div>
                <div class="mt-4 pt-3.5 border-t border-slate-100 text-[11px] font-bold text-amber-900 flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-check text-amber-600"></i> De 400 a 3.100 msnm
                </div>
            </div>

            <div class="bg-white border border-slate-200/80 hover:border-emerald-500 rounded-3xl p-7 shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between h-full">
                <div>
                    <div class="w-14 h-14 bg-emerald-50 border border-emerald-100 text-emerald-800 rounded-2xl flex items-center justify-center text-2xl mb-5 font-bold shadow-2xs">
                        <i class="fa-solid fa-people-roof"></i>
                    </div>
                    <span class="bg-emerald-50 text-emerald-900 border border-emerald-200/60 text-[10px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider">Red Comunal</span>
                    <h4 class="text-lg font-black text-slate-900 mt-2 mb-2 leading-snug">Unidades UPF y Red Productiva</h4>
                    <p class="text-xs text-slate-600 leading-relaxed font-normal">
                        Concatenación directa entre las Unidades de Producción Familiar (UPF), productores locales y la EPSDC La Nueva Esperanza. Soberanía alimentaria genuina sin intermediarios especulativos ni encarecimiento.
                    </p>
                </div>
                <div class="mt-4 pt-3.5 border-t border-slate-100 text-[11px] font-bold text-emerald-900 flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-check text-emerald-600"></i> Comercio justo directo
                </div>
            </div>

        </div>

    </div>
</section>

<!-- SECCIÓN 5: LOGÍSTICA MULTIMODAL, PUERTO LA CEIBA Y FRONTERA CÚCUTA -->
<section id="logistica-multimodal" class="py-20 bg-[#0a1c14] text-white relative overflow-hidden">
    <!-- Curvas topográficas andinas -->
    <div class="absolute inset-0 opacity-[0.05] pointer-events-none overflow-hidden flex items-center justify-center">
        <svg class="w-[140%] h-[140%]" viewBox="0 0 1200 800" fill="none" stroke="currentColor">
            <path d="M-100,200 C200,100 400,350 700,200 C1000,50 1100,280 1300,180" stroke-width="1.5" />
            <path d="M-100,400 C180,260 480,500 800,360 C1100,220 1200,450 1300,360" stroke-width="1.5" />
        </svg>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        
        <div class="text-center max-w-4xl mx-auto mb-16">
            <span class="bg-amber-400/10 text-amber-300 border border-amber-400/30 text-xs font-bold px-4 py-1.5 rounded-full uppercase tracking-wider inline-flex items-center gap-2 mb-3">
                <i class="fa-solid fa-route text-amber-300"></i> Ventajas Geopolíticas y Multimodales
            </span>
            <h2 class="text-3xl sm:text-5xl font-serif font-extrabold text-white tracking-tight">
                Posición Estratégica Continental
            </h2>
            <h3 class="text-xl sm:text-2xl font-serif font-bold text-amber-300 mt-2">
                Salida al Mar Caribe por Puerto La Ceiba y la Frontera más Dinámica de Suramérica
            </h3>
            <p class="text-emerald-100/80 mt-5 text-sm sm:text-base leading-relaxed font-normal">
                Nuestra ubicación en el estado Táchira no solo nos otorga el agua y el clima de los Andes, sino una <strong>posición geopolítica inigualable en el occidente venezolano</strong>. Contamos con acceso expedito tanto hacia el mar como hacia los mercados binacionales más vibrantes.
            </p>
        </div>

        <div class="grid md:grid-cols-2 gap-8 mb-12">
            
            <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-8 shadow-xl flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center text-xl font-bold">
                            <i class="fa-solid fa-ship"></i>
                        </div>
                        <span class="bg-cyan-500/20 text-cyan-300 border border-cyan-400/40 text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-wider">
                            Salida Marítima al Caribe
                        </span>
                    </div>
                    <h3 class="text-2xl font-black text-cyan-300">Puerto de La Ceiba (Mar Caribe)</h3>
                    <p class="text-xs sm:text-sm text-slate-200 leading-relaxed mt-2">
                        Estamos estratégicamente ubicados con proximidad inmediata al <strong>Puerto de La Ceiba</strong>, el punto más cercano de salida hacia el <strong>Mar Caribe y las rutas marítimas internacionales</strong> a través del canal de navegación del Lago de Maracaibo.
                    </p>
                    <ul class="space-y-2 mt-4 text-xs text-slate-300">
                        <li class="flex items-center gap-2"><i class="fa-solid fa-anchor text-cyan-400"></i> Conexión fluvial y lacustre directa hacia los puertos del Caribe.</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-truck-moving text-cyan-400"></i> Despacho de rubros andinos con menor flete hacia los estados costeros e insulares.</li>
                    </ul>
                </div>
                <div class="pt-4 border-t border-white/10 text-xs text-cyan-200 font-bold flex items-center justify-between">
                    <span class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-cyan-400"></i> Corredor Lacustre-Caribe</span>
                    <span class="text-amberGold text-[11px]">Táchira ↔ Trujillo ↔ Mar Caribe</span>
                </div>
            </div>

            <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-8 shadow-xl flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xl font-bold">
                            <i class="fa-solid fa-bridge"></i>
                        </div>
                        <span class="bg-emerald-500/20 text-emerald-300 border border-emerald-400/40 text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-wider">
                            Corredor Binacional Activo
                        </span>
                    </div>
                    <h3 class="text-2xl font-black text-amberGold">La Frontera más Movida de Suramérica</h3>
                    <p class="text-xs sm:text-sm text-slate-200 leading-relaxed mt-2">
                        Contamos con la enorme ventaja estratégica de conectar con una de las <strong>fronteras más activas, comerciales y dinámicas de Suramérica</strong>: el eje fronterizo de <strong>San Antonio del Táchira y Ureña</strong> con el Departamento Norte de Santander de Colombia (<strong>Cúcuta y Villa del Rosario</strong>).
                    </p>
                    <ul class="space-y-2 mt-4 text-xs text-slate-300">
                        <li class="flex items-center gap-2"><i class="fa-solid fa-handshake text-emerald-400"></i> Intercambio binacional constante y acceso a más de 2 millones de consumidores directos.</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-boxes-packing text-emerald-400"></i> Flujo estratégico de bioinsumos agrícolas, herramientas y tecnología.</li>
                    </ul>
                </div>
                <div class="pt-4 border-t border-white/10 text-xs text-emerald-200 font-bold flex items-center justify-between">
                    <span class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-emerald-400"></i> Puentes Internacionales Simón Bolívar y Atanasio Girardot</span>
                    <span class="text-amberGold text-[11px]">Táchira ↔ N. Santander</span>
                </div>
            </div>

        </div>

    </div>
</section>

<!-- JAVASCRIPT DE MAPA Y MUNICIPIOS -->
<script>
    const MUNICIPIOS_DATA = [
        {
            id: 'jauregui',
            municipio: 'Jáuregui (La Grita)',
            zona: 'montana',
            parroquias: 'La Grita, Emilio Constantino Guerrero, Monseñor Miguel Antonio Salas',
            aldeas: ['Quebrada de San José (Sede)', 'Las Porqueras (Truchicultura)', 'Pattueca', 'Llano Grande', 'Sabana Grande', 'Guanare', 'Caricuena', 'La Pradera', 'El Batallón', 'El Pajarito'],
            rubros: ['Truchas Arcoíris (Las Porqueras)', 'Papa de Páramo', 'Zanahoria', 'Cebolla', 'Cebollín', 'Ajo Criollo', 'Tomate', 'Pimentón', 'Repollo Morado', 'Brócoli'],
            badge: 'Sede Principal EPSDC • Jáuregui',
            icon: 'fa-mountain-sun'
        },
        {
            id: 'uribante',
            municipio: 'Uribante (Pregonero)',
            zona: 'montana',
            parroquias: 'Pregonero, Cárdenas, Potosí, Juan Pablo Peñaloza',
            aldeas: ['Pregonero Centro', 'Laguna de García', 'Río Arriba', 'Los Naranjos', 'La Veguita', 'El Caney', 'Potosí'],
            rubros: ['Café de Pregonero (Café de Altura)', 'Papa', 'Cilantro', 'Caña Panelera', 'Plátano', 'Cambur'],
            badge: 'Café de Altura y Cuencas • Pregonero',
            icon: 'fa-mug-hot'
        },
        {
            id: 'san-jose-bolivar',
            municipio: 'Francisco de Miranda (San José de Bolívar)',
            zona: 'montana',
            parroquias: 'San José de Bolívar Capital',
            aldeas: ['Mesa de San José', 'Los Paujiles', 'Río Bobo', 'La Quebradita', 'Loma de San José', 'El Playón'],
            rubros: ['Caña Panelera y Trapiches', 'Café de Montaña', 'Queso y Leche de Páramo', 'Hortalizas', 'Mora Andina', 'Piscicultura'],
            badge: 'Valle del Río Bobo • San José de Bolívar',
            icon: 'fa-mountain-sun'
        },
        {
            id: 'sucre',
            municipio: 'Sucre (Queniquea)',
            zona: 'montana',
            parroquias: 'Queniquea, San Pablo, Eleazar López Contreras',
            aldeas: ['Queniquea Centro', 'San Pablo', 'El Molino', 'La Cieneguita', 'Pueblo Arriba'],
            rubros: ['Papa de Páramo', 'Apio Criollo', 'Mora Andina', 'Flores', 'Ajo Criollo', 'Hortalizas Verdes'],
            badge: 'Alta Serranía • Queniquea',
            icon: 'fa-tree'
        },
        {
            id: 'san-judas',
            municipio: 'San Judas Tadeo (Humuquena / Umuquena)',
            zona: 'andino',
            parroquias: 'Humuquena Capital',
            aldeas: ['Humuquena Centro', 'Trapiches Paneleros', 'La Vega', 'El Boquerón', 'Mata de Café'],
            rubros: ['Panela de Humuquena (Caña Panelera)', 'Cacao Criollo', 'Café', 'Cambur Manzano', 'Yuca', 'Cítricos'],
            badge: 'Cuna Panelera Tradicional • Humuquena',
            icon: 'fa-cubes-stacked'
        },
        {
            id: 'seboruco',
            municipio: 'Seboruco',
            zona: 'andino',
            parroquias: 'Seboruco Capital',
            aldeas: ['Santa Rosa', 'Calichal', 'El Hatico', 'San Pedro', 'La Quebrada'],
            rubros: ['Tomate Perita', 'Pimentón', 'Cebolla', 'Yuca Dulce', 'Calabacín', 'Parchita', 'Pepino'],
            badge: 'Valle Fértil • Seboruco',
            icon: 'fa-wheat-awn'
        },
        {
            id: 'vargas',
            municipio: 'José María Vargas (El Cobre)',
            zona: 'montana',
            parroquias: 'El Cobre',
            aldeas: ['El Zumbador', 'Mesa de Vargas', 'Playón', 'El Diamante'],
            rubros: ['Papa Granola', 'Zanahoria Dulce', 'Ajo Morado', 'Apio Criollo', 'Remolacha'],
            badge: 'Alta Montaña • El Cobre',
            icon: 'fa-snowflake'
        },
        {
            id: 'michelena',
            municipio: 'Michelena',
            zona: 'andino',
            parroquias: 'Michelena',
            aldeas: ['Vegones', 'La San Juana', 'Los Hornos', 'El Retiro'],
            rubros: ['Hortalizas', 'Tomate', 'Pimentón', 'Chayota', 'Lechosa'],
            badge: 'Microclima • Michelena',
            icon: 'fa-cloud-sun'
        },
        {
            id: 'lobatera',
            municipio: 'Lobatera',
            zona: 'andino',
            parroquias: 'Lobatera, Constitución',
            aldeas: ['Potrero de las Casas', 'Zaragoza', 'La Parada', 'Boca de Monte'],
            rubros: ['Tomate', 'Ají Dulce', 'Frijol', 'Cítricos', 'Maíz'],
            badge: 'Valle Central • Lobatera',
            icon: 'fa-sun'
        },
        {
            id: 'panamericano',
            municipio: 'Panamericano (Coloncito)',
            zona: 'piedemonte',
            parroquias: 'Coloncito, La Palmita',
            aldeas: ['Caño Grande', 'La Palmita', 'Bajo Panamericano', 'Pajitas'],
            rubros: ['Plátano Hartón', 'Yuca Amarga y Dulce', 'Lechosa', 'Guayaba', 'Queso Blanco Comunal'],
            badge: 'Piedemonte Fértil • Coloncito',
            icon: 'fa-cow'
        },
        {
            id: 'merida-pueblo-llano',
            municipio: 'Pueblo Llano & Rangel (Estado Mérida)',
            zona: 'andino',
            parroquias: 'Pueblo Llano, Mucuchíes, San Rafael de Mucuchíes',
            aldeas: ['Pueblo Llano', 'Chachopo', 'La Venta', 'Timotes Centro'],
            rubros: ['Papa de Páramo', 'Zanahoria', 'Brócoli', 'Coliflor', 'Lechuga', 'Cebolla'],
            badge: 'Eje Andino • Alta Montaña Mérida',
            icon: 'fa-mountain'
        },
        {
            id: 'trujillo-bocono',
            municipio: 'Boconó & Urdaneta (Estado Trujillo)',
            zona: 'andino',
            parroquias: 'Boconó, El Carmen, Mosquey, La Quebrada',
            aldeas: ['La Quebrada', 'Niquitao', 'Tostós', 'Las Mesitas', 'Carache'],
            rubros: ['Café Especial', 'Legumbres', 'Tomate', 'Pimentón', 'Yuca', 'Cambur', 'Fresa'],
            badge: 'Eje Andino • Trujillo',
            icon: 'fa-seedling'
        }
    ];

    window.addEventListener('DOMContentLoaded', () => {
        renderMunicipiosCards(MUNICIPIOS_DATA);
    });

    function switchMapView(view) {
        const google = document.getElementById('containerMapGoogle');
        const nac = document.getElementById('containerMapNacional');
        const zoom = document.getElementById('containerMapZoom');
        const btnGoogle = document.getElementById('btnMapGoogle');
        const btnNac = document.getElementById('btnMapNacional');
        const btnZoom = document.getElementById('btnMapZoom');

        if (google) google.classList.add('hidden');
        if (nac) nac.classList.add('hidden');
        if (zoom) zoom.classList.add('hidden');

        [btnGoogle, btnNac, btnZoom].forEach(btn => {
            if (btn) {
                btn.classList.remove('bg-agro-700', 'text-white');
                btn.classList.add('text-slate-400');
            }
        });

        if (view === 'zoom') {
            if (zoom) zoom.classList.remove('hidden');
            if (btnZoom) {
                btnZoom.classList.add('bg-agro-700', 'text-white');
                btnZoom.classList.remove('text-slate-400');
            }
        } else if (view === 'nacional') {
            if (nac) nac.classList.remove('hidden');
            if (btnNac) {
                btnNac.classList.add('bg-agro-700', 'text-white');
                btnNac.classList.remove('text-slate-400');
            }
        } else {
            if (google) google.classList.remove('hidden');
            if (btnGoogle) {
                btnGoogle.classList.add('bg-agro-700', 'text-white');
                btnGoogle.classList.remove('text-slate-400');
            }
        }
    }

    function renderMunicipiosCards(list) {
        const container = document.getElementById('municipiosCardsContainer');
        if (!container) return;
        container.innerHTML = '';

        if (list.length === 0) {
            container.innerHTML = '<div class="col-span-full text-center py-12 text-slate-400 font-bold bg-white rounded-3xl p-8 border border-slate-200">No se encontraron municipios o aldeas coincidentes.</div>';
            return;
        }

        list.forEach(m => {
            const aldeasTags = m.aldeas.map(a => `<span class="bg-amber-50 text-amber-900 border border-amber-200/70 text-[10px] font-bold px-2.5 py-0.5 rounded-lg shadow-2xs">${a}</span>`).join(' ');
            const rubrosTags = m.rubros.map(r => `<span class="bg-agro-50 text-agro-800 border border-agro-200/70 text-[10px] font-semibold px-2.5 py-0.5 rounded-lg shadow-2xs"><i class="fa-solid fa-leaf text-[8px] text-agro-600 mr-1"></i>${r}</span>`).join(' ');

            const card = `
                <div class="bg-white border border-slate-200/80 hover:border-agro-500/60 rounded-3xl p-6 sm:p-7 shadow-xs hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 flex flex-col justify-between h-full group">
                    <div>
                        <div class="flex justify-between items-center gap-2 mb-4">
                            <span class="bg-slate-50 text-slate-700 border border-slate-200/80 text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-wider shadow-2xs">${m.badge}</span>
                            <span class="text-slate-400 text-xs"><i class="fa-solid fa-map-pin text-amberGold"></i></span>
                        </div>

                        <div class="flex items-center gap-3.5 mb-5">
                            <div class="w-12 h-12 rounded-2xl bg-agro-50 border border-agro-100/80 text-agro-700 flex items-center justify-center text-lg font-bold group-hover:scale-110 group-hover:bg-agro-700 group-hover:text-white transition shrink-0 shadow-2xs">
                                <i class="fa-solid ${m.icon}"></i>
                            </div>
                            <div>
                                <h3 class="text-lg sm:text-xl font-black text-slate-900 group-hover:text-agro-800 transition leading-snug">
                                    ${m.municipio}
                                </h3>
                            </div>
                        </div>

                        <div class="space-y-3.5 my-4">
                            <div class="bg-slate-50/70 p-3 rounded-2xl border border-slate-100">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-0.5"><i class="fa-solid fa-layer-group text-amberGold mr-1.5"></i>Parroquias:</span>
                                <p class="text-xs text-slate-700 font-medium leading-relaxed">${m.parroquias}</p>
                            </div>

                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5"><i class="fa-solid fa-house-chimney-window text-amberGold mr-1.5"></i>Aldeas Productoras:</span>
                                <div class="flex flex-wrap gap-1.5">
                                    ${aldeasTags}
                                </div>
                            </div>

                            <div class="pt-3 border-t border-slate-100">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5"><i class="fa-solid fa-basket-shopping text-agro-600 mr-1.5"></i>Rubros Cosechados:</span>
                                <div class="flex flex-wrap gap-1.5">
                                    ${rubrosTags}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 mt-3">
                        <a href="<?= e(url('#productores')) ?>" class="w-full bg-slate-50 hover:bg-agro-700 hover:text-white hover:border-agro-700 border border-slate-200/80 text-slate-700 font-bold py-2.5 rounded-xl transition text-center text-xs flex items-center justify-center gap-2 shadow-2xs">
                            <i class="fa-solid fa-handshake-angle text-sm"></i> Contactar Productores
                        </a>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', card);
        });
    }

    function filterMunicipiosData() {
        const q = document.getElementById('municipioSearchInput').value.toLowerCase().trim();
        const filtered = MUNICIPIOS_DATA.filter(m => {
            const matchMun = m.municipio.toLowerCase().includes(q);
            const matchParroquia = m.parroquias.toLowerCase().includes(q);
            const matchAldea = m.aldeas.some(a => a.toLowerCase().includes(q));
            const matchRubro = m.rubros.some(r => r.toLowerCase().includes(q));
            return matchMun || matchParroquia || matchAldea || matchRubro;
        });
        renderMunicipiosCards(filtered);
    }

    function filterMunicipiosByZone(zone) {
        document.querySelectorAll('.mun-filter-btn').forEach(b => {
            b.classList.remove('active', 'bg-agro-700', 'text-white');
            b.classList.add('bg-slate-100', 'text-slate-700');
        });
        if (window.event && window.event.target && window.event.target.classList) {
            window.event.target.classList.add('active', 'bg-agro-700', 'text-white');
            window.event.target.classList.remove('bg-slate-100', 'text-slate-700');
        }

        if (zone === 'todos') {
            renderMunicipiosCards(MUNICIPIOS_DATA);
        } else if (zone === 'andino') {
            const filtered = MUNICIPIOS_DATA.filter(m => m.zona === 'andino');
            renderMunicipiosCards(filtered);
        } else {
            const filtered = MUNICIPIOS_DATA.filter(m => m.zona === zone);
            renderMunicipiosCards(filtered);
        }
    }

    function filterByState(state) {
        document.querySelectorAll('.mun-filter-btn').forEach(b => {
            b.classList.remove('active', 'bg-agro-700', 'text-white');
            b.classList.add('bg-slate-100', 'text-slate-700');
        });
        const searchInput = document.getElementById('municipioSearchInput');
        if (state === 'tachira') {
            if (searchInput) searchInput.value = 'Táchira';
            const filtered = MUNICIPIOS_DATA.filter(m => m.id !== 'merida-pueblo-llano' && m.id !== 'trujillo-bocono');
            renderMunicipiosCards(filtered);
        } else if (state === 'merida') {
            if (searchInput) searchInput.value = 'Mérida';
            const filtered = MUNICIPIOS_DATA.filter(m => m.municipio.includes('Mérida') || m.badge.includes('Mérida'));
            renderMunicipiosCards(filtered);
        } else if (state === 'trujillo') {
            if (searchInput) searchInput.value = 'Trujillo';
            const filtered = MUNICIPIOS_DATA.filter(m => m.municipio.includes('Trujillo') || m.badge.includes('Trujillo'));
            renderMunicipiosCards(filtered);
        }
        const target = document.getElementById('municipios');
        if (target) {
            target.scrollIntoView({ behavior: 'smooth' });
        }
    }
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
