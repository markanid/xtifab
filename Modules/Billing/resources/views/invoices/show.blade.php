@extends('portal.layout')
@section('title', $invoice->invoice_number)
@section('content')
<div class="invoice p-3 mb-3">
    <div class="row"><div class="col-12"><h4><i class="fas fa-globe"></i> XT Tab <small class="float-right">Date: {{ $invoice->invoice_date->format('d M Y') }}</small></h4>@if(auth()->user()->isStaff() && $invoice->status === 'draft')<a href="{{ route('staff.invoices.edit', $invoice) }}" class="btn btn-primary btn-flat btn-sm mb-3"><i class="fas fa-edit mr-1"></i>Edit Draft</a>@endif</div></div>
    <div class="row invoice-info my-3"><div class="col-sm-6">Bill To<address><strong>{{ $invoice->company->company_name }}</strong><br>{{ $invoice->company->address }}<br>{{ $invoice->company->email }}</address></div><div class="col-sm-6"><b>Project:</b> {{ $invoice->project->project_number }}<br><b>Due:</b> {{ $invoice->due_date->format('d M Y') }}<br><b>Approval:</b> {{ ucfirst($invoice->approval_status) }}<br><b>Payment:</b> {{ ucwords(str_replace('_', ' ', $invoice->payment_status)) }}</div></div>
    <div class="table-responsive"><table class="table table-striped"><thead><tr><th>Description</th><th>Qty</th><th>Unit</th><th>Price</th><th>Total</th></tr></thead><tbody>@foreach($invoice->items as $item)<tr><td>{{ $item->description }}</td><td>{{ $item->quantity }}</td><td>{{ $item->unit }}</td><td>{{ number_format($item->unit_price, 2) }}</td><td>{{ number_format($item->line_total, 2) }}</td></tr>@endforeach</tbody></table></div>
    <div class="row"><div class="col-md-6">
        @if(auth()->user()->isCustomer() && $invoice->status === 'issued' && $invoice->approval_status === 'pending')
            <form class="d-inline" method="post" action="{{ route('customer.invoices.approve', $invoice) }}">@csrf<button class="btn btn-success btn-flat"><i class="fas fa-check mr-1"></i> Approve</button></form>
            <form class="mt-3" method="post" action="{{ route('customer.invoices.reject', $invoice) }}">@csrf<textarea name="reason" class="form-control mb-2" placeholder="Mandatory rejection reason" required></textarea><button class="btn btn-danger btn-flat"><i class="fas fa-times mr-1"></i> Reject</button></form>
        @endif
        @if(auth()->user()->isStaff() && $invoice->balance_due > 0)
            <div class="card card-outline card-info"><div class="card-header"><h3 class="card-title">Record Payment</h3></div><form method="post" action="{{ route('staff.invoices.payments.store', $invoice) }}">@csrf<div class="card-body"><input type="date" name="payment_date" value="{{ now()->toDateString() }}" class="form-control mb-2"><input type="number" name="amount" max="{{ $invoice->balance_due }}" step=".01" class="form-control mb-2" placeholder="Amount"><input name="payment_method" class="form-control mb-2" placeholder="Payment method"><button class="btn btn-flat bg-orange">Record</button></div></form></div>
        @endif
    </div><div class="col-md-6"><div class="table-responsive"><table class="table"><tr><th>Subtotal</th><td>{{ number_format($invoice->subtotal, 2) }}</td></tr><tr><th>Tax</th><td>{{ number_format($invoice->tax_amount, 2) }}</td></tr><tr><th>Discount</th><td>{{ number_format($invoice->discount_amount, 2) }}</td></tr><tr><th>Grand Total</th><td>{{ number_format($invoice->total_amount, 2) }}</td></tr><tr><th>Amount Paid</th><td>{{ $invoice->amount_paid }}</td></tr><tr><th>Balance Due</th><td>{{ $invoice->balance_due }}</td></tr></table></div></div></div>
</div>
@endsection
