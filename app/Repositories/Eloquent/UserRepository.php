<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?User
    {
        // withTrashed() is required here: without it, a soft-deleted
        // user's email simply returns null (as if the account never
        // existed), which means AuthService::login()'s trashed()
        // check could never actually be reached.
        return $this->query()->withTrashed()->where('email', $email)->first();
    }

    public function allWithRoles(): Collection
    {
        return $this->query()->with('roles')->latest()->get();
    }

    public function createdBy(int $adminId, ?string $role = null): Collection
    {
        $query = $this->query()->with('roles')->where('created_by', $adminId);

        if ($role) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $role));
        }

        return $query->latest()->get();
    }
}
