<?php

use App\Http\Controllers\Api\DatasetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Open REST API (v1)
|--------------------------------------------------------------------------
|
| These endpoints are public - no login or token is required. Every route
| is scoped by {user_id} and every read is served through Redis.
| Throttled to 120 requests per minute per IP.
|
*/

Route::prefix('v1')->middleware('throttle:120,1')->group(function () {

    Route::get('/ping', fn () => response()->json([
        'success' => true,
        'service' => 'svsbackend open api',
        'version' => 'v1',
        'time' => now()->toIso8601String(),
    ]));

    Route::prefix('users/{user_id}')->group(function () {
        Route::get('/records', [DatasetController::class, 'index']);
        Route::get('/records/summary', [DatasetController::class, 'summary']);
        Route::delete('/cache', [DatasetController::class, 'flush']);
    });
});
