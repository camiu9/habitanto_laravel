<?php

use App\Http\Controllers\Api\FakeStore\CartController;
use App\Http\Controllers\Api\FakeStore\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('fake-store')->name('api.fake-store.')->group(function (): void {
    Route::apiResource('products', ProductController::class);
    Route::apiResource('carts', CartController::class);
});
