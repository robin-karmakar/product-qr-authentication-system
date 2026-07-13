<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find a user by email, including soft-deleted records — this is
     * what lets AuthService::login() distinguish "no such account"
     * from "this account was removed" via trashed().
     */
    public function findByEmail(string $email): ?User;

    /**
     * All staff accounts with roles eager-loaded, for the Super
     * Admin's unrestricted user management view.
     *
     * @return Collection<int, User>
     */
    public function allWithRoles(): Collection;

    /**
     * Users created by a given admin, scoped by role, with roles
     * eager-loaded — used by Company Admin dashboards to list only
     * the Distributors/Retailers they personally onboarded.
     *
     * @return Collection<int, User>
     */
    public function createdBy(int $adminId, ?string $role = null): Collection;
}
