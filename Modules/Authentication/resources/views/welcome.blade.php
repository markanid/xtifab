<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>XtiFab Tab Project Estimation Portal</title>

    <link rel="stylesheet" href="{{ asset('admin-assets/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/dist/css/adminlte.min.css') }}">
</head>
<body class="hold-transition layout-top-nav">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand-md navbar-dark navbar-navy">
        <div class="container">
            <a href="{{ route('portal.home') }}" class="navbar-brand">
                <b>XtiFab</b> Portal
            </a>
        </div>
    </nav>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container text-center">
                <h1>Project Estimation Portal</h1>
                <p class="lead text-muted">Projects, deliverables, approvals and billing in one secure workspace.</p>
            </div>
        </section>

        <section class="content">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card card-outline card-orange">
                            <div class="card-header text-center">
                                <h3 class="card-title float-none">Staff and Customer Access</h3>
                            </div>
                            <div class="card-body text-center">
                                <p>Use your registered XtiFab staff or customer account to continue.</p>
                                <a class="btn btn-primary btn-flat btn-lg btn-block" href="{{ route('login') }}">
                                    <i class="fas fa-sign-in-alt mr-2"></i>Portal Login
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <footer class="main-footer text-center">
        <strong>XtiFab Project Estimation Portal</strong>
    </footer>
</div>

<script src="{{ asset('admin-assets/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('admin-assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('admin-assets/dist/js/adminlte.min.js') }}"></script>
</body>
</html>
