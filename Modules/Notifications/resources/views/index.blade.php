@extends('portal.layout')
@section('title', 'Notifications')
@section('content')
<div class="card card-outline card-orange">
    <div class="card-header"><h3 class="card-title">All Notifications</h3><form method="post" action="{{ route('notifications.read-all') }}">@csrf @method('PATCH')<button class="btn btn-sm btn-default btn-flat float-right"><i class="fas fa-check-double mr-1"></i> Mark all read</button></form></div>
    <div class="list-group list-group-flush">
        @forelse($notifications as $notification)
            <div class="list-group-item {{ $notification->read_at ? '' : 'bg-light' }}"><a href="{{ $notification->data['url'] ?? '#' }}"><strong>{{ $notification->data['title'] ?? 'Notification' }}</strong><p class="mb-0 text-muted">{{ $notification->data['message'] ?? '' }}</p></a><small><i class="far fa-clock mr-1"></i>{{ $notification->created_at->diffForHumans() }}</small>@unless($notification->read_at)<form method="post" class="float-right" action="{{ route('notifications.read', $notification->id) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-link btn-flat">Mark read</button></form>@endunless</div>
        @empty
            <div class="p-4 text-center text-muted"><i class="far fa-bell-slash fa-2x mb-2 d-block"></i>No notifications.</div>
        @endforelse
    </div>
    <div class="card-footer clearfix">{{ $notifications->links() }}</div>
</div>
@endsection
