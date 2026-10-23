<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\SliderController;
use Illuminate\Support\Facades\Route;

Route::get('/settings', [SettingController::class, 'show']);
Route::get('/settings/image/{type}', [SettingController::class, 'image']);
Route::get('/sliders', [SliderController::class, 'index']);
Route::get('/sliders/{slider}/image', [SliderController::class, 'image']);
Route::post('/user/create', [AuthController::class, 'register'])->middleware('throttle:registration');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

Route::middleware(['auth:sanctum', 'active.user', 'app.maintenance'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::patch('/profile', [AuthController::class, 'updateProfile']);
    Route::patch('/password', [AuthController::class, 'updatePassword']);
    Route::get('/login-history', [AuthController::class, 'loginHistory']);
    Route::post('/locations', [LocationController::class, 'store']);
    Route::get('/locations/latest', [LocationController::class, 'latest']);
    Route::get('/locations', [LocationController::class, 'index']);
    Route::get('/members', [MemberController::class, 'index']);
    Route::get('/members/{member}/locations', [MemberController::class, 'locations']);
});
