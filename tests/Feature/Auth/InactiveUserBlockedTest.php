<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

it('logs out and blocks a user the moment their account is deactivated', function () {
    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole('distributor');

    $this->actingAs($user);

    $user->update(['is_active' => false]);

    $response = $this->get('/dashboard');

    $response->assertRedirect(route('login'));
    $this->assertGuest();
});

it('prevents a super admin from being deactivated', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super_admin');

    $anotherAdmin = User::factory()->create();
    $anotherAdmin->assignRole('super_admin');

    $response = $this->actingAs($anotherAdmin)->patchJson("/admin/users/{$superAdmin->uuid}/deactivate");

    $response->assertStatus(422);
    expect($superAdmin->fresh()->is_active)->toBeTrue();
});
