<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'status'    => 'success',
        'message'   => 'API is running',
        'service'   => 'stage-1-profile-classification-api',
        'version'   => '1.0.0',
        'timestamp' => now()->toISOString(),
    ]);
});
