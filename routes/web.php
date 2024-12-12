<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

Route::middleware(['auth:sanctum'])->group(function () {
    Broadcast::routes();
});


require __DIR__.'/auth.php';
