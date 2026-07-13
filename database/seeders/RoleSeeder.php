<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Full permission set for the system. Permissions for
     * not-yet-built modules are seeded now so the roles table is
     * complete from day one; enforcement middleware is wired to
     * routes as each owning module is built.
     *
     * @var array<int, string>
     */
    private const PERMISSIONS = [
        // Module 2 — Authentication & Authorization
        'manage-users',
        'manage-roles',

        // Module 3 — Company Profile
        'manage-company-profile',

        // Module 4/5 — Product Catalog, Batch & Unit / QR generation
        'manage-products',

        // Module 6 — Public Verification
        'verify-product',

        // Module 7 — Supply Chain Custody
        'scan-product',
        'confirm-custody-transfer',

        // Module 8 — Counterfeit Reporting
        'report-counterfeit',

        // Module 9 — Admin Dashboard & Analytics
        'view-all-traceability',
    ];

    /**
     * @var array<string, array<int, string>>
     */
    private const ROLE_PERMISSIONS = [
        'super_admin' => [
            'manage-users',
            'manage-roles',
            'manage-company-profile',
            'manage-products',
            'view-all-traceability',
        ],
        'company_admin' => [
            'manage-company-profile',
            'manage-products',
            'view-all-traceability',
            'manage-users', // scoped to distributor/retailer creation, enforced in Form Request
        ],
        'distributor' => [
            'scan-product',
            'confirm-custody-transfer',
        ],
        'retailer' => [
            'scan-product',
            'confirm-custody-transfer',
        ],
        'consumer' => [
            'verify-product',
            'report-counterfeit',
        ],
    ];

    public function run(): void
    {
        foreach (self::PERMISSIONS as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach (RoleEnum::cases() as $roleEnum) {
            $role = Role::firstOrCreate(['name' => $roleEnum->value, 'guard_name' => 'web']);
            $role->syncPermissions(self::ROLE_PERMISSIONS[$roleEnum->value]);
        }
    }
}
