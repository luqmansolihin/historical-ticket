@forelse($expenses as $expense)
    @can('update', $expense)
        <tr @dblclick="window.loadSpaPage('{{ route('expenses.edit', $expense->id) }}')"
            class="hover:bg-emerald-50/70 cursor-pointer transition-colors group whitespace-nowrap border-b border-slate-200/80 select-none"
            title="Double klik untuk mengedit data histori biaya {{ $expense->invoice_code }}">
    @else
        <tr @dblclick="window.loadSpaPage('{{ route('expenses.show', $expense->id) }}')"
            class="hover:bg-emerald-50/70 cursor-pointer transition-colors group whitespace-nowrap border-b border-slate-200/80 select-none"
            title="Double klik untuk melihat detail histori biaya {{ $expense->invoice_code }}">
    @endcan
        <!-- 1. Kode Booking / Ref -->
        <td class="py-0.5 px-2 font-mono font-semibold text-emerald-700 whitespace-nowrap border-r border-slate-200/80">
            {{ $expense->booking_code ?: '-' }}
        </td>

        <!-- 2. Kode Invoice -->
        <td class="py-0.5 px-2 font-mono font-semibold text-indigo-700 whitespace-nowrap border-r border-slate-200/80">
            {{ $expense->invoice_code ?: '-' }}
        </td>

        <!-- 3. Tgl Biaya -->
        <td class="py-0.5 px-2 whitespace-nowrap font-medium text-slate-700 border-r border-slate-200/80">
            {{ $expense->booking_date ? $expense->booking_date->format('d/m/Y') : '-' }}
        </td>

        <!-- 4. Nama / Rincian Biaya -->
        <td class="py-0.5 px-2 whitespace-nowrap font-medium text-slate-800 border-r border-slate-200/80 max-w-xs truncate">
            {{ $expense->expense_name }}
        </td>

        <!-- 5. Pemesan / Pengaju -->
        <td class="py-0.5 px-2 whitespace-nowrap border-r border-slate-200/80">
            <span class="text-indigo-700 font-semibold">{{ $expense->booked_by }}</span>
        </td>

        <!-- 6. Pembayar -->
        <td class="py-0.5 px-2 whitespace-nowrap border-r border-slate-200/80">
            <span class="text-emerald-700 font-semibold">{{ $expense->paid_by }}</span>
        </td>

        <!-- 7. Tgl Bayar -->
        <td class="py-0.5 px-2 whitespace-nowrap text-slate-500 font-mono text-[9px] border-r border-slate-200/80">
            {{ $expense->payment_date ? $expense->payment_date->format('d/m/Y') : '-' }}
        </td>

        <!-- 8. Biaya (IDR) -->
        <td class="py-0.5 px-2 text-right whitespace-nowrap font-mono font-bold text-emerald-700 border-r border-slate-200/80">
            {{ $expense->formatted_amount }}
        </td>

        <!-- 9. Status -->
        <td class="py-0.5 px-2 text-center whitespace-nowrap">
            <span class="px-1.5 py-0 text-[8.5px] font-semibold rounded-full border {{ $expense->status_badge_class }}">
                {{ $expense->status }}
            </span>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="py-12 text-center text-slate-500">
            <div class="w-14 h-14 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center mx-auto mb-3 text-slate-400">
                <i class="fa-solid fa-receipt text-xl"></i>
            </div>
            <p class="text-sm font-medium text-slate-700">Tidak ada histori biaya lain-lain ditemukan</p>
            <p class="text-xs text-slate-500 mt-1">Coba sesuaikan kata kunci pencarian atau filter yang Anda pilih.</p>
        </td>
    </tr>
@endforelse
