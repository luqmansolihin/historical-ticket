@extends('layouts.app')

@section('title', 'Daftar List Akun - Management Users')

@section('content')
<div x-data="{
        openPop: null,
        nextPageUrl: '{{ $users->nextPageUrl() }}',
        loading: false,
        hasMore: {{ $users->hasMorePages() ? 'true' : 'false' }},
        sortBy: {{ json_encode($sortBy) }},
        sortDir: '{{ $sortDir ?? 'desc' }}',
        init() {
            this.checkAutoFill();
            window.addEventListener('popstate', () => {
                this.applyFilters(window.location.href, false);
            });
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
            if (this.sortBy === col) {
                this.sortDir = (this.sortDir === 'asc') ? 'desc' : 'asc';
            } else {
                this.sortBy = col;
                this.sortDir = (col === 'created_at' || col === 'id') ? 'desc' : 'asc';
            }
            const sortByInput = document.getElementById('users_sort_by_input');
            const sortDirInput = document.getElementById('users_sort_dir_input');
            if (sortByInput) sortByInput.value = this.sortBy;
            if (sortDirInput) sortDirInput.value = this.sortDir;
            this.applyFilters();
        },
        applyFilters(customUrl = null, updateHistory = true) {
            this.loading = true;
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
            this.sortBy = null;
            this.sortDir = 'desc';
            const sortByInput = document.getElementById('users_sort_by_input');
            const sortDirInput = document.getElementById('users_sort_dir_input');
            if (sortByInput) sortByInput.value = '';
            if (sortDirInput) sortDirInput.value = 'desc';
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
                @if($search || $searchName || $searchEmail || $roleFilter || $dateAfter || $dateBefore || $dateOn)
                    <a href="{{ route('users.index') }}" @click.prevent="resetFilters()" class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-rose-300 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 transition-all shadow-sm" title="Reset Semua Filter">
                        <i class="fa-solid fa-rotate-left mr-1.5 text-xs"></i> Reset Filter
                    </a>
                @endif

                <a href="{{ route('users.create') }}" class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold text-white bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-400 hover:to-indigo-500 shadow-md shadow-sky-500/20 transition-all active:scale-95">
                    <i class="fa-solid fa-user-plus mr-1.5"></i> Tambah Akun Baru
                </a>
            </div>
        </div>

        <form id="users-filter-form" action="{{ route('users.index') }}" method="GET" @submit.prevent="applyFilters()" class="flex-1 flex flex-col min-h-0 h-full overflow-hidden justify-between">
            <input type="hidden" id="users_sort_by_input" name="sort_by" value="{{ $sortBy ?? '' }}">
            <input type="hidden" id="users_sort_dir_input" name="sort_dir" value="{{ $sortDir ?? 'desc' }}">

            <div x-ref="scrollContainer" class="overflow-auto flex-1 min-h-0" @scroll.passive="onScroll($event)">
                <table class="w-full text-left text-[9.5px] leading-tight text-slate-300 whitespace-nowrap border-collapse">
                    <thead class="bg-slate-900/95 text-[9px] uppercase font-bold text-slate-400 tracking-tight border-b border-slate-800 whitespace-nowrap sticky top-0 z-20 backdrop-blur-md">
                        <tr>
                            <!-- 1. ID & Nama Pengguna -->
                            <th class="py-1 px-2 whitespace-nowrap relative border-r border-slate-800/60" @click.outside="if (openPop === 'name') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <button type="button" @click="toggleSort('name')" class="flex items-center gap-1 font-bold text-slate-300 hover:text-white transition-colors cursor-pointer select-none group/sort" title="Urutkan Nama Pengguna">
                                        <span>Nama Pengguna</span>
                                        @if(($sortBy ?? '') === 'name')
                                            <i class="fa-solid {{ ($sortDir ?? '') === 'asc' ? 'fa-arrow-up-wide-short text-sky-400' : 'fa-arrow-down-wide-short text-sky-400' }} text-[10px]"></i>
                                        @else
                                            <i class="fa-solid fa-sort text-slate-600 text-[10px] group-hover/sort:text-slate-400 transition-colors"></i>
                                        @endif
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'name' ? null : 'name')" class="p-1 rounded hover:bg-slate-800 transition-colors {{ $searchName ? 'text-sky-400 font-bold bg-sky-500/20' : 'text-slate-500 hover:text-slate-300' }}" title="Filter Nama Pengguna">
                                        <i class="fa-solid fa-caret-down text-xs"></i>
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
                            <th class="py-1 px-2 whitespace-nowrap relative border-r border-slate-800/60" @click.outside="if (openPop === 'email') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <button type="button" @click="toggleSort('email')" class="flex items-center gap-1 font-bold text-slate-300 hover:text-white transition-colors cursor-pointer select-none group/sort" title="Urutkan Alamat Email">
                                        <span>Alamat Email</span>
                                        @if(($sortBy ?? '') === 'email')
                                            <i class="fa-solid {{ ($sortDir ?? '') === 'asc' ? 'fa-arrow-up-wide-short text-sky-400' : 'fa-arrow-down-wide-short text-sky-400' }} text-[10px]"></i>
                                        @else
                                            <i class="fa-solid fa-sort text-slate-600 text-[10px] group-hover/sort:text-slate-400 transition-colors"></i>
                                        @endif
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'email' ? null : 'email')" class="p-1 rounded hover:bg-slate-800 transition-colors {{ $searchEmail ? 'text-sky-400 font-bold bg-sky-500/20' : 'text-slate-500 hover:text-slate-300' }}" title="Filter Email">
                                        <i class="fa-solid fa-caret-down text-xs"></i>
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
                            <th class="py-1 px-2 whitespace-nowrap relative border-r border-slate-800/60" @click.outside="if (openPop === 'role') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <button type="button" @click="toggleSort('role')" class="flex items-center gap-1 font-bold text-slate-300 hover:text-white transition-colors cursor-pointer select-none group/sort" title="Urutkan Role Akses">
                                        <span>Role Akses</span>
                                        @if(($sortBy ?? '') === 'role')
                                            <i class="fa-solid {{ ($sortDir ?? '') === 'asc' ? 'fa-arrow-up-wide-short text-sky-400' : 'fa-arrow-down-wide-short text-sky-400' }} text-[10px]"></i>
                                        @else
                                            <i class="fa-solid fa-sort text-slate-600 text-[10px] group-hover/sort:text-slate-400 transition-colors"></i>
                                        @endif
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'role' ? null : 'role')" class="p-1 rounded hover:bg-slate-800 transition-colors {{ $roleFilter ? 'text-sky-400 font-bold bg-sky-500/20' : 'text-slate-500 hover:text-slate-300' }}" title="Filter Role">
                                        <i class="fa-solid fa-caret-down text-xs"></i>
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
                            <th class="py-1 px-2 whitespace-nowrap relative" @click.outside="if (openPop === 'date') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <button type="button" @click="toggleSort('created_at')" class="flex items-center gap-1 font-bold text-slate-300 hover:text-white transition-colors cursor-pointer select-none group/sort" title="Urutkan Tanggal Terdaftar">
                                        <span>Tgl Terdaftar</span>
                                        @if(($sortBy ?? '') === 'created_at')
                                            <i class="fa-solid {{ ($sortDir ?? '') === 'asc' ? 'fa-arrow-up-wide-short text-sky-400' : 'fa-arrow-down-wide-short text-sky-400' }} text-[10px]"></i>
                                        @else
                                            <i class="fa-solid fa-sort text-slate-600 text-[10px] group-hover/sort:text-slate-400 transition-colors"></i>
                                        @endif
                                    </button>
                                    <button type="button" @click="openPop = (openPop === 'date' ? null : 'date')" class="p-1 rounded hover:bg-slate-800 transition-colors {{ ($dateAfter || $dateBefore || $dateOn) ? 'text-sky-400 font-bold bg-sky-500/20' : 'text-slate-500 hover:text-slate-300' }}" title="Filter Tanggal Terdaftar">
                                        <i class="fa-solid fa-caret-down text-xs"></i>
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
