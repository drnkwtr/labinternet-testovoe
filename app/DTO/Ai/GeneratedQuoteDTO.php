<?php

declare(strict_types=1);

namespace App\DTO\Ai;

use App\Types\QuoteSource;

final readonly class GeneratedQuoteDTO
{
    public function __construct(
        public string $text,
        public QuoteSource $source,
    ) {
    }
}
