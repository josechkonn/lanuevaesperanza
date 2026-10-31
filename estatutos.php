<?php
// estatutos.php - Marco Comunal, Fondos Estatutarios y Acta Constitutiva
require __DIR__ . '/includes/app.php';

$pdo = db();
$stmtEst = $pdo->query("SELECT * FROM estatutos WHERE publicado = 1 ORDER BY orden ASC");
$estatutosDB = $stmtEst->fetchAll();

$pageTitle = 'Marco Comunal, Fondos Estatutarios y Acta Constitutiva';
require __DIR__ . '/includes/header.php';
?>

<!-- BANNER HERO DE PÁGINA: MARCO COMUNAL (Diseño Editorial y Poder Popular) -->
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
                    <i class="fa-solid fa-landmark text-amber-400"></i> Institucionalidad del Poder Popular • Ley Orgánica del Sistema Económico Comunal
                </div>
                <h1 class="text-3xl sm:text-5xl font-serif font-extrabold text-white tracking-tight">
                    Marco Comunal y Fondos Estatutarios
                </h1>
                <p class="text-emerald-100/80 text-xs sm:text-sm md:text-base mt-3 leading-relaxed font-normal">
                    Estructura jurídica, estatutaria y de administración de la <strong>Empresa de Propiedad Social Directa Comunal (EPSDC) La Nueva Esperanza</strong>. Distribución reglamentada de excedentes y articulación directa con las Unidades de Producción Familiar (UPF) y los Consejos Comunales.
                </p>
            </div>
            <div class="flex flex-col sm:flex-row md:flex-col gap-2 shrink-0">
                <a href="#fondos-sociales" class="bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold px-5 py-3 rounded-xl text-xs uppercase tracking-wider transition text-center shadow-xs active:scale-95">
                    <i class="fa-solid fa-coins mr-1.5"></i> Fondos 70% / 20% / 10%
                </a>
                <a href="#estatutos-asamblea" class="bg-white/5 hover:bg-white/10 text-white font-medium px-5 py-3 rounded-xl text-xs uppercase tracking-wider transition text-center border border-white/15 active:scale-95">
                    <i class="fa-solid fa-file-contract mr-1.5"></i> Ver Estatutos
                </a>
            </div>
        </div>
    </div>
</div>

