<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Modules\Billing\Models\Invoice;
use Modules\Companies\Models\Company;
use Modules\Deliverables\Models\Deliverable;
use Modules\Notifications\Notifications\PortalNotification;
use Modules\Projects\Models\Project;
use Tests\TestCase;

class PortalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_and_customer_use_the_same_login_portal(): void
    {
        $company = $this->company();
        $staff = User::factory()->create(['role' => 'xt_tab_user', 'status' => 'active', 'password' => 'password']);
        $customer = User::factory()->create(['company_id' => $company->id, 'role' => 'customer_user', 'status' => 'active', 'password' => 'password']);

        $loginPage = $this->get(route('login'))->assertOk()->assertHeader('Pragma', 'no-cache');
        $this->assertStringContainsString('no-store', $loginPage->headers->get('Cache-Control'));

        $this->get(route('staff.dashboard'))
            ->assertRedirect(route('login'));
        $this->flushSession();
        $this->get(route('customer.dashboard'))
            ->assertRedirect(route('login'));

        $this->actingAs($staff)
            ->get(route('login'))
            ->assertRedirect(route('staff.dashboard'));
        $this->actingAs($customer)
            ->get(route('login'))
            ->assertRedirect(route('customer.dashboard'));

        auth()->logout();
        $this->post(route('login.store'), ['email' => $staff->email, 'password' => 'password'])->assertRedirect(route('staff.dashboard'));
        $this->post(route('portal.logout'));
        $this->post(route('login.store'), ['email' => $customer->email, 'password' => 'password'])->assertRedirect(route('customer.dashboard'));
        $this->assertFalse(app('router')->getRoutes()->hasNamedRoute('register'));
    }

    public function test_customer_creates_project_with_private_drawing(): void
    {
        Storage::fake('local');
        $customer = $this->customer($this->company());
        $this->actingAs($customer)->post(route('customer.projects.store'), ['project_name' => 'Warehouse', 'priority' => 'high', 'drawings' => [UploadedFile::fake()->create('plan.pdf', 50, 'application/pdf')]])->assertRedirect();
        $project = Project::first();
        $this->assertSame($customer->company_id, $project->company_id);
        Storage::disk('local')->assertExists($project->drawings->first()->file_path);
    }

    public function test_customer_cannot_access_another_company_project_or_invoice(): void
    {
        $customer = $this->customer($this->company());
        $other = $this->customer($this->company('Other'));
        $project = $this->project($other);
        $this->actingAs($customer)->get(route('customer.projects.show', $project))->assertForbidden();
        $invoice = $this->invoice($project, $other);
        $this->actingAs($customer)->get(route('customer.invoices.show', $invoice))->assertForbidden();
        $this->actingAs($customer)->post(route('customer.invoices.approve', $invoice))->assertForbidden();
    }

    public function test_staff_uploads_deliverable_and_owning_customer_can_download(): void
    {
        Storage::fake('local');
        $staff = User::factory()->create(['role' => 'xt_tab_user', 'status' => 'active']);
        $customer = $this->customer($this->company());
        $project = $this->project($customer);
        $this->actingAs($staff)->post(route('staff.deliverables.store', $project), ['deliverable_type' => 'material_list', 'title' => 'Materials', 'is_visible_to_customer' => 1, 'file' => UploadedFile::fake()->create('materials.xlsx', 10)])->assertRedirect();
        $this->actingAs($customer)->get(route('deliverables.download', Deliverable::first()))->assertOk();
    }

    public function test_completion_notification_is_not_duplicated(): void
    {
        Notification::fake();
        $staff = User::factory()->create(['role' => 'xt_tab_user', 'status' => 'active']);
        $customer = $this->customer($this->company());
        $project = $this->project($customer);
        $this->actingAs($staff)->patch(route('staff.projects.status', $project), ['status' => 'completed'])->assertRedirect();
        $this->actingAs($staff)->patch(route('staff.projects.status', $project), ['status' => 'completed'])->assertRedirect();
        Notification::assertSentToTimes($customer, PortalNotification::class, 1);
        $this->assertNotNull($project->fresh()->actual_completion_date);
    }

    public function test_invoice_approval_rejection_and_payment_recalculation(): void
    {
        $staff = User::factory()->create(['role' => 'xt_tab_user', 'status' => 'active']);
        $customer = $this->customer($this->company());
        $invoice = $this->invoice($this->project($customer), $customer);
        $this->actingAs($customer)->post(route('customer.invoices.approve', $invoice))->assertRedirect();
        $this->assertSame('approved', $invoice->fresh()->approval_status);
        $this->actingAs($staff)->post(route('staff.invoices.payments.store', $invoice), ['payment_date' => today()->toDateString(), 'amount' => 50, 'payment_method' => 'bank'])->assertRedirect();
        $this->assertSame('partially_paid', $invoice->fresh()->payment_status);
        $this->actingAs($staff)->post(route('staff.invoices.payments.store', $invoice), ['payment_date' => today()->toDateString(), 'amount' => 65, 'payment_method' => 'bank'])->assertRedirect();
        $this->assertSame('paid', $invoice->fresh()->payment_status);
    }

    public function test_suspended_company_customer_cannot_log_in(): void
    {
        $customer = $this->customer($this->company('Suspended', 'suspended'));
        $customer->update(['password' => 'password']);
        $this->post(route('login.store'), ['email' => $customer->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_super_admin_can_manage_staff_users_but_regular_staff_cannot(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'active',
        ]);
        $regularStaff = User::factory()->create([
            'role' => 'xt_tab_user',
            'status' => 'active',
        ]);

        $this->actingAs($superAdmin)
            ->get(route('staff.staff-users.index'))
            ->assertOk()
            ->assertSee('Create Staff User');

        $this->actingAs($superAdmin)->post(route('staff.staff-users.store'), [
            'name' => 'New Estimator',
            'email' => 'estimator@xttab.test',
            'phone' => '+966500000000',
            'password' => 'Temporary!2026',
            'status' => 'active',
        ])->assertRedirect(route('staff.staff-users.index'));

        $staffUser = User::where('email', 'estimator@xttab.test')->firstOrFail();
        $this->assertSame('xt_tab_user', $staffUser->role);
        $this->assertNull($staffUser->company_id);
        $this->assertTrue($staffUser->must_change_password);
        $this->assertTrue(Hash::check('Temporary!2026', $staffUser->password));

        $this->actingAs($superAdmin)->put(route('staff.staff-users.update', $staffUser), [
            'name' => 'Updated Estimator',
            'email' => 'estimator@xttab.test',
            'phone' => null,
            'password' => '',
            'status' => 'active',
        ])->assertRedirect(route('staff.staff-users.index'));
        $this->assertSame('Updated Estimator', $staffUser->fresh()->name);

        $this->actingAs($superAdmin)
            ->patch(route('staff.staff-users.status', $staffUser))
            ->assertRedirect();
        $this->assertSame('inactive', $staffUser->fresh()->status);

        $this->actingAs($superAdmin)
            ->get(route('staff.staff-users.edit', $superAdmin))
            ->assertNotFound();
        $this->actingAs($regularStaff)
            ->get(route('staff.staff-users.index'))
            ->assertForbidden();
        $this->actingAs($regularStaff)
            ->get(route('staff.dashboard'))
            ->assertOk()
            ->assertDontSee('Staff Users');
    }

    public function test_staff_and_customer_can_change_their_temporary_passwords(): void
    {
        $staff = User::factory()->create([
            'role' => 'xt_tab_user',
            'status' => 'active',
            'password' => 'Temporary!2026',
            'must_change_password' => true,
        ]);

        $this->actingAs($staff)
            ->get(route('account.password.edit'))
            ->assertOk()
            ->assertSee('Change Password')
            ->assertSee('temporary password');

        $this->actingAs($staff)
            ->put(route('account.password.update'), [
                'current_password' => 'IncorrectPassword!2026',
                'password' => 'ChangedPassword!2026',
                'password_confirmation' => 'ChangedPassword!2026',
            ])
            ->assertSessionHasErrors('current_password');

        $staff->refresh();
        $this->assertTrue(Hash::check('Temporary!2026', $staff->password));
        $this->assertTrue($staff->must_change_password);

        $this->actingAs($staff)
            ->put(route('account.password.update'), [
                'current_password' => 'Temporary!2026',
                'password' => 'ChangedPassword!2026',
                'password_confirmation' => 'ChangedPassword!2026',
            ])
            ->assertRedirect(route('staff.dashboard'));

        $staff->refresh();
        $this->assertTrue(Hash::check('ChangedPassword!2026', $staff->password));
        $this->assertFalse($staff->must_change_password);

        $customer = $this->customer($this->company());
        $customer->update([
            'password' => 'CustomerTemporary!2026',
            'must_change_password' => true,
        ]);

        $this->actingAs($customer)
            ->get(route('account.password.edit'))
            ->assertOk()
            ->assertSee('Change Password')
            ->assertSee('temporary password');

        $this->actingAs($customer)
            ->put(route('account.password.update'), [
                'current_password' => 'CustomerTemporary!2026',
                'password' => 'CustomerChanged!2026',
                'password_confirmation' => 'CustomerChanged!2026',
            ])
            ->assertRedirect(route('customer.dashboard'));

        $customer->refresh();
        $this->assertTrue(Hash::check('CustomerChanged!2026', $customer->password));
        $this->assertFalse($customer->must_change_password);

        auth()->logout();

        $this->get(route('account.password.edit'))
            ->assertRedirect(route('login'));
    }

    public function test_management_lists_use_adminlte_datatables_without_duplicate_view_buttons(): void
    {
        $staff = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'active',
        ]);
        $company = $this->company();
        $customer = $this->customer($company);
        $project = $this->project($customer);
        $invoice = $this->invoice($project, $staff);

        $responses = [
            $this->actingAs($staff)->get(route('staff.companies.index')),
            $this->actingAs($staff)->get(route('staff.customers.index')),
            $this->actingAs($staff)->get(route('staff.projects.index')),
            $this->actingAs($staff)->get(route('staff.invoices.index')),
            $this->actingAs($staff)->get(route('staff.staff-users.index')),
        ];

        foreach ($responses as $response) {
            $response
                ->assertOk()
                ->assertSee('portal-data-table')
                ->assertDontSee('fa-eye');
        }

        $responses[0]
            ->assertSee('admin-assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')
            ->assertSee('admin-assets/plugins/datatables-buttons/js/buttons.html5.min.js')
            ->assertSee(route('staff.companies.show', $company), false);

        $responses[1]->assertSee(route('staff.customers.show', $customer), false);
        $responses[2]->assertSee(route('staff.projects.show', $project), false);
        $responses[3]->assertSee(route('staff.invoices.show', $invoice), false);
    }

    public function test_dashboard_displays_colored_status_badges_for_recent_projects(): void
    {
        $customer = $this->customer($this->company());
        $project = $this->project($customer);
        $project->update(['status' => 'in_progress']);

        $this->actingAs($customer)
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('<span class="badge badge-primary">In Progress</span>', false)
            ->assertSee('fas fa-boxes', false)
            ->assertSee('fas fa-tools', false)
            ->assertSee('fas fa-pause-circle', false)
            ->assertSee('fas fa-check-circle', false)
            ->assertSee('fas fa-file-invoice-dollar', false);
    }

    public function test_staff_can_update_company_and_toggle_customer_login(): void
    {
        $staff = User::factory()->create(['role' => 'xt_tab_user', 'status' => 'active']);
        $company = $this->company();
        $customer = $this->customer($company);

        $this->actingAs($staff)->put(route('staff.companies.update', $company), [
            'company_name' => 'Updated Company',
            'status' => 'active',
        ])->assertRedirect(route('staff.companies.show', $company));
        $this->assertSame('Updated Company', $company->fresh()->company_name);

        $this->actingAs($staff)->patch(route('staff.customers.status', $customer))->assertRedirect();
        $this->assertSame('inactive', $customer->fresh()->status);
    }

    public function test_customer_can_edit_and_archive_own_submitted_project(): void
    {
        $customer = $this->customer($this->company());
        $project = $this->project($customer);

        $this->actingAs($customer)->put(route('customer.projects.update', $project), [
            'project_name' => 'Updated Project',
            'priority' => 'urgent',
        ])->assertRedirect(route('customer.projects.show', $project));
        $this->assertSame('Updated Project', $project->fresh()->project_name);

        $this->actingAs($customer)->delete(route('customer.projects.destroy', $project))
            ->assertRedirect(route('customer.projects.index'));
        $this->assertSoftDeleted($project);
    }

    public function test_staff_can_update_and_delete_draft_invoice_and_cancel_issued_invoice(): void
    {
        $staff = User::factory()->create(['role' => 'xt_tab_user', 'status' => 'active']);
        $customer = $this->customer($this->company());
        $project = $this->project($customer);
        $draft = $this->invoice($project, $staff);
        $draft->update(['status' => 'draft']);
        $draft->items()->create([
            'description' => 'Initial service',
            'quantity' => 1,
            'unit' => 'item',
            'unit_price' => 100,
            'line_total' => 100,
        ]);

        $this->actingAs($staff)->put(route('staff.invoices.update', $draft), [
            'project_id' => $project->id,
            'invoice_date' => today()->toDateString(),
            'due_date' => today()->addMonth()->toDateString(),
            'currency' => 'SAR',
            'tax_percentage' => 15,
            'discount_amount' => 0,
            'status' => 'draft',
            'items' => [[
                'description' => 'Updated service',
                'quantity' => 2,
                'unit' => 'item',
                'unit_price' => 100,
            ]],
        ])->assertRedirect(route('staff.invoices.show', $draft));
        $this->assertEquals('230.00', $draft->fresh()->total_amount);

        $this->actingAs($staff)->delete(route('staff.invoices.destroy', $draft))
            ->assertRedirect(route('staff.invoices.index'));
        $this->assertSoftDeleted($draft);

        $issued = $this->invoice($project, $staff);
        $this->actingAs($staff)->patch(route('staff.invoices.cancel', $issued))->assertRedirect();
        $this->assertSame('cancelled', $issued->fresh()->status);
    }

    private function company(string $name = 'Acme', string $status = 'active'): Company
    {
        return Company::create(['company_code' => fake()->unique()->bothify('COM-#####'), 'company_name' => $name, 'status' => $status]);
    }

    private function customer(Company $company): User
    {
        return User::factory()->create(['company_id' => $company->id, 'role' => 'customer_user', 'status' => 'active']);
    }

    private function project(User $customer): Project
    {
        return Project::create(['project_number' => fake()->unique()->bothify('PRJ-2026-#####'), 'company_id' => $customer->company_id, 'created_by' => $customer->id, 'project_name' => 'Test Project', 'priority' => 'normal', 'status' => 'submitted']);
    }

    private function invoice(Project $project, User $creator): Invoice
    {
        return Invoice::create(['invoice_number' => fake()->unique()->bothify('INV-2026-#####'), 'company_id' => $project->company_id, 'project_id' => $project->id, 'invoice_date' => today(), 'due_date' => today()->addMonth(), 'currency' => 'SAR', 'subtotal' => 100, 'tax_amount' => 15, 'total_amount' => 115, 'status' => 'issued', 'approval_status' => 'pending', 'payment_status' => 'unpaid', 'created_by' => $creator->id]);
    }
}
