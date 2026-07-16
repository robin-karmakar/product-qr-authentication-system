<?php

declare(strict_types=1);

use App\Enums\ProductStatus;
use App\Http\Requests\Product\StoreProductRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function validateStoreProduct(array $data): \Illuminate\Contracts\Validation\Validator
{
    $request = new StoreProductRequest();

    return Validator::make($data, $request->rules());
}

it('passes validation with valid data', function () {
    $category = ProductCategory::factory()->create(['is_active' => true]);

    $validator = validateStoreProduct([
        'category_uuid' => $category->uuid,
        'name' => 'Valid Product',
        'sku' => 'VAL-001',
        'status' => ProductStatus::ACTIVE->value,
    ]);

    expect($validator->passes())->toBeTrue();
});

it('fails when required fields are missing', function () {
    $validator = validateStoreProduct([]);

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('category_uuid'))->toBeTrue();
    expect($validator->errors()->has('name'))->toBeTrue();
    expect($validator->errors()->has('sku'))->toBeTrue();
    expect($validator->errors()->has('status'))->toBeTrue();
});

it('fails when the category does not exist', function () {
    $validator = validateStoreProduct([
        'category_uuid' => (string) Str::uuid(),
        'name' => 'Product',
        'sku' => 'ABC-001',
        'status' => ProductStatus::ACTIVE->value,
    ]);

    expect($validator->errors()->has('category_uuid'))->toBeTrue();
});

it('fails when the category is inactive', function () {
    $category = ProductCategory::factory()->inactive()->create();

    $validator = validateStoreProduct([
        'category_uuid' => $category->uuid,
        'name' => 'Product',
        'sku' => 'ABC-002',
        'status' => ProductStatus::ACTIVE->value,
    ]);

    expect($validator->errors()->has('category_uuid'))->toBeTrue();
});

it('fails on an invalid sku format', function () {
    $category = ProductCategory::factory()->create(['is_active' => true]);

    $validator = validateStoreProduct([
        'category_uuid' => $category->uuid,
        'name' => 'Product',
        'sku' => 'bad sku!!',
        'status' => ProductStatus::ACTIVE->value,
    ]);

    expect($validator->errors()->has('sku'))->toBeTrue();
});

it('fails on an invalid status value', function () {
    $category = ProductCategory::factory()->create(['is_active' => true]);

    $validator = validateStoreProduct([
        'category_uuid' => $category->uuid,
        'name' => 'Product',
        'sku' => 'ABC-003',
        'status' => 'not-a-real-status',
    ]);

    expect($validator->errors()->has('status'))->toBeTrue();
});

it('fails when the sku already exists', function () {
    $category = ProductCategory::factory()->create(['is_active' => true]);
    Product::factory()->create(['sku' => 'DUP-001', 'product_category_id' => $category->id]);

    $validator = validateStoreProduct([
        'category_uuid' => $category->uuid,
        'name' => 'Product',
        'sku' => 'DUP-001',
        'status' => ProductStatus::ACTIVE->value,
    ]);

    expect($validator->errors()->has('sku'))->toBeTrue();
});
