<?php

namespace App\Policies;

use App\Models\TicketHistory;
use App\Models\User;

class TicketHistoryPolicy
{
    /**
     * Determine whether the user can view any ticket records.
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
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isBooker() || $user->isPayer();
    }

    /**
     * Determine whether the user can update the ticket record.
     * Booker who booked it, Payer responsible for paying it, or Admin.
     */
    public function update(User $user, TicketHistory $ticket): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Booker of this ticket can edit it
        if ($ticket->booked_by_user_id && $ticket->booked_by_user_id === $user->id) {
            return true;
        }

        // Payer assigned to this ticket can edit it
        if ($ticket->paid_by_user_id && $ticket->paid_by_user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the payment status of the ticket.
     */
    public function updatePaymentStatus(User $user, TicketHistory $ticket): bool
    {
        if ($user->isAdmin() || $user->isPayer()) {
            return true;
        }

        return $ticket->paid_by_user_id && $ticket->paid_by_user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the ticket record.
     * Only Admin or the Booker who created the ticket can delete it.
     */
    public function delete(User $user, TicketHistory $ticket): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $ticket->booked_by_user_id && $ticket->booked_by_user_id === $user->id;
    }
}
