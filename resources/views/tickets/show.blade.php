@extends('layouts.app')

@section('title', 'Detail Tiket - ' . $ticket->ticket_code)

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Breadcrumb -->
    <div class="mb-6 no-print">
        <a href="{{ route('tickets.index') }}" class="text-xs font-medium text-sky-400 hover:text-sky-300 inline-flex items-center gap-1.5 mb-2">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Tiket
        </a>
        <h1 class="font-display text-2xl font-bold text-white">Boarding Pass / E-Ticket Detail</h1>
    </div>

    <!-- E-Ticket Pass Card -->
    <div id="ticket-boarding-pass-card" class="bg-slate-900 rounded-3xl overflow-hidden shadow-2xl border border-slate-800 relative printable-card">
        <div class="bg-gradient-to-r from-sky-600 to-indigo-700 p-6 sm:p-8 text-white relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 text-white/10 text-9xl font-bold font-mono select-none">
                TCK
            </div>

            <div class="flex items-center justify-between relative z-10">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-2xl">
                        {{ $ticket->transport_icon }}
                    </div>
                    <div>
                        <p class="text-xs text-sky-200 uppercase font-mono tracking-wider">E-TICKET BOARDING PASS</p>
                        <h3 class="font-mono font-bold text-xl">{{ $ticket->ticket_code }}</h3>
                    </div>
                </div>
                <span class="px-3 py-1 text-xs font-semibold rounded-full border {{ $ticket->status_badge_class }}">
                    {{ $ticket->status }}
                </span>
            </div>

            <!-- Route Visual Banner -->
            <div class="mt-8 pt-6 border-t border-white/20 flex items-center justify-between">
                <div class="text-left">
                    <span class="text-xs text-sky-200 block uppercase">Dari (Origin)</span>
                    <span class="font-display text-2xl font-bold text-white block mt-0.5">{{ $ticket->origin }}</span>
                </div>
                <div class="px-4 text-center">
                    <i class="fa-solid fa-plane-departure text-2xl text-sky-300 animate-pulse"></i>
                </div>
                <div class="text-right">
                    <span class="text-xs text-sky-200 block uppercase">Ke (Destination)</span>
                    <span class="font-display text-2xl font-bold text-white block mt-0.5">{{ $ticket->destination }}</span>
                </div>
            </div>
        </div>

        <!-- Ticket Body Details -->
        <div class="p-6 sm:p-8 space-y-6 bg-slate-900">
            <!-- Passengers List -->
            <div class="bg-slate-950/60 p-5 rounded-2xl border border-slate-800">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs text-slate-400 font-medium flex items-center gap-1.5">
                        <i class="fa-solid fa-users text-sky-400"></i> Daftar Penumpang Tiket
                    </span>
                    <span class="text-xs font-mono font-semibold text-sky-400 bg-sky-500/10 px-2.5 py-0.5 rounded-full border border-sky-500/30">
                        {{ $ticket->passenger_count }} Penumpang
                    </span>
                </div>

                <div class="divide-y divide-slate-800/60">
                    @foreach($ticket->passengers_list as $index => $passenger)
                        <div class="py-2.5 flex items-center justify-between first:pt-0 last:pb-0">
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-lg bg-slate-800 text-slate-400 flex items-center justify-center font-mono text-xs">
                                    {{ $index + 1 }}
                                </span>
                                <span class="text-sm font-semibold text-white">{{ $passenger }}</span>
                            </div>
                            <span class="text-xs text-slate-500 font-mono">Seat Status: Confirmed</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Grid details -->
            <div class="grid grid-cols-2 gap-4 bg-slate-950/60 p-5 rounded-2xl border border-slate-800">
                <div>
                    <span class="text-xs text-slate-400 block">Tanggal Keberangkatan</span>
                    <span class="text-base font-semibold text-sky-400 mt-0.5 block">{{ $ticket->ticket_date->format('d M Y') }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Moda Transportasi</span>
                    <span class="text-sm font-semibold text-slate-200 mt-0.5 block">{{ $ticket->transport_type }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Harga / Biaya Tiket</span>
                    <span class="text-base font-bold text-emerald-400 font-mono mt-0.5 block">{{ $ticket->formatted_amount }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Dibuat Pada</span>
                    <span class="text-sm font-medium text-slate-300 mt-0.5 block">{{ $ticket->created_at->format('d M Y H:i') }}</span>
                </div>
            </div>

            <!-- Financial & Party Details -->
            <div class="grid grid-cols-2 gap-4 bg-slate-950/60 p-5 rounded-2xl border border-slate-800">
                <div>
                    <span class="text-xs text-slate-400 block">Pemesan Tiket</span>
                    <span class="text-sm font-medium text-indigo-300 mt-0.5 block">{{ $ticket->booked_by }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Pembayaran Oleh</span>
                    <span class="text-sm font-medium text-emerald-300 mt-0.5 block">{{ $ticket->paid_by }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Tanggal Pembayaran</span>
                    <span class="text-sm font-medium text-slate-300 mt-0.5 block">{{ $ticket->payment_date ? $ticket->payment_date->format('d M Y') : '-' }}</span>
                </div>
            </div>

            @if($ticket->notes)
                <div class="bg-slate-950/40 p-4 rounded-2xl border border-slate-800/80">
                    <span class="text-xs text-slate-400 block font-medium">Catatan / Keterangan</span>
                    <p class="text-sm text-slate-300 mt-1 leading-relaxed italic">{{ $ticket->notes }}</p>
                </div>
            @endif

            @if($ticket->attachment_path)
                <div>
                    <a href="{{ asset('storage/' . $ticket->attachment_path) }}" target="_blank" class="inline-flex items-center justify-center w-full px-4 py-3 rounded-xl text-xs font-semibold text-white bg-slate-800 hover:bg-slate-700 border border-slate-700 transition-colors">
                        <i class="fa-solid fa-paperclip text-sky-400 mr-2 text-sm"></i> Unduh / Buka File Bukti Tiket (Nota)
                    </a>
                </div>
            @endif

            <!-- Status Change Timeline & Logs -->
            <div class="bg-slate-950/60 p-5 rounded-2xl border border-slate-800 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-list-check text-sky-400"></i> Riwayat Step Status Tiket
                    </span>
                    <span class="text-[10px] text-slate-500 font-mono">Sequential Activity Log</span>
                </div>

                @if($ticket->statusLogs && $ticket->statusLogs->count() > 0)
                    <div class="relative pl-6 space-y-4 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-800">
                        @foreach($ticket->statusLogs as $stepIndex => $log)
                            <div class="relative flex items-start justify-between gap-3">
                                <div class="absolute -left-6 top-1 w-5 h-5 rounded-full bg-slate-900 border-2 border-sky-400 flex items-center justify-center text-[10px] font-mono font-bold text-sky-300">
                                    {{ $stepIndex + 1 }}
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $log->status_badge_class }}">
                                            {{ $log->to_status }}
                                        </span>
                                        @if($log->from_status)
                                            <span class="text-xs text-slate-500 font-mono">(dari {{ $log->from_status }})</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-300 mt-1 leading-relaxed">{{ $log->notes }}</p>
                                    <span class="text-[11px] text-slate-500 font-mono mt-0.5 block">
                                        <i class="fa-solid fa-user text-slate-600 mr-1"></i> {{ $log->user_name }} ({{ ucfirst($log->user_role) }}) &bull; {{ $log->created_at->format('d M Y, H:i') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-500 italic">Belum ada riwayat aktivitas log tercatat.</p>
                @endif
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between pt-2 no-print">
                @can('update', $ticket)
                    <a href="{{ route('tickets.edit', $ticket->id) }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-amber-300 bg-amber-500/10 border border-amber-500/30 hover:bg-amber-500 hover:text-white transition-all">
                        <i class="fa-solid fa-pen-to-square mr-1.5"></i> Edit Tiket Ini
                    </a>
                @endcan

                <a href="{{ route('tickets.pdf', $ticket->id) }}" target="_blank" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-white bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-400 hover:to-indigo-500 shadow-lg shadow-sky-500/20 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-file-pdf text-sm"></i> Download / Cetak PDF Boarding Pass
                </a>
            </div>
        </div>

        <div class="bg-slate-950 p-6 border-t border-slate-800 text-center">
            <div class="font-mono text-3xl tracking-[0.4em] text-slate-600 select-none">
                ||||| ||| ||||||| ||| ||||| ||||
            </div>
            <span class="text-xs text-slate-500 block font-mono mt-2">VERIFIED HISTORICAL TICKET RECORD &bull; {{ $ticket->ticket_code }}</span>
        </div>
    </div>
</div>
@endsection
