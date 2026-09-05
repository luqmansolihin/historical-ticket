@extends('layouts.app')

@section('title', 'Daftar Histori Tiket')

@section('content')
<div x-data="{
        selectedTicket: null,
        showModal: false,
        openPop: null,
        nextPageUrl: '{{ $tickets->nextPageUrl() }}',
        loading: false,
        hasMore: {{ $tickets->hasMorePages() ? 'true' : 'false' }},
        sorts: {{ json_encode($sorts ?? []) }},
        hasFilters: {{ ($search || $searchCode || $searchOrigin || $searchDestination || $searchPassenger || $searchBooker || $searchPayer || $searchRoute || $searchPerson || !empty($transportType) || !empty($status) || $dateAfter || $dateBefore || $dateOn || $payDateAfter || $payDateBefore || $payDateOn || $amountMin || $amountMax || $amountEq || $passengerCountMin || $passengerCountMax || $passengerCountEq) ? 'true' : 'false' }},
        init() {
            this.checkAutoFill();
            window.addEventListener('popstate', () => {
                this.applyFilters(window.location.href, false);
            });
        },
        checkHasFilters() {
            const form = document.getElementById('filter-form');
            if (!form) { this.hasFilters = false; return; }
            const formData = new FormData(form);
            let active = false;
            for (const [key, value] of formData.entries()) {
                if (key === 'sort' || key === 'sort_by' || key === 'sort_dir') continue;
                if (value && value.toString().trim() !== '') {
                    active = true;
                    break;
                }
            }
            this.hasFilters = active;
        },
        checkAutoFill() {
            this.$nextTick(() => {
                const el = this.$refs.scrollContainer;
                if (el && this.hasMore && !this.loading && el.scrollHeight <= el.clientHeight + 100) {
                    this.loadMore();
                }
            });
        },
        loadMore() {
            if (this.loading || !this.nextPageUrl) return;
            this.loading = true;
            fetch(this.nextPageUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('tickets-tbody');
                if (tbody && data.html) {
                    tbody.insertAdjacentHTML('beforeend', data.html);
                }
                this.nextPageUrl = data.next_page_url;
                this.hasMore = data.has_more;
                this.loading = false;
                this.checkAutoFill();
            })
            .catch(err => {
                console.error(err);
                this.loading = false;
            });
        },
        onScroll(e) {
            const el = e.target;
            if (!el) return;
            const bottomDistance = el.scrollHeight - (el.scrollTop + el.clientHeight);
            if (bottomDistance < 250 && this.hasMore && !this.loading) {
                this.loadMore();
            }
        },
        toggleSort(col) {
            const idx = this.sorts.findIndex(s => s.col === col);
            const defaultDir = (col === 'ticket_date' || col === 'payment_date' || col === 'amount' || col === 'passenger_count' || col === 'id') ? 'desc' : 'asc';
            
            if (idx === -1) {
                this.sorts.push({ col: col, dir: defaultDir });
            } else {
                const currentDir = this.sorts[idx].dir;
                const altDir = defaultDir === 'desc' ? 'asc' : 'desc';
                if (currentDir === defaultDir) {
                    this.sorts[idx].dir = altDir;
                } else {
                    this.sorts.splice(idx, 1);
                }
            }
            const sortInput = document.getElementById('sort_input');
            if (sortInput) sortInput.value = this.serializeSorts();
            this.applyFilters();
        },
        getSortIndex(col) {
            return this.sorts.findIndex(s => s.col === col);
        },
        getSortDir(col) {
            const item = this.sorts.find(s => s.col === col);
            return item ? item.dir : null;
        },
        serializeSorts() {
            return this.sorts.map(s => `${s.col}:${s.dir}`).join(',');
        },
        applyFilters(customUrl = null, updateHistory = true) {
            this.loading = true;
            this.checkHasFilters();
            const form = document.getElementById('filter-form');
            let fetchUrl = customUrl;

            if (!fetchUrl && form) {
                const formData = new FormData(form);
                const params = new URLSearchParams();
                for (const [key, value] of formData.entries()) {
                    if (value && value.toString().trim() !== '') {
                        params.append(key, value);
                    }
                }
                fetchUrl = form.action + (params.toString() ? '?' + params.toString() : '');
            }

            fetch(fetchUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('tickets-tbody');
                if (tbody && data.html) {
                    tbody.innerHTML = data.html;
                }
                this.nextPageUrl = data.next_page_url;
                this.hasMore = data.has_more;
                this.loading = false;
                this.openPop = null;

                const el = this.$refs.scrollContainer;
                if (el) el.scrollTop = 0;

                if (updateHistory && fetchUrl) {
                    window.history.pushState(null, '', fetchUrl);
                }

                const exportBtn = document.getElementById('export-csv-btn');
                if (exportBtn && fetchUrl) {
                    const u = new URL(fetchUrl, window.location.origin);
                    exportBtn.href = '{{ route('tickets.export') }}' + u.search;
                }

                this.checkAutoFill();
            })
            .catch(err => {
                console.error(err);
                this.loading = false;
            });
        },
        resetSorting() {
            this.sorts = [];
            const sortInput = document.getElementById('sort_input');
            if (sortInput) sortInput.value = '';
            this.applyFilters();
        },
        resetFilters() {
            const form = document.getElementById('filter-form');
            if (form) form.reset();
            this.hasFilters = false;
            this.applyFilters(form.action);
        }
    }" class="flex-1 flex flex-col min-h-0 h-full overflow-hidden">

    <!-- ERP Data Table Container & Column Header Filters -->
    <div class="glass-card rounded-2xl shadow-2xl relative z-10 no-print flex-1 flex flex-col min-h-0 h-full overflow-hidden">
        <!-- ERP Data Grid Action Toolbar -->
        <div class="px-3 py-1.5 bg-slate-900/90 border-b border-slate-800/80 flex items-center justify-between gap-2 shrink-0">
            <div class="text-xs text-slate-400 font-mono hidden sm:block">
                <i class="fa-solid fa-mouse-pointer text-sky-400 mr-1"></i> Double klik baris tabel untuk edit atau lihat Boarding Pass
            </div>

            <div class="flex items-center gap-2 ml-auto">
                <a href="{{ route('tickets.index') }}" x-show="hasFilters" x-cloak @click.prevent="resetFilters()" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium text-rose-300 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 transition-all shadow-sm" title="Reset Semua Filter Input">
                    <i class="fa-solid fa-filter-circle-xmark mr-1.5 text-xs"></i> Reset Filter
                </a>

                <button type="button" x-show="sorts.length > 0" x-cloak @click="resetSorting()" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium text-amber-300 bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/30 transition-all shadow-sm" title="Kembalikan Urutan Tabel ke Default (ID Terbaru)">
                    <i class="fa-solid fa-arrow-rotate-left mr-1.5 text-xs"></i> Reset Urutan
                </button>

                <a id="export-csv-btn" href="{{ route('tickets.export', request()->query()) }}" class="inline-flex items-center px-3.5 py-1.5 rounded-lg text-xs font-medium text-slate-200 bg-slate-800 hover:bg-slate-700 border border-slate-700 transition-all shadow-sm">
                    <i class="fa-solid fa-file-csv text-emerald-400 mr-1.5 text-xs"></i> Export CSV
                </a>

                @can('create', App\Models\TicketHistory::class)
                    <a href="{{ route('tickets.create') }}" class="inline-flex items-center px-4 py-1.5 rounded-lg text-xs font-semibold text-white bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-400 hover:to-blue-500 shadow-md shadow-sky-500/20 transition-all active:scale-95">
                        <i class="fa-solid fa-plus mr-1.5"></i> Tambah Tiket Baru
                    </a>
                @endcan
            </div>
        </div>

        <form id="filter-form" action="{{ route('tickets.index') }}" method="GET" @submit.prevent="applyFilters()" class="flex-1 flex flex-col min-h-0 h-full overflow-hidden justify-between">
            <input type="hidden" id="sort_input" name="sort" :value="serializeSorts()">

            <div x-ref="scrollContainer" class="overflow-auto flex-1 min-h-0" @scroll.passive="onScroll($event)">
                <table class="w-full text-left text-[9.5px] leading-tight text-slate-300 whitespace-nowrap border-collapse">
                    <thead class="bg-slate-900/95 text-[9px] uppercase font-bold text-slate-400 tracking-tight border-b border-slate-800 whitespace-nowrap sticky top-0 z-20 backdrop-blur-md">
                        <tr>
                            <!-- 1. Kode Tiket -->
                            <th class="py-1 px-2 whitespace-nowrap relative border-r border-slate-800/60" @click.outside="if (openPop === 'code') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <button type="button" @click="toggleSort('ticket_code')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('ticket_code') !== -1 ? 'text-sky-400 font-extrabold' : 'text-slate-300 hover:text-white'" title="Urutkan Kode Tiket">
                                        <span>Kode Tiket</span>
                                        <template x-if="getSortIndex('ticket_code') === -1">
                                            <i class="fa-solid fa-sort text-slate-600 text-[10px] group-hover/sort:text-slate-400 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('ticket_code') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-sky-400 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('ticket_code') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-sky-500/20 px-1 py-0.2 rounded-full border border-sky-500/40 font-mono" x-text="getSortIndex('ticket_code') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
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
                            <th class="py-1 px-2 whitespace-nowrap relative border-r border-slate-800/60" @click.outside="if (openPop === 'date') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <button type="button" @click="toggleSort('ticket_date')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('ticket_date') !== -1 ? 'text-sky-400 font-extrabold' : 'text-slate-300 hover:text-white'" title="Urutkan Tanggal Tiket">
                                        <span>Tgl Tiket</span>
                                        <template x-if="getSortIndex('ticket_date') === -1">
                                            <i class="fa-solid fa-sort text-slate-600 text-[10px] group-hover/sort:text-slate-400 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('ticket_date') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-sky-400 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('ticket_date') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-sky-500/20 px-1 py-0.2 rounded-full border border-sky-500/40 font-mono" x-text="getSortIndex('ticket_date') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'date' ? null : 'date')" class="p-1 rounded hover:bg-slate-800 transition-colors {{ ($dateAfter || $dateBefore || $dateOn) ? 'text-sky-400 font-bold bg-sky-500/20' : 'text-slate-500 hover:text-slate-300' }}" title="Filter Tanggal Tiket">
                                        <i class="fa-solid fa-caret-down text-xs"></i>
                                    </button>
                                </div>
                                <!-- 3-Input Date Filter Popover for Tgl Tiket -->
                                <div x-show="openPop === 'date'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3.5 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-3 text-left font-normal normal-case min-w-[260px]"
                                     x-data="{
                                         dateAfter: '{{ $dateAfter ?? '' }}',
                                         dateBefore: '{{ $dateBefore ?? '' }}',
                                         dateOn: '{{ $dateOn ?? '' }}',
                                         onAfterBeforeChange() {
                                             if (this.dateAfter || this.dateBefore) {
                                                 this.dateOn = '';
                                             }
                                         },
                                         onOnChange() {
                                             if (this.dateOn) {
                                                 this.dateAfter = '';
                                                 this.dateBefore = '';
                                             }
                                         }
                                     }">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Filter Tanggal Tiket</span>
                                        <i class="fa-regular fa-calendar-days text-sky-400"></i>
                                    </div>

                                    <div class="space-y-2.5">
                                        <div>
                                            <label class="block text-[11px] font-medium text-slate-400 mb-1">
                                                <span class="text-sky-400 font-semibold">After</span> (Dari / Setelah):
                                            </label>
                                            <input type="date" name="date_after" x-model="dateAfter" @change="onAfterBeforeChange()" class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 focus:border-sky-400 focus:outline-none font-mono">
                                        </div>

                                        <div>
                                            <label class="block text-[11px] font-medium text-slate-400 mb-1">
                                                <span class="text-sky-400 font-semibold">Before</span> (Sampai / Sebelum):
                                            </label>
                                            <input type="date" name="date_before" x-model="dateBefore" @change="onAfterBeforeChange()" class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 focus:border-sky-400 focus:outline-none font-mono">
                                        </div>

                                        <div class="pt-1 border-t border-slate-800/60">
                                            <label class="block text-[11px] font-medium text-slate-400 mb-1">
                                                <span class="text-amber-400 font-semibold">On</span> (Tepat Pada Tanggal):
                                            </label>
                                            <input type="date" name="date_on" x-model="dateOn" @change="onOnChange()" class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 focus:border-amber-400 focus:outline-none font-mono">
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between pt-2 border-t border-slate-800/80 text-[11px]">
                                        <button type="button" @click="dateAfter = ''; dateBefore = ''; dateOn = ''" class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs">Clear</button>
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow transition-colors">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 3. Asal -->
                            <th class="py-1 px-2 whitespace-nowrap relative border-r border-slate-800/60" @click.outside="if (openPop === 'origin') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <button type="button" @click="toggleSort('origin')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('origin') !== -1 ? 'text-sky-400 font-extrabold' : 'text-slate-300 hover:text-white'" title="Urutkan Kota Asal">
                                        <span>Asal</span>
                                        <template x-if="getSortIndex('origin') === -1">
                                            <i class="fa-solid fa-sort text-slate-600 text-[10px] group-hover/sort:text-slate-400 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('origin') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-sky-400 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('origin') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-sky-500/20 px-1 py-0.2 rounded-full border border-sky-500/40 font-mono" x-text="getSortIndex('origin') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
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
                            <th class="py-1 px-2 whitespace-nowrap relative border-r border-slate-800/60" @click.outside="if (openPop === 'destination') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <button type="button" @click="toggleSort('destination')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('destination') !== -1 ? 'text-sky-400 font-extrabold' : 'text-slate-300 hover:text-white'" title="Urutkan Kota Tujuan">
                                        <span>Tujuan</span>
                                        <template x-if="getSortIndex('destination') === -1">
                                            <i class="fa-solid fa-sort text-slate-600 text-[10px] group-hover/sort:text-slate-400 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('destination') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-sky-400 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('destination') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-sky-500/20 px-1 py-0.2 rounded-full border border-sky-500/40 font-mono" x-text="getSortIndex('destination') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
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
                            <th class="py-1 px-2 whitespace-nowrap relative border-r border-slate-800/60" @click.outside="if (openPop === 'transport') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <button type="button" @click="toggleSort('transport_type')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('transport_type') !== -1 ? 'text-sky-400 font-extrabold' : 'text-slate-300 hover:text-white'" title="Urutkan Transportasi">
                                        <span>Transportasi</span>
                                        <template x-if="getSortIndex('transport_type') === -1">
                                            <i class="fa-solid fa-sort text-slate-600 text-[10px] group-hover/sort:text-slate-400 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('transport_type') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-sky-400 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('transport_type') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-sky-500/20 px-1 py-0.2 rounded-full border border-sky-500/40 font-mono" x-text="getSortIndex('transport_type') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
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
                            <th class="py-1 px-2 whitespace-nowrap relative border-r border-slate-800/60" @click.outside="if (openPop === 'passenger') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <button type="button" @click="toggleSort('passenger_name')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('passenger_name') !== -1 ? 'text-sky-400 font-extrabold' : 'text-slate-300 hover:text-white'" title="Urutkan Nama Penumpang">
                                        <span>Nama Penumpang</span>
                                        <template x-if="getSortIndex('passenger_name') === -1">
                                            <i class="fa-solid fa-sort text-slate-600 text-[10px] group-hover/sort:text-slate-400 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('passenger_name') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-sky-400 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('passenger_name') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-sky-500/20 px-1 py-0.2 rounded-full border border-sky-500/40 font-mono" x-text="getSortIndex('passenger_name') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
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
                            <th class="py-1 px-2 text-center whitespace-nowrap relative border-r border-slate-800/60" @click.outside="if (openPop === 'passenger_count') openPop = null">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" @click="toggleSort('passenger_count')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('passenger_count') !== -1 ? 'text-sky-400 font-extrabold' : 'text-slate-300 hover:text-white'" title="Urutkan Jumlah Penumpang">
                                        <span>Jml</span>
                                        <template x-if="getSortIndex('passenger_count') === -1">
                                            <i class="fa-solid fa-sort text-slate-600 text-[10px] group-hover/sort:text-slate-400 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('passenger_count') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-sky-400 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('passenger_count') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-sky-500/20 px-1 py-0.2 rounded-full border border-sky-500/40 font-mono" x-text="getSortIndex('passenger_count') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'passenger_count' ? null : 'passenger_count')" class="p-1 rounded hover:bg-slate-800 transition-colors {{ ($passengerCountMin || $passengerCountMax || $passengerCountEq) ? 'text-sky-400 font-bold bg-sky-500/20' : 'text-slate-500 hover:text-slate-300' }}" title="Filter Jumlah Penumpang">
                                        <i class="fa-solid fa-caret-down text-xs"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'passenger_count'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3.5 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-3 text-left font-normal normal-case min-w-[250px]"
                                     x-data="{
                                         min: '{{ $passengerCountMin ?? '' }}',
                                         max: '{{ $passengerCountMax ?? '' }}',
                                         eq: '{{ $passengerCountEq ?? '' }}',
                                         onMinMaxChange() {
                                             if (this.min || this.max) { this.eq = ''; }
                                         },
                                         onEqChange() {
                                             if (this.eq) { this.min = ''; this.max = ''; }
                                         }
                                     }">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Filter Jml Penumpang</span>
                                        <i class="fa-solid fa-users text-sky-400"></i>
                                    </div>
                                    <div class="space-y-2.5">
                                        <div>
                                            <label class="block text-[11px] font-medium text-slate-400 mb-1">
                                                <span class="text-sky-400 font-semibold">&ge;</span> Lebih Besar Sama Dengan:
                                            </label>
                                            <input type="number" name="passenger_count_min" x-model="min" @input="onMinMaxChange()" placeholder="Contoh: 2" step="any" class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 placeholder-slate-500 focus:border-sky-400 focus:outline-none font-mono">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-medium text-slate-400 mb-1">
                                                <span class="text-sky-400 font-semibold">&le;</span> Lebih Kecil Sama Dengan:
                                            </label>
                                            <input type="number" name="passenger_count_max" x-model="max" @input="onMinMaxChange()" placeholder="Contoh: 5" step="any" class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 placeholder-slate-500 focus:border-sky-400 focus:outline-none font-mono">
                                        </div>
                                        <div class="pt-1 border-t border-slate-800/60">
                                            <label class="block text-[11px] font-medium text-slate-400 mb-1">
                                                <span class="text-amber-400 font-semibold">=</span> Sama Dengan:
                                            </label>
                                            <input type="number" name="passenger_count_eq" x-model="eq" @input="onEqChange()" placeholder="Contoh: 3" step="any" class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 placeholder-slate-500 focus:border-amber-400 focus:outline-none font-mono">
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between pt-2 border-t border-slate-800/80 text-[11px]">
                                        <button type="button" @click="min = ''; max = ''; eq = ''" class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs">Clear</button>
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow transition-colors">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 8. Pemesan -->
                            <th class="py-1 px-2 whitespace-nowrap relative border-r border-slate-800/60" @click.outside="if (openPop === 'booker') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <button type="button" @click="toggleSort('booked_by')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('booked_by') !== -1 ? 'text-sky-400 font-extrabold' : 'text-slate-300 hover:text-white'" title="Urutkan Pemesan">
                                        <span>Pemesan</span>
                                        <template x-if="getSortIndex('booked_by') === -1">
                                            <i class="fa-solid fa-sort text-slate-600 text-[10px] group-hover/sort:text-slate-400 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('booked_by') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-sky-400 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('booked_by') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-sky-500/20 px-1 py-0.2 rounded-full border border-sky-500/40 font-mono" x-text="getSortIndex('booked_by') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
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
                            <th class="py-1 px-2 whitespace-nowrap relative border-r border-slate-800/60" @click.outside="if (openPop === 'payer') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <button type="button" @click="toggleSort('paid_by')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('paid_by') !== -1 ? 'text-sky-400 font-extrabold' : 'text-slate-300 hover:text-white'" title="Urutkan Pembayar">
                                        <span>Pembayar</span>
                                        <template x-if="getSortIndex('paid_by') === -1">
                                            <i class="fa-solid fa-sort text-slate-600 text-[10px] group-hover/sort:text-slate-400 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('paid_by') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-sky-400 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('paid_by') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-sky-500/20 px-1 py-0.2 rounded-full border border-sky-500/40 font-mono" x-text="getSortIndex('paid_by') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
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
                            <th class="py-1 px-2 whitespace-nowrap relative border-r border-slate-800/60" @click.outside="if (openPop === 'pay_date') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <button type="button" @click="toggleSort('payment_date')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('payment_date') !== -1 ? 'text-sky-400 font-extrabold' : 'text-slate-300 hover:text-white'" title="Urutkan Tanggal Bayar">
                                        <span>Tgl Bayar</span>
                                        <template x-if="getSortIndex('payment_date') === -1">
                                            <i class="fa-solid fa-sort text-slate-600 text-[10px] group-hover/sort:text-slate-400 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('payment_date') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-sky-400 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('payment_date') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-sky-500/20 px-1 py-0.2 rounded-full border border-sky-500/40 font-mono" x-text="getSortIndex('payment_date') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'pay_date' ? null : 'pay_date')" class="p-1 rounded hover:bg-slate-800 transition-colors {{ ($payDateAfter || $payDateBefore || $payDateOn) ? 'text-sky-400 font-bold bg-sky-500/20' : 'text-slate-500 hover:text-slate-300' }}" title="Filter Tanggal Bayar">
                                        <i class="fa-solid fa-caret-down text-xs"></i>
                                    </button>
                                </div>
                                <!-- 3-Input Date Filter Popover for Tgl Bayar -->
                                <div x-show="openPop === 'pay_date'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3.5 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-3 text-left font-normal normal-case min-w-[260px]"
                                     x-data="{
                                         payDateAfter: '{{ $payDateAfter ?? '' }}',
                                         payDateBefore: '{{ $payDateBefore ?? '' }}',
                                         payDateOn: '{{ $payDateOn ?? '' }}',
                                         onAfterBeforeChange() {
                                             if (this.payDateAfter || this.payDateBefore) {
                                                 this.payDateOn = '';
                                             }
                                         },
                                         onOnChange() {
                                             if (this.payDateOn) {
                                                 this.payDateAfter = '';
                                                 this.payDateBefore = '';
                                             }
                                         }
                                     }">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Filter Tanggal Bayar</span>
                                        <i class="fa-regular fa-calendar-check text-sky-400"></i>
                                    </div>

                                    <div class="space-y-2.5">
                                        <div>
                                            <label class="block text-[11px] font-medium text-slate-400 mb-1">
                                                <span class="text-sky-400 font-semibold">After</span> (Dari / Setelah):
                                            </label>
                                            <input type="date" name="pay_date_after" x-model="payDateAfter" @change="onAfterBeforeChange()" class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 focus:border-sky-400 focus:outline-none font-mono">
                                        </div>

                                        <div>
                                            <label class="block text-[11px] font-medium text-slate-400 mb-1">
                                                <span class="text-sky-400 font-semibold">Before</span> (Sampai / Sebelum):
                                            </label>
                                            <input type="date" name="pay_date_before" x-model="payDateBefore" @change="onAfterBeforeChange()" class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 focus:border-sky-400 focus:outline-none font-mono">
                                        </div>

                                        <div class="pt-1 border-t border-slate-800/60">
                                            <label class="block text-[11px] font-medium text-slate-400 mb-1">
                                                <span class="text-amber-400 font-semibold">On</span> (Tepat Pada Tanggal):
                                            </label>
                                            <input type="date" name="pay_date_on" x-model="payDateOn" @change="onOnChange()" class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 focus:border-amber-400 focus:outline-none font-mono">
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between pt-2 border-t border-slate-800/80 text-[11px]">
                                        <button type="button" @click="payDateAfter = ''; payDateBefore = ''; payDateOn = ''" class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs">Clear</button>
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow transition-colors">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 11. Biaya (IDR) -->
                            <th class="py-1 px-2 text-right whitespace-nowrap relative border-r border-slate-800/60" @click.outside="if (openPop === 'amount') openPop = null">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" @click="toggleSort('amount')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('amount') !== -1 ? 'text-emerald-400 font-extrabold' : 'text-slate-300 hover:text-white'" title="Urutkan Biaya">
                                        <span>Biaya (IDR)</span>
                                        <template x-if="getSortIndex('amount') === -1">
                                            <i class="fa-solid fa-sort text-slate-600 text-[10px] group-hover/sort:text-slate-400 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('amount') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-emerald-400 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('amount') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-emerald-500/20 px-1 py-0.2 rounded-full border border-emerald-500/40 font-mono" x-text="getSortIndex('amount') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'amount' ? null : 'amount')" class="p-1 rounded hover:bg-slate-800 transition-colors {{ ($amountMin || $amountMax || $amountEq) ? 'text-emerald-400 font-bold bg-emerald-500/20' : 'text-slate-500 hover:text-slate-300' }}" title="Filter Rentang Biaya">
                                        <i class="fa-solid fa-caret-down text-xs"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'amount'" x-cloak x-transition class="absolute z-50 right-0 mt-2 p-3.5 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-3 text-left font-normal normal-case min-w-[250px]"
                                     x-data="{
                                         min: '{{ $amountMin ?? '' }}',
                                         max: '{{ $amountMax ?? '' }}',
                                         eq: '{{ $amountEq ?? '' }}',
                                         onMinMaxChange() {
                                             if (this.min || this.max) { this.eq = ''; }
                                         },
                                         onEqChange() {
                                             if (this.eq) { this.min = ''; this.max = ''; }
                                         }
                                     }">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Filter Biaya (IDR)</span>
                                        <i class="fa-solid fa-money-bill-wave text-emerald-400"></i>
                                    </div>
                                    <div class="space-y-2.5">
                                        <div>
                                            <label class="block text-[11px] font-medium text-slate-400 mb-1">
                                                <span class="text-emerald-400 font-semibold">&ge;</span> Lebih Besar Sama Dengan:
                                            </label>
                                            <input type="number" name="amount_min" x-model="min" @input="onMinMaxChange()" placeholder="Contoh: 500000" step="any" class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 placeholder-slate-500 focus:border-emerald-400 focus:outline-none font-mono">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-medium text-slate-400 mb-1">
                                                <span class="text-emerald-400 font-semibold">&le;</span> Lebih Kecil Sama Dengan:
                                            </label>
                                            <input type="number" name="amount_max" x-model="max" @input="onMinMaxChange()" placeholder="Contoh: 5000000" step="any" class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 placeholder-slate-500 focus:border-emerald-400 focus:outline-none font-mono">
                                        </div>
                                        <div class="pt-1 border-t border-slate-800/60">
                                            <label class="block text-[11px] font-medium text-slate-400 mb-1">
                                                <span class="text-amber-400 font-semibold">=</span> Sama Dengan:
                                            </label>
                                            <input type="number" name="amount_eq" x-model="eq" @input="onEqChange()" placeholder="Contoh: 1500000" step="any" class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 placeholder-slate-500 focus:border-amber-400 focus:outline-none font-mono">
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between pt-2 border-t border-slate-800/80 text-[11px]">
                                        <button type="button" @click="min = ''; max = ''; eq = ''" class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs">Clear</button>
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow transition-colors">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 12. Status -->
                            <th class="py-1 px-2 text-center whitespace-nowrap relative" @click.outside="if (openPop === 'status') openPop = null">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" @click="toggleSort('status')" class="flex items-center gap-1 font-bold text-slate-300 hover:text-white transition-colors cursor-pointer select-none group/sort" title="Urutkan Status">
                                        <span>Status</span>
                                        @if(($sortBy ?? '') === 'status')
                                            <i class="fa-solid {{ ($sortDir ?? '') === 'asc' ? 'fa-arrow-up-wide-short text-emerald-400' : 'fa-arrow-down-wide-short text-emerald-400' }} text-[10px]"></i>
                                        @else
                                            <i class="fa-solid fa-sort text-slate-600 text-[10px] group-hover/sort:text-slate-400 transition-colors"></i>
                                        @endif
                                    </button>
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
                    <tbody id="tickets-tbody" class="divide-y divide-slate-800/60 whitespace-nowrap font-sans">
                        @include('tickets._rows', ['tickets' => $tickets])
                    </tbody>
                </table>
            </div>
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
