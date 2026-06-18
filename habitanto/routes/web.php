<?php

use App\Http\Controllers\FakeStore\FakeStoreDashboardController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/fake-store');

Route::get('/fake-store', FakeStoreDashboardController::class)
    ->name('fake-store.dashboard');
