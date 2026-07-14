<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Collection;

interface ProductCategoryRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * All categories ordered by name, for the admin listing screen.
     *
     * @return Collection<int, ProductCategory>
     */
    public function allOrdered(): Collection;

    /**
     * Whether a given slug is already taken, optionally ignoring a
     * specific category's own id (used when regenerating a slug on
     * update so a category doesn't collide with itself).
     */
    public function slugExists(string $slug, ?int $ignoreId = null): bool;
}
