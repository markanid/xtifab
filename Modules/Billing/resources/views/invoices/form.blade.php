@extends('portal.layout')
@section('title', $invoice->exists ? 'Edit Draft Invoice' : 'Create Invoice')
@section('content')
@php
    $formItems = old('items', $invoice->exists
        ? $invoice->items->map(fn ($item) => $item->only(['description', 'quantity', 'unit', 'unit_price']))->all()
        : [['description' => '', 'quantity' => 1, 'unit' => 'item', 'unit_price' => '']]);
@endphp
<form method="post" action="{{ $invoice->exists ? route('staff.invoices.update', $invoice) : route('staff.invoices.store') }}">
    @csrf
    @if($invoice->exists) @method('PUT') @endif
    <div class="card card-orange">
        <div class="card-header">
            <h3 class="card-title">Invoice Details</h3>
            <div class="card-tools">
                <a href="{{ $invoice->exists ? route('staff.invoices.show', $invoice) : route('staff.invoices.index') }}" class="btn btn-flat btn-sm" style="background-color:#000;color:#fff;">
                    <i class="fas fa-arrow-alt-circle-left mr-1"></i>Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="form-group col-md-6"><label class="required">Project</label><select name="project_id" class="form-control">@foreach($projects as $project)<option value="{{ $project->id }}" @selected(old('project_id', $invoice->project_id) == $project->id)>{{ $project->project_number }} — {{ $project->company->company_name }}</option>@endforeach</select></div>
                <div class="form-group col-md-3"><label class="required">Invoice Date</label><input type="date" name="invoice_date" value="{{ old('invoice_date', $invoice->invoice_date?->format('Y-m-d') ?: now()->toDateString()) }}" class="form-control"></div>
                <div class="form-group col-md-3"><label class="required">Due Date</label><input type="date" name="due_date" value="{{ old('due_date', $invoice->due_date?->format('Y-m-d') ?: now()->addDays(30)->toDateString()) }}" class="form-control"></div>
                <div class="form-group col-md-3"><label>Currency</label><input name="currency" value="{{ old('currency', $invoice->currency ?: 'SAR') }}" class="form-control"></div>
                <div class="form-group col-md-3"><label>Tax %</label><input type="number" step=".01" min="0" name="tax_percentage" value="{{ old('tax_percentage', $invoice->tax_percentage ?? 15) }}" class="form-control"></div>
                <div class="form-group col-md-3"><label>Discount</label><input type="number" step=".01" min="0" name="discount_amount" value="{{ old('discount_amount', $invoice->discount_amount ?? 0) }}" class="form-control"></div>
                <div class="form-group col-md-3"><label>Status</label><select name="status" class="form-control"><option value="draft" @selected(old('status', $invoice->status ?: 'draft') === 'draft')>Draft</option><option value="issued" @selected(old('status', $invoice->status) === 'issued')>Issued</option></select></div>
            </div>
            <h5 class="mt-3">Line Items</h5>
            <div id="items">
                @foreach($formItems as $index => $item)
                    <div class="row item {{ $loop->first ? '' : 'mt-2' }}">
                        <div class="col-md-5"><input name="items[{{ $index }}][description]" value="{{ $item['description'] }}" class="form-control" placeholder="Description" required></div>
                        <div class="col-md-2"><input name="items[{{ $index }}][quantity]" type="number" step=".0001" min=".0001" value="{{ $item['quantity'] }}" class="form-control" required></div>
                        <div class="col-md-2"><input name="items[{{ $index }}][unit]" value="{{ $item['unit'] }}" class="form-control" required></div>
                        <div class="col-md-3"><input name="items[{{ $index }}][unit_price]" type="number" step=".01" min="0" value="{{ $item['unit_price'] }}" class="form-control" placeholder="Unit price" required></div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="card-footer d-flex justify-content-center align-items-center" style="gap:1rem;"><button class="btn btn-flat bg-orange"><i class="fas fa-save mr-1"></i> {{ $invoice->exists ? 'Update Invoice' : 'Save Invoice' }}</button><button type="reset" class="btn btn-default btn-flat"><i class="fas fa-undo-alt mr-1"></i>Reset</button></div>
    </div>
</form>
@endsection
