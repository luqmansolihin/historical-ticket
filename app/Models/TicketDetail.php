<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_history_id',
        'transport_type',
        'origin',
        'destination',
        'passenger_name',
    ];

    public function bookingHistory(): BelongsTo
    {
        return $this->belongsTo(BookingHistory::class);
    }

    /**
     * Get passenger names as an array
     */
    public function getPassengersListAttribute(): array
    {
        if (empty($this->passenger_name)) {
            return [];
        }

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
}
