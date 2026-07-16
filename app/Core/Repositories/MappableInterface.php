<?php

declare(strict_types=1);

namespace App\Core\Repositories;

interface MappableInterface
{
    public function map(): array;
}
