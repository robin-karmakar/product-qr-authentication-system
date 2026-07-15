<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Whether any non-deleted product references the given category.
     * Used by ProductCategoryService to block deleting a category
     * that still has products assigned to it.
     *
     * Search/filter methods are added in Module 3.2.3 — this
     * submodule (3.2.1) is foundation-only.
     */
    public function existsForCategory(int $categoryId): bool;
}
