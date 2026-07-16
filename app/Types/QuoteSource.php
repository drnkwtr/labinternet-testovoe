<?php

declare(strict_types=1);

namespace App\Types;

enum QuoteSource: string
{
    case OPEN_AI = 'openai';
    case FALLBACK = 'fallback';
}
