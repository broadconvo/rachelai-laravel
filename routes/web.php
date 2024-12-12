<?php

use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::get('/', function () {
    return [
        'Laravel' => app()->version(),
        'message' => 'Welcome to Rachel AI'
    ];
});

require __DIR__.'/auth.php';
