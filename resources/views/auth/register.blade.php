<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Baru - TicketTrace</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

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

    <div class="max-w-md w-full my-8">
        <!-- Logo Header -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-tr from-sky-500 to-indigo-600 text-white shadow-xl shadow-sky-500/20 mb-2">
                <i class="fa-solid fa-ticket text-xl transform -rotate-12"></i>
            </div>
            <h1 class="font-display text-2xl font-bold text-white tracking-tight">Daftar Akun TicketTrace</h1>
            <p class="text-xs text-slate-400 mt-1">Buat akun dengan pilihan peran Booker, Payer, atau User Penumpang</p>
        </div>

        <!-- Register Form Card -->
        <div class="glass-card p-6 sm:p-8 rounded-3xl shadow-2xl">
            <form action="{{ route('register') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Nama Lengkap -->
                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-300 mb-1.5">Nama Lengkap / Instansi</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="Contoh: Siti Nurhaliza" class="w-full glass-input rounded-xl px-4 py-2.5 text-sm placeholder-slate-600 @error('name') border-rose-500 @enderror">
                    @error('name')
                        <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 mb-1.5">Alamat Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="email@domain.com" class="w-full glass-input rounded-xl px-4 py-2.5 text-sm placeholder-slate-600 @error('email') border-rose-500 @enderror">
                    @error('email')
                        <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Peran (Role) -->
                <div>
                    <label for="role" class="block text-xs font-semibold text-slate-300 mb-1.5">Peran Akun (Role)</label>
                    <select id="role" name="role" required class="w-full glass-input rounded-xl px-4 py-2.5 text-sm bg-slate-900 @error('role') border-rose-500 @enderror">
                        <option value="booker" {{ old('role') == 'booker' ? 'selected' : '' }}>📝 Booker / Pemesan Tiket (Sekretaris)</option>
                        <option value="payer" {{ old('role') == 'payer' ? 'selected' : '' }}>💳 Payer / Pembayar Tiket (Finance/Kasir)</option>
                        <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>👤 User / Penumpang Perjalanan</option>
                    </select>
                    @error('role')
                        <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-300 mb-1.5">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Minimal 8 karakter" class="w-full glass-input rounded-xl px-4 py-2.5 text-sm placeholder-slate-600 @error('password') border-rose-500 @enderror">
                    @error('password')
                        <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Konfirmasi Password -->
                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-slate-300 mb-1.5">Konfirmasi Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="Ulangi password" class="w-full glass-input rounded-xl px-4 py-2.5 text-sm placeholder-slate-600">
                </div>

                <button type="submit" class="w-full py-3 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-400 hover:to-blue-500 shadow-lg shadow-sky-500/25 transition-all">
                    <i class="fa-solid fa-user-plus mr-2"></i> Daftar Akun Sekarang
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-slate-800 text-center">
                <p class="text-xs text-slate-400">Sudah punya akun? <a href="{{ route('login') }}" class="text-sky-400 hover:underline font-medium">Masuk / Login di sini</a></p>
            </div>
        </div>
    </div>

</body>
</html>
