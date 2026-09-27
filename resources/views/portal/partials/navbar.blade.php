<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <!-- <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ route(auth()->user()->isStaff() ? 'staff.dashboard' : 'customer.dashboard') }}" class="nav-link">Dashboard</a>
        </li> -->
    </ul>

    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="far fa-bell"></i>
                @if(auth()->user()->unreadNotifications()->count())
                    <span class="badge badge-warning navbar-badge">{{ auth()->user()->unreadNotifications()->count() }}</span>
                @endif
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <span class="dropdown-header">{{ auth()->user()->unreadNotifications()->count() }} unread notifications</span>
                <div class="dropdown-divider"></div>
                @forelse(auth()->user()->notifications()->latest()->limit(5)->get() as $notification)
                    <a href="{{ $notification->data['url'] ?? route('notifications.index') }}" class="dropdown-item">
                        <i class="fas fa-info-circle mr-2 text-info"></i>
                        {{ \Illuminate\Support\Str::limit($notification->data['title'] ?? 'Notification', 30) }}
                        <span class="float-right text-muted text-sm">{{ $notification->created_at->diffForHumans(null, true) }}</span>
                    </a>
                    <div class="dropdown-divider"></div>
                @empty
                    <span class="dropdown-item text-muted">No notifications</span>
                    <div class="dropdown-divider"></div>
                @endforelse
                <a href="{{ route('notifications.index') }}" class="dropdown-item dropdown-footer">View all notifications</a>
            </div>
        </li>
        <li class="nav-item dropdown user-menu">
            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                <i class="far fa-user-circle mr-1"></i>
                <!-- <span class="d-none d-md-inline">{{ auth()->user()->name }}</span> -->
            </a>
            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <li class="user-header bg-orange">
                    <i class="far fa-user-circle fa-4x text-white"></i>
                    <p>{{ auth()->user()->name }}<small>{{ ucwords(str_replace('_', ' ', auth()->user()->role)) }}</small></p>
                </li>
                <li class="user-footer">
                    <div class="row">
                        <div class="col-7 pr-1">
                            <a href="{{ route('account.password.edit') }}" class="btn btn-default btn-flat btn-block btn-sm">
                                <i class="fas fa-key mr-1"></i>Change Password
                            </a>
                        </div>
                        <div class="col-5 pl-1">
                            <form method="post" action="{{ route('portal.logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-flat btn-block btn-sm bg-orange">
                                    <i class="fas fa-sign-out-alt mr-1"></i>Sign out
                                </button>
                            </form>
                        </div>
                    </div>
                </li>
            </ul>
        </li>
    </ul>
</nav>
