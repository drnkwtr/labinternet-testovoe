<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: 'Backend-сервис формы обратной связи лендинга: приём заявки, '
        .'валидация, очередь, email-уведомления и AI-цитата (OpenAI с эвристическим fallback).',
    title: 'Landing Contact API',
)]
#[OA\Server(url: '/api', description: 'API base URL')]
#[OA\Tag(name: 'Contact', description: 'Форма обратной связи')]
#[OA\Tag(name: 'Service', description: 'Служебные эндпоинты: health, metrics')]
abstract class Controller
{
    //
}
