<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\ServiceController;
use App\Http\Controllers\Api\V1\AuthController;

Route::prefix('v1')->middleware('throttle:60,1')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::get('projects', [ProjectController::class, 'index']);
    Route::get('projects/{project:slug}', [ProjectController::class, 'show']);

    Route::get('services', [ServiceController::class, 'index']);
});
