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
     * Admin can edit any ticket.
     * Non-admin users can ONLY edit tickets that they created/booked.
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

        // Ticket can ONLY be edited by the user who created/booked it
        return $ticket->booked_by_user_id !== null && (int)$ticket->booked_by_user_id === (int)$user->id;
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

        return $ticket->booked_by_user_id !== null && (int)$ticket->booked_by_user_id === (int)$user->id;
    }

    /**
     * Determine whether the user can delete the ticket record.
     * Admin can delete any ticket.
     * Booker can delete tickets created by them if status is 'Belum Bayar'.
     */
    public function delete(User $user, TicketHistory $ticket): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($ticket->status === 'Belum Bayar') {
            return $ticket->booked_by_user_id !== null && (int)$ticket->booked_by_user_id === (int)$user->id;
        }

        return false;
    }
}