<!-- SECCIÓN 1: FONDOS ESTATUTARIOS Y DISTRIBUCIÓN DE EXCEDENTES -->
<section id="fondos-sociales" class="py-20 bg-[#faf8f5] border-b border-stone-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="grid lg:grid-cols-12 gap-12 items-center">
            
            <div class="lg:col-span-7 space-y-6">
                <div>
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-emerald-900 bg-emerald-100/80 px-3.5 py-1 rounded-full border border-emerald-300/80 mb-3">
                        <i class="fa-solid fa-scale-balanced text-emerald-700"></i> Distribución Justa de Excedentes
                    </span>
                    <h2 class="text-3xl sm:text-5xl font-serif font-extrabold text-slate-900 tracking-tight">
                        Fondos Estatutarios Reglamentados
                    </h2>
                    <p class="text-slate-600 text-xs sm:text-sm mt-2 leading-relaxed">
                        Conforme a la Ley Orgánica del Sistema Económico Comunal, los excedentes generados por la EPSDC no se reparten de forma mercantil, sino que se reinvierten en tres fondos de beneficio colectivo:
                    </p>
                </div>

                <div class="space-y-4">
                    <!-- Fondo 1: 70% -->
                    <div class="bg-white border border-stone-200/90 hover:border-emerald-700/60 p-6 rounded-3xl shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex items-center gap-5 group">
                        <div class="w-20 h-20 rounded-2xl bg-[#133527] text-white font-serif font-extrabold text-3xl flex items-center justify-center shrink-0 shadow-sm group-hover:scale-105 transition-transform">
                            70%
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider block">Sostenibilidad Agrícola</span>
                            <h4 class="font-serif font-extrabold text-slate-900 text-lg group-hover:text-emerald-900 transition-colors">Fondo de Mantenimiento Productivo</h4>
                            <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                                Adquisición de insumos ecológicos, semillas certificadas, compost, biofertilizantes, mantenimiento del parque automotor de carga y garantía de la continuidad de siembras en las UPF.
                            </p>
                        </div>
                    </div>

                    <!-- Fondo 2: 20% -->
                    <div class="bg-white border border-stone-200/90 hover:border-amber-600/60 p-6 rounded-3xl shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex items-center gap-5 group">
                        <div class="w-20 h-20 rounded-2xl bg-amber-400 text-slate-950 font-serif font-extrabold text-3xl flex items-center justify-center shrink-0 shadow-sm group-hover:scale-105 transition-transform">
                            20%
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-amber-800 uppercase tracking-wider block">Seguridad Social Campesina</span>
                            <h4 class="font-serif font-extrabold text-slate-900 text-lg group-hover:text-amber-900 transition-colors">Fondo de Atención a los Productores</h4>
                            <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                                Asistencia y auxilio médico para las familias campesinas, botiquín comunal, subsidio solidario por contingencias o pérdidas de cosecha derivadas de lluvias o heladas.
                            </p>
                        </div>
                    </div>

                    <!-- Fondo 3: 10% -->
                    <div class="bg-white border border-stone-200/90 hover:border-emerald-700/60 p-6 rounded-3xl shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex items-center gap-5 group">
                        <div class="w-20 h-20 rounded-2xl bg-emerald-800 text-white font-serif font-extrabold text-3xl flex items-center justify-center shrink-0 shadow-sm group-hover:scale-105 transition-transform">
                            10%
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider block">Poder Popular y Territorio</span>
                            <h4 class="font-serif font-extrabold text-slate-900 text-lg group-hover:text-emerald-900 transition-colors">Fondo Comunitario de Reinversión Social</h4>
                            <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                                Transferido a las asambleas de ciudadanos y consejos comunales para la reparación de vías agrícolas de penetración, acueductos rurales, escuelas y proyectos comunitarios.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Tarjeta de Compromiso Comunal -->
            <div class="lg:col-span-5 bg-[#0a1c14] text-white rounded-3xl p-8 lg:p-10 shadow-2xl border border-emerald-800/60 relative overflow-hidden">
                <!-- Curvas topográficas andinas -->
                <div class="absolute inset-0 opacity-[0.05] pointer-events-none overflow-hidden flex items-center justify-center">
                    <svg class="w-[140%] h-[140%]" viewBox="0 0 1200 800" fill="none" stroke="currentColor">
                        <path d="M-100,200 C200,100 400,350 700,200 C1000,50 1100,280 1300,180" stroke-width="1.5" />
                        <path d="M-100,400 C180,260 480,500 800,360 C1100,220 1200,450 1300,360" stroke-width="1.5" />
                    </svg>
                </div>

                <div class="relative z-10">
                    <div class="w-14 h-14 bg-amber-400/10 text-amber-300 rounded-2xl flex items-center justify-center text-2xl mb-4 font-black border border-amber-400/30">
                        <i class="fa-solid fa-handshake-angle"></i>
                    </div>
                    <h3 class="font-serif text-2xl sm:text-3xl font-extrabold text-amber-300 mb-2">Concatenación con las UPF</h3>
                    <p class="text-xs text-emerald-100/70 leading-relaxed mb-6 font-normal">
                        La EPSDC no compra para revender con fines de lucro individual; articula a los pequeños agricultores y unidades productivas familiares para acopiar, estandarizar y distribuir alimentos a precios justos directos.
                    </p>

                    <ul class="space-y-3.5 text-xs text-emerald-100/90">
                        <li class="flex items-center gap-2.5">
                            <i class="fa-solid fa-circle-check text-emerald-400 text-sm shrink-0"></i>
                            <span>Cero intermediación parasitaria o cobro de comisiones especulativas.</span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <i class="fa-solid fa-circle-check text-emerald-400 text-sm shrink-0"></i>
                            <span>Garantía de pesaje exacto y liquidación transparente.</span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <i class="fa-solid fa-circle-check text-emerald-400 text-sm shrink-0"></i>
                            <span>Acceso prioritario a bioinsumos y fondos de apoyo solidario.</span>
                        </li>
                    </ul>

                    <div class="pt-6 mt-6 border-t border-emerald-800/60">
                        <a href="<?= e(url('index.php#productores')) ?>" class="w-full bg-amber-400 hover:bg-amber-300 text-slate-950 font-bold py-3.5 rounded-xl transition text-center text-xs flex items-center justify-center gap-2 uppercase tracking-wider shadow-sm active:scale-95">
                            <i class="fa-solid fa-file-signature"></i> Formulario de Registro UPF
                        </a>
                    </div>
                </div>
            </div>

        </div>

    </div>
