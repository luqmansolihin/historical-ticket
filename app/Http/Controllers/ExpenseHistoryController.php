<?php

namespace App\Http\Controllers;

use App\Models\BookingStatusLog;
use App\Models\ExpenseDetail;
use App\Models\ExpenseHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ExpenseHistoryController extends Controller
{
    /**
     * Build filtered query based on request parameters.
     */
    private function buildFilteredQuery(Request $request)
    {
        $search = $request->input('search');
        $searchCode = $request->input('search_code');
        $searchInvoice = $request->input('search_invoice');
        $searchExpense = $request->input('search_expense');
        $searchBooker = $request->input('search_booker');
        $searchPayer = $request->input('search_payer');
        $status = array_values(array_filter((array) $request->input('status', [])));
        $amountMin = $request->input('amount_min');
        $amountMax = $request->input('amount_max');
        $amountEq = $request->input('amount_eq');

        // Date filters for Booking Date (Tanggal Biaya)
        $dateAfter = $request->input('date_after', $request->input('date_from'));
        $dateBefore = $request->input('date_before', $request->input('date_to'));
        $dateOn = $request->input('date_on');

        // Date filters for Payment Date
        $payDateAfter = $request->input('pay_date_after', $request->input('pay_date_from'));
        $payDateBefore = $request->input('pay_date_before', $request->input('pay_date_to'));
        $payDateOn = $request->input('pay_date_on');

        $query = ExpenseHistory::query()
            ->with(['bookerUser', 'payerUser', 'expenseDetail', 'statusLogs'])
            ->search($search)
            ->filterInvoiceCode($searchInvoice)
            ->filterExpenseName($searchExpense)
            ->filterBooker($searchBooker)
            ->filterPayer($searchPayer)
            ->filterStatus($status)
            ->filterAmount($amountMin, $amountMax, $amountEq);

        if ($searchCode) {
            $query->where('booking_code', 'like', "%{$searchCode}%");
        }

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
        $sortField = $request->input('sort', 'booking_date');
        $sortDir = strtolower($request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowedSorts = ['booking_date', 'invoice_code', 'booking_code', 'amount', 'status', 'created_at', 'payment_date'];
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'booking_date';
        }

        $query->orderBy($sortField, $sortDir);

        return [
            'query' => $query,
            'filters' => [
                'search' => $search,
                'searchCode' => $searchCode,
                'searchInvoice' => $searchInvoice,
                'searchExpense' => $searchExpense,
                'searchBooker' => $searchBooker,
                'searchPayer' => $searchPayer,
                'status' => $status,
                'amountMin' => $amountMin,
                'amountMax' => $amountMax,
                'amountEq' => $amountEq,
                'dateAfter' => $dateAfter,
                'dateBefore' => $dateBefore,
                'dateOn' => $dateOn,
                'payDateAfter' => $payDateAfter,
                'payDateBefore' => $payDateBefore,
                'payDateOn' => $payDateOn,
                'sort' => $sortField,
                'direction' => $sortDir,
            ],
        ];
    }

    /**
     * Display a listing of expense history records.
     */
    public function index(Request $request)
    {
        $filtered = $this->buildFilteredQuery($request);
        $query = $filtered['query'];

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $expenses = $query->paginate($perPage)->withQueryString();

        // Calculate Overview Card Totals
        $allExpensesQuery = ExpenseHistory::query();
        $totalExpensesCount = $allExpensesQuery->count();

        $totalLunasCount = (clone $allExpensesQuery)->where('status', 'Lunas')->count();
        $totalBelumBayarCount = (clone $allExpensesQuery)->where('status', 'Belum Bayar')->count();
        $totalDibatalkanCount = (clone $allExpensesQuery)->where('status', 'Dibatalkan')->count();

        $totalNominalIdr = (clone $allExpensesQuery)->where('status', '!=', 'Dibatalkan')->sum('amount');
        $totalLunasNominalIdr = (clone $allExpensesQuery)->where('status', 'Lunas')->sum('amount');
        $totalBelumBayarNominalIdr = (clone $allExpensesQuery)->where('status', 'Belum Bayar')->sum('amount');

        $statusOptions = ['Lunas', 'Belum Bayar', 'Dibatalkan'];

        if (!$request->header('X-SPA-REQUEST') && $request->ajax()) {
            return response()->json([
                'table_html' => view('expenses._rows', compact('expenses'))->render(),
                'pagination_html' => $expenses->links()->render(),
                'total' => $expenses->total(),
                'from' => $expenses->firstItem() ?? 0,
                'to' => $expenses->lastItem() ?? 0,
                'stats' => [
                    'total_count' => $totalExpensesCount,
                    'total_lunas_count' => $totalLunasCount,
                    'total_belum_bayar_count' => $totalBelumBayarCount,
                    'total_dibatalkan_count' => $totalDibatalkanCount,
                    'total_nominal_idr' => 'Rp ' . number_format($totalNominalIdr, 0, ',', '.'),
                    'lunas_nominal_idr' => 'Rp ' . number_format($totalLunasNominalIdr, 0, ',', '.'),
                    'belum_bayar_nominal_idr' => 'Rp ' . number_format($totalBelumBayarNominalIdr, 0, ',', '.'),
                ]
            ]);
        }

        return view('expenses.index', array_merge(
            $filtered['filters'],
            compact(
                'expenses',
                'statusOptions',
                'totalExpensesCount',
                'totalLunasCount',
                'totalBelumBayarCount',
                'totalDibatalkanCount',
                'totalNominalIdr',
                'totalLunasNominalIdr',
                'totalBelumBayarNominalIdr'
            )
        ));
    }

    /**
     * Show the form for creating a new expense record.
     */
    public function create()
    {
        Gate::authorize('create', ExpenseHistory::class);

        $statusOptions = ['Lunas', 'Belum Bayar', 'Dibatalkan'];
        $users = User::orderBy('name')->get();

        return view('expenses.create', compact('statusOptions', 'users'));
    }

    /**
     * Store a newly created expense record in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', ExpenseHistory::class);

        $validated = $request->validate([
            'booking_code' => 'nullable|string|max:50|unique:booking_histories,booking_code',
            'invoice_code' => 'required|string|max:100|unique:booking_histories,invoice_code',
            'booking_date' => 'required|date',
            'expense_name' => 'required|string|max:255',
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
            'expense_name.required' => 'Nama / Rincian Biaya wajib diisi.',
            'booking_date.required' => 'Tanggal biaya wajib diisi.',
            'booked_by.required' => 'Nama pengaju / pemesan wajib diisi.',
            'amount.required' => 'Nominal biaya wajib diisi.',
        ]);

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
            $attachmentPath = $request->file('attachment')->store('expenses', 'public');
        }

        $headerData = [
            'booking_type' => 'expense',
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

        $newExpense = ExpenseHistory::create($headerData);

        ExpenseDetail::create([
            'booking_history_id' => $newExpense->id,
            'expense_name' => $validated['expense_name'],
        ]);

        $creatorName = Auth::user()->name ?? 'System';
        $creatorRole = ucfirst(Auth::user()->role ?? 'user');
        $creatorId = Auth::id();

        BookingStatusLog::create([
            'booking_history_id' => $newExpense->id,
            'user_id' => $creatorId,
            'user_name' => $creatorName,
            'user_role' => Auth::user()->role ?? 'user',
            'from_status' => null,
            'to_status' => $newExpense->status,
            'notes' => 'Histori biaya lain-lain baru dibuat oleh ' . $creatorName . ' (ID: #' . $creatorId . ' • ' . $creatorRole . ') atas nama ' . $newExpense->booked_by . ' dengan status ' . $newExpense->status . '.',
        ]);

        return redirect()->route('expenses.edit', $newExpense->id)
            ->with('success', 'Histori biaya lain-lain berhasil ditambahkan!');
    }

    /**
     * Display the specified expense record details.
     */
    public function show(ExpenseHistory $expense)
    {
        $expense->load(['bookerUser', 'payerUser', 'expenseDetail', 'statusLogs']);

        if (request()->wantsJson()) {
            return response()->json($expense);
        }

        return view('expenses.show', compact('expense'));
    }

    /**
     * Show the form for editing the specified expense record.
     */
    public function edit(ExpenseHistory $expense)
    {
        Gate::authorize('update', $expense);

        $expense->load(['bookerUser', 'payerUser', 'expenseDetail', 'statusLogs']);

        $statusOptions = ['Lunas', 'Belum Bayar', 'Dibatalkan'];
        $users = User::orderBy('name')->get();

        return view('expenses.edit', compact('expense', 'statusOptions', 'users'));
    }

    /**
     * Update the specified expense record in storage.
     */
    public function update(Request $request, ExpenseHistory $expense)
    {
        Gate::authorize('update', $expense);

        if (!Auth::user()->isAdmin() && $expense->status === 'Lunas') {
            $request->merge([
                'booking_code' => $request->input('booking_code', $expense->booking_code),
                'invoice_code' => $request->input('invoice_code', $expense->invoice_code),
                'booking_date' => $request->input('booking_date', $expense->booking_date ? $expense->booking_date->format('Y-m-d') : null),
                'expense_name' => $request->input('expense_name', $expense->expense_name),
                'booked_by' => $request->input('booked_by', $expense->booked_by),
                'booked_by_user_id' => $request->input('booked_by_user_id', $expense->booked_by_user_id),
                'amount' => $request->input('amount', $expense->amount),
            ]);
        }

        $validated = $request->validate([
            'booking_code' => 'nullable|string|max:50|unique:booking_histories,booking_code,' . $expense->id,
            'invoice_code' => 'required|string|max:100|unique:booking_histories,invoice_code,' . $expense->id,
            'booking_date' => 'required|date',
            'expense_name' => 'required|string|max:255',
            'booked_by' => 'required|string|max:255',
            'booked_by_user_id' => 'nullable|exists:users,id',
            'paid_by' => 'nullable|string|max:255',
            'paid_by_user_id' => 'nullable|exists:users,id',
            'payment_date' => 'nullable|date',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|string|max:50',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $oldStatus = $expense->status;
        $newStatus = $validated['status'];

        if (Auth::user()->isFinance() && !Auth::user()->isAdmin()) {
            $validated['paid_by'] = Auth::user()->name;
            $validated['paid_by_user_id'] = Auth::id();

            if ($newStatus === 'Lunas') {
                if (empty($validated['payment_date'])) {
                    $validated['payment_date'] = now()->format('Y-m-d');
                }
            } else {
                $validated['payment_date'] = null;
            }
        } elseif (!Auth::user()->isAdmin()) {
            if ($oldStatus !== 'Lunas') {
                $validated['status'] = 'Belum Bayar';
                $validated['paid_by'] = '-';
                $validated['paid_by_user_id'] = null;
                $validated['payment_date'] = null;
            }
        }

        if ($request->hasFile('attachment')) {
            if ($expense->attachment_path && Storage::disk('public')->exists($expense->attachment_path)) {
                Storage::disk('public')->delete($expense->attachment_path);
            }
            $validated['attachment_path'] = $request->file('attachment')->store('expenses', 'public');
        }

        $headerUpdate = [
            'booking_code' => $validated['booking_code'] ?? null,
            'invoice_code' => $validated['invoice_code'],
            'booking_date' => $validated['booking_date'],
            'booked_by' => $validated['booked_by'],
            'booked_by_user_id' => $validated['booked_by_user_id'] ?? null,
            'paid_by' => $validated['paid_by'] ?? '-',
            'paid_by_user_id' => $validated['paid_by_user_id'] ?? null,
            'payment_date' => $validated['payment_date'] ?? null,
            'amount' => $validated['amount'],
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ];

        if (isset($validated['attachment_path'])) {
            $headerUpdate['attachment_path'] = $validated['attachment_path'];
        }

        $expense->update($headerUpdate);

        $expense->expenseDetail()->updateOrCreate(
            ['booking_history_id' => $expense->id],
            ['expense_name' => $validated['expense_name']]
        );

        if ($oldStatus !== $newStatus) {
            $logNotes = match ($newStatus) {
                'Lunas' => 'Status pembayaran diperbarui menjadi Lunas.',
                'Dibatalkan' => 'Histori biaya lain-lain dibatalkan oleh ' . (Auth::user()->name ?? 'User') . ' (' . ucfirst(Auth::user()->role ?? 'user') . ').',
                default => 'Status histori biaya diubah dari ' . $oldStatus . ' menjadi ' . $newStatus . '.',
            };

            BookingStatusLog::create([
                'booking_history_id' => $expense->id,
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name ?? 'System',
                'user_role' => Auth::user()->role ?? 'user',
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'notes' => $logNotes,
            ]);
        }

        return redirect()->route('expenses.index')
            ->with('success', 'Histori biaya lain-lain berhasil diperbarui!');
    }

    /**
     * Remove the specified expense record from storage.
     */
    public function destroy(ExpenseHistory $expense)
    {
        Gate::authorize('delete', $expense);

        if ($expense->attachment_path && Storage::disk('public')->exists($expense->attachment_path)) {
            Storage::disk('public')->delete($expense->attachment_path);
        }

        $expense->statusLogs()->delete();
        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', 'Histori biaya lain-lain berhasil dihapus.');
    }

    /**
     * Export filtered expense list to CSV file.
     */
    public function exportCsv(Request $request)
    {
        $filtered = $this->buildFilteredQuery($request);
        $query = $filtered['query'];
        $expenses = $query->orderBy('booking_date', 'desc')->get();

        $filename = "histori_biaya_lain_lain_" . date('Ymd_His') . ".csv";

        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($expenses) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, [
                'Kode Booking / Referensi',
                'Kode Invoice',
                'Tanggal Biaya',
                'Nama / Rincian Biaya',
                'Pengaju / Pemesan (Booked By)',
                'Pembayaran Oleh (Paid By)',
                'Tanggal Bayar',
                'Nominal Biaya (IDR)',
                'Status Pembayaran',
                'Catatan'
            ]);

            foreach ($expenses as $e) {
                fputcsv($file, [
                    $e->booking_code ?? '-',
                    $e->invoice_code,
                    $e->booking_date ? $e->booking_date->format('Y-m-d') : '',
                    $e->expense_name,
                    $e->booked_by,
                    $e->paid_by,
                    $e->payment_date ? $e->payment_date->format('Y-m-d') : '-',
                    $e->amount,
                    $e->status,
                    $e->notes ?? '-'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
