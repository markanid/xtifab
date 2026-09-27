@extends('portal.layout')
@section('title', 'Customer Users')
@section('content')
<div class="card card-outline card-orange">
    <div class="card-header"><h3 class="card-title">Customer login accounts</h3><a class="btn btn-flat btn-sm float-right bg-orange" href="{{ route('staff.customers.create') }}"><i class="fas fa-user-plus mr-1"></i> Create Customer User</a></div>
    <div class="card-body"><table class="table table-bordered table-hover portal-data-table"><thead><tr><th>Name</th><th>Company</th><th>Email</th><th>Status</th><th>Last Login</th><th class="text-right no-export">Actions</th></tr></thead><tbody>
        @foreach($customers as $customer)<tr><td><a href="{{ route('staff.customers.show', $customer) }}">{{ $customer->name }}</a></td><td>{{ $customer->company?->company_name }}</td><td>{{ $customer->email }}</td><td><span class="badge badge-{{ $customer->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($customer->status) }}</span></td><td>{{ $customer->last_login_at?->diffForHumans() ?: 'Never' }}</td><td class="text-right"><div class="btn-group"><a href="{{ route('staff.customers.edit', $customer) }}" class="btn btn-primary btn-flat btn-sm"><i class="fas fa-edit"></i></a><form method="post" action="{{ route('staff.customers.status', $customer) }}" data-confirm="{{ $customer->status === 'active' ? 'Deactivate' : 'Activate' }} the login for {{ $customer->name }}?">@csrf @method('PATCH')<button class="btn btn-{{ $customer->status === 'active' ? 'warning' : 'success' }} btn-flat btn-sm" title="{{ $customer->status === 'active' ? 'Deactivate' : 'Activate' }}"><i class="fas fa-{{ $customer->status === 'active' ? 'user-slash' : 'user-check' }}"></i></button></form></div></td></tr>@endforeach
    </tbody></table></div>
</div>
@endsection
