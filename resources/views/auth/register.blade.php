@extends('layouts.app')

@section('title', 'Buat Akun Pengguna Baru (Admin Only)')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('tickets.index') }}" class="text-xs font-medium text-sky-400 hover:text-sky-300 inline-flex items-center gap-1.5 mb-2">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard Tiket
        </a>
        <h1 class="font-display text-2xl sm:text-3xl font-bold text-white flex items-center gap-2">
            <i class="fa-solid fa-user-plus text-sky-400"></i> Buat Akun Pengguna Baru
        </h1>
        <p class="text-slate-400 text-sm mt-1">Form pendaftaran akun khusus Administrator untuk menambahkan Booker, Payer, atau User Penumpang baru.</p>
    </div>

    <div class="glass-card p-6 sm:p-8 rounded-2xl shadow-2xl">
        <form action="{{ route('users.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Nama Lengkap -->
            <div>
                <label for="name" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Nama Lengkap / Instansi <span class="text-rose-400">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="Contoh: Siti Nurhaliza (Sekretaris)" class="w-full glass-input rounded-xl px-4 py-2.5 text-sm placeholder-slate-600 @error('name') border-rose-500 @enderror">
                @error('name')
                    <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Alamat Email (Untuk Login) <span class="text-rose-400">*</span>
                </label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="email@company.com" class="w-full glass-input rounded-xl px-4 py-2.5 text-sm placeholder-slate-600 @error('email') border-rose-500 @enderror">
                @error('email')
                    <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Peran (Role) -->
            <div>
                <label for="role" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Peran Akun / Hak Akses (Role) <span class="text-rose-400">*</span>
                </label>
                <select id="role" name="role" required class="w-full glass-input rounded-xl px-4 py-2.5 text-sm bg-slate-900 @error('role') border-rose-500 @enderror">
                    <option value="booker" {{ old('role', 'booker') == 'booker' ? 'selected' : '' }}>📝 Booker / Pemesan Tiket (Sekretaris/Admin Pemesan)</option>
                    <option value="payer" {{ old('role') == 'payer' ? 'selected' : '' }}>💳 Payer / Pembayar Tiket (Finance/Bendahara)</option>
                    <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>👤 User / Penumpang Perjalanan</option>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>🛡️ Admin Manager (Akses Penuh)</option>
                </select>
                @error('role')
                    <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Password <span class="text-rose-400">*</span>
                </label>
                <input type="password" id="password" name="password" required placeholder="Minimal 8 karakter" class="w-full glass-input rounded-xl px-4 py-2.5 text-sm placeholder-slate-600 @error('password') border-rose-500 @enderror">
                @error('password')
                    <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Konfirmasi Password -->
            <div>
                <label for="password_confirmation" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Konfirmasi Password <span class="text-rose-400">*</span>
                </label>
                <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="Ulangi password" class="w-full glass-input rounded-xl px-4 py-2.5 text-sm placeholder-slate-600">
            </div>

            <!-- Submit buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                <a href="{{ route('tickets.index') }}" class="px-5 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-400 hover:to-blue-500 shadow-lg shadow-sky-500/25 transition-all">
                    <i class="fa-solid fa-user-plus mr-2"></i> Buat Akun Pengguna
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
