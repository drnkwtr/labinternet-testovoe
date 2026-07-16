<?php

declare(strict_types=1);

namespace App\Core\DTO\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface ModelDTOInterface
{
    public static function fromModel(Model $model): self;

    public static function fromCollection(Collection $collection): Collection;
}
