<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\DTO\Ai\GeneratedQuoteDTO;

interface QuoteGenerator
{
    public function generate(string $comment): GeneratedQuoteDTO;
}
