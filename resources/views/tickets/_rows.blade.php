@forelse($tickets as $ticket)
    @can('update', $ticket)
        <tr @dblclick="window.location.href = '{{ route('tickets.edit', $ticket->id) }}'"
            class="hover:bg-sky-950/40 cursor-pointer transition-colors group whitespace-nowrap border-b border-slate-800/40 select-none"
            title="Double klik untuk mengedit data tiket {{ $ticket->ticket_code }}">
    @else
        <tr @dblclick="selectedTicket = {{ json_encode([
                'ticket_code' => $ticket->ticket_code,
                'ticket_date' => $ticket->ticket_date->format('d M Y'),
                'origin' => $ticket->origin,
                'destination' => $ticket->destination,
                'transport_type' => $ticket->transport_type,
                'passenger_display' => implode(', ', $ticket->passengers_list) ?: $ticket->passenger_name,
                'passengers_list' => $ticket->passengers_list,
                'passenger_count' => $ticket->passenger_count,
                'booked_by' => $ticket->booked_by,
                'paid_by' => $ticket->paid_by,
                'payment_date' => $ticket->payment_date ? $ticket->payment_date->format('d M Y') : '-',
                'amount' => $ticket->formatted_amount,
                'status' => $ticket->status,
                'status_badge' => $ticket->status_badge_class,
                'notes' => $ticket->notes ?? '-',
                'attachment_url' => $ticket->attachment_path ? asset('storage/' . $ticket->attachment_path) : null,
                'pdf_url' => route('tickets.pdf', $ticket->id),
                'status_logs' => $ticket->statusLogs->map(fn($log) => [
                    'to_status' => $log->to_status,
                    'from_status' => $log->from_status,
                    'user_name' => $log->user_name,
                    'user_role' => $log->display_role,
                    'notes' => $log->notes,
                    'date' => $log->created_at->format('d M Y, H:i'),
                    'badge' => $log->status_badge_class,
                ])
            ]) }}; showModal = true"
            class="hover:bg-sky-950/40 cursor-pointer transition-colors group whitespace-nowrap border-b border-slate-800/40 select-none"
            title="Double klik untuk melihat Boarding Pass {{ $ticket->ticket_code }}">
    @endcan
        <!-- 1. Kode Tiket -->
        <td class="py-0.5 px-2 font-mono font-semibold text-sky-400 whitespace-nowrap border-r border-slate-800/40">
            {{ $ticket->ticket_code }}
        </td>

        <!-- 2. Tgl Tiket -->
        <td class="py-0.5 px-2 whitespace-nowrap font-medium text-slate-300 border-r border-slate-800/40">
            {{ $ticket->ticket_date->format('d/m/Y') }}
        </td>

        <!-- 3. Asal -->
        <td class="py-0.5 px-2 whitespace-nowrap font-medium text-slate-200 border-r border-slate-800/40">
            {{ $ticket->origin }}
        </td>

        <!-- 4. Tujuan -->
        <td class="py-0.5 px-2 whitespace-nowrap font-medium text-slate-200 border-r border-slate-800/40">
            {{ $ticket->destination }}
        </td>

        <!-- 5. Transportasi -->
        <td class="py-0.5 px-2 whitespace-nowrap border-r border-slate-800/40">
            <span class="inline-block px-1 py-0 rounded text-[8.5px] font-medium bg-slate-800 text-slate-300 border border-slate-700/60 whitespace-nowrap">
                {{ $ticket->transport_type }}
            </span>
        </td>

        <!-- 6. Penumpang -->
        <td class="py-0.5 px-2 whitespace-nowrap text-slate-200 font-medium border-r border-slate-800/40">
            {{ implode(', ', $ticket->passengers_list) ?: $ticket->passenger_name }}
        </td>

        <!-- 7. Jml Penumpang -->
        <td class="py-0.5 px-2 text-center whitespace-nowrap font-mono text-slate-300 font-bold border-r border-slate-800/40">
            {{ $ticket->passenger_count }}
        </td>

        <!-- 8. Pemesan -->
        <td class="py-0.5 px-2 whitespace-nowrap border-r border-slate-800/40">
            <span class="text-indigo-300 font-medium">{{ $ticket->booked_by }}</span>
        </td>

        <!-- 9. Pembayar -->
        <td class="py-0.5 px-2 whitespace-nowrap border-r border-slate-800/40">
            <span class="text-emerald-300 font-medium">{{ $ticket->paid_by }}</span>
        </td>

        <!-- 10. Tgl Bayar -->
        <td class="py-0.5 px-2 whitespace-nowrap text-slate-400 font-mono text-[9px] border-r border-slate-800/40">
            {{ $ticket->payment_date ? $ticket->payment_date->format('d/m/Y') : '-' }}
        </td>

        <!-- 11. Biaya (IDR) -->
        <td class="py-0.5 px-2 text-right whitespace-nowrap font-mono font-bold text-emerald-400 border-r border-slate-800/40">
            {{ $ticket->formatted_amount }}
        </td>

        <!-- 12. Status -->
        <td class="py-0.5 px-2 text-center whitespace-nowrap">
            <span class="px-1.5 py-0 text-[8.5px] font-semibold rounded-full border {{ $ticket->status_badge_class }}">
                {{ $ticket->status }}
            </span>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="12" class="py-12 text-center text-slate-500">
            <div class="w-14 h-14 rounded-full bg-slate-900 border border-slate-800 flex items-center justify-center mx-auto mb-3 text-slate-600">
                <i class="fa-solid fa-table-list text-xl"></i>
            </div>
            <p class="text-sm font-medium text-slate-400">Tidak ada histori tiket ditemukan</p>
            <p class="text-xs text-slate-500 mt-1">Coba sesuaikan kata kunci pencarian atau filter yang Anda pilih.</p>
        </td>
    </tr>
@endforelse
