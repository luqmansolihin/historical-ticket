@extends('layouts.app')

@section('title', 'Daftar List Akun - Management Users')

@section('content')
<div x-data="{
        openPop: null,
        nextPageUrl: '{{ $users->nextPageUrl() }}',
        loading: false,
        hasMore: {{ $users->hasMorePages() ? 'true' : 'false' }},
        sorts: {{ json_encode($sorts ?? []) }},
        hasFilters: {{ ($search || $searchName || $searchEmail || $roleFilter || $dateAfter || $dateBefore || $dateOn) ? 'true' : 'false' }},
        activeFilters: {
            name: {{ !empty($searchName) ? 'true' : 'false' }},
            email: {{ !empty($searchEmail) ? 'true' : 'false' }},
            role: {{ !empty($roleFilter) ? 'true' : 'false' }},
            date: {{ ($dateAfter || $dateBefore || $dateOn) ? 'true' : 'false' }},
        },
        init() {
            this.checkAutoFill();
            window.addEventListener('popstate', () => {
                this.applyFilters(window.location.href, false);
            });
        },
        checkHasFilters() {
            const form = document.getElementById('users-filter-form');
            if (!form) { this.hasFilters = false; return; }
            const formData = new FormData(form);
            
            this.activeFilters.name = !!(formData.get('search_name') && formData.get('search_name').trim());
            this.activeFilters.email = !!(formData.get('search_email') && formData.get('search_email').trim());
            this.activeFilters.role = !!(formData.get('role') && formData.get('role').trim());
            this.activeFilters.date = !!((formData.get('date_after') && formData.get('date_after').trim()) || (formData.get('date_before') && formData.get('date_before').trim()) || (formData.get('date_on') && formData.get('date_on').trim()));
            
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
                const tbody = document.getElementById('users-tbody');
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
            const defaultDir = (col === 'created_at' || col === 'id') ? 'desc' : 'asc';
            
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
            const sortInput = document.getElementById('users_sort_input');
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
            const form = document.getElementById('users-filter-form');
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
                const tbody = document.getElementById('users-tbody');
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

                this.checkAutoFill();
            })
            .catch(err => {
                console.error(err);
                this.loading = false;
            });
        },
        resetFilters() {
            const form = document.getElementById('users-filter-form');
            if (form) form.reset();
            this.sorts = [];
            this.hasFilters = false;
            Object.keys(this.activeFilters).forEach(k => this.activeFilters[k] = false);
            const sortInput = document.getElementById('users_sort_input');
            if (sortInput) sortInput.value = '';
            this.applyFilters(form.action);
        }
    }" class="flex-1 flex flex-col min-h-0 h-full overflow-hidden">

    <!-- ERP Data Table Container & Column Header Filters -->
    <div class="glass-card rounded-2xl shadow-2xl relative z-10 no-print flex-1 flex flex-col min-h-0 h-full overflow-hidden">
        <!-- ERP Data Grid Action Toolbar -->
        <div class="px-3 py-1.5 bg-slate-900/90 border-b border-slate-800/80 flex items-center justify-between gap-2 shrink-0">
            <div class="text-xs text-slate-400 font-mono hidden sm:block">
                <i class="fa-solid fa-mouse-pointer text-sky-400 mr-1"></i> Double klik baris tabel untuk edit data akun
            </div>

            <div class="flex items-center gap-2 ml-auto">
                <a href="{{ route('users.index') }}" x-show="hasFilters || sorts.length > 0" x-cloak @click.prevent="resetFilters()" class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-rose-300 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 transition-all shadow-sm" title="Reset Filter & Urutan Tabel">
                    <i class="fa-solid fa-rotate-left mr-1.5 text-xs"></i> Reset Filter & Urutan
                </a>

                <a href="{{ route('users.create') }}" class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold text-white bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-400 hover:to-indigo-500 shadow-md shadow-sky-500/20 transition-all active:scale-95">
                    <i class="fa-solid fa-user-plus mr-1.5"></i> Tambah Akun Baru
                </a>
            </div>
        </div>

        <form id="users-filter-form" action="{{ route('users.index') }}" method="GET" @submit.prevent="applyFilters()" class="flex-1 flex flex-col min-h-0 h-full overflow-hidden justify-between">
            <input type="hidden" id="users_sort_input" name="sort" :value="serializeSorts()">

            <div x-ref="scrollContainer" class="overflow-auto flex-1 min-h-0" @scroll.passive="onScroll($event)">
                <table class="w-full text-left text-[9.5px] leading-tight text-slate-300 whitespace-nowrap border-collapse">
                    <thead class="bg-slate-900/95 text-[9px] uppercase font-bold text-slate-400 tracking-tight border-b border-slate-800 whitespace-nowrap sticky top-0 z-20 backdrop-blur-md">
                        <tr>
                            <!-- 1. ID & Nama Pengguna -->
                            <th class="py-1 px-2 whitespace-nowrap relative border-r border-slate-800/60 transition-colors" :class="activeFilters.name ? 'bg-sky-950/80 border-b-2 border-b-sky-400 text-sky-200' : ''" @click.outside="if (openPop === 'name') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <button type="button" @click="toggleSort('name')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('name') !== -1 ? 'text-sky-400 font-extrabold' : 'text-slate-300 hover:text-white'" title="Urutkan Nama Pengguna">
                                        <span>Nama Pengguna</span>
                                        <template x-if="getSortIndex('name') === -1">
                                            <i class="fa-solid fa-sort text-slate-600 text-[10px] group-hover/sort:text-slate-400 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('name') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-sky-400 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('name') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-sky-500/20 px-1 py-0.2 rounded-full border border-sky-500/40 font-mono" x-text="getSortIndex('name') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'name' ? null : 'name')" class="p-1 rounded transition-colors" :class="activeFilters.name ? 'text-sky-300 bg-sky-500/30 ring-1 ring-sky-400/50 font-bold shadow-sm shadow-sky-500/20' : 'text-slate-500 hover:text-slate-300 hover:bg-slate-800'" title="Filter Nama Pengguna">
                                        <i class="fa-solid" :class="activeFilters.name ? 'fa-filter text-sky-400 text-[11px]' : 'fa-caret-down text-xs'"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'name'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-3 text-left font-normal normal-case min-w-[220px]">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Filter Nama Pengguna</span>
                                        <i class="fa-solid fa-user text-sky-400"></i>
                                    </div>
                                    <input type="text" name="search_name" value="{{ $searchName }}" placeholder="Cari nama pengguna..." class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 placeholder-slate-500 focus:border-sky-400 focus:outline-none">
                                    <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-800/80">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow transition-colors">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 2. Alamat Email -->
                            <th class="py-1 px-2 whitespace-nowrap relative border-r border-slate-800/60 transition-colors" :class="activeFilters.email ? 'bg-sky-950/80 border-b-2 border-b-sky-400 text-sky-200' : ''" @click.outside="if (openPop === 'email') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <button type="button" @click="toggleSort('email')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('email') !== -1 ? 'text-sky-400 font-extrabold' : 'text-slate-300 hover:text-white'" title="Urutkan Alamat Email">
                                        <span>Alamat Email</span>
                                        <template x-if="getSortIndex('email') === -1">
                                            <i class="fa-solid fa-sort text-slate-600 text-[10px] group-hover/sort:text-slate-400 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('email') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-sky-400 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('email') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-sky-500/20 px-1 py-0.2 rounded-full border border-sky-500/40 font-mono" x-text="getSortIndex('email') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'email' ? null : 'email')" class="p-1 rounded transition-colors" :class="activeFilters.email ? 'text-sky-300 bg-sky-500/30 ring-1 ring-sky-400/50 font-bold shadow-sm shadow-sky-500/20' : 'text-slate-500 hover:text-slate-300 hover:bg-slate-800'" title="Filter Email">
                                        <i class="fa-solid" :class="activeFilters.email ? 'fa-filter text-sky-400 text-[11px]' : 'fa-caret-down text-xs'"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'email'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-3 text-left font-normal normal-case min-w-[220px]">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Filter Alamat Email</span>
                                        <i class="fa-solid fa-envelope text-sky-400"></i>
                                    </div>
                                    <input type="text" name="search_email" value="{{ $searchEmail }}" placeholder="Cari email..." class="w-full h-8 rounded-lg px-2.5 text-xs bg-slate-950 border border-slate-700/80 text-slate-200 placeholder-slate-500 focus:border-sky-400 focus:outline-none">
                                    <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-800/80">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow transition-colors">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 3. Role Akses -->
                            <th class="py-1 px-2 whitespace-nowrap relative border-r border-slate-800/60 transition-colors" :class="activeFilters.role ? 'bg-sky-950/80 border-b-2 border-b-sky-400 text-sky-200' : ''" @click.outside="if (openPop === 'role') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <button type="button" @click="toggleSort('role')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('role') !== -1 ? 'text-sky-400 font-extrabold' : 'text-slate-300 hover:text-white'" title="Urutkan Role Akses">
                                        <span>Role Akses</span>
                                        <template x-if="getSortIndex('role') === -1">
                                            <i class="fa-solid fa-sort text-slate-600 text-[10px] group-hover/sort:text-slate-400 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('role') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-sky-400 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('role') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-sky-500/20 px-1 py-0.2 rounded-full border border-sky-500/40 font-mono" x-text="getSortIndex('role') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'role' ? null : 'role')" class="p-1 rounded transition-colors" :class="activeFilters.role ? 'text-sky-300 bg-sky-500/30 ring-1 ring-sky-400/50 font-bold shadow-sm shadow-sky-500/20' : 'text-slate-500 hover:text-slate-300 hover:bg-slate-800'" title="Filter Role">
                                        <i class="fa-solid" :class="activeFilters.role ? 'fa-filter text-sky-400 text-[11px]' : 'fa-caret-down text-xs'"></i>
                                    </button>
                                </div>
                                <div x-show="openPop === 'role'" x-cloak x-transition class="absolute z-50 left-0 mt-2 p-3 bg-slate-900 border border-slate-700/90 rounded-xl shadow-2xl space-y-3 text-left font-normal normal-case min-w-[200px]">
                                    <div class="text-xs font-semibold text-slate-300 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                        <span>Filter Role Akses</span>
                                        <i class="fa-solid fa-shield-halved text-sky-400"></i>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="flex items-center gap-2 px-2 py-1 rounded hover:bg-slate-800 cursor-pointer text-xs text-slate-200">
                                            <input type="radio" name="role" value="" {{ empty($roleFilter) ? 'checked' : '' }} class="text-sky-500">
                                            <span>Semua Role</span>
                                        </label>
                                        @foreach($roleOptions as $roleOpt)
                                            <label class="flex items-center gap-2 px-2 py-1 rounded hover:bg-slate-800 cursor-pointer text-xs text-slate-200">
                                                <input type="radio" name="role" value="{{ $roleOpt }}" {{ $roleFilter === $roleOpt ? 'checked' : '' }} class="text-sky-500">
                                                <span>{{ ($roleOpt === 'finance' || $roleOpt === 'booker') ? 'Finance' : ucfirst($roleOpt) }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-800/80">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow transition-colors">Terapkan</button>
                                    </div>
                                </div>
                            </th>

                            <!-- 4. Tanggal Terdaftar -->
                            <th class="py-1 px-2 whitespace-nowrap relative transition-colors" :class="activeFilters.date ? 'bg-sky-950/80 border-b-2 border-b-sky-400 text-sky-200' : ''" @click.outside="if (openPop === 'date') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <button type="button" @click="toggleSort('created_at')" class="flex items-center gap-1 font-bold transition-colors cursor-pointer select-none group/sort" :class="getSortIndex('created_at') !== -1 ? 'text-sky-400 font-extrabold' : 'text-slate-300 hover:text-white'" title="Urutkan Tanggal Terdaftar">
                                        <span>Tgl Terdaftar</span>
                                        <template x-if="getSortIndex('created_at') === -1">
                                            <i class="fa-solid fa-sort text-slate-600 text-[10px] group-hover/sort:text-slate-400 transition-colors"></i>
                                        </template>
                                        <template x-if="getSortIndex('created_at') !== -1">
                                            <span class="inline-flex items-center gap-0.5 text-sky-400 font-bold text-[10px]">
                                                <i class="fa-solid" :class="getSortDir('created_at') === 'asc' ? 'fa-arrow-up-wide-short' : 'fa-arrow-down-wide-short'"></i>
                                                <span x-show="sorts.length > 1" class="text-[8px] bg-sky-500/20 px-1 py-0.2 rounded-full border border-sky-500/40 font-mono" x-text="getSortIndex('created_at') + 1"></span>
                                            </span>
                                        </template>
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'date' ? null : 'date')" class="p-1 rounded transition-colors" :class="activeFilters.date ? 'text-sky-300 bg-sky-500/30 ring-1 ring-sky-400/50 font-bold shadow-sm shadow-sky-500/20' : 'text-slate-500 hover:text-slate-300 hover:bg-slate-800'" title="Filter Tanggal Terdaftar">
                                        <i class="fa-solid" :class="activeFilters.date ? 'fa-filter text-sky-400 text-[11px]' : 'fa-caret-down text-xs'"></i>
                                    </button>
                                </div>
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
                                        <span>Filter Tgl Terdaftar</span>
                                        <i class="fa-regular fa-calendar-days text-sky-400"></i>
                                    </div>
                                    <div class="space-y-2 text-xs">
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
                                        <div>
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
                        </tr>
                    </thead>
                    <tbody id="users-tbody" class="divide-y divide-slate-800/60 whitespace-nowrap font-sans">
                        @include('users._rows', ['users' => $users])
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>
@endsection
