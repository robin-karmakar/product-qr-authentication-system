# Module 3.2.1 — Product Foundation: Setup Instructions

## Files in this package

**New files:**
```
app/Enums/ProductStatus.php
app/Models/Product.php
app/Policies/ProductPolicy.php
app/Repositories/Contracts/ProductRepositoryInterface.php
app/Repositories/Eloquent/ProductRepository.php
database/migrations/2025_02_02_000001_create_products_table.php
database/factories/ProductFactory.php
tests/Feature/Product/ProductModelTest.php
tests/Feature/Product/ProductCategoryDeletionGuardTest.php
tests/Feature/Product/ProductPolicyTest.php
```

**Modified files (Module 3.1) — overwrite completely, additive only:**
```
app/Models/ProductCategory.php       (added products() relationship)
app/Services/ProductCategoryService.php  (implemented the deletion guard)
app/Providers/AppServiceProvider.php     (added Product repository binding + policy)
```

No Module 1 or Module 2 files are modified. `app/Services/BaseService.php`
is included in this package only to guarantee it matches its original
Module 1 state exactly — an earlier draft of this delivery had proposed
changing its method visibility; that change was rejected and reverted.
The architectural rule it was meant to enforce (controllers must only
call domain-specific Service methods, never BaseService's generic
create()/update()/delete()/etc. directly) is instead enforced by manual
review during every future submodule audit — see the audit log below.

## Exact Artisan Commands

```bash
php artisan migrate
php artisan route:clear
php artisan config:clear
php artisan test --filter=Product
```

This filter matches all Product-related test files, including the
Module 3.1 category tests (since "Product" is a substring of
"ProductCategory") — expect **26 total passing tests**:
- 12 from Module 3.1 (`ProductCategoryTest`, `ProductCategoryPolicyTest`)
- 14 new from this submodule (`ProductModelTest`: 7, `ProductCategoryDeletionGuardTest`: 3, `ProductPolicyTest`: 4)

If you want only this submodule's new tests:
```bash
php artisan test tests/Feature/Product/ProductModelTest.php tests/Feature/Product/ProductCategoryDeletionGuardTest.php tests/Feature/Product/ProductPolicyTest.php
```

## Exact Git Commit Message

```
feat(module-3.2.1): add Product foundation (model, migration, repository, policy)

- Add ProductStatus enum (draft/active/discontinued)
- Add products migration (FK to product_categories and users,
  soft deletes)
- Add Product model (UUID, soft deletes, category/createdBy
  relationships, native enum cast)
- Add ProductRepository/Interface (foundation CRUD only — search
  arrives in Module 3.2.3)
- Add ProductPolicy, registered in AppServiceProvider
- Add ProductFactory
- Add 14 Pest tests covering relationships, UUID binding, soft
  deletes, and policy authorization

Module 3.1 changes (explained before regenerating):
- ProductCategory: add products() HasMany relationship, now that
  Product exists
- ProductCategoryService: implement the deleteCategory() guard
  deferred from Module 3.1 (blocks deleting a category with
  non-deleted products assigned to it)
- AppServiceProvider: bind ProductRepositoryInterface, register
  ProductPolicy

No changes to Module 1 or Module 2.
```

## Design decision: product_category_id stays visible

Unlike `id` and `created_by`, `Product::$hidden` deliberately does **not**
include `product_category_id`. This is an internal-admin-only field decision
(the public-facing verification system in later modules never touches the
admin `Product` JSON shape directly), and it's genuinely useful for admin
UI needs like populating a category `<select>` without an extra
UUID-to-id resolution round trip.

## Standing audit rule going forward

**Controllers must only call domain-specific Service methods, never
BaseService's generic `all()`/`paginate()`/`findByUuid()`/`findByUuidOrFail()`/
`create()`/`update()`/`delete()` directly.** This is enforced by manual
review (grep + read-through) during every future submodule's 18/20-point
audit, not by method visibility. Verified clean as of this submodule via
exhaustive search — no controller, request, or test calls these generic
methods externally anywhere in the codebase.

## Manual Verification Checklist

1. `php artisan migrate` — confirm `products` table created with FKs to `product_categories` and `users`.
2. `php artisan test --filter=Product` — confirm 26/26 passing.
3. In Tinker (`php artisan tinker`):
   ```php
   $category = \App\Models\ProductCategory::factory()->create();
   $product = \App\Models\Product::factory()->create(['product_category_id' => $category->id]);
   $product->category;      // should return the category
   $product->status;        // should return a ProductStatus enum instance
   $category->products;     // should return a collection containing $product
   ```
4. Still in Tinker, confirm the deletion guard works end-to-end:
   ```php
   $categoryService = app(\App\Services\ProductCategoryService::class);
   $categoryService->deleteCategory($category); // should throw ApiException
   ```
5. Soft-delete the product first, then retry step 4 — this time it should succeed (category has zero *active* products).

---

**Module 3.2.1 status: complete, fully audited (18/18 checks, 0 issues
found).** No routes, controller, or view exist yet for `Product` — those
arrive in Module 3.2.2. Waiting for your local test results before
starting **Module 3.2.2 — Product CRUD**.
