<?php

declare(strict_types=1);

namespace App\DTO\Contact;

use App\Core\Http\Requests\RequestDTOInterface;
use App\Core\Repositories\MappableInterface;
use Illuminate\Http\Request;

final readonly class StoreContactDTO implements MappableInterface, RequestDTOInterface
{
    public function __construct(
        public string $name,
        public string $phone,
        public string $email,
        public string $comment,
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->input('name'),
            phone: $request->input('phone'),
            email: $request->input('email'),
            comment: $request->input('comment'),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );
    }

    public function map(): array
    {
        return [
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'comment' => $this->comment,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
        ];
    }
}
