<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistem Manajemen Perjalanan & Rekapitulasi Biaya') - ExpenseTrace</title>

    <!-- Local Fonts -->
    <link rel="stylesheet" href="{{ asset('vendor/fonts/fonts.css') }}">

    <!-- Local Tailwind CSS -->
    <script src="{{ asset('vendor/tailwindcss/tailwindcss.js') }}"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Outfit', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    }
                }
            }
        }
    </script>

    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    
    <!-- Flatpickr Range Calendar & Dark Theme -->
    <link rel="stylesheet" href="{{ asset('vendor/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/flatpickr/dark.css') }}">
    <script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('vendor/flatpickr/id.js') }}"></script>

    <script defer src="{{ asset('vendor/alpinejs/alpine.min.js') }}"></script>

    <style>
        [x-cloak] { display: none !important; }
        .glass-card {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glass-input {
            background: rgba(30, 41, 59, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #f8fafc;
        }
        .glass-input:focus {
            border-color: #38bdf8;
            outline: none;
            box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.25);
        }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }

        /* Bright & Highlighted Native Datepicker Icon */
        input[type="date"] {
            cursor: pointer;
        }
        input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(0.7) sepia(1) saturate(6) hue-rotate(175deg);
            cursor: pointer;
            border-radius: 4px;
            padding: 2px;
            transition: all 0.2s ease-in-out;
        }
        input[type="date"]::-webkit-calendar-picker-indicator:hover {
            filter: invert(1) brightness(1.2);
            transform: scale(1.2);
            background-color: rgba(56, 189, 248, 0.25);
        }

        /* Flatpickr Dark Theme Customization */
        .flatpickr-calendar.inline {
            background: #090d16 !important;
            border: 1px solid #1e293b !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.6) !important;
            border-radius: 0.75rem !important;
            margin: 0 auto !important;
        }
        .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange {
            background: #0284c7 !important;
            border-color: #0284c7 !important;
            color: #ffffff !important;
            font-weight: 700 !important;
        }
        .flatpickr-day.inRange {
            background: rgba(56, 189, 248, 0.18) !important;
            box-shadow: -5px 0 0 rgba(56, 189, 248, 0.18), 5px 0 0 rgba(56, 189, 248, 0.18) !important;
            color: #38bdf8 !important;
        }
        .flatpickr-day:hover {
            background: #1e293b !important;
        }

        /* ========================================================= */
        /* PRINT COLOR & LAYOUT ACCURACY PRESERVATION RULES           */
        /* ========================================================= */
        @media print {
            /* Force exact background colors, gradients, and text colors */
            *, *::before, *::after {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            @page {
                size: portrait;
                margin: 0.8cm;
            }

            body {
                background-color: #020617 !important;
                color: #f8fafc !important;
                margin: 0 !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Hide non-printable UI elements */
            header,
            footer,
            .no-print,
            .fixed.bottom-6,
            nav {
                display: none !important;
            }

            /* Container print adjustments */
            main {
                padding: 0 !important;
                margin: 0 !important;
            }

            .printable-card {
                box-shadow: none !important;
                border: 1px solid rgba(255, 255, 255, 0.15) !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 auto !important;
                position: relative !important;
            }

            /* If modal print is active, hide everything outside modal */
            .fixed.inset-0:has(.printable-card) ~ * {
                display: none !important;
            }
        }
    </style>
</head>
<body class="h-screen w-screen overflow-hidden font-sans antialiased bg-slate-950 text-slate-100 selection:bg-sky-500 selection:text-white">

    <!-- SPA Top Progress Indicator -->
    <div id="spa-progress-bar" class="fixed top-0 left-0 right-0 h-1 bg-gradient-to-r from-sky-400 via-emerald-400 to-indigo-500 z-50 transition-all duration-300 opacity-0 pointer-events-none" style="width: 0%;"></div>

    <div x-data="{ mobileSidebarOpen: false, isCollapsed: true }" class="h-screen w-screen flex flex-col md:flex-row overflow-hidden bg-slate-950">

        <!-- Mobile Header Bar -->
        <header class="md:hidden sticky top-0 z-40 bg-slate-900/95 backdrop-blur-md border-b border-slate-800 px-4 py-3 flex items-center justify-between no-print shrink-0">
            <div class="flex items-center space-x-3">
                <button @click="mobileSidebarOpen = !mobileSidebarOpen" type="button" class="p-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white focus:outline-none">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
                <a href="{{ route('tickets.index') }}" class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center text-white shadow-md">
                        <i class="fa-solid fa-ticket text-sm transform -rotate-12"></i>
                    </div>
                    <span class="font-display font-bold text-lg text-white tracking-tight">ExpenseTrace</span>
                </a>
            </div>

            @auth
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-sky-400 text-xs shadow-sm" title="{{ Auth::user()->name }}">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="inline" data-no-spa>
                        @csrf
                        <button type="submit" class="px-2.5 py-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 text-rose-400 hover:text-white flex items-center gap-1.5 text-xs font-medium transition-all shadow-sm" title="Keluar dari Aplikasi">
                            <i class="fa-solid fa-right-from-bracket text-xs"></i>
                            <span class="font-semibold">Keluar</span>
                        </button>
                    </form>
                </div>
            @endauth
        </header>

        <!-- Mobile Sidebar Backdrop Overlay -->
        <div x-show="mobileSidebarOpen" 
             @click="mobileSidebarOpen = false" 
             x-transition:enter="transition-opacity ease-linear duration-200" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="transition-opacity ease-linear duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-40 md:hidden no-print" 
             x-cloak>
        </div>

        <!-- Sidebar Navigation (Left Menu - Default Minimized) -->
        <aside :class="[
            mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0',
            isCollapsed ? 'md:w-20' : 'md:w-64'
        ]" class="fixed md:sticky top-0 inset-y-0 left-0 z-50 w-64 bg-slate-900/95 md:bg-slate-900 backdrop-blur-md border-r border-slate-800/80 flex flex-col justify-between h-full max-h-screen md:h-screen shrink-0 transition-all duration-300 ease-in-out no-print">
            
            <div class="flex-1 flex flex-col min-h-0 overflow-y-auto">
                <!-- Brand / Logo Header & Toggle Button -->
                <div class="p-4 border-b border-slate-800/80 flex items-center justify-between" :class="isCollapsed ? 'md:justify-center md:px-2' : ''">
                    <a href="{{ route('tickets.index') }}" class="flex items-center space-x-3 group" title="ExpenseTrace">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-sky-500/20 group-hover:scale-105 transition-transform duration-200 shrink-0">
                            <i class="fa-solid fa-receipt text-lg"></i>
                        </div>
                        <div :class="isCollapsed ? 'md:hidden' : ''" class="transition-opacity duration-200">
                            <span class="font-display font-bold text-lg tracking-tight bg-gradient-to-r from-white via-slate-200 to-slate-400 bg-clip-text text-transparent block leading-tight">
                                ExpenseTrace
                            </span>
                            <span class="text-[10px] block text-emerald-400 font-mono tracking-wider font-semibold uppercase">EXPENSE & TRAVEL LOG</span>
                        </div>
                    </a>

                    <!-- Toggle Sidebar Minimize/Expand (Desktop) -->
                    <button @click="isCollapsed = !isCollapsed" type="button" class="hidden md:flex p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors" :title="isCollapsed ? 'Perluas Sidebar Menu' : 'Minimalkan Sidebar Menu'">
                        <i class="fa-solid text-sm" :class="isCollapsed ? 'fa-angles-right' : 'fa-angles-left'"></i>
                    </button>

                    <!-- Close Mobile Drawer -->
                    <button @click="mobileSidebarOpen = false" class="md:hidden text-slate-400 hover:text-white p-1">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <!-- Navigation Links -->
                <div class="p-3">
                    <nav class="space-y-1">
                            <!-- Histori Tiket -->
                            <a href="{{ route('tickets.index') }}" 
                               :class="isCollapsed ? 'md:justify-center md:px-0' : ''"
                               class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('tickets.*') ? 'bg-sky-500/10 text-sky-300 border border-sky-500/30 font-semibold shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}"
                               :title="isCollapsed ? 'Histori Tiket' : ''">
                                <i class="fa-solid fa-ticket text-base {{ request()->routeIs('tickets.*') ? 'text-sky-400' : 'text-slate-400' }}"></i>
                                <span :class="isCollapsed ? 'md:hidden' : ''">Histori Tiket</span>
                            </a>

                            <!-- Histori Hotel -->
                            <a href="{{ route('hotels.index') }}" 
                               :class="isCollapsed ? 'md:justify-center md:px-0' : ''"
                               class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('hotels.*') ? 'bg-amber-500/10 text-amber-300 border border-amber-500/30 font-semibold shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}"
                               :title="isCollapsed ? 'Histori Hotel' : ''">
                                <i class="fa-solid fa-hotel text-base {{ request()->routeIs('hotels.*') ? 'text-amber-400' : 'text-slate-400' }}"></i>
                                <span :class="isCollapsed ? 'md:hidden' : ''">Histori Hotel</span>
                            </a>

                            <!-- Histori Biaya Lain-lain -->
                            <a href="{{ route('expenses.index') }}" 
                               :class="isCollapsed ? 'md:justify-center md:px-0' : ''"
                               class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('expenses.*') ? 'bg-emerald-500/10 text-emerald-300 border border-emerald-500/30 font-semibold shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}"
                               :title="isCollapsed ? 'Histori Biaya Lain-lain' : ''">
                                <i class="fa-solid fa-receipt text-base {{ request()->routeIs('expenses.*') ? 'text-emerald-400' : 'text-slate-400' }}"></i>
                                <span :class="isCollapsed ? 'md:hidden' : ''">Histori Biaya Lain-lain</span>
                            </a>

                            <!-- Kelola User (Admin Only) -->
                            @auth
                                @if(Auth::user()->isAdmin())
                                    <a href="{{ route('users.index') }}" 
                                       :class="isCollapsed ? 'md:justify-center md:px-0' : ''"
                                       class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('users.*') ? 'bg-purple-500/10 text-purple-300 border border-purple-500/30 font-semibold shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}"
                                       :title="isCollapsed ? 'Kelola User' : ''">
                                        <i class="fa-solid fa-users-gear text-base {{ request()->routeIs('users.*') ? 'text-purple-400' : 'text-slate-400' }}"></i>
                                        <span :class="isCollapsed ? 'md:hidden' : ''">Kelola User</span>
                                    </a>
                                @endif
                            @endauth
                        </nav>
                </div>
            </div>

            <!-- User Profile & Logout Bottom Bar -->
            @auth
                <div class="p-3 border-t border-slate-800/80 bg-slate-950/40 shrink-0 mt-auto">
                    <div class="p-2.5 rounded-2xl bg-slate-800/60 border border-slate-700/50 mb-2 flex items-center" :class="isCollapsed ? 'md:justify-center md:p-2' : 'space-x-3'">
                        <div class="w-9 h-9 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-sky-400 text-sm shrink-0" :title="Auth::user()->name">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div :class="isCollapsed ? 'md:hidden' : ''" class="min-w-0 flex-1">
                            <div class="text-xs font-semibold text-white truncate leading-tight">{{ Auth::user()->name }}</div>
                            <div class="text-[10px] font-mono text-sky-400 truncate mt-0.5 capitalize">
                                @if(Auth::user()->isAdmin())
                                    Admin
                                @elseif(Auth::user()->isFinance())
                                    Finance
                                @else
                                    User
                                @endif
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('logout') }}" method="POST" data-no-spa>
                        @csrf
                        <button type="submit" 
                                :class="isCollapsed ? 'md:justify-center md:px-0' : 'px-3.5'"
                                class="w-full py-2 rounded-xl text-xs font-medium text-rose-400 hover:text-white hover:bg-rose-500/20 border border-rose-500/30 flex items-center justify-center gap-2 transition-all" 
                                :title="isCollapsed ? 'Keluar dari Aplikasi' : ''">
                            <i class="fa-solid fa-right-from-bracket text-sm"></i>
                            <span :class="isCollapsed ? 'md:hidden' : ''">Keluar</span>
                        </button>
                    </form>
                </div>
            @endauth
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden">
            <main class="flex-1 p-3 sm:p-4 lg:p-5 overflow-y-auto flex flex-col min-h-0">
                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-3 p-3.5 rounded-xl bg-emerald-950/80 border border-emerald-500/40 text-emerald-200 flex items-center justify-between shadow-xl backdrop-blur-sm no-print shrink-0">
                        <div class="flex items-center space-x-3">
                            <div class="w-7 h-7 rounded-lg bg-emerald-500/20 flex items-center justify-center text-emerald-400">
                                <i class="fa-solid fa-circle-check text-sm"></i>
                            </div>
                            <span class="text-xs font-medium">{{ session('success') }}</span>
                        </div>
                        <button @click="show = false" class="text-emerald-400 hover:text-emerald-200">
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="mb-3 p-3.5 rounded-xl bg-rose-950/80 border border-rose-500/40 text-rose-200 flex items-center justify-between shadow-xl no-print shrink-0">
                        <div class="flex items-center space-x-3">
                            <div class="w-7 h-7 rounded-lg bg-rose-500/20 flex items-center justify-center text-rose-400">
                                <i class="fa-solid fa-triangle-exclamation text-sm"></i>
                            </div>
                            <span class="text-xs font-medium">{{ session('error') }}</span>
                        </div>
                        <button @click="show = false" class="text-rose-400 hover:text-rose-200">
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- html2pdf Library for 100% UI Accurate PDF Export -->
    <script src="{{ asset('vendor/html2pdf/html2pdf.bundle.min.js') }}"></script>
    <script>
        function downloadTicketPDF(elementId, filename = 'E-Ticket-Boarding-Pass.pdf') {
            const element = document.getElementById(elementId);
            if (!element) {
                alert('Elemen tiket tidak ditemukan.');
                return;
            }

            const nonPrintables = element.querySelectorAll('.no-print');
            nonPrintables.forEach(el => el.style.setProperty('display', 'none', 'important'));

            const opt = {
                margin:       [8, 8, 8, 8],
                filename:     filename,
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { 
                    scale: 2, 
                    useCORS: true, 
                    logging: false,
                    backgroundColor: '#0f172a'
                },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            html2pdf().set(opt).from(element).save().then(() => {
                nonPrintables.forEach(el => el.style.removeProperty('display'));
            }).catch(err => {
                nonPrintables.forEach(el => el.style.removeProperty('display'));
                console.error(err);
            });
        }
    </script>

    @stack('scripts')

    <!-- SPA Router Engine Script -->
    <script>
        (function() {
            function initSpaEngine() {
                if (window.location.pathname !== '/main/index' && window.location.pathname !== '/login' && window.location.pathname !== '/index') {
                    try {
                        window.history.replaceState(window.history.state, '', '/main/index');
                    } catch(e){}
                } else if (window.location.search) {
                    try {
                        window.history.replaceState(window.history.state, '', window.location.pathname);
                    } catch(e){}
                }
                const progressBar = document.getElementById('spa-progress-bar');
                let progressTimer = null;

                function startProgress() {
                    if (!progressBar) return;
                    progressBar.style.width = '25%';
                    progressBar.style.opacity = '1';
                    clearInterval(progressTimer);
                    progressTimer = setInterval(() => {
                        let cur = parseFloat(progressBar.style.width) || 25;
                        if (cur < 90) {
                            progressBar.style.width = (cur + Math.random() * 8) + '%';
                        }
                    }, 100);
                }

                function finishProgress() {
                    if (!progressBar) return;
                    clearInterval(progressTimer);
                    progressBar.style.width = '100%';
                    setTimeout(() => {
                        progressBar.style.opacity = '0';
                        setTimeout(() => { progressBar.style.width = '0%'; }, 250);
                    }, 150);
                }

                function updateActiveSidebarLinks(newPathname) {
                    const sidebarLinks = document.querySelectorAll('aside nav a');
                    const activeColorMap = {
                        expenses: { link: ['bg-emerald-500/10', 'text-emerald-300', 'border-emerald-500/30'], icon: 'text-emerald-400' },
                        hotels:   { link: ['bg-amber-500/10', 'text-amber-300', 'border-amber-500/30'],     icon: 'text-amber-400' },
                        users:    { link: ['bg-purple-500/10', 'text-purple-300', 'border-purple-500/30'],   icon: 'text-purple-400' },
                        tickets:  { link: ['bg-sky-500/10', 'text-sky-300', 'border-sky-500/30'],         icon: 'text-sky-400' }
                    };

                    sidebarLinks.forEach(link => {
                        const href = link.getAttribute('href');
                        if (!href) return;
                        try {
                            const url = new URL(href, window.location.origin);
                            const linkPath = url.pathname;
                            const icon = link.querySelector('i');
                            const isMatch = linkPath === newPathname || (linkPath !== '/' && newPathname.startsWith(linkPath));

                            link.classList.remove(
                                'bg-sky-500/10', 'text-sky-300', 'border-sky-500/30',
                                'bg-amber-500/10', 'text-amber-300', 'border-amber-500/30',
                                'bg-emerald-500/10', 'text-emerald-300', 'border-emerald-500/30',
                                'bg-purple-500/10', 'text-purple-300', 'border-purple-500/30',
                                'font-semibold', 'shadow-sm', 'text-slate-400', 'border-transparent'
                            );

                            if (icon) {
                                icon.classList.remove('text-sky-400', 'text-amber-400', 'text-emerald-400', 'text-purple-400', 'text-slate-400');
                            }

                            if (isMatch) {
                                link.classList.remove('hover:bg-slate-800/60');
                                link.classList.add('border', 'font-semibold', 'shadow-sm');

                                let theme = 'tickets';
                                if (linkPath.includes('expenses')) theme = 'expenses';
                                else if (linkPath.includes('hotels')) theme = 'hotels';
                                else if (linkPath.includes('users')) theme = 'users';

                                link.classList.add(...activeColorMap[theme].link);
                                if (icon) icon.classList.add(activeColorMap[theme].icon);
                            } else {
                                link.classList.add('text-slate-400', 'hover:text-white', 'hover:bg-slate-800/60', 'border', 'border-transparent');
                                if (icon) icon.classList.add('text-slate-400');
                            }
                        } catch(e) {}
                    });
                }

                async function loadSpaPage(url, options = {}) {
                    const { method = 'GET', body = null, pushHistory = true } = options;
                    const mainEl = document.querySelector('main');

                    if (!mainEl) {
                        window.location.href = url;
                        return;
                    }

                    startProgress();
                    mainEl.style.transition = 'opacity 0.15s ease';
                    mainEl.style.opacity = '0.3';

                    try {
                        const fetchOptions = {
                            method: method,
                            headers: {
                                'X-SPA-REQUEST': '1'
                            }
                        };

                        if (body) {
                            fetchOptions.body = body;
                        }

                        const response = await fetch(url, fetchOptions);

                        if (response.redirected && (response.url.includes('/login') || response.url.includes('/index'))) {
                            window.location.href = response.url;
                            return;
                        }

                        if (!response.ok) {
                            window.location.href = url;
                            return;
                        }

                        const html = await response.text();
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');

                        const newMain = doc.querySelector('main');
                        const newTitle = doc.querySelector('title');

                        if (newMain) {
                            mainEl.innerHTML = newMain.innerHTML;

                            if (newTitle) {
                                document.title = newTitle.innerText;
                            }

                            const targetUrl = response.url || url;
                            updateActiveSidebarLinks(new URL(targetUrl, window.location.origin).pathname);

                            // Re-execute inline scripts inside newly loaded content
                            const scripts = Array.from(mainEl.querySelectorAll('script'));
                            for (const oldScript of scripts) {
                                try {
                                    const newScript = document.createElement('script');
                                    Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                                    newScript.textContent = oldScript.textContent;
                                    oldScript.parentNode.replaceChild(newScript, oldScript);
                                } catch(e){}
                            }

                            // Re-initialize Alpine.js on the swapped main element
                            if (window.Alpine) {
                                delete mainEl._x_dataStack;
                                setTimeout(() => {
                                    try {
                                        if (typeof Alpine.initTree === 'function') {
                                            Alpine.initTree(mainEl);
                                        }
                                    } catch(e) {
                                        console.warn('Alpine re-init:', e);
                                    }
                                }, 20);
                            }

                            mainEl.scrollTo({ top: 0, behavior: 'instant' });
                            window.dispatchEvent(new CustomEvent('spa:loaded', { detail: { url: targetUrl } }));
                        } else {
                            window.location.href = url;
                        }
                    } catch (err) {
                        console.error('SPA Load Error, fallback to full navigate:', err);
                        window.location.href = url;
                    } finally {
                        if (mainEl) mainEl.style.opacity = '1';
                        finishProgress();
                    }
                }

                window.loadSpaPage = loadSpaPage;

                // Intercept internal link clicks
                document.addEventListener('click', function(e) {
                    const link = e.target.closest('a');
                    if (!link) return;

                    try { link.blur(); } catch(err){}

                    const href = link.getAttribute('href');
                    if (!href || href.startsWith('#') || href.startsWith('javascript:') || link.hasAttribute('data-no-spa') || link.getAttribute('target') === '_blank' || link.hasAttribute('download')) {
                        return;
                    }

                    if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;

                    try {
                        const targetUrl = new URL(href, window.location.origin);
                        if (targetUrl.origin !== window.location.origin) return;

                        e.preventDefault();
                        loadSpaPage(targetUrl.href);
                    } catch(err) {}
                });

                // Intercept form submissions
                document.addEventListener('submit', function(e) {
                    const form = e.target;
                    if (!form || form.hasAttribute('data-no-spa') || form.getAttribute('target') === '_blank') return;

                    const action = form.getAttribute('action') || window.location.href;
                    const method = (form.getAttribute('method') || 'GET').toUpperCase();

                    try {
                        const targetUrl = new URL(action, window.location.origin);
                        if (targetUrl.origin !== window.location.origin) return;

                        e.preventDefault();

                        const formData = new FormData(form);

                        if (method === 'GET') {
                            const params = new URLSearchParams(formData);
                            const fullUrl = targetUrl.pathname + (params.toString() ? '?' + params.toString() : '');
                            loadSpaPage(fullUrl);
                        } else {
                            loadSpaPage(targetUrl.href, {
                                method: method,
                                body: formData
                            });
                        }
                    } catch(err) {}
                });

                // Handle browser back/forward history buttons
                window.addEventListener('popstate', function(e) {
                    loadSpaPage(window.location.href, { pushHistory: false });
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initSpaEngine);
            } else {
                initSpaEngine();
            }
        })();
    </script>
</body>
</html>
