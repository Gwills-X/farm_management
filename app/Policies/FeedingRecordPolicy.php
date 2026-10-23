<?php

namespace App\Policies;

use App\Models\FeedingRecord;
use App\Models\User;

class FeedingRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, FeedingRecord $record): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    /** Admins can edit any record. Staff can edit only records they created. */
    public function update(User $user, FeedingRecord $record): bool
    {
        return $user->isAdmin() || $record->recorded_by === $user->id;
    }
}
