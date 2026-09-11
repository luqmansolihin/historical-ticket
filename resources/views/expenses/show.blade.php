@extends('layouts.app')

@section('title', 'Detail Histori Biaya Lain-lain - ' . $expense->invoice_code)

@section('content')
<div class="max-w-4xl mx-auto min-w-0 w-full pb-12">

    <!-- Header & Action Buttons -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('expenses.index') }}" class="text-xs font-medium text-emerald-700 hover:text-emerald-800 inline-flex items-center gap-1.5 mb-2">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Histori Biaya Lain-lain
            </a>
            <h1 class="font-display text-2xl sm:text-3xl font-bold text-slate-900 leading-tight">Detail Histori Biaya Lain-lain</h1>
            <p class="text-slate-600 text-xs sm:text-sm mt-1">Invoice <span class="font-mono text-emerald-700 font-semibold">{{ $expense->invoice_code }}</span></p>
        </div>

        <div class="flex items-center gap-2">
            @can('update', $expense)
                <a href="{{ route('expenses.edit', $expense->id) }}" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-md shadow-emerald-600/20 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-sm"></i> Edit Data
                </a>
            @endcan
        </div>
    </div>

    <!-- Detail Card Container -->
    <div class="glass-card p-6 sm:p-8 rounded-2xl shadow-sm border border-slate-200 bg-white space-y-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-slate-200 pb-6">
            <div>
                <span class="text-xs font-mono font-bold text-emerald-700 uppercase tracking-wider block mb-1">Rincian Transaksi Biaya</span>
                <h2 class="text-xl font-bold text-slate-900">{{ $expense->expense_name }}</h2>
            </div>
            <div class="text-left sm:text-right">
                <span class="text-xs text-slate-500 block mb-1">Status Pembayaran</span>
                <span class="px-3 py-1 text-xs font-semibold rounded-full border inline-block {{ $expense->status_badge_class }}">
                    {{ $expense->status }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 text-xs">
            <div>
                <span class="text-slate-500 block mb-1">Kode Invoice</span>
                <span class="text-sm font-semibold font-mono text-teal-700 block">{{ $expense->invoice_code }}</span>
            </div>

            <div>
                <span class="text-slate-500 block mb-1">Kode Booking / Referensi</span>
                <span class="text-sm font-semibold font-mono text-emerald-700 block">{{ $expense->booking_code ?: '-' }}</span>
            </div>

            <div>
                <span class="text-slate-500 block mb-1">Tanggal Biaya</span>
                <span class="text-sm font-semibold font-mono text-slate-800 block">{{ $expense->booking_date ? $expense->booking_date->format('d M Y') : '-' }}</span>
            </div>

            <div>
                <span class="text-slate-500 block mb-1">Pengaju / Pemesan (Booked By)</span>
                <span class="text-sm font-semibold text-emerald-800 block">{{ $expense->booked_by }}</span>
            </div>

            <div>
                <span class="text-slate-500 block mb-1">Pembayaran Oleh (Paid By)</span>
                <span class="text-sm font-semibold text-emerald-800 block">{{ $expense->paid_by }}</span>
            </div>

            <div>
                <span class="text-slate-500 block mb-1">Tanggal Bayar</span>
                <span class="text-sm font-semibold font-mono text-slate-800 block">{{ $expense->payment_date ? $expense->payment_date->format('d M Y') : '-' }}</span>
            </div>

            <div>
                <span class="text-slate-500 block mb-1">Nominal Biaya</span>
                <span class="text-lg font-bold font-mono text-emerald-700 block">{{ $expense->formatted_amount }}</span>
            </div>
        </div>

        @if($expense->notes)
            <div class="border-t border-slate-200 pt-4">
                <span class="text-xs font-semibold text-slate-500 block mb-1">Catatan Tambahan</span>
                <p class="text-xs text-slate-700 italic bg-slate-50 p-3 rounded-xl border border-slate-200">{{ $expense->notes }}</p>
            </div>
        @endif

        @if($expense->attachment_path)
            <div class="border-t border-slate-200 pt-4">
                <span class="text-xs font-semibold text-slate-500 block mb-2">File Lampiran Bukti / Invoice</span>
                <a href="{{ asset('storage/' . $expense->attachment_path) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-xs font-medium transition-all">
                    <i class="fa-solid fa-paperclip"></i> Lihat & Unduh Lampiran
                </a>
            </div>
        @endif
    </div>

    <!-- Activity Timeline Logs -->
    @if($expense->statusLogs->count() > 0)
        <div class="mt-8 glass-card p-6 rounded-2xl border border-slate-200 bg-white shadow-sm">
            <h3 class="text-xs font-mono font-bold text-emerald-700 uppercase tracking-wider mb-4 flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left"></i> Riwayat Log Status Pembayaran
            </h3>
            <div class="space-y-3">
                @foreach($expense->statusLogs as $log)
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-emerald-700">{{ $log->to_status }}</span>
                            <span class="text-[10px] font-mono text-slate-500">{{ $log->created_at->format('d M Y, H:i') }}</span>
                        </div>
                        <p class="text-slate-700 text-xs mt-1">{{ $log->notes }}</p>
                        <div class="text-[10px] text-slate-500 font-mono mt-1">{{ $log->user_name }} ({{ ucfirst($log->user_role ?? 'user') }})</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
