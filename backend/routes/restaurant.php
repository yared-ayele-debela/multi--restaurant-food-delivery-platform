<?php

use App\Http\Controllers\Restaurant\BranchController;
use App\Http\Controllers\Restaurant\CategoryController;
use App\Http\Controllers\Restaurant\DashboardController;
use App\Http\Controllers\Restaurant\HourController;
use App\Http\Controllers\Restaurant\OrderController;
use App\Http\Controllers\Restaurant\ProductAddonController;
use App\Http\Controllers\Restaurant\ProductController;
use App\Http\Controllers\Restaurant\ProductSizeController;
use App\Http\Controllers\Restaurant\ProductStockController;
use App\Http\Controllers\Restaurant\SettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Restaurant Owner Routes
|--------------------------------------------------------------------------
|
| Routes for restaurant owners and staff to manage their restaurant,
| menu, orders, and settings.
|
*/

Route::middleware(['auth', 'restaurant.owner', 'web'])
    ->prefix('restaurant')
    ->name('restaurant.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Categories
        Route::resource('categories', CategoryController::class)->except(['show']);

        // Products
        Route::resource('products', ProductController::class);

        // Product Sizes
        Route::get('products/{product}/sizes/create', [ProductSizeController::class, 'create'])
            ->name('products.sizes.create');
        Route::post('products/{product}/sizes', [ProductSizeController::class, 'store'])
            ->name('products.sizes.store');
        Route::get('products/{product}/sizes/{size}/edit', [ProductSizeController::class, 'edit'])
            ->name('products.sizes.edit');
        Route::put('products/{product}/sizes/{size}', [ProductSizeController::class, 'update'])
            ->name('products.sizes.update');
        Route::delete('products/{product}/sizes/{size}', [ProductSizeController::class, 'destroy'])
            ->name('products.sizes.destroy');

        // Product Addons
        Route::get('products/{product}/addons/create', [ProductAddonController::class, 'create'])
            ->name('products.addons.create');
        Route::post('products/{product}/addons', [ProductAddonController::class, 'store'])
            ->name('products.addons.store');
        Route::get('products/{product}/addons/{addon}/edit', [ProductAddonController::class, 'edit'])
            ->name('products.addons.edit');
        Route::put('products/{product}/addons/{addon}', [ProductAddonController::class, 'update'])
            ->name('products.addons.update');
        Route::delete('products/{product}/addons/{addon}', [ProductAddonController::class, 'destroy'])
            ->name('products.addons.destroy');

        // Product Stock
        Route::get('products/{product}/stock', [ProductStockController::class, 'index'])
            ->name('products.stock.index');
        Route::get('products/{product}/stock/create', [ProductStockController::class, 'create'])
            ->name('products.stock.create');
        Route::post('products/{product}/stock', [ProductStockController::class, 'store'])
            ->name('products.stock.store');
        Route::get('products/{product}/stock/{stock}/edit', [ProductStockController::class, 'edit'])
            ->name('products.stock.edit');
        Route::put('products/{product}/stock/{stock}', [ProductStockController::class, 'update'])
            ->name('products.stock.update');
        Route::post('products/{product}/stock/{stock}/adjust', [ProductStockController::class, 'adjustStock'])
            ->name('products.stock.adjust');
        Route::delete('products/{product}/stock/{stock}', [ProductStockController::class, 'destroy'])
            ->name('products.stock.destroy');

        // Orders
        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/board', [OrderController::class, 'board'])->name('orders.board');
        Route::get('orders/refresh', [OrderController::class, 'refresh'])->name('orders.refresh');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('orders/{order}/accept', [OrderController::class, 'accept'])->name('orders.accept');
        Route::post('orders/{order}/prepare', [OrderController::class, 'markPreparing'])->name('orders.prepare');
        Route::post('orders/{order}/ready', [OrderController::class, 'markReady'])->name('orders.ready');
        Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

        // Branches
        Route::resource('branches', BranchController::class);

        // Restaurant Profile Settings
        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

        // Operating Hours
        Route::get('hours', [HourController::class, 'index'])->name('hours.index');
        Route::get('hours/edit', [HourController::class, 'edit'])->name('hours.edit');
        Route::put('hours', [HourController::class, 'update'])->name('hours.update');
    });
