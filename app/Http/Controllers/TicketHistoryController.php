<?php

namespace App\Http\Controllers;

use App\Models\TicketHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TicketHistoryController extends Controller
{
    /**
     * Display a listing of historical tickets with search, filtering, and summary stats.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $transportType = $request->input('transport_type');
        $status = $request->input('status');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = TicketHistory::query()
            ->search($search)
            ->filterTransport($transportType)
            ->filterStatus($status);

        if ($dateFrom) {
            $query->whereDate('ticket_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('ticket_date', '<=', $dateTo);
        }

        // Summary statistics (calculated from filtered records)
        $statsQuery = clone $query;
        $totalTickets = $statsQuery->count();
        $totalAmount = (float) $statsQuery->sum('amount');
        $totalLunas = (clone $statsQuery)->where('status', 'Lunas')->count();
        $totalReimburse = (clone $statsQuery)->where('status', 'Reimburse')->count();
        $totalBelumBayar = (clone $statsQuery)->where('status', 'Belum Bayar')->count();

        $tickets = $query->orderBy('ticket_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        $transportOptions = ['Pesawat', 'Kereta Api', 'Bus', 'Travel', 'Kapal Laut', 'Mobil / Rental'];
        $statusOptions = ['Lunas', 'Belum Bayar', 'Reimburse', 'Dibatalkan'];

        return view('tickets.index', compact(
            'tickets',
            'search',
            'transportType',
            'status',
            'dateFrom',
            'dateTo',
            'totalTickets',
            'totalAmount',
            'totalLunas',
            'totalReimburse',
            'totalBelumBayar',
            'transportOptions',
            'statusOptions'
        ));
    }

    /**
     * Show the form for creating a new ticket history record.
     */
    public function create()
    {
        $transportOptions = ['Pesawat', 'Kereta Api', 'Bus', 'Travel', 'Kapal Laut', 'Mobil / Rental'];
        $statusOptions = ['Lunas', 'Belum Bayar', 'Reimburse', 'Dibatalkan'];

        return view('tickets.create', compact('transportOptions', 'statusOptions'));
    }

    /**
     * Store a newly created ticket history record in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ticket_code' => 'nullable|string|max:50|unique:ticket_histories,ticket_code',
            'ticket_date' => 'required|date',
            'origin' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'transport_type' => 'required|string|max:100',
            'passenger_name' => 'required|string|max:255',
            'booked_by' => 'required|string|max:255',
            'paid_by' => 'required|string|max:255',
            'payment_date' => 'nullable|date',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|string|max:50',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Auto-generate ticket code if not specified
        if (empty($validated['ticket_code'])) {
            $validated['ticket_code'] = 'TCK-' . strtoupper(Str::random(6));
        }

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('tickets', 'public');
            $validated['attachment_path'] = $path;
        }

        TicketHistory::create($validated);

        return redirect()->route('tickets.index')
            ->with('success', 'Tiket histori berhasil ditambahkan!');
    }

    /**
     * Display the specified ticket details (Boarding Pass / E-Ticket View).
     */
    public function show(TicketHistory $ticket)
    {
        if (request()->wantsJson()) {
            return response()->json($ticket);
        }

        return view('tickets.show', compact('ticket'));
    }

    /**
     * Show the form for editing the specified ticket record.
     */
    public function edit(TicketHistory $ticket)
    {
        $transportOptions = ['Pesawat', 'Kereta Api', 'Bus', 'Travel', 'Kapal Laut', 'Mobil / Rental'];
        $statusOptions = ['Lunas', 'Belum Bayar', 'Reimburse', 'Dibatalkan'];

        return view('tickets.edit', compact('ticket', 'transportOptions', 'statusOptions'));
    }

    /**
     * Update the specified ticket record in storage.
     */
    public function update(Request $request, TicketHistory $ticket)
    {
        $validated = $request->validate([
            'ticket_code' => 'required|string|max:50|unique:ticket_histories,ticket_code,' . $ticket->id,
            'ticket_date' => 'required|date',
            'origin' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'transport_type' => 'required|string|max:100',
            'passenger_name' => 'required|string|max:255',
            'booked_by' => 'required|string|max:255',
            'paid_by' => 'required|string|max:255',
            'payment_date' => 'nullable|date',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|string|max:50',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('attachment')) {
            if ($ticket->attachment_path && Storage::disk('public')->exists($ticket->attachment_path)) {
                Storage::disk('public')->delete($ticket->attachment_path);
            }
            $path = $request->file('attachment')->store('tickets', 'public');
            $validated['attachment_path'] = $path;
        }

        $ticket->update($validated);

        return redirect()->route('tickets.index')
            ->with('success', 'Tiket histori berhasil diperbarui!');
    }

    /**
     * Remove the specified ticket record from storage.
     */
    public function destroy(TicketHistory $ticket)
    {
        if ($ticket->attachment_path && Storage::disk('public')->exists($ticket->attachment_path)) {
            Storage::disk('public')->delete($ticket->attachment_path);
        }

        $ticket->delete();

        return redirect()->route('tickets.index')
            ->with('success', 'Tiket histori berhasil dihapus.');
    }

    /**
     * Export filtered ticket list to CSV file.
     */
    public function exportCsv(Request $request)
    {
        $search = $request->input('search');
        $transportType = $request->input('transport_type');
        $status = $request->input('status');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = TicketHistory::query()
            ->search($search)
            ->filterTransport($transportType)
            ->filterStatus($status);

        if ($dateFrom) {
            $query->whereDate('ticket_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('ticket_date', '<=', $dateTo);
        }

        $tickets = $query->orderBy('ticket_date', 'desc')->get();

        $filename = "histori_tiket_" . date('Ymd_His') . ".csv";

        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($tickets) {
            $file = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fputs($file, "\xEF\xBB\xBF");

            // CSV Column Headers
            fputcsv($file, [
                'Kode Tiket',
                'Tanggal Tiket',
                'Dari (Origin)',
                'Ke (Destination)',
                'Jenis Transportasi',
                'Nama Penumpang',
                'Siapa Booking (Booked By)',
                'Siapa Bayar (Paid By)',
                'Tanggal Bayar',
                'Harga Tiket (IDR)',
                'Status Pembayaran',
                'Catatan'
            ]);

            foreach ($tickets as $t) {
                fputcsv($file, [
                    $t->ticket_code,
                    $t->ticket_date ? $t->ticket_date->format('Y-m-d') : '',
                    $t->origin,
                    $t->destination,
                    $t->transport_type,
                    $t->passenger_name,
                    $t->booked_by,
                    $t->paid_by,
                    $t->payment_date ? $t->payment_date->format('Y-m-d') : '-',
                    $t->amount,
                    $t->status,
                    $t->notes ?? '-'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
