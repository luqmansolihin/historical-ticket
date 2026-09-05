@extends('layouts.app')

@section('title', 'Daftar List Akun - Management Users')

@section('content')
<div x-data="{
        nextPageUrl: '{{ $users->nextPageUrl() }}',
        loading: false,
        hasMore: {{ $users->hasMorePages() ? 'true' : 'false' }},
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
                const tbody = this.$refs.usersBody;
                if (tbody && data.html) {
                    tbody.insertAdjacentHTML('beforeend', data.html);
                }
                this.nextPageUrl = data.next_page_url;
                this.hasMore = data.has_more;
                this.loading = false;
            })
            .catch(err => {
                console.error(err);
                this.loading = false;
            });
        },
        onScroll(e) {
            const el = e.target;
            if (el.scrollHeight - el.scrollTop - el.clientHeight < 120) {
                this.loadMore();
            }
        }
    }" class="flex-1 flex flex-col min-h-0 h-full overflow-hidden">

    <!-- ERP Data Table Container & Column Header Filters -->
    <div x-data="{ openPop: null }" class="glass-card rounded-2xl shadow-2xl relative z-10 no-print flex-1 flex flex-col min-h-0 h-full overflow-hidden">
        <!-- ERP Data Grid Action Toolbar -->
        <div class="px-3 py-1.5 bg-slate-900/90 border-b border-slate-800/80 flex items-center justify-between gap-2 shrink-0">
            <div class="text-xs text-slate-400 font-mono hidden sm:block">
                <i class="fa-solid fa-mouse-pointer text-sky-400 mr-1"></i> Double klik baris tabel untuk edit data akun
            </div>

            <div class="flex items-center gap-2 ml-auto">
                @if($search || $searchName || $searchEmail || $roleFilter || $dateAfter || $dateBefore || $dateOn)
                    <a href="{{ route('users.index') }}" class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-rose-300 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 transition-all shadow-sm" title="Reset Semua Filter">
                        <i class="fa-solid fa-rotate-left mr-1.5 text-xs"></i> Reset Filter
                    </a>
                @endif

                <a href="{{ route('users.create') }}" class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold text-white bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-400 hover:to-indigo-500 shadow-md shadow-sky-500/20 transition-all active:scale-95">
                    <i class="fa-solid fa-user-plus mr-1.5"></i> Tambah Akun Baru
                </a>
            </div>
        </div>

        <form action="{{ route('users.index') }}" method="GET" class="flex-1 flex flex-col min-h-0 h-full overflow-hidden justify-between">
            <div class="overflow-auto flex-1 min-h-0" @scroll.passive="onScroll($event)">
                <table class="w-full text-left text-[9.5px] leading-tight text-slate-300 whitespace-nowrap border-collapse">
                    <thead class="bg-slate-900/95 text-[9px] uppercase font-bold text-slate-400 tracking-tight border-b border-slate-800 whitespace-nowrap sticky top-0 z-20 backdrop-blur-md">
                        <tr>
                            <!-- 1. ID & Nama Pengguna -->
                            <th class="py-1 px-2 whitespace-nowrap relative border-r border-slate-800/60" @click.outside="if (openPop === 'name') openPop = null">
                                <div class="flex items-center gap-1.5 justify-between">
                                    <span>Nama Pengguna</span>
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
                                    <span>Alamat Email</span>
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
                                    <span>Role Akses</span>
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
                                                <span>{{ $roleOpt === 'booker' ? 'Booker & Payer' : ucfirst($roleOpt) }}</span>
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
                                    <span>Tgl Terdaftar</span>
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
                    <tbody x-ref="usersBody" class="divide-y divide-slate-800/60 whitespace-nowrap font-sans">
                        @include('users._rows', ['users' => $users])
                    </tbody>
                </table>
            </div>

            <div class="p-2 bg-slate-900/90 border-t border-slate-800 shrink-0 text-center text-xs font-mono">
                <template x-if="loading">
                    <span class="text-sky-400 font-semibold inline-flex items-center justify-center gap-2">
                        <i class="fa-solid fa-circle-notch fa-spin text-sm"></i> Memuat akun berikutnya...
                    </span>
                </template>
                <template x-if="!loading && hasMore">
                    <button type="button" @click="loadMore()" class="text-slate-400 hover:text-sky-400 transition-colors">
                        <i class="fa-solid fa-angles-down mr-1"></i> Scroll ke bawah atau klik di sini untuk memuat data lebih banyak
                    </button>
                </template>
                <template x-if="!loading && !hasMore">
                    <span class="text-slate-500">
                        Semua data akun telah ditampilkan (Total: <strong class="text-slate-300">{{ $users->total() }}</strong> akun)
                    </span>
                </template>
            </div>
        </form>
    </div>
</div>
@endsection
