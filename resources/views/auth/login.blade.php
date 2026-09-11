<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-100 text-slate-800">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - ExpenseTrace</title>

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
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
        }
        .glass-input {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            color: #0f172a;
        }
        .glass-input:focus {
            background: #ffffff;
            border-color: #0284c7;
            outline: none;
            box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.15);
        }
    </style>
</head>
<body class="h-full font-sans antialiased bg-slate-100 text-slate-800 flex items-center justify-center p-4">

    <div class="max-w-md w-full">
        <!-- Logo Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-sky-500 to-indigo-600 text-white shadow-xl shadow-sky-500/20 mb-3">
                <i class="fa-solid fa-receipt text-2xl"></i>
            </div>
            <h1 class="font-display text-3xl font-bold text-slate-900 tracking-tight">ExpenseTrace</h1>
            <p class="text-sm text-slate-500 mt-1">Purchase & Payment Tracking System</p>
        </div>

        <!-- Notification -->
        @if(session('success'))
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-init="setTimeout(() => show = false, 4000)" 
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 class="mb-4 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" @click="show = false" class="text-emerald-500 hover:text-emerald-700 ml-2 transition-colors">
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
                 class="mb-4 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600 text-sm"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" @click="show = false" class="text-rose-500 hover:text-rose-700 ml-2 transition-colors">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        @endif

        <!-- Login Form Card -->
        <div class="glass-card p-6 sm:p-8 rounded-3xl shadow-xl">
            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-sm">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="email@example.com" class="w-full glass-input rounded-xl pl-10 pr-4 py-2.5 text-sm placeholder-slate-400 @error('email') border-rose-500 @enderror">
                    </div>
                    @error('email')
                        <p class="text-rose-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-sm">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <input type="password" id="password" name="password" required placeholder="••••••••" class="w-full glass-input rounded-xl pl-10 pr-4 py-2.5 text-sm placeholder-slate-400">
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 text-slate-600 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded bg-white border-slate-300 text-sky-600 focus:ring-0">
                        <span>Remember me</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 hover:to-blue-700 shadow-lg shadow-sky-500/25 transition-all">
                    <i class="fa-solid fa-right-to-bracket mr-2"></i> Sign In
                </button>
            </form>
        </div>
    </div>

</body>
</html>
