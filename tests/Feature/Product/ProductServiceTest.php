<?php

declare(strict_types=1);

use App\Enums\ProductStatus;
use App\Exceptions\ApiException;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(ProductService::class);
    $this->user = User::factory()->create();
    $this->category = ProductCategory::factory()->create(['is_active' => true]);
});

it('creates a product with an auto-generated slug and uppercased sku', function () {
    $product = $this->service->createProduct([
        'category_uuid' => $this->category->uuid,
        'name' => 'Premium Olive Oil',
        'sku' => 'oil-500',
        'description' => 'Cold pressed',
        'status' => ProductStatus::ACTIVE->value,
    ], $this->user);

    expect($product->slug)->toBe('premium-olive-oil');
    expect($product->sku)->toBe('OIL-500');
    expect($product->product_category_id)->toBe($this->category->id);
    expect($product->created_by)->toBe($this->user->id);
    expect($product->status)->toBe(ProductStatus::ACTIVE);
});

it('appends a numeric suffix when the generated slug collides', function () {
    Product::factory()->create([
        'name' => 'Widget',
        'slug' => 'widget',
        'product_category_id' => $this->category->id,
    ]);

    $product = $this->service->createProduct([
        'category_uuid' => $this->category->uuid,
        'name' => 'Widget!',
        'sku' => 'WID-002',
        'status' => ProductStatus::DRAFT->value,
    ], $this->user);

    expect($product->slug)->toBe('widget-2');
});

it('rejects creating a product against an inactive category', function () {
    $inactiveCategory = ProductCategory::factory()->inactive()->create();

    expect(fn () => $this->service->createProduct([
        'category_uuid' => $inactiveCategory->uuid,
        'name' => 'Bad Product',
        'sku' => 'BAD-001',
        'status' => ProductStatus::DRAFT->value,
    ], $this->user))->toThrow(ApiException::class);
});

it('rejects creating a product against a nonexistent category', function () {
    expect(fn () => $this->service->createProduct([
        'category_uuid' => (string) Str::uuid(),
        'name' => 'Orphan Product',
        'sku' => 'ORP-001',
        'status' => ProductStatus::DRAFT->value,
    ], $this->user))->toThrow(NotFoundHttpException::class);
});

it('updates a product and regenerates the slug when the name changes', function () {
    $product = Product::factory()->create([
        'product_category_id' => $this->category->id,
        'name' => 'Old Name',
        'slug' => 'old-name',
        'sku' => 'OLD-001',
    ]);

    $updated = $this->service->updateProduct($product, [
        'category_uuid' => $this->category->uuid,
        'name' => 'New Name',
        'sku' => 'new-001',
        'status' => ProductStatus::ACTIVE->value,
    ]);

    expect($updated->slug)->toBe('new-name');
    expect($updated->sku)->toBe('NEW-001');
});

it('does not regenerate the slug when the name is unchanged', function () {
    $product = Product::factory()->create([
        'product_category_id' => $this->category->id,
        'name' => 'Stable Name',
        'slug' => 'stable-name',
    ]);

    $updated = $this->service->updateProduct($product, [
        'category_uuid' => $this->category->uuid,
        'name' => 'Stable Name',
        'sku' => $product->sku,
        'status' => ProductStatus::ACTIVE->value,
    ]);

    expect($updated->slug)->toBe('stable-name');
});

it('moves a product to a different category on update', function () {
    $newCategory = ProductCategory::factory()->create(['is_active' => true]);
    $product = Product::factory()->create(['product_category_id' => $this->category->id]);

    $updated = $this->service->updateProduct($product, [
        'category_uuid' => $newCategory->uuid,
        'name' => $product->name,
        'sku' => $product->sku,
        'status' => ProductStatus::ACTIVE->value,
    ]);

    expect($updated->product_category_id)->toBe($newCategory->id);
});

it('rejects moving a product into an inactive category on update', function () {
    $inactiveCategory = ProductCategory::factory()->inactive()->create();
    $product = Product::factory()->create(['product_category_id' => $this->category->id]);

    expect(fn () => $this->service->updateProduct($product, [
        'category_uuid' => $inactiveCategory->uuid,
        'name' => $product->name,
        'sku' => $product->sku,
        'status' => ProductStatus::ACTIVE->value,
    ]))->toThrow(ApiException::class);
});

it('soft deletes a product', function () {
    $product = Product::factory()->create(['product_category_id' => $this->category->id]);

    $this->service->deleteProduct($product);

    $this->assertSoftDeleted('products', ['id' => $product->id]);
});

it('lists products with the category relationship eager loaded', function () {
    Product::factory()->count(2)->create(['product_category_id' => $this->category->id]);

    $products = $this->service->listProducts();

    expect($products)->toHaveCount(2);
    expect($products->first()->relationLoaded('category'))->toBeTrue();
});

it('retrieves a single product by uuid with relations eager loaded', function () {
    $product = Product::factory()->create([
        'product_category_id' => $this->category->id,
        'created_by' => $this->user->id,
    ]);

    $found = $this->service->getProductOrFail($product->uuid);

    expect($found->id)->toBe($product->id);
    expect($found->relationLoaded('category'))->toBeTrue();
    expect($found->relationLoaded('createdBy'))->toBeTrue();
});

it('throws a not found error for an unknown product uuid', function () {
    expect(fn () => $this->service->getProductOrFail((string) Str::uuid()))
        ->toThrow(NotFoundHttpException::class);
});
