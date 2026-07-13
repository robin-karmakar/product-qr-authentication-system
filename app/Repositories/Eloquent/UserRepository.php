<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->query()->where('email', $email)->first();
    }

    /**
     * Users created by a given admin, scoped by role — used by
     * Company Admin dashboards to list only the Distributors/
     * Retailers they personally onboarded.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    public function createdBy(int $adminId, ?string $role = null)
    {
        $query = $this->query()->where('created_by', $adminId);

        if ($role) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $role));
        }

        return $query->latest()->get();
    }
}
