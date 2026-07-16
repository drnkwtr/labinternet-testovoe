<?php

declare(strict_types=1);

namespace App\Http\Requests\Contact;

use App\Core\Http\Requests\AbstractRequest;
use App\DTO\Contact\StoreContactDTO;

class StoreContactRequest extends AbstractRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => ['required', 'string', 'min:5', 'max:20', 'regex:/^\+?[0-9\s\-()]+$/'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'comment' => ['required', 'string', 'min:5', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(
            collect(['name', 'phone', 'email', 'comment'])
                ->filter(fn (string $field): bool => is_string($this->input($field)))
                ->mapWithKeys(fn (string $field): array => [$field => trim($this->input($field))])
                ->all()
        );
    }

    public function toDto(): StoreContactDTO
    {
        return StoreContactDTO::fromRequest($this);
    }
}
