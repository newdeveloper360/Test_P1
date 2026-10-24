<?php

use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\SiteController;
use App\Http\Controllers\Web\BlogController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\ContactController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SiteController::class, 'home'])->name('site.home');
Route::get('/about', [SiteController::class, 'about'])->name('site.about');
Route::get('/features', [SiteController::class, 'features'])->name('site.features');
Route::get('/faq', [SiteController::class, 'faq'])->name('site.faq');
Route::get('/contact', [SiteController::class, 'contact'])->name('site.contact');
Route::post('/contact', [SiteController::class, 'sendContact'])->middleware('throttle:contact-form')->name('site.contact.send');
Route::get('/blog', [SiteController::class, 'blogs'])->name('site.blogs');
Route::get('/blog/{blog:slug}', [SiteController::class, 'blog'])->name('site.blog');
Route::get('/media/blog/{blog}', [SiteController::class, 'image'])->name('site.blog.image');
Route::get('/pages/{page:slug}', [SiteController::class, 'page'])->name('site.page');
Route::get('/download', [SiteController::class, 'download'])->middleware('throttle:30,1')->name('site.download');
Route::get('/sitemap.xml', [SiteController::class, 'sitemap'])->name('site.sitemap');
Route::get('/robots.txt', [SiteController::class, 'robots'])->name('site.robots');

Route::get('/admin/login', [AdminController::class, 'loginForm'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login'])
    ->middleware('throttle:5,1')->name('admin.login.submit');

Route::prefix('admin')->name('admin.')->middleware(['admin.web', 'throttle:admin-panel'])->group(function () {
    Route::resource('blogs', BlogController::class)->except('show');
    Route::resource('pages', PageController::class)->except('show');
    Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::patch('/contacts/{contact}', [ContactController::class, 'update'])->name('contacts.update');
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
    Route::delete('/accounts/{user}/locations', [AdminController::class, 'clearLocations'])->name('accounts.locations.destroy');
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
