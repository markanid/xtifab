<?php

use Illuminate\Support\Facades\Route;
use Modules\Companies\Http\Controllers\CompanyController;
use Modules\Companies\Http\Controllers\CustomerUserController;

Route::prefix('staff')->name('staff.')->middleware(['web', 'auth', 'portal.role:super_admin,xt_tab_user'])->group(function () {
    Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
    Route::get('/companies/create', [CompanyController::class, 'create'])->name('companies.create');
    Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
    Route::get('/companies/{company}', [CompanyController::class, 'show'])->name('companies.show');
    Route::get('/companies/{company}/edit', [CompanyController::class, 'edit'])->name('companies.edit');
    Route::put('/companies/{company}', [CompanyController::class, 'update'])->name('companies.update');
    Route::delete('/companies/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');
    Route::get('/customers', [CustomerUserController::class, 'index'])->name('customers.index');
    Route::get('/customers/create', [CustomerUserController::class, 'create'])->name('customers.create');
    Route::post('/customers', [CustomerUserController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}', [CustomerUserController::class, 'show'])->name('customers.show');
    Route::get('/customers/{customer}/edit', [CustomerUserController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{customer}', [CustomerUserController::class, 'update'])->name('customers.update');
    Route::patch('/customers/{customer}/status', [CustomerUserController::class, 'toggleStatus'])->name('customers.status');
});
