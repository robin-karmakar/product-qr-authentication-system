<?php

declare(strict_types=1);

use App\Models\ProductCategory;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

it('allows a company admin to manage categories', function () {
    $companyAdmin = User::factory()->create();
    $companyAdmin->assignRole('company_admin');

    $response = $this->actingAs($companyAdmin)->get(route('admin.categories.index'));

    $response->assertOk();
});

it('denies a distributor access to category management', function () {
    $distributor = User::factory()->create();
    $distributor->assignRole('distributor');

    $response = $this->actingAs($distributor)->get(route('admin.categories.index'));

    $response->assertForbidden();
});

it('denies a retailer from creating a category', function () {
    $retailer = User::factory()->create();
    $retailer->assignRole('retailer');

    $response = $this->actingAs($retailer)->postJson(route('admin.categories.store'), [
        'name' => 'Unauthorized Category',
    ]);

    $response->assertForbidden();
});

it('denies an unauthenticated guest access to category management', function () {
    $response = $this->get(route('admin.categories.index'));

    $response->assertRedirect(route('login'));
});

it('denies a distributor from deleting a category', function () {
    $distributor = User::factory()->create();
    $distributor->assignRole('distributor');

    $category = ProductCategory::factory()->create();

    $response = $this->actingAs($distributor)->deleteJson(route('admin.categories.destroy', $category->uuid));

    $response->assertForbidden();
});
