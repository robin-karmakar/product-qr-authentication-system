<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Single entry point after login. Redirects by role rather than
     * exposing role-specific dashboard routes directly to the login
     * flow, so adding/renaming a role's dashboard URL later never
     * requires touching the auth layer.
     */
    public function redirect(Request $request): RedirectResponse
    {
        $user = $request->user();

        return match (true) {
            $user->hasRole(RoleEnum::SUPER_ADMIN->value) => redirect()->route('admin.users.index'),
            $user->hasRole(RoleEnum::COMPANY_ADMIN->value) => redirect()->route('admin.users.index'),
            $user->hasRole(RoleEnum::DISTRIBUTOR->value) => redirect('/custody/dashboard'),
            $user->hasRole(RoleEnum::RETAILER->value) => redirect('/custody/dashboard'),
            default => redirect('/'),
        };
    }
}
