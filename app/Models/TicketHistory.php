<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'booked_by_user_id',
        'paid_by',
        'paid_by_user_id',
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
     * Relationship to Booker user
     */
    public function bookerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'booked_by_user_id');
    }

    /**
     * Relationship to Payer user
     */
    public function payerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by_user_id');
    }

    /**
     * Relationship to status activity logs
     */
    public function statusLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TicketStatusLog::class, 'ticket_history_id')->orderBy('id', 'asc');
    }

    /**
     * Get passenger names as an array
     */
    public function getPassengersListAttribute(): array
    {
        if (empty($this->passenger_name)) {
            return [];
        }

        // Handle JSON or comma/newline separated strings
        if (str_starts_with(trim($this->passenger_name), '[')) {
            $decoded = json_decode($this->passenger_name, true);
            if (is_array($decoded)) {
                return array_values(array_filter(array_map('trim', $decoded)));
            }
        }

        $items = preg_split('/[,\n]+/', $this->passenger_name);
        return array_values(array_filter(array_map('trim', $items)));
    }

    /**
     * Get count of passengers
     */
    public function getPassengerCountAttribute(): int
    {
        return count($this->passengers_list);
    }

    /**
     * Get formatted display string for passengers
     */
    public function getPassengerDisplayAttribute(): string
    {
        $list = $this->passengers_list;
        if (empty($list)) {
            return '-';
        }

        if (count($list) === 1) {
            return $list[0];
        }

        return implode(', ', $list) . ' (' . count($list) . ' Penumpang)';
    }

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
     * Scope for transport type filter (supports single string or array of strings)
     */
    public function scopeFilterTransport($query, string|array|null $transport)
    {
        if (empty($transport)) {
            return $query;
        }

        if (is_array($transport)) {
            $filtered = array_values(array_filter($transport));
            return empty($filtered) ? $query : $query->whereIn('transport_type', $filtered);
        }

        return $query->where('transport_type', $transport);
    }

    /**
     * Scope for status filter (supports single string or array of strings)
     */
    public function scopeFilterStatus($query, string|array|null $status)
    {
        if (empty($status)) {
            return $query;
        }

        if (is_array($status)) {
            $filtered = array_values(array_filter($status));
            return empty($filtered) ? $query : $query->whereIn('status', $filtered);
        }

        return $query->where('status', $status);
    }
}
