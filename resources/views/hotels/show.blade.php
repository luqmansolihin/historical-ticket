@extends('layouts.app')

@section('title', 'Detail Hotel - ' . ($hotel->booking_code ?: $hotel->hotel_name))

@section('content')
<div x-data="{ showModal: false }" class="max-w-4xl mx-auto space-y-4">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <a href="{{ route('hotels.index') }}" class="text-xs font-semibold text-amber-400 hover:text-amber-300 flex items-center gap-1 mb-1">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Histori Hotel
            </a>
            <h1 class="text-lg sm:text-xl font-bold font-display tracking-tight text-white flex items-center gap-2">
                <i class="fa-solid fa-hotel text-amber-400"></i> {{ $hotel->hotel_name }}
            </h1>
            <p class="text-slate-400 text-xs sm:text-sm">Kode Booking: <span class="font-mono text-amber-400 font-semibold">{{ $hotel->booking_code ?: '-' }}</span> • Invoice: <span class="font-mono text-indigo-300 font-semibold">{{ $hotel->invoice_code }}</span></p>
        </div>

        <div class="flex items-center gap-2">
            <!-- Preview HTML & Stream PDF Button -->
            <button type="button" @click="showModal = true" class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-amber-600 hover:bg-amber-500 shadow-lg transition-all flex items-center gap-1.5">
                <i class="fa-solid fa-file-pdf"></i> Preview & Cetak Voucher Hotel
            </button>

            @can('update', $hotel)
                <a href="{{ route('hotels.edit', $hotel->id) }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-amber-300 bg-amber-500/10 border border-amber-500/30 hover:bg-amber-500 hover:text-white transition-all flex items-center gap-1.5">
                    <i class="fa-solid fa-pen-to-square"></i> Edit
                </a>
            @endcan

            @can('delete', $hotel)
                <form action="{{ route('hotels.destroy', $hotel->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data hotel ini? Data yang dihapus tidak dapat dikembalikan.');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold text-rose-300 bg-rose-500/10 border border-rose-500/30 hover:bg-rose-600 hover:text-white transition-all flex items-center gap-1.5">
                        <i class="fa-solid fa-trash-can"></i> Hapus
                    </button>
                </form>
            @endcan
        </div>
    </div>

    <!-- Voucher Detail Card -->
    <div class="glass-card p-5 sm:p-6 rounded-2xl border border-slate-800 space-y-5">
        <!-- Status & Dates Bar -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4 rounded-xl bg-slate-900/60 border border-slate-800">
            <div>
                <div class="text-[10px] uppercase font-mono text-slate-400">Status Pembayaran</div>
                <span class="inline-block mt-1 px-2.5 py-0.5 text-xs font-semibold rounded-full border {{ $hotel->status_badge_class }}">
                    {{ $hotel->status }}
                </span>
            </div>

            <div>
                <div class="text-[10px] uppercase font-mono text-slate-400">Tanggal Check In</div>
                <div class="text-xs font-bold text-emerald-400 mt-1 font-mono">
                    {{ $hotel->check_in_date ? $hotel->check_in_date->format('d M Y') : '-' }}
                </div>
            </div>

            <div>
                <div class="text-[10px] uppercase font-mono text-slate-400">Tanggal Check Out</div>
                <div class="text-xs font-bold text-rose-400 mt-1 font-mono">
                    {{ $hotel->check_out_date ? $hotel->check_out_date->format('d M Y') : '-' }}
                </div>
            </div>

            <div>
                <div class="text-[10px] uppercase font-mono text-slate-400">Durasi Menginap</div>
                <div class="text-xs font-bold text-amber-300 mt-1 font-mono">
                    {{ $hotel->night_count }} Malam
                </div>
            </div>
        </div>

        <!-- Guests & Amount Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="p-4 rounded-xl bg-slate-900/40 border border-slate-800">
                <div class="text-[10px] uppercase font-mono text-slate-400 mb-1.5 flex items-center gap-1.5">
                    <i class="fa-solid fa-users text-amber-400"></i> Tamu yang Menginap ({{ $hotel->guest_count }} Orang)
                </div>
                <ul class="list-disc list-inside text-xs text-slate-200 font-medium space-y-1">
                    @foreach($hotel->guests_list as $guest)
                        <li>{{ $guest }}</li>
                    @endforeach
                </ul>
            </div>

            <div class="p-4 rounded-xl bg-slate-900/40 border border-slate-800">
                <div class="text-[10px] uppercase font-mono text-slate-400 mb-1.5 flex items-center gap-1.5">
                    <i class="fa-solid fa-receipt text-emerald-400"></i> Total Biaya Reservasi
                </div>
                <div class="text-xl font-bold font-mono text-emerald-400 mt-1">
                    {{ $hotel->formatted_amount }}
                </div>
                <div class="text-[11px] text-slate-400 mt-1">
                    Booked by: <span class="text-indigo-300 font-semibold">{{ $hotel->booked_by }}</span>
                </div>
                <div class="text-[11px] text-slate-400">
                    Paid by: <span class="text-emerald-300 font-semibold">{{ $hotel->paid_by }}</span> @if($hotel->payment_date) ({{ $hotel->payment_date->format('d M Y') }}) @endif
                </div>
            </div>
        </div>

        @if($hotel->notes)
            <div class="p-4 rounded-xl bg-slate-900/40 border border-slate-800">
                <div class="text-[10px] uppercase font-mono text-slate-400 mb-1">Catatan</div>
                <p class="text-xs text-slate-300">{{ $hotel->notes }}</p>
            </div>
        @endif

        @if($hotel->attachment_path)
            <div class="p-4 rounded-xl bg-slate-900/40 border border-slate-800 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-paperclip text-amber-400 text-sm"></i>
                    <span class="text-xs font-semibold text-slate-200">Lampiran Bukti Pembayaran / Invoice</span>
                </div>
                <a href="{{ asset('storage/' . $hotel->attachment_path) }}" target="_blank" class="px-3 py-1.5 rounded-xl bg-amber-500/20 text-amber-300 hover:bg-amber-500 hover:text-slate-950 border border-amber-500/30 text-xs font-semibold transition-all">
                    Buka Lampiran
                </a>
            </div>
        @endif

        <!-- Status Activity Logs -->
        <div class="pt-2">
            <h4 class="text-xs font-mono font-bold text-slate-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-amber-400"></i> Log Aktivitas Perubahan Status
            </h4>
            <div class="space-y-2">
                @forelse($hotel->statusLogs as $log)
                    <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800/80 flex items-start justify-between gap-3 text-xs">
                        <div>
                            <div class="font-semibold text-slate-200 flex items-center gap-2">
                                <span class="text-amber-300">{{ $log->user_name }}</span>
                                <span class="text-[10px] px-2 py-0.5 rounded bg-slate-800 text-slate-400 font-mono capitalize">{{ $log->user_role ?? 'user' }}</span>
                            </div>
                            <p class="text-slate-400 text-[11px] mt-0.5">{{ $log->notes }}</p>
                        </div>
                        <div class="text-[10px] font-mono text-slate-500 shrink-0 text-right">
                            {{ $log->created_at->format('d M Y, H:i') }}
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 italic">Belum ada catatan aktivitas.</p>
                @endforelse
            </div>
        </div>
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
                                <span class="text-xs text-amber-200 block uppercase">Durasi</span>
                                <span class="font-display text-lg font-bold text-amber-300 block mt-0.5">{{ $hotel->night_count }} Malam</span>
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
                                <span class="text-xs font-mono font-semibold text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded-full border border-amber-500/30">{{ $hotel->guest_count }} Tamu</span>
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

                        <div class="grid grid-cols-3 gap-4 bg-slate-950/60 p-4 rounded-2xl border border-slate-800">
                            <div>
                                <span class="text-xs text-slate-400 block">Pemesan Hotel</span>
                                <span class="text-xs font-semibold text-indigo-300 mt-0.5 block">{{ $hotel->booked_by }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-slate-400 block">Pembayaran Oleh</span>
                                <span class="text-xs font-semibold text-emerald-300 mt-0.5 block">{{ $hotel->paid_by }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-slate-400 block">Tanggal Bayar</span>
                                <span class="text-xs font-semibold text-slate-300 mt-0.5 font-mono block">{{ $hotel->payment_date ? $hotel->payment_date->format('d M Y') : '-' }}</span>
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
