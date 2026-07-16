<?php

declare(strict_types=1);

return [

    'owner_email' => env('CONTACT_OWNER_EMAIL', 'owner@example.com'),

    'rate_limit' => [
        'max_attempts' => (int) env('CONTACT_RATE_LIMIT_MAX', 5),
        'decay_seconds' => (int) env('CONTACT_RATE_LIMIT_DECAY', 60),
    ],

];
