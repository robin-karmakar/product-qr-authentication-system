<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ProductCategory;
use App\Repositories\Contracts\ProductCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ProductCategoryService extends BaseService
{
    /**
     * Stored under a distinct property name, not $repository — see
     * AuthService for the full explanation of why redeclaring an
     * inherited typed property with a narrower type is a fatal
     * error in PHP.
     */
    public function __construct(protected ProductCategoryRepositoryInterface $categoryRepository)
    {
        parent::__construct($categoryRepository);
    }

    /**
     * Named listCategories() rather than the inherited all() so this
     * class reads consistently — all() (from BaseService) remains
     * available too, untouched, for any generic use.
     *
     * @return Collection<int, ProductCategory>
     */
    public function listCategories(): Collection
    {
        return $this->categoryRepository->allOrdered();
    }

    /**
     * Named createCategory() for naming consistency with
     * updateCategory()/deleteCategory() below, even though create()
     * itself would have been safe to override here (only its return
     * type narrows, which is allowed — see updateCategory()'s
     * docblock for why the other two are not safe to override).
     *
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
     * Deliberately NOT named update() / does NOT override
     * BaseService::update(Model $model, array $data): Model.
     * Overriding it with (ProductCategory $category, array $data):
     * ProductCategory would narrow the parameter type from Model to
     * ProductCategory, which violates PHP's contravariance rule for
     * method parameters and is a fatal "Declaration must be
     * compatible" error at class-load time — not a warning, the
     * class fails to load at all. Using a distinct method name
     * avoids the override (and the error) entirely.
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
     * Same reasoning as updateCategory() — a distinct name avoids
     * narrowing BaseService::delete(Model $model): bool.
     *
     * NOTE: Module 3.2 will extend this method with a guard that
     * prevents deletion while the category has any non-deleted
     * products assigned to it. That check depends on the `products`
     * table, which does not exist in Module 3.1.
     */
    public function deleteCategory(ProductCategory $category): bool
    {
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
