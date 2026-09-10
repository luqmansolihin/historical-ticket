@extends('layouts.app')

@section('title', 'Edit Histori Hotel')

@section('content')
<div x-data="{ 
    guests: {{ json_encode(old('guest_names', $hotel->guests_list ?: [$hotel->guest_name])) }},
    addGuest() { this.guests.push(''); },
    removeGuest(index) { if (this.guests.length > 1) this.guests.splice(index, 1); },
    status: '{{ old('status', $hotel->status) }}'
}" class="max-w-4xl mx-auto space-y-4">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <a href="{{ route('hotels.index') }}" class="text-xs font-semibold text-sky-400 hover:text-sky-300 flex items-center gap-1 mb-1">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Histori Hotel
            </a>
            <h1 class="text-lg sm:text-xl font-bold font-display tracking-tight text-white">Edit Histori Reservasi Hotel</h1>
            <p class="text-slate-400 text-xs sm:text-sm">Perbarui data hotel <span class="font-mono text-sky-400 font-semibold">{{ $hotel->booking_code ?: '-' }}</span></p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('hotels.pdf', $hotel->id) }}" target="_blank" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-amber-600 hover:bg-amber-500 shadow-lg transition-all flex items-center justify-center gap-2">
                <i class="fa-solid fa-file-pdf text-sm"></i> Preview & Cetak Voucher
            </a>
            @can('delete', $hotel)
                <form action="{{ route('hotels.destroy', $hotel->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data hotel ini? Data yang dihapus tidak dapat dikembalikan.');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-rose-300 bg-rose-500/20 hover:bg-rose-600 hover:text-white border border-rose-500/30 shadow-lg transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-trash-can text-sm"></i> Hapus
                    </button>
                </form>
            @endcan
        </div>
    </div>

    <!-- Form Container -->
    <form action="{{ route('hotels.update', $hotel->id) }}" method="POST" enctype="multipart/form-data" class="glass-card p-5 sm:p-6 rounded-2xl border border-slate-800 space-y-6">
        @csrf
        @method('PUT')

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
                    <input type="text" name="booking_code" value="{{ old('booking_code', $hotel->booking_code) }}" class="glass-input w-full px-3.5 py-2 rounded-xl text-xs font-mono">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Kode Invoice <span class="text-rose-400">*</span></label>
                    <input type="text" name="invoice_code" value="{{ old('invoice_code', $hotel->invoice_code) }}" required class="glass-input w-full px-3.5 py-2 rounded-xl text-xs font-mono">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Tanggal Booking <span class="text-rose-400">*</span></label>
                    <input type="date" name="booking_date" value="{{ old('booking_date', $hotel->booking_date ? $hotel->booking_date->format('Y-m-d') : '') }}" required class="glass-input w-full px-3.5 py-2 rounded-xl text-xs font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Nama Hotel <span class="text-rose-400">*</span></label>
                    <input type="text" name="hotel_name" value="{{ old('hotel_name', $hotel->hotel_name) }}" required class="glass-input w-full px-3.5 py-2 rounded-xl text-xs">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Tanggal Check In <span class="text-rose-400">*</span></label>
                    <input type="date" name="check_in_date" value="{{ old('check_in_date', $hotel->check_in_date ? $hotel->check_in_date->format('Y-m-d') : '') }}" required class="glass-input w-full px-3.5 py-2 rounded-xl text-xs font-mono">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Tanggal Check Out <span class="text-rose-400">*</span></label>
                    <input type="date" name="check_out_date" value="{{ old('check_out_date', $hotel->check_out_date ? $hotel->check_out_date->format('Y-m-d') : '') }}" required class="glass-input w-full px-3.5 py-2 rounded-xl text-xs font-mono">
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
                        <input type="text" :name="'guest_names[' + index + ']'" x-model="guests[index]" required class="glass-input flex-1 px-3.5 py-2 rounded-xl text-xs">
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
                    <input type="text" name="booked_by" value="{{ old('booked_by', $hotel->booked_by) }}" required class="glass-input w-full px-3.5 py-2 rounded-xl text-xs">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Biaya Hotel (IDR) <span class="text-rose-400">*</span></label>
                    <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount', $hotel->amount) }}" required class="glass-input w-full px-3.5 py-2 rounded-xl text-xs font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Status Pembayaran <span class="text-rose-400">*</span></label>
                    <select name="status" x-model="status" required class="glass-input w-full px-3.5 py-2 rounded-xl text-xs">
                        @foreach($statusOptions as $opt)
                            <option value="{{ $opt }}" class="bg-slate-900 text-white" {{ old('status', $hotel->status) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Penanggung Jawab Biaya (Paid By)</label>
                    <input type="text" name="paid_by" value="{{ old('paid_by', $hotel->paid_by) }}" class="glass-input w-full px-3.5 py-2 rounded-xl text-xs">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Tanggal Bayar</label>
                    <input type="date" name="payment_date" value="{{ old('payment_date', $hotel->payment_date ? $hotel->payment_date->format('Y-m-d') : '') }}" class="glass-input w-full px-3.5 py-2 rounded-xl text-xs font-mono">
                </div>
            </div>
        </div>

        <hr class="border-slate-800">

        <!-- Section 4: Catatan & Lampiran -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">Catatan Tambahan</label>
                <textarea name="notes" rows="3" class="glass-input w-full px-3.5 py-2 rounded-xl text-xs">{{ old('notes', $hotel->notes) }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">File Lampiran Bukti / Invoice (Kosongkan jika tidak diubah)</label>
                <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png" class="glass-input w-full px-3.5 py-2 rounded-xl text-xs text-slate-400 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-sky-500/20 file:text-sky-300 hover:file:bg-sky-500 hover:file:text-white">
                @if($hotel->attachment_path)
                    <div class="mt-2 text-xs">
                        <a href="{{ asset('storage/' . $hotel->attachment_path) }}" target="_blank" class="text-sky-400 hover:underline flex items-center gap-1">
                            <i class="fa-solid fa-paperclip"></i> Lihat File Lampiran Saat Ini
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Submit Button -->
        <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-800">
            <a href="{{ route('hotels.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition-all">Batal</a>
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-400 hover:to-indigo-500 text-white text-xs font-semibold shadow-lg shadow-sky-500/20 transition-all flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i> Perbarui Data Hotel
            </button>
        </div>
    </form>
</div>
@endsection
