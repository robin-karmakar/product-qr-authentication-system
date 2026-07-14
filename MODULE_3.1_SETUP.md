# Module 3.1 — Product Categories: Setup Instructions (Final)

## Files in this package

**New files — copy directly in:**
```
app/Models/ProductCategory.php
app/Repositories/Contracts/ProductCategoryRepositoryInterface.php
app/Repositories/Eloquent/ProductCategoryRepository.php
app/Services/ProductCategoryService.php
app/Policies/ProductCategoryPolicy.php
app/Http/Requests/Product/ProductCategoryRequest.php
app/Http/Controllers/Admin/ProductCategoryController.php
database/migrations/2025_02_01_000001_create_product_categories_table.php
database/factories/ProductCategoryFactory.php
resources/views/admin/categories/index.blade.php
tests/Feature/Product/ProductCategoryTest.php
tests/Feature/Product/ProductCategoryPolicyTest.php
```

**Modified files — overwrite completely (additive only, nothing removed):**
```
app/Http/Controllers/Controller.php      (added AuthorizesRequests trait)
app/Providers/AppServiceProvider.php     (added repository binding + policy registration)
routes/web.php                           (added admin/categories/* route group)
```

> If you already copied in the first Module 3.1 delivery, you only need to
> re-copy `app/Services/ProductCategoryService.php` and
> `app/Http/Controllers/Admin/ProductCategoryController.php` — those are
> the two files the second audit pass corrected (a fatal method-signature
> error that would have crashed every request touching the category
> service). Every other file is unchanged from the first delivery.

## Exact Artisan Commands

```bash
php artisan migrate
php artisan route:clear
php artisan config:clear
php artisan test --filter=ProductCategory
```

Expect **12 passing tests** (7 in `ProductCategoryTest`, 5 in `ProductCategoryPolicyTest`).

## Exact Git Commit Message

```
feat(module-3.1): add product category management

- Add ProductCategory model (UUID, soft deletes)
- Add ProductCategoryRepository/Interface (Repository pattern)
- Add ProductCategoryService with auto slug generation and
  collision handling
- Add ProductCategoryPolicy, registered explicitly in
  AppServiceProvider
- Add ProductCategoryRequest (shared create/update Form Request)
- Add Admin\ProductCategoryController (thin, policy-authorized)
- Add admin/categories AJAX management view
- Add ProductCategoryFactory and 12 Pest feature tests
- Extend base Controller with AuthorizesRequests trait
- Extend AppServiceProvider with new repository binding and
  policy registration
- Extend routes/web.php with admin/categories/* route group

No changes to existing Module 1/2 functionality — all changes to
those files are additive.
```

## Manual Verification Checklist

1. `php artisan migrate` — confirm `product_categories` table is created with no errors.
2. Log in as the bootstrap Super Admin, visit `/admin/categories`.
3. Create a category — confirm the slug is generated automatically (e.g. "Home Appliances" → `home-appliances`).
4. Create a second category whose name slugifies to the same base slug (e.g. "Electronics" then "Electronics!") — confirm the second one gets `-2` appended.
5. Try creating a category with a name that already exists exactly — confirm a validation error, not a server error.
6. Edit a category's name — confirm the slug regenerates to match.
7. Edit a category's description only (leave name unchanged) — confirm the slug does *not* change.
8. Delete a category — confirm it disappears from the list, and confirm in the DB that `deleted_at` is set rather than the row being removed.
9. Log in as a Distributor or Retailer — confirm `/admin/categories` returns 403 (both via browser and via a direct `curl`/Postman JSON request).
10. Log out completely and hit `/admin/categories` directly — confirm redirect to `/login`.

---

**Module 3.1 status: complete, fully audited (18/18 checks), 1 critical
bug found and fixed during the second audit pass.** Waiting for your
local test results before starting **Module 3.2 — Product Core**.
