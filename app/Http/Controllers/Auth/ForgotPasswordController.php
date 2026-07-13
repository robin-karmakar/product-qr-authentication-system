<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Responses\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    use ApiResponseTrait;

    public function create(): View
    {
        return view('auth.passwords.email');
    }

    public function store(ForgotPasswordRequest $request): JsonResponse|RedirectResponse
    {
        $status = Password::sendResetLink($request->only('email'));

        $message = $status === Password::RESET_LINK_SENT
            ? 'A password reset link has been sent to your email.'
            : 'Unable to send a reset link for that email address.';

        if ($request->expectsJson()) {
            return $status === Password::RESET_LINK_SENT
                ? $this->success(null, $message)
                : $this->error($message, 422);
        }

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', $message)
            : back()->withErrors(['email' => $message]);
    }
}
