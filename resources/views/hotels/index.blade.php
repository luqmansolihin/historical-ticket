@extends('layouts.app')

@section('title', 'Histori Hotel')

@section('content')
<div x-data="{
        selectedHotel: null,
        showModal: false,
        openPop: null,
        nextPageUrl: '{{ $hotels->nextPageUrl() }}',
        loading: false,
        hasMore: {{ $hotels->hasMorePages() ? 'true' : 'false' }},
        sorts: {{ json_encode($sorts ?? []) }},
        hasFilters: {{ ($search || $searchCode || $searchInvoice || $searchHotel || $searchGuest || $searchBooker || $searchPayer || !empty($status) || $dateAfter || $dateBefore || $dateOn || $checkInFrom || $checkInTo || $checkOutFrom || $checkOutTo || $payDateAfter || $payDateBefore || $payDateOn || $amountMin || $amountMax || $amountEq) ? 'true' : 'false' }},
        activeFilters: {
            code: {{ !empty($searchCode) ? 'true' : 'false' }},
            invoice: {{ !empty($searchInvoice) ? 'true' : 'false' }},
            date: {{ ($dateAfter || $dateBefore || $dateOn) ? 'true' : 'false' }},
            hotel: {{ !empty($searchHotel) ? 'true' : 'false' }},
            check_in: {{ ($checkInFrom || $checkInTo) ? 'true' : 'false' }},
            check_out: {{ ($checkOutFrom || $checkOutTo) ? 'true' : 'false' }},
            guest: {{ !empty($searchGuest) ? 'true' : 'false' }},
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
            this.activeFilters.check_in = !!((formData.get('check_in_from') && formData.get('check_in_from').trim()) || (formData.get('check_in_to') && formData.get('check_in_to').trim()));
            this.activeFilters.check_out = !!((formData.get('check_out_from') && formData.get('check_out_from').trim()) || (formData.get('check_out_to') && formData.get('check_out_to').trim()));
            this.activeFilters.guest = !!(formData.get('search_guest') && formData.get('search_guest').trim());
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
            const defaultDir = (col === 'booking_date' || col === 'check_in_date' || col === 'payment_date' || col === 'amount' || col === 'id') ? 'desc' : 'asc';
            
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
    }" class="flex-1 flex flex-col min-h-0 h-full overflow-hidden space-y-3">

    <!-- Top Summary Stats Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5 shrink-0">
        <div class="glass-card p-3 rounded-2xl border border-slate-800 flex items-center justify-between">
            <div>
                <div class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">Total Reservasi</div>
                <div class="text-base sm:text-lg font-bold font-mono text-white mt-0.5">{{ number_format($totalHotels) }}</div>
            </div>
            <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center">
                <i class="fa-solid fa-hotel text-sm"></i>
            </div>
        </div>

        <div class="glass-card p-3 rounded-2xl border border-slate-800 flex items-center justify-between">
            <div>
                <div class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">Total Nominal</div>
                <div class="text-base sm:text-lg font-bold font-mono text-emerald-400 mt-0.5">Rp {{ number_format($totalAmount, 0, ',', '.') }}</div>
            </div>
            <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                <i class="fa-solid fa-money-bill-wave text-sm"></i>
            </div>
        </div>

        <div class="glass-card p-3 rounded-2xl border border-slate-800 flex items-center justify-between">
            <div>
                <div class="text-[10px] font-medium text-emerald-400 uppercase tracking-wider">Status Lunas</div>
                <div class="text-base sm:text-lg font-bold font-mono text-emerald-300 mt-0.5">{{ number_format($totalLunas) }}</div>
            </div>
            <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                <i class="fa-solid fa-circle-check text-sm"></i>
            </div>
        </div>

        <div class="glass-card p-3 rounded-2xl border border-slate-800 flex items-center justify-between">
            <div>
                <div class="text-[10px] font-medium text-rose-400 uppercase tracking-wider">Belum Bayar</div>
                <div class="text-base sm:text-lg font-bold font-mono text-rose-300 mt-0.5">{{ number_format($totalBelumBayar) }}</div>
            </div>
            <div class="w-8 h-8 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center">
                <i class="fa-solid fa-clock text-sm"></i>
            </div>
        </div>

        <div class="glass-card p-3 rounded-2xl border border-slate-800 flex items-center justify-between col-span-2 sm:col-span-1">
            <div>
                <div class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">Dibatalkan</div>
                <div class="text-base sm:text-lg font-bold font-mono text-slate-400 mt-0.5">{{ number_format($totalDibatalkan) }}</div>
            </div>
            <div class="w-8 h-8 rounded-xl bg-slate-800 text-slate-400 flex items-center justify-center">
                <i class="fa-solid fa-ban text-sm"></i>
            </div>
        </div>
    </div>

    <!-- Data Table Container & Column Header Filters -->
    <div class="glass-card rounded-2xl shadow-2xl relative z-10 no-print flex-1 flex flex-col min-h-0 h-full overflow-hidden">
        <!-- Action Toolbar -->
        <div class="px-3 py-1.5 bg-slate-900/90 border-b border-slate-800/80 flex items-center justify-between gap-2 shrink-0">
            <div class="text-xs text-slate-400 font-mono hidden sm:block">
                <i class="fa-solid fa-mouse-pointer text-amber-400 mr-1"></i> Double klik baris tabel untuk edit atau preview Voucher Hotel dalam HTML
            </div>

            <div class="flex items-center gap-2 ml-auto">
                <a href="{{ route('hotels.index') }}" x-show="hasFilters || sorts.length > 0" x-cloak @click.prevent="resetFilters()" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium text-rose-300 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 transition-all shadow-sm" title="Reset Filter & Urutan">
                    <i class="fa-solid fa-rotate-left mr-1.5 text-xs"></i> Reset Filter & Urutan
                </a>

                <a id="export-csv-btn" href="{{ route('hotels.export', request()->query()) }}" class="inline-flex items-center px-3.5 py-1.5 rounded-lg text-xs font-medium text-slate-200 bg-slate-800 hover:bg-slate-700 border border-slate-700 transition-all shadow-sm">
                    <i class="fa-solid fa-file-csv text-emerald-400 mr-1.5 text-xs"></i> Export CSV
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
                                    <button type="button" @click="toggleSort('booking_code')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('booking_code') !== -1 ? 'text-amber-400 font-extrabold' : 'text-slate-300 hover:text-white'">
                                        <span>Kode Booking</span>
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'code' ? null : 'code')" class="p-1 rounded transition-colors" :class="activeFilters.code ? 'text-amber-300 bg-amber-500/30' : 'text-slate-500 hover:text-slate-300'">
                                        <i class="fa-solid" :class="activeFilters.code ? 'fa-filter text-amber-400 text-[11px]' : 'fa-caret-down text-xs'"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'code'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-3 text-left font-normal normal-case min-w-[220px]">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Filter Kode Booking</span>
                                        <i class="fa-solid fa-hotel text-amber-400"></i>
                                    </div>
                                    <input type="text" name="search_code" value="{{ $searchCode }}" placeholder="Cari kode booking..." class="w-full px-3 py-1.5 rounded-lg bg-slate-950 border border-slate-700 text-xs text-white placeholder-slate-500">
                                    <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-800/80">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-500 text-white text-xs font-semibold shadow">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 2. Kode Invoice -->
                            <th class="py-1 px-2 whitespace-nowrap relative border-r border-slate-800/60 transition-colors" :class="activeFilters.invoice ? 'bg-indigo-950/80 border-b-2 border-b-indigo-400 text-indigo-200' : ''" @click.outside="if (openPop === 'invoice') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <button type="button" @click="toggleSort('invoice_code')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('invoice_code') !== -1 ? 'text-indigo-300 font-extrabold' : 'text-slate-300 hover:text-white'">
                                        <span>Kode Invoice</span>
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'invoice' ? null : 'invoice')" class="p-1 rounded transition-colors" :class="activeFilters.invoice ? 'text-indigo-300 bg-indigo-500/30' : 'text-slate-500 hover:text-slate-300'">
                                        <i class="fa-solid" :class="activeFilters.invoice ? 'fa-filter text-indigo-400 text-[11px]' : 'fa-caret-down text-xs'"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'invoice'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-3 text-left font-normal normal-case min-w-[220px]">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Filter Kode Invoice</span>
                                        <i class="fa-solid fa-receipt text-indigo-400"></i>
                                    </div>
                                    <input type="text" name="search_invoice" value="{{ $searchInvoice }}" placeholder="Cari kode invoice..." class="w-full px-3 py-1.5 rounded-lg bg-slate-950 border border-slate-700 text-xs text-white placeholder-slate-500">
                                    <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-800/80">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 3. Tgl Booking -->
                            <th class="py-1 px-2 whitespace-nowrap border-r border-slate-800/60">Tgl Booking</th>

                            <!-- 4. Nama Hotel -->
                            <th class="py-1 px-2 whitespace-nowrap relative border-r border-slate-800/60" @click.outside="if (openPop === 'hotel') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <span class="font-bold">Nama Hotel</span>
                                    <button type="button" @click="openPop = (openPop === 'hotel' ? null : 'hotel')" class="p-1 text-slate-500 hover:text-slate-300">
                                        <i class="fa-solid fa-caret-down text-xs"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'hotel'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-3 text-left font-normal normal-case min-w-[220px]">
                                    <input type="text" name="search_hotel" value="{{ $searchHotel }}" placeholder="Cari nama hotel..." class="w-full px-3 py-1.5 rounded-lg bg-slate-950 border border-slate-700 text-xs text-white">
                                    <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-800/80">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-500 text-white text-xs font-semibold">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 5. Check In -->
                            <th class="py-1 px-2 whitespace-nowrap border-r border-slate-800/60">Check In</th>

                            <!-- 6. Check Out -->
                            <th class="py-1 px-2 whitespace-nowrap border-r border-slate-800/60">Check Out</th>

                            <!-- 7. Malam -->
                            <th class="py-1 px-2 text-center whitespace-nowrap border-r border-slate-800/60">Malam</th>

                            <!-- 8. Tamu -->
                            <th class="py-1 px-2 whitespace-nowrap border-r border-slate-800/60">Nama Tamu</th>

                            <!-- 9. Jml Tamu -->
                            <th class="py-1 px-2 text-center whitespace-nowrap border-r border-slate-800/60">Jml Tamu</th>

                            <!-- 10. Pemesan -->
                            <th class="py-1 px-2 whitespace-nowrap border-r border-slate-800/60">Pemesan</th>

                            <!-- 11. Pembayar -->
                            <th class="py-1 px-2 whitespace-nowrap border-r border-slate-800/60">Pembayar</th>

                            <!-- 12. Tgl Bayar -->
                            <th class="py-1 px-2 whitespace-nowrap border-r border-slate-800/60">Tgl Bayar</th>

                            <!-- 13. Biaya -->
                            <th class="py-1 px-2 text-right whitespace-nowrap border-r border-slate-800/60">Biaya (IDR)</th>

                            <!-- 14. Status -->
                            <th class="py-1 px-2 text-center whitespace-nowrap">Status</th>
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
                                    <a :href="selectedHotel.pdf_url" target="_blank" class="px-3.5 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs flex items-center gap-1.5 transition-colors shadow-sm" title="Stream / Download PDF Voucher Hotel">
                                        <i class="fa-solid fa-file-pdf"></i> Stream PDF
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
                                    <span class="text-xs text-amber-200 block uppercase">Durasi</span>
                                    <span class="font-display text-lg font-bold text-amber-300 block mt-0.5" x-text="selectedHotel.night_count + ' Malam'"></span>
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
                                    <span class="text-xs font-mono font-semibold text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded-full border border-amber-500/30" x-text="selectedHotel.guest_count + ' Tamu'"></span>
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
                                    <span class="text-xs text-slate-400 block">Booked By</span>
                                    <span class="text-xs font-semibold text-indigo-300 mt-0.5 block" x-text="selectedHotel.booked_by"></span>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-400 block">Paid By & Tanggal</span>
                                    <span class="text-xs font-semibold text-emerald-300 mt-0.5 block" x-text="selectedHotel.paid_by + ' (' + selectedHotel.payment_date + ')'"></span>
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
