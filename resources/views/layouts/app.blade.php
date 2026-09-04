<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Histori Tiket Perjalanan') - TicketTrace</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

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
                    <span class="font-display font-bold text-lg text-white tracking-tight">TicketTrace <span class="text-xs text-sky-400 font-mono">ERP</span></span>
                </a>
            </div>

            @auth
                <div class="w-8 h-8 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-sky-400 text-xs">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
            @endauth
        </header>

        <!-- Sidebar Navigation (ERP Left Menu - Default Minimized) -->
        <aside :class="[
            mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0',
            isCollapsed ? 'md:w-20' : 'md:w-64'
        ]" class="fixed md:sticky top-0 inset-y-0 left-0 z-50 w-64 bg-slate-900/95 md:bg-slate-900 backdrop-blur-md border-r border-slate-800/80 flex flex-col justify-between h-screen shrink-0 transition-all duration-300 ease-in-out no-print">
            
            <div>
                <!-- Brand / Logo Header & Toggle Button -->
                <div class="p-4 border-b border-slate-800/80 flex items-center justify-between" :class="isCollapsed ? 'md:justify-center md:px-2' : ''">
                    <a href="{{ route('tickets.index') }}" class="flex items-center space-x-3 group" title="TicketTrace ERP">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-sky-500/20 group-hover:scale-105 transition-transform duration-200 shrink-0">
                            <i class="fa-solid fa-ticket text-lg transform -rotate-12"></i>
                        </div>
                        <div x-show="!isCollapsed" class="transition-opacity duration-200">
                            <span class="font-display font-bold text-lg tracking-tight bg-gradient-to-r from-white via-slate-200 to-slate-400 bg-clip-text text-transparent block leading-tight">
                                TicketTrace
                            </span>
                            <span class="text-[10px] block text-sky-400 font-mono tracking-wider font-semibold uppercase">ERP HISTORICAL TICKET</span>
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
                                <i class="fa-solid fa-table-list text-base {{ request()->routeIs('tickets.*') ? 'text-sky-400' : 'text-slate-400' }}"></i>
                                <span x-show="!isCollapsed">Histori Tiket</span>
                            </a>

                            <!-- Kelola User (Admin Only) -->
                            @auth
                                @if(Auth::user()->isAdmin())
                                    <a href="{{ route('users.index') }}" 
                                       :class="isCollapsed ? 'md:justify-center md:px-0' : ''"
                                       class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('users.*') ? 'bg-sky-500/10 text-sky-300 border border-sky-500/30 font-semibold shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}"
                                       :title="isCollapsed ? 'Kelola User' : ''">
                                        <i class="fa-solid fa-users-gear text-base {{ request()->routeIs('users.*') ? 'text-sky-400' : 'text-slate-400' }}"></i>
                                        <span x-show="!isCollapsed">Kelola User</span>
                                    </a>
                                @endif
                            @endauth
                        </nav>
                </div>
            </div>

            <!-- User Profile & Logout Bottom Bar -->
            @auth
                <div class="p-3 border-t border-slate-800/80 bg-slate-950/40">
                    <div class="p-2.5 rounded-2xl bg-slate-800/60 border border-slate-700/50 mb-2 flex items-center" :class="isCollapsed ? 'md:justify-center md:p-2' : 'space-x-3'">
                        <div class="w-9 h-9 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-sky-400 text-sm shrink-0" :title="Auth::user()->name">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div x-show="!isCollapsed" class="min-w-0 flex-1">
                            <div class="text-xs font-semibold text-white truncate leading-tight">{{ Auth::user()->name }}</div>
                            <div class="text-[10px] font-mono text-sky-400 truncate mt-0.5 capitalize">
                                @if(Auth::user()->isAdmin())
                                    Admin
                                @elseif(Auth::user()->isBooker() || Auth::user()->role === 'payer')
                                    Booker & Payer
                                @else
                                    User
                                @endif
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" 
                                :class="isCollapsed ? 'md:justify-center md:px-0' : 'px-3.5'"
                                class="w-full py-2 rounded-xl text-xs font-medium text-rose-400 hover:text-white hover:bg-rose-500/20 border border-rose-500/30 flex items-center justify-center gap-2 transition-all" 
                                :title="isCollapsed ? 'Logout / Keluar' : ''">
                            <i class="fa-solid fa-right-from-bracket text-sm"></i>
                            <span x-show="!isCollapsed">Logout</span>
                        </button>
                    </form>
                </div>
            @endauth
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
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
                    <div x-data="{ show: true }" x-show="show" class="mb-3 p-3.5 rounded-xl bg-rose-950/80 border border-rose-500/40 text-rose-200 flex items-center justify-between shadow-xl no-print shrink-0">
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
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
</body>
</html>
