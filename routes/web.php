<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Root Route
|--------------------------------------------------------------------------
| No public landing page exists in this system yet — every visitor is
| sent to the login screen. Revisit this once a public verification
| landing page is built (Module 6).
*/
Route::get('/', fn () => redirect()->route('login'))->name('home');

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
| No public registration exists in this system — staff accounts are
| created exclusively by an authorized admin (see routes/admin group
| below). Only login and password reset are open to guests.
*/
Route::middleware('guest')->group(function (): void {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']);

    Route::get('forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');

    Route::get('reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'store'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
| 'active' runs on every request in this group so a deactivation takes
| effect immediately, not just at next login.
*/
Route::middleware(['auth', 'active'])->group(function (): void {
    Route::post('logout', [LogoutController::class, 'destroy'])->name('logout');
    Route::get('dashboard', [DashboardController::class, 'redirect'])->name('dashboard');

    // Distributor / Retailer placeholder — full custody flow arrives in Module 7.
    Route::middleware('role:distributor|retailer')->group(function (): void {
        Route::view('/custody/dashboard', 'custody.placeholder')->name('custody.dashboard');
    });

    // Product catalog administration — Super Admin and Company Admin
    // only, gated by the 'manage-products' permission (also enforced
    // again at the policy layer inside each controller action).
    Route::middleware(['role:super_admin|company_admin', 'permission:manage-products'])
        ->prefix('admin')
        ->name('admin.')
        ->group(function (): void {
            Route::get('categories', [ProductCategoryController::class, 'index'])->name('categories.index');
            Route::post('categories', [ProductCategoryController::class, 'store'])->name('categories.store');
            Route::put('categories/{category}', [ProductCategoryController::class, 'update'])->name('categories.update');
            Route::delete('categories/{category}', [ProductCategoryController::class, 'destroy'])->name('categories.destroy');
        });

    // Staff account management — Super Admin manages everyone,
    // Company Admin manages only their own Distributors/Retailers.
    Route::middleware(['role:super_admin|company_admin', 'permission:manage-users'])
        ->prefix('admin')
        ->name('admin.')
        ->group(function (): void {
            Route::get('users', [UserManagementController::class, 'index'])->name('users.index');
            Route::post('users', [UserManagementController::class, 'store'])->name('users.store');
            Route::patch('users/{user}/activate', [UserManagementController::class, 'activate'])->name('users.activate');
            Route::patch('users/{user}/deactivate', [UserManagementController::class, 'deactivate'])->name('users.deactivate');
        });
});
