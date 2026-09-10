<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_history_id',
        'expense_name',
    ];

    public function bookingHistory(): BelongsTo
    {
        return $this->belongsTo(BookingHistory::class);
    }
}
