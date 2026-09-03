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
    </style>
</head>
<body class="h-full font-sans antialiased bg-slate-950 text-slate-100 selection:bg-sky-500 selection:text-white flex flex-col min-h-screen">

    <!-- Header / Navbar -->
    <header class="sticky top-0 z-40 bg-slate-900/90 backdrop-blur-md border-b border-slate-800/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo & Brand -->
                <div class="flex items-center space-x-3">
                    <a href="{{ route('tickets.index') }}" class="flex items-center space-x-3 group">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-sky-500/20 group-hover:scale-105 transition-transform duration-200">
                            <i class="fa-solid fa-ticket text-lg transform -rotate-12"></i>
                        </div>
                        <div>
                            <span class="font-display font-bold text-xl tracking-tight bg-gradient-to-r from-white via-slate-200 to-slate-400 bg-clip-text text-transparent">
                                TicketTrace
                            </span>
                            <span class="text-xs block text-sky-400 font-mono tracking-wider font-medium">HISTORICAL TICKET</span>
                        </div>
                    </a>
                </div>

                <!-- Navigation & Authenticated User Info -->
                <div class="flex items-center space-x-3 sm:space-x-4">


                    @auth
                        @if(Auth::user()->isAdmin())
                            <a href="{{ route('users.create') }}" class="px-3.5 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('users.create') ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                                <i class="fa-solid fa-user-plus mr-1.5 text-indigo-400"></i> Buat Akun User
                            </a>
                        @endif
                    @endauth



                    <!-- User Profile & Logout -->
                    @auth
                        <div x-data="{ open: false }" class="relative pl-3 border-l border-slate-800">
                            <button @click="open = !open" class="flex items-center space-x-2.5 hover:opacity-90 transition-opacity focus:outline-none">
                                <div class="w-9 h-9 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-sky-400 text-sm">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                                <div class="hidden md:block text-left">
                                    <div class="text-xs font-semibold text-white leading-tight">{{ Auth::user()->name }}</div>
                                    <div class="text-[10px] font-mono text-sky-400 capitalize">
                                        @if(Auth::user()->isAdmin())
                                            🛡️ Admin
                                        @elseif(Auth::user()->isBooker())
                                            📝 Booker
                                        @elseif(Auth::user()->isPayer())
                                            💳 Payer
                                        @else
                                            👤 User
                                        @endif
                                    </div>
                                </div>
                                <i class="fa-solid fa-chevron-down text-xs text-slate-500"></i>
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-cloak x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-56 bg-slate-900 rounded-2xl shadow-2xl border border-slate-800 py-2 z-50">
                                <div class="px-4 py-3 border-b border-slate-800">
                                    <p class="text-xs text-slate-400">Login sebagai:</p>
                                    <p class="text-sm font-semibold text-white truncate mt-0.5">{{ Auth::user()->name }}</p>
                                    <p class="text-xs font-mono text-sky-400 truncate">{{ Auth::user()->email }}</p>
                                </div>

                                <div class="py-1">
                                    @if(Auth::user()->isAdmin())
                                        <a href="{{ route('users.create') }}" class="w-full text-left px-4 py-2 text-xs text-slate-300 hover:bg-slate-800 flex items-center gap-2 transition-colors">
                                            <i class="fa-solid fa-user-plus text-indigo-400"></i> Buat Akun User Baru
                                        </a>
                                    @endif

                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-4 py-2.5 text-xs text-rose-400 hover:bg-rose-500/10 flex items-center gap-2 transition-colors">
                                            <i class="fa-solid fa-right-from-bracket"></i> Logout / Keluar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Main Body Container -->
    <main class="flex-1 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-6 p-4 rounded-xl bg-emerald-950/80 border border-emerald-500/40 text-emerald-200 flex items-center justify-between shadow-xl backdrop-blur-sm">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center text-emerald-400">
                            <i class="fa-solid fa-circle-check text-lg"></i>
                        </div>
                        <span class="text-sm font-medium">{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-emerald-400 hover:text-emerald-200">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div x-data="{ show: true }" x-show="show" class="mb-6 p-4 rounded-xl bg-rose-950/80 border border-rose-500/40 text-rose-200 flex items-center justify-between shadow-xl">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-rose-500/20 flex items-center justify-center text-rose-400">
                            <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                        </div>
                        <span class="text-sm font-medium">{{ session('error') }}</span>
                    </div>
                    <button @click="show = false" class="text-rose-400 hover:text-rose-200">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <footer class="mt-auto border-t border-slate-900 bg-slate-950 py-6 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p>&copy; {{ date('Y') }} TicketTrace &bull; Sistem Histori Tiket, Booker & Payer Authorization</p>
            <div class="flex items-center space-x-4 text-slate-400 font-mono">
                <span><i class="fa-solid fa-shield-halved text-sky-400 mr-1"></i> Role: {{ ucfirst(Auth::user()->role ?? 'Guest') }}</span>
            </div>
        </div>
    </footer>

    <!-- Scroll To Top Floating Button -->
    <div x-data="{ showScrollTop: false }"
         @scroll.window="showScrollTop = (window.pageYOffset > 300)"
         class="fixed bottom-6 right-6 z-50">
        <button x-show="showScrollTop"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 scale-90"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 scale-90"
                @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
                type="button"
                class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-sky-500 to-blue-600 text-white shadow-xl shadow-sky-500/30 flex items-center justify-center hover:scale-110 active:scale-95 transition-all duration-200 border border-sky-400/40 group"
                title="Kembali ke Atas">
            <i class="fa-solid fa-arrow-up text-lg group-hover:-translate-y-0.5 transition-transform"></i>
        </button>
    </div>

    @stack('scripts')
</body>
</html>
