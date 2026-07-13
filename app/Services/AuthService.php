<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\RoleEnum;
use App\Exceptions\ApiException;
use App\Models\User;
use App\Notifications\StaffAccountCreatedNotification;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthService extends BaseService
{
    /**
     * Stored under a distinct property name (not $repository) because
     * BaseService already declares a typed $repository property as
     * BaseRepositoryInterface via constructor promotion. PHP requires
     * an inherited typed property to keep the exact same type in a
     * child class — it cannot be narrowed to UserRepositoryInterface,
     * even though UserRepositoryInterface extends BaseRepositoryInterface.
     * Redeclaring it with a narrower type is a fatal error at class
     * load time. Using a second, distinctly named property avoids the
     * conflict entirely while still giving this class access to the
     * User-specific repository methods (e.g. findByEmail()).
     */
    public function __construct(protected UserRepositoryInterface $userRepository)
    {
        parent::__construct($userRepository);
    }

    /**
     * Attempt session-based login. Returns the authenticated user
     * on success or throws ApiException on failure — callers never
     * need to duplicate the credential-mismatch vs inactive-account
     * distinction themselves.
     *
     * @param array{email: string, password: string} $credentials
     */
    public function login(array $credentials, bool $remember = false): User
    {
        $user = $this->userRepository->findByEmail($credentials['email']);

        if (! $user || $user->trashed()) {
            throw new ApiException('These credentials do not match our records.', 422);
        }

        if (! $user->is_active) {
            throw new ApiException('This account has been deactivated. Contact your administrator.', 403);
        }

        if (! Auth::guard('web')->attempt($credentials, $remember)) {
            throw new ApiException('These credentials do not match our records.', 422);
        }

        return $user;
    }

    public function logout(): void
    {
        Auth::guard('web')->logout();
    }

    /**
     * Create a staff account (Company Admin, Distributor, Retailer,
     * or another Super Admin) on behalf of an authorized acting
     * admin. Role-assignment permission is validated in
     * CreateStaffUserRequest before this ever runs — this method
     * assumes the role is already authorized.
     *
     * @param array{name: string, email: string, phone?: string|null, role: string} $data
     */
    public function createStaffUser(array $data, User $actingAdmin): User
    {
        return $this->transaction(function () use ($data, $actingAdmin) {
            /** @var User $user */
            $user = $this->userRepository->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => bcrypt(Str::random(40)), // unusable placeholder; set via reset link
                'is_active' => true,
                'created_by' => $actingAdmin->id,
                'email_verified_at' => now(),
            ]);

            $user->assignRole($data['role']);

            $token = Password::createToken($user);
            $user->notify(new StaffAccountCreatedNotification($token));

            return $user;
        });
    }

    public function deactivate(User $user, User $actingAdmin): User
    {
        if ($user->is($actingAdmin)) {
            throw new ApiException('You cannot deactivate your own account.', 422);
        }

        if ($user->hasRole(RoleEnum::SUPER_ADMIN->value)) {
            throw new ApiException('Super Admin accounts cannot be deactivated.', 422);
        }

        return $this->userRepository->update($user, ['is_active' => false]);
    }

    public function activate(User $user): User
    {
        return $this->userRepository->update($user, ['is_active' => true]);
    }

    /**
     * Staff listing scoped to the acting admin: Super Admin sees
     * everyone, Company Admin sees only accounts they personally
     * created. Lives here (not in the controller) so the controller
     * never touches Eloquent directly, keeping the repository/service
     * layers as the single source of query logic.
     *
     * @return Collection<int, User>
     */
    public function listStaffFor(User $actingAdmin): Collection
    {
        return $actingAdmin->hasRole(RoleEnum::SUPER_ADMIN->value)
            ? $this->userRepository->allWithRoles()
            : $this->userRepository->createdBy($actingAdmin->id);
    }

    /**
     * Roles a given acting admin is permitted to assign when
     * creating a new staff account. Source of truth used by both
     * the Form Request (authorization) and any UI that needs to
     * render only the valid role options.
     *
     * @return array<int, string>
     */
    public function assignableRolesFor(User $actingAdmin): array
    {
        return match (true) {
            $actingAdmin->hasRole(RoleEnum::SUPER_ADMIN->value) => [
                RoleEnum::SUPER_ADMIN->value,
                RoleEnum::COMPANY_ADMIN->value,
                RoleEnum::DISTRIBUTOR->value,
                RoleEnum::RETAILER->value,
            ],
            $actingAdmin->hasRole(RoleEnum::COMPANY_ADMIN->value) => [
                RoleEnum::DISTRIBUTOR->value,
                RoleEnum::RETAILER->value,
            ],
            default => [],
        };
    }
}
