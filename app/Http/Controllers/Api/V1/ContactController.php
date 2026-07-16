<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Core\Http\Controllers\ApiController;
use App\Http\Requests\Contact\StoreContactRequest;
use App\Jobs\ProcessContactSubmissionJob;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class ContactController extends ApiController
{
    #[OA\Post(
        path: '/v1/contact',
        summary: 'Отправка формы обратной связи',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'phone', 'email', 'comment'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Иван Иванов', maxLength: 100, minLength: 2),
                    new OA\Property(property: 'phone', type: 'string', example: '+7 900 123-45-67', maxLength: 20, minLength: 5),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'ivan@example.com', maxLength: 255),
                    new OA\Property(property: 'comment', type: 'string', example: 'Здравствуйте, хочу обсудить проект.', maxLength: 2000, minLength: 5),
                ],
            ),
        ),
        tags: ['Contact'],
        responses: [
            new OA\Response(response: 204, description: 'Обращение принято в обработку'),
            new OA\Response(response: 422, description: 'Ошибка валидации'),
            new OA\Response(response: 429, description: 'Превышен лимит запросов (анти-спам)'),
        ],
    )]
    public function store(StoreContactRequest $request): Response
    {
        ProcessContactSubmissionJob::dispatch($request->toDto());

        return response()->noContent();
    }
}
