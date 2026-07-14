<?php

declare(strict_types=1);

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

it('lists categories for an authorized admin', function () {
    ProductCategory::factory()->count(3)->create();

    $response = $this->actingAs($this->admin)->get(route('admin.categories.index'));

    $response->assertOk();
});

it('creates a category with an auto-generated slug', function () {
    $response = $this->actingAs($this->admin)->postJson(route('admin.categories.store'), [
        'name' => 'Home Appliances',
        'description' => 'Kitchen and household electronics',
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('product_categories', [
        'name' => 'Home Appliances',
        'slug' => 'home-appliances',
    ]);
});

it('appends a numeric suffix when the generated slug collides', function () {
    ProductCategory::factory()->create(['name' => 'Electronics', 'slug' => 'electronics']);

    $response = $this->actingAs($this->admin)->postJson(route('admin.categories.store'), [
        'name' => 'Electronics!',
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('product_categories', ['slug' => 'electronics-2']);
});

it('rejects a duplicate category name', function () {
    ProductCategory::factory()->create(['name' => 'Beverages']);

    $response = $this->actingAs($this->admin)->postJson(route('admin.categories.store'), [
        'name' => 'Beverages',
    ]);

    $response->assertStatus(422);
});

it('updates a category and regenerates the slug when the name changes', function () {
    $category = ProductCategory::factory()->create(['name' => 'Old Name', 'slug' => 'old-name']);

    $response = $this->actingAs($this->admin)->putJson(route('admin.categories.update', $category->uuid), [
        'name' => 'New Name',
    ]);

    $response->assertOk();
    expect($category->fresh()->slug)->toBe('new-name');
});

it('soft deletes a category', function () {
    $category = ProductCategory::factory()->create();

    $response = $this->actingAs($this->admin)->deleteJson(route('admin.categories.destroy', $category->uuid));

    $response->assertOk();
    $this->assertSoftDeleted('product_categories', ['id' => $category->id]);
});

it('resolves categories by uuid, never by internal id', function () {
    $category = ProductCategory::factory()->create();

    $response = $this->actingAs($this->admin)->putJson(route('admin.categories.update', $category->uuid), [
        'name' => 'Renamed Category',
    ]);

    $response->assertOk();
});
