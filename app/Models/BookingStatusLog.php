<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_history_id',
        'user_id',
        'user_name',
        'user_role',
        'from_status',
        'to_status',
        'notes',
    ];

    public function bookingHistory(): BelongsTo
    {
        return $this->belongsTo(BookingHistory::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
