<?php

declare(strict_types=1);

namespace App\DTO\Health;

use App\Core\Repositories\MappableInterface;
use Illuminate\Support\Carbon;

final readonly class HealthStatusDTO implements MappableInterface
{
    public function __construct(
        public bool $healthy,
        public array $checks,
    ) {
    }

    public function map(): array
    {
        return [
            'status' => $this->healthy ? 'ok' : 'degraded',
            'checks' => $this->checks,
            'timestamp' => Carbon::now()->toIso8601String(),
        ];
    }
}
