<?php

declare(strict_types=1);

namespace App\Core\Repositories;

use App\Core\DTO\Models\ModelDTOInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractRepository
{
    abstract public function query(): Builder;

    abstract public function toDto(Model $model): ModelDTOInterface;

    public function store(MappableInterface|array $dto): ModelDTOInterface
    {
        return $this->toDto(
            $this->query()->create(
                $dto instanceof MappableInterface ? $dto->map() : $dto
            )
        );
    }
}
