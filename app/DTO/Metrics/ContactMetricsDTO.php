<?php

declare(strict_types=1);

namespace App\DTO\Metrics;

use App\Core\Repositories\MappableInterface;
use Illuminate\Support\Carbon;

final readonly class ContactMetricsDTO implements MappableInterface
{
    public function __construct(
        public int $total,
        public int $today,
        public int $last7Days,
        public ?Carbon $lastContactAt,
    ) {
    }

    public function map(): array
    {
        return [
            'total' => $this->total,
            'today' => $this->today,
            'last_7_days' => $this->last7Days,
            'last_contact_at' => $this->lastContactAt,
        ];
    }
}
