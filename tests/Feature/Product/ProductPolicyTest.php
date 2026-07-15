<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

it('allows a super admin to manage products', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super_admin');

    $product = Product::factory()->create();

    expect($superAdmin->can('viewAny', Product::class))->toBeTrue();
    expect($superAdmin->can('create', Product::class))->toBeTrue();
    expect($superAdmin->can('update', $product))->toBeTrue();
    expect($superAdmin->can('delete', $product))->toBeTrue();
});

it('allows a company admin to manage products', function () {
    $companyAdmin = User::factory()->create();
    $companyAdmin->assignRole('company_admin');

    expect($companyAdmin->can('viewAny', Product::class))->toBeTrue();
    expect($companyAdmin->can('create', Product::class))->toBeTrue();
});

it('denies a distributor from managing products', function () {
    $distributor = User::factory()->create();
    $distributor->assignRole('distributor');

    $product = Product::factory()->create();

    expect($distributor->can('viewAny', Product::class))->toBeFalse();
    expect($distributor->can('create', Product::class))->toBeFalse();
    expect($distributor->can('update', $product))->toBeFalse();
    expect($distributor->can('delete', $product))->toBeFalse();
});

it('denies a retailer from managing products', function () {
    $retailer = User::factory()->create();
    $retailer->assignRole('retailer');

    expect($retailer->can('viewAny', Product::class))->toBeFalse();
});
