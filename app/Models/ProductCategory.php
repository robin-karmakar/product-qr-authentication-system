<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCategory extends Model
{
    /** @use HasFactory<\Database\Factories\ProductCategoryFactory> */
    use HasFactory, HasUuid, SoftDeletes;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    /**
     * `id` is intentionally hidden — the UUID is the only identifier
     * ever exposed publicly, per project policy.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope: only active categories — used to populate the
     * "create product" category dropdown so retired categories
     * can't be assigned to new products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Module 3.2 integration point
    |--------------------------------------------------------------------------
    | Once Product exists, a `products(): HasMany` relationship and a
    | corresponding `hasProducts()` deletion guard will be added here.
    | Deliberately absent in 3.1 since the `products` table does not
    | exist yet.
    */
}
