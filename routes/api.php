<?php

use App\Http\Controllers\EmailAgentController;
use App\Http\Controllers\GithubController;
use App\Http\Controllers\GmailController;
use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

//Auth::loginUsingId(1);


// redirect to the provider
Route::get('/auth/redirect', [GmailController::class, 'googleRedirect']);

// callback from the provider
Route::get('/auth/callback', [GmailController::class, 'index'])->name('google.oauth.callback');

// process emails
Route::get('/emails', [GmailController::class, 'getEmails']);

// watch for new emails
Route::get('/emails/watch', [GmailController::class, 'watchGmail']);


Route::post('/email', [EmailAgentController::class, 'create']);
Route::get('/email/filters', [EmailAgentController::class, 'getFilters']);
Route::get('/email/filters/create', [EmailAgentController::class, 'createFilters']);// ->withoutMiddleware(Ensure);

Route::post('/github/webhook', [GithubController::class, 'handle'])->middleware(ForceJsonResponse::class);
