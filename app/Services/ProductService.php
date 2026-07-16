<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use App\Repositories\Contracts\ProductCategoryRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ProductService extends BaseService
{
    /**
     * $productRepository and $categoryRepository are both distinct,
     * non-inherited property names — never $repository — per the
     * lesson from AuthService's original bug: redeclaring
     * BaseService's inherited typed $repository property with a
     * narrower type is a fatal error in PHP.
     */
    public function __construct(
        protected ProductRepositoryInterface $productRepository,
        protected ProductCategoryRepositoryInterface $categoryRepository,
    ) {
        parent::__construct($productRepository);
    }

    /**
     * Plain listing, eager-loaded. No filtering/sorting logic here —
     * that's Module 3.2.3's search()/filter() responsibility.
     *
     * @return Collection<int, Product>
     */
    public function listProducts(): Collection
    {
        return $this->productRepository->all(['category']);
    }

    /**
     * Single product lookup with relations eager-loaded — used by
     * the future show() controller action.
     */
    public function getProductOrFail(string $uuid): Product
    {
        return $this->productRepository->findByUuidOrFail($uuid, ['category', 'createdBy']);
    }

    /**
     * Named createProduct(), not create() — overriding
     * BaseService::create(array $data): Model with a narrower
     * Product return type would actually be legal (covariant return
     * types are allowed), but the name is kept consistent with
     * updateProduct()/deleteProduct() below, which are NOT safe to
     * override, for a single predictable naming convention across
     * the class.
     *
     * @param array{category_uuid: string, name: string, sku: string, description?: string|null, status: string} $data
     */
    public function createProduct(array $data, User $actingUser): Product
    {
        return $this->transaction(function () use ($data, $actingUser) {
            $category = $this->resolveActiveCategory($data['category_uuid']);

            $payload = [
                'product_category_id' => $category->id,
                'name' => $data['name'],
                'slug' => $this->generateUniqueSlug($data['name']),
                'sku' => strtoupper($data['sku']),
                'description' => $data['description'] ?? null,
                'status' => $data['status'],
                'created_by' => $actingUser->id,
            ];

            /** @var Product $product */
            $product = $this->productRepository->create($payload);

            return $product;
        });
    }

    /**
     * Deliberately NOT named update() — overriding
     * BaseService::update(Model $model, array $data): Model with a
     * narrower Product parameter type is a fatal "Declaration must
     * be compatible" error at class-load time. A distinct method
     * name avoids the override entirely. Same reasoning for
     * deleteProduct() below.
     *
     * @param array{category_uuid: string, name: string, sku: string, description?: string|null, status: string} $data
     */
    public function updateProduct(Product $product, array $data): Product
    {
        return $this->transaction(function () use ($product, $data) {
            $category = $this->resolveActiveCategory($data['category_uuid']);

            $payload = [
                'product_category_id' => $category->id,
                'name' => $data['name'],
                'sku' => strtoupper($data['sku']),
                'description' => $data['description'] ?? null,
                'status' => $data['status'],
            ];

            if ($data['name'] !== $product->name) {
                $payload['slug'] = $this->generateUniqueSlug($data['name'], $product->id);
            }

            /** @var Product $updated */
            $updated = $this->productRepository->update($product, $payload);

            return $updated;
        });
    }

    public function deleteProduct(Product $product): bool
    {
        return $this->productRepository->delete($product);
    }

    /**
     * Resolves a category by its public UUID and enforces the
     * business rule that products may only be created/moved into an
     * active category. Goes through ProductCategoryRepositoryInterface
     * (already bound since Module 3.2.1) rather than the
     * ProductCategory model directly — Services never touch Eloquent
     * when a repository already exists.
     */
    private function resolveActiveCategory(string $categoryUuid): ProductCategory
    {
        $category = $this->categoryRepository->findByUuidOrFail($categoryUuid);

        if (! $category->is_active) {
            throw new ApiException('The selected category is not active.', 422);
        }

        return $category;
    }

    /**
     * Same slug-collision handling pattern as
     * ProductCategoryService::generateUniqueSlug().
     */
    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $suffix = 2;

        while ($this->productRepository->slugExists($slug, $ignoreId)) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
