<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\StoreController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::get('general-products', [ProductController::class, 'index']);
Route::get('general-product-detail/{id}', [ProductController::class, 'show']);

Route::middleware('auth:api')->group(function(){
    Route::resource('products', ProductController::class);
    Route::resource('store', StoreController::class);
    Route::post('store/{id_seller}', [StoreController::class, 'updateStoreBySeller']);
});


