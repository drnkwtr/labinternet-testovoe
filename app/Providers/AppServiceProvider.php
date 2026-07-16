<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('contact', static function (Request $request): Limit {
            $config = config('contact.rate_limit');

            return new Limit(
                key: (string) $request->ip(),
                maxAttempts: $config['max_attempts'],
                decaySeconds: $config['decay_seconds'],
            );
        });
    }
}
