<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface BaseRepositoryInterface
{
    /**
     * Retrieve all records, optionally with relations eager-loaded.
     *
     * @param array<int, string> $relations
     */
    public function all(array $relations = []): Collection;

    /**
     * Paginate records.
     *
     * @param array<int, string> $relations
     */
    public function paginate(int $perPage = 15, array $relations = []): LengthAwarePaginator;

    /**
     * Find a record by its internal id. Prefer findByUuid for
     * anything crossing an application boundary.
     *
     * @param array<int, string> $relations
     */
    public function find(int $id, array $relations = []): ?Model;

    /**
     * Find a record by its public UUID.
     *
     * @param array<int, string> $relations
     */
    public function findByUuid(string $uuid, array $relations = []): ?Model;

    /**
     * Find a record by UUID or throw a 404.
     *
     * @param array<int, string> $relations
     */
    public function findByUuidOrFail(string $uuid, array $relations = []): Model;

    /**
     * Create a new record.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Model;

    /**
     * Update an existing record.
     *
     * @param array<string, mixed> $data
     */
    public function update(Model $model, array $data): Model;

    /**
     * Delete a record (soft delete if the model supports it).
     */
    public function delete(Model $model): bool;
}
