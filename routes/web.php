<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

Route::middleware(['auth:sanctum'])->group(function () {
    Broadcast::routes();
});

Route::get('/show-cache/{userId}', function ($userId) {
    $cacheKey = "user-{$userId}-online";
    $isOnline = Cache::get($cacheKey, false);
    return response()->json([
        'user_id' => $userId,
        'is_online' => $isOnline,
    ]);
});

require __DIR__.'/auth.php';
