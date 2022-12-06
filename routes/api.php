<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthLogController;
use App\Http\Controllers\ActivityLogController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/auth/login', [UserController::class, 'login']);

Route::middleware(['auth:user'])->group(function () {
    Route::prefix('user')->group(function () {
        Route::post('/list', [UserController::class, 'list']);
        Route::post('/create', [UserController::class, 'create']);
        Route::post('/read', [UserController::class, 'read']);
        Route::post('/update', [UserController::class, 'update']);
        Route::post('/delete', [UserController::class, 'delete']);
        Route::post('/logout', [UserController::class, 'logout']);
    });

    Route::prefix('post')->group(function () {
        Route::post('/list', [PostController::class, 'list']);
        Route::post('/create', [PostController::class, 'create']);
        Route::post('/read', [PostController::class, 'read']);
        Route::post('/update', [PostController::class, 'update']);
        Route::post('/delete', [PostController::class, 'delete']);
        Route::post('/logout', [PostController::class, 'logout']);
    });

    Route::prefix('role')->group(function () {
        Route::post('/list', [RoleController::class, 'list']);
        Route::post('/create', [RoleController::class, 'create']);
        Route::post('/read', [RoleController::class, 'read']);
        Route::post('/update', [RoleController::class, 'update']);
        Route::post('/delete', [RoleController::class, 'delete']);
        Route::post('/logout', [RoleController::class, 'logout']);
    });

    Route::prefix('activity-log')->group(function () {
        Route::post('/read', [ActivityLogController::class, 'read']);
        Route::post('/list', [ActivityLogController::class, 'list']);
        Route::post('/create', [ActivityLogController::class, 'create']);
    });

    Route::prefix('auth-log')->group(function () {
        Route::post('/read', [AuthLogController::class, 'read']);
        Route::post('/list', [AuthLogController::class, 'list']);
        Route::post('/create', [AuthLogController::class, 'create']);
    });

});




