<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\ProductCategory;
use App\Repositories\Contracts\ProductCategoryRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ProductCategoryService extends BaseService
{
    /**
     * $categoryRepository and $productRepository are both stored
     * under distinct, non-inherited property names — never
     * $repository — per the lesson from AuthService's original bug:
     * redeclaring BaseService's inherited typed $repository property
     * with a narrower type is a fatal error in PHP.
     */
    public function __construct(
        protected ProductCategoryRepositoryInterface $categoryRepository,
        protected ProductRepositoryInterface $productRepository,
    ) {
        parent::__construct($categoryRepository);
    }

    /**
     * @return Collection<int, ProductCategory>
     */
    public function listCategories(): Collection
    {
        return $this->categoryRepository->allOrdered();
    }

    /**
     * @param array{name: string, description?: string|null, is_active?: bool} $data
     */
    public function createCategory(array $data): ProductCategory
    {
        $data['slug'] = $this->generateUniqueSlug($data['name']);

        /** @var ProductCategory $category */
        $category = $this->categoryRepository->create($data);

        return $category;
    }

    /**
     * Deliberately named updateCategory(), not update() — see
     * ProductCategoryService's original audit note: overriding
     * BaseService::update(Model $model, array $data): Model with a
     * narrower ProductCategory parameter type is a fatal
     * "Declaration must be compatible" error at class-load time.
     * A distinct method name avoids the override entirely.
     *
     * @param array{name: string, description?: string|null, is_active?: bool} $data
     */
    public function updateCategory(ProductCategory $category, array $data): ProductCategory
    {
        if ($data['name'] !== $category->name) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], $category->id);
        }

        /** @var ProductCategory $updated */
        $updated = $this->categoryRepository->update($category, $data);

        return $updated;
    }

    /**
     * Same naming reasoning as updateCategory() — avoids narrowing
     * BaseService::delete(Model $model): bool.
     *
     * The guard below was deferred from Module 3.1 (flagged there
     * with a TODO) because it depends on the `products` table, which
     * didn't exist yet. Now that ProductRepositoryInterface exists,
     * it's implemented here. Note this guard only protects against
     * the soft-delete path used by this application — it does not
     * rely on the DB's `restrictOnDelete()` foreign key, since that
     * constraint only fires on a genuine SQL DELETE, and soft
     * deleting a category is just an UPDATE of `deleted_at`, which
     * never touches the FK at all.
     */
    public function deleteCategory(ProductCategory $category): bool
    {
        if ($this->productRepository->existsForCategory($category->id)) {
            throw new ApiException(
                'This category cannot be deleted because it has products assigned to it.',
                422
            );
        }

        return $this->categoryRepository->delete($category);
    }

    /**
     * Generates a URL-safe slug from the category name, appending a
     * numeric suffix (-2, -3, ...) if the base slug is already taken
     * — necessary because the slug is derived automatically rather
     * than user-supplied, so collisions ("Electronics" vs
     * "Electronics!") are possible and must be handled here.
     */
    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $suffix = 2;

        while ($this->categoryRepository->slugExists($slug, $ignoreId)) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
