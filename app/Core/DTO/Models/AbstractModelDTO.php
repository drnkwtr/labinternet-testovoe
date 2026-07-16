<?php

declare(strict_types=1);

namespace App\Core\DTO\Models;

use App\Core\Repositories\MappableInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract readonly class AbstractModelDTO implements MappableInterface, ModelDTOInterface
{
    abstract public static function fromModel(Model $model): self;

    abstract public function map(): array;

    public static function fromCollection(Collection $collection): Collection
    {
        return $collection->map(fn (Model $model) => static::fromModel($model));
    }
}
