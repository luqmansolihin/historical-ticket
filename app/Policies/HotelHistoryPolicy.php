<?php

namespace App\Policies;

use App\Models\HotelHistory;
use App\Models\User;

class HotelHistoryPolicy
{
    /**
     * Determine whether the user can view any hotel records.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view a specific hotel record.
     */
    public function view(User $user, HotelHistory $hotel): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create hotel records.
     * Only Admin or Finance can create hotel bookings.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isFinance();
    }

    /**
     * Determine whether the user can update the hotel record.
     */
    public function update(User $user, HotelHistory $hotel): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->role === 'user') {
            return false;
        }

        if ($hotel->status === 'Dibatalkan') {
            return false;
        }

        return $hotel->booked_by_user_id !== null && (int)$hotel->booked_by_user_id === (int)$user->id;
    }

    /**
     * Determine whether the user can delete the hotel record.
     */
    public function delete(User $user, HotelHistory $hotel): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($hotel->status === 'Belum Bayar') {
            return $hotel->booked_by_user_id !== null && (int)$hotel->booked_by_user_id === (int)$user->id;
        }

        return false;
    }
}
