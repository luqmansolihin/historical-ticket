<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotelDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_history_id',
        'hotel_name',
        'check_in_date',
        'check_out_date',
        'guest_name',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
    ];

    public function bookingHistory(): BelongsTo
    {
        return $this->belongsTo(BookingHistory::class);
    }

    /**
     * Get guest names as an array
     */
    public function getGuestsListAttribute(): array
    {
        if (empty($this->guest_name)) {
            return [];
        }

        if (str_starts_with(trim($this->guest_name), '[')) {
            $decoded = json_decode($this->guest_name, true);
            if (is_array($decoded)) {
                return array_values(array_filter(array_map('trim', $decoded)));
            }
        }

        $items = preg_split('/[,\n]+/', $this->guest_name);
        return array_values(array_filter(array_map('trim', $items)));
    }

    /**
     * Get count of guests
     */
    public function getGuestCountAttribute(): int
    {
        return count($this->guests_list);
    }

    /**
     * Get formatted display string for guests
     */
    public function getGuestDisplayAttribute(): string
    {
        $list = $this->guests_list;
        if (empty($list)) {
            return '-';
        }

        if (count($list) === 1) {
            return $list[0];
        }

        return implode(', ', $list) . ' (' . count($list) . ' Tamu)';
    }

    /**
     * Get total nights count
     */
    public function getNightCountAttribute(): int
    {
        if (!$this->check_in_date || !$this->check_out_date) {
            return 0;
        }

        $nights = $this->check_in_date->diffInDays($this->check_out_date);
        return max(1, (int) $nights);
    }
}
