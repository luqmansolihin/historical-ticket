@forelse($hotels as $hotel)
    @can('update', $hotel)
        <tr @dblclick="window.location.href = '{{ route('hotels.edit', $hotel->id) }}'"
            class="hover:bg-amber-950/40 cursor-pointer transition-colors group whitespace-nowrap border-b border-slate-800/40 select-none"
            title="Double klik untuk mengedit data hotel {{ $hotel->booking_code ?: '-' }}">
    @else
        <tr @dblclick="selectedHotel = {{ json_encode([
                'id' => $hotel->id,
                'booking_code' => $hotel->booking_code ?: '-',
                'invoice_code' => $hotel->invoice_code ?: '-',
                'booking_date' => $hotel->booking_date ? $hotel->booking_date->format('d M Y') : '-',
                'hotel_name' => $hotel->hotel_name,
                'check_in_date' => $hotel->check_in_date ? $hotel->check_in_date->format('d M Y') : '-',
                'check_out_date' => $hotel->check_out_date ? $hotel->check_out_date->format('d M Y') : '-',
                'night_count' => $hotel->night_count,
                'room_count' => $hotel->room_count,
                'guest_display' => implode(', ', $hotel->guests_list) ?: $hotel->guest_name,
                'guests_list' => $hotel->guests_list,
                'guest_count' => $hotel->guest_count,
                'booked_by' => $hotel->booked_by,
                'paid_by' => $hotel->paid_by,
                'payment_date' => $hotel->payment_date ? $hotel->payment_date->format('d M Y') : '-',
                'amount' => $hotel->formatted_amount,
                'status' => $hotel->status,
                'status_badge' => $hotel->status_badge_class,
                'notes' => $hotel->notes ?? '-',
                'attachment_url' => $hotel->attachment_path ? asset('storage/' . $hotel->attachment_path) : null,
                'pdf_url' => route('hotels.pdf', $hotel->id),
                'delete_url' => route('hotels.destroy', $hotel->id),
                'can_delete' => Auth::user()->can('delete', $hotel),
                'status_logs' => $hotel->statusLogs->map(fn($log) => [
                    'to_status' => $log->to_status,
                    'from_status' => $log->from_status,
                    'user_name' => $log->user_name,
                    'user_role' => ucfirst($log->user_role ?? 'user'),
                    'notes' => $log->notes,
                    'date' => $log->created_at->format('d M Y, H:i'),
                ])
            ]) }}; showModal = true"
            class="hover:bg-amber-950/40 cursor-pointer transition-colors group whitespace-nowrap border-b border-slate-800/40 select-none"
            title="Double klik untuk melihat Voucher Hotel {{ $hotel->booking_code ?: '-' }}">
    @endcan
        <!-- 1. Kode Booking -->
        <td class="py-0.5 px-2 font-mono font-semibold text-amber-400 whitespace-nowrap border-r border-slate-800/40">
            {{ $hotel->booking_code ?: '-' }}
        </td>

        <!-- 2. Kode Invoice -->
        <td class="py-0.5 px-2 font-mono font-semibold text-indigo-300 whitespace-nowrap border-r border-slate-800/40">
            {{ $hotel->invoice_code ?: '-' }}
        </td>

        <!-- 3. Tgl Booking -->
        <td class="py-0.5 px-2 whitespace-nowrap font-medium text-slate-300 border-r border-slate-800/40">
            {{ $hotel->booking_date ? $hotel->booking_date->format('d/m/Y') : '-' }}
        </td>

        <!-- 4. Nama Hotel -->
        <td class="py-0.5 px-2 whitespace-nowrap font-medium text-slate-200 border-r border-slate-800/40">
            {{ $hotel->hotel_name }}
        </td>

        <!-- 5. Check In -->
        <td class="py-0.5 px-2 whitespace-nowrap font-medium text-slate-300 border-r border-slate-800/40">
            {{ $hotel->check_in_date ? $hotel->check_in_date->format('d/m/Y') : '-' }}
        </td>

        <!-- 6. Check Out -->
        <td class="py-0.5 px-2 whitespace-nowrap font-medium text-slate-300 border-r border-slate-800/40">
            {{ $hotel->check_out_date ? $hotel->check_out_date->format('d/m/Y') : '-' }}
        </td>

        <!-- 7. Jml Malam -->
        <td class="py-0.5 px-2 text-center whitespace-nowrap font-mono text-amber-300 font-bold border-r border-slate-800/40">
            {{ $hotel->night_count }}
        </td>

        <!-- 7b. Jml Kamar -->
        <td class="py-0.5 px-2 text-center whitespace-nowrap font-mono text-sky-300 font-bold border-r border-slate-800/40">
            {{ $hotel->room_count }}
        </td>

        <!-- 8. Tamu -->
        <td class="py-0.5 px-2 whitespace-nowrap text-slate-200 font-medium border-r border-slate-800/40">
            {{ implode(', ', $hotel->guests_list) ?: $hotel->guest_name }}
        </td>

        <!-- 9. Jml Tamu -->
        <td class="py-0.5 px-2 text-center whitespace-nowrap font-mono text-slate-300 font-bold border-r border-slate-800/40">
            {{ $hotel->guest_count }}
        </td>

        <!-- 10. Pemesan -->
        <td class="py-0.5 px-2 whitespace-nowrap border-r border-slate-800/40">
            <span class="text-indigo-300 font-medium">{{ $hotel->booked_by }}</span>
        </td>

        <!-- 11. Pembayar -->
        <td class="py-0.5 px-2 whitespace-nowrap border-r border-slate-800/40">
            <span class="text-emerald-300 font-medium">{{ $hotel->paid_by }}</span>
        </td>

        <!-- 12. Tgl Bayar -->
        <td class="py-0.5 px-2 whitespace-nowrap text-slate-400 font-mono text-[9px] border-r border-slate-800/40">
            {{ $hotel->payment_date ? $hotel->payment_date->format('d/m/Y') : '-' }}
        </td>

        <!-- 13. Biaya (IDR) -->
        <td class="py-0.5 px-2 text-right whitespace-nowrap font-mono font-bold text-emerald-400 border-r border-slate-800/40">
            {{ $hotel->formatted_amount }}
        </td>

        <!-- 14. Status -->
        <td class="py-0.5 px-2 text-center whitespace-nowrap">
            <span class="px-1.5 py-0 text-[8.5px] font-semibold rounded-full border {{ $hotel->status_badge_class }}">
                {{ $hotel->status }}
            </span>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="14" class="py-12 text-center text-slate-500">
            <div class="w-14 h-14 rounded-full bg-slate-900 border border-slate-800 flex items-center justify-center mx-auto mb-3 text-slate-600">
                <i class="fa-solid fa-hotel text-xl"></i>
            </div>
            <p class="text-sm font-medium text-slate-400">Tidak ada histori hotel ditemukan</p>
            <p class="text-xs text-slate-500 mt-1">Coba sesuaikan kata kunci pencarian atau filter yang Anda pilih.</p>
        </td>
    </tr>
@endforelse
