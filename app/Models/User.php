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
     * Check if user is Booker
     */
    public function isBooker(): bool
    {
        return $this->role === 'booker' || $this->isAdmin();
    }

    /**
     * Check if user is Payer
     */
    public function isPayer(): bool
    {
        return $this->role === 'payer' || $this->isAdmin();
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
}
