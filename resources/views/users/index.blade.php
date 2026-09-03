@extends('layouts.app')

@section('title', 'Daftar List Akun - Management Users')

@section('content')
<div class="space-y-8">
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-semibold text-sky-400 uppercase tracking-widest block font-mono mb-1">
                <i class="fa-solid fa-users-gear mr-1"></i> Admin User Management
            </span>
            <h1 class="font-display text-2xl sm:text-3xl font-bold text-white tracking-tight">Daftar Akun Pengguna</h1>
            <p class="text-slate-400 text-sm mt-1">Kelola seluruh hak akses, role, dan akun terdaftar dalam sistem TicketTrace.</p>
        </div>
        <div>
            <a href="{{ route('users.create') }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-white bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-400 hover:to-indigo-500 shadow-lg shadow-sky-500/20 transition-all inline-flex items-center gap-2">
                <i class="fa-solid fa-user-plus text-sm"></i> Tambah Akun Baru
            </a>
        </div>
    </div>

    <!-- Summary Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="glass-card p-4 rounded-2xl border-l-4 border-l-sky-500">
            <span class="text-xs font-semibold text-slate-400 block uppercase">Total Akun</span>
            <div class="font-display text-2xl font-bold text-white mt-1">{{ number_format($totalUsers) }}</div>
            <span class="text-[10px] text-slate-500 font-mono mt-1 block">Akun Terdaftar</span>
        </div>
        <div class="glass-card p-4 rounded-2xl border-l-4 border-l-amber-500">
            <span class="text-xs font-semibold text-amber-400 block uppercase">Admin</span>
            <div class="font-display text-2xl font-bold text-amber-300 mt-1">{{ number_format($totalAdmin) }}</div>
            <span class="text-[10px] text-amber-500/80 font-mono mt-1 block">Full Access</span>
        </div>
        <div class="glass-card p-4 rounded-2xl border-l-4 border-l-sky-400">
            <span class="text-xs font-semibold text-sky-400 block uppercase">Booker</span>
            <div class="font-display text-2xl font-bold text-sky-300 mt-1">{{ number_format($totalBooker) }}</div>
            <span class="text-[10px] text-sky-500/80 font-mono mt-1 block">Pemesan Tiket</span>
        </div>
        <div class="glass-card p-4 rounded-2xl border-l-4 border-l-emerald-500">
            <span class="text-xs font-semibold text-emerald-400 block uppercase">Payer</span>
            <div class="font-display text-2xl font-bold text-emerald-300 mt-1">{{ number_format($totalPayer) }}</div>
            <span class="text-[10px] text-emerald-500/80 font-mono mt-1 block">Penanggung Jawab</span>
        </div>
        <div class="glass-card p-4 rounded-2xl border-l-4 border-l-slate-600">
            <span class="text-xs font-semibold text-slate-400 block uppercase">User Regular</span>
            <div class="font-display text-2xl font-bold text-slate-300 mt-1">{{ number_format($totalRegularUser) }}</div>
            <span class="text-[10px] text-slate-500 font-mono mt-1 block">Read-Only</span>
        </div>
    </div>

    <!-- Search & Filter Toolbar -->
    <div class="glass-card p-5 rounded-2xl">
        <form action="{{ route('users.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
            <div class="sm:col-span-6 lg:col-span-7">
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Pencarian Nama / Email</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama pengguna atau alamat email..." class="w-full glass-input rounded-xl pl-10 pr-4 py-2.5 text-sm placeholder-slate-500">
                </div>
            </div>

            <div class="sm:col-span-4 lg:col-span-3">
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Filter Role</label>
                <select name="role" class="w-full glass-input rounded-xl px-3 py-2.5 text-sm bg-slate-900 capitalize">
                    <option value="">Semua Role</option>
                    @foreach($roleOptions as $roleOpt)
                        <option value="{{ $roleOpt }}" {{ $roleFilter == $roleOpt ? 'selected' : '' }}>{{ ucfirst($roleOpt) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 lg:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full h-[42px] px-4 rounded-xl text-xs font-semibold text-white bg-sky-600 hover:bg-sky-500 shadow-md shadow-sky-600/20 transition-all flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                @if($search || $roleFilter)
                    <a href="{{ route('users.index') }}" class="h-[42px] px-3.5 rounded-xl text-xs font-medium text-rose-300 hover:text-white bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 transition-all flex items-center justify-center" title="Reset Filter">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Users Table View -->
    <div class="glass-card rounded-2xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-900/90 text-xs uppercase font-semibold text-slate-400 tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-4 px-5">Pengguna</th>
                        <th class="py-4 px-5">Email</th>
                        <th class="py-4 px-5">Role Akses</th>
                        <th class="py-4 px-5">Tgl Terdaftar</th>
                        <th class="py-4 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($users as $userItem)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="py-4 px-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-sky-400 text-sm shrink-0 shadow-inner">
                                        {{ strtoupper(substr($userItem->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-white flex items-center gap-2">
                                            {{ $userItem->name }}
                                            @if(Auth::id() === $userItem->id)
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono bg-sky-500/20 text-sky-300 border border-sky-500/30">
                                                    Anda
                                                </span>
                                            @endif
                                        </div>
                                        <span class="text-[11px] text-slate-500 font-mono block">ID: #{{ $userItem->id }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-5 font-mono text-slate-300 text-xs">
                                {{ $userItem->email }}
                            </td>
                            <td class="py-4 px-5">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold border inline-flex items-center gap-1.5 capitalize {{ $userItem->role_badge_class }}">
                                    @if($userItem->isAdmin())
                                        <i class="fa-solid fa-shield-halved text-amber-400 text-[10px]"></i>
                                    @elseif($userItem->role === 'booker')
                                        <i class="fa-solid fa-user-check text-sky-400 text-[10px]"></i>
                                    @elseif($userItem->role === 'payer')
                                        <i class="fa-solid fa-credit-card text-emerald-400 text-[10px]"></i>
                                    @else
                                        <i class="fa-solid fa-user text-slate-400 text-[10px]"></i>
                                    @endif
                                    {{ ucfirst($userItem->role) }}
                                </span>
                            </td>
                            <td class="py-4 px-5 text-slate-400 text-xs font-mono">
                                {{ $userItem->created_at ? $userItem->created_at->format('d M Y, H:i') : '-' }}
                            </td>
                            <td class="py-4 px-5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('users.edit', $userItem->id) }}" class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 hover:bg-amber-500 hover:text-white flex items-center justify-center transition-colors" title="Edit Akun & Role">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </a>

                                    @if(Auth::id() !== $userItem->id)
                                        <form action="{{ route('users.destroy', $userItem->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun {{ $userItem->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-8 h-8 rounded-lg bg-rose-500/10 text-rose-400 hover:bg-rose-500 hover:text-white flex items-center justify-center transition-colors" title="Hapus Akun Ini">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" disabled class="w-8 h-8 rounded-lg bg-slate-800 text-slate-600 cursor-not-allowed flex items-center justify-center" title="Tidak dapat menghapus akun Anda sendiri">
                                            <i class="fa-solid fa-ban text-xs"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-500">
                                <i class="fa-solid fa-user-slash text-4xl block mb-3 text-slate-600"></i>
                                Tidak ada akun pengguna yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-slate-800 bg-slate-900/50">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
