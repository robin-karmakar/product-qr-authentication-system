<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Minimal stub proving Sanctum token auth is wired correctly. The
| full versioned API (v1) for the public verification endpoint and
| future mobile scanning app is built out starting Module 6/11.
*/
Route::middleware('auth:sanctum')->get('/v1/user', function (Request $request) {
    return response()->json([
        'success' => true,
        'message' => 'Authenticated user retrieved.',
        'data' => [
            'uuid' => $request->user()->uuid,
            'name' => $request->user()->name,
            'email' => $request->user()->email,
            'roles' => $request->user()->getRoleNames(),
        ],
    ]);
});
