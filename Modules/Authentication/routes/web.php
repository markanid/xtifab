<?php

use Illuminate\Support\Facades\Route;
use Modules\Authentication\Http\Controllers\AccountPasswordController;
use Modules\Authentication\Http\Controllers\AuthController;

Route::middleware('web')->group(function () {
    Route::get('/', fn () => view('authentication::welcome'))->name('portal.home');

    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'form'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:6,1')->name('login.store');
    });

    Route::get('/staff/login', fn () => redirect()->route('login'))->name('staff.login');
    Route::get('/customer/login', fn () => redirect()->route('login'))->name('customer.login');

    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('portal.logout');

    Route::middleware('auth')->prefix('/account')->name('account.')->group(function () {
        Route::get('/password', [AccountPasswordController::class, 'edit'])->name('password.edit');
        Route::put('/password', [AccountPasswordController::class, 'update'])->name('password.update');
    });
});
