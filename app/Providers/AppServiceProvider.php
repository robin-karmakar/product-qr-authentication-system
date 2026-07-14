<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\ProductCategory;
use App\Policies\ProductCategoryPolicy;
use App\Repositories\Contracts\ProductCategoryRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\ProductCategoryRepository;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        UserRepositoryInterface::class => UserRepository::class,
        ProductCategoryRepositoryInterface::class => ProductCategoryRepository::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Explicit policy registration rather than relying purely on
        // Laravel's naming-convention auto-discovery — makes the
        // authorization wiring visible and grep-able as the number
        // of models grows across later modules.
        Gate::policy(ProductCategory::class, ProductCategoryPolicy::class);
    }
}
