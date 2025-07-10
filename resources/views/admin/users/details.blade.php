@extends('admin.layout')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/vendor/fonts/circular-std/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/libs/css/style.css') }}">
    {{-- <link rel="stylesheet" href="{{ asset('assets/admin/vendor/fonts/fontawesome/css/fontawesome-all.css') }}"> --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
                        <h2 class="pageheader-title">{{ $person->first_name.' '.$person->surname.' @'.$person->username }}</h2>
                        {{-- <p class="pageheader-text">Proin placerat ante duiullam scelerisque a velit ac porta, fusce sit amet vestibulum mi. Morbi lobortis pulvinar quam.</p> --}}
                        <div class="page-breadcrumb">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('admin.customer.list') }}" class="breadcrumb-link">Customers</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">{{ $person->first_name.' '.$person->surname.' @'.$person->username }}</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row pb-3">
                <div class="col-xl-8 col-lg-8 col-md-12 col-sm-12 col-12">
                    <div class="card h-100">
                        <h5 class="card-header">Account Details <span class="float-right">Balance: N {{ number_format($person->balance,2) }}</span></h5>
                        <div class="card-body">
                             <form action="{{ route('admin.customer.update', $person->id) }}" method="POST" id="basicform" data-parsley-validate="">
                                @csrf
                                @method('PUT')
                                <div class="row mb-3 px-5" style="max-height: 150px;">
                                    <div class="col-md-5 py-2 bg-danger d-flex justify-content-center" style="border-radius: 20px;">
                                        <img src="{{ $person->profile }}" alt="{{ $person->username }} Image" class="rounded-circle" style="width: 150px">
                                    </div>
                                    <div class="col-md-7">
                                        <div class="block">
                                            @php
                                                $pay = json_decode($person->paystack_id);
                                            @endphp
                                            <h5 class="mb-0"><b>Username: @</b>{{ $person->username }}</h5>
                                            {{-- <h5 class="mb-0"><b>Balance: N </b>{{ number_format($person->balance,2) }}</h5> --}}
                                            <h5 class="mb-0"><b>Account Number: </b>{{ $person->account_number }}</h5>
                                            <h5 class="mb-0"><b>Account Level: </b>{{ $person->account_level }}</h5>
                                            <h5 class="mb-0"><b>BVN: </b>{{ $person->bvn }}</h5>
                                            @if (!empty($person->referral))
                                                <h5 class="mb-0"><b>Referred by: </b>{{ $person->referral }}</h5>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-5">
                                    <div class="form-group col-md-6">
                                        <label for="mfkmdk">First Name</label>
                                        <input id="mfkmdk" type="text" name="first_name" value="{{ $person->first_name }}" required="" autocomplete="off" class="form-control">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="ckmvkvmk">Surname</label>
                                        <input id="ckmvkvmk" type="text" name="surname" value="{{ $person->surname }}" required="" autocomplete="off" class="form-control">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="dmfvkcmsk">Other Name</label>
                                        <input id="dmfvkcmsk" type="text" name="other_name" value="{{ $person->other_name }}" autocomplete="off" class="form-control">
                                    </div>
                                     <div class="form-group col-md-6">
                                        <label for="jmbgjrggr">Username</label>
                                        <input id="jmbgjrggr" type="text" name="username" value="{{ $person->username }}" required="" autocomplete="off" class="form-control">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="mfbkfvmck">Email Address</label>
                                        <input id="mfbkfvmck" type="email" name="email" value="{{ $person->email }}" required="" autocomplete="off" class="form-control">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="mfbgomjvm">Phone Number</label>
                                        <input id="mfbgomjvm" type="text" name="phone_number" value="{{ $person->phone_number }}" required="" autocomplete="off" class="form-control">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="kmfkmbkmhmb">Date</label>
                                        <input id="kmfkmbkmhmb" max="2013-04-04" type="date" name="dob" value="{{ $person->dob }}" autocomplete="off" class="form-control">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="cvfgobmkbm">Gender</label>
                                        <select id="cvfgobmkbm" class="form-control" name="gender" required>
                                            <option disabled {{ $person->gender ? '' : 'selected' }}>Select Gender</option>
                                            <option value="Male" {{ $person->gender == 'Male' ? 'selected' : '' }}>Male</option>
                                            <option value="Female" {{ $person->gender == 'Female' ? 'selected' : '' }}>Female</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label for="kfgkgkj">Address</label>
                                        <input id="kfgkgkj" type="text" name="address" value="{{ $person->address }}" " autocomplete="off" class="form-control">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 pl-0">
                                        <p class="text-right">
                                            <button type="submit" class="btn btn-space btn-primary">Submit</button>
                                            <button type="reset" class="btn btn-space btn-secondary">Cancel</button>
                                        </p>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-12 col-sm-12 col-12">
                    <div class="card h-100">
                        <h5 class="card-header">Referral ({{ $referrals->count() }})</h5>
                        @if ($referrals->count() > 0)
                            <div class="card-body overflow-scroll">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">First</th>
                                            <th scope="col">Last</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($referrals as $k => $ref)
                                            <tr onclick="window.location.href = '{{ route('admin.customer.view', ['uid'=>$ref->id]) }}'" style="cursor: pointer;">
                                                <th scope="row">{{ $k+1 }}</th>
                                                <td>{{ !empty($ref->first_name) ? $ref->first_name.' '.$ref->surname : "Not set" }}</td>
                                                <td>{{ !empty($ref->username) ? $ref->username : "Not set" }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="card-body d-flex justify-content-center align-items-center">
                                <h5 class="text-muted text-center">No record found.</h5>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="row">
                <!-- ============================================================== -->
                <!-- data table  -->
                <!-- ============================================================== -->
                <div class="col-xl-8 col-lg-8 col-md-12 col-sm-12 col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Transactions List ({{ $transactions->count() }})</h5>
                            <p>View {{ $person->first_name.' '.$person->surname }} transactions.</p>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="example" class="table table-striped table-bordered second" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th width="5%">Ref ID</th>
                                            <th>Transaction</th>
                                            <th>Amount</th>
                                            <th>Recipient/Sender</th>
                                            <th>Status</th>
                                            <th>Created&nbsp;At</th>
                                            <th width="5%" class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($transactions as $k => $trf)
                                            @php
                                                $data = json_decode($trf->data);
                                                // dd($data);
                                            @endphp
                                            <tr>
                                                <td>{{ $k + 1 }}</td>
                                                <td>{{ $trf->transaction_id }}</td>
                                                <td>{{ $trf->type }}</td>
                                                <td><b>N </b>{{ number_format($trf->amount,2) }}</td>
                                                <td>
                                                    @if ($trf->type == "Deposit")
                                                        <p>{{ $data->authorization->sender_bank ?? "" }} || {{ $data->authorization->sender_bank_account_number ?? "" }} || {{ $data->authorization->account_name ?? "" }}</p>
                                                    @elseif($trf->type == "Transfer")
                                                        @if (isset($data->bank_name) && str_contains($data->bank_name,"Paypoint"))
                                                            <p>{{ $data->bank_name ?? "" }} || {{ $data->account_number ?? "" }} || {{ $data->account_name ?? "" }}</p>
                                                        @else
                                                            <p>{{ $data->bank_name ?? "" }} || {{ $data->account_number ?? "" }} || {{ $data->account_name ?? "" }}</p>
                                                        @endif
                                                    @elseif($trf->type == "Electricity")
                                                        <p>{{ $data->service_name ?? "" }} || {{ $data->metreNo ?? "" }} || {{ $data->units ?? "" }}</p>
                                                    @elseif($trf->type == "Cable TV")
                                                        <p>{{ $data->service_name ?? "" }} || {{ $data->metreNo ?? "" }} || {{ $data->units ?? "" }}</p>
                                                    @elseif($trf->type == "Airtime")
                                                        <p>{{ $data->service_name ?? "" }} N{{ number_format($trf->amount,2) }} to {{ $data->phone ?? "" }}</p>
                                                    @elseif($trf->type == "Data")
                                                        <p>{{ $data->service_name ?? "" }} N{{ number_format($trf->amount,2) }} to {{ $data->phone ?? "" }}</p>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($trf->status == "successful")
                                                        <span class="badge badge-success">{{ $trf->status }}</span>
                                                    @elseif ($trf->status == "reversed")
                                                        <span class="badge badge-warning">{{ $trf->status }}</span>
                                                    @elseif ($trf->status == "pending")
                                                        <span class="badge badge-info">{{ $trf->status }}</span>
                                                    @elseif ($trf->status == "failed")
                                                        <span class="badge badge-danger">{{ $trf->status }}</span>
                                                    @else
                                                        <span class="badge badge-secondary">{{ $trf->status }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $trf->created_at->diffForHumans() }}</td>
                                                <td>
                                                    <div class="d-flex">
                                                        <a href="{{ route('admin.customer.view', ['uid'=>$trf->id]) }}"><span class="cursor-pointer badge badge-primary float-right py-2"><i class="fas fa-eye"></i></span></a>
                                                        {{-- <span style="width: 5px;"></span>
                                                        <a href="{{ route('admin.customer.toggle', ['uid'=>$trf->id]) }}"><span class="cursor-pointer badge {{ $trf->view == 1 ? 'badge-warning' : 'badge-danger' }} float-right py-2"><i class="fas fa-user-times"></i></span></a> --}}
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>#</th>
                                            <th width="5%">Ref ID</th>
                                            <th>Transaction</th>
                                            <th>Amount</th>
                                            <th>Recipient/Sender</th>
                                            <th>Status</th>
                                            <th>Created&nbsp;At</th>
                                            <th width="5%" class="text-center">Action</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-4 col-lg-4 col-md-12 col-sm-12 col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Login History</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="example" class="table table-striped table-bordered" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Date/Time</th>
                                            <th>IP Address</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($activities as $k => $activity)
                                            <tr>
                                                <td>{{ $k + 1 }}</td>
                                                <td>{{ $activity->logged_in_at }}</td>
                                                <td>{{ $activity->ip_address }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
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
@endpush