# Testing Configuration Fix — Setup Notes

## What to copy

Copy these 3 files into your project, overwriting any existing copies:

```
tests/Pest.php        (new — this was the missing file causing the error)
tests/TestCase.php    (regenerated to guarantee correctness)
phpunit.xml           (regenerated — forces sqlite in-memory DB for tests)
```

## Required: install the sqlite PHP driver

`phpunit.xml` now runs tests against an in-memory SQLite database (fast,
isolated, no MySQL dependency for CI). Confirm the driver is available:

```bash
php -m | grep sqlite
```

If missing, install `php8.3-sqlite3` (or your OS equivalent) — no code
change needed on our side, this is an environment requirement.

## Manual check — composer.json (do NOT blindly overwrite this file)

I'm not regenerating your `composer.json` since I don't have your current
copy and overwriting it could silently drop dependencies you've since
added. Instead, open it and confirm these two things are present:

**1. `autoload-dev` must map the `Tests` namespace:**
```json
"autoload-dev": {
    "psr-4": {
        "Tests\\": "tests/"
    }
}
```
This is what makes `Tests\TestCase` (used in `tests/Pest.php`) resolvable
at all. If it's missing, add it, then run:
```bash
composer dump-autoload
```

**2. The `test` script should already be Laravel's default — no change needed:**
```json
"scripts": {
    "test": [
        "@php artisan config:clear --ansi",
        "@php artisan test"
    ]
}
```

## Run it

```bash
composer dump-autoload
php artisan test
```

All 4 Module 2 Feature test files should now pass:
`LoginTest`, `StaffUserCreationTest`, `RoleGatingTest`, `InactiveUserBlockedTest`.

## Why the existing 4 test files needed zero changes

They already use Pest 3–correct syntax (`uses(RefreshDatabase::class)`,
`it()`, `beforeEach()`, `expect()`). The failure was purely a missing
wiring file (`tests/Pest.php`), not a syntax or version incompatibility —
so per your instruction, they have not been regenerated.
