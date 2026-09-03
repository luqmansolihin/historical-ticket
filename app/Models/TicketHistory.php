<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_code',
        'ticket_date',
        'origin',
        'destination',
        'transport_type',
        'passenger_name',
        'booked_by',
        'paid_by',
        'payment_date',
        'amount',
        'status',
        'notes',
        'attachment_path',
    ];

    protected $casts = [
        'ticket_date' => 'date',
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Format amount in IDR (Rp 1.500.000)
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    /**
     * Get icon representation for transport type
     */
    public function getTransportIconAttribute(): string
    {
        return match ($this->transport_type) {
            'Pesawat' => '✈️',
            'Kereta Api' => '🚆',
            'Bus' => '🚌',
            'Travel' => '🚐',
            'Kapal Laut' => '🚢',
            'Mobil / Rental' => '🚗',
            default => '🎫',
        };
    }

    /**
     * Get CSS classes for status badge
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'Lunas' => 'bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-900/40 dark:text-emerald-300 dark:border-emerald-700',
            'Belum Bayar' => 'bg-rose-100 text-rose-800 border-rose-300 dark:bg-rose-900/40 dark:text-rose-300 dark:border-rose-700',
            'Reimburse' => 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-900/40 dark:text-amber-300 dark:border-amber-700',
            'Dibatalkan' => 'bg-slate-100 text-slate-700 border-slate-300 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700',
            default => 'bg-gray-100 text-gray-800 border-gray-300',
        };
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
            $q->where('ticket_code', 'like', "%{$search}%")
                ->orWhere('origin', 'like', "%{$search}%")
                ->orWhere('destination', 'like', "%{$search}%")
                ->orWhere('passenger_name', 'like', "%{$search}%")
                ->orWhere('booked_by', 'like', "%{$search}%")
                ->orWhere('paid_by', 'like', "%{$search}%")
                ->orWhere('transport_type', 'like', "%{$search}%");
        });
    }

    /**
     * Scope for transport type filter
     */
    public function scopeFilterTransport($query, ?string $transport)
    {
        if (empty($transport)) {
            return $query;
        }

        return $query->where('transport_type', $transport);
    }

    /**
     * Scope for status filter
     */
    public function scopeFilterStatus($query, ?string $status)
    {
        if (empty($status)) {
            return $query;
        }

        return $query->where('status', $status);
    }
}
