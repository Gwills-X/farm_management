<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Creates the first administrator account for local development.
 *
 * Credentials come from .env (ADMIN_EMAIL, ADMIN_PASSWORD). If they are not set,
 * development defaults are used. CHANGE THE PASSWORD BEFORE ANY REAL DEPLOYMENT.
 * The password is only set when the account is first created, so re-running the
 * seeder does not reset a password you have already changed.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@farm.test');
        $password = env('ADMIN_PASSWORD', 'ChangeMe123!');

        $user = User::firstOrNew(['email' => $email]);

        $user->name = $user->name ?: 'Farm Administrator';
        $user->role = User::ROLE_ADMIN;
        $user->is_active = true;

        if (! $user->exists) {
            // The model's 'hashed' cast hashes this automatically on save.
            $user->password = $password;
        }

        $user->save();
    }
}
