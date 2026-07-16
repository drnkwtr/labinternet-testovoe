<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Repositories\AbstractRepository;
use App\DTO\Contact\ContactDTO;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ContactRepository extends AbstractRepository
{
    public function query(): Builder
    {
        return Contact::query();
    }

    public function toDto(Model $model): ContactDTO
    {
        return ContactDTO::fromModel($model);
    }
}
