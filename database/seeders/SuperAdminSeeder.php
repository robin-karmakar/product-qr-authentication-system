<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Creates the single bootstrap Super Admin account. This is the
     * only account ever created outside the admin-created-user flow,
     * since there must be at least one account with the authority to
     * create every other account.
     *
     * Credentials come from .env and must be rotated immediately
     * after first login in any real deployment.
     */
    public function run(): void
    {
        $email = config('spats.super_admin.email');
        $password = config('spats.super_admin.password');

        if (! $email || ! $password) {
            $this->command?->warn(
                'SPATS_SUPER_ADMIN_EMAIL / SPATS_SUPER_ADMIN_PASSWORD not set — skipping Super Admin seed.'
            );

            return;
        }

        $admin = User::withTrashed()->firstOrCreate(
            ['email' => $email],
            [
                'name' => 'System Super Admin',
                'password' => Hash::make($password),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        if (! $admin->hasRole(RoleEnum::SUPER_ADMIN->value)) {
            $admin->assignRole(RoleEnum::SUPER_ADMIN->value);
        }
    }
}
