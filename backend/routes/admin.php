
<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\CommissionLedgerController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\WithdrawalRequestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
     */

Route::middleware(['auth', 'admin', 'web'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    //    Example
    //    Route::resource('/users', UserController::class)->names(['index' => '.users.index', 'create' => '.users.create', 'store' => '.users.store', 'show' => '.users.show', 'edit' => '.users.edit', 'update' => '.users.update', 'destroy' => '.users.destroy']);
    //    Route::resource('/roles', RoleController::class)->names(['index' => '.roles.index', 'create' => '.roles.create', 'store' => '.roles.store', 'show' => '.roles.show', 'edit' => '.roles.edit', 'update' => '.roles.update', 'destroy' => '.roles.destroy']);

    Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);
    Route::resource('permissions', \App\Http\Controllers\Admin\PermissionController::class);
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    Route::post('users/{user}/suspend', [\App\Http\Controllers\Admin\UserController::class, 'suspend'])->name('users.suspend');
    Route::post('users/{user}/activate', [\App\Http\Controllers\Admin\UserController::class, 'activate'])->name('users.activate');

    Route::get('drivers', [DriverController::class, 'index'])->name('drivers.index');
    Route::get('drivers/{driver}', [DriverController::class, 'show'])->name('drivers.show');
    Route::post('drivers/{driver}/approve', [DriverController::class, 'approve'])->name('drivers.approve');
    Route::post('drivers/{driver}/reject', [DriverController::class, 'reject'])->name('drivers.reject');

    Route::get('withdrawals', [WithdrawalRequestController::class, 'index'])->name('withdrawals.index');
    Route::post('withdrawals/{withdrawalRequest}/complete', [WithdrawalRequestController::class, 'complete'])->name('withdrawals.complete');
    Route::post('withdrawals/{withdrawalRequest}/reject', [WithdrawalRequestController::class, 'reject'])->name('withdrawals.reject');
    Route::get('commissions', [CommissionLedgerController::class, 'index'])->name('commissions.index');
    Route::get('commissions/export/csv', [CommissionLedgerController::class, 'exportCsv'])->name('commissions.export-csv');

    Route::get('restaurants', [\App\Http\Controllers\Admin\RestaurantController::class, 'index'])->name('restaurants.index');
    Route::get('restaurants/{restaurant}', [\App\Http\Controllers\Admin\RestaurantController::class, 'show'])->name('restaurants.show');
    Route::post('restaurants/{restaurant}/approve', [\App\Http\Controllers\Admin\RestaurantController::class, 'approve'])->name('restaurants.approve');
    Route::post('restaurants/{restaurant}/reject', [\App\Http\Controllers\Admin\RestaurantController::class, 'reject'])->name('restaurants.reject');
    Route::post('restaurants/{restaurant}/suspend', [\App\Http\Controllers\Admin\RestaurantController::class, 'suspend'])->name('restaurants.suspend');
    Route::post('restaurants/{restaurant}/toggle-featured', [\App\Http\Controllers\Admin\RestaurantController::class, 'toggleFeatured'])->name('restaurants.toggle-featured');

    // Settings
    Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('settings/remove-logo', [SettingsController::class, 'removeLogo'])->name('settings.remove-logo');
    Route::get('settings/remove-favicon', [SettingsController::class, 'removeFavicon'])->name('settings.remove-favicon');

});
