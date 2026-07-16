<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\MetricsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('contact', [ContactController::class, 'store'])
        ->middleware('throttle:contact')
        ->name('api.v1.contact.store');

    Route::get('health', [HealthController::class, 'index'])->name('api.v1.health');
    Route::get('metrics', [MetricsController::class, 'index'])->name('api.v1.metrics');
});
