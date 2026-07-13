<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

it('denies a retailer access to the admin user management route', function () {
    $retailer = User::factory()->create();
    $retailer->assignRole('retailer');

    $response = $this->actingAs($retailer)->get('/admin/users');

    $response->assertForbidden();
});

it('allows a super admin access to the admin user management route', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super_admin');

    $response = $this->actingAs($superAdmin)->get('/admin/users');

    $response->assertOk();
});

it('redirects distributors and retailers to the custody dashboard', function () {
    $distributor = User::factory()->create();
    $distributor->assignRole('distributor');

    $response = $this->actingAs($distributor)->get('/dashboard');

    $response->assertRedirect('/custody/dashboard');
});
