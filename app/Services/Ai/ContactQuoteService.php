<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\DTO\Ai\GeneratedQuoteDTO;
use Illuminate\Support\Facades\Log;
use Throwable;

final readonly class ContactQuoteService
{
    public function __construct(
        private OpenAiQuoteGenerator $primary,
        private HeuristicQuoteGenerator $fallback,
    ) {
    }

    public function forComment(string $comment): GeneratedQuoteDTO
    {
        try {
            return $this->primary->generate($comment);
        } catch (Throwable $e) {
            Log::warning('OpenAI quote generation failed, using heuristic fallback', [
                'error' => $e->getMessage(),
            ]);

            return $this->fallback->generate($comment);
        }
    }
}
