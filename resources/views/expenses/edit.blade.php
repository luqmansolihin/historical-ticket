@extends('layouts.app')

@section('title', 'Edit Histori Biaya Lain-lain' . ($expense->invoice_code ? ' - ' . $expense->invoice_code : ''))

@section('content')
@php
    $isAdmin = Auth::user()->isAdmin();
    $isFinance = Auth::user()->isFinance() && !$isAdmin;
    $isLunas = $expense->status === 'Lunas';
    
    $isBookerLunas = $isFinance && $isLunas;
    $isDataLocked = $isBookerLunas;
@endphp

<div x-data="{ 
    status: '{{ old('status', $expense->status) }}'
}" class="max-w-4xl mx-auto min-w-0 w-full pb-12">

    <!-- Header & Action Buttons -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('expenses.index') }}" class="text-xs font-medium text-emerald-400 hover:text-emerald-300 inline-flex items-center gap-1.5 mb-2">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Histori Biaya Lain-lain
            </a>
            <h1 class="font-display text-2xl sm:text-3xl font-bold text-white leading-tight">Edit Histori Biaya Lain-lain</h1>
            <p class="text-slate-400 text-xs sm:text-sm mt-1">Perbarui data transaksi <span class="font-mono text-emerald-400 font-semibold">{{ $expense->invoice_code }}</span></p>
        </div>

        <!-- Tombol Hapus di Header -->
        <div class="flex items-center gap-2">
            @can('delete', $expense)
                <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data histori biaya ini? Data yang dihapus tidak dapat dikembalikan.');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full sm:w-auto px-4 py-2.5 rounded-xl text-xs font-semibold text-rose-300 bg-rose-500/20 hover:bg-rose-600 hover:text-white border border-rose-500/30 shadow-lg transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-trash-can text-sm"></i> Hapus
                    </button>
                </form>
            @endcan
        </div>
    </div>

    <!-- Form Container -->
    <div class="glass-card p-4 sm:p-8 rounded-2xl shadow-2xl overflow-hidden">
        @if($isBookerLunas)
            <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center gap-3">
                <i class="fa-solid fa-lock text-xl text-emerald-400 shrink-0"></i>
                <div>
                    <span class="font-bold block text-sm">Transaksi Biaya Berstatus Lunas — Mode Pembatasan Akses (Finance)</span>
                    <span>Data biaya dan pengaju telah dikunci karena pembayaran sudah <strong>Lunas</strong>. Sebagai Finance, Anda diperbolehkan mengedit <strong>Tanggal Pembayaran</strong> atau mengubah status menjadi <strong>Dibatalkan</strong>.</span>
                </div>
            </div>
        @endif

        <form action="{{ route('expenses.update', $expense->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            @if($isDataLocked)
                <input type="hidden" name="booking_code" value="{{ old('booking_code', $expense->booking_code) }}">
                <input type="hidden" name="invoice_code" value="{{ old('invoice_code', $expense->invoice_code) }}">
                <input type="hidden" name="booking_date" value="{{ old('booking_date', $expense->booking_date ? $expense->booking_date->format('Y-m-d') : '') }}">
                <input type="hidden" name="expense_name" value="{{ old('expense_name', $expense->expense_name) }}">
                <input type="hidden" name="amount" value="{{ old('amount', $expense->amount) }}">
                <input type="hidden" name="booked_by" value="{{ old('booked_by', $expense->booked_by) }}">
                <input type="hidden" name="booked_by_user_id" value="{{ old('booked_by_user_id', $expense->booked_by_user_id) }}">
            @endif

            @if ($errors->any())
                <div class="p-4 rounded-xl bg-rose-950/80 border border-rose-500/40 text-rose-200 text-xs space-y-1">
                    <div class="font-bold flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation"></i> Terdapat kesalahan pengisian formulir:
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 pl-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Section 1: Informasi Transaksi Biaya -->
            <div>
                <h3 class="text-xs sm:text-sm font-semibold text-emerald-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-receipt"></i> Data Transaksi Biaya
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1.5">Tanggal Biaya <span class="text-rose-400">*</span></label>
                        <input type="date" name="booking_date" value="{{ old('booking_date', $expense->booking_date ? $expense->booking_date->format('Y-m-d') : '') }}" required {{ $isDataLocked ? 'disabled' : '' }} onclick="this.showPicker?.()" class="glass-input w-full px-4 py-2.5 rounded-xl text-sm font-mono cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1.5">Kode Booking / Referensi <span class="text-slate-500">(Opsional)</span></label>
                        <input type="text" name="booking_code" value="{{ old('booking_code', $expense->booking_code) }}" {{ $isDataLocked ? 'disabled' : '' }} class="glass-input w-full px-4 py-2.5 rounded-xl text-sm font-mono disabled:opacity-60 disabled:cursor-not-allowed">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-slate-300 mb-1.5">Kode Invoice <span class="text-rose-400">*</span></label>
                        <input type="text" name="invoice_code" value="{{ old('invoice_code', $expense->invoice_code) }}" required {{ $isDataLocked ? 'disabled' : '' }} class="glass-input w-full px-4 py-2.5 rounded-xl text-sm font-mono disabled:opacity-60 disabled:cursor-not-allowed">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-slate-300 mb-1.5">Nama / Rincian Biaya <span class="text-rose-400">*</span></label>
                        <input type="text" name="expense_name" value="{{ old('expense_name', $expense->expense_name) }}" required {{ $isDataLocked ? 'disabled' : '' }} class="glass-input w-full px-4 py-2.5 rounded-xl text-sm disabled:opacity-60 disabled:cursor-not-allowed">
                    </div>
                </div>
            </div>

            <hr class="border-slate-800/80">

            <!-- Section 2: Pemesan & Pembayaran -->
            <div>
                <h3 class="text-xs sm:text-sm font-semibold text-emerald-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-wallet"></i> Detail Pengaju & Pembayaran
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-slate-300 mb-1.5">Nama Pengaju / Pemesan (Booked By) <span class="text-rose-400">*</span></label>
                        <input type="text" name="booked_by" value="{{ old('booked_by', $expense->booked_by) }}" required placeholder="contoh: Martha" {{ $isDataLocked ? 'disabled' : '' }} class="glass-input w-full px-4 py-2.5 rounded-xl text-sm disabled:opacity-60 disabled:cursor-not-allowed">
                        <input type="hidden" name="booked_by_user_id" value="{{ old('booked_by_user_id', $expense->booked_by_user_id ?: Auth::id()) }}">
                    </div>

                    @if($isFinance)
                        <div class="md:col-span-2 bg-emerald-500/10 border border-emerald-500/30 rounded-xl p-3 flex items-center gap-2.5 shadow-inner">
                            <div class="w-8 h-8 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs shrink-0">
                                <i class="fa-solid fa-credit-card"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-[10px] text-emerald-400 font-semibold uppercase tracking-wider truncate">Pembayaran Oleh (Finance)</div>
                                <div class="text-xs font-bold text-white flex items-center gap-1.5 truncate">
                                    <span class="truncate">{{ $expense->paid_by && $expense->paid_by !== '-' ? $expense->paid_by : Auth::user()->name }}</span>
                                    <span class="text-[9px] font-mono px-1.5 py-0.2 rounded-full bg-emerald-400/20 text-emerald-300 border border-emerald-400/30 shrink-0">
                                        Finance
                                    </span>
                                </div>
                            </div>
                            <input type="hidden" name="paid_by" value="{{ $expense->paid_by && $expense->paid_by !== '-' ? $expense->paid_by : Auth::user()->name }}">
                            <input type="hidden" name="paid_by_user_id" value="{{ $expense->paid_by_user_id ?: Auth::id() }}">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-slate-300 mb-1.5">Tanggal Pembayaran <span class="text-slate-400">(Wajib jika status Lunas)</span></label>
                            <input type="date" name="payment_date" value="{{ old('payment_date', $expense->payment_date ? $expense->payment_date->format('Y-m-d') : '') }}" onclick="this.showPicker?.()" class="glass-input w-full px-4 py-2.5 rounded-xl text-sm font-mono cursor-pointer">
                        </div>
                    @else
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-slate-300 mb-1.5">Pembayaran Oleh</label>
                            <input type="text" name="paid_by" value="{{ old('paid_by', $expense->paid_by) }}" placeholder="Ex: PT Corporate Finance" class="glass-input w-full px-4 py-2.5 rounded-xl text-sm">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-slate-300 mb-1.5">Tanggal Bayar</label>
                            <input type="date" name="payment_date" value="{{ old('payment_date', $expense->payment_date ? $expense->payment_date->format('Y-m-d') : '') }}" onclick="this.showPicker?.()" class="glass-input w-full px-4 py-2.5 rounded-xl text-sm font-mono cursor-pointer">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-slate-300 mb-1.5">Linkkan dengan User Pembayar (Sistem)</label>
                            <select name="paid_by_user_id" class="glass-input w-full px-4 py-2.5 rounded-xl text-sm bg-slate-900">
                                <option value="">-- Pilih User Pembayar --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('paid_by_user_id', $expense->paid_by_user_id) == $user->id ? 'selected' : '' }} class="bg-slate-900 text-white">
                                        {{ $user->name }} ({{ ucfirst($user->role) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1.5">Nominal Biaya (IDR) <span class="text-rose-400">*</span></label>
                        <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount', $expense->amount) }}" required {{ $isDataLocked ? 'disabled' : '' }} class="glass-input w-full px-4 py-2.5 rounded-xl text-sm font-mono disabled:opacity-60 disabled:cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1.5">Status Pembayaran <span class="text-rose-400">*</span></label>
                        @if($isFinance)
                            <select name="status" x-model="status" required class="glass-input w-full px-4 py-2.5 rounded-xl text-sm bg-slate-900">
                                @if($expense->status === 'Lunas')
                                    <option value="Lunas" {{ old('status', $expense->status) == 'Lunas' ? 'selected' : '' }} class="bg-slate-900 text-white">Lunas (Status Saat Ini)</option>
                                    <option value="Dibatalkan" {{ old('status', $expense->status) == 'Dibatalkan' ? 'selected' : '' }} class="bg-slate-900 text-white">Dibatalkan</option>
                                @else
                                    <option value="Belum Bayar" {{ old('status', $expense->status) == 'Belum Bayar' ? 'selected' : '' }} class="bg-slate-900 text-white">Belum Bayar</option>
                                    <option value="Lunas" {{ old('status', $expense->status) == 'Lunas' ? 'selected' : '' }} class="bg-slate-900 text-white">Lunas (Konfirmasi Pembayaran)</option>
                                    <option value="Dibatalkan" {{ old('status', $expense->status) == 'Dibatalkan' ? 'selected' : '' }} class="bg-slate-900 text-white">Dibatalkan</option>
                                @endif
                            </select>
                        @else
                            <select name="status" x-model="status" required class="glass-input w-full px-4 py-2.5 rounded-xl text-sm bg-slate-900">
                                @foreach($statusOptions as $opt)
                                    <option value="{{ $opt }}" class="bg-slate-900 text-white" {{ old('status', $expense->status) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                </div>
            </div>

            <hr class="border-slate-800/80">

            <!-- Section 3: Catatan & Lampiran -->
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1.5">File Lampiran Bukti / Invoice (Kosongkan jika tidak diubah)</label>
                    <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png" class="glass-input w-full px-3.5 py-2.5 rounded-xl text-xs text-slate-400 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-500/20 file:text-emerald-300 hover:file:bg-emerald-500 hover:file:text-slate-950">
                    @if($expense->attachment_path)
                        <div class="mt-2 text-xs">
                            <a href="{{ asset('storage/' . $expense->attachment_path) }}" target="_blank" class="text-emerald-400 hover:underline flex items-center gap-1">
                                <i class="fa-solid fa-paperclip"></i> Lihat File Lampiran Saat Ini
                            </a>
                        </div>
                    @endif
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1.5">Catatan Tambahan</label>
                    <textarea name="notes" rows="3" {{ $isDataLocked ? 'disabled' : '' }} class="glass-input w-full px-4 py-2.5 rounded-xl text-sm disabled:opacity-60 disabled:cursor-not-allowed">{{ old('notes', $expense->notes) }}</textarea>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <a href="{{ route('expenses.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition-all">Batal</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-slate-950 font-bold text-xs shadow-lg shadow-emerald-500/20 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> Perbarui Data Biaya
                </button>
            </div>
        </form>
    </div>

    <!-- Riwayat Log Status Activity -->
    @if($expense->statusLogs->count() > 0)
        <div class="mt-8 glass-card p-6 rounded-2xl border border-slate-800">
            <h3 class="text-xs font-mono font-bold text-emerald-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left"></i> Riwayat Log Status Pembayaran
            </h3>
            <div class="space-y-3">
                @foreach($expense->statusLogs as $log)
                    <div class="p-3.5 rounded-xl bg-slate-900/80 border border-slate-800 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-emerald-400">{{ $log->to_status }}</span>
                            <span class="text-[10px] font-mono text-slate-400">{{ $log->created_at->format('d M Y, H:i') }}</span>
                        </div>
                        <p class="text-slate-300 text-xs mt-1">{{ $log->notes }}</p>
                        <div class="text-[10px] text-slate-500 font-mono mt-1">{{ $log->user_name }} ({{ ucfirst($log->user_role ?? 'user') }})</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
