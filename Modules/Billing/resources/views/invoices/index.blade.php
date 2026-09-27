@extends('portal.layout')
@section('title', 'Invoices')
@section('content')
<div class="card card-outline card-orange">
    <div class="card-header"><h3 class="card-title">Invoice List</h3>@if(auth()->user()->isStaff())<a class="btn btn-flat btn-sm float-right bg-orange" href="{{ route('staff.invoices.create') }}"><i class="fas fa-plus mr-1"></i> Create Invoice</a>@endif</div>
    <div class="card-body"><table class="table table-bordered table-hover portal-data-table"><thead><tr><th>Invoice</th><th>Project</th><th>Company</th><th>Total</th><th>Approval</th><th>Payment</th><th>Due</th><th class="text-right no-export">Actions</th></tr></thead><tbody>
        @foreach($invoices as $invoice)
            <tr><td><a href="{{ route(auth()->user()->isStaff() ? 'staff.invoices.show' : 'customer.invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></td><td>{{ $invoice->project->project_number }}</td><td>{{ $invoice->company->company_name }}</td><td>{{ $invoice->currency }} {{ number_format($invoice->total_amount, 2) }}</td><td><span class="badge badge-info">{{ ucfirst($invoice->approval_status) }}</span></td><td><span class="badge badge-{{ $invoice->payment_status === 'paid' ? 'success' : 'warning' }}">{{ ucwords(str_replace('_', ' ', $invoice->payment_status)) }}</span></td><td>{{ $invoice->due_date->format('d M Y') }}</td><td class="text-right"><div class="btn-group">@if(auth()->user()->isStaff() && $invoice->status === 'draft')<a href="{{ route('staff.invoices.edit', $invoice) }}" class="btn btn-primary btn-flat btn-sm"><i class="fas fa-edit"></i></a><form method="post" action="{{ route('staff.invoices.destroy', $invoice) }}" data-confirm="Delete draft invoice {{ $invoice->invoice_number }}?">@csrf @method('DELETE')<button class="btn btn-danger btn-flat btn-sm"><i class="fas fa-trash"></i></button></form>@elseif(auth()->user()->isStaff() && ! in_array($invoice->status, ['cancelled', 'draft']) && $invoice->payment_status !== 'paid')<form method="post" action="{{ route('staff.invoices.cancel', $invoice) }}" data-confirm="Cancel issued invoice {{ $invoice->invoice_number }}?">@csrf @method('PATCH')<button class="btn btn-warning btn-flat btn-sm"><i class="fas fa-ban"></i></button></form>@endif</div></td></tr>
        @endforeach
    </tbody></table></div>
</div>
@endsection
