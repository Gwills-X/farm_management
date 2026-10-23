<?php

namespace App\Policies;

use App\Models\AnimalType;
use App\Models\User;

class AnimalTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, AnimalType $animalType): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, AnimalType $animalType): bool
    {
        return $user->isAdmin();
    }
}
