<?php

namespace Modules\Companies\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Billing\Models\Invoice;
use Modules\Companies\Models\Company;
use Modules\Projects\Models\Project;

class PortalDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make(env('DEMO_PASSWORD', 'ChangeMe!2026'));
        $admin = User::updateOrCreate(['email' => 'admin@xttab.test'], ['name' => 'Portal Administrator', 'password' => $password, 'role' => 'super_admin', 'status' => 'active']);
        User::updateOrCreate(['email' => 'staff@xttab.test'], ['name' => 'XT Tab Estimator', 'password' => $password, 'role' => 'xt_tab_user', 'status' => 'active', 'created_by' => $admin->id]);
        User::updateOrCreate(['email' => 'billing@xttab.test'], ['name' => 'XT Tab Billing', 'password' => $password, 'role' => 'xt_tab_user', 'status' => 'active', 'created_by' => $admin->id]);
        foreach ([['COM-00001', 'Gulf Structures Ltd'], ['COM-00002', 'Riyadh Steel Works']] as $index => [$code, $name]) {
            $company = Company::updateOrCreate(['company_code' => $code], ['company_name' => $name, 'contact_person' => 'Project Manager', 'email' => "projects{$index}@example.test", 'country' => 'Saudi Arabia', 'status' => 'active', 'created_by' => $admin->id]);
            $customer = User::updateOrCreate(['email' => 'customer'.($index + 1).'@example.test'], ['company_id' => $company->id, 'name' => "{$name} User", 'password' => $password, 'role' => 'customer_user', 'status' => 'active', 'created_by' => $admin->id]);
            foreach (['submitted', 'in_progress', 'on_hold', 'completed'] as $position => $status) {
                $project = Project::updateOrCreate(['project_number' => 'PRJ-'.now()->year.'-'.str_pad((string) ($index * 4 + $position + 1), 5, '0', STR_PAD_LEFT)], ['company_id' => $company->id, 'created_by' => $customer->id, 'project_name' => $name.' Sample '.($position + 1), 'priority' => $position === 2 ? 'urgent' : 'normal', 'status' => $status, 'required_delivery_date' => now()->addDays(14 + $position), 'actual_completion_date' => $status === 'completed' ? today() : null]);
                if ($position === 1) {
                    $invoice = Invoice::updateOrCreate(['invoice_number' => 'INV-'.now()->year.'-'.str_pad((string) ($index + 1), 5, '0', STR_PAD_LEFT)], ['company_id' => $company->id, 'project_id' => $project->id, 'invoice_date' => today(), 'due_date' => now()->addDays(30), 'currency' => 'SAR', 'subtotal' => 10000, 'tax_percentage' => 15, 'tax_amount' => 1500, 'total_amount' => 11500, 'status' => 'issued', 'approval_status' => 'pending', 'payment_status' => 'unpaid', 'created_by' => $admin->id]);
                    $invoice->items()->updateOrCreate(['sort_order' => 0], ['description' => 'Project estimation services', 'quantity' => 1, 'unit' => 'project', 'unit_price' => 10000, 'tax_percentage' => 15, 'line_total' => 10000]);
                }
            }
        }
    }
}
