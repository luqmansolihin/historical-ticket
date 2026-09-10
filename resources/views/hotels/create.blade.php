@extends('layouts.app')

@section('title', 'Tambah Histori Hotel')

@section('content')
<div x-data="{ 
    guests: ['{{ old('guest_names.0', '') }}'],
    addGuest() { this.guests.push(''); },
    removeGuest(index) { if (this.guests.length > 1) this.guests.splice(index, 1); },
    status: '{{ old('status', 'Belum Bayar') }}'
}" class="max-w-4xl mx-auto space-y-4">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('hotels.index') }}" class="text-xs font-semibold text-sky-400 hover:text-sky-300 flex items-center gap-1 mb-1">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Histori Hotel
            </a>
            <h1 class="text-lg sm:text-xl font-bold font-display tracking-tight text-white">Tambah Histori Reservasi Hotel</h1>
            <p class="text-slate-400 text-xs sm:text-sm">Isi formulir reservasi hotel untuk mencatat histori transaksi dan invoice.</p>
        </div>
    </div>

    <!-- Form Container -->
    <form action="{{ route('hotels.store') }}" method="POST" enctype="multipart/form-data" class="glass-card p-5 sm:p-6 rounded-2xl border border-slate-800 space-y-6">
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

        <!-- Section 1: Informasi Reservasi & Hotel -->
        <div>
            <h3 class="text-xs font-mono font-bold text-sky-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                <i class="fa-solid fa-hotel"></i> Data Reservasi & Hotel
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Kode Booking Hotel</label>
                    <input type="text" name="booking_code" value="{{ old('booking_code') }}" placeholder="Ex: HTL-89102" class="glass-input w-full px-3.5 py-2 rounded-xl text-xs font-mono">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Kode Invoice <span class="text-rose-400">*</span></label>
                    <input type="text" name="invoice_code" value="{{ old('invoice_code') }}" required placeholder="Ex: INV-HTL-2026-001" class="glass-input w-full px-3.5 py-2 rounded-xl text-xs font-mono">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Tanggal Booking <span class="text-rose-400">*</span></label>
                    <input type="date" name="booking_date" value="{{ old('booking_date', date('Y-m-d')) }}" required class="glass-input w-full px-3.5 py-2 rounded-xl text-xs font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                <div class="sm:col-span-1">
                    <label class="block text-xs font-medium text-slate-300 mb-1">Nama Hotel <span class="text-rose-400">*</span></label>
                    <input type="text" name="hotel_name" value="{{ old('hotel_name') }}" required placeholder="Ex: Hotel Grand Indonesia, Jakarta" class="glass-input w-full px-3.5 py-2 rounded-xl text-xs">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Tanggal Check In <span class="text-rose-400">*</span></label>
                    <input type="date" name="check_in_date" value="{{ old('check_in_date') }}" required class="glass-input w-full px-3.5 py-2 rounded-xl text-xs font-mono">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Tanggal Check Out <span class="text-rose-400">*</span></label>
                    <input type="date" name="check_out_date" value="{{ old('check_out_date') }}" required class="glass-input w-full px-3.5 py-2 rounded-xl text-xs font-mono">
                </div>
            </div>
        </div>

        <hr class="border-slate-800">

        <!-- Section 2: Daftar Tamu Menginap -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-mono font-bold text-sky-400 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-users"></i> Tamu yang Menginap <span class="text-rose-400">*</span>
                </h3>
                <button type="button" @click="addGuest()" class="px-3 py-1 rounded-lg bg-sky-500/20 hover:bg-sky-500 text-sky-300 hover:text-white border border-sky-500/30 text-xs font-semibold transition-all flex items-center gap-1.5">
                    <i class="fa-solid fa-plus text-xs"></i> Tambah Tamu
                </button>
            </div>

            <div class="space-y-2">
                <template x-for="(guest, index) in guests" :key="index">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center font-mono text-xs font-bold text-slate-400 shrink-0" x-text="index + 1"></div>
                        <input type="text" :name="'guest_names[' + index + ']'" x-model="guests[index]" required placeholder="Nama lengkap tamu..." class="glass-input flex-1 px-3.5 py-2 rounded-xl text-xs">
                        <button type="button" @click="removeGuest(index)" x-show="guests.length > 1" class="p-2 rounded-xl bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-white border border-rose-500/30 transition-all shrink-0">
                            <i class="fa-solid fa-trash-can text-xs"></i>
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <hr class="border-slate-800">

        <!-- Section 3: Pemesan & Pembayaran -->
        <div>
            <h3 class="text-xs font-mono font-bold text-sky-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                <i class="fa-solid fa-wallet"></i> Detail Pemesan & Pembayaran
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Nama Pemesan (Booked By) <span class="text-rose-400">*</span></label>
                    <input type="text" name="booked_by" value="{{ old('booked_by', Auth::user()->name) }}" required class="glass-input w-full px-3.5 py-2 rounded-xl text-xs">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Biaya Hotel (IDR) <span class="text-rose-400">*</span></label>
                    <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount') }}" required placeholder="Ex: 1500000" class="glass-input w-full px-3.5 py-2 rounded-xl text-xs font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Status Pembayaran <span class="text-rose-400">*</span></label>
                    <select name="status" x-model="status" required class="glass-input w-full px-3.5 py-2 rounded-xl text-xs">
                        @foreach($statusOptions as $opt)
                            <option value="{{ $opt }}" class="bg-slate-900 text-white">{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Penanggung Jawab Biaya (Paid By)</label>
                    <input type="text" name="paid_by" value="{{ old('paid_by') }}" placeholder="Ex: PT Corporate Finance" class="glass-input w-full px-3.5 py-2 rounded-xl text-xs">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Tanggal Bayar</label>
                    <input type="date" name="payment_date" value="{{ old('payment_date') }}" class="glass-input w-full px-3.5 py-2 rounded-xl text-xs font-mono">
                </div>
            </div>
        </div>

        <hr class="border-slate-800">

        <!-- Section 4: Catatan & Lampiran -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">Catatan Tambahan</label>
                <textarea name="notes" rows="3" placeholder="Catatan seperti instruksi khusus, include sarapan, dsb..." class="glass-input w-full px-3.5 py-2 rounded-xl text-xs">{{ old('notes') }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">File Lampiran Bukti / Invoice (PDF / JPG / PNG)</label>
                <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png" class="glass-input w-full px-3.5 py-2 rounded-xl text-xs text-slate-400 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-sky-500/20 file:text-sky-300 hover:file:bg-sky-500 hover:file:text-white">
            </div>
        </div>

        <!-- Submit Button -->
        <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-800">
            <a href="{{ route('hotels.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition-all">Batal</a>
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-400 hover:to-indigo-500 text-white text-xs font-semibold shadow-lg shadow-sky-500/20 transition-all flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Histori Hotel
            </button>
        </div>
    </form>
</div>
@endsection
