<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    /**
     * Added in Module 3.2.1 now that Product exists. Used both for
     * eager-loading a category's products where needed, and as the
     * relationship backing ProductCategoryService's deletion guard
     * (via ProductRepository::existsForCategory(), not this relation
     * directly, to keep the guard in the repository/service layer
     * rather than querying through the model in the service).
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
