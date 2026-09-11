@extends('layouts.app')

@section('title', 'Tambah Histori Biaya Lain-lain')

@section('content')
<div x-data="{ 
    status: '{{ old('status', 'Belum Bayar') }}'
}" class="max-w-4xl mx-auto min-w-0 w-full pb-12">

    <!-- Header -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('expenses.index') }}" class="text-xs font-medium text-emerald-400 hover:text-emerald-300 inline-flex items-center gap-1.5 mb-2">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Histori Biaya Lain-lain
            </a>
            <h1 class="font-display text-2xl sm:text-3xl font-bold text-white leading-tight">Tambah Histori Biaya Lain-lain</h1>
            <p class="text-slate-400 text-xs sm:text-sm mt-1">Isi formulir pencatatan pengeluaran operasional di luar tiket dan hotel.</p>
        </div>
    </div>

    <!-- Form Container -->
    <div class="glass-card p-4 sm:p-8 rounded-2xl shadow-2xl overflow-hidden">
        <form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

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
                        <input type="date" name="booking_date" value="{{ old('booking_date', date('Y-m-d')) }}" required onclick="this.showPicker?.()" class="glass-input w-full px-4 py-2.5 rounded-xl text-sm font-mono cursor-pointer">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1.5">Kode Booking / Referensi <span class="text-slate-500">(Opsional)</span></label>
                        <input type="text" name="booking_code" value="{{ old('booking_code') }}" placeholder="Ex: EXP-89102" class="glass-input w-full px-4 py-2.5 rounded-xl text-sm font-mono">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-slate-300 mb-1.5">Kode Invoice <span class="text-rose-400">*</span></label>
                        <input type="text" name="invoice_code" value="{{ old('invoice_code') }}" required placeholder="Ex: INV-EXP-2026-001" class="glass-input w-full px-4 py-2.5 rounded-xl text-sm font-mono">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-slate-300 mb-1.5">Nama / Rincian Biaya <span class="text-rose-400">*</span></label>
                        <input type="text" name="expense_name" value="{{ old('expense_name') }}" required placeholder="Ex: Biaya Swab PCR / Transportasi Lokal / Extra Bagasi" class="glass-input w-full px-4 py-2.5 rounded-xl text-sm">
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
                        <input type="text" name="booked_by" value="{{ old('booked_by', '') }}" required placeholder="contoh: Martha" class="glass-input w-full px-4 py-2.5 rounded-xl text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1.5">Nominal Biaya (IDR) <span class="text-rose-400">*</span></label>
                        <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount') }}" required placeholder="Ex: 500000" class="glass-input w-full px-4 py-2.5 rounded-xl text-sm font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1.5">Status Pembayaran <span class="text-rose-400">*</span></label>
                        @if(!Auth::user()->isAdmin())
                            <div class="w-full glass-input rounded-xl px-4 py-2.5 text-sm bg-slate-900/80 flex items-center justify-between border border-emerald-500/30">
                                <span class="inline-flex items-center gap-2 font-semibold text-emerald-400">
                                    <i class="fa-solid fa-hourglass-half text-xs"></i> Belum Bayar
                                </span>
                            </div>
                            <input type="hidden" name="status" value="Belum Bayar">
                        @else
                            <select name="status" x-model="status" required class="glass-input w-full px-4 py-2.5 rounded-xl text-sm bg-slate-900">
                                @foreach($statusOptions as $opt)
                                    <option value="{{ $opt }}" {{ old('status', 'Belum Bayar') == $opt ? 'selected' : '' }} class="bg-slate-900 text-white">{{ $opt }}</option>
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
                    <label class="block text-xs font-medium text-slate-300 mb-1.5">File Lampiran Bukti / Invoice (PDF / JPG / PNG)</label>
                    <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png" class="glass-input w-full px-3.5 py-2.5 rounded-xl text-xs text-slate-400 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-500/20 file:text-emerald-300 hover:file:bg-emerald-500 hover:file:text-slate-950">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1.5">Catatan Tambahan</label>
                    <textarea name="notes" rows="3" placeholder="Catatan seperti keperluan dinas, nomor resi, rincian item, dsb..." class="glass-input w-full px-4 py-2.5 rounded-xl text-sm">{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <a href="{{ route('expenses.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition-all">Batal</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-slate-950 font-bold text-xs shadow-lg shadow-emerald-500/20 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Histori Biaya
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
