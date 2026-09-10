<?php

namespace App\Policies;

use App\Models\ExpenseHistory;
use App\Models\User;

class ExpenseHistoryPolicy
{
    /**
     * Determine whether the user can view any expense records.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view a specific expense record.
     */
    public function view(User $user, ExpenseHistory $expense): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create expense records.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isFinance();
    }

    /**
     * Determine whether the user can update the expense record.
     */
    public function update(User $user, ExpenseHistory $expense): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->role === 'user') {
            return false;
        }

        if ($expense->status === 'Dibatalkan') {
            return false;
        }

        return $expense->booked_by_user_id !== null && (int)$expense->booked_by_user_id === (int)$user->id;
    }

    /**
     * Determine whether the user can delete the expense record.
     */
    public function delete(User $user, ExpenseHistory $expense): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($expense->status === 'Belum Bayar') {
            return $expense->booked_by_user_id !== null && (int)$expense->booked_by_user_id === (int)$user->id;
        }

        return false;
    }
}
