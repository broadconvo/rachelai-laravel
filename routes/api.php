<?php

use App\Http\Controllers\Auth\ApiAuthController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Broadconvo\PhoneExtensionController;
use App\Http\Controllers\Broadconvo\UserController as BroadconvoUserController;
use App\Http\Controllers\EmailAgentController;
use App\Http\Controllers\GithubController;
use App\Http\Controllers\GmailController;
use App\Http\Controllers\Qdrant\CollectionController;
use App\Http\Controllers\Qdrant\VectorController;
use App\Http\Controllers\RachelAI\FaqController;
use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Support\Facades\Route;

Route::post('/login', [ApiAuthController::class, 'login']);


Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/user', [ApiAuthController::class, 'user']);

    Route::post('/logout', [ApiAuthController::class, 'logout']);
});

Route::post('/register', [RegisteredUserController::class, 'store']);

//Auth::loginUsingId(1);


// redirect to the provider
Route::get('/auth/redirect', [GmailController::class, 'googleRedirect']);

// callback from the provider
Route::get('/auth/callback', [GmailController::class, 'index'])->name('google.oauth.callback');

// process emails
Route::get('/emails', [GmailController::class, 'getEmails']);
Route::get('/emails/sent', [GmailController::class, 'sentItems']);

// watch for new emails
Route::get('/emails/watch', [GmailController::class, 'watchGmail']);


Route::post('/email', [EmailAgentController::class, 'create']);
Route::get('/email/filters', [EmailAgentController::class, 'getFilters']);
Route::get('/email/filters/create', [EmailAgentController::class, 'createFilters']);// ->withoutMiddleware(Ensure);

Route::post('/github/webhook', [GithubController::class, 'handle'])->middleware(ForceJsonResponse::class);


Route::prefix('broadconvo')->group(function() {
    Route::get('agents', [BroadconvoUserController::class, 'index']);

    Route::get('phone-extensions', [PhoneExtensionController::class, 'available']);
});

Route::prefix('qdrant')->group(function() {
    Route::post('/collections', [CollectionController::class, 'store']);
    Route::get('/collections', [CollectionController::class, 'index']);


    Route::prefix('/collections/{collection}')->group(function() {
        Route::post('/vectors/search', [VectorController::class, 'search']);

        Route::post('/vectors', [VectorController::class, 'store']);
        Route::get('/vectors', [VectorController::class, 'index']);
    });
});

Route::prefix('rachel')->group(function() {
    Route::get('/faq', [FaqController::class, 'generate']);
});
