@extends('layouts.app')

@section('title', 'Daftar Histori Tiket')

@section('content')
<div x-data="{ selectedTicket: null, showModal: false }">

    <!-- ERP Data Table Container & Column Header Filters -->
    <div x-data="{ openPop: null }" class="glass-card rounded-2xl overflow-hidden shadow-2xl relative z-10 no-print">
        <!-- ERP Data Grid Toolbar -->
        <div class="px-5 py-3.5 bg-slate-900/90 border-b border-slate-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-lg bg-sky-500/10 border border-sky-500/20 flex items-center justify-center text-sky-400">
                    <i class="fa-solid fa-database text-sm"></i>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-white font-mono tracking-tight flex items-center gap-2">
                        <span>DATA HISTORI TIKET</span>
                        <span class="text-[11px] font-mono px-2 py-0.5 rounded-full bg-sky-500/10 text-sky-400 border border-sky-500/30">
                            {{ $totalTickets }} Tiket
                        </span>
                    </h2>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('tickets.export', request()->query()) }}" class="inline-flex items-center px-3.5 py-1.5 rounded-lg text-xs font-medium text-slate-200 bg-slate-800 hover:bg-slate-700 border border-slate-700 transition-all shadow-sm">
                    <i class="fa-solid fa-file-csv text-emerald-400 mr-1.5 text-xs"></i> Export CSV
                </a>

                @can('create', App\Models\TicketHistory::class)
                    <a href="{{ route('tickets.create') }}" class="inline-flex items-center px-4 py-1.5 rounded-lg text-xs font-semibold text-white bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-400 hover:to-blue-500 shadow-md shadow-sky-500/20 transition-all active:scale-95">
                        <i class="fa-solid fa-plus mr-1.5"></i> Tambah Tiket Baru
                    </a>
                @endcan
            </div>
        </div>

        <form action="{{ route('tickets.index') }}" method="GET">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300 whitespace-nowrap">
                    <thead class="bg-slate-900/90 text-xs uppercase font-semibold text-slate-400 tracking-wider border-b border-slate-800 whitespace-nowrap">
                        <tr>
                            <!-- 1. Kode Tiket -->
                            <th class="py-3.5 px-4 whitespace-nowrap relative">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <span>Kode Tiket</span>
                                    <button type="button" @click="openPop = (openPop === 'code' ? null : 'code')" @click.outside="if (openPop === 'code') openPop = null" class="p-1 rounded hover:bg-slate-800 transition-colors {{ $search ? 'text-sky-400 font-bold bg-sky-500/20' : 'text-slate-500 hover:text-slate-300' }}" title="Filter Kode Tiket">
                                        <i class="fa-solid fa-caret-down text-xs"></i>
                                    </button>
                                </div>
                                <!-- Popover Filter Kode Tiket -->
                                <div x-show="openPop === 'code'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-3 text-left font-normal normal-case min-w-[220px]">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Filter Keyword / Kode</span>
                                        <i class="fa-solid fa-magnifying-glass text-sky-400"></i>
                                    </div>
                                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari kode, kota, nama..." class="w-full h-9 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 placeholder-slate-500 focus:border-sky-400 focus:outline-none">
                                    <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-800/80">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow transition-colors">
                                            Terapkan
                                        </button>
                                    </div>
                                </div>
                            </th>

                            <!-- 2. Tanggal Tiket -->
                            <th class="py-3.5 px-4 whitespace-nowrap relative">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <span>Tgl Tiket</span>
                                    <button type="button" @click="openPop = (openPop === 'date' ? null : 'date')" @click.outside="if (openPop === 'date') openPop = null" class="p-1 rounded hover:bg-slate-800 transition-colors {{ ($dateFrom || $dateTo) ? 'text-sky-400 font-bold bg-sky-500/20' : 'text-slate-500 hover:text-slate-300' }}" title="Filter Tanggal Tiket">
                                        <i class="fa-solid fa-caret-down text-xs"></i>
                                    </button>
                                </div>
                                <!-- Popover Filter Tanggal -->
                                <div x-show="openPop === 'date'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3.5 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-3 text-left font-normal normal-case min-w-[250px]">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Filter Tanggal Tiket</span>
                                        <i class="fa-regular fa-calendar-days text-sky-400"></i>
                                    </div>
                                    <div class="space-y-2">
                                        <div>
                                            <label class="block text-[11px] text-slate-400 mb-1">Dari Tanggal:</label>
                                            <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full h-8 rounded-lg px-2 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 focus:border-sky-400 focus:outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] text-slate-400 mb-1">Sampai Tanggal:</label>
                                            <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full h-8 rounded-lg px-2 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 focus:border-sky-400 focus:outline-none">
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-800/80">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow transition-colors">
                                            Terapkan
                                        </button>
                                    </div>
                                </div>
                            </th>

                            <!-- 3. Rute & Penumpang -->
                            <th class="py-3.5 px-4 whitespace-nowrap relative">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <span>Rute & Penumpang</span>
                                    <button type="button" @click="openPop = (openPop === 'route' ? null : 'route')" @click.outside="if (openPop === 'route') openPop = null" class="p-1 rounded hover:bg-slate-800 transition-colors {{ $search ? 'text-sky-400 font-bold bg-sky-500/20' : 'text-slate-500 hover:text-slate-300' }}" title="Filter Rute & Penumpang">
                                        <i class="fa-solid fa-caret-down text-xs"></i>
                                    </button>
                                </div>
                                <!-- Popover Filter Rute -->
                                <div x-show="openPop === 'route'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-3 text-left font-normal normal-case min-w-[220px]">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Cari Kota / Penumpang</span>
                                        <i class="fa-solid fa-route text-sky-400"></i>
                                    </div>
                                    <input type="text" name="search" value="{{ $search }}" placeholder="Contoh: Jakarta, Luqman..." class="w-full h-9 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 placeholder-slate-500 focus:border-sky-400 focus:outline-none">
                                    <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-800/80">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow transition-colors">
                                            Terapkan
                                        </button>
                                    </div>
                                </div>
                            </th>

                            <!-- 4. Transportasi -->
                            <th class="py-3.5 px-4 whitespace-nowrap relative">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <span>Transportasi</span>
                                    <button type="button" @click="openPop = (openPop === 'transport' ? null : 'transport')" @click.outside="if (openPop === 'transport') openPop = null" class="p-1 rounded hover:bg-slate-800 transition-colors {{ !empty($transportType) ? 'text-sky-400 font-bold bg-sky-500/20' : 'text-slate-500 hover:text-slate-300' }}" title="Filter Transportasi">
                                        <i class="fa-solid fa-caret-down text-xs"></i>
                                    </button>
                                </div>
                                <!-- Popover Filter Transportasi -->
                                <div x-show="openPop === 'transport'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-2 text-left font-normal normal-case min-w-[200px]" x-data="{ selected: {{ json_encode($transportType) }} }">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Pilih Transportasi</span>
                                        <i class="fa-solid fa-plane-departure text-sky-400"></i>
                                    </div>
                                    <div class="space-y-1 max-h-48 overflow-y-auto pr-1">
                                        <label class="flex items-center gap-2 px-2 py-1 rounded hover:bg-slate-800 cursor-pointer text-xs font-semibold text-sky-400 border-b border-slate-800 mb-1">
                                            <input type="checkbox" @change="selected = $event.target.checked ? {{ json_encode($transportOptions) }} : []" :checked="selected.length === {{ count($transportOptions) }}" class="rounded bg-slate-800 border-slate-700 text-sky-500">
                                            <span>Pilih Semua</span>
                                        </label>
                                        @foreach($transportOptions as $option)
                                            <label class="flex items-center gap-2 px-2 py-1 rounded hover:bg-slate-800 cursor-pointer text-xs text-slate-200">
                                                <input type="checkbox" name="transport_type[]" value="{{ $option }}" x-model="selected" class="rounded bg-slate-800 border-slate-700 text-sky-500">
                                                <span>{{ $option }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-800/80">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow transition-colors">
                                            Terapkan
                                        </button>
                                    </div>
                                </div>
                            </th>

                            <!-- 5. Pemesan & Pembayar -->
                            <th class="py-3.5 px-4 whitespace-nowrap relative">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <span>Pemesan & Pembayar</span>
                                    <button type="button" @click="openPop = (openPop === 'person' ? null : 'person')" @click.outside="if (openPop === 'person') openPop = null" class="p-1 rounded hover:bg-slate-800 transition-colors {{ $search ? 'text-sky-400 font-bold bg-sky-500/20' : 'text-slate-500 hover:text-slate-300' }}" title="Filter Pemesan & Pembayar">
                                        <i class="fa-solid fa-caret-down text-xs"></i>
                                    </button>
                                </div>
                                <!-- Popover Filter Person -->
                                <div x-show="openPop === 'person'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-3 text-left font-normal normal-case min-w-[220px]">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Cari Pemesan / Pembayar</span>
                                        <i class="fa-solid fa-user-check text-sky-400"></i>
                                    </div>
                                    <input type="text" name="search" value="{{ $search }}" placeholder="Nama pemesan / pembayar..." class="w-full h-9 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 placeholder-slate-500 focus:border-sky-400 focus:outline-none">
                                    <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-800/80">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow transition-colors">
                                            Terapkan
                                        </button>
                                    </div>
                                </div>
                            </th>

                            <!-- 6. Biaya (IDR) -->
                            <th class="py-3.5 px-4 text-right whitespace-nowrap">Biaya (IDR)</th>

                            <!-- 7. Status -->
                            <th class="py-3.5 px-4 text-center whitespace-nowrap relative">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span>Status</span>
                                    <button type="button" @click="openPop = (openPop === 'status' ? null : 'status')" @click.outside="if (openPop === 'status') openPop = null" class="p-1 rounded hover:bg-slate-800 transition-colors {{ !empty($status) ? 'text-emerald-400 font-bold bg-emerald-500/20' : 'text-slate-500 hover:text-slate-300' }}" title="Filter Status">
                                        <i class="fa-solid fa-caret-down text-xs"></i>
                                    </button>
                                </div>
                                <!-- Popover Filter Status -->
                                <div x-show="openPop === 'status'" x-cloak x-transition class="absolute z-50 right-0 mt-2 p-3 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-2 text-left font-normal normal-case min-w-[180px]" x-data="{ selected: {{ json_encode($status) }} }">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Pilih Status</span>
                                        <i class="fa-solid fa-tag text-emerald-400"></i>
                                    </div>
                                    <div class="space-y-1 max-h-48 overflow-y-auto pr-1">
                                        <label class="flex items-center gap-2 px-2 py-1 rounded hover:bg-slate-800 cursor-pointer text-xs font-semibold text-emerald-400 border-b border-slate-800 mb-1">
                                            <input type="checkbox" @change="selected = $event.target.checked ? {{ json_encode($statusOptions) }} : []" :checked="selected.length === {{ count($statusOptions) }}" class="rounded bg-slate-800 border-slate-700 text-emerald-500">
                                            <span>Pilih Semua</span>
                                        </label>
                                        @foreach($statusOptions as $optStatus)
                                            <label class="flex items-center gap-2 px-2 py-1 rounded hover:bg-slate-800 cursor-pointer text-xs text-slate-200">
                                                <input type="checkbox" name="status[]" value="{{ $optStatus }}" x-model="selected" class="rounded bg-slate-800 border-slate-700 text-emerald-500">
                                                <span>{{ $optStatus }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-800/80">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow transition-colors">
                                            Terapkan
                                        </button>
                                    </div>
                                </div>
                            </th>

                            <!-- 8. Aksi -->
                            <th class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span>Aksi</span>
                                    @if($search || !empty($transportType) || !empty($status) || $dateFrom || $dateTo)
                                        <a href="{{ route('tickets.index') }}" class="px-2 py-0.5 rounded bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 text-[10px] font-mono normal-case transition-colors inline-flex items-center gap-1" title="Reset Semua Filter">
                                            <i class="fa-solid fa-rotate-left text-[9px]"></i> Reset
                                        </a>
                                    @endif
                                </div>
                            </th>
                        </tr>
                    </thead>
                <tbody class="divide-y divide-slate-800/60 whitespace-nowrap">
                    @forelse($tickets as $ticket)
                        <tr class="hover:bg-slate-900/50 transition-colors group whitespace-nowrap">
                            <!-- Kode Tiket -->
                            <td class="py-4 px-4 font-mono font-medium text-sky-400 whitespace-nowrap">
                                <div class="flex items-center gap-2 whitespace-nowrap">
                                    <span class="text-base">{{ $ticket->transport_icon }}</span>
                                    <span>{{ $ticket->ticket_code }}</span>
                                </div>
                            </td>

                            <!-- Tanggal Tiket -->
                            <td class="py-4 px-4 whitespace-nowrap font-medium text-slate-200">
                                <i class="fa-regular fa-calendar text-slate-500 mr-1.5"></i>
                                {{ $ticket->ticket_date->format('d M Y') }}
                            </td>

                            <!-- Rute & Penumpang -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <div class="flex items-center gap-2 font-medium text-slate-100 whitespace-nowrap">
                                    <span>{{ $ticket->origin }}</span>
                                    <i class="fa-solid fa-arrow-right text-xs text-sky-400"></i>
                                    <span>{{ $ticket->destination }}</span>
                                </div>
                                <div class="text-xs text-slate-300 mt-1 flex items-center gap-1.5 whitespace-nowrap">
                                    <i class="fa-solid fa-user-group text-slate-500"></i>
                                    <span class="font-medium text-slate-200 whitespace-nowrap">{{ $ticket->passenger_display }}</span>
                                    @if($ticket->passenger_count > 1)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] bg-sky-950 text-sky-300 border border-sky-800 font-bold whitespace-nowrap">
                                            {{ $ticket->passenger_count }} Orang
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Transportasi -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-800 text-slate-300 border border-slate-700/60 whitespace-nowrap">
                                    {{ $ticket->transport_type }}
                                </span>
                            </td>

                            <!-- Booker & Payer -->
                            <td class="py-4 px-4 text-xs whitespace-nowrap">
                                <div class="whitespace-nowrap">
                                    <span class="text-slate-400">Pemesan:</span> 
                                    <strong class="text-indigo-300">{{ $ticket->booked_by }}</strong>
                                    @if($ticket->bookerUser)
                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-indigo-950 text-indigo-400 border border-indigo-800 whitespace-nowrap">Akun</span>
                                    @endif
                                </div>
                                <div class="mt-0.5 whitespace-nowrap">
                                    <span class="text-slate-400">Pembayaran Oleh:</span> 
                                    <strong class="text-emerald-300">{{ $ticket->paid_by }}</strong>
                                    @if($ticket->payerUser)
                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-950 text-emerald-400 border border-emerald-800 whitespace-nowrap">Akun</span>
                                    @endif
                                </div>
                                @if($ticket->payment_date)
                                    <div class="text-[11px] text-slate-500 mt-0.5 whitespace-nowrap">Tgl Bayar: {{ $ticket->payment_date->format('d/m/Y') }}</div>
                                @endif
                            </td>

                            <!-- Biaya -->
                            <td class="py-4 px-4 text-right whitespace-nowrap font-mono font-bold text-emerald-400">
                                {{ $ticket->formatted_amount }}
                            </td>

                            <!-- Status -->
                            <td class="py-4 px-4 text-center whitespace-nowrap">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full border {{ $ticket->status_badge_class }}">
                                    {{ $ticket->status }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="py-4 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-2">
                                    <!-- View Detail / Boarding Pass -->
                                    <button @click="selectedTicket = {{ json_encode([
                                        'ticket_code' => $ticket->ticket_code,
                                        'ticket_date' => $ticket->ticket_date->format('d M Y'),
                                        'origin' => $ticket->origin,
                                        'destination' => $ticket->destination,
                                        'transport_type' => $ticket->transport_type,
                                        'transport_icon' => $ticket->transport_icon,
                                        'passenger_display' => $ticket->passenger_display,
                                        'passengers_list' => $ticket->passengers_list,
                                        'passenger_count' => $ticket->passenger_count,
                                        'booked_by' => $ticket->booked_by,
                                        'paid_by' => $ticket->paid_by,
                                        'payment_date' => $ticket->payment_date ? $ticket->payment_date->format('d M Y') : '-',
                                        'amount' => $ticket->formatted_amount,
                                        'status' => $ticket->status,
                                        'status_badge' => $ticket->status_badge_class,
                                        'notes' => $ticket->notes ?? '-',
                                        'attachment_url' => $ticket->attachment_path ? asset('storage/' . $ticket->attachment_path) : null,
                                        'pdf_url' => route('tickets.pdf', $ticket->id),
                                        'status_logs' => $ticket->statusLogs->map(fn($log) => [
                                            'to_status' => $log->to_status,
                                            'from_status' => $log->from_status,
                                            'user_name' => $log->user_name,
                                            'user_role' => ucfirst($log->user_role),
                                            'notes' => $log->notes,
                                            'date' => $log->created_at->format('d M Y, H:i'),
                                            'badge' => $log->status_badge_class,
                                        ])
                                    ]) }}; showModal = true" class="w-8 h-8 rounded-lg bg-sky-500/10 text-sky-400 hover:bg-sky-500 hover:text-white flex items-center justify-center transition-colors" title="Lihat E-Ticket Boarding Pass">
                                        <i class="fa-solid fa-ticket-simple text-sm"></i>
                                    </button>

                                    <!-- Edit -->
                                    @can('update', $ticket)
                                        <a href="{{ route('tickets.edit', $ticket->id) }}" class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 hover:bg-amber-500 hover:text-white flex items-center justify-center transition-colors" title="Edit Tiket">
                                            <i class="fa-solid fa-pen-to-square text-sm"></i>
                                        </a>
                                    @else
                                        <span class="w-8 h-8 rounded-lg bg-slate-800 text-slate-600 flex items-center justify-center cursor-not-allowed" title="Anda tidak memiliki akses mengedit tiket ini">
                                            <i class="fa-solid fa-lock text-xs"></i>
                                        </span>
                                    @endcan

                                    <!-- Delete -->
                                    @can('delete', $ticket)
                                        <form action="{{ route('tickets.destroy', $ticket->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus histori tiket {{ $ticket->ticket_code }}?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-8 h-8 rounded-lg bg-rose-500/10 text-rose-400 hover:bg-rose-500 hover:text-white flex items-center justify-center transition-colors" title="Hapus Tiket">
                                                <i class="fa-solid fa-trash-can text-sm"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-500">
                                <div class="w-16 h-16 rounded-full bg-slate-900 border border-slate-800 flex items-center justify-center mx-auto mb-3 text-slate-600">
                                    <i class="fa-solid fa-ticket-simple text-2xl"></i>
                                </div>
                                <p class="text-base font-medium text-slate-400">Tidak ada histori tiket ditemukan</p>
                                <p class="text-xs text-slate-500 mt-1">Coba sesuaikan kata kunci pencarian atau filter yang Anda pilih.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tickets->hasPages())
            <div class="p-4 bg-slate-900/80 border-t border-slate-800">
                {{ $tickets->links() }}
            </div>
        @endif
        </form>
    </div>

    <!-- Boarding Pass Modal -->
    <div x-cloak x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="showModal = false" class="fixed inset-0 bg-slate-950/80 backdrop-blur-md transition-opacity no-print"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div id="modal-boarding-pass-card" x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-slate-900 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-slate-800 relative printable-card">
                <template x-if="selectedTicket">
                    <div class="p-0">
                        <div class="bg-gradient-to-r from-sky-600 to-indigo-700 p-6 text-white relative overflow-hidden">
                            <div class="absolute -right-6 -bottom-6 text-white/10 text-9xl font-bold font-mono select-none">
                                TCK
                            </div>

                            <div class="flex items-center justify-between relative z-10">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-xl">
                                        <span x-text="selectedTicket.transport_icon"></span>
                                    </div>
                                    <div>
                                        <p class="text-xs text-sky-200 uppercase font-mono tracking-wider">E-TICKET BOARDING PASS</p>
                                        <h3 class="font-mono font-bold text-lg" x-text="selectedTicket.ticket_code"></h3>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 no-print">
                                    <a :href="selectedTicket.pdf_url" target="_blank" class="px-3.5 py-1.5 rounded-lg bg-sky-500 hover:bg-sky-400 text-white text-xs font-semibold flex items-center gap-1.5 transition-colors shadow-sm" title="Download / Cetak Boarding Pass Versi PDF">
                                        <i class="fa-solid fa-file-pdf"></i> Download PDF
                                    </a>
                                    <button @click="showModal = false" class="w-8 h-8 rounded-full bg-black/20 hover:bg-black/40 flex items-center justify-center text-white transition-colors">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mt-6 pt-4 border-t border-white/20 flex items-center justify-between">
                                <div class="text-left">
                                    <span class="text-xs text-sky-200 block uppercase">Dari (Origin)</span>
                                    <span class="font-display text-xl font-bold text-white block mt-0.5" x-text="selectedTicket.origin"></span>
                                </div>
                                <div class="px-4 text-center">
                                    <i class="fa-solid fa-plane-departure text-xl text-sky-300 animate-pulse"></i>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs text-sky-200 block uppercase">Ke (Destination)</span>
                                    <span class="font-display text-xl font-bold text-white block mt-0.5" x-text="selectedTicket.destination"></span>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 space-y-5 bg-slate-900">
                            <!-- Passengers Section in Modal -->
                            <div class="bg-slate-950/60 p-4 rounded-2xl border border-slate-800">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs text-slate-400 flex items-center gap-1.5 font-medium">
                                        <i class="fa-solid fa-users text-sky-400"></i> Daftar Penumpang
                                    </span>
                                    <span class="text-xs font-mono font-semibold text-sky-400 bg-sky-500/10 px-2 py-0.5 rounded-full border border-sky-500/30" x-text="selectedTicket.passenger_count + ' Penumpang'"></span>
                                </div>
                                <div class="space-y-1">
                                    <template x-for="(name, idx) in selectedTicket.passengers_list" :key="idx">
                                        <div class="flex items-center gap-2 text-sm text-slate-100 font-medium py-1 border-b border-slate-800/40 last:border-0">
                                            <span class="text-xs font-mono text-slate-500" x-text="(idx + 1) + '.'"></span>
                                            <span x-text="name"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 bg-slate-950/60 p-4 rounded-2xl border border-slate-800">
                                <div>
                                    <span class="text-xs text-slate-400 block">Tanggal Keberangkatan</span>
                                    <span class="text-sm font-semibold text-sky-400 mt-0.5 block" x-text="selectedTicket.ticket_date"></span>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-400 block">Moda Transportasi</span>
                                    <span class="text-sm font-semibold text-slate-200 mt-0.5 block" x-text="selectedTicket.transport_type"></span>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-400 block">Status Pembayaran</span>
                                    <span class="inline-block mt-1 px-2.5 py-0.5 text-xs font-semibold rounded-full border" :class="selectedTicket.status_badge" x-text="selectedTicket.status"></span>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-400 block">Harga / Biaya Tiket</span>
                                    <span class="text-base font-bold text-emerald-400 font-mono mt-0.5 block" x-text="selectedTicket.amount"></span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 bg-slate-950/60 p-4 rounded-2xl border border-slate-800">
                                <div>
                                    <span class="text-xs text-slate-400 block">Pemesan Tiket</span>
                                    <span class="text-sm font-medium text-indigo-300 mt-0.5 block" x-text="selectedTicket.booked_by"></span>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-400 block">Pembayaran Oleh</span>
                                    <span class="text-sm font-medium text-emerald-300 mt-0.5 block" x-text="selectedTicket.paid_by"></span>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-400 block">Tanggal Pembayaran</span>
                                    <span class="text-sm font-medium text-slate-300 mt-0.5 block" x-text="selectedTicket.payment_date"></span>
                                </div>
                            </div>

                            <div class="bg-slate-950/40 p-4 rounded-2xl border border-slate-800/80">
                                <span class="text-xs text-slate-400 block font-medium">Catatan / Keterangan</span>
                                <p class="text-xs text-slate-300 mt-1 leading-relaxed italic" x-text="selectedTicket.notes"></p>
                            </div>

                            <!-- Status Log Timeline in Modal -->
                            <div class="bg-slate-950/60 p-4 rounded-2xl border border-slate-800 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-slate-400 flex items-center gap-1.5 font-semibold uppercase tracking-wider">
                                        <i class="fa-solid fa-list-check text-sky-400"></i> Riwayat Step Status Tiket
                                    </span>
                                    <span class="text-[10px] text-slate-500 font-mono">Sequential Log</span>
                                </div>
                                <div class="relative pl-6 space-y-3 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-800">
                                    <template x-for="(log, lIdx) in selectedTicket.status_logs" :key="lIdx">
                                        <div class="relative">
                                            <div class="absolute -left-6 top-0.5 w-5 h-5 rounded-full bg-slate-900 border-2 border-sky-400 flex items-center justify-center text-[10px] font-mono font-bold text-sky-300" x-text="lIdx + 1"></div>
                                            <div>
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold border" :class="log.badge" x-text="log.to_status"></span>
                                                    <template x-if="log.from_status">
                                                        <span class="text-[10px] text-slate-500 font-mono" x-text="'(dari ' + log.from_status + ')'"></span>
                                                    </template>
                                                </div>
                                                <p class="text-xs text-slate-300 mt-0.5 leading-relaxed" x-text="log.notes"></p>
                                                <span class="text-[10px] text-slate-500 font-mono mt-0.5 block" x-text="log.user_name + ' (' + log.user_role + ') • ' + log.date"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <template x-if="selectedTicket.attachment_url">
                                <div class="pt-2">
                                    <a :href="selectedTicket.attachment_url" target="_blank" class="inline-flex items-center justify-center w-full px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-slate-800 hover:bg-slate-700 border border-slate-700 transition-colors">
                                        <i class="fa-solid fa-paperclip text-sky-400 mr-2"></i> Buka File Bukti Tiket / Nota Upload
                                    </a>
                                </div>
                            </template>
                        </div>

                        <div class="bg-slate-950 p-4 border-t border-slate-800 text-center">
                            <div class="font-mono text-2xl tracking-[0.3em] text-slate-600 select-none">
                                ||||| ||| ||||||| ||| |||||
                            </div>
                            <span class="text-[10px] text-slate-500 block font-mono mt-1">VERIFIED HISTORICAL TICKET RECORD</span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

</div>
@endsection
