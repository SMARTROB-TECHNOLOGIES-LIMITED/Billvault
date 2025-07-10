@extends('admin.layout')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/vendor/fonts/circular-std/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/libs/css/style.css') }}">
    {{-- <link rel="stylesheet" href="{{ asset('assets/admin/vendor/fonts/fontawesome/css/fontawesome-all.css') }}"> --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

@endpush
@section('content')
    <div class="dashboard-wrapper">
        <div class="container-fluid  dashboard-content">
            <!-- ============================================================== -->
            <!-- pageheader -->
            <!-- ============================================================== -->
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                    <div class="page-header">
                        <h2 class="pageheader-title">User Account Top Up</h2>
                        <div class="page-breadcrumb">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('admin.customer.list') }}" class="breadcrumb-link">Airtime to cash</a></li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row pb-3">
                <div class="col-xl-10 col-lg-10 col-md-12 col-sm-12 col-12">
                    <div class="card h-100">
                        <h5 class="card-header"></h5>
                        <div class="card-body">
                            <div class="container">
                                <h4 class="my-2">Airtime to Cash Settings</h4>
                            
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Network Name</th>
                                            <th>Status</th>
                                            <th>Receiver Number</th>
                                            <th>Payment %</th>
                                            <th>Min Airtime</th>
                                            <th>Max Airtime</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($settings as $setting)
                                        <tr>
                                            <td>{{ $setting->network_name }}</td>
                                            <td>{{ $setting->is_enabled ? 'Enabled' : 'Disabled' }}</td>
                                            <td>{{ $setting->receiver_number }}</td>
                                            <td>{{ $setting->payment_percentage }}%</td>
                                            <td>{{ number_format($setting->minimum_airtime, 2) }}</td>
                                            <td>{{ number_format($setting->maximum_airtime, 2) }}</td>
                                            <td>
                                                <form action="/admin/airtime-to-cash/{{$setting->id}}/toggle-status" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="btn btn-sm {{ $setting->is_enabled ? 'btn-danger' : 'btn-success' }}">
                                                        {{ $setting->is_enabled ? 'Disable' : 'Enable' }}
                                                    </button>
                                                </form>
                                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editNetworkModal{{ $setting->id }}">
                                                    Edit
                                                </button>
                                            </td>
                                        </tr>
                            
                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editNetworkModal{{ $setting->id }}" tabindex="-1" aria-labelledby="editNetworkModalLabel{{ $setting->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editNetworkModalLabel{{ $setting->id }}">Edit Network</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="/admin/airtime-to-cash/{{$setting->id}}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label for="network_name" class="form-label">Network Name</label>
                                                                <input type="text" name="network_name" id="network_name" class="form-control" value="{{ $setting->network_name }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="receiver_number" class="form-label">Receiver Number</label>
                                                                <input type="text" name="receiver_number" id="receiver_number" class="form-control" value="{{ $setting->receiver_number }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="payment_percentage" class="form-label">Payment Percentage</label>
                                                                <input type="number" step="0.01" name="payment_percentage" id="payment_percentage" class="form-control" value="{{ $setting->payment_percentage }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="minimum_airtime" class="form-label">Minimum Airtime</label>
                                                                <input type="number" name="minimum_airtime" id="minimum_airtime" class="form-control" value="{{ $setting->minimum_airtime }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="maximum_airtime" class="form-label">Maximum Airtime</label>
                                                                <input type="number" name="maximum_airtime" id="maximum_airtime" class="form-control" value="{{ $setting->maximum_airtime }}" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
                
            </div>
            
        </div>
        <div class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                        Copyright © <?=date('Y')?> Concept. All rights reserved.
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
@endsection
@push('scripts')
    <script src="{{ asset('assets/admin/vendor/jquery/jquery-3.3.1.min.js') }}"></script>
    <script src="{{ asset('assets/admin/vendor/bootstrap/js/bootstrap.bundle.js') }}"></script>
    <script src="{{ asset('assets/admin/vendor/slimscroll/jquery.slimscroll.js') }}"></script>
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
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.select2').select2();
        });
    </script>
@endpush