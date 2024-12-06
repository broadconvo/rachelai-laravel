<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/email', [\App\Http\Controllers\Email::class, 'create']);

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});
