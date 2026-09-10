<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class ExpenseHistory extends BookingHistory
{
    protected static function booted(): void
    {
        static::addGlobalScope('expense', function (Builder $builder) {
            $builder->where('booking_type', 'expense');
        });

        static::creating(function ($model) {
            $model->booking_type = 'expense';
        });
    }

    /**
     * Delegated accessors for expense details
     */
    public function getExpenseNameAttribute(): string
    {
        return $this->expenseDetail?->expense_name ?? '';
    }

    /**
     * Scope for searching keyword
     */
    public function scopeSearch($query, ?string $search)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('booking_code', 'like', "%{$search}%")
                ->orWhere('invoice_code', 'like', "%{$search}%")
                ->orWhere('booked_by', 'like', "%{$search}%")
                ->orWhere('paid_by', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%")
                ->orWhereHas('expenseDetail', function ($e) use ($search) {
                    $e->where('expense_name', 'like', "%{$search}%");
                });
        });
    }

    public function scopeFilterInvoiceCode($query, ?string $invoice)
    {
        return empty($invoice) ? $query : $query->where('invoice_code', 'like', "%{$invoice}%");
    }

    public function scopeFilterExpenseName($query, ?string $name)
    {
        return empty($name) ? $query : $query->whereHas('expenseDetail', fn($e) => $e->where('expense_name', 'like', "%{$name}%"));
    }

    public function scopeFilterBooker($query, ?string $booker)
    {
        return empty($booker) ? $query : $query->where('booked_by', 'like', "%{$booker}%");
    }

    public function scopeFilterPayer($query, ?string $payer)
    {
        return empty($payer) ? $query : $query->where('paid_by', 'like', "%{$payer}%");
    }
}
