<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class TicketHistory extends BookingHistory
{
    protected static function booted(): void
    {
        static::addGlobalScope('ticket', function (Builder $builder) {
            $builder->where('booking_type', 'ticket');
        });

        static::creating(function ($model) {
            $model->booking_type = 'ticket';
        });
    }

    /**
     * Alias for ticket_code -> booking_code
     */
    public function getTicketCodeAttribute(): ?string
    {
        return $this->booking_code;
    }

    public function setTicketCodeAttribute(?string $value): void
    {
        $this->attributes['booking_code'] = $value;
    }

    /**
     * Alias for ticket_date -> booking_date
     */
    public function getTicketDateAttribute()
    {
        return $this->booking_date;
    }

    public function setTicketDateAttribute($value): void
    {
        $this->attributes['booking_date'] = $value;
    }

    /**
     * Delegated accessors for ticket details
     */
    public function getTransportTypeAttribute(): string
    {
        return $this->ticketDetail?->transport_type ?? 'Pesawat';
    }

    public function getOriginAttribute(): string
    {
        return $this->ticketDetail?->origin ?? '';
    }

    public function getDestinationAttribute(): string
    {
        return $this->ticketDetail?->destination ?? '';
    }

    public function getPassengerNameAttribute(): string
    {
        return $this->ticketDetail?->passenger_name ?? '';
    }

    public function getPassengersListAttribute(): array
    {
        return $this->ticketDetail?->passengers_list ?? [];
    }

    public function getPassengerCountAttribute(): int
    {
        return $this->ticketDetail?->passenger_count ?? 0;
    }

    public function getPassengerDisplayAttribute(): string
    {
        return $this->ticketDetail?->passenger_display ?? '-';
    }

    public function getTransportIconAttribute(): string
    {
        return $this->ticketDetail?->transport_icon ?? '🎫';
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
                ->orWhereHas('ticketDetail', function ($t) use ($search) {
                    $t->where('origin', 'like', "%{$search}%")
                        ->orWhere('destination', 'like', "%{$search}%")
                        ->orWhere('passenger_name', 'like', "%{$search}%")
                        ->orWhere('transport_type', 'like', "%{$search}%");
                });
        });
    }

    public function scopeFilterCode($query, ?string $code)
    {
        return empty($code) ? $query : $query->where('booking_code', 'like', "%{$code}%");
    }

    public function scopeFilterInvoiceCode($query, ?string $invoice)
    {
        return empty($invoice) ? $query : $query->where('invoice_code', 'like', "%{$invoice}%");
    }

    public function scopeFilterOrigin($query, ?string $origin)
    {
        return empty($origin) ? $query : $query->whereHas('ticketDetail', fn($t) => $t->where('origin', 'like', "%{$origin}%"));
    }

    public function scopeFilterDestination($query, ?string $destination)
    {
        return empty($destination) ? $query : $query->whereHas('ticketDetail', fn($t) => $t->where('destination', 'like', "%{$destination}%"));
    }

    public function scopeFilterPassenger($query, ?string $passenger)
    {
        return empty($passenger) ? $query : $query->whereHas('ticketDetail', fn($t) => $t->where('passenger_name', 'like', "%{$passenger}%"));
    }

    public function scopeFilterBooker($query, ?string $booker)
    {
        return empty($booker) ? $query : $query->where('booked_by', 'like', "%{$booker}%");
    }

    public function scopeFilterPayer($query, ?string $payer)
    {
        return empty($payer) ? $query : $query->where('paid_by', 'like', "%{$payer}%");
    }

    public function scopeFilterRoute($query, ?string $route)
    {
        if (empty($route)) {
            return $query;
        }

        return $query->whereHas('ticketDetail', function ($t) use ($route) {
            $t->where('origin', 'like', "%{$route}%")
                ->orWhere('destination', 'like', "%{$route}%")
                ->orWhere('passenger_name', 'like', "%{$route}%");
        });
    }

    public function scopeFilterPerson($query, ?string $person)
    {
        if (empty($person)) {
            return $query;
        }

        return $query->where(function ($q) use ($person) {
            $q->where('booked_by', 'like', "%{$person}%")
                ->orWhere('paid_by', 'like', "%{$person}%");
        });
    }

    public function scopeFilterTransport($query, string|array|null $transport)
    {
        if (empty($transport)) {
            return $query;
        }

        $filtered = array_values(array_filter((array) $transport));
        if (empty($filtered)) {
            return $query;
        }

        return $query->whereHas('ticketDetail', fn($t) => $t->whereIn('transport_type', $filtered));
    }

    public function scopeFilterPassengerCount($query, $min = null, $max = null, $eq = null)
    {
        $expr = "(LENGTH(COALESCE(ticket_details.passenger_name, '')) - LENGTH(REPLACE(COALESCE(ticket_details.passenger_name, ''), ',', '')) + CASE WHEN COALESCE(ticket_details.passenger_name, '') = '' THEN 0 ELSE 1 END)";

        return $query->whereHas('ticketDetail', function ($q) use ($min, $max, $eq, $expr) {
            if ($eq !== null && $eq !== '') {
                $q->whereRaw("{$expr} = ?", [(int) $eq]);
            } else {
                if ($min !== null && $min !== '') {
                    $q->whereRaw("{$expr} >= ?", [(int) $min]);
                }
                if ($max !== null && $max !== '') {
                    $q->whereRaw("{$expr} <= ?", [(int) $max]);
                }
            }
        });
    }
}
