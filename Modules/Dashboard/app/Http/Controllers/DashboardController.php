<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Billing\Models\Invoice;
use Modules\Companies\Models\Company;
use Modules\Projects\Models\Project;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $projects = Project::visibleTo($user);
        $invoices = Invoice::visibleTo($user);
        $stats = [
            'projects' => (clone $projects)->count(),
            'in_progress' => (clone $projects)->where('status', 'in_progress')->count(),
            'on_hold' => (clone $projects)->where('status', 'on_hold')->count(),
            'completed' => (clone $projects)->where('status', 'completed')->count(),
            'unpaid' => (clone $invoices)->whereIn('payment_status', ['unpaid', 'partially_paid'])->count(),
        ];
        if ($user->isStaff()) {
            $stats['companies'] = Company::count();
        }

        return view('dashboard::index', [
            'stats' => $stats,
            'projects' => $projects->with('company')->latest()->limit(8)->get(),
        ]);
    }
}
