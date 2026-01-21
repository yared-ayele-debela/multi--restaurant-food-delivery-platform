<?php

use App\Http\Controllers\Api\V1\Admin\WithdrawalController as AdminWithdrawalController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\RestaurantBranchController;
use App\Http\Controllers\Api\V1\RestaurantController;
use App\Http\Controllers\Api\V1\RestaurantOrderController;
use App\Http\Controllers\Api\V1\RestaurantWalletController;
use App\Http\Controllers\Api\V1\RestaurantWithdrawalController;
use App\Http\Controllers\Api\V1\SettingController;
use App\Http\Controllers\Api\V1\UserAddressController;
use App\Http\Controllers\Api\V1\UserProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'api' => 'v1',
        ]);
    });

    Route::get('/settings', [SettingController::class, 'show']);
    Route::get('/restaurants', [RestaurantController::class, 'index']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/restaurants/{restaurant:slug}/branches', [RestaurantBranchController::class, 'index']);
    Route::get('/restaurants/{restaurant:slug}', [RestaurantController::class, 'show']);

    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/user', [UserProfileController::class, 'show']);
        Route::patch('/auth/user', [UserProfileController::class, 'update']);

        Route::get('/user/addresses', [UserAddressController::class, 'index']);
        Route::post('/user/addresses', [UserAddressController::class, 'store']);
        Route::patch('/user/addresses/{address}', [UserAddressController::class, 'update']);
        Route::delete('/user/addresses/{address}', [UserAddressController::class, 'destroy']);

        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);

        Route::middleware('permission:orders.manage_restaurant')->group(function () {
            Route::post('/restaurant/orders/{order}/accept', [RestaurantOrderController::class, 'accept']);
            Route::post('/restaurant/orders/{order}/preparing', [RestaurantOrderController::class, 'preparing']);
            Route::post('/restaurant/orders/{order}/ready', [RestaurantOrderController::class, 'ready']);
            Route::post('/restaurant/orders/{order}/assign-driver', [RestaurantOrderController::class, 'assignDriver']);
        });

        Route::middleware('permission:wallet.manage_restaurant')->group(function () {
            Route::get('/restaurant/wallet', [RestaurantWalletController::class, 'show']);
            Route::get('/restaurant/wallet/withdrawals', [RestaurantWithdrawalController::class, 'index']);
            Route::post('/restaurant/wallet/withdrawals', [RestaurantWithdrawalController::class, 'store']);
        });

        Route::middleware('role:admin')->prefix('admin')->group(function () {
            Route::get('/withdrawals', [AdminWithdrawalController::class, 'index']);
            Route::post('/withdrawals/{withdrawal}/complete', [AdminWithdrawalController::class, 'complete']);
            Route::post('/withdrawals/{withdrawal}/reject', [AdminWithdrawalController::class, 'reject']);
        });
    });
});
