<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('admin-assets/dist/img/favicon.png') }}">
    <title>XtiFab&trade; | @yield('title')</title>

    <link rel="stylesheet" href="{{ asset('admin-assets/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/plugins/toastr/toastr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/dist/css/adminlte.min.css') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{asset('admin-assets/dist/img/favicon.png')}}">
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed accent-orange">
<div class="wrapper">
    @include('portal.partials.navbar')
    @include('portal.partials.sidebar')

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>@yield('title', 'Dashboard')</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item">
                                <a href="{{ route(auth()->user()->isStaff() ? 'staff.dashboard' : 'customer.dashboard') }}">Home</a>
                            </li>
                            @hasSection('breadcrumb')
                                @yield('breadcrumb')
                            @else
                                <li class="breadcrumb-item active">@yield('title', 'Dashboard')</li>
                            @endif
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="icon fas fa-check"></i> {{ session('success') }}
                    </div>
                @endif

                @if(auth()->user()->must_change_password && ! request()->routeIs('account.password.*'))
                    <div class="alert alert-warning">
                        <div class="row align-items-center">
                            <div class="col-sm">
                                <i class="icon fas fa-exclamation-triangle"></i>
                                You are using a temporary password.
                            </div>
                            <div class="col-sm-auto mt-2 mt-sm-0">
                                <a href="{{ route('account.password.edit') }}" class="btn btn-outline-dark btn-flat btn-sm btn-block">
                                    <i class="fas fa-key mr-1"></i>Change Password
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <h5><i class="icon fas fa-ban"></i> Please correct the following:</h5>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </section>
    </div>

    @include('portal.partials.footer')
    @include('portal.partials.confirm-modal')
</div>

<script src="{{ asset('admin-assets/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('admin-assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('admin-assets/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
<script src="{{ asset('admin-assets/plugins/toastr/toastr.min.js') }}"></script>
<script src="{{ asset('admin-assets/dist/js/adminlte.min.js') }}"></script>
<script src="{{ asset('admin-assets/plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
<script src="{{ asset('admin-assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('admin-assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('admin-assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('admin-assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ asset('admin-assets/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('admin-assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
<script src="{{ asset('admin-assets/plugins/jszip/jszip.min.js') }}"></script>
<script src="{{ asset('admin-assets/plugins/pdfmake/pdfmake.min.js') }}"></script>
<script src="{{ asset('admin-assets/plugins/pdfmake/vfs_fonts.js') }}"></script>
<script src="{{ asset('admin-assets/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
<script>
$(function () {
    bsCustomFileInput.init();

    $('.portal-data-table').each(function () {
        var table = $(this).DataTable({
            responsive: true,
            lengthChange: true,
            autoWidth: false,
            pageLength: 10,
            order: [],
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel mr-1"></i>Excel',
                    className: 'btn-success btn-sm',
                    exportOptions: {
                        columns: ':not(.no-export)'
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf mr-1"></i>PDF',
                    className: 'btn-danger btn-sm',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: {
                        columns: ':not(.no-export)'
                    }
                }
            ]
        });

        table.buttons().container().appendTo(
            $(this).closest('.dataTables_wrapper').find('.col-md-6').first()
        );
    });

    var pendingForm = null;
    $(document).on('submit', 'form[data-confirm]', function (event) {
        event.preventDefault();
        pendingForm = this;
        $('#confirmActionMessage').text($(this).data('confirm'));
        $('#confirmActionModal').modal('show');
    });

    $('#confirmActionButton').on('click', function () {
        if (pendingForm) {
            var form = pendingForm;
            pendingForm = null;
            $('#confirmActionModal').modal('hide');
            form.submit();
        }
    });
});
</script>
</body>
</html>
