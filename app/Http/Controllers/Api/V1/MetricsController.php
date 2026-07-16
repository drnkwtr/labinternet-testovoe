<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Core\Http\Controllers\ApiController;
use App\Http\Requests\Metrics\MetricsRequest;
use App\Http\Resources\ContactMetricsResource;
use App\Services\Metrics\MetricsService;
use OpenApi\Attributes as OA;

class MetricsController extends ApiController
{
    #[OA\Get(
        path: '/v1/metrics',
        summary: 'Статистика обращений',
        tags: ['Service'],
        responses: [
            new OA\Response(response: 200, description: 'Агрегированная статистика по обращениям'),
        ],
    )]
    public function index(MetricsRequest $request, MetricsService $metrics): ContactMetricsResource
    {
        return new ContactMetricsResource($metrics->contactMetrics());
    }
}
