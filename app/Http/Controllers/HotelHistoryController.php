<?php

namespace App\Http\Controllers;

use App\Models\BookingStatusLog;
use App\Models\HotelDetail;
use App\Models\HotelHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class HotelHistoryController extends Controller
{
    /**
     * Export specified hotel booking as PDF.
     */
    public function exportPdf(HotelHistory $hotel)
    {
        $hotel->load(['bookerUser', 'payerUser', 'hotelDetail', 'statusLogs']);

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('hotels.pdf', compact('hotel'))
            ->setPaper('a4', 'portrait')
            ->setOption([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ]);

        return $pdf->stream('Hotel-Voucher-' . ($hotel->booking_code ?: 'HOTEL') . '.pdf');
    }

    /**
     * Build filtered query based on request parameters.
     */
    private function buildFilteredQuery(Request $request)
    {
        $search = $request->input('search');
        $searchCode = $request->input('search_code');
        $searchInvoice = $request->input('search_invoice');
        $searchHotel = $request->input('search_hotel');
        $searchGuest = $request->input('search_guest');
        $guestCountMin = $request->input('guest_count_min');
        $guestCountMax = $request->input('guest_count_max');
        $guestCountEq = $request->input('guest_count_eq');
        $searchBooker = $request->input('search_booker');
        $searchPayer = $request->input('search_payer');
        $status = array_values(array_filter((array) $request->input('status', [])));
        $amountMin = $request->input('amount_min');
        $amountMax = $request->input('amount_max');
        $amountEq = $request->input('amount_eq');

        // Night Count filters
        $nightCountMin = $request->input('night_count_min');
        $nightCountMax = $request->input('night_count_max');
        $nightCountEq = $request->input('night_count_eq');

        // Date filters for Booking Date
        $dateAfter = $request->input('date_after', $request->input('date_from'));
        $dateBefore = $request->input('date_before', $request->input('date_to'));
        $dateOn = $request->input('date_on');

        // Check-in & Check-out date filters
        $checkInFrom = $request->input('check_in_from');
        $checkInTo = $request->input('check_in_to');
        $checkInOn = $request->input('check_in_on');

        $checkOutFrom = $request->input('check_out_from');
        $checkOutTo = $request->input('check_out_to');
        $checkOutOn = $request->input('check_out_on');

        // Date filters for Payment Date
        $payDateAfter = $request->input('pay_date_after', $request->input('pay_date_from'));
        $payDateBefore = $request->input('pay_date_before', $request->input('pay_date_to'));
        $payDateOn = $request->input('pay_date_on');

        $query = HotelHistory::query()
            ->with(['bookerUser', 'payerUser', 'hotelDetail', 'statusLogs'])
            ->search($search)
            ->filterCode($searchCode)
            ->filterInvoiceCode($searchInvoice)
            ->filterHotelName($searchHotel)
            ->filterGuest($searchGuest)
            ->filterBooker($searchBooker)
            ->filterPayer($searchPayer)
            ->filterStatus($status)
            ->filterAmount($amountMin, $amountMax, $amountEq);

        // Apply Booking Date Filters
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

        // Apply Check-In Date Filters
        if ($checkInOn) {
            $query->whereHas('hotelDetail', fn($h) => $h->whereDate('check_in_date', '=', $checkInOn));
        } else if ($checkInFrom || $checkInTo) {
            $query->whereHas('hotelDetail', function ($h) use ($checkInFrom, $checkInTo) {
                if ($checkInFrom) $h->whereDate('check_in_date', '>=', $checkInFrom);
                if ($checkInTo) $h->whereDate('check_in_date', '<=', $checkInTo);
            });
        }

        // Apply Check-Out Date Filters
        if ($checkOutOn) {
            $query->whereHas('hotelDetail', fn($h) => $h->whereDate('check_out_date', '=', $checkOutOn));
        } else if ($checkOutFrom || $checkOutTo) {
            $query->whereHas('hotelDetail', function ($h) use ($checkOutFrom, $checkOutTo) {
                if ($checkOutFrom) $h->whereDate('check_out_date', '>=', $checkOutFrom);
                if ($checkOutTo) $h->whereDate('check_out_date', '<=', $checkOutTo);
            });
        }

        // Apply Night Count Filters
        if ($nightCountMin || $nightCountMax || $nightCountEq) {
            $expr = "DATEDIFF(hotel_details.check_out_date, hotel_details.check_in_date)";
            $query->whereHas('hotelDetail', function ($h) use ($nightCountMin, $nightCountMax, $nightCountEq, $expr) {
                if ($nightCountEq !== null && $nightCountEq !== '') {
                    $h->whereRaw("{$expr} = ?", [(int) $nightCountEq]);
                } else {
                    if ($nightCountMin !== null && $nightCountMin !== '') {
                        $h->whereRaw("{$expr} >= ?", [(int) $nightCountMin]);
                    }
                    if ($nightCountMax !== null && $nightCountMax !== '') {
                        $h->whereRaw("{$expr} <= ?", [(int) $nightCountMax]);
                    }
                }
            });
        }

        // Apply Guest Count Filters
        if ($guestCountMin || $guestCountMax || $guestCountEq) {
            $expr = "(LENGTH(COALESCE(hotel_details.guest_name, '')) - LENGTH(REPLACE(COALESCE(hotel_details.guest_name, ''), ',', '')) + CASE WHEN COALESCE(hotel_details.guest_name, '') = '' THEN 0 ELSE 1 END)";
            $query->whereHas('hotelDetail', function ($h) use ($guestCountMin, $guestCountMax, $guestCountEq, $expr) {
                if ($guestCountEq !== null && $guestCountEq !== '') {
                    $h->whereRaw("{$expr} = ?", [(int) $guestCountEq]);
                } else {
                    if ($guestCountMin !== null && $guestCountMin !== '') {
                        $h->whereRaw("{$expr} >= ?", [(int) $guestCountMin]);
                    }
                    if ($guestCountMax !== null && $guestCountMax !== '') {
                        $h->whereRaw("{$expr} <= ?", [(int) $guestCountMax]);
                    }
                }
            });
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

        // Sorting
        $sortParam = $request->input('sort');
        $sorts = [];

        $allowedSorts = [
            'booking_code' => 'booking_code',
            'invoice_code' => 'invoice_code',
            'booking_date' => 'booking_date',
            'hotel_name' => 'hotel_name',
            'check_in_date' => 'check_in_date',
            'check_out_date' => 'check_out_date',
            'night_count' => 'night_count',
            'room_count' => 'room_count',
            'guest_name' => 'guest_name',
            'guest_count' => 'guest_count',
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
                $c = $s['col'];
                $d = $s['dir'];
                if (in_array($c, ['hotel_name', 'check_in_date', 'check_out_date', 'night_count', 'room_count', 'guest_name', 'guest_count'])) {
                    $query->join('hotel_details', 'booking_histories.id', '=', 'hotel_details.booking_history_id')
                          ->select('booking_histories.*');
                    if ($c === 'guest_count') {
                        $expr = "(LENGTH(COALESCE(hotel_details.guest_name, '')) - LENGTH(REPLACE(COALESCE(hotel_details.guest_name, ''), ',', '')) + CASE WHEN COALESCE(hotel_details.guest_name, '') = '' THEN 0 ELSE 1 END)";
                        $query->orderByRaw("{$expr} {$d}");
                    } elseif ($c === 'night_count') {
                        $query->orderByRaw("DATEDIFF(hotel_details.check_out_date, hotel_details.check_in_date) {$d}");
                    } else {
                        $query->orderBy("hotel_details.{$c}", $d);
                    }
                } else {
                    $query->orderBy("booking_histories.{$allowedSorts[$c]}", $d);
                }
            }
            $query->orderBy('booking_histories.id', 'desc');
        } else {
            $query->orderBy('booking_histories.id', 'desc');
        }

        return [
            'query' => $query,
            'params' => [
                'search' => $search,
                'searchCode' => $searchCode,
                'searchInvoice' => $searchInvoice,
                'searchHotel' => $searchHotel,
                'searchGuest' => $searchGuest,
                'nightCountMin' => $nightCountMin,
                'nightCountMax' => $nightCountMax,
                'nightCountEq' => $nightCountEq,
                'guestCountMin' => $guestCountMin,
                'guestCountMax' => $guestCountMax,
                'guestCountEq' => $guestCountEq,
                'searchBooker' => $searchBooker,
                'searchPayer' => $searchPayer,
                'status' => $status,
                'dateAfter' => $dateAfter,
                'dateBefore' => $dateBefore,
                'dateOn' => $dateOn,
                'checkInFrom' => $checkInFrom,
                'checkInTo' => $checkInTo,
                'checkInOn' => $checkInOn,
                'checkOutFrom' => $checkOutFrom,
                'checkOutTo' => $checkOutTo,
                'checkOutOn' => $checkOutOn,
                'payDateAfter' => $payDateAfter,
                'payDateBefore' => $payDateBefore,
                'payDateOn' => $payDateOn,
                'amountMin' => $amountMin,
                'amountMax' => $amountMax,
                'amountEq' => $amountEq,
                'sorts' => $sorts,
                'sortParam' => $sortParam,
                'sortBy' => !empty($sorts) ? $sorts[0]['col'] : null,
                'sortDir' => !empty($sorts) ? $sorts[0]['dir'] : 'desc',
            ]
        ];
    }

    /**
     * Display a listing of historical hotel bookings.
     */
    public function index(Request $request)
    {
        $filtered = $this->buildFilteredQuery($request);
        $query = $filtered['query'];
        $params = $filtered['params'];

        $hotels = $query->paginate(25)->withQueryString();

        $statusOptions = ['Lunas', 'Belum Bayar', 'Dibatalkan'];

        if (!$request->header('X-SPA-REQUEST') && ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest')) {
            return response()->json([
                'html' => view('hotels._rows', compact('hotels'))->render(),
                'next_page_url' => $hotels->nextPageUrl(),
                'has_more' => $hotels->hasMorePages(),
                'total' => $hotels->total(),
            ]);
        }

        return view('hotels.index', array_merge(
            $params,
            compact('hotels', 'statusOptions')
        ));
    }

    /**
     * Show the form for creating a new hotel history record.
     */
    public function create()
    {
        Gate::authorize('create', HotelHistory::class);

        $statusOptions = ['Lunas', 'Belum Bayar', 'Dibatalkan'];
        $users = User::orderBy('name')->get();

        return view('hotels.create', compact('statusOptions', 'users'));
    }

    /**
     * Store a newly created hotel history record in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', HotelHistory::class);

        $validated = $request->validate([
            'booking_code' => 'nullable|string|max:50|unique:booking_histories,booking_code',
            'invoice_code' => 'required|string|max:100|unique:booking_histories,invoice_code',
            'booking_date' => 'required|date',
            'hotel_name' => 'required|string|max:255',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after_or_equal:check_in_date',
            'room_count' => 'required|integer|min:1',
            'guest_names' => 'required|array|min:1',
            'guest_names.*' => 'required|string|max:255',
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
            'hotel_name.required' => 'Nama hotel wajib diisi.',
            'check_in_date.required' => 'Tanggal Check In wajib diisi.',
            'check_out_date.required' => 'Tanggal Check Out wajib diisi.',
            'check_out_date.after_or_equal' => 'Tanggal Check Out harus sama atau setelah Check In.',
            'room_count.required' => 'Jumlah kamar wajib diisi.',
            'room_count.min' => 'Jumlah kamar minimal 1.',
            'guest_names.required' => 'Nama tamu yang menginap wajib diisi minimal 1 orang.',
            'guest_names.*.required' => 'Nama tamu tidak boleh kosong.',
            'booked_by.required' => 'Nama pemesan wajib diisi.',
        ]);

        $names = array_values(array_filter(array_map('trim', $validated['guest_names'])));
        $guestName = implode(', ', $names);

        if (empty($validated['booked_by'])) {
            $validated['booked_by'] = Auth::user()->name;
        }
        $validated['booked_by_user_id'] = Auth::id();

        if (!Auth::user()->isAdmin()) {
            $validated['status'] = 'Belum Bayar';
            $validated['paid_by'] = '-';
            $validated['paid_by_user_id'] = null;
            $validated['payment_date'] = null;
        } else {
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
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('hotels', 'public');
        }

        $headerData = [
            'booking_type' => 'hotel',
            'booking_code' => $validated['booking_code'] ?? null,
            'invoice_code' => $validated['invoice_code'],
            'booking_date' => $validated['booking_date'],
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

        $newHotel = HotelHistory::create($headerData);

        HotelDetail::create([
            'booking_history_id' => $newHotel->id,
            'hotel_name' => $validated['hotel_name'],
            'check_in_date' => $validated['check_in_date'],
            'check_out_date' => $validated['check_out_date'],
            'room_count' => $validated['room_count'],
            'guest_name' => $guestName,
        ]);

        $creatorName = Auth::user()->name ?? 'System';
        $creatorRole = ucfirst(Auth::user()->role ?? 'user');
        $creatorId = Auth::id();

        BookingStatusLog::create([
            'booking_history_id' => $newHotel->id,
            'user_id' => $creatorId,
            'user_name' => $creatorName,
            'user_role' => Auth::user()->role ?? 'user',
            'from_status' => null,
            'to_status' => $newHotel->status,
            'notes' => 'Histori hotel baru dibuat oleh ' . $creatorName . ' (ID: #' . $creatorId . ' • ' . $creatorRole . ') atas nama ' . $newHotel->booked_by . ' dengan status ' . $newHotel->status . '.',
        ]);

        return redirect()->route('hotels.edit', $newHotel->id)
            ->with('success', 'Histori hotel dengan ' . count($names) . ' tamu berhasil ditambahkan!');
    }

    /**
     * Display the specified hotel booking details.
     */
    public function show(HotelHistory $hotel)
    {
        $hotel->load(['bookerUser', 'payerUser', 'hotelDetail', 'statusLogs']);

        if (request()->wantsJson()) {
            return response()->json($hotel);
        }

        return view('hotels.show', compact('hotel'));
    }

    /**
     * Show the form for editing the specified hotel record.
     */
    public function edit(HotelHistory $hotel)
    {
        Gate::authorize('update', $hotel);

        $hotel->load(['bookerUser', 'payerUser', 'hotelDetail', 'statusLogs']);

        $statusOptions = ['Lunas', 'Belum Bayar', 'Dibatalkan'];
        $users = User::orderBy('name')->get();

        return view('hotels.edit', compact('hotel', 'statusOptions', 'users'));
    }

    /**
     * Update the specified hotel record in storage.
     */
    public function update(Request $request, HotelHistory $hotel)
    {
        Gate::authorize('update', $hotel);

        if (!Auth::user()->isAdmin() && $hotel->status === 'Lunas') {
            $request->merge([
                'booking_code' => $request->input('booking_code', $hotel->booking_code),
                'invoice_code' => $request->input('invoice_code', $hotel->invoice_code),
                'booking_date' => $request->input('booking_date', $hotel->booking_date ? $hotel->booking_date->format('Y-m-d') : null),
                'hotel_name' => $request->input('hotel_name', $hotel->hotel_name),
                'check_in_date' => $request->input('check_in_date', $hotel->check_in_date ? $hotel->check_in_date->format('Y-m-d') : null),
                'check_out_date' => $request->input('check_out_date', $hotel->check_out_date ? $hotel->check_out_date->format('Y-m-d') : null),
                'room_count' => $request->input('room_count', $hotel->room_count),
                'guest_names' => $request->input('guest_names', $hotel->guests_list ?: [$hotel->guest_name]),
                'booked_by' => $request->input('booked_by', $hotel->booked_by),
                'booked_by_user_id' => $request->input('booked_by_user_id', $hotel->booked_by_user_id),
                'amount' => $request->input('amount', $hotel->amount),
                'paid_by' => $request->input('paid_by', $hotel->paid_by ?: Auth::user()->name),
                'paid_by_user_id' => $request->input('paid_by_user_id', $hotel->paid_by_user_id ?: Auth::id()),
            ]);
        }

        $validated = $request->validate([
            'booking_code' => 'nullable|string|max:50|unique:booking_histories,booking_code,' . $hotel->id,
            'invoice_code' => 'required|string|max:100|unique:booking_histories,invoice_code,' . $hotel->id,
            'booking_date' => 'required|date',
            'hotel_name' => 'required|string|max:255',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after_or_equal:check_in_date',
            'room_count' => 'required|integer|min:1',
            'guest_names' => 'required|array|min:1',
            'guest_names.*' => 'required|string|max:255',
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
            'hotel_name.required' => 'Nama hotel wajib diisi.',
            'check_in_date.required' => 'Tanggal Check In wajib diisi.',
            'check_out_date.required' => 'Tanggal Check Out wajib diisi.',
            'room_count.required' => 'Jumlah kamar wajib diisi.',
            'room_count.min' => 'Jumlah kamar minimal 1.',
            'guest_names.required' => 'Nama tamu yang menginap wajib diisi minimal 1 orang.',
            'booked_by.required' => 'Nama pemesan wajib diisi.',
        ]);

        $names = array_values(array_filter(array_map('trim', $validated['guest_names'])));
        $guestName = implode(', ', $names);

        if (!Auth::user()->isAdmin()) {
            $validated['booked_by_user_id'] = $hotel->booked_by_user_id ?: Auth::id();
        }

        if (!Auth::user()->isAdmin() && ($AuthUser = Auth::user()) && ($AuthUser->isBooker() || $AuthUser->isPayer())) {
            if ($hotel->status === 'Dibatalkan') {
                return redirect()->route('hotels.show', $hotel->id)
                    ->with('error', 'Histori hotel yang berstatus Dibatalkan telah dikunci dan tidak dapat diubah kembali.');
            }

            if ($hotel->status === 'Lunas') {
                $validated['booking_code'] = $hotel->booking_code;
                $validated['booking_date'] = $hotel->booking_date ? $hotel->booking_date->format('Y-m-d') : null;
                $validated['hotel_name'] = $hotel->hotel_name;
                $validated['check_in_date'] = $hotel->check_in_date ? $hotel->check_in_date->format('Y-m-d') : null;
                $validated['check_out_date'] = $hotel->check_out_date ? $hotel->check_out_date->format('Y-m-d') : null;
                $validated['room_count'] = $hotel->room_count;
                $validated['guest_name'] = $hotel->guest_name;
                $validated['booked_by'] = $hotel->booked_by;
                $validated['booked_by_user_id'] = $hotel->booked_by_user_id;
                $validated['amount'] = $hotel->amount;
                $validated['notes'] = $hotel->notes;
                $validated['paid_by'] = $hotel->paid_by ?: Auth::user()->name;
                $validated['paid_by_user_id'] = $hotel->paid_by_user_id ?: Auth::id();

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
                    $validated['paid_by'] = $hotel->paid_by ?: '-';
                    $validated['paid_by_user_id'] = $hotel->paid_by_user_id;
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

        $oldStatus = $hotel->status;
        $newStatus = $validated['status'];

        if ($request->hasFile('attachment')) {
            if ($hotel->attachment_path && Storage::disk('public')->exists($hotel->attachment_path)) {
                Storage::disk('public')->delete($hotel->attachment_path);
            }
            $path = $request->file('attachment')->store('hotels', 'public');
            $validated['attachment_path'] = $path;
        }

        $headerUpdate = [
            'booking_code' => $validated['booking_code'] ?? null,
            'invoice_code' => $validated['invoice_code'],
            'booking_date' => $validated['booking_date'],
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

        $hotel->update($headerUpdate);

        $hotel->hotelDetail()->updateOrCreate(
            ['booking_history_id' => $hotel->id],
            [
                'hotel_name' => $validated['hotel_name'],
                'check_in_date' => $validated['check_in_date'],
                'check_out_date' => $validated['check_out_date'],
                'room_count' => $validated['room_count'],
                'guest_name' => $guestName,
            ]
        );

        if ($oldStatus !== $newStatus) {
            $logNotes = match ($newStatus) {
                'Lunas' => 'Status pembayaran diperbarui menjadi Lunas.',
                'Dibatalkan' => 'Histori hotel dibatalkan oleh ' . (Auth::user()->name ?? 'User') . ' (' . ucfirst(Auth::user()->role ?? 'user') . ').',
                default => 'Status histori hotel diubah dari ' . $oldStatus . ' menjadi ' . $newStatus . '.',
            };

            BookingStatusLog::create([
                'booking_history_id' => $hotel->id,
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name ?? 'System',
                'user_role' => Auth::user()->role ?? 'user',
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'notes' => $logNotes,
            ]);
        }

        return redirect()->route('hotels.index')
            ->with('success', 'Histori hotel berhasil diperbarui!');
    }

    /**
     * Remove the specified hotel history record from storage.
     */
    public function destroy(HotelHistory $hotel)
    {
        Gate::authorize('delete', $hotel);

        if ($hotel->attachment_path && Storage::disk('public')->exists($hotel->attachment_path)) {
            Storage::disk('public')->delete($hotel->attachment_path);
        }

        $hotel->statusLogs()->delete();
        $hotel->delete();

        return redirect()->route('hotels.index')
            ->with('success', 'Histori hotel berhasil dihapus.');
    }

    /**
     * Export filtered hotel list to CSV file.
     */
    public function exportCsv(Request $request)
    {
        $filtered = $this->buildFilteredQuery($request);
        $query = $filtered['query'];
        $hotels = $query->orderBy('booking_date', 'desc')->get();

        $filename = "histori_hotel_" . date('Ymd_His') . ".csv";

        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($hotels) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, [
                'Kode Booking Hotel',
                'Kode Invoice',
                'Tanggal Booking',
                'Nama Hotel',
                'Check In',
                'Check Out',
                'Malam',
                'Jumlah Kamar',
                'Nama Tamu (Menginap)',
                'Pemesan (Booked By)',
                'Penanggung Jawab Biaya (Paid By)',
                'Tanggal Bayar',
                'Biaya Hotel (IDR)',
                'Status Pembayaran',
                'Catatan'
            ]);

            foreach ($hotels as $h) {
                fputcsv($file, [
                    $h->booking_code ?? '-',
                    $h->invoice_code,
                    $h->booking_date ? $h->booking_date->format('Y-m-d') : '',
                    $h->hotel_name,
                    $h->check_in_date ? $h->check_in_date->format('Y-m-d') : '-',
                    $h->check_out_date ? $h->check_out_date->format('Y-m-d') : '-',
                    $h->night_count . ' Malam',
                    $h->room_count . ' Kamar',
                    $h->guest_name,
                    $h->booked_by,
                    $h->paid_by,
                    $h->payment_date ? $h->payment_date->format('Y-m-d') : '-',
                    $h->amount,
                    $h->status,
                    $h->notes ?? '-'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
