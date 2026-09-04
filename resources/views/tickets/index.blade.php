@extends('layouts.app')

@section('title', 'Daftar Histori Tiket')

@section('content')
<div x-data="{ selectedTicket: null, showModal: false }" class="flex-1 flex flex-col min-h-0 h-full overflow-hidden">

    <!-- ERP Data Table Container & Column Header Filters -->
    <div x-data="{ openPop: null }" class="glass-card rounded-2xl shadow-2xl relative z-10 no-print flex-1 flex flex-col min-h-0 h-full overflow-hidden">
        <!-- ERP Data Grid Action Toolbar -->
        <div class="px-4 py-2.5 bg-slate-900/90 border-b border-slate-800/80 flex items-center justify-between gap-2 shrink-0">
            <div class="text-xs text-slate-400 font-mono hidden sm:block">
                <i class="fa-solid fa-mouse-pointer text-sky-400 mr-1"></i> Double klik baris tabel untuk edit atau lihat Boarding Pass
            </div>

            <div class="flex items-center gap-2 ml-auto">
                @if($search || $searchCode || $searchOrigin || $searchDestination || $searchPassenger || $searchBooker || $searchPayer || $searchRoute || $searchPerson || !empty($transportType) || !empty($status) || $dateVal || $dateFrom || $dateTo || $payDateVal || $payDateFrom || $payDateTo || $amountMin || $amountMax || $passengerCountMin || $passengerCountMax)
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
                                    <button type="button" @click="openPop = (openPop === 'date' ? null : 'date')" class="p-1 rounded hover:bg-slate-800 transition-colors {{ $dateVal ? 'text-sky-400 font-bold bg-sky-500/20' : 'text-slate-500 hover:text-slate-300' }}" title="Filter Tanggal Tiket">
                                        <i class="fa-solid fa-caret-down text-xs"></i>
                                    </button>
                                </div>
                                <!-- 3-Mode Date Filter Popover for Tgl Tiket -->
                                <div x-show="openPop === 'date'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-2.5 text-left font-normal normal-case min-w-[310px]"
                                     x-data="{
                                         dateMode: '{{ $dateMode ?? 'on' }}',
                                         dateVal: '{{ $dateVal ?? '' }}',
                                         initPicker() {
                                             this.$nextTick(() => {
                                                 flatpickr(this.$refs.datePicker, {
                                                     inline: true,
                                                     mode: 'single',
                                                     locale: 'id',
                                                     dateFormat: 'Y-m-d',
                                                     defaultDate: this.dateVal || null,
                                                     onChange: (selectedDates, dateStr, instance) => {
                                                         if (selectedDates.length > 0) {
                                                             this.dateVal = instance.formatDate(selectedDates[0], 'Y-m-d');
                                                         } else {
                                                             this.dateVal = '';
                                                         }
                                                     }
                                                 });
                                             });
                                         }
                                     }" x-init="initPicker()">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Filter Tanggal Tiket</span>
                                        <i class="fa-regular fa-calendar-days text-sky-400"></i>
                                    </div>
                                    
                                    <!-- 3 Mode Buttons: Before, After, On -->
                                    <div class="grid grid-cols-3 gap-1 bg-slate-950 p-1 rounded-lg border border-slate-800 text-[11px] font-medium text-center">
                                        <button type="button" @click="dateMode = 'before'" 
                                                :class="dateMode === 'before' ? 'bg-sky-600 text-white shadow font-bold' : 'text-slate-400 hover:text-slate-200'"
                                                class="py-1 px-2 rounded-md transition-all">
                                            Before
                                        </button>
                                        <button type="button" @click="dateMode = 'after'" 
                                                :class="dateMode === 'after' ? 'bg-sky-600 text-white shadow font-bold' : 'text-slate-400 hover:text-slate-200'"
                                                class="py-1 px-2 rounded-md transition-all">
                                            After
                                        </button>
                                        <button type="button" @click="dateMode = 'on'" 
                                                :class="dateMode === 'on' ? 'bg-sky-600 text-white shadow font-bold' : 'text-slate-400 hover:text-slate-200'"
                                                class="py-1 px-2 rounded-md transition-all">
                                            On
                                        </button>
                                    </div>

                                    <input type="hidden" name="date_mode" :value="dateMode">
                                    <input type="hidden" name="date_val" :value="dateVal">
                                    
                                    <div class="py-1 flex justify-center">
                                        <div x-ref="datePicker"></div>
                                    </div>

                                    <div class="flex items-center justify-between pt-2 border-t border-slate-800/80 text-[11px]">
                                        <div class="text-slate-400 font-mono">
                                            <template x-if="dateVal">
                                                <span>
                                                    <span class="uppercase text-sky-400 font-semibold" x-text="dateMode"></span>: <span x-text="dateVal"></span>
                                                </span>
                                            </template>
                                            <template x-if="!dateVal">
                                                <span>Belum ada tanggal</span>
                                            </template>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="dateVal = ''; if ($refs.datePicker._flatpickr) $refs.datePicker._flatpickr.clear()" class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs">Clear</button>
                                            <button type="submit" class="px-3 py-1 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow transition-colors">Terapkan</button>
                                        </div>
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
                            <th class="py-3 px-3 text-center whitespace-nowrap relative border-r border-slate-800/60" @click.outside="if (openPop === 'passenger_count') openPop = null">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span>Jml</span>
                                    <button type="button" @click="openPop = (openPop === 'passenger_count' ? null : 'passenger_count')" class="p-1 rounded hover:bg-slate-800 transition-colors {{ ($passengerCountMin || $passengerCountMax) ? 'text-sky-400 font-bold bg-sky-500/20' : 'text-slate-500 hover:text-slate-300' }}" title="Filter Jumlah Penumpang">
                                        <i class="fa-solid fa-caret-down text-xs"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'passenger_count'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3.5 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-3 text-left font-normal normal-case min-w-[220px]">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Filter Jml Penumpang</span>
                                        <i class="fa-solid fa-users text-sky-400"></i>
                                    </div>
                                    <div class="space-y-2">
                                        <div>
                                            <label class="block text-[11px] text-slate-400 mb-1">Minimal (Orang):</label>
                                            <input type="number" name="passenger_count_min" value="{{ $passengerCountMin }}" placeholder="Contoh: 1" min="1" class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 placeholder-slate-500 focus:border-sky-400 focus:outline-none font-mono">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] text-slate-400 mb-1">Maksimal (Orang):</label>
                                            <input type="number" name="passenger_count_max" value="{{ $passengerCountMax }}" placeholder="Contoh: 5" min="1" class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 placeholder-slate-500 focus:border-sky-400 focus:outline-none font-mono">
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-800/80">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow transition-colors">Terapkan</button>
                                    </div>
                                </div>
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
                            <th class="py-3 px-3 whitespace-nowrap relative border-r border-slate-800/60" @click.outside="if (openPop === 'pay_date') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <span>Tgl Bayar</span>
                                    <button type="button" @click="openPop = (openPop === 'pay_date' ? null : 'pay_date')" class="p-1 rounded hover:bg-slate-800 transition-colors {{ $payDateVal ? 'text-sky-400 font-bold bg-sky-500/20' : 'text-slate-500 hover:text-slate-300' }}" title="Filter Tanggal Bayar">
                                        <i class="fa-solid fa-caret-down text-xs"></i>
                                    </button>
                                </div>
                                <!-- 3-Mode Date Filter Popover for Tgl Bayar -->
                                <div x-show="openPop === 'pay_date'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-2.5 text-left font-normal normal-case min-w-[310px]"
                                     x-data="{
                                         payDateMode: '{{ $payDateMode ?? 'on' }}',
                                         payDateVal: '{{ $payDateVal ?? '' }}',
                                         initPayPicker() {
                                             this.$nextTick(() => {
                                                 flatpickr(this.$refs.payDatePicker, {
                                                     inline: true,
                                                     mode: 'single',
                                                     locale: 'id',
                                                     dateFormat: 'Y-m-d',
                                                     defaultDate: this.payDateVal || null,
                                                     onChange: (selectedDates, dateStr, instance) => {
                                                         if (selectedDates.length > 0) {
                                                             this.payDateVal = instance.formatDate(selectedDates[0], 'Y-m-d');
                                                         } else {
                                                             this.payDateVal = '';
                                                         }
                                                     }
                                                 });
                                             });
                                         }
                                     }" x-init="initPayPicker()">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Filter Tanggal Bayar</span>
                                        <i class="fa-regular fa-calendar-check text-sky-400"></i>
                                    </div>
                                    
                                    <!-- 3 Mode Buttons: Before, After, On -->
                                    <div class="grid grid-cols-3 gap-1 bg-slate-950 p-1 rounded-lg border border-slate-800 text-[11px] font-medium text-center">
                                        <button type="button" @click="payDateMode = 'before'" 
                                                :class="payDateMode === 'before' ? 'bg-sky-600 text-white shadow font-bold' : 'text-slate-400 hover:text-slate-200'"
                                                class="py-1 px-2 rounded-md transition-all">
                                            Before
                                        </button>
                                        <button type="button" @click="payDateMode = 'after'" 
                                                :class="payDateMode === 'after' ? 'bg-sky-600 text-white shadow font-bold' : 'text-slate-400 hover:text-slate-200'"
                                                class="py-1 px-2 rounded-md transition-all">
                                            After
                                        </button>
                                        <button type="button" @click="payDateMode = 'on'" 
                                                :class="payDateMode === 'on' ? 'bg-sky-600 text-white shadow font-bold' : 'text-slate-400 hover:text-slate-200'"
                                                class="py-1 px-2 rounded-md transition-all">
                                            On
                                        </button>
                                    </div>

                                    <input type="hidden" name="pay_date_mode" :value="payDateMode">
                                    <input type="hidden" name="pay_date_val" :value="payDateVal">
                                    
                                    <div class="py-1 flex justify-center">
                                        <div x-ref="payDatePicker"></div>
                                    </div>

                                    <div class="flex items-center justify-between pt-2 border-t border-slate-800/80 text-[11px]">
                                        <div class="text-slate-400 font-mono">
                                            <template x-if="payDateVal">
                                                <span>
                                                    <span class="uppercase text-sky-400 font-semibold" x-text="payDateMode"></span>: <span x-text="payDateVal"></span>
                                                </span>
                                            </template>
                                            <template x-if="!payDateVal">
                                                <span>Belum ada tanggal</span>
                                            </template>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="payDateVal = ''; if ($refs.payDatePicker._flatpickr) $refs.payDatePicker._flatpickr.clear()" class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs">Clear</button>
                                            <button type="submit" class="px-3 py-1 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow transition-colors">Terapkan</button>
                                        </div>
                                    </div>
                                </div>
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
                            @can('update', $ticket)
                                <tr @dblclick="window.location.href = '{{ route('tickets.edit', $ticket->id) }}'"
                                    class="hover:bg-sky-950/40 cursor-pointer transition-colors group whitespace-nowrap border-b border-slate-800/40 select-none"
                                    title="Double klik untuk mengedit data tiket {{ $ticket->ticket_code }}">
                            @else
                                <tr @dblclick="selectedTicket = {{ json_encode([
                                        'ticket_code' => $ticket->ticket_code,
                                        'ticket_date' => $ticket->ticket_date->format('d M Y'),
                                        'origin' => $ticket->origin,
                                        'destination' => $ticket->destination,
                                        'transport_type' => $ticket->transport_type,
                                        'passenger_display' => implode(', ', $ticket->passengers_list) ?: $ticket->passenger_name,
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
                                    ]) }}; showModal = true"
                                    class="hover:bg-sky-950/40 cursor-pointer transition-colors group whitespace-nowrap border-b border-slate-800/40 select-none"
                                    title="Double klik untuk melihat Boarding Pass {{ $ticket->ticket_code }}">
                            @endcan
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

    <!-- Boarding Pass Preview Modal (Smart Double Click Fallback) -->
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
                                    <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white text-lg">
                                        <i class="fa-solid fa-ticket"></i>
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
                                    <button type="button" @click="showModal = false" class="w-8 h-8 rounded-full bg-black/20 hover:bg-black/40 flex items-center justify-center text-white transition-colors">
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
                                    <i class="fa-solid fa-plane-departure text-xl text-sky-300"></i>
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
                                    <a :href="selectedTicket.attachment_url" target="_blank" class="inline-flex items-center space-x-2 px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-sky-400 border border-slate-700 text-xs font-semibold transition-colors w-full justify-center">
                                        <i class="fa-solid fa-paperclip"></i>
                                        <span>Lihat Dokumen / Bukti Lampiran Original</span>
                                    </a>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection
