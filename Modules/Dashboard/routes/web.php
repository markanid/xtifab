<?php

use Illuminate\Support\Facades\Route;
use Modules\Dashboard\Http\Controllers\DashboardController;

Route::get('/staff/dashboard', [DashboardController::class, 'index'])
    ->middleware(['web', 'auth', 'portal.role:super_admin,xt_tab_user'])->name('staff.dashboard');

Route::get('/customer/dashboard', [DashboardController::class, 'index'])
    ->middleware(['web', 'auth', 'portal.role:customer_user', 'company.active'])->name('customer.dashboard');
