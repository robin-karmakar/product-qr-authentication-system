<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('super_admin');
});

it('blocks deleting a category that has products assigned to it', function () {
    $category = ProductCategory::factory()->create();
    Product::factory()->create(['product_category_id' => $category->id]);

    $response = $this->actingAs($this->admin)->deleteJson(route('admin.categories.destroy', $category->uuid));

    $response->assertStatus(422);
    $this->assertDatabaseHas('product_categories', ['id' => $category->id, 'deleted_at' => null]);
});

it('allows deleting a category with zero products', function () {
    $category = ProductCategory::factory()->create();

    $response = $this->actingAs($this->admin)->deleteJson(route('admin.categories.destroy', $category->uuid));

    $response->assertOk();
    $this->assertSoftDeleted('product_categories', ['id' => $category->id]);
});

it('allows deleting a category whose only products are already soft-deleted', function () {
    $category = ProductCategory::factory()->create();
    $product = Product::factory()->create(['product_category_id' => $category->id]);
    $product->delete();

    $response = $this->actingAs($this->admin)->deleteJson(route('admin.categories.destroy', $category->uuid));

    $response->assertOk();
    $this->assertSoftDeleted('product_categories', ['id' => $category->id]);
});
