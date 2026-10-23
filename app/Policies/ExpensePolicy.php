<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    /** Admins can edit any expense. Staff can edit only expenses they recorded. */
    public function update(User $user, Expense $expense): bool
    {
        return $user->isAdmin() || $expense->recorded_by === $user->id;
    }

    /** Only admins can delete expenses. */
    public function delete(User $user, Expense $expense): bool
    {
        return $user->isAdmin();
    }
}
