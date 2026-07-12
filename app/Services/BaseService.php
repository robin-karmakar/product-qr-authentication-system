<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

abstract class BaseService
{
    public function __construct(protected BaseRepositoryInterface $repository)
    {
    }

    public function all(array $relations = []): Collection
    {
        return $this->repository->all($relations);
    }

    public function paginate(int $perPage = 15, array $relations = []): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $relations);
    }

    public function findByUuid(string $uuid, array $relations = []): ?Model
    {
        return $this->repository->findByUuid($uuid, $relations);
    }

    public function findByUuidOrFail(string $uuid, array $relations = []): Model
    {
        return $this->repository->findByUuidOrFail($uuid, $relations);
    }

    public function create(array $data): Model
    {
        return $this->repository->create($data);
    }

    public function update(Model $model, array $data): Model
    {
        return $this->repository->update($model, $data);
    }

    public function delete(Model $model): bool
    {
        return $this->repository->delete($model);
    }

    /**
     * Run a callback inside a DB transaction. Every service method
     * that performs multi-step writes (e.g. batch + unit + QR
     * generation) must be wrapped through this helper.
     */
    protected function transaction(\Closure $callback): mixed
    {
        return DB::transaction($callback);
    }
}
