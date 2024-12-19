<?php

use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::get('/', function () {
    return [
        'message' => 'Welcome to RachelAI'
    ];
});

require __DIR__.'/auth.php';
