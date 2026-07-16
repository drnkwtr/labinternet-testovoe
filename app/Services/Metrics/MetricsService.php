<?php

declare(strict_types=1);

namespace App\Services\Metrics;

use App\DTO\Metrics\ContactMetricsDTO;
use App\Repositories\ContactRepository;
use Illuminate\Support\Carbon;

final readonly class MetricsService
{
    public function __construct(
        private ContactRepository $contacts,
    ) {
    }

    public function contactMetrics(): ContactMetricsDTO
    {
        $now = Carbon::now();

        return new ContactMetricsDTO(
            total: $this->contacts->total(),
            today: $this->contacts->countSince($now->copy()->startOfDay()),
            last7Days: $this->contacts->countSince($now->copy()->subDays(7)),
            lastContactAt: $this->contacts->lastCreatedAt(),
        );
    }
}
