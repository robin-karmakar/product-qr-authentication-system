# Module 2 — Authentication & Authorization: Setup Instructions

## 1. Copy files into your Laravel 12 project

Copy every file in this archive into the matching path, overwriting
`bootstrap/app.php`, `app/Providers/AppServiceProvider.php`,
`routes/web.php`, `routes/api.php`, `database/seeders/DatabaseSeeder.php`,
and `database/factories/UserFactory.php` (these already existed with
Laravel's defaults — Module 2 replaces them completely per project rules).

## 2. .env additions

```env
SPATS_SUPER_ADMIN_EMAIL=admin@spats.local
SPATS_SUPER_ADMIN_PASSWORD=ChangeMeImmediately123!

SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,127.0.0.1:8000,localhost:8000
SESSION_DOMAIN=localhost
```

Add the same two keys to `.env.example` with placeholder values (never a
real password) so the repo stays clean for anyone cloning it.

## 3. Run migrations and seed

```bash
php artisan migrate
php artisan db:seed
```

This runs, in order: Laravel's default `users`/`password_reset_tokens`/
`sessions` migrations → our `add_uuid_and_profile_fields_to_users_table`
→ Spatie's permission tables → Sanctum's `personal_access_tokens` →
`RoleSeeder` (5 roles + permissions) → `SuperAdminSeeder` (bootstrap
account from the `.env` values above).

## 4. Mail driver for local development

Staff account creation sends `StaffAccountCreatedNotification`. For local
testing without a real mailer, set in `.env`:

```env
MAIL_MAILER=log
```

and read the activation link from `storage/logs/laravel.log`, or use
Mailpit/Mailtrap for a visual inbox.

## 5. Run the test suite

```bash
php artisan test
```

All five Pest test files in `tests/Feature/Auth/` should pass:
`LoginTest`, `StaffUserCreationTest`, `RoleGatingTest`,
`InactiveUserBlockedTest`.

## 6. Manual verification checklist

1. Log in as the bootstrap Super Admin (`SPATS_SUPER_ADMIN_EMAIL`).
2. From `/admin/users`, create a Company Admin — confirm the activation
   email/log entry appears and the reset-password link sets a working
   password.
3. Log in as that Company Admin, create a Distributor and a Retailer.
4. Confirm the Company Admin's `/admin/users` list shows only accounts
   they created — not the Super Admin or other Company Admins.
5. Deactivate the Distributor while logged in as that Distributor in a
   second browser session — confirm their very next request is bounced
   to `/login` with the deactivation message.
6. Confirm attempting to deactivate a Super Admin returns a 422 and the
   account remains active.

---

**Module 2 complete.** Waiting for your approval before starting
**Module 3 — Company Profile Module**.
