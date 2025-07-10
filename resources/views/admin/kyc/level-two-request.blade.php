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
                            <div class="card-header">
                                <h5 class="mb-0">Level Two Customer Request</h5>
                                <p>View, Approve, Decline Request</p>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="example" class="table table-striped table-bordered second" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>DoB</th>
                                                <th>User Picture</th>
                                                <th>BVN</th>
                                                <th>ID Type</th>
                                                <th>ID Card</th>
                                                <th>Status</th>
                                                <th>Submitted On</th>
                                                <th width="5%" class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($list as $k => $ls)
                                                <tr>
                                                    <td>{{ $k + 1 }}</td>
                                                    <td>{{ ucfirst($ls->user->first_name ?? '') . ' ' . ucfirst($ls->user->surname ?? '') }}</td>
                                                    <td>{{ $ls->date_of_birth }}</td>
                                                    <td>
                                                        @if ($ls->user && $ls->user->profile)
                                                            <a target="_blank" href="{{ asset($ls->user->profile) }}"> 
                                                                <img width="100px" height="100px" src="{{ asset($ls->user->profile) }}" alt="Profile Picture"> 
                                                            </a>
                                                        @else
                                                            <img width="100px" height="100px" src="{{ asset('/default/image.png') }}" alt="Default Profile Picture">
                                                        @endif
                                                    </td> 

                                                    <td>{{ $ls->bvn }}</td>
                                                    <td>{{ ucfirst($ls->user->id_type ?? '') }}</td>
                                                    <td>
                                                        <a target="_blank" href="{{asset('storage/app/public/'. $ls->id_front)}}"> 
                                                            <img width="100px" height="100px" src="{{asset('storage/app/public/'.$ls->id_front)}}"> 
                                                        </a>
                                                    </td>
                                                    <td>
                                                        @if($ls->status == 0)
                                                            <span class="badge bg-warning">Pending</span>
                                                        @elseif($ls->status == 1)
                                                            <span class="badge bg-success">Approved</span>
                                                        @elseif($ls->status == 2)
                                                            <span class="badge bg-danger">Declined</span>
                                                        @else
                                                            <span class="badge bg-secondary">Unknown Status</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $ls->created_at }}</td>
                                                    <td>
                                                        <div class="d-flex">
                                                            <a href="#" onclick="openApproveModal('{{ $ls->id }}')">
                                                                <span class="cursor-pointer badge badge-primary float-right py-2" title="Approve">
                                                                    <i class="fas fa-check"></i>
                                                                </span>
                                                            </a>
                                                            
                                                            <span style="width: 5px;"></span>
                                                            <a href="#" onclick="openRejectModal('{{ $ls->id }}')">
                                                                <span class="cursor-pointer badge badge-danger float-right py-2" title="Decline">
                                                                    <i class="fas fa-times"></i>
                                                                </span>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <!--<tfoot>-->
                                        <!--    <tr>-->
                                        <!--        <th>#</th>-->
                                        <!--        <th>Name</th>-->
                                        <!--        <th>Username</th>-->
                                        <!--        <th>Phone Number</th>-->
                                        <!--        <th>Email Address</th>-->
                                        <!--        <th>Balance</th>-->
                                        <!--        <th>Level</th>-->
                                        <!--        <th>Joined On</th>-->
                                        <!--        <th width="5%" class="text-center">Action</th>-->
                                        <!--    </tr>-->
                                        <!--</tfoot>-->
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
                <!--Modal Start-->
                <!-- Approve Confirmation Modal -->
                <div id="approveModal" class="modal fade" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="approveModalLabel">Confirm Approval</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form id="approveForm" method="POST" action="{{ route('admin.kyc.approve-level-two') }}">
                                @csrf
                                <div class="modal-body">
                                    <input type="hidden" name="user_id" id="approveUserId">
                                    <p>Are you sure you want to approve this request?</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Approve</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!--Rejection modal-->
                <div id="rejectModal" class="modal fade" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="rejectModalLabel">Provide Rejection Reason</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form id="rejectForm" method="POST" action="{{ route('admin.kyc.reject-level-two') }}">
                                @csrf
                                <div class="modal-body">
                                    <input type="hidden" name="kyc_id" id="rejectUserId">
                                    <div class="mb-3">
                                        <label for="rejectReason" class="form-label">Reason</label>
                                        <textarea class="form-control" id="rejectReason" name="reject_reason" rows="3" required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-danger">Reject</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!--Modal Ends-->
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
    <script>
        function openRejectModal(userId) {
            document.getElementById('rejectUserId').value = userId;
            var myModal = new bootstrap.Modal(document.getElementById('rejectModal'));
            myModal.show();
        }
        
        function openApproveModal(userId) {
            document.getElementById('approveUserId').value = userId;
            var approveModal = new bootstrap.Modal(document.getElementById('approveModal'));
            approveModal.show();
        }
    </script>

@endpush