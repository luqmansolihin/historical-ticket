<?php

namespace App\Http\Controllers;

use App\Models\BookingStatusLog;
use App\Models\TicketDetail;
use App\Models\TicketHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class TicketHistoryController extends Controller
{
    /**
     * Export specified ticket as PDF.
     */
    public function exportPdf(TicketHistory $ticket)
    {
        $ticket->load(['bookerUser', 'payerUser', 'ticketDetail', 'statusLogs']);

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('tickets.pdf', compact('ticket'))
            ->setPaper('a4', 'portrait')
            ->setOption([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ]);

        return $pdf->stream('Boarding-Pass-' . ($ticket->ticket_code ?: 'TICKET') . '.pdf');
    }

    /**
     * Build filtered query based on request parameters.
     */
    private function buildFilteredQuery(Request $request)
    {
        $search = $request->input('search');
        $searchCode = $request->input('search_code');
        $searchInvoice = $request->input('search_invoice');
        $searchOrigin = $request->input('search_origin');
        $searchDestination = $request->input('search_destination');
        $searchPassenger = $request->input('search_passenger');
        $searchBooker = $request->input('search_booker');
        $searchPayer = $request->input('search_payer');
        $searchRoute = $request->input('search_route');
        $searchPerson = $request->input('search_person');
        $transportType = array_values(array_filter((array) $request->input('transport_type', [])));
        $status = array_values(array_filter((array) $request->input('status', [])));
        $amountMin = $request->input('amount_min');
        $amountMax = $request->input('amount_max');
        $amountEq = $request->input('amount_eq');
        $passengerCountMin = $request->input('passenger_count_min');
        $passengerCountMax = $request->input('passenger_count_max');
        $passengerCountEq = $request->input('passenger_count_eq');

        // Date filters for Ticket Date
        $dateAfter = $request->input('date_after', $request->input('date_from'));
        $dateBefore = $request->input('date_before', $request->input('date_to'));
        $dateOn = $request->input('date_on');
        $dateVal = $request->input('date_val');
        $dateMode = $request->input('date_mode');

        if ($dateVal && !$dateAfter && !$dateBefore && !$dateOn) {
            if ($dateMode === 'before') {
                $dateBefore = $dateVal;
            } elseif ($dateMode === 'after') {
                $dateAfter = $dateVal;
            } else {
                $dateOn = $dateVal;
            }
        }

        // Date filters for Payment Date
        $payDateAfter = $request->input('pay_date_after', $request->input('pay_date_from'));
        $payDateBefore = $request->input('pay_date_before', $request->input('pay_date_to'));
        $payDateOn = $request->input('pay_date_on');
        $payDateVal = $request->input('pay_date_val');
        $payDateMode = $request->input('pay_date_mode');

        if ($payDateVal && !$payDateAfter && !$payDateBefore && !$payDateOn) {
            if ($payDateMode === 'before') {
                $payDateBefore = $payDateVal;
            } elseif ($payDateMode === 'after') {
                $payDateAfter = $payDateVal;
            } else {
                $payDateOn = $payDateVal;
            }
        }

        $query = TicketHistory::query()
            ->with(['bookerUser', 'payerUser', 'ticketDetail', 'statusLogs'])
            ->search($search)
            ->filterCode($searchCode)
            ->filterInvoiceCode($searchInvoice)
            ->filterOrigin($searchOrigin)
            ->filterDestination($searchDestination)
            ->filterPassenger($searchPassenger)
            ->filterBooker($searchBooker)
            ->filterPayer($searchPayer)
            ->filterRoute($searchRoute)
            ->filterPerson($searchPerson)
            ->filterTransport($transportType)
            ->filterStatus($status)
            ->filterAmount($amountMin, $amountMax, $amountEq)
            ->filterPassengerCount($passengerCountMin, $passengerCountMax, $passengerCountEq);

        // Apply Ticket Date Filters
        if ($dateOn) {
            $query->whereDate('booking_date', '=', $dateOn);
        } else {
            if ($dateAfter) {
                $query->whereDate('booking_date', '>=', $dateAfter);
            }
            if ($dateBefore) {
                $query->whereDate('booking_date', '<=', $dateBefore);
            }
        }

        // Apply Payment Date Filters
        if ($payDateOn) {
            $query->whereDate('payment_date', '=', $payDateOn);
        } else {
            if ($payDateAfter) {
                $query->whereDate('payment_date', '>=', $payDateAfter);
            }
            if ($payDateBefore) {
                $query->whereDate('payment_date', '<=', $payDateBefore);
            }
        }

        // Multi-column sorting logic
        $sortParam = $request->input('sort');
        $sorts = [];

        $allowedSorts = [
            'ticket_code' => 'booking_code',
            'invoice_code' => 'invoice_code',
            'ticket_date' => 'booking_date',
            'booked_by' => 'booked_by',
            'paid_by' => 'paid_by',
            'amount' => 'amount',
            'payment_date' => 'payment_date',
            'status' => 'status',
        ];

        if (!empty($sortParam)) {
            $pairs = explode(',', $sortParam);
            foreach ($pairs as $pair) {
                $parts = explode(':', trim($pair));
                if (count($parts) === 2) {
                    $col = trim($parts[0]);
                    $dir = strtolower(trim($parts[1])) === 'asc' ? 'asc' : 'desc';
                    if (array_key_exists($col, $allowedSorts)) {
                        $sorts[] = ['col' => $col, 'dir' => $dir];
                    }
                }
            }
        } elseif ($request->filled('sort_by')) {
            $col = $request->input('sort_by');
            $dir = strtolower($request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
            if (array_key_exists($col, $allowedSorts)) {
                $sorts[] = ['col' => $col, 'dir' => $dir];
            }
        }

        if (!empty($sorts)) {
            foreach ($sorts as $s) {
                $query->orderBy($allowedSorts[$s['col']], $s['dir']);
            }
            $query->orderBy('id', 'desc');
        } else {
            $query->orderBy('id', 'desc');
        }

        return [
            'query' => $query,
            'params' => [
                'search' => $search,
                'searchCode' => $searchCode,
                'searchInvoice' => $searchInvoice,
                'searchOrigin' => $searchOrigin,
                'searchDestination' => $searchDestination,
                'searchPassenger' => $searchPassenger,
                'searchBooker' => $searchBooker,
                'searchPayer' => $searchPayer,
                'searchRoute' => $searchRoute,
                'searchPerson' => $searchPerson,
                'transportType' => $transportType,
                'status' => $status,
                'dateAfter' => $dateAfter,
                'dateBefore' => $dateBefore,
                'dateOn' => $dateOn,
                'payDateAfter' => $payDateAfter,
                'payDateBefore' => $payDateBefore,
                'payDateOn' => $payDateOn,
                'amountMin' => $amountMin,
                'amountMax' => $amountMax,
                'amountEq' => $amountEq,
                'passengerCountMin' => $passengerCountMin,
                'passengerCountMax' => $passengerCountMax,
                'passengerCountEq' => $passengerCountEq,
                'sorts' => $sorts,
                'sortParam' => $sortParam,
                'sortBy' => !empty($sorts) ? $sorts[0]['col'] : null,
                'sortDir' => !empty($sorts) ? $sorts[0]['dir'] : 'desc',
            ]
        ];
    }

    /**
     * Display a listing of historical tickets with search, filtering, and summary stats.
     */
    public function index(Request $request)
    {
        $filtered = $this->buildFilteredQuery($request);
        $query = $filtered['query'];
        $params = $filtered['params'];

        // Summary statistics
        $statsQuery = clone $query;
        $totalTickets = $statsQuery->count();
        $totalAmount = (float) $statsQuery->sum('amount');
        $totalLunas = (clone $statsQuery)->where('status', 'Lunas')->count();
        $totalBelumBayar = (clone $statsQuery)->where('status', 'Belum Bayar')->count();
        $totalDibatalkan = (clone $statsQuery)->where('status', 'Dibatalkan')->count();

        $tickets = $query->paginate(25)->withQueryString();

        $transportOptions = ['Pesawat', 'Kereta Api', 'Bus', 'Travel', 'Kapal Laut', 'Mobil / Rental'];
        $statusOptions = ['Lunas', 'Belum Bayar', 'Dibatalkan'];

        if (!$request->header('X-SPA-REQUEST') && ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest')) {
            return response()->json([
                'html' => view('tickets._rows', compact('tickets'))->render(),
                'next_page_url' => $tickets->nextPageUrl(),
                'has_more' => $tickets->hasMorePages(),
                'total' => $tickets->total(),
            ]);
        }

        return view('tickets.index', array_merge(
            $params,
            compact(
                'tickets',
                'totalTickets',
                'totalAmount',
                'totalLunas',
                'totalBelumBayar',
                'totalDibatalkan',
                'transportOptions',
                'statusOptions'
            )
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
            'ticket_code' => 'nullable|string|max:50|unique:booking_histories,booking_code',
            'invoice_code' => 'required|string|max:100|unique:booking_histories,invoice_code',
            'ticket_date' => 'required|date',
            'origin' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'transport_type' => 'required|string|max:100',
            'passenger_names' => 'required|array|min:1',
            'passenger_names.*' => 'required|string|max:255',
            'booked_by' => 'required|string|max:255',
            'booked_by_user_id' => 'nullable|exists:users,id',
            'paid_by' => 'nullable|string|max:255',
            'paid_by_user_id' => 'nullable|exists:users,id',
            'payment_date' => 'nullable|date',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|string|max:50',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'invoice_code.required' => 'Kode invoice wajib diisi.',
            'invoice_code.unique' => 'Kode invoice sudah digunakan.',
            'passenger_names.required' => 'Nama penumpang wajib diisi minimal 1 orang.',
            'passenger_names.*.required' => 'Nama penumpang tidak boleh kosong.',
            'booked_by.required' => 'Nama pemesan wajib diisi.',
        ]);

        $names = array_values(array_filter(array_map('trim', $validated['passenger_names'])));
        $passengerName = implode(', ', $names);

        if (empty($validated['booked_by'])) {
            $validated['booked_by'] = Auth::user()->name;
        }
        $validated['booked_by_user_id'] = Auth::id();

        if (empty($validated['paid_by'])) {
            $validated['paid_by'] = '-';
        }

        if ($validated['status'] === 'Lunas') {
            $validated['paid_by'] = Auth::user()->name;
            $validated['paid_by_user_id'] = Auth::id();
            if (empty($validated['payment_date'])) {
                $validated['payment_date'] = now()->format('Y-m-d');
            }
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('tickets', 'public');
        }

        $headerData = [
            'booking_type' => 'ticket',
            'booking_code' => $validated['ticket_code'] ?? null,
            'invoice_code' => $validated['invoice_code'],
            'booking_date' => $validated['ticket_date'],
            'booked_by' => $validated['booked_by'],
            'booked_by_user_id' => $validated['booked_by_user_id'],
            'paid_by' => $validated['paid_by'],
            'paid_by_user_id' => $validated['paid_by_user_id'] ?? null,
            'payment_date' => $validated['payment_date'] ?? null,
            'amount' => $validated['amount'],
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
            'attachment_path' => $attachmentPath,
        ];

        $newTicket = TicketHistory::create($headerData);

        TicketDetail::create([
            'booking_history_id' => $newTicket->id,
            'transport_type' => $validated['transport_type'],
            'origin' => $validated['origin'],
            'destination' => $validated['destination'],
            'passenger_name' => $passengerName,
        ]);

        $creatorName = Auth::user()->name ?? 'System';
        $creatorRole = ucfirst(Auth::user()->role ?? 'user');
        $creatorId = Auth::id();

        BookingStatusLog::create([
            'booking_history_id' => $newTicket->id,
            'user_id' => $creatorId,
            'user_name' => $creatorName,
            'user_role' => Auth::user()->role ?? 'user',
            'from_status' => null,
            'to_status' => $newTicket->status,
            'notes' => 'Tiket baru dibuat oleh ' . $creatorName . ' (ID: #' . $creatorId . ' • ' . $creatorRole . ') atas nama ' . $newTicket->booked_by . ' dengan status ' . $newTicket->status . '.',
        ]);

        return redirect()->route('tickets.edit', $newTicket->id)
            ->with('success', 'Tiket histori dengan ' . count($names) . ' penumpang berhasil ditambahkan!');
    }

    /**
     * Display the specified ticket details.
     */
    public function show(TicketHistory $ticket)
    {
        $ticket->load(['bookerUser', 'payerUser', 'ticketDetail', 'statusLogs']);

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

        $ticket->load(['bookerUser', 'payerUser', 'ticketDetail', 'statusLogs']);

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

        if (!Auth::user()->isAdmin() && $ticket->status === 'Lunas') {
            $request->merge([
                'ticket_code' => $request->input('ticket_code', $ticket->ticket_code),
                'invoice_code' => $request->input('invoice_code', $ticket->invoice_code),
                'ticket_date' => $request->input('ticket_date', $ticket->ticket_date ? $ticket->ticket_date->format('Y-m-d') : null),
                'origin' => $request->input('origin', $ticket->origin),
                'destination' => $request->input('destination', $ticket->destination),
                'transport_type' => $request->input('transport_type', $ticket->transport_type),
                'passenger_names' => $request->input('passenger_names', $ticket->passengers_list ?: [$ticket->passenger_name]),
                'booked_by' => $request->input('booked_by', $ticket->booked_by),
                'booked_by_user_id' => $request->input('booked_by_user_id', $ticket->booked_by_user_id),
                'amount' => $request->input('amount', $ticket->amount),
                'paid_by' => $request->input('paid_by', $ticket->paid_by ?: Auth::user()->name),
                'paid_by_user_id' => $request->input('paid_by_user_id', $ticket->paid_by_user_id ?: Auth::id()),
            ]);
        }

        $validated = $request->validate([
            'ticket_code' => 'nullable|string|max:50|unique:booking_histories,booking_code,' . $ticket->id,
            'invoice_code' => 'required|string|max:100|unique:booking_histories,invoice_code,' . $ticket->id,
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
        ], [
            'invoice_code.required' => 'Kode invoice wajib diisi.',
            'invoice_code.unique' => 'Kode invoice sudah digunakan.',
            'passenger_names.required' => 'Nama penumpang wajib diisi minimal 1 orang.',
            'passenger_names.*.required' => 'Nama penumpang tidak boleh kosong.',
            'booked_by.required' => 'Nama pemesan wajib diisi.',
        ]);

        $names = array_values(array_filter(array_map('trim', $validated['passenger_names'])));
        $passengerName = implode(', ', $names);

        if (!Auth::user()->isAdmin()) {
            $validated['booked_by_user_id'] = $ticket->booked_by_user_id ?: Auth::id();
        }

        if (!Auth::user()->isAdmin() && ($AuthUser = Auth::user()) && ($AuthUser->isBooker() || $AuthUser->isPayer())) {
            if ($ticket->status === 'Dibatalkan') {
                return redirect()->route('tickets.show', $ticket->id)
                    ->with('error', 'Tiket yang berstatus Dibatalkan telah dikunci dan tidak dapat diubah kembali.');
            }

            if ($ticket->status === 'Lunas') {
                $validated['ticket_code'] = $ticket->ticket_code;
                $validated['ticket_date'] = $ticket->ticket_date->format('Y-m-d');
                $validated['origin'] = $ticket->origin;
                $validated['destination'] = $ticket->destination;
                $validated['transport_type'] = $ticket->transport_type;
                $validated['passenger_name'] = $ticket->passenger_name;
                $validated['booked_by'] = $ticket->booked_by;
                $validated['booked_by_user_id'] = $ticket->booked_by_user_id;
                $validated['amount'] = $ticket->amount;
                $validated['notes'] = $ticket->notes;
                $validated['paid_by'] = $ticket->paid_by ?: Auth::user()->name;
                $validated['paid_by_user_id'] = $ticket->paid_by_user_id ?: Auth::id();

                if ($request->input('status') === 'Dibatalkan') {
                    $validated['status'] = 'Dibatalkan';
                } else {
                    $validated['status'] = 'Lunas';
                }
            } else {
                if ($validated['status'] === 'Lunas') {
                    $validated['paid_by'] = Auth::user()->name;
                    $validated['paid_by_user_id'] = Auth::id();
                    if (empty($validated['payment_date'])) {
                        $validated['payment_date'] = now()->format('Y-m-d');
                    }
                } elseif ($validated['status'] === 'Dibatalkan') {
                    $validated['status'] = 'Dibatalkan';
                } else {
                    $validated['status'] = 'Belum Bayar';
                    $validated['paid_by'] = $ticket->paid_by ?: '-';
                    $validated['paid_by_user_id'] = $ticket->paid_by_user_id;
                    $validated['payment_date'] = null;
                }
            }
        }

        if ($validated['status'] === 'Lunas') {
            if (empty($validated['paid_by_user_id']) || empty($validated['paid_by']) || $validated['paid_by'] === '-') {
                $validated['paid_by'] = Auth::user()->name;
                $validated['paid_by_user_id'] = Auth::id();
            }
        }

        $oldStatus = $ticket->status;
        $newStatus = $validated['status'];

        if ($request->hasFile('attachment')) {
            if ($ticket->attachment_path && Storage::disk('public')->exists($ticket->attachment_path)) {
                Storage::disk('public')->delete($ticket->attachment_path);
            }
            $path = $request->file('attachment')->store('tickets', 'public');
            $validated['attachment_path'] = $path;
        }

        $headerUpdate = [
            'booking_code' => $validated['ticket_code'] ?? null,
            'invoice_code' => $validated['invoice_code'],
            'booking_date' => $validated['ticket_date'],
            'booked_by' => $validated['booked_by'],
            'booked_by_user_id' => $validated['booked_by_user_id'] ?? null,
            'paid_by' => $validated['paid_by'],
            'paid_by_user_id' => $validated['paid_by_user_id'] ?? null,
            'payment_date' => $validated['payment_date'] ?? null,
            'amount' => $validated['amount'],
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ];
        if (isset($validated['attachment_path'])) {
            $headerUpdate['attachment_path'] = $validated['attachment_path'];
        }

        $ticket->update($headerUpdate);

        $ticket->ticketDetail()->updateOrCreate(
            ['booking_history_id' => $ticket->id],
            [
                'transport_type' => $validated['transport_type'],
                'origin' => $validated['origin'],
                'destination' => $validated['destination'],
                'passenger_name' => $passengerName,
            ]
        );

        if ($oldStatus !== $newStatus) {
            $logNotes = match ($newStatus) {
                'Lunas' => 'Status pembayaran diperbarui menjadi Lunas.',
                'Dibatalkan' => 'Tiket dibatalkan oleh ' . (Auth::user()->name ?? 'User') . ' (' . ucfirst(Auth::user()->role ?? 'user') . ').',
                default => 'Status tiket diubah dari ' . $oldStatus . ' menjadi ' . $newStatus . '.',
            };

            BookingStatusLog::create([
                'booking_history_id' => $ticket->id,
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name ?? 'System',
                'user_role' => Auth::user()->role ?? 'user',
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'notes' => $logNotes,
            ]);
        }

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

        $ticket->statusLogs()->delete();
        $ticket->delete();

        return redirect()->route('tickets.index')
            ->with('success', 'Tiket histori berhasil dihapus.');
    }

    /**
     * Export filtered ticket list to CSV file.
     */
    public function exportCsv(Request $request)
    {
        $filtered = $this->buildFilteredQuery($request);
        $query = $filtered['query'];
        $tickets = $query->orderBy('booking_date', 'desc')->get();

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
                'Kode Invoice',
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
                    $t->invoice_code,
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
