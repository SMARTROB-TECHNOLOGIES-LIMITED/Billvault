@extends('admin.layout')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/vendor/fonts/circular-std/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/libs/css/style.css') }}">
    {{-- <link rel="stylesheet" href="{{ asset('assets/admin/vendor/fonts/fontawesome/css/fontawesome-all.css') }}"> --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/vendor/datatables/css/dataTables.bootstrap4.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/vendor/datatables/css/buttons.bootstrap4.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/vendor/datatables/css/select.bootstrap4.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/vendor/datatables/css/fixedHeader.bootstrap4.css') }}">
@endpush
@section('content')
    <!-- ============================================================== -->
        <!-- wrapper  -->
        <!-- ============================================================== -->
        <div class="dashboard-wrapper">
            <div class="container-fluid  dashboard-content">
                <!-- ============================================================== -->
                <!-- pageheader -->
                <!-- ============================================================== -->
                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                        <div class="page-header">
                            <h2 class="pageheader-title">Customer List</h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link">Customers</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">List</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- end pageheader -->
                <!-- ============================================================== -->
                <div class="row">
                    <!-- ============================================================== -->
                    <!-- data table  -->
                    <!-- ============================================================== -->
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between">
                                <div>
                                    <h5 class="mb-0">Customer List</h5>
                                    <p>View, Update, Ban/Deactivate Customers</p>
                                </div>
                                <div>
                                    <p>
                                        <span class="cursor-pointer badge badge-success py-2">
                                                                <i class="fas fa-cancel"></i></span> = Toggle Restriction for kyc 
                                    </p>
                                    
                                    <p>
                                        <span class="cursor-pointer badge badge-warning py-2"><i class="fas fa-user-times"></i></span> = Toggle account for ban
                                    </p>
                                    
                                    <p>
                                        <span class="cursor-pointer badge badge-primary py-2"><i class="fas fa-key"></i></span> = Toggle Login access
                                    </p>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="example" class="table table-striped table-bordered second" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Username</th>
                                                <th>Phone Number</th>
                                                <th>Email Address</th>
                                                <th>Balance</th>
                                                <th>Level</th>
                                                <th>Joined On</th>
                                                <th width="5%" class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($list as $k => $ls)
                                                <tr>
                                                    <td>{{ $k + 1 }}</td>
                                                    <td>{{ ucfirst($ls->first_name. ' ' .$ls->surname) }}</td>
                                                    <td>{{ $ls->username }}</td>
                                                    <td>{{ $ls->phone_number }}</td>
                                                    <td>{{ $ls->email }}</td>
                                                    <td><b>N </b>{{ number_format($ls->balance,2) }}</td>
                                                    <td>{{ $ls->account_level }}</td>
                                                    <td>{{ $ls->created_at }}</td>
                                                    <td>
                                                        <div class="d-flex">
                                                            <a href="{{ route('admin.customer.view', ['uid'=>$ls->id]) }}">
                                                                <span class="cursor-pointer badge badge-secondary float-right py-2"><i class="fas fa-edit"></i></span></a>
                                                            <span style="width: 5px;"></span>
                                                            <a href="{{ route('admin.customer.toggle', ['uid'=>$ls->id]) }}">
                                                                <span class="cursor-pointer badge {{ $ls->is_ban == 1 ? 'badge-danger' : 'badge-warning' }} float-right py-2"><i class="fas fa-user-times"></i></span></a>
                                                            
                                                            <span style="width: 5px;"></span>
                                                            <a href="{{ route('admin.customer.restrict', ['uid'=>$ls->id]) }}">
                                                                <span class="cursor-pointer badge {{ $ls->is_account_restricted == 0 ? 'badge-success' : 'badge-danger' }} float-right py-2">
                                                                <i class="fas fa-cancel"></i></span></a>
                                                            
                                                            <span style="width: 5px;"></span>
                                                            <a href="{{ route('admin.customer.toggle_login', ['uid'=>$ls->id]) }}">
                                                                <span class="cursor-pointer badge {{ $ls->view == 1 ? 'badge-primary' : 'badge-danger' }} float-right py-2">
                                                                <i class="fas fa-key"></i></span></a>
                                                            
                                                            <span style="width: 5px;"></span>
                                                            <a onclick="return confirm('Are you sure you want to delete?')" href="{{ route('admin.customer.delete', ['uid'=>$ls->id]) }}">
                                                                <span class="cursor-pointer badge badge-danger float-right py-2"><i class="fas fa-trash"></i></span></a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Username</th>
                                                <th>Phone Number</th>
                                                <th>Email Address</th>
                                                <th>Balance</th>
                                                <th>Level</th>
                                                <th>Joined On</th>
                                                <th width="5%" class="text-center">Action</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ============================================================== -->
                    <!-- end data table  -->
                    <!-- ============================================================== -->
                </div>
            </div>
            <!-- ============================================================== -->
            <!-- footer -->
            <!-- ============================================================== -->
            <div class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                            Copyright © 2018 Concept. All rights reserved. Dashboard by <a href="https://colorlib.com/wp/">Colorlib</a>.
                        </div>
                        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                            <div class="text-md-right footer-links d-none d-sm-block">
                                <a href="javascript: void(0);">About</a>
                                <a href="javascript: void(0);">Support</a>
                                <a href="javascript: void(0);">Contact Us</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ============================================================== -->
            <!-- end footer -->
            <!-- ============================================================== -->
        </div>
    </div>
    <!-- ============================================================== -->
    <!-- end main wrapper -->
    <!-- ============================================================== -->
@endsection
@push('scripts')
    <script src="{{ asset('assets/admin/vendor/jquery/jquery-3.3.1.min.js') }}"></script>
    <script src="{{ asset('assets/admin/vendor/bootstrap/js/bootstrap.bundle.js') }}"></script>
    <script src="{{ asset('assets/admin/vendor/slimscroll/jquery.slimscroll.js') }}"></script>
    <script src="{{ asset('assets/admin/vendor/multi-select/js/jquery.multi-select.js') }}"></script>
    <script src="{{ asset('assets/admin/libs/js/main-js.js') }}"></script>
    <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
    <script src="{{ asset('assets/admin/vendor/datatables/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.2/js/dataTables.buttons.min.js"></script>
    <script src="{{ asset('assets/admin/vendor/datatables/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/admin/vendor/datatables/js/data-table.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.colVis.min.js"></script>
    <script src="https://cdn.datatables.net/rowgroup/1.0.4/js/dataTables.rowGroup.min.js"></script>
    <script src="https://cdn.datatables.net/select/1.2.7/js/dataTables.select.min.js"></script>
    <script src="https://cdn.datatables.net/fixedheader/3.1.5/js/dataTables.fixedHeader.min.js"></script>
@endpush