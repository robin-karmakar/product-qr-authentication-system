<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Whether any non-deleted product references the given category.
     * Used by ProductCategoryService to block deleting a category
     * that still has products assigned to it. (Module 3.2.1)
     */
    public function existsForCategory(int $categoryId): bool;

    /**
     * Whether a given slug is already taken, optionally ignoring a
     * specific product's own id — same pattern as
     * ProductCategoryRepository::slugExists(), needed because slugs
     * are server-generated from the name rather than user-supplied,
     * so collisions are possible. (Module 3.2.2.1)
     */
    public function slugExists(string $slug, ?int $ignoreId = null): bool;
}
