<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class HotelHistory extends BookingHistory
{
    protected static function booted(): void
    {
        static::addGlobalScope('hotel', function (Builder $builder) {
            $builder->where('booking_type', 'hotel');
        });

        static::creating(function ($model) {
            $model->booking_type = 'hotel';
        });
    }

    /**
     * Delegated accessors for hotel details
     */
    public function getHotelNameAttribute(): string
    {
        return $this->hotelDetail?->hotel_name ?? '';
    }

    public function getCheckInDateAttribute()
    {
        return $this->hotelDetail?->check_in_date;
    }

    public function getCheckOutDateAttribute()
    {
        return $this->hotelDetail?->check_out_date;
    }

    public function getGuestNameAttribute(): string
    {
        return $this->hotelDetail?->guest_name ?? '';
    }

    public function getGuestsListAttribute(): array
    {
        return $this->hotelDetail?->guests_list ?? [];
    }

    public function getGuestCountAttribute(): int
    {
        return $this->hotelDetail?->guest_count ?? 0;
    }

    public function getGuestDisplayAttribute(): string
    {
        return $this->hotelDetail?->guest_display ?? '-';
    }

    public function getNightCountAttribute(): int
    {
        return $this->hotelDetail?->night_count ?? 0;
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
                ->orWhereHas('hotelDetail', function ($h) use ($search) {
                    $h->where('hotel_name', 'like', "%{$search}%")
                        ->orWhere('guest_name', 'like', "%{$search}%");
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

    public function scopeFilterHotelName($query, ?string $hotel)
    {
        return empty($hotel) ? $query : $query->whereHas('hotelDetail', fn($h) => $h->where('hotel_name', 'like', "%{$hotel}%"));
    }

    public function scopeFilterGuest($query, ?string $guest)
    {
        return empty($guest) ? $query : $query->whereHas('hotelDetail', fn($h) => $h->where('guest_name', 'like', "%{$guest}%"));
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
