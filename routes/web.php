<?php

use App\Http\Controllers\Web\AdminController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::get('/admin/login', [AdminController::class, 'loginForm'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login'])
    ->middleware('throttle:5,1')->name('admin.login.submit');

Route::prefix('admin')->name('admin.')->middleware('admin.web')->group(function () {
    Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/accounts', [AdminController::class, 'accounts'])->name('accounts.index');
    Route::get('/accounts/create', [AdminController::class, 'createAccount'])->name('accounts.create');
    Route::post('/accounts', [AdminController::class, 'storeAccount'])->name('accounts.store');
    Route::get('/accounts/{user}', [AdminController::class, 'showAccount'])->name('accounts.show');
    Route::get('/accounts/{user}/edit', [AdminController::class, 'editAccount'])->name('accounts.edit');
    Route::patch('/accounts/{user}', [AdminController::class, 'updateAccount'])->name('accounts.update');
    Route::patch('/accounts/{user}/block', [AdminController::class, 'toggleBlock'])->name('accounts.block');
    Route::delete('/accounts/{user}', [AdminController::class, 'destroyAccount'])->name('accounts.destroy');
    Route::get('/accounts/{user}/locations', [AdminController::class, 'locations'])->name('accounts.locations');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::patch('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
    Route::post('/settings/image/{type}', [AdminController::class, 'uploadSettingImage'])->name('settings.image');
    Route::get('/sliders', [AdminController::class, 'sliders'])->name('sliders');
    Route::get('/sliders/{slider}/image', [AdminController::class, 'sliderImage'])->name('sliders.image');
    Route::post('/sliders', [AdminController::class, 'storeSliders'])->name('sliders.store');
    Route::patch('/sliders/{slider}', [AdminController::class, 'updateSlider'])->name('sliders.update');
    Route::delete('/sliders/{slider}', [AdminController::class, 'destroySlider'])->name('sliders.destroy');
    Route::get('/profile', [AdminController::class, 'profile'])->name('profile');
    Route::patch('/profile', [AdminController::class, 'updateProfile'])->name('profile.update');
});
