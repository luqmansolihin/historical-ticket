<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_history_id',
        'user_id',
        'user_name',
        'user_role',
        'from_status',
        'to_status',
        'notes',
    ];

    /**
     * Relationship to TicketHistory
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(TicketHistory::class, 'ticket_history_id');
    }

    /**
     * Relationship to User who performed action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get display role (always synchronized with the active user account role if available)
     */
    public function getDisplayRoleAttribute(): string
    {
        $role = $this->user ? $this->user->role : $this->user_role;
        return ucfirst($role ?? 'user');
    }

    /**
     * Get CSS badge class for the target status (to_status)
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->to_status) {
            'Lunas' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
            'Belum Bayar' => 'bg-rose-500/10 text-rose-400 border-rose-500/30',
            'Dibatalkan' => 'bg-slate-800 text-slate-400 border-slate-700',
            default => 'bg-gray-500/10 text-gray-400 border-gray-500/30',
        };
    }
}