</section>

<!-- SECCIÓN 2: ESTATUTOS Y ACTA CONSTITUTIVA -->
<section id="estatutos-asamblea" class="py-20 bg-white border-t border-stone-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto mb-16">
            <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-emerald-900 bg-emerald-100/80 px-3.5 py-1 rounded-full border border-emerald-300/80 mb-3">
                <i class="fa-solid fa-scale-balanced text-emerald-700"></i> Marco Jurídico e Institucional
            </span>
            <h2 class="text-3xl sm:text-5xl font-serif font-extrabold text-slate-900 tracking-tight">Estatutos y Normativas Comunitarias</h2>
            <div class="flex items-center justify-center gap-2 mt-2">
                <p class="text-slate-600 text-xs sm:text-sm mt-2 max-w-2xl leading-relaxed">
                    Transparencia y legalidad en cada uno de los procesos de la EPSDC. Directrices aprobadas por la asamblea comunal de productores.
                </p>
                <span class="font-hand text-lg text-emerald-700 font-bold hidden sm:inline -rotate-1">
                    • Poder Popular
                </span>
            </div>
        </div>

        <div class="max-w-4xl mx-auto space-y-4">
            <?php if (!$estatutosDB): ?>
                <div class="bg-[#faf8f5] p-8 rounded-3xl text-center text-slate-400 font-bold border border-stone-200">
                    No hay estatutos publicados actualmente en la base de datos.
                </div>
            <?php endif; ?>

            <?php foreach ($estatutosDB as $idx => $est): ?>
                <div class="bg-white border border-stone-200/90 rounded-2xl overflow-hidden shadow-xs hover:border-emerald-700/60 transition">
                    <button onclick="toggleEstatuto('estatuto_<?= $est['id'] ?>')" class="w-full p-5 text-left flex justify-between items-center hover:bg-[#faf8f5] transition">
                        <div class="flex items-center gap-3.5">
                            <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-900 font-serif font-bold text-xs flex items-center justify-center shrink-0 border border-emerald-200">
                                <?= (int)$est['orden'] ?>
                            </span>
                            <h4 class="font-serif font-extrabold text-slate-900 text-base"><?= e($est['titulo']) ?></h4>
                        </div>
                        <i id="icon_estatuto_<?= $est['id'] ?>" class="fa-solid fa-chevron-down text-slate-400 text-xs transition-transform duration-300"></i>
                    </button>
                    <div id="estatuto_<?= $est['id'] ?>" class="<?= $idx === 0 ? '' : 'hidden' ?> p-5 pt-0 border-t border-stone-100 text-xs sm:text-sm text-slate-600 leading-relaxed bg-[#faf8f5]/50">
                        <?php if ($est['contenido']): ?>
                            <div class="py-3 whitespace-pre-line"><?= e($est['contenido']) ?></div>
                        <?php endif; ?>
                        <?php if ($est['archivo_pdf']): ?>
                            <div class="pt-3 border-t border-stone-200">
                                <a href="<?= e(url($est['archivo_pdf'])) ?>" target="_blank" class="inline-flex items-center gap-2 text-rose-700 hover:text-rose-800 font-bold text-xs bg-rose-50 border border-rose-200 px-3 py-1.5 rounded-lg">
                                    <i class="fa-solid fa-file-pdf"></i> Ver / Descargar Documento Oficial en PDF
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>

    </div>
</section>

<script>
    function toggleEstatuto(id) {
        const el = document.getElementById(id);
        const icon = document.getElementById('icon_' + id);
        if (!el) return;
        el.classList.toggle('hidden');
        if (icon) {
            icon.classList.toggle('rotate-180');
        }
    }
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
