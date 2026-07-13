<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Binds every test in tests/Feature and tests/Unit to Tests\TestCase,
| which extends Illuminate\Foundation\Testing\TestCase. This is what
| makes $this->seed(), $this->actingAs(), $this->postJson(),
| assertDatabaseHas(), etc. available inside Pest's it()/test()
| closures. Without this binding, tests run against a bare PHPUnit
| test case with none of Laravel's testing helpers.
|
*/

uses(TestCase::class)->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Database Refresh
|--------------------------------------------------------------------------
|
| Applied to the whole Feature suite here rather than repeating
| uses(RefreshDatabase::class) in every file. Existing Feature test
| files that already declare it locally are unaffected — Pest simply
| composes the trait once either way.
|
*/

uses(RefreshDatabase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeUuid', function () {
    return $this->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i');
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| Shared helper for logging in as a given role inside a test, used
| across the auth and (soon) product/catalog Feature suites.
|
*/

function actingAsRole(string $role): App\Models\User
{
    $user = App\Models\User::factory()->create();
    $user->assignRole($role);

    test()->actingAs($user);

    return $user;
}
