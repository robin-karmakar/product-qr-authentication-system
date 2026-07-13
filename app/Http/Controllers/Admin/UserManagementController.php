<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CreateStaffUserRequest;
use App\Http\Responses\ApiResponseTrait;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private readonly AuthService $authService)
    {
    }

    /**
     * List staff accounts. Super Admin sees everyone; Company Admin
     * sees only accounts they personally created (Distributors and
     * Retailers), keeping the single-organization model's scoping
     * simple without needing a separate multi-tenant guard.
     *
     * Query logic lives entirely in AuthService/UserRepository —
     * this method never touches Eloquent directly, per the project's
     * repository/service architecture.
     */
    public function index(Request $request): View
    {
        $actingUser = $request->user();

        return view('admin.users.index', [
            'users' => $this->authService->listStaffFor($actingUser),
            'assignableRoles' => $this->authService->assignableRolesFor($actingUser),
        ]);
    }

    public function store(CreateStaffUserRequest $request): JsonResponse
    {
        $user = $this->authService->createStaffUser(
            $request->validated(),
            $request->user()
        );

        return $this->created([
            'uuid' => $user->uuid,
            'name' => $user->name,
            'email' => $user->email,
        ], 'Staff account created. An activation email has been sent.');
    }

    public function deactivate(Request $request, User $user): JsonResponse
    {
        $this->authService->deactivate($user, $request->user());

        return $this->success(null, 'Account deactivated.');
    }

    public function activate(User $user): JsonResponse
    {
        $this->authService->activate($user);

        return $this->success(null, 'Account activated.');
    }
}
