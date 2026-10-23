<?php

namespace App\Services;

use App\Models\User;
use DomainException;

/**
 * Protects the admin team from locking itself out.
 */
class UserAccountService
{
    public function assertCanChangeRole(User $target, string $newRole, User $actor): void
    {
        if ($actor->is($target)) {
            throw new DomainException('You cannot change your own role.');
        }

        if ($newRole === User::ROLE_ADMIN) {
            return;
        }

        if ($target->isAdmin() && $target->is_active) {
            $this->assertOtherActiveAdminExists($target, 'demoted');
        }
    }

    public function assertCanDeactivate(User $target, User $actor): void
    {
        if ($target->is($actor)) {
            throw new DomainException('You cannot deactivate your own account.');
        }

        if ($target->isAdmin() && $target->is_active) {
            $this->assertOtherActiveAdminExists($target, 'deactivated');
        }
    }

    private function assertOtherActiveAdminExists(User $target, string $action): void
    {
        $exists = User::query()
            ->where('role', User::ROLE_ADMIN)
            ->where('is_active', true)
            ->whereKeyNot($target->getKey())
            ->exists();

        if (! $exists) {
            throw new DomainException("The last active administrator cannot be {$action}.");
        }
    }
}
