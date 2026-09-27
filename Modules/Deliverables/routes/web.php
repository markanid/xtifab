<?php

use Illuminate\Support\Facades\Route;
use Modules\Deliverables\Http\Controllers\DeliverableController;

Route::post('/staff/projects/{project}/deliverables', [DeliverableController::class, 'store'])
    ->middleware(['web', 'auth', 'portal.role:super_admin,xt_tab_user'])->name('staff.deliverables.store');

Route::get('/deliverables/{deliverable}/download', [DeliverableController::class, 'download'])
    ->middleware(['web', 'auth', 'throttle:30,1'])->name('deliverables.download');
