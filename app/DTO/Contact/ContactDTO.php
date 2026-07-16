<?php

declare(strict_types=1);

namespace App\DTO\Contact;

use App\Core\DTO\Models\AbstractModelDTO;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

final readonly class ContactDTO extends AbstractModelDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $phone,
        public string $email,
        public string $comment,
        public Carbon $createdAt,
    ) {
    }

    public static function fromModel(Model $model): self
    {
        return new self(
            id: $model->getAttribute('id'),
            name: $model->getAttribute('name'),
            phone: $model->getAttribute('phone'),
            email: $model->getAttribute('email'),
            comment: $model->getAttribute('comment'),
            createdAt: $model->getAttribute('created_at'),
        );
    }

    public function map(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'comment' => $this->comment,
            'created_at' => $this->createdAt,
        ];
    }
}
