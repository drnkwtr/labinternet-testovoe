<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Core\Http\Controllers\ApiController;
use App\Http\Requests\Health\HealthRequest;
use App\Services\Health\HealthService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class HealthController extends ApiController
{
    #[OA\Get(
        path: '/v1/health',
        summary: 'Проверка статуса сервиса и зависимостей (БД, кеш)',
        tags: ['Service'],
        responses: [
            new OA\Response(response: 200, description: 'Сервис работает'),
            new OA\Response(response: 503, description: 'Сервис деградирован (недоступна зависимость)'),
        ],
    )]
    public function index(HealthRequest $request, HealthService $health): JsonResponse
    {
        $status = $health->check();

        return response()->json(
            $status->map(),
            $status->healthy ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE,
        );
    }
}
