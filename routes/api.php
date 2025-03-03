<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Transaction\UserTransactionController;
use App\Http\Controllers\Api\AdminActions\AdminActionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);

    Route::middleware('auth:api')->group(function () {
        Route::get('profile', [AuthController::class, 'profile']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });
});

Route::middleware('auth:api')->group(function () {
    Route::group(['prefix' => 'transactions'], function () {
        Route::post('create', [UserTransactionController::class, 'create']);
        Route::post('view', [UserTransactionController::class, 'view']);
        Route::get('list', [UserTransactionController::class, 'list']);
    });

    Route::group(['prefix' => 'admin'], function () {
        Route::get('list/users', [AdminActionController::class, 'listUsers']);
        Route::post('wallet/deposit', [AdminActionController::class, 'deposit']);
        Route::post('wallet/debit', [AdminActionController::class, 'debit']);
        Route::get('adminStats', [AdminActionController::class, 'stats']);
    });
});