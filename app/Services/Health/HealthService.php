<?php

declare(strict_types=1);

namespace App\Services\Health;

use App\DTO\Health\HealthStatusDTO;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

final readonly class HealthService
{
    public function check(): HealthStatusDTO
    {
        $checks = [
            'database' => $this->pingDatabase(),
            'cache' => $this->pingCache(),
        ];

        return new HealthStatusDTO(
            healthy: ! in_array(false, $checks, true),
            checks: $checks,
        );
    }

    private function pingDatabase(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function pingCache(): bool
    {
        try {
            $token = (string) Str::uuid();
            Cache::put('health:ping', $token, 5);

            return Cache::get('health:ping') === $token;
        } catch (Throwable) {
            return false;
        }
    }
}
