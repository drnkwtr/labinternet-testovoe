<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Core\Http\Controllers\ApiController;
use App\Http\Requests\Contact\StoreContactRequest;
use App\Jobs\ProcessContactSubmissionJob;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ContactController extends ApiController
{
    public function store(StoreContactRequest $request): JsonResponse
    {
        ProcessContactSubmissionJob::dispatch($request->toDto());

        return response()->json([
            'message' => 'Обращение принято в обработку.', // can be returned as json (if we'll leave job idea) or translated via messages.php
        ], ResponseAlias::HTTP_ACCEPTED);
    }
}
