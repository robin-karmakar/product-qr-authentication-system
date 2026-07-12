# Module 1 — Project Foundation: Setup Instructions

This module was generated as standalone files to drop into a fresh Laravel 12
install (Laravel is not scaffolded here since it requires Packagist access
your local machine has and this sandbox does not).

## 1. Create the project locally

```bash
composer create-project laravel/laravel spats "12.*"
cd spats
```

## 2. Install required packages

```bash
composer require spatie/laravel-permission
composer require simplesoftwareio/simple-qrcode
composer require laravel/sanctum   # ships with Laravel 12 by default, confirm it's present

composer require --dev pestphp/pest --with-all-dependencies
composer require --dev pestphp/pest-plugin-laravel
php artisan pest:install
```

## 3. Publish Spatie's config/migrations

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

## 4. Copy the generated files into your project

Copy each file from this module into the matching path in your Laravel
install, overwriting the default `bootstrap/app.php`:

```
app/Traits/HasUuid.php
app/Enums/RoleEnum.php
app/Repositories/Contracts/BaseRepositoryInterface.php
app/Repositories/Eloquent/BaseRepository.php
app/Services/BaseService.php
app/Http/Responses/ApiResponseTrait.php
app/Exceptions/ApiException.php
bootstrap/app.php     (overwrite existing)
```

## 5. .env additions

No new required env vars for this module. Module 2 (Authentication) will
add `SANCTUM_STATEFUL_DOMAINS` and role-seeding configuration.

## 6. Verify

```bash
php artisan test
```

Should run with zero tests found but no errors — confirms Pest is wired up
correctly before we start adding feature tests in later modules.

---

**Design notes recap:**
- `HasUuid` — attach to every model that is publicly routable. Never attach
  it to purely internal pivot/log tables that are only ever queried by
  foreign key from trusted server-side code.
- `BaseRepository` / `BaseService` — every future module's repository and
  service extends these; do not bypass them by calling Eloquent directly
  from controllers.
- `ApiResponseTrait` — apply to every controller that returns JSON
  (all AJAX endpoints, all API controllers).
- `ApiException` — throw this from service-layer business rule violations
  instead of returning ad-hoc error arrays.
