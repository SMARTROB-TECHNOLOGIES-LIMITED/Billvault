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
                        <h2 class="pageheader-title">Manual KYC</h2>
                        <div class="page-breadcrumb">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('admin.customer.list') }}" class="breadcrumb-link">Manual KYC</a></li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row pb-3">
                <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                    <div class="card h-100">
                        <div class="card-header">Level Two KYC</div>
                        <div class="card-body">
                             @if (session('error'))
                                <div class="alert alert-danger">{{ session('error') }}</div>
                            @endif
                        
                            @if (session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif
                            
                             <form id="topupForm" action="/admin/kyc/manual-level-two-kyc" enctype="multipart/form-data"  method="POST">
                                @csrf
                                <div class="form-group">
                                    <label for="user_id">Select User</label>
                                    <select id="user_id" name="user_id" class="form-control select2" required>
                                        <option value="">-- Select User --</option>
                                        @foreach($list as $user)
                                            <option value="{{ $user->id }}">{{ $user->first_name." ".$user->surname }} ({{ $user->email }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="date_of_birth">Date of Birth</label>
                                    <input type="date" name="date_of_birth" class="form-control" required>
                                </div>
                        
                                <div class="mb-3">
                                    <label for="bvn">BVN/NIN</label>
                                    <input type="text" name="bvn" maxlength="11" class="form-control" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="passport">Upload Passport Photo</label>
                                    <input type="file" name="passport" class="form-control" required>
                                </div>

                        
                                <div class="mb-3">
                                    <label for="id_type">ID Type</label>
                                    <select name="id_type" class="form-control" required>
                                        <option value="">Select ID Type</option>
                                        <option value="national_id">National ID</option>
                                        <option value="driver_license">Driver's License</option>
                                        <option value="international_passport">International Passport</option>
                                        <option value="voters_card">Voter's Card</option>
                                    </select>
                                </div>
                        
                                <div class="mb-3">
                                    <label for="id_front">Upload Front of ID</label>
                                    <input type="file" name="id_front" class="form-control" required>
                                </div>
                        
                                <div class="mb-3">
                                    <label for="id_back">Upload Back of ID (optional)</label>
                                    <input type="file" name="id_back" class="form-control">
                                </div>
                        
                                <button type="submit" class="btn btn-primary">Submit Tier 2</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                    <div class="card h-100">
                        <div class="card-header">Level Three KYC</div>
                        <div class="card-body">
                             @if (session('error'))
                                <div class="alert alert-danger">{{ session('error') }}</div>
                            @endif
                        
                            @if (session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif
                            
                             <form id="topupForm" action="/admin/kyc/manual-level-three-kyc" enctype="multipart/form-data"  method="POST">
                                @csrf
                                <div class="form-group">
                                    <label for="user_id">Select User</label>
                                    <select id="user_id" name="user_id" class="form-control select2" required>
                                        <option value="">-- Select User --</option>
                                        @foreach($list as $user)
                                            <option value="{{ $user->id }}">{{ $user->first_name." ".$user->surname }} ({{ $user->email }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="house_address">House Address</label>
                                    <input type="text" name="house_address" class="form-control" required value="{{ old('house_address') }}">
                                </div>
                        
                                <div class="mb-3">
                                    <label for="utility_bill">Upload Utility Bill</label>
                                    <input type="file" name="utility_bill" class="form-control" required>
                                </div>
                        
                                <button type="submit" class="btn btn-primary">Submit Tier 3</button>
                            </form>
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