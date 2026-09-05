@extends('layouts.app')

@section('title', 'Edit Akun - ' . $user->name)

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Header with Back Link, Title, and Delete Account Button above Card -->
    <div class="mb-6 flex items-start justify-between gap-4">
        <div>
            <a href="{{ route('users.index') }}" class="text-xs font-medium text-sky-400 hover:text-sky-300 inline-flex items-center gap-1.5 mb-2">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Akun
            </a>
            <h1 class="font-display text-2xl sm:text-3xl font-bold text-white">Edit Akun Pengguna</h1>
            <p class="text-slate-400 text-sm mt-1">Perbarui informasi akun & hak akses role untuk <span class="font-semibold text-white">{{ $user->name }}</span></p>
        </div>

        @if(Auth::id() !== $user->id)
            <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun {{ $user->name }}?');" class="shrink-0 mt-2">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-rose-300 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 transition-all inline-flex items-center gap-1.5 shadow-sm" title="Hapus Akun Ini">
                    <i class="fa-solid fa-trash-can"></i> Hapus Akun Ini
                </button>
            </form>
        @endif
    </div>

    <!-- Edit User Card -->
    <div class="glass-card p-6 sm:p-8 rounded-2xl shadow-2xl">
        <form action="{{ route('users.update', $user->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-xs font-medium text-slate-300 mb-1.5">
                    Nama Lengkap <span class="text-rose-400">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required class="w-full glass-input rounded-xl px-4 py-2.5 text-sm @error('name') border-rose-500 @enderror">
                @error('name')
                    <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-xs font-medium text-slate-300 mb-1.5">
                    Alamat Email <span class="text-rose-400">*</span>
                </label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full glass-input rounded-xl px-4 py-2.5 text-sm font-mono @error('email') border-rose-500 @enderror">
                @error('email')
                    <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="role" class="block text-xs font-medium text-slate-300 mb-1.5">
                    Role Hak Akses <span class="text-rose-400">*</span>
                </label>
                <select id="role" name="role" required class="w-full glass-input rounded-xl px-4 py-2.5 text-sm bg-slate-900 capitalize @error('role') border-rose-500 @enderror">
                    @foreach($roles as $rOption)
                        <option value="{{ $rOption }}" {{ old('role', $user->role) == $rOption ? 'selected' : '' }}>
                            {{ ($rOption === 'finance' || $rOption === 'booker') ? 'Finance' : ucfirst($rOption) }} 
                            @if($rOption === 'admin')
                                (Full Control System)
                            @elseif($rOption === 'finance' || $rOption === 'booker')
                                (Membuat, Mengelola Tiket & Pembayaran)
                            @else
                                (Read-Only Penumpang)
                            @endif
                        </option>
                    @endforeach
                </select>
                @error('role')
                    <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-xs font-medium text-slate-300 mb-1.5">
                    Ubah Password <span class="text-slate-500">(Kosongkan jika tidak ingin mengubah password)</span>
                </label>
                <input type="password" id="password" name="password" placeholder="Masukkan password baru (minimal 6 karakter)..." class="w-full glass-input rounded-xl px-4 py-2.5 text-sm @error('password') border-rose-500 @enderror">
                @error('password')
                    <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                <a href="{{ route('users.index') }}" class="px-5 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-400 hover:to-indigo-500 shadow-lg shadow-sky-500/25 transition-all">
                    <i class="fa-solid fa-floppy-disk mr-2"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
