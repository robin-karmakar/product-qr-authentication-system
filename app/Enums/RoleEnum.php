<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Type-safe representation of the five system roles.
 *
 * The source of truth for authorization is Spatie's roles/permissions
 * tables; this enum exists so application code never references role
 * names as raw magic strings and gets IDE/static-analysis safety.
 */
enum RoleEnum: string
{
    case SUPER_ADMIN = 'super_admin';
    case COMPANY_ADMIN = 'company_admin';
    case DISTRIBUTOR = 'distributor';
    case RETAILER = 'retailer';
    case CONSUMER = 'consumer';

    /**
     * Human-readable label for UI display.
     */
    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Admin',
            self::COMPANY_ADMIN => 'Company Admin',
            self::DISTRIBUTOR => 'Distributor',
            self::RETAILER => 'Retailer',
            self::CONSUMER => 'Consumer',
        };
    }

    /**
     * Roles permitted to manage the supply-chain custody flow.
     *
     * @return array<int, self>
     */
    public static function custodyRoles(): array
    {
        return [self::DISTRIBUTOR, self::RETAILER];
    }

    /**
     * Roles permitted to manage product/batch/QR generation.
     *
     * @return array<int, self>
     */
    public static function managementRoles(): array
    {
        return [self::SUPER_ADMIN, self::COMPANY_ADMIN];
    }

    /**
     * All role values as a plain array, useful for validation rules
     * (e.g. Rule::in(RoleEnum::values())).
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $role) => $role->value, self::cases());
    }
}
