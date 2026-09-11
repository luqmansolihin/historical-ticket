@extends('layouts.app')

@section('title', 'Daftar Histori Biaya Lain-lain')

@section('content')
<div x-data="{
        selectedExpense: null,
        showModal: false,
        openPop: null,
        nextPageUrl: '{{ $expenses->nextPageUrl() }}',
        loading: false,
        hasMore: {{ $expenses->hasMorePages() ? 'true' : 'false' }},
        sorts: {{ json_encode($sorts ?? []) }},
        hasFilters: {{ ($search || $searchCode || $searchInvoice || $searchExpense || $searchBooker || $searchPayer || !empty($status) || $dateAfter || $dateBefore || $dateOn || $payDateAfter || $payDateBefore || $payDateOn || $amountMin || $amountMax || $amountEq) ? 'true' : 'false' }},
        activeFilters: {
            code: {{ !empty($searchCode) ? 'true' : 'false' }},
            invoice: {{ !empty($searchInvoice) ? 'true' : 'false' }},
            date: {{ ($dateAfter || $dateBefore || $dateOn) ? 'true' : 'false' }},
            expense: {{ !empty($searchExpense) ? 'true' : 'false' }},
            booker: {{ !empty($searchBooker) ? 'true' : 'false' }},
            payer: {{ !empty($searchPayer) ? 'true' : 'false' }},
            pay_date: {{ ($payDateAfter || $payDateBefore || $payDateOn) ? 'true' : 'false' }},
            amount: {{ ($amountMin || $amountMax || $amountEq) ? 'true' : 'false' }},
            status: {{ !empty($status) ? 'true' : 'false' }},
        },
        init() {
            this.checkAutoFill();
        },
        checkHasFilters() {
            const form = document.getElementById('filter-form');
            if (!form) { this.hasFilters = false; return; }
            const formData = new FormData(form);
            
            this.activeFilters.code = !!(formData.get('search_code') && formData.get('search_code').trim());
            this.activeFilters.invoice = !!(formData.get('search_invoice') && formData.get('search_invoice').trim());
            this.activeFilters.date = !!((formData.get('date_after') && formData.get('date_after').trim()) || (formData.get('date_before') && formData.get('date_before').trim()) || (formData.get('date_on') && formData.get('date_on').trim()));
            this.activeFilters.expense = !!(formData.get('search_expense') && formData.get('search_expense').trim());
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
                const tbody = document.getElementById('expenses-tbody');
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
            const defaultDir = (col === 'booking_date' || col === 'payment_date' || col === 'amount' || col === 'id') ? 'desc' : 'asc';
            
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
                const tbody = document.getElementById('expenses-tbody');
                if (tbody && (data.html || data.table_html)) {
                    tbody.innerHTML = data.html || data.table_html;
                }
                this.nextPageUrl = data.next_page_url;
                this.hasMore = data.has_more;
                this.loading = false;
                
                const exportBtn = document.getElementById('export-csv-btn');
                if (exportBtn) {
                    const currentUrl = new URL(fetchUrl, window.location.origin);
                    exportBtn.href = '{{ route("expenses.export") }}' + currentUrl.search;
                }
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

    <!-- Flash Message Notification -->
    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" class="mb-3 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between shadow-sm shrink-0">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button @click="show = false" class="text-emerald-700 hover:text-emerald-900 p-1">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    <!-- Data Table Container & Column Header Filters -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 relative z-10 no-print flex-1 flex flex-col min-h-0 h-full overflow-hidden">
        <!-- Data Grid Action Toolbar -->
        <div class="px-3 py-1.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between gap-2 shrink-0">
            <div class="text-xs text-slate-600 font-mono hidden sm:block">
                <i class="fa-solid fa-mouse-pointer text-emerald-600 mr-1"></i> Double klik baris tabel untuk edit data histori biaya
            </div>

            <div class="flex items-center gap-2 ml-auto">
                <a href="{{ route('expenses.index') }}" x-show="hasFilters || sorts.length > 0" x-cloak @click.prevent="resetFilters()" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-all shadow-sm" title="Reset Filter & Urutan Tabel">
                    <i class="fa-solid fa-rotate-left mr-1.5 text-xs"></i> Reset Filter & Urutan
                </a>

                <a id="export-csv-btn" href="{{ route('expenses.export', request()->query()) }}" class="inline-flex items-center px-3.5 py-1.5 rounded-lg text-xs font-medium text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 transition-all shadow-sm">
                    <i class="fa-solid fa-file-csv text-emerald-600 mr-1.5 text-xs"></i> Ekspor CSV
                </a>

                @can('create', App\Models\ExpenseHistory::class)
                    <a href="{{ route('expenses.create') }}" class="inline-flex items-center px-4 py-1.5 rounded-lg text-xs font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 shadow-md shadow-emerald-500/20 transition-all active:scale-95">
                        <i class="fa-solid fa-plus mr-1.5"></i> Tambah Biaya Baru
                    </a>
                @endcan
            </div>
        </div>

        <form id="filter-form" action="{{ route('expenses.index') }}" method="GET" @submit.prevent="applyFilters()" class="flex-1 flex flex-col min-h-0 h-full overflow-hidden justify-between">
            <input type="hidden" id="sort_input" name="sort" :value="serializeSorts()">

            <div x-ref="scrollContainer" class="overflow-auto flex-1 min-h-0" @scroll.passive="onScroll($event)">
                <table class="w-full text-left text-[9.5px] leading-tight text-slate-800 whitespace-nowrap border-collapse">
                    <thead class="bg-slate-100 text-[9px] uppercase font-bold text-slate-700 tracking-tight border-b border-slate-200 whitespace-nowrap sticky top-0 z-20 backdrop-blur-md">
                        <tr>
                            <!-- 1. Kode Booking / Ref -->
                            <th class="py-1 px-2 whitespace-nowrap relative border-r border-slate-200 transition-colors" :class="activeFilters.code ? 'bg-emerald-50 border-b-2 border-b-emerald-600 text-emerald-900' : ''" @click.outside="if (openPop === 'code') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <button type="button" @click="toggleSort('booking_code')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('booking_code') !== -1 ? 'text-emerald-700 font-extrabold' : 'text-slate-700 hover:text-slate-900'" title="Urutkan Kode Ref">
                                        <span>Kode Ref</span>
                                        <template x-if="getSortIndex('booking_code') === -1">
                                            <i class="fa-solid fa-sort text-slate-400 text-[10px] group-hover/sort:text-slate-600 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('booking_code') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-emerald-700 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('booking_code') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-emerald-100 px-1 py-0.2 rounded-full border border-emerald-300 font-mono text-emerald-800" x-text="getSortIndex('booking_code') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'code' ? null : 'code')" class="p-1 rounded transition-colors" :class="activeFilters.code ? 'text-emerald-700 bg-emerald-100 ring-1 ring-emerald-400 font-bold shadow-sm' : 'text-slate-400 hover:text-slate-700 hover:bg-slate-200'" title="Filter Kode Ref">
                                        <i class="fa-solid" :class="activeFilters.code ? 'fa-filter text-emerald-600 text-[11px]' : 'fa-caret-down text-xs'"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'code'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3 bg-white border border-slate-200 rounded-xl shadow-xl space-y-3 text-left font-normal normal-case min-w-[220px]">
                                    <div class="text-xs font-semibold text-slate-800 border-b border-slate-200 pb-1.5 flex items-center justify-between">
                                        <span>Filter Kode Ref</span>
                                        <i class="fa-solid fa-hashtag text-emerald-600"></i>
                                    </div>
                                    <input type="text" name="search_code" value="{{ $searchCode }}" placeholder="Cari kode referensi..." class="w-full h-8 rounded-lg px-2.5 text-xs bg-white border border-slate-300 text-slate-900 placeholder-slate-400 focus:border-emerald-600 focus:outline-none">
                                    <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-200">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow transition-colors">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 2. Kode Invoice -->
                            <th class="py-1 px-2 whitespace-nowrap relative border-r border-slate-200 transition-colors" :class="activeFilters.invoice ? 'bg-emerald-50 border-b-2 border-b-emerald-600 text-emerald-900' : ''" @click.outside="if (openPop === 'invoice') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <button type="button" @click="toggleSort('invoice_code')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('invoice_code') !== -1 ? 'text-emerald-700 font-extrabold' : 'text-slate-700 hover:text-slate-900'" title="Urutkan Kode Invoice">
                                        <span>Kode Invoice</span>
                                        <template x-if="getSortIndex('invoice_code') === -1">
                                            <i class="fa-solid fa-sort text-slate-400 text-[10px] group-hover/sort:text-slate-600 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('invoice_code') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-emerald-700 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('invoice_code') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-emerald-100 px-1 py-0.2 rounded-full border border-emerald-300 font-mono text-emerald-800" x-text="getSortIndex('invoice_code') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'invoice' ? null : 'invoice')" class="p-1 rounded transition-colors" :class="activeFilters.invoice ? 'text-emerald-700 bg-emerald-100 ring-1 ring-emerald-400 font-bold shadow-sm' : 'text-slate-400 hover:text-slate-700 hover:bg-slate-200'" title="Filter Kode Invoice">
                                        <i class="fa-solid" :class="activeFilters.invoice ? 'fa-filter text-emerald-600 text-[11px]' : 'fa-caret-down text-xs'"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'invoice'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3 bg-white border border-slate-200 rounded-xl shadow-xl space-y-3 text-left font-normal normal-case min-w-[220px]">
                                    <div class="text-xs font-semibold text-slate-800 border-b border-slate-200 pb-1.5 flex items-center justify-between">
                                        <span>Filter Kode Invoice</span>
                                        <i class="fa-solid fa-file-invoice text-emerald-600"></i>
                                    </div>
                                    <input type="text" name="search_invoice" value="{{ $searchInvoice }}" placeholder="Cari kode invoice..." class="w-full h-8 rounded-lg px-2.5 text-xs bg-white border border-slate-300 text-slate-900 placeholder-slate-400 focus:border-emerald-600 focus:outline-none">
                                    <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-200">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow transition-colors">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 3. Tgl Biaya -->
                            <th class="py-1 px-2 whitespace-nowrap relative border-r border-slate-200 transition-colors" :class="activeFilters.date ? 'bg-emerald-50 border-b-2 border-b-emerald-600 text-emerald-900' : ''" @click.outside="if (openPop === 'date') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <button type="button" @click="toggleSort('booking_date')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('booking_date') !== -1 ? 'text-emerald-700 font-extrabold' : 'text-slate-700 hover:text-slate-900'" title="Urutkan Tanggal Biaya">
                                        <span>Tgl Biaya</span>
                                        <template x-if="getSortIndex('booking_date') === -1">
                                            <i class="fa-solid fa-sort text-slate-400 text-[10px] group-hover/sort:text-slate-600 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('booking_date') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-emerald-700 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('booking_date') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-emerald-100 px-1 py-0.2 rounded-full border border-emerald-300 font-mono text-emerald-800" x-text="getSortIndex('booking_date') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'date' ? null : 'date')" class="p-1 rounded transition-colors" :class="activeFilters.date ? 'text-emerald-700 bg-emerald-100 ring-1 ring-emerald-400 font-bold shadow-sm' : 'text-slate-400 hover:text-slate-700 hover:bg-slate-200'" title="Filter Tanggal Biaya">
                                        <i class="fa-solid" :class="activeFilters.date ? 'fa-filter text-emerald-600 text-[11px]' : 'fa-caret-down text-xs'"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'date'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3.5 bg-white border border-slate-200 rounded-xl shadow-xl space-y-3 text-left font-normal normal-case min-w-[260px]"
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
                                    <div class="text-xs font-semibold text-slate-800 border-b border-slate-200 pb-1.5 flex items-center justify-between">
                                        <span>Filter Tanggal Biaya</span>
                                        <i class="fa-regular fa-calendar-days text-emerald-600"></i>
                                    </div>

                                    <div class="space-y-2.5 text-xs">
                                        <div>
                                            <label class="block text-[11px] font-medium text-slate-600 mb-1">
                                                <span class="text-emerald-700 font-semibold">After</span> (Dari / Setelah):
                                            </label>
                                             <input type="date" name="date_after" x-model="dateAfter" @change="onAfterBeforeChange()" onclick="this.showPicker?.()" class="w-full h-8 rounded-lg px-2.5 text-xs bg-white border border-slate-300 text-slate-900 focus:border-emerald-600 focus:outline-none font-mono cursor-pointer">
                                        </div>

                                        <div>
                                            <label class="block text-[11px] font-medium text-slate-600 mb-1">
                                                <span class="text-emerald-700 font-semibold">Before</span> (Sampai / Sebelum):
                                            </label>
                                             <input type="date" name="date_before" x-model="dateBefore" @change="onAfterBeforeChange()" onclick="this.showPicker?.()" class="w-full h-8 rounded-lg px-2.5 text-xs bg-white border border-slate-300 text-slate-900 focus:border-emerald-600 focus:outline-none font-mono cursor-pointer">
                                        </div>

                                        <div class="pt-1 border-t border-slate-200">
                                            <label class="block text-[11px] font-medium text-slate-600 mb-1">
                                                <span class="text-emerald-700 font-semibold">On</span> (Tepat Pada Tanggal):
                                            </label>
                                             <input type="date" name="date_on" x-model="dateOn" @change="onOnChange()" onclick="this.showPicker?.()" class="w-full h-8 rounded-lg px-2.5 text-xs bg-white border border-slate-300 text-slate-900 focus:border-emerald-600 focus:outline-none font-mono cursor-pointer">
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between pt-2 border-t border-slate-200 text-[11px]">
                                        <button type="button" @click="dateAfter = ''; dateBefore = ''; dateOn = ''" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 text-xs">Clear</button>
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow transition-colors">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 4. Nama / Rincian Biaya -->
                            <th class="py-1 px-2 whitespace-nowrap relative border-r border-slate-200 transition-colors" :class="activeFilters.expense ? 'bg-emerald-50 border-b-2 border-b-emerald-600 text-emerald-900' : ''" @click.outside="if (openPop === 'expense') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <button type="button" @click="toggleSort('expense_name')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('expense_name') !== -1 ? 'text-emerald-700 font-extrabold' : 'text-slate-700 hover:text-slate-900'" title="Urutkan Nama Biaya">
                                        <span>Nama / Rincian Biaya</span>
                                        <template x-if="getSortIndex('expense_name') === -1">
                                            <i class="fa-solid fa-sort text-slate-400 text-[10px] group-hover/sort:text-slate-600 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('expense_name') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-emerald-700 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('expense_name') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-emerald-100 px-1 py-0.2 rounded-full border border-emerald-300 font-mono text-emerald-800" x-text="getSortIndex('expense_name') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'expense' ? null : 'expense')" class="p-1 rounded transition-colors" :class="activeFilters.expense ? 'text-emerald-700 bg-emerald-100 ring-1 ring-emerald-400 font-bold shadow-sm' : 'text-slate-400 hover:text-slate-700 hover:bg-slate-200'" title="Filter Nama Biaya">
                                        <i class="fa-solid" :class="activeFilters.expense ? 'fa-filter text-emerald-600 text-[11px]' : 'fa-caret-down text-xs'"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'expense'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3 bg-white border border-slate-200 rounded-xl shadow-xl space-y-3 text-left font-normal normal-case min-w-[220px]">
                                    <div class="text-xs font-semibold text-slate-800 border-b border-slate-200 pb-1.5 flex items-center justify-between">
                                        <span>Filter Nama Biaya</span>
                                        <i class="fa-solid fa-receipt text-emerald-600"></i>
                                    </div>
                                    <input type="text" name="search_expense" value="{{ $searchExpense }}" placeholder="Cari nama biaya..." class="w-full h-8 rounded-lg px-2.5 text-xs bg-white border border-slate-300 text-slate-900 placeholder-slate-400 focus:border-emerald-600 focus:outline-none">
                                    <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-200">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow transition-colors">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 5. Pengaju (Booked By) -->
                            <th class="py-1 px-2 whitespace-nowrap relative border-r border-slate-200 transition-colors" :class="activeFilters.booker ? 'bg-emerald-50 border-b-2 border-b-emerald-600 text-emerald-900' : ''" @click.outside="if (openPop === 'booker') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <button type="button" @click="toggleSort('booked_by')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('booked_by') !== -1 ? 'text-emerald-700 font-extrabold' : 'text-slate-700 hover:text-slate-900'" title="Urutkan Pengaju">
                                        <span>Pengaju</span>
                                        <template x-if="getSortIndex('booked_by') === -1">
                                            <i class="fa-solid fa-sort text-slate-400 text-[10px] group-hover/sort:text-slate-600 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('booked_by') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-emerald-700 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('booked_by') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-emerald-100 px-1 py-0.2 rounded-full border border-emerald-300 font-mono text-emerald-800" x-text="getSortIndex('booked_by') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'booker' ? null : 'booker')" class="p-1 rounded transition-colors" :class="activeFilters.booker ? 'text-emerald-700 bg-emerald-100 ring-1 ring-emerald-400 font-bold shadow-sm' : 'text-slate-400 hover:text-slate-700 hover:bg-slate-200'" title="Filter Pengaju">
                                        <i class="fa-solid" :class="activeFilters.booker ? 'fa-filter text-emerald-600 text-[11px]' : 'fa-caret-down text-xs'"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'booker'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3 bg-white border border-slate-200 rounded-xl shadow-xl space-y-3 text-left font-normal normal-case min-w-[220px]">
                                    <div class="text-xs font-semibold text-slate-800 border-b border-slate-200 pb-1.5 flex items-center justify-between">
                                        <span>Filter Pengaju</span>
                                        <i class="fa-solid fa-user-pen text-emerald-600"></i>
                                    </div>
                                    <input type="text" name="search_booker" value="{{ $searchBooker }}" placeholder="Cari nama pengaju..." class="w-full h-8 rounded-lg px-2.5 text-xs bg-white border border-slate-300 text-slate-900 placeholder-slate-400 focus:border-emerald-600 focus:outline-none">
                                    <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-200">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow transition-colors">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 6. Pembayar (Paid By) -->
                            <th class="py-1 px-2 whitespace-nowrap relative border-r border-slate-200 transition-colors" :class="activeFilters.payer ? 'bg-emerald-50 border-b-2 border-b-emerald-600 text-emerald-900' : ''" @click.outside="if (openPop === 'payer') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <button type="button" @click="toggleSort('paid_by')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('paid_by') !== -1 ? 'text-emerald-700 font-extrabold' : 'text-slate-700 hover:text-slate-900'" title="Urutkan Pembayar">
                                        <span>Pembayar</span>
                                        <template x-if="getSortIndex('paid_by') === -1">
                                            <i class="fa-solid fa-sort text-slate-400 text-[10px] group-hover/sort:text-slate-600 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('paid_by') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-emerald-700 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('paid_by') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-emerald-100 px-1 py-0.2 rounded-full border border-emerald-300 font-mono text-emerald-800" x-text="getSortIndex('paid_by') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'payer' ? null : 'payer')" class="p-1 rounded transition-colors" :class="activeFilters.payer ? 'text-emerald-700 bg-emerald-100 ring-1 ring-emerald-400 font-bold shadow-sm' : 'text-slate-400 hover:text-slate-700 hover:bg-slate-200'" title="Filter Pembayar">
                                        <i class="fa-solid" :class="activeFilters.payer ? 'fa-filter text-emerald-600 text-[11px]' : 'fa-caret-down text-xs'"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'payer'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3 bg-white border border-slate-200 rounded-xl shadow-xl space-y-3 text-left font-normal normal-case min-w-[220px]">
                                    <div class="text-xs font-semibold text-slate-800 border-b border-slate-200 pb-1.5 flex items-center justify-between">
                                        <span>Filter Pembayar</span>
                                        <i class="fa-solid fa-credit-card text-emerald-600"></i>
                                    </div>
                                    <input type="text" name="search_payer" value="{{ $searchPayer }}" placeholder="Cari nama pembayar..." class="w-full h-8 rounded-lg px-2.5 text-xs bg-white border border-slate-300 text-slate-900 placeholder-slate-400 focus:border-emerald-600 focus:outline-none">
                                    <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-200">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow transition-colors">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 7. Tgl Bayar -->
                            <th class="py-1 px-2 whitespace-nowrap relative border-r border-slate-200 transition-colors" :class="activeFilters.pay_date ? 'bg-emerald-50 border-b-2 border-b-emerald-600 text-emerald-900' : ''" @click.outside="if (openPop === 'pay_date') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <button type="button" @click="toggleSort('payment_date')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('payment_date') !== -1 ? 'text-emerald-700 font-extrabold' : 'text-slate-700 hover:text-slate-900'" title="Urutkan Tanggal Bayar">
                                        <span>Tgl Bayar</span>
                                        <template x-if="getSortIndex('payment_date') === -1">
                                            <i class="fa-solid fa-sort text-slate-400 text-[10px] group-hover/sort:text-slate-600 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('payment_date') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-emerald-700 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('payment_date') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-emerald-100 px-1 py-0.2 rounded-full border border-emerald-300 font-mono text-emerald-800" x-text="getSortIndex('payment_date') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'pay_date' ? null : 'pay_date')" class="p-1 rounded transition-colors" :class="activeFilters.pay_date ? 'text-emerald-700 bg-emerald-100 ring-1 ring-emerald-400 font-bold shadow-sm' : 'text-slate-400 hover:text-slate-700 hover:bg-slate-200'" title="Filter Tanggal Bayar">
                                        <i class="fa-solid" :class="activeFilters.pay_date ? 'fa-filter text-emerald-600 text-[11px]' : 'fa-caret-down text-xs'"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'pay_date'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3.5 bg-white border border-slate-200 rounded-xl shadow-xl space-y-3 text-left font-normal normal-case min-w-[260px]"
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
                                    <div class="text-xs font-semibold text-slate-800 border-b border-slate-200 pb-1.5 flex items-center justify-between">
                                        <span>Filter Tanggal Bayar</span>
                                        <i class="fa-regular fa-calendar-check text-emerald-600"></i>
                                    </div>

                                    <div class="space-y-2.5 text-xs">
                                        <div>
                                            <label class="block text-[11px] font-medium text-slate-600 mb-1">
                                                <span class="text-emerald-700 font-semibold">After</span> (Dari / Setelah):
                                            </label>
                                             <input type="date" name="pay_date_after" x-model="payDateAfter" @change="onAfterBeforeChange()" onclick="this.showPicker?.()" class="w-full h-8 rounded-lg px-2.5 text-xs bg-white border border-slate-300 text-slate-900 focus:border-emerald-600 focus:outline-none font-mono cursor-pointer">
                                        </div>

                                        <div>
                                            <label class="block text-[11px] font-medium text-slate-600 mb-1">
                                                <span class="text-emerald-700 font-semibold">Before</span> (Sampai / Sebelum):
                                            </label>
                                             <input type="date" name="pay_date_before" x-model="payDateBefore" @change="onAfterBeforeChange()" onclick="this.showPicker?.()" class="w-full h-8 rounded-lg px-2.5 text-xs bg-white border border-slate-300 text-slate-900 focus:border-emerald-600 focus:outline-none font-mono cursor-pointer">
                                        </div>

                                        <div class="pt-1 border-t border-slate-200">
                                            <label class="block text-[11px] font-medium text-slate-600 mb-1">
                                                <span class="text-emerald-700 font-semibold">On</span> (Tepat Pada Tanggal):
                                            </label>
                                             <input type="date" name="pay_date_on" x-model="payDateOn" @change="onOnChange()" onclick="this.showPicker?.()" class="w-full h-8 rounded-lg px-2.5 text-xs bg-white border border-slate-300 text-slate-900 focus:border-emerald-600 focus:outline-none font-mono cursor-pointer">
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between pt-2 border-t border-slate-200 text-[11px]">
                                        <button type="button" @click="payDateAfter = ''; payDateBefore = ''; payDateOn = ''" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 text-xs">Clear</button>
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow transition-colors">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 8. Biaya (IDR) -->
                            <th class="py-1 px-2 text-right whitespace-nowrap relative border-r border-slate-200 transition-colors" :class="activeFilters.amount ? 'bg-emerald-50 border-b-2 border-b-emerald-600 text-emerald-900' : ''" @click.outside="if (openPop === 'amount') openPop = null">
                                <div class="flex items-center gap-1.5 justify-end">
                                    <button type="button" @click="toggleSort('amount')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('amount') !== -1 ? 'text-emerald-700 font-extrabold' : 'text-slate-700 hover:text-slate-900'" title="Urutkan Biaya">
                                        <span>Biaya (IDR)</span>
                                        <template x-if="getSortIndex('amount') === -1">
                                            <i class="fa-solid fa-sort text-slate-400 text-[10px] group-hover/sort:text-slate-600 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('amount') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-emerald-700 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('amount') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-emerald-100 px-1 py-0.2 rounded-full border border-emerald-300 font-mono text-emerald-800" x-text="getSortIndex('amount') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'amount' ? null : 'amount')" class="p-1 rounded transition-colors" :class="activeFilters.amount ? 'text-emerald-700 bg-emerald-100 ring-1 ring-emerald-400 font-bold shadow-sm' : 'text-slate-400 hover:text-slate-700 hover:bg-slate-200'" title="Filter Biaya">
                                        <i class="fa-solid" :class="activeFilters.amount ? 'fa-filter text-emerald-600 text-[11px]' : 'fa-caret-down text-xs'"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'amount'" x-cloak x-transition class="absolute z-50 right-0 mt-2 p-3.5 bg-white border border-slate-200 rounded-xl shadow-xl space-y-3 text-left font-normal normal-case min-w-[240px]"
                                     x-data="{
                                         amountMin: '{{ $amountMin ?? '' }}',
                                         amountMax: '{{ $amountMax ?? '' }}',
                                         amountEq: '{{ $amountEq ?? '' }}',
                                         onMinMaxChange() {
                                             if (this.amountMin || this.amountMax) {
                                                 this.amountEq = '';
                                             }
                                         },
                                         onEqChange() {
                                             if (this.amountEq) {
                                                 this.amountMin = '';
                                                 this.amountMax = '';
                                             }
                                         }
                                     }">
                                    <div class="text-xs font-semibold text-slate-800 border-b border-slate-200 pb-1.5 flex items-center justify-between">
                                        <span>Filter Nominal Biaya</span>
                                        <i class="fa-solid fa-money-bill-wave text-emerald-600"></i>
                                    </div>

                                    <div class="space-y-2 text-xs">
                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <label class="block text-[10px] font-medium text-slate-600 mb-1">Min (Rp)</label>
                                                <input type="number" name="amount_min" x-model="amountMin" @input="onMinMaxChange()" placeholder="Contoh: 500000" class="w-full h-8 rounded-lg px-2 text-xs bg-white border border-slate-300 text-slate-900 font-mono focus:border-emerald-600 focus:outline-none">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-medium text-slate-600 mb-1">Max (Rp)</label>
                                                <input type="number" name="amount_max" x-model="amountMax" @input="onMinMaxChange()" placeholder="Contoh: 5000000" class="w-full h-8 rounded-lg px-2 text-xs bg-white border border-slate-300 text-slate-900 font-mono focus:border-emerald-600 focus:outline-none">
                                            </div>
                                        </div>

                                        <div class="pt-1 border-t border-slate-200">
                                            <label class="block text-[10px] font-medium text-slate-600 mb-1">Sama Dengan (Exact Rp)</label>
                                            <input type="number" name="amount_eq" x-model="amountEq" @input="onEqChange()" placeholder="Contoh: 1500000" class="w-full h-8 rounded-lg px-2.5 text-xs bg-white border border-slate-300 text-slate-900 font-mono focus:border-emerald-600 focus:outline-none">
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between pt-2 border-t border-slate-200 text-[11px]">
                                        <button type="button" @click="amountMin = ''; amountMax = ''; amountEq = ''" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 text-xs">Clear</button>
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow transition-colors">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 9. Status -->
                            <th class="py-1 px-2 text-center whitespace-nowrap relative transition-colors" :class="activeFilters.status ? 'bg-emerald-50 border-b-2 border-b-emerald-600 text-emerald-900' : ''" @click.outside="if (openPop === 'status') openPop = null">
                                <div class="flex items-center gap-1.5 justify-center">
                                    <button type="button" @click="toggleSort('status')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('status') !== -1 ? 'text-emerald-700 font-extrabold' : 'text-slate-700 hover:text-slate-900'" title="Urutkan Status">
                                        <span>Status</span>
                                        <template x-if="getSortIndex('status') === -1">
                                            <i class="fa-solid fa-sort text-slate-400 text-[10px] group-hover/sort:text-slate-600 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('status') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-emerald-700 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('status') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-emerald-100 px-1 py-0.2 rounded-full border border-emerald-300 font-mono text-emerald-800" x-text="getSortIndex('status') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'status' ? null : 'status')" class="p-1 rounded transition-colors" :class="activeFilters.status ? 'text-emerald-700 bg-emerald-100 ring-1 ring-emerald-400 font-bold shadow-sm' : 'text-slate-400 hover:text-slate-700 hover:bg-slate-200'" title="Filter Status Pembayaran">
                                        <i class="fa-solid" :class="activeFilters.status ? 'fa-filter text-emerald-600 text-[11px]' : 'fa-caret-down text-xs'"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'status'" x-cloak x-transition class="absolute z-50 right-0 mt-2 p-3 bg-white border border-slate-200 rounded-xl shadow-xl space-y-2 text-left font-normal normal-case min-w-[200px]" x-data="{ selected: {{ json_encode($status) }} }">
                                    <div class="text-xs font-semibold text-slate-800 border-b border-slate-200 pb-1.5 flex items-center justify-between">
                                        <span>Pilih Status</span>
                                        <i class="fa-solid fa-list-check text-emerald-600"></i>
                                    </div>
                                    <div class="space-y-1">
                                        @foreach($statusOptions as $st)
                                            <label class="flex items-center gap-2 px-2 py-1 rounded hover:bg-slate-100 cursor-pointer text-xs">
                                                <input type="checkbox" name="status[]" value="{{ $st }}" x-model="selected" class="rounded bg-white border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                                <span class="text-slate-800">{{ $st }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <div class="flex items-center justify-between pt-2 border-t border-slate-200 text-[11px]">
                                        <button type="button" @click="selected = []" class="px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 text-xs">Clear</button>
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow transition-colors">Terapkan</button>
                                    </div>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="expenses-tbody" class="divide-y divide-slate-200">
                        @include('expenses._rows', ['expenses' => $expenses])
                    </tbody>
                </table>

                <!-- Infinite Loading Indicator -->
                <div x-show="loading" class="p-4 text-center text-emerald-700 font-semibold text-xs flex items-center justify-center gap-2">
                    <i class="fa-solid fa-spinner fa-spin"></i> Memuat data histori biaya...
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
