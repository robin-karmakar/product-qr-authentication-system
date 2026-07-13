<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Responses\ApiResponseTrait;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ResetPasswordController extends Controller
{
    use ApiResponseTrait;

    public function create(string $token): View
    {
        return view('auth.passwords.reset', [
            'token' => $token,
            'email' => request('email', ''),
        ]);
    }

    public function store(ResetPasswordRequest $request): JsonResponse|RedirectResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        $success = $status === Password::PASSWORD_RESET;
        $message = $success
            ? 'Your password has been set. You may now log in.'
            : 'This password reset link is invalid or has expired.';

        if ($request->expectsJson()) {
            return $success
                ? $this->success(['redirect' => route('login')], $message)
                : $this->error($message, 422);
        }

        return $success
            ? redirect()->route('login')->with('status', $message)
            : back()->withErrors(['email' => $message]);
    }
}
