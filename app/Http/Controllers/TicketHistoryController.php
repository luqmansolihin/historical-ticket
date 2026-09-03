<?php

namespace App\Http\Controllers;

use App\Models\TicketHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
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
            ->with(['bookerUser', 'payerUser'])
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
        $totalBelumBayar = (clone $statsQuery)->where('status', 'Belum Bayar')->count();

        $tickets = $query->orderBy('ticket_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        $transportOptions = ['Pesawat', 'Kereta Api', 'Bus', 'Travel', 'Kapal Laut', 'Mobil / Rental'];
        $statusOptions = ['Lunas', 'Belum Bayar', 'Dibatalkan'];

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
        Gate::authorize('create', TicketHistory::class);

        $transportOptions = ['Pesawat', 'Kereta Api', 'Bus', 'Travel', 'Kapal Laut', 'Mobil / Rental'];
        $statusOptions = ['Lunas', 'Belum Bayar', 'Dibatalkan'];
        $users = User::orderBy('name')->get();

        return view('tickets.create', compact('transportOptions', 'statusOptions', 'users'));
    }

    /**
     * Store a newly created ticket history record in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', TicketHistory::class);

        $validated = $request->validate([
            'ticket_code' => 'nullable|string|max:50|unique:ticket_histories,ticket_code',
            'ticket_date' => 'required|date',
            'origin' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'transport_type' => 'required|string|max:100',
            'passenger_names' => 'required|array|min:1',
            'passenger_names.*' => 'required|string|max:255',
            'booked_by' => 'nullable|string|max:255',
            'booked_by_user_id' => 'nullable|exists:users,id',
            'paid_by' => 'required|string|max:255',
            'paid_by_user_id' => 'nullable|exists:users,id',
            'payment_date' => 'nullable|date',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|string|max:50',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Process array of passenger names into clean comma-separated string
        $names = array_values(array_filter(array_map('trim', $validated['passenger_names'])));
        $validated['passenger_name'] = implode(', ', $names);
        unset($validated['passenger_names']);

        // Auto-generate ticket code if not specified
        if (empty($validated['ticket_code'])) {
            $validated['ticket_code'] = 'TCK-' . strtoupper(Str::random(6));
        }

        // Automatically bind Booker name and User ID to currently logged in user
        if (empty($validated['booked_by'])) {
            $validated['booked_by'] = Auth::user()->name;
        }
        if (empty($validated['booked_by_user_id'])) {
            $validated['booked_by_user_id'] = Auth::id();
        }

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('tickets', 'public');
            $validated['attachment_path'] = $path;
        }

        TicketHistory::create($validated);

        return redirect()->route('tickets.index')
            ->with('success', 'Tiket histori dengan ' . count($names) . ' penumpang berhasil ditambahkan!');
    }

    /**
     * Display the specified ticket details.
     */
    public function show(TicketHistory $ticket)
    {
        $ticket->load(['bookerUser', 'payerUser']);

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
        Gate::authorize('update', $ticket);

        $transportOptions = ['Pesawat', 'Kereta Api', 'Bus', 'Travel', 'Kapal Laut', 'Mobil / Rental'];
        $statusOptions = ['Lunas', 'Belum Bayar', 'Dibatalkan'];
        $users = User::orderBy('name')->get();

        return view('tickets.edit', compact('ticket', 'transportOptions', 'statusOptions', 'users'));
    }

    /**
     * Update the specified ticket record in storage.
     */
    public function update(Request $request, TicketHistory $ticket)
    {
        Gate::authorize('update', $ticket);

        $validated = $request->validate([
            'ticket_code' => 'required|string|max:50|unique:ticket_histories,ticket_code,' . $ticket->id,
            'ticket_date' => 'required|date',
            'origin' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'transport_type' => 'required|string|max:100',
            'passenger_names' => 'required|array|min:1',
            'passenger_names.*' => 'required|string|max:255',
            'booked_by' => 'required|string|max:255',
            'booked_by_user_id' => 'nullable|exists:users,id',
            'paid_by' => 'required|string|max:255',
            'paid_by_user_id' => 'nullable|exists:users,id',
            'payment_date' => 'nullable|date',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|string|max:50',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $names = array_values(array_filter(array_map('trim', $validated['passenger_names'])));
        $validated['passenger_name'] = implode(', ', $names);
        unset($validated['passenger_names']);

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
        Gate::authorize('delete', $ticket);

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
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, [
                'Kode Tiket',
                'Tanggal Tiket',
                'Dari (Origin)',
                'Ke (Destination)',
                'Jenis Transportasi',
                'Nama Penumpang',
                'Pemesan Tiket (Booked By)',
                'Penanggung Jawab Biaya (Paid By)',
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
