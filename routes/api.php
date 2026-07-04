<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\DesignController;
use App\Http\Controllers\Api\V1\ServiceController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ArticleController;

Route::prefix('v1')->middleware('throttle:60,1')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('projects', [ProjectController::class, 'index']);
    Route::post('projects', [ProjectController::class, 'store']);
    Route::get('projects/{project}', [ProjectController::class, 'show']);
    Route::match(['put', 'patch'], 'projects/{project}', [ProjectController::class, 'update']);
    Route::delete('projects/{project}', [ProjectController::class, 'destroy']);

    Route::get('designs', [DesignController::class, 'index']);
    Route::post('designs', [DesignController::class, 'store']);
    Route::get('designs/{design}', [DesignController::class, 'show']);
    Route::match(['put', 'patch'], 'designs/{design}', [DesignController::class, 'update']);
    Route::delete('designs/{design}', [DesignController::class, 'destroy']);

    Route::get('articles', [ArticleController::class, 'index']);
    Route::post('articles', [ArticleController::class, 'store']);
    Route::get('articles/{article}', [ArticleController::class, 'show']);
    Route::match(['put', 'patch'], 'articles/{article}', [ArticleController::class, 'update']);
    Route::delete('articles/{article}', [ArticleController::class, 'destroy']);

    Route::get('services', [ServiceController::class, 'index']);
});

