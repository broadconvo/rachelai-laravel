<?php

use App\Http\Controllers\Auth\ApiAuthController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Broadconvo\CountryController;
use App\Http\Controllers\Broadconvo\KnowledgebaseController;
use App\Http\Controllers\Broadconvo\PhoneExtensionController;
use App\Http\Controllers\Broadconvo\RachelController;
use App\Http\Controllers\Broadconvo\TenantController;
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

Route::post('/github/webhook', [GithubController::class, 'handle'])->middleware(ForceJsonResponse::class);

// APIs for broadconvo database
Route::prefix('broadconvo')->group(function() {
    Route::get('agents', [BroadconvoUserController::class, 'index']);

    Route::post('phone-extensions', [PhoneExtensionController::class, 'create']);
    Route::get('phone-extensions', [PhoneExtensionController::class, 'available']);

    // should be able to automatically create rachel when creating a tenant
    Route::post('tenants', [TenantController::class, 'create']);
    Route::get('tenants', [TenantController::class, 'index']);
    Route::get('tenants/{tenant}', [TenantController::class, 'show']);

    Route::post('countries', [CountryController::class, 'create']);

    // create rachel to your tenant
    Route::post('rachels', [RachelController::class, 'create']);
    Route::get('rachels/{rachel}', [RachelController::class, 'show']);

    Route::post('knowledgebases', [KnowledgebaseController::class, 'create']);
    Route::put('knowledgebases/{knowledgebase}', [KnowledgebaseController::class, 'update']);
    Route::delete('knowledgebases/{knowledgebase}', [KnowledgebaseController::class, 'destroy']);
    Route::get('knowledgebases/{knowledgebase}', [KnowledgebaseController::class, 'show']);
    Route::get('knowledgebases/{knowledgebase}/download', [KnowledgebaseController::class, 'download']);
});

// API for Qdrant
Route::prefix('qdrant')->group(function() {
    Route::post('/collections', [CollectionController::class, 'store']);
    Route::get('/collections', [CollectionController::class, 'index']);


    Route::prefix('/collections/{collection}')->group(function() {
        Route::post('/vectors/search', [VectorController::class, 'search']);

        Route::post('/vectors', [VectorController::class, 'store']);
        Route::get('/vectors', [VectorController::class, 'index']);
    });
});

// APIs of legacy rachelai
Route::prefix('rachel')->group(function() {
    // process emails
    Route::get('/email-agent', [GmailController::class, 'getEmails']);
    Route::get('/email-agent/sent', [GmailController::class, 'sentItems']);

    // watch for new emails
    Route::get('/email-agent/watch', [GmailController::class, 'watchGmail']);


    Route::post('/email-agent', [EmailAgentController::class, 'create']);
    Route::get('/email-agent/filters', [EmailAgentController::class, 'getFilters']);
    Route::get('/email-agent/filters/create', [EmailAgentController::class, 'createFilters']);// ->withoutMiddleware(Ensure);
    Route::get('/email-agent/google-status', [EmailAgentController::class, 'googleStatus']);

    Route::post('/email-agent/train', [FaqController::class, 'generate']);
});
