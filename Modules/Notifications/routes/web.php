<?php

use Illuminate\Support\Facades\Route;
use Modules\Notifications\Http\Controllers\NotificationController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{id}', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::patch('/notifications', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
});
