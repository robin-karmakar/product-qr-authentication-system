<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Services\AuthService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateStaffUserRequest extends FormRequest
{
    /**
     * A user may only create staff accounts with roles their own
     * role is permitted to assign — enforced here so no controller
     * or service call can ever be reached with an unauthorized
     * role in the payload.
     */
    public function authorize(): bool
    {
        /** @var \App\Models\User|null $actingUser */
        $actingUser = $this->user();

        if (! $actingUser) {
            return false;
        }

        return $actingUser->can('manage-users');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $assignableRoles = app(AuthService::class)->assignableRolesFor($this->user());

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', 'string', Rule::in($assignableRoles)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'role.in' => 'You are not authorized to assign this role.',
        ];
    }
}
