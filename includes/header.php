<?php
// includes/header.php - Topbar y Encabezado Común
if (!isset($pageTitle)) {
    $pageTitle = 'Soberanía Agroalimentaria y Red Comunal';
}
$org = config_get('nombre_organizacion', 'La Nueva Esperanza');
$lema = config_get('lema', 'Caracterización Territorial y Soberanía Agroalimentaria');
$tel = config_get('telefono', '+58 412 1234567');
$email = config_get('email_contacto', 'contacto@lanuevaesperanza.org');
$dir = config_get('direccion', 'Estado Táchira, Andes Venezolanos');
$logo = config_get('logo');
$currentPath = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | EPSDC <?= e($org) ?></title>
    <meta name="description" content="Empresa de Propiedad Social Directa Comunal <?= e($org) ?>. Producción agrícola andina los 365 días del año en La Grita, Táchira, Venezuela.">
    <!-- Google Fonts & Tailwind -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@600;700&family=Playfair+Display:ital,wght@0,600;0,800;0,900;1,400;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="<?= e(url('assets/js/dialogs.js')) ?>?v=<?= filemtime(__DIR__ . '/../assets/js/dialogs.js') ?>"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        agro: {
                            50: '#f4f9f4',
                            100: '#e5f2e6',
                            200: '#cae4cd',
                            500: '#2d6a4f',
                            600: '#22543d',
                            700: '#1b4332',
                            800: '#133527',
                            900: '#0d231a',
                            950: '#071510'
                        },
                        amberGold: '#d97706',
                        terracota: '#c86d3b',
                        tierra: '#78350f',
                        soberano: '#0f172a',
                        crema: '#faf8f5',
                        pergamino: '#f4efea'
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        serif: ['"Playfair Display"', 'Georgia', 'serif'],
                        hand: ['"Caveat"', 'cursive'],
                    }
                }
            }
        }
    </script>
    <style>
        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }
        html { scroll-behavior: smooth; }
        .glass-header {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(10px);
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        @keyframes marquee {
            0% { transform: translateX(0%); }
            100% { transform: translateX(-50%); }
        }
        .animate-marquee {
            animation: marquee 35s linear infinite;
        }
        .animate-marquee:hover {
            animation-play-state: paused;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased selection:bg-agro-500 selection:text-white overflow-x-hidden min-h-screen flex flex-col justify-between">

    <div>
        <!-- Top Info Bar (Sobrio, editorial y elegante) -->
        <div class="bg-slate-900 text-slate-300 text-xs py-2 px-4 border-b border-slate-800 w-full overflow-hidden">
            <div class="max-w-7xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-2">
                <div class="flex items-center gap-2 flex-wrap justify-center sm:justify-start text-center sm:text-left">
                    <span class="inline-flex items-center gap-1.5 bg-amber-500/10 text-amber-400 border border-amber-500/20 font-bold px-2.5 py-0.5 rounded-full text-[10px] uppercase tracking-wider shrink-0">
                        <i class="fa-solid fa-location-dot text-[9px]"></i> Eje Andino • La Grita, Táchira
                    </span>
                    <span class="text-slate-400 text-xs">
                        EPSDC <strong class="text-slate-100 font-semibold">"<?= e($org) ?>"</strong> <span class="hidden md:inline text-slate-500">• Producción 365 Días al Año</span>
                    </span>
                </div>
                <div class="flex items-center gap-3 text-xs font-medium shrink-0">
                    <span class="inline-flex items-center gap-2 text-slate-300">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                        <i class="fa-solid fa-droplet text-sky-400 text-[11px]"></i> Riego Continuo 365 Días
                    </span>
                    <div class="h-3 w-[1px] bg-slate-700 hidden sm:block"></div>
                    <a href="<?= e(url('admin/login.php')) ?>" class="inline-flex items-center gap-1 text-slate-300 hover:text-white transition-colors font-medium px-2 py-0.5 rounded-lg hover:bg-slate-800">
                        <i class="fa-solid fa-shield-halved text-[11px] text-amber-400"></i> Admin
                    </a>
                </div>
            </div>
        </div>

        <!-- Navigation Header Principal (Editorial, Moderno y Equilibrado) -->
        <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-[0_2px_12px_-2px_rgba(0,0,0,0.04)] transition-all duration-300 w-full overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-20 gap-4">
                    
                    <!-- Logo & Brand -->
                    <a href="<?= e(url('index.php')) ?>" class="group flex items-center gap-3 shrink-0 transition-opacity hover:opacity-90">
                        <?php if ($logo): ?>
                            <img src="<?= e(url($logo)) ?>" alt="<?= e($org) ?>" class="w-11 h-11 rounded-xl object-contain bg-white shadow-xs p-1 border border-slate-200 shrink-0">
                        <?php else: ?>
                            <div class="w-11 h-11 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-center text-emerald-400 shadow-xs shrink-0">
                                <i class="fa-solid fa-mountain-sun text-lg"></i>
                            </div>
                        <?php endif; ?>
                        <div class="shrink-0">
                            <div class="font-extrabold text-slate-900 text-base sm:text-lg leading-tight uppercase whitespace-nowrap tracking-tight">
                                <?= e($org) ?>
                            </div>
                            <div class="text-[10px] font-bold text-emerald-700 tracking-wider uppercase whitespace-nowrap flex items-center gap-1.5 mt-0.5">
                                <span>Soberanía Alimentaria</span>
                                <span class="inline-block w-1 h-1 rounded-full bg-slate-300"></span>
                                <span class="text-slate-500 font-medium">Táchira</span>
                            </div>
                        </div>
                    </a>

                    <!-- Desktop Navigation Links (Navegación organizada en categorías esenciales) -->
                    <nav class="hidden lg:flex items-center gap-1 xl:gap-2 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        <a href="<?= e(url('index.php#combos')) ?>" class="px-3.5 py-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all whitespace-nowrap flex items-center gap-1.5">
                            <i class="fa-solid fa-bag-shopping text-emerald-600"></i> Combos
                        </a>
                        <a href="<?= e(url('index.php#entregas')) ?>" class="px-3.5 py-2 rounded-xl text-slate-700 hover:text-slate-900 hover:bg-slate-50 transition-all font-semibold flex items-center gap-1.5 whitespace-nowrap">
                            <i class="fa-solid fa-truck-fast text-amber-600"></i> Entregas
                        </a>
                        <a href="<?= e(url('rubros.php')) ?>" class="px-3.5 py-2 rounded-xl transition-all flex items-center gap-1.5 whitespace-nowrap <?= $currentPath === 'rubros.php' ? 'text-slate-900 bg-slate-100 font-bold' : 'hover:text-slate-900 hover:bg-slate-50' ?>">
                            <i class="fa-solid fa-carrot text-emerald-600"></i>
                            <span>39 Rubros</span>
                            <span class="bg-slate-200/80 text-slate-700 text-[9px] font-bold px-1.5 py-0.5 rounded-full">365D</span>
                        </a>
                        <a href="<?= e(url('territorio.php')) ?>" class="px-3.5 py-2 rounded-xl transition-all flex items-center gap-1.5 whitespace-nowrap <?= $currentPath === 'territorio.php' ? 'text-slate-900 bg-slate-100 font-bold' : 'hover:text-slate-900 hover:bg-slate-50' ?>">
                            <i class="fa-solid fa-map-location-dot text-slate-500"></i> Territorio & Mapa
                        </a>
                        <a href="<?= e(url('estatutos.php')) ?>" class="px-3.5 py-2 rounded-xl transition-all flex items-center gap-1.5 whitespace-nowrap <?= $currentPath === 'estatutos.php' ? 'text-slate-900 bg-slate-100 font-bold' : 'hover:text-slate-900 hover:bg-slate-50' ?>">
                            <i class="fa-solid fa-landmark text-slate-500"></i> Marco Comunal
                        </a>
                    </nav>

                    <!-- Desktop Actions -->
                    <div class="hidden sm:flex items-center gap-2.5 shrink-0">
                        <a href="<?= e(url('index.php#productores')) ?>" class="bg-emerald-800 hover:bg-emerald-900 text-white font-bold px-4 py-2 rounded-xl shadow-xs transition-all flex items-center gap-1.5 text-xs uppercase tracking-wider active:scale-95 whitespace-nowrap">
                            <i class="fa-solid fa-handshake-angle text-emerald-300"></i> Portal UPF
                        </a>
                    </div>

                    <!-- Mobile Menu Toggle Button -->
                    <div class="flex lg:hidden items-center gap-2 shrink-0">
                        <button id="mobileMenuBtn" onclick="toggleMobileMenu()" aria-label="Abrir menú" class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition focus:outline-none">
                            <i class="fa-solid fa-bars text-lg"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Navigation Menu -->
            <div id="mobileMenu" class="hidden lg:hidden bg-white/95 backdrop-blur-md border-b border-slate-200 px-5 pt-3 pb-6 space-y-2.5 shadow-lg">
                <a href="<?= e(url('index.php')) ?>" onclick="toggleMobileMenu()" class="flex items-center gap-3 font-bold text-slate-700 hover:text-agro-600 p-2.5 rounded-xl hover:bg-slate-50 <?= $currentPath === 'index.php' ? 'bg-agro-50 text-agro-800' : '' ?>">
                    <i class="fa-solid fa-house w-5 text-agro-600"></i> Inicio
                </a>
                <a href="<?= e(url('index.php#combos')) ?>" onclick="toggleMobileMenu()" class="flex items-center gap-3 font-bold text-slate-700 hover:text-agro-600 p-2.5 rounded-xl hover:bg-slate-50">
                    <i class="fa-solid fa-bag-shopping w-5 text-agro-600"></i> Bolsas y Combos Agrícolas
                </a>
                <a href="<?= e(url('index.php#entregas')) ?>" onclick="toggleMobileMenu()" class="flex items-center gap-3 font-extrabold text-amber-700 hover:text-amber-800 p-2.5 rounded-xl bg-amber-50/50 hover:bg-amber-50">
                    <i class="fa-solid fa-truck-fast w-5 text-amber-600"></i> Entregas de la Semana
                </a>
                <a href="<?= e(url('rubros.php')) ?>" onclick="toggleMobileMenu()" class="flex items-center gap-3 font-bold text-slate-700 hover:text-agro-600 p-2.5 rounded-xl hover:bg-slate-50 <?= $currentPath === 'rubros.php' ? 'bg-agro-50 text-agro-800' : '' ?>">
                    <i class="fa-solid fa-carrot w-5 text-orange-500"></i> Catálogo Completo (39 Rubros)
                </a>
                <a href="<?= e(url('territorio.php')) ?>" onclick="toggleMobileMenu()" class="flex items-center gap-3 font-bold text-slate-700 hover:text-agro-600 p-2.5 rounded-xl hover:bg-slate-50 <?= $currentPath === 'territorio.php' ? 'bg-agro-50 text-agro-800' : '' ?>">
                    <i class="fa-solid fa-map-location-dot w-5 text-cyan-600"></i> Territorio, Cuencas & Google Maps
                </a>
                <a href="<?= e(url('estatutos.php')) ?>" onclick="toggleMobileMenu()" class="flex items-center gap-3 font-bold text-slate-700 hover:text-agro-600 p-2.5 rounded-xl hover:bg-slate-50 <?= $currentPath === 'estatutos.php' ? 'bg-agro-50 text-agro-800' : '' ?>">
                    <i class="fa-solid fa-landmark w-5 text-amber-600"></i> Marco Comunal & Fondos 70/20/10
                </a>
                <a href="<?= e(url('index.php#productores')) ?>" onclick="toggleMobileMenu()" class="flex items-center gap-3 font-extrabold text-emerald-700 hover:text-emerald-800 p-2.5 rounded-xl bg-emerald-50/50 hover:bg-emerald-50">
                    <i class="fa-solid fa-handshake-angle w-5 text-emerald-600"></i> Portal de Productores y UPF
                </a>
                <div class="pt-3 flex flex-col sm:flex-row gap-2 border-t border-slate-100">
                    <a href="<?= e(url('index.php#combos')) ?>" onclick="toggleMobileMenu()" class="flex-1 text-center bg-agro-600 hover:bg-agro-700 text-white font-bold py-2.5 rounded-xl text-xs uppercase tracking-wider shadow-sm">Ver Combos y Precios</a>
                    <a href="<?= e(url('admin/login.php')) ?>" class="flex-1 text-center border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold py-2.5 rounded-xl text-xs">Panel Admin</a>
                </div>
            </div>
        </header>

        <!-- Mensajes Flash / Notificaciones -->
        <?php foreach (flashes() as $f): ?>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-xl"></i>
                        <span class="text-sm font-semibold"><?= e($f['msg']) ?></span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 text-lg font-bold">&times;</button>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (!empty($errorProductor)): ?>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-exclamation text-red-600 text-xl"></i>
                        <span class="text-sm font-semibold"><?= e($errorProductor) ?></span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 text-lg font-bold">&times;</button>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <!-- Main Content Area Starts -->
    <main class="flex-1">
