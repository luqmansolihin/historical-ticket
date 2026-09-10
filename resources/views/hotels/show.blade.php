@extends('layouts.app')

@section('title', 'Detail Hotel - ' . ($hotel->booking_code ?: $hotel->hotel_name))

@section('content')
<div class="max-w-4xl mx-auto space-y-4">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <a href="{{ route('hotels.index') }}" class="text-xs font-semibold text-sky-400 hover:text-sky-300 flex items-center gap-1 mb-1">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Histori Hotel
            </a>
            <h1 class="text-lg sm:text-xl font-bold font-display tracking-tight text-white flex items-center gap-2">
                <i class="fa-solid fa-hotel text-amber-400"></i> {{ $hotel->hotel_name }}
            </h1>
            <p class="text-slate-400 text-xs sm:text-sm">Kode Booking: <span class="font-mono text-sky-400 font-semibold">{{ $hotel->booking_code ?: '-' }}</span> • Invoice: <span class="font-mono text-indigo-300 font-semibold">{{ $hotel->invoice_code }}</span></p>
        </div>

        <div class="flex items-center gap-2">
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

            <a href="{{ route('hotels.pdf', $hotel->id) }}" target="_blank" class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-amber-600 hover:bg-amber-500 shadow-lg transition-all flex items-center gap-1.5">
                <i class="fa-solid fa-file-pdf"></i> Download PDF Voucher
            </a>
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
                    <i class="fa-solid fa-users text-sky-400"></i> Tamu yang Menginap ({{ $hotel->guest_count }} Orang)
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
                    <i class="fa-solid fa-paperclip text-sky-400 text-sm"></i>
                    <span class="text-xs font-semibold text-slate-200">Lampiran Bukti Pembayaran / Invoice</span>
                </div>
                <a href="{{ asset('storage/' . $hotel->attachment_path) }}" target="_blank" class="px-3 py-1.5 rounded-xl bg-sky-500/20 text-sky-300 hover:bg-sky-500 hover:text-white border border-sky-500/30 text-xs font-semibold transition-all">
                    Buka Lampiran
                </a>
            </div>
        @endif

        <!-- Status Activity Logs -->
        <div class="pt-2">
            <h4 class="text-xs font-mono font-bold text-slate-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-sky-400"></i> Log Aktivitas Perubahan Status
            </h4>
            <div class="space-y-2">
                @forelse($hotel->statusLogs as $log)
                    <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800/80 flex items-start justify-between gap-3 text-xs">
                        <div>
                            <div class="font-semibold text-slate-200 flex items-center gap-2">
                                <span class="text-sky-300">{{ $log->user_name }}</span>
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
</div>
@endsection
