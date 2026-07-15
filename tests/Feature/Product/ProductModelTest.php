<?php

declare(strict_types=1);

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('belongs to a product category', function () {
    $category = ProductCategory::factory()->create();
    $product = Product::factory()->create(['product_category_id' => $category->id]);

    expect($product->category)->not->toBeNull();
    expect($product->category->id)->toBe($category->id);
});

it('exposes the category relationship via uuid, and keeps product_category_id visible for internal admin use', function () {
    $category = ProductCategory::factory()->create();
    $product = Product::factory()->create(['product_category_id' => $category->id]);

    $array = $product->load('category')->toArray();

    expect($array)->not->toHaveKey('id');
    expect($array)->not->toHaveKey('created_by');
    expect($array)->toHaveKey('product_category_id');
    expect($array['product_category_id'])->toBe($category->id);
    expect($array['category'])->not->toHaveKey('id');
    expect($array['category'])->toHaveKey('uuid');
});

it('belongs to the user who created it', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);

    expect($product->createdBy)->not->toBeNull();
    expect($product->createdBy->id)->toBe($user->id);
});

it('casts status to the ProductStatus enum', function () {
    $product = Product::factory()->draft()->create();

    expect($product->status)->toBeInstanceOf(ProductStatus::class);
    expect($product->status)->toBe(ProductStatus::DRAFT);
});

it('resolves route bindings by uuid', function () {
    $product = Product::factory()->create();

    expect($product->getRouteKeyName())->toBe('uuid');
});

it('soft deletes a product', function () {
    $product = Product::factory()->create();

    $product->delete();

    expect($product->trashed())->toBeTrue();
    $this->assertSoftDeleted('products', ['id' => $product->id]);
});

it('produces a category with an inverse products relationship', function () {
    $category = ProductCategory::factory()->create();
    Product::factory()->count(2)->create(['product_category_id' => $category->id]);

    expect($category->products()->count())->toBe(2);
});
