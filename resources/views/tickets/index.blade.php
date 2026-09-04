@extends('layouts.app')

@section('title', 'Daftar Histori Tiket')

@section('content')
<div x-data="{ selectedTicket: null, showModal: false }" class="flex-1 flex flex-col min-h-0 h-full overflow-hidden">

    <!-- ERP Data Table Container & Column Header Filters -->
    <div x-data="{ openPop: null }" class="glass-card rounded-2xl shadow-2xl relative z-10 no-print flex-1 flex flex-col min-h-0 h-full overflow-hidden">
        <!-- ERP Data Grid Action Toolbar -->
        <div class="px-4 py-2.5 bg-slate-900/90 border-b border-slate-800/80 flex items-center justify-between gap-2 shrink-0">
            <div class="text-xs text-slate-400 font-mono hidden sm:block">
                <i class="fa-solid fa-mouse-pointer text-sky-400 mr-1"></i> Double klik pada baris tabel untuk mengedit data
            </div>

            <div class="flex items-center gap-2 ml-auto">
                @if($search || $searchCode || $searchOrigin || $searchDestination || $searchPassenger || $searchBooker || $searchPayer || $searchRoute || $searchPerson || !empty($transportType) || !empty($status) || $dateFrom || $dateTo || $amountMin || $amountMax)
                    <a href="{{ route('tickets.index') }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium text-rose-300 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 transition-all shadow-sm" title="Reset Semua Filter">
                        <i class="fa-solid fa-rotate-left mr-1.5 text-xs"></i> Reset Filter
                    </a>
                @endif

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

        <form action="{{ route('tickets.index') }}" method="GET" class="flex-1 flex flex-col min-h-0 h-full overflow-hidden justify-between">
            <div class="overflow-auto flex-1 min-h-0">
                <table class="w-full text-left text-xs text-slate-300 whitespace-nowrap border-collapse">
                    <thead class="bg-slate-900/95 text-[11px] uppercase font-semibold text-slate-400 tracking-wider border-b border-slate-800 whitespace-nowrap sticky top-0 z-20 backdrop-blur-md">
                        <tr>
                            <!-- 1. Kode Tiket -->
                            <th class="py-3 px-3 whitespace-nowrap relative border-r border-slate-800/60" @click.outside="if (openPop === 'code') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <span>Kode Tiket</span>
                                    <button type="button" @click="openPop = (openPop === 'code' ? null : 'code')" class="p-1 rounded hover:bg-slate-800 transition-colors {{ $searchCode ? 'text-sky-400 font-bold bg-sky-500/20' : 'text-slate-500 hover:text-slate-300' }}" title="Filter Kode Tiket">
                                        <i class="fa-solid fa-caret-down text-xs"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'code'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-3 text-left font-normal normal-case min-w-[220px]">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Filter Kode Tiket</span>
                                        <i class="fa-solid fa-ticket text-sky-400"></i>
                                    </div>
                                    <input type="text" name="search_code" value="{{ $searchCode }}" placeholder="Cari kode tiket..." class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 placeholder-slate-500 focus:border-sky-400 focus:outline-none">
                                    <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-800/80">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow transition-colors">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 2. Tgl SPK / Tiket -->
                            <th class="py-3 px-3 whitespace-nowrap relative border-r border-slate-800/60" @click.outside="if (openPop === 'date') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <span>Tgl Tiket</span>
                                    <button type="button" @click="openPop = (openPop === 'date' ? null : 'date')" class="p-1 rounded hover:bg-slate-800 transition-colors {{ ($dateFrom || $dateTo) ? 'text-sky-400 font-bold bg-sky-500/20' : 'text-slate-500 hover:text-slate-300' }}" title="Filter Tanggal Tiket">
                                        <i class="fa-solid fa-caret-down text-xs"></i>
                                    </button>
                                </div>
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
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow transition-colors">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 3. Asal -->
                            <th class="py-3 px-3 whitespace-nowrap relative border-r border-slate-800/60" @click.outside="if (openPop === 'origin') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <span>Asal</span>
                                    <button type="button" @click="openPop = (openPop === 'origin' ? null : 'origin')" class="p-1 rounded hover:bg-slate-800 transition-colors {{ $searchOrigin ? 'text-sky-400 font-bold bg-sky-500/20' : 'text-slate-500 hover:text-slate-300' }}" title="Filter Kota Asal">
                                        <i class="fa-solid fa-caret-down text-xs"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'origin'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-3 text-left font-normal normal-case min-w-[220px]">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Filter Kota Asal</span>
                                        <i class="fa-solid fa-location-dot text-sky-400"></i>
                                    </div>
                                    <input type="text" name="search_origin" value="{{ $searchOrigin }}" placeholder="Cari kota asal..." class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 placeholder-slate-500 focus:border-sky-400 focus:outline-none">
                                    <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-800/80">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow transition-colors">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 4. Tujuan -->
                            <th class="py-3 px-3 whitespace-nowrap relative border-r border-slate-800/60" @click.outside="if (openPop === 'destination') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <span>Tujuan</span>
                                    <button type="button" @click="openPop = (openPop === 'destination' ? null : 'destination')" class="p-1 rounded hover:bg-slate-800 transition-colors {{ $searchDestination ? 'text-sky-400 font-bold bg-sky-500/20' : 'text-slate-500 hover:text-slate-300' }}" title="Filter Kota Tujuan">
                                        <i class="fa-solid fa-caret-down text-xs"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'destination'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-3 text-left font-normal normal-case min-w-[220px]">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Filter Kota Tujuan</span>
                                        <i class="fa-solid fa-location-arrow text-sky-400"></i>
                                    </div>
                                    <input type="text" name="search_destination" value="{{ $searchDestination }}" placeholder="Cari kota tujuan..." class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 placeholder-slate-500 focus:border-sky-400 focus:outline-none">
                                    <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-800/80">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow transition-colors">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 5. Transportasi -->
                            <th class="py-3 px-3 whitespace-nowrap relative border-r border-slate-800/60" @click.outside="if (openPop === 'transport') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <span>Transportasi</span>
                                    <button type="button" @click="openPop = (openPop === 'transport' ? null : 'transport')" class="p-1 rounded hover:bg-slate-800 transition-colors {{ !empty($transportType) ? 'text-sky-400 font-bold bg-sky-500/20' : 'text-slate-500 hover:text-slate-300' }}" title="Filter Transportasi">
                                        <i class="fa-solid fa-caret-down text-xs"></i>
                                    </button>
                                </div>
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
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow transition-colors">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 6. Penumpang -->
                            <th class="py-3 px-3 whitespace-nowrap relative border-r border-slate-800/60" @click.outside="if (openPop === 'passenger') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <span>Nama Penumpang</span>
                                    <button type="button" @click="openPop = (openPop === 'passenger' ? null : 'passenger')" class="p-1 rounded hover:bg-slate-800 transition-colors {{ $searchPassenger ? 'text-sky-400 font-bold bg-sky-500/20' : 'text-slate-500 hover:text-slate-300' }}" title="Filter Penumpang">
                                        <i class="fa-solid fa-caret-down text-xs"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'passenger'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-3 text-left font-normal normal-case min-w-[220px]">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Filter Nama Penumpang</span>
                                        <i class="fa-solid fa-user text-sky-400"></i>
                                    </div>
                                    <input type="text" name="search_passenger" value="{{ $searchPassenger }}" placeholder="Cari nama penumpang..." class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 placeholder-slate-500 focus:border-sky-400 focus:outline-none">
                                    <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-800/80">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow transition-colors">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 7. Jml Penumpang -->
                            <th class="py-3 px-3 text-center whitespace-nowrap border-r border-slate-800/60">
                                <span>Jml</span>
                            </th>

                            <!-- 8. Pemesan -->
                            <th class="py-3 px-3 whitespace-nowrap relative border-r border-slate-800/60" @click.outside="if (openPop === 'booker') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <span>Pemesan</span>
                                    <button type="button" @click="openPop = (openPop === 'booker' ? null : 'booker')" class="p-1 rounded hover:bg-slate-800 transition-colors {{ $searchBooker ? 'text-sky-400 font-bold bg-sky-500/20' : 'text-slate-500 hover:text-slate-300' }}" title="Filter Pemesan">
                                        <i class="fa-solid fa-caret-down text-xs"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'booker'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-3 text-left font-normal normal-case min-w-[220px]">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Filter Pemesan</span>
                                        <i class="fa-solid fa-user-pen text-sky-400"></i>
                                    </div>
                                    <input type="text" name="search_booker" value="{{ $searchBooker }}" placeholder="Nama pemesan..." class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 placeholder-slate-500 focus:border-sky-400 focus:outline-none">
                                    <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-800/80">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow transition-colors">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 9. Pembayar -->
                            <th class="py-3 px-3 whitespace-nowrap relative border-r border-slate-800/60" @click.outside="if (openPop === 'payer') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <span>Pembayar</span>
                                    <button type="button" @click="openPop = (openPop === 'payer' ? null : 'payer')" class="p-1 rounded hover:bg-slate-800 transition-colors {{ $searchPayer ? 'text-sky-400 font-bold bg-sky-500/20' : 'text-slate-500 hover:text-slate-300' }}" title="Filter Pembayar">
                                        <i class="fa-solid fa-caret-down text-xs"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'payer'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-3 text-left font-normal normal-case min-w-[220px]">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Filter Pembayar</span>
                                        <i class="fa-solid fa-credit-card text-sky-400"></i>
                                    </div>
                                    <input type="text" name="search_payer" value="{{ $searchPayer }}" placeholder="Nama pembayar..." class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 placeholder-slate-500 focus:border-sky-400 focus:outline-none">
                                    <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-800/80">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow transition-colors">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 10. Tgl Bayar -->
                            <th class="py-3 px-3 whitespace-nowrap border-r border-slate-800/60">
                                <span>Tgl Bayar</span>
                            </th>

                            <!-- 11. Biaya (IDR) -->
                            <th class="py-3 px-3 text-right whitespace-nowrap relative border-r border-slate-800/60" @click.outside="if (openPop === 'amount') openPop = null">
                                <div class="flex items-center justify-end gap-1.5">
                                    <span>Biaya (IDR)</span>
                                    <button type="button" @click="openPop = (openPop === 'amount' ? null : 'amount')" class="p-1 rounded hover:bg-slate-800 transition-colors {{ ($amountMin || $amountMax) ? 'text-emerald-400 font-bold bg-emerald-500/20' : 'text-slate-500 hover:text-slate-300' }}" title="Filter Rentang Biaya">
                                        <i class="fa-solid fa-caret-down text-xs"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'amount'" x-cloak x-transition class="absolute z-50 right-0 mt-2 p-3.5 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-3 text-left font-normal normal-case min-w-[240px]">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Filter Rentang Biaya</span>
                                        <i class="fa-solid fa-money-bill-wave text-emerald-400"></i>
                                    </div>
                                    <div class="space-y-2">
                                        <div>
                                            <label class="block text-[11px] text-slate-400 mb-1">Minimal (Rp):</label>
                                            <input type="number" name="amount_min" value="{{ $amountMin }}" placeholder="Contoh: 500000" min="0" step="10000" class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 placeholder-slate-500 focus:border-emerald-400 focus:outline-none font-mono">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] text-slate-400 mb-1">Maksimal (Rp):</label>
                                            <input type="number" name="amount_max" value="{{ $amountMax }}" placeholder="Contoh: 5000000" min="0" step="10000" class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 placeholder-slate-500 focus:border-emerald-400 focus:outline-none font-mono">
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-800/80">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow transition-colors">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 12. Status -->
                            <th class="py-3 px-3 text-center whitespace-nowrap relative" @click.outside="if (openPop === 'status') openPop = null">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span>Status</span>
                                    <button type="button" @click="openPop = (openPop === 'status' ? null : 'status')" class="p-1 rounded hover:bg-slate-800 transition-colors {{ !empty($status) ? 'text-emerald-400 font-bold bg-emerald-500/20' : 'text-slate-500 hover:text-slate-300' }}" title="Filter Status">
                                        <i class="fa-solid fa-caret-down text-xs"></i>
                                    </button>
                                </div>
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
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow transition-colors">Terapkan</button>
                                    </div>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 whitespace-nowrap font-sans">
                        @forelse($tickets as $ticket)
                            <tr @dblclick="window.location.href = '{{ route('tickets.edit', $ticket->id) }}'"
                                class="hover:bg-sky-950/40 cursor-pointer transition-colors group whitespace-nowrap border-b border-slate-800/40 select-none"
                                title="Double klik untuk mengedit data tiket {{ $ticket->ticket_code }}">
                                <!-- 1. Kode Tiket -->
                                <td class="py-2.5 px-3 font-mono font-semibold text-sky-400 whitespace-nowrap border-r border-slate-800/40">
                                    {{ $ticket->ticket_code }}
                                </td>

                                <!-- 2. Tgl Tiket -->
                                <td class="py-2.5 px-3 whitespace-nowrap font-medium text-slate-300 border-r border-slate-800/40">
                                    {{ $ticket->ticket_date->format('d/m/Y') }}
                                </td>

                                <!-- 3. Asal -->
                                <td class="py-2.5 px-3 whitespace-nowrap font-medium text-slate-200 border-r border-slate-800/40">
                                    {{ $ticket->origin }}
                                </td>

                                <!-- 4. Tujuan -->
                                <td class="py-2.5 px-3 whitespace-nowrap font-medium text-slate-200 border-r border-slate-800/40">
                                    {{ $ticket->destination }}
                                </td>

                                <!-- 5. Transportasi -->
                                <td class="py-2.5 px-3 whitespace-nowrap border-r border-slate-800/40">
                                    <span class="inline-block px-2 py-0.5 rounded text-[11px] font-medium bg-slate-800 text-slate-300 border border-slate-700/60 whitespace-nowrap">
                                        {{ $ticket->transport_type }}
                                    </span>
                                </td>

                                <!-- 6. Penumpang -->
                                <td class="py-2.5 px-3 whitespace-nowrap text-slate-200 font-medium border-r border-slate-800/40">
                                    {{ implode(', ', $ticket->passengers_list) ?: $ticket->passenger_name }}
                                </td>

                                <!-- 7. Jml Penumpang -->
                                <td class="py-2.5 px-3 text-center whitespace-nowrap font-mono text-slate-300 font-bold border-r border-slate-800/40">
                                    {{ $ticket->passenger_count }}
                                </td>

                                <!-- 8. Pemesan -->
                                <td class="py-2.5 px-3 whitespace-nowrap border-r border-slate-800/40">
                                    <span class="text-indigo-300 font-medium">{{ $ticket->booked_by }}</span>
                                    @if($ticket->bookerUser)
                                        <span class="ml-1 text-[9px] px-1 py-0.2 rounded bg-indigo-950 text-indigo-400 border border-indigo-800 font-mono">Akun</span>
                                    @endif
                                </td>

                                <!-- 9. Pembayar -->
                                <td class="py-2.5 px-3 whitespace-nowrap border-r border-slate-800/40">
                                    <span class="text-emerald-300 font-medium">{{ $ticket->paid_by }}</span>
                                    @if($ticket->payerUser)
                                        <span class="ml-1 text-[9px] px-1 py-0.2 rounded bg-emerald-950 text-emerald-400 border border-emerald-800 font-mono">Akun</span>
                                    @endif
                                </td>

                                <!-- 10. Tgl Bayar -->
                                <td class="py-2.5 px-3 whitespace-nowrap text-slate-400 font-mono text-[11px] border-r border-slate-800/40">
                                    {{ $ticket->payment_date ? $ticket->payment_date->format('d/m/Y') : '-' }}
                                </td>

                                <!-- 11. Biaya (IDR) -->
                                <td class="py-2.5 px-3 text-right whitespace-nowrap font-mono font-bold text-emerald-400 border-r border-slate-800/40">
                                    {{ $ticket->formatted_amount }}
                                </td>

                                <!-- 12. Status -->
                                <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 text-[11px] font-semibold rounded-full border {{ $ticket->status_badge_class }}">
                                        {{ $ticket->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="py-12 text-center text-slate-500">
                                    <div class="w-14 h-14 rounded-full bg-slate-900 border border-slate-800 flex items-center justify-center mx-auto mb-3 text-slate-600">
                                        <i class="fa-solid fa-table-list text-xl"></i>
                                    </div>
                                    <p class="text-sm font-medium text-slate-400">Tidak ada histori tiket ditemukan</p>
                                    <p class="text-xs text-slate-500 mt-1">Coba sesuaikan kata kunci pencarian atau filter yang Anda pilih.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($tickets->hasPages())
                <div class="p-3 bg-slate-900/90 border-t border-slate-800 mt-auto shrink-0">
                    {{ $tickets->links() }}
                </div>
            @endif
        </form>
    </div>
</div>
@endsection
