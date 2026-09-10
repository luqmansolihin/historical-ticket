@extends('layouts.app')

@section('title', 'Daftar Histori Hotel')

@section('content')
<div x-data="{
        selectedHotel: null,
        showModal: false,
        openPop: null,
        nextPageUrl: '{{ $hotels->nextPageUrl() }}',
        loading: false,
        hasMore: {{ $hotels->hasMorePages() ? 'true' : 'false' }},
        sorts: {{ json_encode($sorts ?? []) }},
        hasFilters: {{ ($search || $searchCode || $searchInvoice || $searchHotel || $searchGuest || $searchBooker || $searchPayer || !empty($status) || $dateAfter || $dateBefore || $dateOn || $checkInFrom || $checkInTo || $checkInOn || $checkOutFrom || $checkOutTo || $checkOutOn || $nightCountMin || $nightCountMax || $nightCountEq || $payDateAfter || $payDateBefore || $payDateOn || $amountMin || $amountMax || $amountEq || $guestCountMin || $guestCountMax || $guestCountEq) ? 'true' : 'false' }},
        activeFilters: {
            code: {{ !empty($searchCode) ? 'true' : 'false' }},
            invoice: {{ !empty($searchInvoice) ? 'true' : 'false' }},
            date: {{ ($dateAfter || $dateBefore || $dateOn) ? 'true' : 'false' }},
            hotel: {{ !empty($searchHotel) ? 'true' : 'false' }},
            check_in: {{ ($checkInFrom || $checkInTo || $checkInOn) ? 'true' : 'false' }},
            check_out: {{ ($checkOutFrom || $checkOutTo || $checkOutOn) ? 'true' : 'false' }},
            night_count: {{ ($nightCountMin || $nightCountMax || $nightCountEq) ? 'true' : 'false' }},
            guest: {{ !empty($searchGuest) ? 'true' : 'false' }},
            guest_count: {{ ($guestCountMin || $guestCountMax || $guestCountEq) ? 'true' : 'false' }},
            booker: {{ !empty($searchBooker) ? 'true' : 'false' }},
            payer: {{ !empty($searchPayer) ? 'true' : 'false' }},
            pay_date: {{ ($payDateAfter || $payDateBefore || $payDateOn) ? 'true' : 'false' }},
            amount: {{ ($amountMin || $amountMax || $amountEq) ? 'true' : 'false' }},
            status: {{ !empty($status) ? 'true' : 'false' }},
        },
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
            
            this.activeFilters.code = !!(formData.get('search_code') && formData.get('search_code').trim());
            this.activeFilters.invoice = !!(formData.get('search_invoice') && formData.get('search_invoice').trim());
            this.activeFilters.date = !!((formData.get('date_after') && formData.get('date_after').trim()) || (formData.get('date_before') && formData.get('date_before').trim()) || (formData.get('date_on') && formData.get('date_on').trim()));
            this.activeFilters.hotel = !!(formData.get('search_hotel') && formData.get('search_hotel').trim());
            this.activeFilters.check_in = !!((formData.get('check_in_from') && formData.get('check_in_from').trim()) || (formData.get('check_in_to') && formData.get('check_in_to').trim()) || (formData.get('check_in_on') && formData.get('check_in_on').trim()));
            this.activeFilters.check_out = !!((formData.get('check_out_from') && formData.get('check_out_from').trim()) || (formData.get('check_out_to') && formData.get('check_out_to').trim()) || (formData.get('check_out_on') && formData.get('check_out_on').trim()));
            this.activeFilters.night_count = !!((formData.get('night_count_min') && formData.get('night_count_min').trim()) || (formData.get('night_count_max') && formData.get('night_count_max').trim()) || (formData.get('night_count_eq') && formData.get('night_count_eq').trim()));
            this.activeFilters.guest = !!(formData.get('search_guest') && formData.get('search_guest').trim());
            this.activeFilters.guest_count = !!((formData.get('guest_count_min') && formData.get('guest_count_min').trim()) || (formData.get('guest_count_max') && formData.get('guest_count_max').trim()) || (formData.get('guest_count_eq') && formData.get('guest_count_eq').trim()));
            this.activeFilters.booker = !!(formData.get('search_booker') && formData.get('search_booker').trim());
            this.activeFilters.payer = !!(formData.get('search_payer') && formData.get('search_payer').trim());
            this.activeFilters.pay_date = !!((formData.get('pay_date_after') && formData.get('pay_date_after').trim()) || (formData.get('pay_date_before') && formData.get('pay_date_before').trim()) || (formData.get('pay_date_on') && formData.get('pay_date_on').trim()));
            this.activeFilters.amount = !!((formData.get('amount_min') && formData.get('amount_min').trim()) || (formData.get('amount_max') && formData.get('amount_max').trim()) || (formData.get('amount_eq') && formData.get('amount_eq').trim()));
            
            const statuses = formData.getAll('status[]');
            this.activeFilters.status = statuses.length > 0 && statuses.some(s => s.trim() !== '');

            this.hasFilters = Object.values(this.activeFilters).some(v => v === true);
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
                const tbody = document.getElementById('hotels-tbody');
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
            const defaultDir = (col === 'booking_date' || col === 'check_in_date' || col === 'check_out_date' || col === 'payment_date' || col === 'amount' || col === 'guest_count' || col === 'id') ? 'desc' : 'asc';
            
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
                const tbody = document.getElementById('hotels-tbody');
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
                    exportBtn.href = '{{ route('hotels.export') }}' + u.search;
                }

                this.checkAutoFill();
            })
            .catch(err => {
                console.error(err);
                this.loading = false;
            });
        },
        resetFilters() {
            const form = document.getElementById('filter-form');
            if (form) form.reset();
            this.sorts = [];
            this.hasFilters = false;
            Object.keys(this.activeFilters).forEach(k => this.activeFilters[k] = false);
            const sortInput = document.getElementById('sort_input');
            if (sortInput) sortInput.value = '';
            this.applyFilters(form.action);
        }
    }" class="flex-1 flex flex-col min-h-0 h-full overflow-hidden">

    <!-- Data Table Container & Column Header Filters -->
    <div class="glass-card rounded-2xl shadow-2xl relative z-10 no-print flex-1 flex flex-col min-h-0 h-full overflow-hidden">
        <!-- Data Grid Action Toolbar -->
        <div class="px-3 py-1.5 bg-slate-900/90 border-b border-slate-800/80 flex items-center justify-between gap-2 shrink-0">
            <div class="text-xs text-slate-400 font-mono hidden sm:block">
                <i class="fa-solid fa-mouse-pointer text-amber-400 mr-1"></i> Double klik baris tabel untuk edit atau lihat Voucher Hotel
            </div>

            <div class="flex items-center gap-2 ml-auto">
                <a href="{{ route('hotels.index') }}" x-show="hasFilters || sorts.length > 0" x-cloak @click.prevent="resetFilters()" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium text-rose-300 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 transition-all shadow-sm" title="Reset Filter & Urutan Tabel">
                    <i class="fa-solid fa-rotate-left mr-1.5 text-xs"></i> Reset Filter & Urutan
                </a>

                <a id="export-csv-btn" href="{{ route('hotels.export', request()->query()) }}" class="inline-flex items-center px-3.5 py-1.5 rounded-lg text-xs font-medium text-slate-200 bg-slate-800 hover:bg-slate-700 border border-slate-700 transition-all shadow-sm">
                    <i class="fa-solid fa-file-csv text-emerald-400 mr-1.5 text-xs"></i> Ekspor CSV
                </a>

                @can('create', App\Models\HotelHistory::class)
                    <a href="{{ route('hotels.create') }}" class="inline-flex items-center px-4 py-1.5 rounded-lg text-xs font-semibold text-white bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 shadow-md shadow-amber-500/20 transition-all active:scale-95">
                        <i class="fa-solid fa-plus mr-1.5"></i> Tambah Histori Hotel
                    </a>
                @endcan
            </div>
        </div>

        <form id="filter-form" action="{{ route('hotels.index') }}" method="GET" @submit.prevent="applyFilters()" class="flex-1 flex flex-col min-h-0 h-full overflow-hidden justify-between">
            <input type="hidden" id="sort_input" name="sort" :value="serializeSorts()">

            <div x-ref="scrollContainer" class="overflow-auto flex-1 min-h-0" @scroll.passive="onScroll($event)">
                <table class="w-full text-left text-[9.5px] leading-tight text-slate-300 whitespace-nowrap border-collapse">
                    <thead class="bg-slate-900/95 text-[9px] uppercase font-bold text-slate-400 tracking-tight border-b border-slate-800 whitespace-nowrap sticky top-0 z-20 backdrop-blur-md">
                        <tr>
                            <!-- 1. Kode Booking -->
                            <th class="py-1 px-2 whitespace-nowrap relative border-r border-slate-800/60 transition-colors" :class="activeFilters.code ? 'bg-amber-950/80 border-b-2 border-b-amber-400 text-amber-200' : ''" @click.outside="if (openPop === 'code') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <button type="button" @click="toggleSort('booking_code')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('booking_code') !== -1 ? 'text-amber-400 font-extrabold' : 'text-slate-300 hover:text-white'" title="Urutkan Kode Booking">
                                        <span>Kode Booking</span>
                                        <template x-if="getSortIndex('booking_code') === -1">
                                            <i class="fa-solid fa-sort text-slate-600 text-[10px] group-hover/sort:text-slate-400 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('booking_code') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-amber-400 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('booking_code') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-amber-500/20 px-1 py-0.2 rounded-full border border-amber-500/40 font-mono" x-text="getSortIndex('booking_code') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'code' ? null : 'code')" class="p-1 rounded transition-colors" :class="activeFilters.code ? 'text-amber-300 bg-amber-500/30 ring-1 ring-amber-400/50 font-bold shadow-sm shadow-amber-500/20' : 'text-slate-500 hover:text-slate-300 hover:bg-slate-800'" title="Filter Kode Booking">
                                        <i class="fa-solid" :class="activeFilters.code ? 'fa-filter text-amber-400 text-[11px]' : 'fa-caret-down text-xs'"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'code'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-3 text-left font-normal normal-case min-w-[220px]">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Filter Kode Booking</span>
                                        <i class="fa-solid fa-hotel text-amber-400"></i>
                                    </div>
                                    <input type="text" name="search_code" value="{{ $searchCode }}" placeholder="Cari kode booking..." class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 placeholder-slate-500 focus:border-amber-400 focus:outline-none">
                                    <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-800/80">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow transition-colors">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 2. Kode Invoice -->
                            <th class="py-1 px-2 whitespace-nowrap relative border-r border-slate-800/60 transition-colors" :class="activeFilters.invoice ? 'bg-amber-950/80 border-b-2 border-b-amber-400 text-amber-200' : ''" @click.outside="if (openPop === 'invoice') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <button type="button" @click="toggleSort('invoice_code')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('invoice_code') !== -1 ? 'text-amber-400 font-extrabold' : 'text-slate-300 hover:text-white'" title="Urutkan Kode Invoice">
                                        <span>Kode Invoice</span>
                                        <template x-if="getSortIndex('invoice_code') === -1">
                                            <i class="fa-solid fa-sort text-slate-600 text-[10px] group-hover/sort:text-slate-400 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('invoice_code') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-amber-400 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('invoice_code') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-amber-500/20 px-1 py-0.2 rounded-full border border-amber-500/40 font-mono" x-text="getSortIndex('invoice_code') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'invoice' ? null : 'invoice')" class="p-1 rounded transition-colors" :class="activeFilters.invoice ? 'text-amber-300 bg-amber-500/30 ring-1 ring-amber-400/50 font-bold shadow-sm shadow-amber-500/20' : 'text-slate-500 hover:text-slate-300 hover:bg-slate-800'" title="Filter Kode Invoice">
                                        <i class="fa-solid" :class="activeFilters.invoice ? 'fa-filter text-amber-400 text-[11px]' : 'fa-caret-down text-xs'"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'invoice'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-3 text-left font-normal normal-case min-w-[220px]">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Filter Kode Invoice</span>
                                        <i class="fa-solid fa-receipt text-amber-400"></i>
                                    </div>
                                    <input type="text" name="search_invoice" value="{{ $searchInvoice }}" placeholder="Cari kode invoice..." class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 placeholder-slate-500 focus:border-amber-400 focus:outline-none">
                                    <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-800/80">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow transition-colors">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 3. Tgl Booking -->
                            <th class="py-1 px-2 whitespace-nowrap relative border-r border-slate-800/60 transition-colors" :class="activeFilters.date ? 'bg-amber-950/80 border-b-2 border-b-amber-400 text-amber-200' : ''" @click.outside="if (openPop === 'date') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <button type="button" @click="toggleSort('booking_date')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('booking_date') !== -1 ? 'text-amber-400 font-extrabold' : 'text-slate-300 hover:text-white'" title="Urutkan Tanggal Booking">
                                        <span>Tgl Booking</span>
                                        <template x-if="getSortIndex('booking_date') === -1">
                                            <i class="fa-solid fa-sort text-slate-600 text-[10px] group-hover/sort:text-slate-400 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('booking_date') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-amber-400 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('booking_date') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-amber-500/20 px-1 py-0.2 rounded-full border border-amber-500/40 font-mono" x-text="getSortIndex('booking_date') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'date' ? null : 'date')" class="p-1 rounded transition-colors" :class="activeFilters.date ? 'text-amber-300 bg-amber-500/30 ring-1 ring-amber-400/50 font-bold shadow-sm shadow-amber-500/20' : 'text-slate-500 hover:text-slate-300 hover:bg-slate-800'" title="Filter Tanggal Booking">
                                        <i class="fa-solid" :class="activeFilters.date ? 'fa-filter text-amber-400 text-[11px]' : 'fa-caret-down text-xs'"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'date'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3.5 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-3 text-left font-normal normal-case min-w-[260px]"
                                     x-data="{
                                         dateAfter: '{{ $dateAfter ?? '' }}',
                                         dateBefore: '{{ $dateBefore ?? '' }}',
                                         dateOn: '{{ $dateOn ?? '' }}',
                                         onAfterBeforeChange() {
                                             if (this.dateAfter || this.dateBefore) { this.dateOn = ''; }
                                         },
                                         onOnChange() {
                                             if (this.dateOn) { this.dateAfter = ''; this.dateBefore = ''; }
                                         }
                                     }">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Filter Tanggal Booking</span>
                                        <i class="fa-regular fa-calendar-days text-amber-400"></i>
                                    </div>
                                    <div class="space-y-2.5">
                                        <div>
                                            <label class="block text-[11px] font-medium text-slate-400 mb-1">Dari (Setelah):</label>
                                            <input type="date" name="date_after" x-model="dateAfter" @change="onAfterBeforeChange()" onclick="this.showPicker?.()" class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 focus:border-amber-400 focus:outline-none font-mono cursor-pointer">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-medium text-slate-400 mb-1">Sampai (Sebelum):</label>
                                            <input type="date" name="date_before" x-model="dateBefore" @change="onAfterBeforeChange()" onclick="this.showPicker?.()" class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 focus:border-amber-400 focus:outline-none font-mono cursor-pointer">
                                        </div>
                                        <div class="pt-1 border-t border-slate-800/60">
                                            <label class="block text-[11px] font-medium text-slate-400 mb-1">Tepat Pada Tanggal:</label>
                                            <input type="date" name="date_on" x-model="dateOn" @change="onOnChange()" onclick="this.showPicker?.()" class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 focus:border-amber-400 focus:outline-none font-mono cursor-pointer">
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between pt-2 border-t border-slate-800/80 text-[11px]">
                                        <button type="button" @click="dateAfter = ''; dateBefore = ''; dateOn = ''" class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs">Clear</button>
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow transition-colors">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 4. Nama Hotel -->
                            <th class="py-1 px-2 whitespace-nowrap relative border-r border-slate-800/60 transition-colors" :class="activeFilters.hotel ? 'bg-amber-950/80 border-b-2 border-b-amber-400 text-amber-200' : ''" @click.outside="if (openPop === 'hotel') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <button type="button" @click="toggleSort('hotel_name')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('hotel_name') !== -1 ? 'text-amber-400 font-extrabold' : 'text-slate-300 hover:text-white'" title="Urutkan Nama Hotel">
                                        <span>Nama Hotel</span>
                                        <template x-if="getSortIndex('hotel_name') === -1">
                                            <i class="fa-solid fa-sort text-slate-600 text-[10px] group-hover/sort:text-slate-400 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('hotel_name') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-amber-400 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('hotel_name') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-amber-500/20 px-1 py-0.2 rounded-full border border-amber-500/40 font-mono" x-text="getSortIndex('hotel_name') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'hotel' ? null : 'hotel')" class="p-1 rounded transition-colors" :class="activeFilters.hotel ? 'text-amber-300 bg-amber-500/30' : 'text-slate-500 hover:text-slate-300'">
                                        <i class="fa-solid" :class="activeFilters.hotel ? 'fa-filter text-amber-400 text-[11px]' : 'fa-caret-down text-xs'"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'hotel'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-3 text-left font-normal normal-case min-w-[220px]">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Filter Nama Hotel</span>
                                        <i class="fa-solid fa-hotel text-amber-400"></i>
                                    </div>
                                    <input type="text" name="search_hotel" value="{{ $searchHotel }}" placeholder="Cari nama hotel..." class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 placeholder-slate-500 focus:border-amber-400 focus:outline-none">
                                    <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-800/80">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow transition-colors">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 5. Check In -->
                            <th class="py-1 px-2 whitespace-nowrap relative border-r border-slate-800/60 transition-colors" :class="activeFilters.check_in ? 'bg-amber-950/80 border-b-2 border-b-amber-400 text-amber-200' : ''" @click.outside="if (openPop === 'check_in') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <button type="button" @click="toggleSort('check_in_date')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('check_in_date') !== -1 ? 'text-amber-400 font-extrabold' : 'text-slate-300 hover:text-white'" title="Urutkan Check In">
                                        <span>Check In</span>
                                        <template x-if="getSortIndex('check_in_date') === -1">
                                            <i class="fa-solid fa-sort text-slate-600 text-[10px] group-hover/sort:text-slate-400 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('check_in_date') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-amber-400 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('check_in_date') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-amber-500/20 px-1 py-0.2 rounded-full border border-amber-500/40 font-mono" x-text="getSortIndex('check_in_date') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'check_in' ? null : 'check_in')" class="p-1 rounded transition-colors" :class="activeFilters.check_in ? 'text-amber-300 bg-amber-500/30' : 'text-slate-500 hover:text-slate-300'">
                                        <i class="fa-solid" :class="activeFilters.check_in ? 'fa-filter text-amber-400 text-[11px]' : 'fa-caret-down text-xs'"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'check_in'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3.5 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-3 text-left font-normal normal-case min-w-[260px]"
                                     x-data="{
                                         from: '{{ $checkInFrom ?? '' }}',
                                         to: '{{ $checkInTo ?? '' }}',
                                         on: '{{ $checkInOn ?? '' }}'
                                     }">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Filter Tanggal Check In</span>
                                        <i class="fa-solid fa-calendar-check text-emerald-400"></i>
                                    </div>
                                    <div class="space-y-2.5">
                                        <div>
                                            <label class="block text-[11px] font-medium text-slate-400 mb-1">Dari Check In:</label>
                                            <input type="date" name="check_in_from" x-model="from" onclick="this.showPicker?.()" class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 font-mono cursor-pointer">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-medium text-slate-400 mb-1">Sampai Check In:</label>
                                            <input type="date" name="check_in_to" x-model="to" onclick="this.showPicker?.()" class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 font-mono cursor-pointer">
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between pt-2 border-t border-slate-800/80 text-[11px]">
                                        <button type="button" @click="from = ''; to = ''; on = ''" class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs">Clear</button>
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 6. Check Out -->
                            <th class="py-1 px-2 whitespace-nowrap relative border-r border-slate-800/60 transition-colors" :class="activeFilters.check_out ? 'bg-amber-950/80 border-b-2 border-b-amber-400 text-amber-200' : ''" @click.outside="if (openPop === 'check_out') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <button type="button" @click="toggleSort('check_out_date')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('check_out_date') !== -1 ? 'text-amber-400 font-extrabold' : 'text-slate-300 hover:text-white'" title="Urutkan Check Out">
                                        <span>Check Out</span>
                                        <template x-if="getSortIndex('check_out_date') === -1">
                                            <i class="fa-solid fa-sort text-slate-600 text-[10px] group-hover/sort:text-slate-400 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('check_out_date') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-amber-400 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('check_out_date') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-amber-500/20 px-1 py-0.2 rounded-full border border-amber-500/40 font-mono" x-text="getSortIndex('check_out_date') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'check_out' ? null : 'check_out')" class="p-1 rounded transition-colors" :class="activeFilters.check_out ? 'text-amber-300 bg-amber-500/30' : 'text-slate-500 hover:text-slate-300'">
                                        <i class="fa-solid" :class="activeFilters.check_out ? 'fa-filter text-amber-400 text-[11px]' : 'fa-caret-down text-xs'"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'check_out'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3.5 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-3 text-left font-normal normal-case min-w-[260px]"
                                     x-data="{
                                         from: '{{ $checkOutFrom ?? '' }}',
                                         to: '{{ $checkOutTo ?? '' }}',
                                         on: '{{ $checkOutOn ?? '' }}'
                                     }">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Filter Tanggal Check Out</span>
                                        <i class="fa-solid fa-calendar-xmark text-rose-400"></i>
                                    </div>
                                    <div class="space-y-2.5">
                                        <div>
                                            <label class="block text-[11px] font-medium text-slate-400 mb-1">Dari Check Out:</label>
                                            <input type="date" name="check_out_from" x-model="from" onclick="this.showPicker?.()" class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 font-mono cursor-pointer">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-medium text-slate-400 mb-1">Sampai Check Out:</label>
                                            <input type="date" name="check_out_to" x-model="to" onclick="this.showPicker?.()" class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 font-mono cursor-pointer">
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between pt-2 border-t border-slate-800/80 text-[11px]">
                                        <button type="button" @click="from = ''; to = ''; on = ''" class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs">Clear</button>
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 7. Jml Malam -->
                            <th class="py-1 px-2 text-center whitespace-nowrap relative border-r border-slate-800/60 transition-colors" :class="activeFilters.night_count ? 'bg-amber-950/80 border-b-2 border-b-amber-400 text-amber-200' : ''" @click.outside="if (openPop === 'night_count') openPop = null">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" @click="toggleSort('night_count')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('night_count') !== -1 ? 'text-amber-400 font-extrabold' : 'text-slate-300 hover:text-white'" title="Urutkan Jumlah Malam">
                                        <span>Malam</span>
                                        <template x-if="getSortIndex('night_count') === -1">
                                            <i class="fa-solid fa-sort text-slate-600 text-[10px] group-hover/sort:text-slate-400 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('night_count') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-amber-400 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('night_count') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-amber-500/20 px-1 py-0.2 rounded-full border border-amber-500/40 font-mono" x-text="getSortIndex('night_count') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'night_count' ? null : 'night_count')" class="p-1 rounded transition-colors" :class="activeFilters.night_count ? 'text-amber-300 bg-amber-500/30' : 'text-slate-500 hover:text-slate-300'" title="Filter Jumlah Malam">
                                        <i class="fa-solid" :class="activeFilters.night_count ? 'fa-filter text-amber-400 text-[11px]' : 'fa-caret-down text-xs'"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'night_count'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3.5 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-3 text-left font-normal normal-case min-w-[250px]"
                                     x-data="{
                                         min: '{{ $nightCountMin ?? '' }}',
                                         max: '{{ $nightCountMax ?? '' }}',
                                         eq: '{{ $nightCountEq ?? '' }}',
                                         onMinMaxChange() { if (this.min || this.max) { this.eq = ''; } },
                                         onEqChange() { if (this.eq) { this.min = ''; this.max = ''; } }
                                     }">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Filter Jumlah Malam</span>
                                        <i class="fa-solid fa-moon text-amber-400"></i>
                                    </div>
                                    <div class="space-y-2.5">
                                        <div>
                                            <label class="block text-[11px] font-medium text-slate-400 mb-1">&ge; Lebih Besar Sama Dengan:</label>
                                            <input type="number" name="night_count_min" x-model="min" @input="onMinMaxChange()" placeholder="Contoh: 1" step="1" min="1" class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 font-mono">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-medium text-slate-400 mb-1">&le; Lebih Kecil Sama Dengan:</label>
                                            <input type="number" name="night_count_max" x-model="max" @input="onMinMaxChange()" placeholder="Contoh: 5" step="1" min="1" class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 font-mono">
                                        </div>
                                        <div class="pt-1 border-t border-slate-800/60">
                                            <label class="block text-[11px] font-medium text-slate-400 mb-1">= Sama Dengan:</label>
                                            <input type="number" name="night_count_eq" x-model="eq" @input="onEqChange()" placeholder="Contoh: 2" step="1" min="1" class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 font-mono">
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between pt-2 border-t border-slate-800/80 text-[11px]">
                                        <button type="button" @click="min = ''; max = ''; eq = ''" class="px-2.5 py-1 rounded bg-slate-800 text-slate-300 text-xs">Clear</button>
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 7b. Jml Kamar -->
                            <th class="py-1 px-2 text-center whitespace-nowrap border-r border-slate-800/60 transition-colors">
                                <button type="button" @click="toggleSort('room_count')" class="flex items-center justify-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort w-full" :class="getSortIndex('room_count') !== -1 ? 'text-amber-400 font-extrabold' : 'text-slate-300 hover:text-white'" title="Urutkan Jumlah Kamar">
                                    <span>Kamar</span>
                                    <template x-if="getSortIndex('room_count') === -1">
                                        <i class="fa-solid fa-sort text-slate-600 text-[10px] group-hover/sort:text-slate-400 transition-colors"></i>
                                    </template>
                                    <template x-if="getSortIndex('room_count') !== -1">
                                        <span class="inline-flex items-center gap-0.5 text-amber-400 font-bold text-[10px]">
                                            <i class="fa-solid" :class="getSortDir('room_count') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                        </span>
                                    </template>
                                </button>
                            </th>

                            <!-- 8. Tamu -->
                            <th class="py-1 px-2 whitespace-nowrap relative border-r border-slate-800/60 transition-colors" :class="activeFilters.guest ? 'bg-amber-950/80 border-b-2 border-b-amber-400 text-amber-200' : ''" @click.outside="if (openPop === 'guest') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <button type="button" @click="toggleSort('guest_name')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('guest_name') !== -1 ? 'text-amber-400 font-extrabold' : 'text-slate-300 hover:text-white'" title="Urutkan Nama Tamu">
                                        <span>Nama Tamu</span>
                                        <template x-if="getSortIndex('guest_name') === -1">
                                            <i class="fa-solid fa-sort text-slate-600 text-[10px] group-hover/sort:text-slate-400 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('guest_name') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-amber-400 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('guest_name') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-amber-500/20 px-1 py-0.2 rounded-full border border-amber-500/40 font-mono" x-text="getSortIndex('guest_name') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'guest' ? null : 'guest')" class="p-1 rounded transition-colors" :class="activeFilters.guest ? 'text-amber-300 bg-amber-500/30' : 'text-slate-500 hover:text-slate-300'">
                                        <i class="fa-solid" :class="activeFilters.guest ? 'fa-filter text-amber-400 text-[11px]' : 'fa-caret-down text-xs'"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'guest'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-3 text-left font-normal normal-case min-w-[220px]">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Filter Nama Tamu</span>
                                        <i class="fa-solid fa-user text-amber-400"></i>
                                    </div>
                                    <input type="text" name="search_guest" value="{{ $searchGuest }}" placeholder="Cari nama tamu..." class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 placeholder-slate-500 focus:border-amber-400 focus:outline-none">
                                    <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-800/80">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow transition-colors">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 9. Jml Tamu -->
                            <th class="py-1 px-2 text-center whitespace-nowrap relative border-r border-slate-800/60 transition-colors" :class="activeFilters.guest_count ? 'bg-amber-950/80 border-b-2 border-b-amber-400 text-amber-200' : ''" @click.outside="if (openPop === 'guest_count') openPop = null">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" @click="toggleSort('guest_count')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('guest_count') !== -1 ? 'text-amber-400 font-extrabold' : 'text-slate-300 hover:text-white'" title="Urutkan Jumlah Tamu">
                                        <span>Jml</span>
                                        <template x-if="getSortIndex('guest_count') === -1">
                                            <i class="fa-solid fa-sort text-slate-600 text-[10px] group-hover/sort:text-slate-400 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('guest_count') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-amber-400 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('guest_count') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-amber-500/20 px-1 py-0.2 rounded-full border border-amber-500/40 font-mono" x-text="getSortIndex('guest_count') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'guest_count' ? null : 'guest_count')" class="p-1 rounded transition-colors" :class="activeFilters.guest_count ? 'text-amber-300 bg-amber-500/30' : 'text-slate-500 hover:text-slate-300'">
                                        <i class="fa-solid" :class="activeFilters.guest_count ? 'fa-filter text-amber-400 text-[11px]' : 'fa-caret-down text-xs'"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'guest_count'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3.5 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-3 text-left font-normal normal-case min-w-[250px]"
                                     x-data="{
                                         min: '{{ $guestCountMin ?? '' }}',
                                         max: '{{ $guestCountMax ?? '' }}',
                                         eq: '{{ $guestCountEq ?? '' }}',
                                         onMinMaxChange() { if (this.min || this.max) { this.eq = ''; } },
                                         onEqChange() { if (this.eq) { this.min = ''; this.max = ''; } }
                                     }">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Filter Jml Tamu</span>
                                        <i class="fa-solid fa-users text-amber-400"></i>
                                    </div>
                                    <div class="space-y-2.5">
                                        <div>
                                            <label class="block text-[11px] font-medium text-slate-400 mb-1">&ge; Lebih Besar Sama Dengan:</label>
                                            <input type="number" name="guest_count_min" x-model="min" @input="onMinMaxChange()" placeholder="Contoh: 2" step="any" class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 font-mono">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-medium text-slate-400 mb-1">&le; Lebih Kecil Sama Dengan:</label>
                                            <input type="number" name="guest_count_max" x-model="max" @input="onMinMaxChange()" placeholder="Contoh: 5" step="any" class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 font-mono">
                                        </div>
                                        <div class="pt-1 border-t border-slate-800/60">
                                            <label class="block text-[11px] font-medium text-slate-400 mb-1">= Sama Dengan:</label>
                                            <input type="number" name="guest_count_eq" x-model="eq" @input="onEqChange()" placeholder="Contoh: 3" step="any" class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 font-mono">
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between pt-2 border-t border-slate-800/80 text-[11px]">
                                        <button type="button" @click="min = ''; max = ''; eq = ''" class="px-2.5 py-1 rounded bg-slate-800 text-slate-300 text-xs">Clear</button>
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 10. Pemesan -->
                            <th class="py-1 px-2 whitespace-nowrap relative border-r border-slate-800/60 transition-colors" :class="activeFilters.booker ? 'bg-amber-950/80 border-b-2 border-b-amber-400 text-amber-200' : ''" @click.outside="if (openPop === 'booker') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <button type="button" @click="toggleSort('booked_by')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('booked_by') !== -1 ? 'text-amber-400 font-extrabold' : 'text-slate-300 hover:text-white'" title="Urutkan Pemesan">
                                        <span>Pemesan</span>
                                        <template x-if="getSortIndex('booked_by') === -1">
                                            <i class="fa-solid fa-sort text-slate-600 text-[10px] group-hover/sort:text-slate-400 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('booked_by') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-amber-400 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('booked_by') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-amber-500/20 px-1 py-0.2 rounded-full border border-amber-500/40 font-mono" x-text="getSortIndex('booked_by') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'booker' ? null : 'booker')" class="p-1 rounded transition-colors" :class="activeFilters.booker ? 'text-amber-300 bg-amber-500/30' : 'text-slate-500 hover:text-slate-300'">
                                        <i class="fa-solid" :class="activeFilters.booker ? 'fa-filter text-amber-400 text-[11px]' : 'fa-caret-down text-xs'"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'booker'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-3 text-left font-normal normal-case min-w-[220px]">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Filter Pemesan</span>
                                        <i class="fa-solid fa-user-pen text-amber-400"></i>
                                    </div>
                                    <input type="text" name="search_booker" value="{{ $searchBooker }}" placeholder="Nama pemesan..." class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 placeholder-slate-500 focus:border-amber-400 focus:outline-none">
                                    <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-800/80">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow transition-colors">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 11. Pembayar -->
                            <th class="py-1 px-2 whitespace-nowrap relative border-r border-slate-800/60 transition-colors" :class="activeFilters.payer ? 'bg-amber-950/80 border-b-2 border-b-amber-400 text-amber-200' : ''" @click.outside="if (openPop === 'payer') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <button type="button" @click="toggleSort('paid_by')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('paid_by') !== -1 ? 'text-amber-400 font-extrabold' : 'text-slate-300 hover:text-white'" title="Urutkan Pembayar">
                                        <span>Pembayar</span>
                                        <template x-if="getSortIndex('paid_by') === -1">
                                            <i class="fa-solid fa-sort text-slate-600 text-[10px] group-hover/sort:text-slate-400 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('paid_by') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-amber-400 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('paid_by') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-amber-500/20 px-1 py-0.2 rounded-full border border-amber-500/40 font-mono" x-text="getSortIndex('paid_by') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'payer' ? null : 'payer')" class="p-1 rounded transition-colors" :class="activeFilters.payer ? 'text-amber-300 bg-amber-500/30' : 'text-slate-500 hover:text-slate-300'">
                                        <i class="fa-solid" :class="activeFilters.payer ? 'fa-filter text-amber-400 text-[11px]' : 'fa-caret-down text-xs'"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'payer'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-3 text-left font-normal normal-case min-w-[220px]">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Filter Pembayar</span>
                                        <i class="fa-solid fa-credit-card text-amber-400"></i>
                                    </div>
                                    <input type="text" name="search_payer" value="{{ $searchPayer }}" placeholder="Nama pembayar..." class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 placeholder-slate-500 focus:border-amber-400 focus:outline-none">
                                    <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-800/80">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow transition-colors">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 12. Tgl Bayar -->
                            <th class="py-1 px-2 whitespace-nowrap relative border-r border-slate-800/60 transition-colors" :class="activeFilters.pay_date ? 'bg-amber-950/80 border-b-2 border-b-amber-400 text-amber-200' : ''" @click.outside="if (openPop === 'pay_date') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <button type="button" @click="toggleSort('payment_date')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('payment_date') !== -1 ? 'text-amber-400 font-extrabold' : 'text-slate-300 hover:text-white'" title="Urutkan Tanggal Bayar">
                                        <span>Tgl Bayar</span>
                                        <template x-if="getSortIndex('payment_date') === -1">
                                            <i class="fa-solid fa-sort text-slate-600 text-[10px] group-hover/sort:text-slate-400 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('payment_date') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-amber-400 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('payment_date') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-amber-500/20 px-1 py-0.2 rounded-full border border-amber-500/40 font-mono" x-text="getSortIndex('payment_date') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'pay_date' ? null : 'pay_date')" class="p-1 rounded transition-colors" :class="activeFilters.pay_date ? 'text-amber-300 bg-amber-500/30' : 'text-slate-500 hover:text-slate-300'">
                                        <i class="fa-solid" :class="activeFilters.pay_date ? 'fa-filter text-amber-400 text-[11px]' : 'fa-caret-down text-xs'"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'pay_date'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3.5 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-3 text-left font-normal normal-case min-w-[260px]"
                                     x-data="{
                                         payDateAfter: '{{ $payDateAfter ?? '' }}',
                                         payDateBefore: '{{ $payDateBefore ?? '' }}',
                                         payDateOn: '{{ $payDateOn ?? '' }}',
                                         onAfterBeforeChange() { if (this.payDateAfter || this.payDateBefore) { this.payDateOn = ''; } },
                                         onOnChange() { if (this.payDateOn) { this.payDateAfter = ''; this.payDateBefore = ''; } }
                                     }">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Filter Tanggal Bayar</span>
                                        <i class="fa-regular fa-calendar-check text-emerald-400"></i>
                                    </div>
                                    <div class="space-y-2.5">
                                        <div>
                                            <label class="block text-[11px] font-medium text-slate-400 mb-1">Dari (Setelah):</label>
                                            <input type="date" name="pay_date_after" x-model="payDateAfter" @change="onAfterBeforeChange()" onclick="this.showPicker?.()" class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 font-mono cursor-pointer">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-medium text-slate-400 mb-1">Sampai (Sebelum):</label>
                                            <input type="date" name="pay_date_before" x-model="payDateBefore" @change="onAfterBeforeChange()" onclick="this.showPicker?.()" class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 font-mono cursor-pointer">
                                        </div>
                                        <div class="pt-1 border-t border-slate-800/60">
                                            <label class="block text-[11px] font-medium text-slate-400 mb-1">Tepat Pada Tanggal:</label>
                                            <input type="date" name="pay_date_on" x-model="payDateOn" @change="onOnChange()" onclick="this.showPicker?.()" class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 font-mono cursor-pointer">
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between pt-2 border-t border-slate-800/80 text-[11px]">
                                        <button type="button" @click="payDateAfter = ''; payDateBefore = ''; payDateOn = ''" class="px-2.5 py-1 rounded bg-slate-800 text-slate-300 text-xs">Clear</button>
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 13. Biaya (IDR) -->
                            <th class="py-1 px-2 text-right whitespace-nowrap relative border-r border-slate-800/60 transition-colors" :class="activeFilters.amount ? 'bg-amber-950/80 border-b-2 border-b-amber-400 text-amber-200' : ''" @click.outside="if (openPop === 'amount') openPop = null">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" @click="toggleSort('amount')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('amount') !== -1 ? 'text-amber-400 font-extrabold' : 'text-slate-300 hover:text-white'" title="Urutkan Biaya">
                                        <span>Biaya (IDR)</span>
                                        <template x-if="getSortIndex('amount') === -1">
                                            <i class="fa-solid fa-sort text-slate-600 text-[10px] group-hover/sort:text-slate-400 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('amount') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-amber-400 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('amount') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-amber-500/20 px-1 py-0.2 rounded-full border border-amber-500/40 font-mono" x-text="getSortIndex('amount') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'amount' ? null : 'amount')" class="p-1 rounded transition-colors" :class="activeFilters.amount ? 'text-amber-300 bg-amber-500/30' : 'text-slate-500 hover:text-slate-300'" title="Filter Biaya">
                                        <i class="fa-solid" :class="activeFilters.amount ? 'fa-filter text-amber-400 text-[11px]' : 'fa-caret-down text-xs'"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'amount'" x-cloak x-transition class="absolute z-50 right-0 mt-2 p-3.5 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-3 text-left font-normal normal-case min-w-[250px]"
                                     x-data="{
                                         min: '{{ $amountMin ?? '' }}',
                                         max: '{{ $amountMax ?? '' }}',
                                         eq: '{{ $amountEq ?? '' }}',
                                         onMinMaxChange() { if (this.min || this.max) { this.eq = ''; } },
                                         onEqChange() { if (this.eq) { this.min = ''; this.max = ''; } }
                                     }">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Filter Biaya (IDR)</span>
                                        <i class="fa-solid fa-money-bill-wave text-emerald-400"></i>
                                    </div>
                                    <div class="space-y-2.5">
                                        <div>
                                            <label class="block text-[11px] font-medium text-slate-400 mb-1">&ge; Minimum (Rp):</label>
                                            <input type="number" name="amount_min" x-model="min" @input="onMinMaxChange()" placeholder="Ex: 500000" step="any" class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 font-mono">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-medium text-slate-400 mb-1">&le; Maksimum (Rp):</label>
                                            <input type="number" name="amount_max" x-model="max" @input="onMinMaxChange()" placeholder="Ex: 5000000" step="any" class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 font-mono">
                                        </div>
                                        <div class="pt-1 border-t border-slate-800/60">
                                            <label class="block text-[11px] font-medium text-slate-400 mb-1">= Tepat Nominal (Rp):</label>
                                            <input type="number" name="amount_eq" x-model="eq" @input="onEqChange()" placeholder="Ex: 1500000" step="any" class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 font-mono">
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between pt-2 border-t border-slate-800/80 text-[11px]">
                                        <button type="button" @click="min = ''; max = ''; eq = ''" class="px-2.5 py-1 rounded bg-slate-800 text-slate-300 text-xs">Clear</button>
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 14. Status -->
                            <th class="py-1 px-2 text-center whitespace-nowrap relative transition-colors" :class="activeFilters.status ? 'bg-amber-950/80 border-b-2 border-b-amber-400 text-amber-200' : ''" @click.outside="if (openPop === 'status') openPop = null">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" @click="toggleSort('status')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('status') !== -1 ? 'text-amber-400 font-extrabold' : 'text-slate-300 hover:text-white'" title="Urutkan Status">
                                        <span>Status</span>
                                        <template x-if="getSortIndex('status') === -1">
                                            <i class="fa-solid fa-sort text-slate-600 text-[10px] group-hover/sort:text-slate-400 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('status') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-amber-400 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('status') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-amber-500/20 px-1 py-0.2 rounded-full border border-amber-500/40 font-mono" x-text="getSortIndex('status') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'status' ? null : 'status')" class="p-1 rounded transition-colors" :class="activeFilters.status ? 'text-amber-300 bg-amber-500/30' : 'text-slate-500 hover:text-slate-300'" title="Filter Status">
                                        <i class="fa-solid" :class="activeFilters.status ? 'fa-filter text-amber-400 text-[11px]' : 'fa-caret-down text-xs'"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'status'" x-cloak x-transition class="absolute z-50 right-0 mt-2 p-3 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-2 text-left font-normal normal-case min-w-[180px]" x-data="{ selected: {{ json_encode($status) }} }">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Pilih Status</span>
                                        <i class="fa-solid fa-tag text-emerald-400"></i>
                                    </div>
                                    <div class="space-y-1 max-h-48 overflow-y-auto pr-1">
                                        <label class="flex items-center gap-2 px-2 py-1 rounded hover:bg-slate-800 cursor-pointer text-xs font-semibold text-amber-400 border-b border-slate-800 mb-1">
                                            <input type="checkbox" @change="selected = $event.target.checked ? {{ json_encode($statusOptions) }} : []" :checked="selected.length === {{ count($statusOptions) }}" class="rounded bg-slate-800 border-slate-700 text-amber-500">
                                            <span>Pilih Semua</span>
                                        </label>
                                        @foreach($statusOptions as $optStatus)
                                            <label class="flex items-center gap-2 px-2 py-1 rounded hover:bg-slate-800 cursor-pointer text-xs text-slate-200">
                                                <input type="checkbox" name="status[]" value="{{ $optStatus }}" x-model="selected" class="rounded bg-slate-800 border-slate-700 text-amber-500">
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
                    <tbody id="hotels-tbody" class="divide-y divide-slate-800/60 whitespace-nowrap font-sans">
                        @include('hotels._rows', ['hotels' => $hotels])
                    </tbody>
                </table>
            </div>
        </form>
    </div>

    <!-- HTML Preview Modal for Voucher Hotel (Tampilkan dalam HTML dulu sebelum distream sebagai PDF) -->
    <div x-cloak x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="showModal = false" class="fixed inset-0 bg-slate-950/85 backdrop-blur-md transition-opacity no-print"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div id="modal-hotel-voucher-card" x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-slate-900 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-800 relative printable-card">
                <template x-if="selectedHotel">
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
                                        <h3 class="font-mono font-bold text-lg" x-text="selectedHotel.hotel_name"></h3>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 no-print">
                                    <template x-if="selectedHotel.can_delete">
                                        <form :action="selectedHotel.delete_url" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data hotel ini? Data yang dihapus tidak dapat dikembalikan.');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-rose-600/80 hover:bg-rose-500 text-white text-xs font-semibold flex items-center gap-1.5 transition-colors shadow-sm" title="Hapus Histori Hotel Ini">
                                                <i class="fa-solid fa-trash-can"></i> Hapus
                                            </button>
                                        </form>
                                    </template>

                                    <!-- Stream / Download PDF Button -->
                                    <a :href="selectedHotel.pdf_url" target="_blank" class="px-3.5 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs flex items-center gap-1.5 transition-colors shadow-sm" title="Download PDF Voucher Hotel">
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
                                    <span class="font-display text-lg font-bold text-white block mt-0.5" x-text="selectedHotel.check_in_date"></span>
                                </div>
                                <div class="text-center">
                                    <span class="text-xs text-amber-200 block uppercase">Durasi & Kamar</span>
                                    <span class="font-display text-lg font-bold text-amber-300 block mt-0.5" x-text="selectedHotel.night_count + ' Malam • ' + selectedHotel.room_count + ' Kamar'"></span>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs text-amber-200 block uppercase">Check Out</span>
                                    <span class="font-display text-lg font-bold text-white block mt-0.5" x-text="selectedHotel.check_out_date"></span>
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
                                    <span class="text-xs font-mono font-semibold text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded-full border border-amber-500/30" x-text="selectedHotel.guest_count + ' Tamu (' + selectedHotel.room_count + ' Kamar)'"></span>
                                </div>
                                <div class="space-y-1">
                                    <template x-for="(name, idx) in selectedHotel.guests_list" :key="idx">
                                        <div class="flex items-center gap-2 text-sm text-slate-100 font-medium py-1 border-b border-slate-800/40 last:border-0">
                                            <span class="text-xs font-mono text-slate-500" x-text="(idx + 1) + '.'"></span>
                                            <span x-text="name"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Core Details Grid -->
                            <div class="grid grid-cols-2 gap-4 bg-slate-950/60 p-4 rounded-2xl border border-slate-800">
                                <div>
                                    <span class="text-xs text-slate-400 block">Kode Booking</span>
                                    <span class="text-sm font-semibold font-mono text-amber-400 mt-0.5 block" x-text="selectedHotel.booking_code"></span>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-400 block">Kode Invoice</span>
                                    <span class="text-sm font-semibold font-mono text-indigo-300 mt-0.5 block" x-text="selectedHotel.invoice_code"></span>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-400 block">Status Pembayaran</span>
                                    <span class="inline-block mt-1 px-2.5 py-0.5 text-xs font-semibold rounded-full border" :class="selectedHotel.status_badge" x-text="selectedHotel.status"></span>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-400 block">Biaya Reservasi</span>
                                    <span class="text-base font-bold text-emerald-400 font-mono mt-0.5 block" x-text="selectedHotel.amount"></span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 bg-slate-950/60 p-4 rounded-2xl border border-slate-800">
                                <div>
                                    <span class="text-xs text-slate-400 block">Pemesan Hotel</span>
                                    <span class="text-xs font-semibold text-indigo-300 mt-0.5 block" x-text="selectedHotel.booked_by"></span>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-400 block">Pembayaran Oleh</span>
                                    <span class="text-xs font-semibold text-emerald-300 mt-0.5 block" x-text="selectedHotel.paid_by"></span>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-400 block">Tanggal Booking</span>
                                    <span class="text-xs font-semibold font-mono text-slate-200 mt-0.5 block" x-text="selectedHotel.booking_date"></span>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-400 block">Tanggal Bayar</span>
                                    <span class="text-xs font-semibold font-mono text-slate-200 mt-0.5 block" x-text="selectedHotel.payment_date"></span>
                                </div>
                            </div>

                            <template x-if="selectedHotel.notes">
                                <div class="bg-slate-950/60 p-4 rounded-2xl border border-slate-800">
                                    <span class="text-xs text-slate-400 block mb-1">Catatan</span>
                                    <p class="text-slate-300 italic" x-text="selectedHotel.notes"></p>
                                </div>
                            </template>

                            <!-- Activity Timeline Logs in HTML Preview -->
                            <template x-if="selectedHotel.status_logs && selectedHotel.status_logs.length > 0">
                                <div class="bg-slate-950/60 p-4 rounded-2xl border border-slate-800 space-y-2">
                                    <span class="text-xs font-bold text-slate-400 block uppercase tracking-wider mb-2">Riwayat Log Aktivitas Status</span>
                                    <template x-for="(log, lIdx) in selectedHotel.status_logs" :key="lIdx">
                                        <div class="p-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs">
                                            <div class="flex items-center justify-between">
                                                <span class="font-semibold text-amber-400" x-text="log.to_status"></span>
                                                <span class="text-[10px] font-mono text-slate-500" x-text="log.date"></span>
                                            </div>
                                            <p class="text-slate-400 text-[11px] mt-0.5" x-text="log.notes"></p>
                                            <div class="text-[10px] text-slate-500 font-mono mt-1" x-text="log.user_name + ' (' + log.user_role + ')'"></div>
                                        </div>
                                    </template>
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
