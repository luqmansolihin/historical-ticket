@extends('layouts.app')

@section('title', 'Histori Hotel')

@section('content')
<div x-data="{ 
    showFilterModal: false,
    showModal: false,
    selectedHotel: null,
    dateFrom: '{{ $dateAfter ?? '' }}',
    dateTo: '{{ $dateBefore ?? '' }}',
    payDateFrom: '{{ $payDateAfter ?? '' }}',
    payDateTo: '{{ $payDateBefore ?? '' }}',
    checkInFrom: '{{ $checkInFrom ?? '' }}',
    checkInTo: '{{ $checkInTo ?? '' }}',
    checkOutFrom: '{{ $checkOutFrom ?? '' }}',
    checkOutTo: '{{ $checkOutTo ?? '' }}'
}" class="h-full flex flex-col space-y-3 min-h-0">

    <!-- Top Header & Action Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 shrink-0">
        <div>
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 rounded-lg bg-amber-500/20 border border-amber-500/30 flex items-center justify-center text-amber-400">
                    <i class="fa-solid fa-hotel text-sm"></i>
                </div>
                <h1 class="text-lg sm:text-xl font-bold font-display tracking-tight text-white">Histori Hotel & Penginapan</h1>
            </div>
            <p class="text-xs text-slate-400 mt-0.5">Kelola seluruh histori reservasi hotel, tanggal check-in/check-out, tamu, dan bukti transaksi.</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            @can('create', App\Models\HotelHistory::class)
                <a href="{{ route('hotels.create') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-white bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-400 hover:to-indigo-500 shadow-lg shadow-sky-500/20 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-circle-plus"></i> Tambah Histori Hotel
                </a>
            @endcan

            <a href="{{ route('hotels.export', request()->query()) }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-300 bg-slate-800 hover:bg-slate-700 border border-slate-700 transition-all flex items-center gap-2" title="Ekspor data hotel ke CSV">
                <i class="fa-solid fa-file-csv text-emerald-400"></i> Ekspor CSV
            </a>
        </div>
    </div>

    <!-- Summary Stats Cards Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5 shrink-0">
        <div class="glass-card p-3 rounded-2xl border border-slate-800 flex items-center justify-between">
            <div>
                <div class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">Total Reservasi</div>
                <div class="text-base sm:text-lg font-bold font-mono text-white mt-0.5">{{ number_format($totalHotels) }}</div>
            </div>
            <div class="w-8 h-8 rounded-xl bg-sky-500/10 text-sky-400 flex items-center justify-center">
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

    <!-- Main Table Container -->
    <div class="glass-card rounded-2xl border border-slate-800/80 flex-1 flex flex-col min-h-0 overflow-hidden shadow-2xl">
        
        <!-- Search & Quick Filter Bar -->
        <div class="p-3 border-b border-slate-800/80 bg-slate-900/60 flex items-center justify-between gap-3 shrink-0">
            <form action="{{ route('hotels.index') }}" method="GET" class="flex-1 flex items-center gap-2">
                <div class="relative flex-1">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari kode booking, invoice, nama hotel, tamu, pemesan, pembayar..." class="glass-input w-full pl-8 pr-3 py-1.5 rounded-xl text-xs placeholder:text-slate-500">
                </div>

                <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow-md shadow-sky-500/20 transition-all flex items-center gap-1.5">
                    <i class="fa-solid fa-magnifying-glass"></i> Cari
                </button>

                <button type="button" @click="showFilterModal = true" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 text-xs font-medium flex items-center gap-1.5 transition-all">
                    <i class="fa-solid fa-sliders text-sky-400"></i> Filter
                </button>

                @if(request()->hasAny(['search', 'search_code', 'search_invoice', 'search_hotel', 'search_guest', 'search_booker', 'search_payer', 'status', 'date_after', 'date_before', 'check_in_from', 'check_in_to']))
                    <a href="{{ route('hotels.index') }}" class="px-2.5 py-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 text-xs font-medium transition-all" title="Reset semua filter">
                        <i class="fa-solid fa-rotate-left"></i> Reset
                    </a>
                @endif
            </form>
        </div>

        <!-- Scrollable Table -->
        <div class="flex-1 overflow-auto">
            <table class="w-full text-[11px] text-left border-collapse">
                <thead class="sticky top-0 z-10 bg-slate-900/95 backdrop-blur-md border-b border-slate-800 text-slate-400 uppercase font-mono tracking-wider font-semibold text-[10px]">
                    <tr>
                        <th class="py-2 px-2 border-r border-slate-800/80">Kode Booking</th>
                        <th class="py-2 px-2 border-r border-slate-800/80">Kode Invoice</th>
                        <th class="py-2 px-2 border-r border-slate-800/80">Tgl Booking</th>
                        <th class="py-2 px-2 border-r border-slate-800/80">Nama Hotel</th>
                        <th class="py-2 px-2 border-r border-slate-800/80">Check In</th>
                        <th class="py-2 px-2 border-r border-slate-800/80">Check Out</th>
                        <th class="py-2 px-2 border-r border-slate-800/80 text-center">Malam</th>
                        <th class="py-2 px-2 border-r border-slate-800/80">Nama Tamu</th>
                        <th class="py-2 px-2 border-r border-slate-800/80 text-center">Jml Tamu</th>
                        <th class="py-2 px-2 border-r border-slate-800/80">Pemesan</th>
                        <th class="py-2 px-2 border-r border-slate-800/80">Pembayar</th>
                        <th class="py-2 px-2 border-r border-slate-800/80">Tgl Bayar</th>
                        <th class="py-2 px-2 border-r border-slate-800/80 text-right">Biaya (IDR)</th>
                        <th class="py-2 px-2 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40 font-mono text-[11px]">
                    @include('hotels._rows', ['hotels' => $hotels])
                </tbody>
            </table>
        </div>

        <!-- Pagination Bar -->
        <div class="p-2.5 border-t border-slate-800/80 bg-slate-900/60 shrink-0">
            {{ $hotels->links() }}
        </div>
    </div>

    <!-- Modal Filter Detail -->
    <div x-show="showFilterModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showFilterModal" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity" @click="showFilterModal = false"></div>

            <div x-show="showFilterModal" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="inline-block align-bottom glass-card rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-700/80">
                <form action="{{ route('hotels.index') }}" method="GET">
                    <div class="p-4 border-b border-slate-800 flex items-center justify-between bg-slate-900/80">
                        <div class="flex items-center space-x-2">
                            <i class="fa-solid fa-sliders text-sky-400"></i>
                            <h3 class="text-sm font-bold text-white">Filter Histori Hotel</h3>
                        </div>
                        <button type="button" @click="showFilterModal = false" class="text-slate-400 hover:text-white">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <div class="p-4 space-y-3.5 max-h-[70vh] overflow-y-auto text-xs">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-slate-400 mb-1 font-medium">Kode Booking</label>
                                <input type="text" name="search_code" value="{{ $searchCode ?? '' }}" placeholder="Ex: HTL-001" class="glass-input w-full px-3 py-1.5 rounded-xl">
                            </div>
                            <div>
                                <label class="block text-slate-400 mb-1 font-medium">Kode Invoice</label>
                                <input type="text" name="search_invoice" value="{{ $searchInvoice ?? '' }}" placeholder="Ex: INV-HTL-001" class="glass-input w-full px-3 py-1.5 rounded-xl">
                            </div>
                        </div>

                        <div>
                            <label class="block text-slate-400 mb-1 font-medium">Nama Hotel</label>
                            <input type="text" name="search_hotel" value="{{ $searchHotel ?? '' }}" placeholder="Ex: Aston, Hotel Indonesia" class="glass-input w-full px-3 py-1.5 rounded-xl">
                        </div>

                        <div>
                            <label class="block text-slate-400 mb-1 font-medium">Nama Tamu</label>
                            <input type="text" name="search_guest" value="{{ $searchGuest ?? '' }}" placeholder="Nama tamu yang menginap" class="glass-input w-full px-3 py-1.5 rounded-xl">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-slate-400 mb-1 font-medium">Pemesan (Booked By)</label>
                                <input type="text" name="search_booker" value="{{ $searchBooker ?? '' }}" placeholder="Nama pemesan" class="glass-input w-full px-3 py-1.5 rounded-xl">
                            </div>
                            <div>
                                <label class="block text-slate-400 mb-1 font-medium">Pembayar (Paid By)</label>
                                <input type="text" name="search_payer" value="{{ $searchPayer ?? '' }}" placeholder="Nama pembayar" class="glass-input w-full px-3 py-1.5 rounded-xl">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-slate-400 mb-1 font-medium">Check In Dari</label>
                                <input type="date" name="check_in_from" value="{{ $checkInFrom ?? '' }}" class="glass-input w-full px-3 py-1.5 rounded-xl">
                            </div>
                            <div>
                                <label class="block text-slate-400 mb-1 font-medium">Check In Sampai</label>
                                <input type="date" name="check_in_to" value="{{ $checkInTo ?? '' }}" class="glass-input w-full px-3 py-1.5 rounded-xl">
                            </div>
                        </div>

                        <div>
                            <label class="block text-slate-400 mb-1 font-medium">Status Pembayaran</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($statusOptions as $opt)
                                    <label class="inline-flex items-center px-3 py-1.5 rounded-xl bg-slate-800/80 border border-slate-700/80 cursor-pointer text-slate-300 hover:text-white">
                                        <input type="checkbox" name="status[]" value="{{ $opt }}" {{ in_array($opt, (array)($status ?? [])) ? 'checked' : '' }} class="rounded border-slate-700 text-sky-500 focus:ring-sky-500 bg-slate-900 mr-2">
                                        {{ $opt }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="p-3 border-t border-slate-800 bg-slate-900/80 flex items-center justify-between">
                        <a href="{{ route('hotels.index') }}" class="px-3 py-1.5 rounded-xl bg-slate-800 text-slate-400 hover:text-white text-xs">Reset</a>
                        <button type="submit" class="px-4 py-1.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-semibold text-xs shadow-md shadow-sky-500/20">Terapkan Filter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Preview Detail Voucher Hotel -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showModal" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-950/85 backdrop-blur-md transition-opacity" @click="showModal = false"></div>

            <div x-show="showModal" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="inline-block align-bottom glass-card rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-700/80">
                <template x-if="selectedHotel">
                    <div>
                        <div class="p-4 border-b border-slate-800 bg-slate-900/90 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-xl bg-amber-500/20 border border-amber-500/30 flex items-center justify-center text-amber-400">
                                    <i class="fa-solid fa-hotel text-base"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-white" x-text="selectedHotel.hotel_name"></h3>
                                    <p class="text-[10px] text-slate-400 font-mono">Kode Booking: <span class="text-sky-400" x-text="selectedHotel.booking_code"></span> • Invoice: <span class="text-indigo-300" x-text="selectedHotel.invoice_code"></span></p>
                                </div>
                            </div>
                            <button type="button" @click="showModal = false" class="text-slate-400 hover:text-white">
                                <i class="fa-solid fa-xmark text-lg"></i>
                            </button>
                        </div>

                        <div class="p-5 space-y-4 max-h-[75vh] overflow-y-auto text-xs">
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 p-3.5 rounded-xl bg-slate-900/60 border border-slate-800">
                                <div>
                                    <div class="text-[10px] text-slate-400 uppercase font-mono">Tgl Check-In</div>
                                    <div class="font-bold text-emerald-400 mt-0.5" x-text="selectedHotel.check_in_date"></div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-slate-400 uppercase font-mono">Tgl Check-Out</div>
                                    <div class="font-bold text-rose-400 mt-0.5" x-text="selectedHotel.check_out_date"></div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-slate-400 uppercase font-mono">Durasi</div>
                                    <div class="font-bold text-amber-300 mt-0.5" x-text="selectedHotel.night_count + ' Malam'"></div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="p-3 rounded-xl bg-slate-900/40 border border-slate-800">
                                    <div class="text-[10px] text-slate-400 uppercase font-mono mb-1">Daftar Tamu Menginap</div>
                                    <div class="font-semibold text-slate-200" x-text="selectedHotel.guest_display"></div>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-900/40 border border-slate-800">
                                    <div class="text-[10px] text-slate-400 uppercase font-mono mb-1">Total Biaya</div>
                                    <div class="font-bold text-base text-emerald-400 font-mono" x-text="selectedHotel.amount"></div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 p-3 rounded-xl bg-slate-900/40 border border-slate-800">
                                <div>
                                    <div class="text-[10px] text-slate-400 uppercase font-mono">Booked By</div>
                                    <div class="font-semibold text-indigo-300" x-text="selectedHotel.booked_by"></div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-slate-400 uppercase font-mono">Paid By & Tanggal</div>
                                    <div class="font-semibold text-emerald-300" x-text="selectedHotel.paid_by + ' (' + selectedHotel.payment_date + ')'"></div>
                                </div>
                            </div>

                            <template x-if="selectedHotel.notes">
                                <div class="p-3 rounded-xl bg-slate-900/40 border border-slate-800">
                                    <div class="text-[10px] text-slate-400 uppercase font-mono mb-1">Catatan</div>
                                    <p class="text-slate-300" x-text="selectedHotel.notes"></p>
                                </div>
                            </template>

                            <template x-if="selectedHotel.attachment_url">
                                <div class="p-3 rounded-xl bg-slate-900/40 border border-slate-800 flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <i class="fa-solid fa-paperclip text-sky-400"></i>
                                        <span class="text-slate-300 font-medium">Lampiran Bukti / Invoice</span>
                                    </div>
                                    <a :href="selectedHotel.attachment_url" target="_blank" class="px-3 py-1 rounded-lg bg-sky-500/20 text-sky-300 border border-sky-500/30 hover:bg-sky-500 hover:text-white transition-all text-xs font-semibold">Buka File</a>
                                </div>
                            </template>
                        </div>

                        <div class="p-3 border-t border-slate-800 bg-slate-900/90 flex items-center justify-between">
                            <template x-if="selectedHotel.can_delete">
                                <form :action="selectedHotel.delete_url" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data hotel ini? Data yang dihapus tidak dapat dikembalikan.');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-rose-600/80 hover:bg-rose-500 text-white text-xs font-semibold flex items-center gap-1.5 transition-colors shadow-sm" title="Hapus Histori Hotel Ini">
                                        <i class="fa-solid fa-trash-can"></i> Hapus
                                    </button>
                                </form>
                            </template>

                            <a :href="selectedHotel.pdf_url" target="_blank" class="px-3.5 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs flex items-center gap-1.5 transition-colors shadow-sm">
                                <i class="fa-solid fa-file-pdf"></i> Download PDF Voucher
                            </a>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection
