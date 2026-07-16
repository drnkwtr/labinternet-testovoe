<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\ContactController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('contact', [ContactController::class, 'store'])->name('api.v1.contact.store');
});
