<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\DTO\Ai\GeneratedQuoteDTO;
use App\Types\QuoteSource;

final class HeuristicQuoteGenerator implements QuoteGenerator
{
    private const array COLLAB_KEYWORDS = [
        'работ', 'проект', 'сотруднич', 'вакан', 'наня', 'нанять',
        'задач', 'разработ', 'заказ', 'предложен',
    ];

    private const array ISSUE_KEYWORDS = [
        'проблем', 'помощ', 'ошибк', 'не работает', 'баг',
        'срочн', 'жалоб', 'сломал', 'не могу',
    ];

    private const string QUOTE_COLLAB = 'Великие дела нужно совершать, а не обдумывать их без конца. (Юлий Цезарь)';
    private const string QUOTE_ISSUE = 'Наша величайшая слава не в том, чтобы никогда не падать, а в том, чтобы подниматься каждый раз, когда мы упали. (Конфуций)';
    private const string QUOTE_DEFAULT = 'Единственный способ делать великую работу — любить то, что ты делаешь. (Стив Джобс)';

    public function generate(string $comment): GeneratedQuoteDTO
    {
        $text = mb_strtolower($comment);
        $quote = match (true) {
            $this->matches($text, self::ISSUE_KEYWORDS) => self::QUOTE_ISSUE,
            $this->matches($text, self::COLLAB_KEYWORDS) => self::QUOTE_COLLAB,
            default => self::QUOTE_DEFAULT,
        };

        return new GeneratedQuoteDTO($quote, QuoteSource::FALLBACK);
    }

    private function matches(string $text, array $keywords): bool
    {
        return collect($keywords)
            ->map(fn (string $keyword): bool => str_contains($text, $keyword))
            ->contains(true);
    }
}
