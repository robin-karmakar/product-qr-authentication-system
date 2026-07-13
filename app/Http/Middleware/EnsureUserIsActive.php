<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Checked on every authenticated request, not just at login, so
     * a mid-session deactivation (e.g. a Company Admin offboarding a
     * Distributor) takes effect immediately rather than waiting for
     * the user's session to naturally expire.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            auth()->guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This account has been deactivated. Contact your administrator.',
                ], 403);
            }

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'This account has been deactivated. Contact your administrator.']);
        }

        return $next($request);
    }
}
