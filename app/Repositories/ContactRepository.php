<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Repositories\AbstractRepository;
use App\DTO\Contact\ContactDTO;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

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

    public function total(): int
    {
        return $this->query()->count();
    }

    public function countSince(Carbon $since): int
    {
        return $this->query()->where('created_at', '>=', $since)->count();
    }

    public function lastCreatedAt(): ?Carbon
    {
        return $this->query()->latest('created_at')->first(['created_at'])?->created_at;
    }
}
