<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ExpenseTrace</title>

    <!-- Local Fonts -->
    <link rel="stylesheet" href="{{ asset('vendor/fonts/fonts.css') }}">

    <!-- Local Tailwind CSS -->
    <script src="{{ asset('vendor/tailwindcss/tailwindcss.js') }}"></script>
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
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">

    <!-- Local Alpine.js -->
    <script defer src="{{ asset('vendor/alpinejs/alpine.min.js') }}"></script>

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
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-600 text-white shadow-xl shadow-emerald-500/20 mb-3">
                <i class="fa-solid fa-receipt text-2xl"></i>
            </div>
            <h1 class="font-display text-3xl font-bold text-white tracking-tight">ExpenseTrace</h1>
            <p class="text-sm text-slate-400 mt-1">Sistem Pengeluaran Biaya</p>
        </div>

        <!-- Notification -->
        @if(session('success'))
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-init="setTimeout(() => show = false, 4000)" 
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 class="mb-4 p-3.5 rounded-xl bg-emerald-950/80 border border-emerald-500/40 text-emerald-200 text-xs flex items-center justify-between shadow-lg backdrop-blur-sm">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-400 text-sm"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" @click="show = false" class="text-emerald-400 hover:text-emerald-200 ml-2 transition-colors">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-init="setTimeout(() => show = false, 5000)" 
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 class="mb-4 p-3.5 rounded-xl bg-rose-950/80 border border-rose-500/40 text-rose-200 text-xs flex items-center justify-between shadow-lg backdrop-blur-sm">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-rose-400 text-sm"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" @click="show = false" class="text-rose-400 hover:text-rose-200 ml-2 transition-colors">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
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
        </div>
    </div>

</body>
</html>
