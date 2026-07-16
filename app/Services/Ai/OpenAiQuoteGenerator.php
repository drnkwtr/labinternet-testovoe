<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\DTO\Ai\GeneratedQuoteDTO;
use App\Types\QuoteSource;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class OpenAiQuoteGenerator implements QuoteGenerator
{
    private const string SYSTEM_PROMPT = <<<'PROMPT'
        Ты подбираешь одну короткую вдохновляющую цитату на русском языке,
        уместную по смыслу комментария пользователя из формы обратной связи.
        Ответь только текстом цитаты и автором в скобках, без кавычек и пояснений.
        Не более 200 символов.
        PROMPT;

    public function generate(string $comment): GeneratedQuoteDTO
    {
        $key = config('services.openai.key');

        if (blank($key)) {
            throw new RuntimeException('OpenAI API key is not configured.');
        }

        $content = Http::baseUrl(config('services.openai.base_url'))
            ->withToken($key)
            ->timeout((int) config('services.openai.timeout'))
            ->post('/chat/completions', [
                'model' => config('services.openai.model'),
                'temperature' => 0.8,
                'max_tokens' => 120,
                'messages' => [
                    ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
                    ['role' => 'user', 'content' => $comment],
                ],
            ])
            ->throw()
            ->json('choices.0.message.content');

        $text = trim((string) $content);

        if (empty($text)) {
            throw new RuntimeException('OpenAI returned an empty quote.');
        }

        return new GeneratedQuoteDTO($text, QuoteSource::OPEN_AI);
    }
}
