<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use App\Notifications\StaffAccountCreatedNotification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

it('allows a super admin to create a company admin', function () {
    Notification::fake();

    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super_admin');

    $response = $this->actingAs($superAdmin)->postJson(route('admin.users.store'), [
        'name' => 'Jane Manufacturer',
        'email' => 'jane@example.com',
        'role' => 'company_admin',
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);

    $created = User::where('email', 'jane@example.com')->first();
    expect($created->hasRole('company_admin'))->toBeTrue();

    Notification::assertSentTo($created, StaffAccountCreatedNotification::class);
});

it('prevents a company admin from creating another company admin', function () {
    $companyAdmin = User::factory()->create();
    $companyAdmin->assignRole('company_admin');

    $response = $this->actingAs($companyAdmin)->postJson(route('admin.users.store'), [
        'name' => 'Rogue Admin',
        'email' => 'rogue@example.com',
        'role' => 'company_admin',
    ]);

    $response->assertStatus(422);
    $this->assertDatabaseMissing('users', ['email' => 'rogue@example.com']);
});

it('allows a company admin to create a distributor', function () {
    Notification::fake();

    $companyAdmin = User::factory()->create();
    $companyAdmin->assignRole('company_admin');

    $response = $this->actingAs($companyAdmin)->postJson(route('admin.users.store'), [
        'name' => 'Regional Distributor',
        'email' => 'dist@example.com',
        'role' => 'distributor',
    ]);

    $response->assertCreated();

    $created = User::where('email', 'dist@example.com')->first();
    expect($created->created_by)->toBe($companyAdmin->id);
    expect($created->hasRole('distributor'))->toBeTrue();
});

it('rejects staff creation from a distributor', function () {
    $distributor = User::factory()->create();
    $distributor->assignRole('distributor');

    $response = $this->actingAs($distributor)->postJson(route('admin.users.store'), [
        'name' => 'Someone',
        'email' => 'someone@example.com',
        'role' => 'retailer',
    ]);

    $response->assertStatus(403);
});
