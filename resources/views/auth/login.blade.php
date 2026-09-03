<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TicketTrace</title>

    <!-- Google Fonts Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Outfit', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        .glass-card {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(16px);
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
    </style>
</head>
<body class="h-full font-sans antialiased bg-slate-950 text-slate-100 flex items-center justify-center p-4">

    <div class="max-w-md w-full">
        <!-- Logo Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-sky-500 to-indigo-600 text-white shadow-xl shadow-sky-500/20 mb-3">
                <i class="fa-solid fa-ticket text-2xl transform -rotate-12"></i>
            </div>
            <h1 class="font-display text-3xl font-bold text-white tracking-tight">TicketTrace</h1>
            <p class="text-sm text-slate-400 mt-1">Sistem Histori Tiket & Hak Akses Booker / Payer</p>
        </div>

        <!-- Notification -->
        @if(session('success'))
            <div class="mb-4 p-3.5 rounded-xl bg-emerald-950/80 border border-emerald-500/40 text-emerald-200 text-xs flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-400 text-sm"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-3.5 rounded-xl bg-rose-950/80 border border-rose-500/40 text-rose-200 text-xs flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-rose-400 text-sm"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Login Form Card -->
        <div class="glass-card p-6 sm:p-8 rounded-3xl shadow-2xl">
            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 mb-1.5">Alamat Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-sm">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="email@example.com" class="w-full glass-input rounded-xl pl-10 pr-4 py-2.5 text-sm placeholder-slate-600 @error('email') border-rose-500 @enderror">
                    </div>
                    @error('email')
                        <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-300 mb-1.5">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-sm">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <input type="password" id="password" name="password" required placeholder="••••••••" class="w-full glass-input rounded-xl pl-10 pr-4 py-2.5 text-sm placeholder-slate-600">
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 text-slate-400 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded bg-slate-800 border-slate-700 text-sky-500 focus:ring-0">
                        <span>Ingat saya</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-400 hover:to-blue-500 shadow-lg shadow-sky-500/25 transition-all">
                    <i class="fa-solid fa-right-to-bracket mr-2"></i> Masuk / Login
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-slate-800 text-center">
                <p class="text-xs text-slate-400">Belum punya akun? <a href="{{ route('register') }}" class="text-sky-400 hover:underline font-medium">Daftar Akun Baru</a></p>
            </div>
        </div>

        <!-- Quick Login Demo Buttons -->
        <div class="mt-6 glass-card p-4 rounded-2xl border border-slate-800">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block text-center mb-3">
                ⚡ Demo Quick Login (Klik untuk Masuk Instan)
            </span>
            <div class="grid grid-cols-2 gap-2">
                <!-- Admin -->
                <form action="{{ route('quick-login') }}" method="POST">
                    @csrf
                    <input type="hidden" name="email" value="admin@ticket.com">
                    <button type="submit" class="w-full py-2 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-700/80 text-left text-xs transition-colors group">
                        <div class="font-bold text-sky-400 group-hover:text-sky-300">🛡️ Admin Manager</div>
                        <div class="text-[10px] text-slate-500">Akses Penuh</div>
                    </button>
                </form>

                <!-- Booker -->
                <form action="{{ route('quick-login') }}" method="POST">
                    @csrf
                    <input type="hidden" name="email" value="booker@ticket.com">
                    <button type="submit" class="w-full py-2 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-700/80 text-left text-xs transition-colors group">
                        <div class="font-bold text-indigo-400 group-hover:text-indigo-300">📝 Booker / Pemesan</div>
                        <div class="text-[10px] text-slate-500">Siti (Sekretaris)</div>
                    </button>
                </form>

                <!-- Payer -->
                <form action="{{ route('quick-login') }}" method="POST">
                    @csrf
                    <input type="hidden" name="email" value="payer@ticket.com">
                    <button type="submit" class="w-full py-2 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-700/80 text-left text-xs transition-colors group">
                        <div class="font-bold text-emerald-400 group-hover:text-emerald-300">💳 Payer / Finance</div>
                        <div class="text-[10px] text-slate-500">PT Corporate Finance</div>
                    </button>
                </form>

                <!-- Regular User -->
                <form action="{{ route('quick-login') }}" method="POST">
                    @csrf
                    <input type="hidden" name="email" value="user@ticket.com">
                    <button type="submit" class="w-full py-2 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-700/80 text-left text-xs transition-colors group">
                        <div class="font-bold text-amber-400 group-hover:text-amber-300">👤 User Penumpang</div>
                        <div class="text-[10px] text-slate-500">Luqman Solihin</div>
                    </button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
