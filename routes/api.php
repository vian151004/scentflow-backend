<?php

use App\Http\Controllers\Api\AttendanceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CashClosingController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\ProductController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('materials', MaterialController::class);
    Route::apiResource('products', ProductController::class);

    Route::get('/attendances/today', [AttendanceController::class, 'today']);
    Route::apiResource('attendances', AttendanceController::class);

    Route::apiResource('cash-closings', CashClosingController::class)->only([
        'index', 
        'store', 
        'show'
    ]);
});