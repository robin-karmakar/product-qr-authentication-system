<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find a user by email, including soft-deleted records (needed
     * so deactivated/removed accounts still surface a clear error
     * rather than a generic "not found" during login).
     */
    public function findByEmail(string $email): ?User;
}
