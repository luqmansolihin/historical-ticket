@extends('layouts.app')

@section('title', 'Daftar Histori Tiket')

@section('content')
<div x-data="{ selectedTicket: null, showModal: false }">

    <!-- Page Header & Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="font-display text-2xl sm:text-3xl font-bold tracking-tight text-white flex items-center gap-3">
                <span>Histori Tiket Perjalanan</span>
                <span class="text-xs font-mono font-medium px-2.5 py-1 rounded-full bg-sky-500/10 text-sky-400 border border-sky-500/30">
                    {{ $totalTickets }} Tiket Terdaftar
                </span>
            </h1>
            <p class="text-slate-400 text-sm mt-1">Pencatatan riwayat keberangkatan, penumpang (multi-person), pemesan (Booker), dan pembayar (Payer).</p>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
            <a href="{{ route('tickets.export', request()->query()) }}" class="inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-medium text-slate-200 bg-slate-800 hover:bg-slate-700 border border-slate-700/80 transition-all duration-200 shadow-sm">
                <i class="fa-solid fa-file-csv text-emerald-400 mr-2 text-base"></i> Export CSV
            </a>

            @can('create', App\Models\TicketHistory::class)
                <a href="{{ route('tickets.create') }}" class="inline-flex items-center px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-400 hover:to-blue-500 shadow-lg shadow-sky-500/25 transition-all duration-200 active:scale-95">
                    <i class="fa-solid fa-plus mr-2"></i> Tambah Tiket Baru
                </a>
            @endcan
        </div>
    </div>

    <!-- Analytics & Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="glass-card p-5 rounded-2xl relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Total Tiket</p>
                    <h3 class="text-2xl font-bold text-white mt-1 font-display">{{ number_format($totalTickets) }}</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-sky-500/10 border border-sky-500/20 flex items-center justify-center text-sky-400 text-xl group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-ticket-simple"></i>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-800/60 text-xs text-slate-400 flex items-center gap-1.5">
                <i class="fa-solid fa-clock-rotate-left text-sky-400"></i> Histori tercatat dalam sistem
            </div>
        </div>

        <div class="glass-card p-5 rounded-2xl relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Total Pengeluaran</p>
                    <h3 class="text-2xl font-bold text-emerald-400 mt-1 font-display">Rp {{ number_format($totalAmount, 0, ',', '.') }}</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 text-xl group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-800/60 text-xs text-slate-400 flex items-center gap-1.5">
                <i class="fa-solid fa-calculator text-emerald-400"></i> Akumulasi biaya tiket
            </div>
        </div>

        <div class="glass-card p-5 rounded-2xl relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Status Lunas</p>
                    <h3 class="text-2xl font-bold text-sky-300 mt-1 font-display">{{ number_format($totalLunas) }} Tiket</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-sky-500/10 border border-sky-500/20 flex items-center justify-center text-sky-400 text-xl group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-800/60 text-xs text-slate-400 flex items-center gap-1.5">
                <i class="fa-solid fa-check text-sky-400"></i> Pembayaran tuntas
            </div>
        </div>

        <div class="glass-card p-5 rounded-2xl relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Reimburse / Pending</p>
                    <h3 class="text-2xl font-bold text-amber-400 mt-1 font-display">{{ number_format($totalReimburse + $totalBelumBayar) }} Tiket</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 text-xl group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-800/60 text-xs text-slate-400 flex items-center gap-1.5">
                <span class="text-amber-400">{{ $totalReimburse }} Reimburse</span> &bull; <span class="text-rose-400">{{ $totalBelumBayar }} Belum Bayar</span>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="glass-card p-5 rounded-2xl mb-8">
        <form action="{{ route('tickets.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
            <div class="lg:col-span-4">
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Pencarian Keyword</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari kode, kota, penumpang, pemesan, pembayar..." class="w-full glass-input rounded-xl pl-10 pr-4 py-2.5 text-sm placeholder-slate-500">
                </div>
            </div>

            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Moda Transportasi</label>
                <select name="transport_type" class="w-full glass-input rounded-xl px-3 py-2.5 text-sm bg-slate-900">
                    <option value="">Semua Transportasi</option>
                    @foreach($transportOptions as $option)
                        <option value="{{ $option }}" {{ $transportType == $option ? 'selected' : '' }}>{{ $option }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Status Pembayaran</label>
                <select name="status" class="w-full glass-input rounded-xl px-3 py-2.5 text-sm bg-slate-900">
                    <option value="">Semua Status</option>
                    @foreach($statusOptions as $optStatus)
                        <option value="{{ $optStatus }}" {{ $status == $optStatus ? 'selected' : '' }}>{{ $optStatus }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full glass-input rounded-xl px-3 py-2 text-sm bg-slate-900">
            </div>

            <div class="lg:col-span-2 flex items-center gap-2">
                <div class="w-full">
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Sampai Tanggal</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full glass-input rounded-xl px-3 py-2 text-sm bg-slate-900">
                </div>
            </div>

            <div class="lg:col-span-12 flex items-center justify-end gap-3 pt-2 border-t border-slate-800/50 mt-2">
                <a href="{{ route('tickets.index') }}" class="px-4 py-2 rounded-xl text-xs font-medium text-rose-300 hover:text-white bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 transition-all flex items-center gap-1.5" title="Hapus seluruh filter pencarian">
                    <i class="fa-solid fa-filter-circle-xmark"></i> Hapus Filter
                </a>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-semibold text-white bg-sky-600 hover:bg-sky-500 shadow-md shadow-sky-600/20 transition-all">
                    <i class="fa-solid fa-filter mr-1.5"></i> Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Tickets Table View -->
    <div class="glass-card rounded-2xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-900/90 text-xs uppercase font-semibold text-slate-400 tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-4 px-4">Kode Tiket</th>
                        <th class="py-4 px-4">Tgl Tiket</th>
                        <th class="py-4 px-4">Rute & Penumpang</th>
                        <th class="py-4 px-4">Transportasi</th>
                        <th class="py-4 px-4">Booker & Payer</th>
                        <th class="py-4 px-4 text-right">Biaya (IDR)</th>
                        <th class="py-4 px-4 text-center">Status</th>
                        <th class="py-4 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($tickets as $ticket)
                        <tr class="hover:bg-slate-900/50 transition-colors group">
                            <!-- Kode Tiket -->
                            <td class="py-4 px-4 font-mono font-medium text-sky-400 whitespace-nowrap">
                                <div class="flex items-center gap-2">
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
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-2 font-medium text-slate-100">
                                    <span>{{ $ticket->origin }}</span>
                                    <i class="fa-solid fa-arrow-right text-xs text-sky-400"></i>
                                    <span>{{ $ticket->destination }}</span>
                                </div>
                                <div class="text-xs text-slate-300 mt-1 flex items-center gap-1.5 flex-wrap">
                                    <i class="fa-solid fa-user-group text-slate-500"></i>
                                    <span class="font-medium text-slate-200">{{ $ticket->passenger_display }}</span>
                                    @if($ticket->passenger_count > 1)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] bg-sky-950 text-sky-300 border border-sky-800 font-bold">
                                            {{ $ticket->passenger_count }} Orang
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Transportasi -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-800 text-slate-300 border border-slate-700/60">
                                    {{ $ticket->transport_type }}
                                </span>
                            </td>

                            <!-- Booker & Payer -->
                            <td class="py-4 px-4 text-xs">
                                <div>
                                    <span class="text-slate-400">Booker:</span> 
                                    <strong class="text-indigo-300">{{ $ticket->booked_by }}</strong>
                                    @if($ticket->bookerUser)
                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-indigo-950 text-indigo-400 border border-indigo-800">Akun</span>
                                    @endif
                                </div>
                                <div class="mt-0.5">
                                    <span class="text-slate-400">Payer:</span> 
                                    <strong class="text-emerald-300">{{ $ticket->paid_by }}</strong>
                                    @if($ticket->payerUser)
                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-950 text-emerald-400 border border-emerald-800">Akun</span>
                                    @endif
                                </div>
                                @if($ticket->payment_date)
                                    <div class="text-[11px] text-slate-500 mt-0.5">Tgl Bayar: {{ $ticket->payment_date->format('d/m/Y') }}</div>
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
                                        'attachment_url' => $ticket->attachment_path ? asset('storage/' . $ticket->attachment_path) : null
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
    </div>

    <!-- Boarding Pass Modal -->
    <div x-cloak x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="showModal = false" class="fixed inset-0 bg-slate-950/80 backdrop-blur-md transition-opacity"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-slate-900 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-slate-800 relative">
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
                                <button @click="showModal = false" class="w-8 h-8 rounded-full bg-black/20 hover:bg-black/40 flex items-center justify-center text-white transition-colors">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
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
                                    <span class="text-xs text-slate-400 block">Siapa yang Booking</span>
                                    <span class="text-sm font-medium text-indigo-300 mt-0.5 block" x-text="selectedTicket.booked_by"></span>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-400 block">Siapa yang Bayar</span>
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
