<?php

namespace Modules\Billing\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Billing\Http\Requests\StoreInvoiceRequest;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Services\InvoiceService;
use Modules\Projects\Models\Project;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $invoices = Invoice::visibleTo($request->user())
            ->with(['company', 'project'])->latest()->get();

        return view('billing::invoices.index', compact('invoices'));
    }

    public function create()
    {
        return view('billing::invoices.form', [
            'invoice' => new Invoice,
            'projects' => Project::with('company')->whereNotIn('status', ['cancelled'])->latest()->get(),
        ]);
    }

    public function store(StoreInvoiceRequest $request, InvoiceService $service)
    {
        $project = Project::findOrFail($request->project_id);
        $invoice = $service->create(
            array_merge($request->validated(), ['company_id' => $project->company_id]),
            $request->user()
        );

        return redirect()->route('staff.invoices.show', $invoice)->with('success', 'Invoice created.');
    }

    public function show(Request $request, Invoice $invoice)
    {
        abort_unless(Invoice::visibleTo($request->user())->whereKey($invoice)->exists(), 403);
        $invoice->load(['company', 'project', 'items', 'payments']);

        return view('billing::invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        abort_unless($invoice->status === 'draft', 403);
        $invoice->load('items');

        return view('billing::invoices.form', [
            'invoice' => $invoice,
            'projects' => Project::with('company')->whereNotIn('status', ['cancelled'])->latest()->get(),
        ]);
    }

    public function update(StoreInvoiceRequest $request, Invoice $invoice, InvoiceService $service)
    {
        abort_unless($invoice->status === 'draft', 403);
        $project = Project::findOrFail($request->project_id);
        $service->update($invoice, array_merge(
            $request->validated(),
            ['company_id' => $project->company_id]
        ));

        return redirect()->route('staff.invoices.show', $invoice)->with('success', 'Draft invoice updated.');
    }

    public function destroy(Invoice $invoice)
    {
        abort_unless($invoice->status === 'draft' && ! $invoice->payments()->exists(), 403);
        $invoice->delete();

        return redirect()->route('staff.invoices.index')->with('success', 'Draft invoice deleted.');
    }

    public function cancel(Invoice $invoice)
    {
        abort_if($invoice->status === 'draft' || $invoice->payment_status === 'paid', 403);
        $invoice->update(['status' => 'cancelled']);

        return back()->with('success', 'Invoice cancelled.');
    }

    public function approve(Request $request, Invoice $invoice)
    {
        abort_unless(
            $request->user()->company_id === $invoice->company_id
            && $invoice->status === 'issued'
            && $invoice->approval_status === 'pending',
            403
        );
        $invoice->update([
            'approval_status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        return back()->with('success', 'Invoice approved.');
    }

    public function reject(Request $request, Invoice $invoice)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:2000']]);
        abort_unless(
            $request->user()->company_id === $invoice->company_id
            && $invoice->status === 'issued'
            && $invoice->approval_status === 'pending',
            403
        );
        $invoice->update([
            'approval_status' => 'rejected',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'rejection_reason' => $data['reason'],
        ]);

        return back()->with('success', 'Invoice rejected.');
    }

    public function recordPayment(Request $request, Invoice $invoice, InvoiceService $service)
    {
        $data = $request->validate([
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'gt:0', 'lte:'.$invoice->balance_due],
            'payment_method' => ['required', 'string'],
            'reference_number' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);
        $invoice->payments()->create(array_merge($data, ['recorded_by' => $request->user()->id]));
        $service->recalculatePaymentStatus($invoice);

        return back()->with('success', 'Payment recorded.');
    }
}
