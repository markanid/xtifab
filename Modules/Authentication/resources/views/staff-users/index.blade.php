@extends('portal.layout')
@section('title', 'Staff Users')
@section('content')
<div class="card card-outline card-orange">
    <div class="card-header">
        <h3 class="card-title">XT Tab staff accounts</h3>
        <div class="card-tools">
            <a class="btn btn-flat btn-sm bg-orange" href="{{ route('staff.staff-users.create') }}">
                <i class="fas fa-user-plus mr-1"></i>Create Staff User
            </a>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-hover portal-data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th class="text-right no-export">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($staffUsers as $staffUser)
                    <tr>
                        <td>{{ $staffUser->name }}</td>
                        <td>{{ $staffUser->email }}</td>
                        <td>{{ $staffUser->phone ?: '—' }}</td>
                        <td>
                            <span class="badge badge-{{ $staffUser->role === 'super_admin' ? 'danger' : 'info' }}">
                                {{ $staffUser->role === 'super_admin' ? 'Super Admin' : 'Staff User' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-{{ $staffUser->status === 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($staffUser->status) }}
                            </span>
                        </td>
                        <td>{{ $staffUser->last_login_at?->diffForHumans() ?: 'Never' }}</td>
                        <td class="text-right">
                            @if($staffUser->role === 'xt_tab_user')
                                <div class="btn-group">
                                    <a href="{{ route('staff.staff-users.edit', $staffUser) }}" class="btn btn-primary btn-flat btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form
                                        method="post"
                                        action="{{ route('staff.staff-users.status', $staffUser) }}"
                                        data-confirm="{{ $staffUser->status === 'active' ? 'Deactivate' : 'Activate' }} the login for {{ $staffUser->name }}?"
                                    >
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-{{ $staffUser->status === 'active' ? 'warning' : 'success' }} btn-flat btn-sm" title="{{ $staffUser->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                            <i class="fas fa-{{ $staffUser->status === 'active' ? 'user-slash' : 'user-check' }}"></i>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span class="text-muted">Protected</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
