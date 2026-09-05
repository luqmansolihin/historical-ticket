<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if user is Admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is Finance
     */
    public function isFinance(): bool
    {
        return $this->role === 'finance' || $this->role === 'booker' || $this->isAdmin();
    }

    /**
     * Check if user is Booker (Alias for Finance)
     */
    public function isBooker(): bool
    {
        return $this->isFinance();
    }

    /**
     * Check if user is Payer (Alias for Finance)
     */
    public function isPayer(): bool
    {
        return $this->isFinance();
    }

    /**
     * Tickets booked by this user
     */
    public function bookedTickets(): HasMany
    {
        return $this->hasMany(TicketHistory::class, 'booked_by_user_id');
    }

    /**
     * Tickets paid by this user
     */
    public function paidTickets(): HasMany
    {
        return $this->hasMany(TicketHistory::class, 'paid_by_user_id');
    }

    /**
     * Get CSS badge class for user role
     */
    public function getRoleBadgeClassAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'bg-amber-500/10 text-amber-300 border-amber-500/30',
            'finance', 'booker', 'payer' => 'bg-sky-500/10 text-sky-300 border-sky-500/30',
            default => 'bg-slate-500/10 text-slate-300 border-slate-500/30',
        };
    }
}
