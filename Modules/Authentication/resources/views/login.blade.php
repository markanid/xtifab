<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>XtiFab&trade; | Login </title>

    <link rel="stylesheet" href="{{ asset('admin-assets/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/dist/css/adminlte.min.css') }}">
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="login-logo">
        <a href="{{ route('portal.home') }}"><b>XtiFab&trade;</b></a>
    </div>

    <div class="card card-outline card-orange">
        <div class="card-header text-center">
            <span class="h4">Login</span>
        </div>
        <div class="card-body login-card-body">

            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="icon fas fa-ban mr-1"></i>{{ $errors->first() }}
                </div>
            @endif

            <form method="post" action="{{ route('login.store') }}">
                @csrf
                <div class="input-group mb-3">
                    <input
                        class="form-control @error('email') is-invalid @enderror"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="Email"
                        autocomplete="username"
                        required
                        autofocus
                    >
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-envelope"></span></div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input
                        class="form-control"
                        type="password"
                        name="password"
                        placeholder="Password"
                        autocomplete="current-password"
                        required
                    >
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-lock"></span></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-7">
                        <div class="icheck-primary">
                            <input type="checkbox" id="remember" name="remember" value="1">
                            <label for="remember">Remember me</label>
                        </div>
                    </div>
                    <div class="col-5">
                        <button class="btn btn-flat btn-block bg-orange">Sign In</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center">
        Powered by <a href="https://apexsoftlabs.com" target="_blank" rel="noopener noreferrer" class="text-orange" style="color: orange;">Apex Soft Labs</a>
    </div>
</div>

<script src="{{ asset('admin-assets/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('admin-assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('admin-assets/dist/js/adminlte.min.js') }}"></script>
</body>
</html>
