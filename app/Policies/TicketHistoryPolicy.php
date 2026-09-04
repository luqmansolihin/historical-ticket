<?php

namespace App\Policies;

use App\Models\TicketHistory;
use App\Models\User;

class TicketHistoryPolicy
{
    /**
     * Determine whether the user can view any ticket records.
     * All authenticated users (Admin, Booker, Payer, User) can view ticket list.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view a specific ticket record.
     */
    public function view(User $user, TicketHistory $ticket): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create ticket records.
     * Only Admin or Booker can create tickets.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isBooker();
    }

    /**
     * Determine whether the user can update the ticket record.
     * Admin, Booker (who booked it or is handling payment). Regular 'user' CANNOT edit.
     */
    public function update(User $user, TicketHistory $ticket): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Regular users (role = 'user') are strictly read-only
        if ($user->role === 'user') {
            return false;
        }

        // Non-admin users CANNOT edit tickets that are already canceled ('Dibatalkan')
        if ($ticket->status === 'Dibatalkan') {
            return false;
        }

        // Booker (merangkap Payer) who booked this ticket, is assigned as payer, or for unpaid tickets
        if ($user->isBooker() || $user->isPayer()) {
            if ($ticket->booked_by_user_id === $user->id || $ticket->paid_by_user_id === $user->id || $ticket->paid_by_user_id === null || $ticket->status === 'Belum Bayar') {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can update the payment status of the ticket.
     */
    public function updatePaymentStatus(User $user, TicketHistory $ticket): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->role === 'user') {
            return false;
        }

        return ($user->isBooker() || $user->isPayer()) && ($ticket->paid_by_user_id === $user->id || $ticket->paid_by_user_id === null);
    }

    /**
     * Determine whether the user can delete the ticket record.
     * Admin can delete any ticket. Booker (merangkap Payer) can delete tickets if status is 'Belum Bayar'.
     */
    public function delete(User $user, TicketHistory $ticket): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($ticket->status === 'Belum Bayar') {
            if ($user->isBooker() || $user->isPayer()) {
                return true;
            }
        }

        return false;
    }
}
