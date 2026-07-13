<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Responses\ApiResponseTrait;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LoginController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private readonly AuthService $authService)
    {
    }

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): JsonResponse|RedirectResponse
    {
        $user = $this->authService->login(
            $request->credentials(),
            (bool) $request->boolean('remember')
        );

        $request->session()->regenerate();

        $redirectTo = route('dashboard');

        if ($request->expectsJson()) {
            return $this->success(['redirect' => $redirectTo], 'Login successful.');
        }

        return redirect()->intended($redirectTo);
    }
}
