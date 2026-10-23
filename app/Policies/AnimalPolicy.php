<?php

namespace App\Policies;

use App\Models\Animal;
use App\Models\User;

class AnimalPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Animal $animal): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, Animal $animal): bool
    {
        return $user->is_active;
    }

    public function delete(User $user, Animal $animal): bool
    {
        return $user->isAdmin();
    }
}
