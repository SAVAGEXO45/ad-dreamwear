<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PasswordController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SessionController;
use App\Http\Controllers\Admin\SiteController;
use App\Http\Controllers\CatalogueController;
use App\Http\Controllers\MediaController;
use App\Http\Middleware\Administrator;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogueController::class, 'home'])->name('home');
Route::get('/dresses', [CatalogueController::class, 'index'])->name('catalogue');
Route::get('/dresses/{product:slug}', [CatalogueController::class, 'show'])->name('dresses.show');
Route::get('/media/products/{product}/{file}', [MediaController::class, 'product'])->where('file', '[a-f0-9-]+\.webp')->name('media.product');
Route::get('/media/banners/{file}', [MediaController::class, 'banner'])->where('file', '[a-f0-9-]+\.webp')->name('media.banner');

Route::prefix('admin')->group(function () {
    Route::get('/login', [SessionController::class, 'create'])->name('login');
    Route::post('/login', [SessionController::class, 'store'])->middleware('throttle:10,1')->name('login.store');

    Route::name('admin.')->middleware(['auth', Administrator::class, 'auth.session'])->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');
        Route::get('/password', [PasswordController::class, 'edit'])->name('password.edit');
        Route::put('/password', [PasswordController::class, 'update'])->middleware('throttle:6,1,password-change')->name('password.update');
        Route::resource('products', ProductController::class)->except('show');
        Route::resource('categories', CategoryController::class)->except('show');
        Route::get('/website', [SiteController::class, 'edit'])->name('website.edit');
        Route::put('/website', [SiteController::class, 'update'])->name('website.update');
    });
});
