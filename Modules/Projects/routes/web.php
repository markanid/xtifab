<?php

use Illuminate\Support\Facades\Route;
use Modules\Projects\Http\Controllers\DrawingController;
use Modules\Projects\Http\Controllers\ProjectController;

Route::prefix('staff')->name('staff.')->middleware(['web', 'auth', 'portal.role:super_admin,xt_tab_user'])->group(function () {
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    Route::patch('/projects/{project}/status', [ProjectController::class, 'updateStatus'])->name('projects.status');
});

Route::prefix('customer')->name('customer.')->middleware(['web', 'auth', 'portal.role:customer_user', 'company.active'])->group(function () {
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
});

Route::get('/drawings/{drawing}/download', [DrawingController::class, 'download'])
    ->middleware(['web', 'auth', 'throttle:30,1'])->name('drawings.download');
