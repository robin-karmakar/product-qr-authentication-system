<?php

declare(strict_types=1);

use App\Enums\ProductStatus;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

/**
 * UpdateProductRequest::rules() calls $this->route('product') to
 * support ignoring the current product's own SKU during the unique
 * check. Without a real HTTP request/route (no controller/routes
 * exist until Module 3.2.2.2), that call safely resolves to null via
 * Laravel's default route resolver — equivalent to "ignore nothing".
 * These tests therefore cover every rule except the ignore-self
 * behavior specifically; that one detail gets an HTTP-level test once
 * real routes exist in 3.2.2.2.
 */
function validateUpdateProduct(array $data): \Illuminate\Contracts\Validation\Validator
{
    $request = new UpdateProductRequest();

    return Validator::make($data, $request->rules());
}

it('passes validation with valid data', function () {
    $category = ProductCategory::factory()->create(['is_active' => true]);

    $validator = validateUpdateProduct([
        'category_uuid' => $category->uuid,
        'name' => 'Valid Product',
        'sku' => 'VAL-002',
        'status' => ProductStatus::ACTIVE->value,
    ]);

    expect($validator->passes())->toBeTrue();
});

it('fails when required fields are missing', function () {
    $validator = validateUpdateProduct([]);

    expect($validator->fails())->toBeTrue();
});

it('fails when the category is inactive', function () {
    $category = ProductCategory::factory()->inactive()->create();

    $validator = validateUpdateProduct([
        'category_uuid' => $category->uuid,
        'name' => 'Product',
        'sku' => 'ABC-010',
        'status' => ProductStatus::ACTIVE->value,
    ]);

    expect($validator->errors()->has('category_uuid'))->toBeTrue();
});

it('fails on an invalid sku format', function () {
    $category = ProductCategory::factory()->create(['is_active' => true]);

    $validator = validateUpdateProduct([
        'category_uuid' => $category->uuid,
        'name' => 'Product',
        'sku' => 'nope!!',
        'status' => ProductStatus::ACTIVE->value,
    ]);

    expect($validator->errors()->has('sku'))->toBeTrue();
});

it('fails on an invalid status value', function () {
    $category = ProductCategory::factory()->create(['is_active' => true]);

    $validator = validateUpdateProduct([
        'category_uuid' => $category->uuid,
        'name' => 'Product',
        'sku' => 'ABC-011',
        'status' => 'bogus',
    ]);

    expect($validator->errors()->has('status'))->toBeTrue();
});
