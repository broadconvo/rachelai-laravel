<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return [
        'Laravel' => app()->version(),
        'message' => 'Welcome to Rachel AI'
    ];
});

require __DIR__.'/auth.php';
