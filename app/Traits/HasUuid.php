<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Adds a UUID-based public identifier to a model and forces
 * route-model binding to resolve by that UUID instead of the
 * internal auto-incrementing primary key.
 *
 * Requirement: the model's table must have a `uuid` column,
 * typically defined as: $table->uuid('uuid')->unique();
 */
trait HasUuid
{
    /**
     * Boot the trait and attach the UUID generation hook.
     */
    protected static function bootHasUuid(): void
    {
        static::creating(function (Model $model): void {
            if (empty($model->{$model->uuidColumn()})) {
                $model->{$model->uuidColumn()} = (string) Str::uuid();
            }
        });
    }

    /**
     * The column name used to store the UUID. Override on the
     * model if a different column name is required.
     */
    public function uuidColumn(): string
    {
        return 'uuid';
    }

    /**
     * Resolve route-model binding using the UUID column,
     * never the internal database id.
     */
    public function getRouteKeyName(): string
    {
        return $this->uuidColumn();
    }

    /**
     * Explicitly block mass-assignment or accidental exposure
     * of the internal id in API responses built off this model.
     */
    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        return $query->where($field ?? $this->uuidColumn(), $value);
    }
}
