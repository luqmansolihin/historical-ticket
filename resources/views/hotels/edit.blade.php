@extends('layouts.app')

@section('title', 'Edit Histori Hotel' . ($hotel->booking_code ? ' - ' . $hotel->booking_code : ''))

@section('content')
@php
    $isAdmin = Auth::user()->isAdmin();
    $isFinance = Auth::user()->isFinance() && !$isAdmin;
    $isLunas = $hotel->status === 'Lunas';
    
    $isBookerLunas = $isFinance && $isLunas;
    $isDataLocked = $isBookerLunas;
@endphp

<div x-data="{ 
    showModal: false,
    guests: {{ json_encode(old('guest_names', $hotel->guests_list ?: [$hotel->guest_name])) }},
    addGuest() { this.guests.push(''); },
    removeGuest(index) { if (this.guests.length > 1) this.guests.splice(index, 1); },
    status: '{{ old('status', $hotel->status) }}'
}" class="max-w-4xl mx-auto min-w-0 w-full pb-12">

    <!-- Header & Action Buttons -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('hotels.index') }}" class="text-xs font-medium text-amber-400 hover:text-amber-300 inline-flex items-center gap-1.5 mb-2">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Histori Hotel
            </a>
            <h1 class="font-display text-2xl sm:text-3xl font-bold text-white leading-tight">Edit Histori Hotel</h1>
            <p class="text-slate-400 text-xs sm:text-sm mt-1">Perbarui data reservasi <span class="font-mono text-amber-400 font-semibold">{{ $hotel->booking_code ?: '-' }}</span></p>
        </div>

        <!-- Tombol Preview HTML & Cetak PDF / Hapus di Header -->
        <div class="flex items-center gap-2">
            <button type="button" @click="showModal = true" class="w-full sm:w-auto px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-amber-600 hover:bg-amber-500 shadow-lg shadow-amber-500/20 transition-all flex items-center justify-center gap-2">
                <i class="fa-solid fa-file-pdf text-sm"></i> Preview & Cetak Voucher Hotel
            </button>
            @can('delete', $hotel)
                <form action="{{ route('hotels.destroy', $hotel->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data hotel ini? Data yang dihapus tidak dapat dikembalikan.');" class="inline">
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
            <div class="mb-6 p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs flex items-center gap-3">
                <i class="fa-solid fa-lock text-xl text-amber-400 shrink-0"></i>
                <div>
                    <span class="font-bold block text-sm">Reservasi Hotel Berstatus Lunas — Mode Pembatasan Akses (Finance)</span>
                    <span>Data hotel, tamu, dan biaya telah dikunci karena pembayaran sudah <strong>Lunas</strong>. Sebagai Finance, Anda diperbolehkan mengedit <strong>Tanggal Pembayaran</strong> atau mengubah status menjadi <strong>Dibatalkan</strong>.</span>
                </div>
            </div>
        @endif

        <form action="{{ route('hotels.update', $hotel->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            @if($isDataLocked)
                <input type="hidden" name="booking_code" value="{{ old('booking_code', $hotel->booking_code) }}">
                <input type="hidden" name="invoice_code" value="{{ old('invoice_code', $hotel->invoice_code) }}">
                <input type="hidden" name="booking_date" value="{{ old('booking_date', $hotel->booking_date ? $hotel->booking_date->format('Y-m-d') : '') }}">
                <input type="hidden" name="hotel_name" value="{{ old('hotel_name', $hotel->hotel_name) }}">
                <input type="hidden" name="check_in_date" value="{{ old('check_in_date', $hotel->check_in_date ? $hotel->check_in_date->format('Y-m-d') : '') }}">
                <input type="hidden" name="check_out_date" value="{{ old('check_out_date', $hotel->check_out_date ? $hotel->check_out_date->format('Y-m-d') : '') }}">
                <input type="hidden" name="amount" value="{{ old('amount', $hotel->amount) }}">
                <input type="hidden" name="booked_by" value="{{ old('booked_by', $hotel->booked_by) }}">
                <input type="hidden" name="booked_by_user_id" value="{{ old('booked_by_user_id', $hotel->booked_by_user_id) }}">
                @foreach($hotel->guests_list as $gIdx => $gName)
                    <input type="hidden" name="guest_names[{{ $gIdx }}]" value="{{ $gName }}">
                @endforeach
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

            <!-- Section 1: Informasi Reservasi & Hotel -->
            <div>
                <h3 class="text-xs font-mono font-bold text-amber-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-hotel"></i> Data Reservasi & Hotel
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Kode Booking Hotel</label>
                        <input type="text" name="booking_code" value="{{ old('booking_code', $hotel->booking_code) }}" {{ $isDataLocked ? 'disabled' : '' }} class="glass-input w-full px-3.5 py-2.5 rounded-xl text-xs font-mono disabled:opacity-60 disabled:cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Kode Invoice <span class="text-rose-400">*</span></label>
                        <input type="text" name="invoice_code" value="{{ old('invoice_code', $hotel->invoice_code) }}" required {{ $isDataLocked ? 'disabled' : '' }} class="glass-input w-full px-3.5 py-2.5 rounded-xl text-xs font-mono disabled:opacity-60 disabled:cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Tanggal Booking <span class="text-rose-400">*</span></label>
                        <input type="date" name="booking_date" value="{{ old('booking_date', $hotel->booking_date ? $hotel->booking_date->format('Y-m-d') : '') }}" required {{ $isDataLocked ? 'disabled' : '' }} class="glass-input w-full px-3.5 py-2.5 rounded-xl text-xs font-mono disabled:opacity-60 disabled:cursor-not-allowed">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-slate-300 mb-1">Nama Hotel <span class="text-rose-400">*</span></label>
                        <input type="text" name="hotel_name" value="{{ old('hotel_name', $hotel->hotel_name) }}" required {{ $isDataLocked ? 'disabled' : '' }} class="glass-input w-full px-3.5 py-2.5 rounded-xl text-xs disabled:opacity-60 disabled:cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Jumlah Kamar <span class="text-rose-400">*</span></label>
                        <input type="number" name="room_count" value="{{ old('room_count', $hotel->room_count) }}" min="1" required {{ $isDataLocked ? 'disabled' : '' }} placeholder="Jumlah kamar..." class="glass-input w-full px-3.5 py-2.5 rounded-xl text-xs font-mono disabled:opacity-60 disabled:cursor-not-allowed">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Tanggal Check In <span class="text-rose-400">*</span></label>
                        <input type="date" name="check_in_date" value="{{ old('check_in_date', $hotel->check_in_date ? $hotel->check_in_date->format('Y-m-d') : '') }}" required {{ $isDataLocked ? 'disabled' : '' }} class="glass-input w-full px-3.5 py-2.5 rounded-xl text-xs font-mono disabled:opacity-60 disabled:cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Tanggal Check Out <span class="text-rose-400">*</span></label>
                        <input type="date" name="check_out_date" value="{{ old('check_out_date', $hotel->check_out_date ? $hotel->check_out_date->format('Y-m-d') : '') }}" required {{ $isDataLocked ? 'disabled' : '' }} class="glass-input w-full px-3.5 py-2.5 rounded-xl text-xs font-mono disabled:opacity-60 disabled:cursor-not-allowed">
                    </div>
                </div>
            </div>

            <hr class="border-slate-800">

            <!-- Section 2: Daftar Tamu Menginap -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xs font-mono font-bold text-amber-400 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-users"></i> Tamu yang Menginap <span class="text-rose-400">*</span>
                    </h3>
                    @if(!$isDataLocked)
                        <button type="button" @click="addGuest()" class="px-3.5 py-1.5 rounded-xl bg-amber-500/20 hover:bg-amber-500 text-amber-300 hover:text-slate-950 border border-amber-500/30 text-xs font-semibold transition-all flex items-center gap-1.5">
                            <i class="fa-solid fa-plus text-xs"></i> Tambah Tamu
                        </button>
                    @endif
                </div>

                <div class="space-y-2.5">
                    <template x-for="(guest, index) in guests" :key="index">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center font-mono text-xs font-bold text-slate-400 shrink-0" x-text="index + 1"></div>
                            <input type="text" :name="'guest_names[' + index + ']'" x-model="guests[index]" required {{ $isDataLocked ? 'disabled' : '' }} class="glass-input flex-1 px-3.5 py-2.5 rounded-xl text-xs disabled:opacity-60 disabled:cursor-not-allowed">
                            @if(!$isDataLocked)
                                <button type="button" @click="removeGuest(index)" x-show="guests.length > 1" class="p-2.5 rounded-xl bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-white border border-rose-500/30 transition-all shrink-0">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            @endif
                        </div>
                    </template>
                </div>
            </div>

            <hr class="border-slate-800">

            <!-- Section 3: Pemesan & Pembayaran -->
            <div>
                <h3 class="text-xs font-mono font-bold text-amber-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-wallet"></i> Detail Pemesan & Pembayaran
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Nama Pemesan (Booked By) <span class="text-rose-400">*</span></label>
                        <input type="text" name="booked_by" value="{{ old('booked_by', $hotel->booked_by) }}" required {{ $isDataLocked ? 'disabled' : '' }} class="glass-input w-full px-3.5 py-2.5 rounded-xl text-xs disabled:opacity-60 disabled:cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Biaya Hotel (IDR) <span class="text-rose-400">*</span></label>
                        <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount', $hotel->amount) }}" required {{ $isDataLocked ? 'disabled' : '' }} class="glass-input w-full px-3.5 py-2.5 rounded-xl text-xs font-mono disabled:opacity-60 disabled:cursor-not-allowed">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Status Pembayaran <span class="text-rose-400">*</span></label>
                        <select name="status" x-model="status" required class="glass-input w-full px-3.5 py-2.5 rounded-xl text-xs">
                            @foreach($statusOptions as $opt)
                                @if($isDataLocked && $opt === 'Belum Bayar')
                                    @continue
                                @endif
                                <option value="{{ $opt }}" class="bg-slate-900 text-white" {{ old('status', $hotel->status) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Penanggung Jawab Biaya (Paid By)</label>
                        <input type="text" name="paid_by" value="{{ old('paid_by', $hotel->paid_by) }}" class="glass-input w-full px-3.5 py-2.5 rounded-xl text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Tanggal Bayar</label>
                        <input type="date" name="payment_date" value="{{ old('payment_date', $hotel->payment_date ? $hotel->payment_date->format('Y-m-d') : '') }}" class="glass-input w-full px-3.5 py-2.5 rounded-xl text-xs font-mono">
                    </div>
                </div>
            </div>

            <hr class="border-slate-800">

            <!-- Section 4: Catatan & Lampiran -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Catatan Tambahan</label>
                    <textarea name="notes" rows="3" {{ $isDataLocked ? 'disabled' : '' }} class="glass-input w-full px-3.5 py-2.5 rounded-xl text-xs disabled:opacity-60 disabled:cursor-not-allowed">{{ old('notes', $hotel->notes) }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">File Lampiran Bukti / Invoice (Kosongkan jika tidak diubah)</label>
                    <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png" class="glass-input w-full px-3.5 py-2.5 rounded-xl text-xs text-slate-400 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-amber-500/20 file:text-amber-300 hover:file:bg-amber-500 hover:file:text-slate-950">
                    @if($hotel->attachment_path)
                        <div class="mt-2 text-xs">
                            <a href="{{ asset('storage/' . $hotel->attachment_path) }}" target="_blank" class="text-amber-400 hover:underline flex items-center gap-1">
                                <i class="fa-solid fa-paperclip"></i> Lihat File Lampiran Saat Ini
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <a href="{{ route('hotels.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition-all">Batal</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-bold text-xs shadow-lg shadow-amber-500/20 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> Perbarui Data Hotel
                </button>
            </div>
        </form>
    </div>

    <!-- HTML Preview Modal for Voucher Hotel -->
    <div x-cloak x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="showModal = false" class="fixed inset-0 bg-slate-950/85 backdrop-blur-md transition-opacity no-print"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div id="modal-hotel-voucher-card" x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-slate-900 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-800 relative printable-card">
                <div class="p-0">
                    <!-- HTML Voucher Header Banner -->
                    <div class="bg-gradient-to-r from-amber-600 via-amber-700 to-indigo-800 p-6 text-white relative overflow-hidden">
                        <div class="absolute -right-6 -bottom-6 text-white/10 text-9xl font-bold font-mono select-none">
                            HTL
                        </div>

                        <div class="flex items-center justify-between relative z-10">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white text-lg">
                                    <i class="fa-solid fa-hotel"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-amber-200 uppercase font-mono tracking-wider">HOTEL RESERVATION VOUCHER</p>
                                    <h3 class="font-mono font-bold text-lg">{{ $hotel->hotel_name }}</h3>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 no-print">
                                <!-- Stream / Download PDF Button -->
                                <a href="{{ route('hotels.pdf', $hotel->id) }}" target="_blank" class="px-3.5 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs flex items-center gap-1.5 transition-colors shadow-sm" title="Download PDF Voucher Hotel">
                                    <i class="fa-solid fa-file-pdf"></i> Download PDF
                                </a>

                                <button type="button" @click="showModal = false" class="w-8 h-8 rounded-full bg-black/20 hover:bg-black/40 flex items-center justify-center text-white transition-colors">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-white/20 grid grid-cols-3 gap-3">
                            <div>
                                <span class="text-xs text-amber-200 block uppercase">Check In</span>
                                <span class="font-display text-lg font-bold text-white block mt-0.5">{{ $hotel->check_in_date ? $hotel->check_in_date->format('d M Y') : '-' }}</span>
                            </div>
                            <div class="text-center">
                                <span class="text-xs text-amber-200 block uppercase">Durasi & Kamar</span>
                                <span class="font-display text-lg font-bold text-amber-300 block mt-0.5">{{ $hotel->night_count }} Malam • {{ $hotel->room_count }} Kamar</span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs text-amber-200 block uppercase">Check Out</span>
                                <span class="font-display text-lg font-bold text-white block mt-0.5">{{ $hotel->check_out_date ? $hotel->check_out_date->format('d M Y') : '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- HTML Voucher Body -->
                    <div class="p-6 space-y-5 bg-slate-900 text-xs">
                        <!-- Guest List Section -->
                        <div class="bg-slate-950/60 p-4 rounded-2xl border border-slate-800">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs text-slate-400 flex items-center gap-1.5 font-medium">
                                    <i class="fa-solid fa-users text-amber-400"></i> Daftar Tamu Menginap
                                </span>
                                <span class="text-xs font-mono font-semibold text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded-full border border-amber-500/30">{{ $hotel->guest_count }} Tamu ({{ $hotel->room_count }} Kamar)</span>
                            </div>
                            <div class="space-y-1">
                                @foreach($hotel->guests_list as $idx => $gName)
                                    <div class="flex items-center gap-2 text-sm text-slate-100 font-medium py-1 border-b border-slate-800/40 last:border-0">
                                        <span class="text-xs font-mono text-slate-500">{{ $idx + 1 }}.</span>
                                        <span>{{ $gName }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Core Details Grid -->
                        <div class="grid grid-cols-2 gap-4 bg-slate-950/60 p-4 rounded-2xl border border-slate-800">
                            <div>
                                <span class="text-xs text-slate-400 block">Kode Booking</span>
                                <span class="text-sm font-semibold font-mono text-amber-400 mt-0.5 block">{{ $hotel->booking_code ?: '-' }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-slate-400 block">Kode Invoice</span>
                                <span class="text-sm font-semibold font-mono text-indigo-300 mt-0.5 block">{{ $hotel->invoice_code }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-slate-400 block">Status Pembayaran</span>
                                <span class="inline-block mt-1 px-2.5 py-0.5 text-xs font-semibold rounded-full border {{ $hotel->status_badge_class }}">{{ $hotel->status }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-slate-400 block">Biaya Reservasi</span>
                                <span class="text-base font-bold text-emerald-400 font-mono mt-0.5 block">{{ $hotel->formatted_amount }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 bg-slate-950/60 p-4 rounded-2xl border border-slate-800">
                            <div>
                                <span class="text-xs text-slate-400 block">Pemesan Hotel</span>
                                <span class="text-xs font-semibold text-indigo-300 mt-0.5 block">{{ $hotel->booked_by }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-slate-400 block">Pembayaran Oleh</span>
                                <span class="text-xs font-semibold text-emerald-300 mt-0.5 block">{{ $hotel->paid_by }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-slate-400 block">Tanggal Booking</span>
                                <span class="text-xs font-semibold font-mono text-slate-200 mt-0.5 block">{{ $hotel->booking_date ? $hotel->booking_date->format('d M Y') : '-' }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-slate-400 block">Tanggal Bayar</span>
                                <span class="text-xs font-semibold font-mono text-slate-200 mt-0.5 block">{{ $hotel->payment_date ? $hotel->payment_date->format('d M Y') : '-' }}</span>
                            </div>
                        </div>

                        @if($hotel->notes)
                            <div class="bg-slate-950/60 p-4 rounded-2xl border border-slate-800">
                                <span class="text-xs text-slate-400 block mb-1">Catatan</span>
                                <p class="text-slate-300 italic">{{ $hotel->notes }}</p>
                            </div>
                        @endif

                        <!-- Activity Timeline Logs in HTML Preview -->
                        @if($hotel->statusLogs->count() > 0)
                            <div class="bg-slate-950/60 p-4 rounded-2xl border border-slate-800 space-y-2">
                                <span class="text-xs font-bold text-slate-400 block uppercase tracking-wider mb-2">Riwayat Log Aktivitas Status</span>
                                @foreach($hotel->statusLogs as $log)
                                    <div class="p-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs">
                                        <div class="flex items-center justify-between">
                                            <span class="font-semibold text-amber-400">{{ $log->to_status }}</span>
                                            <span class="text-[10px] font-mono text-slate-500">{{ $log->created_at->format('d M Y, H:i') }}</span>
                                        </div>
                                        <p class="text-slate-400 text-[11px] mt-0.5">{{ $log->notes }}</p>
                                        <div class="text-[10px] text-slate-500 font-mono mt-1">{{ $log->user_name }} ({{ ucfirst($log->user_role ?? 'user') }})</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
