<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class BaseRepository implements BaseRepositoryInterface
{
    public function __construct(protected Model $model)
    {
    }

    public function all(array $relations = []): Collection
    {
        return $this->model->newQuery()->with($relations)->get();
    }

    public function paginate(int $perPage = 15, array $relations = []): LengthAwarePaginator
    {
        return $this->model->newQuery()->with($relations)->paginate($perPage);
    }

    public function find(int $id, array $relations = []): ?Model
    {
        return $this->model->newQuery()->with($relations)->find($id);
    }

    public function findByUuid(string $uuid, array $relations = []): ?Model
    {
        return $this->model->newQuery()->with($relations)->where('uuid', $uuid)->first();
    }

    public function findByUuidOrFail(string $uuid, array $relations = []): Model
    {
        $record = $this->findByUuid($uuid, $relations);

        if (! $record) {
            throw new NotFoundHttpException(class_basename($this->model).' not found.');
        }

        return $record;
    }

    public function create(array $data): Model
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(Model $model, array $data): Model
    {
        $model->fill($data);
        $model->save();

        return $model->refresh();
    }

    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }

    /**
     * Expose the underlying query builder for repository-specific
     * scopes defined in child classes.
     */
    protected function query()
    {
        return $this->model->newQuery();
    }
}
