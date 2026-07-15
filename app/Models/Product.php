<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProductStatus;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory, HasUuid, SoftDeletes;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'product_category_id',
        'name',
        'slug',
        'sku',
        'description',
        'status',
        'created_by',
    ];

    /**
     * `id` is intentionally hidden — the UUID is the only identifier
     * ever exposed publicly, per project policy. `created_by` is
     * hidden too, since it's internal audit metadata with no admin
     * UI use yet. `product_category_id` is deliberately NOT hidden:
     * unlike the public verification-facing parts of this system,
     * the admin panel is internal-only, and the category's internal
     * id is genuinely useful there (e.g. populating a category
     * `<select>` without an extra UUID-to-id resolution step).
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'id',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ProductStatus::class,
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Module 5 integration point
    |--------------------------------------------------------------------------
    | A `batches(): HasMany` relationship to ProductBatch will be added
    | here once Module 5 introduces batch/unit/QR generation. Not
    | present yet — deliberately out of scope for Module 3.
    */
}
