<?php

namespace App\Policies;

use App\Models\HealthRecord;
use App\Models\User;

class HealthRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, HealthRecord $record): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    /** Admins can edit any record. Staff can edit only records they created. */
    public function update(User $user, HealthRecord $record): bool
    {
        return $user->isAdmin() || $record->recorded_by === $user->id;
    }
}
