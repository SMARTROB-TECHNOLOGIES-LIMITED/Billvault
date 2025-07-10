@extends('admin.layout')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/vendor/fonts/circular-std/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/libs/css/style.css') }}">
    {{-- <link rel="stylesheet" href="{{ asset('assets/admin/vendor/fonts/fontawesome/css/fontawesome-all.css') }}"> --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .custom-dropdown .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .flag-icon {
            width: 20px;
            height: 15px;
            object-fit: contain;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        .dropdown-toggle{
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
    </style>
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
                        <h2 class="pageheader-title">Manage Gift Cards</h2>
                        <div class="page-breadcrumb">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('admin.customer.list') }}" class="breadcrumb-link">Manage Gift Cards</a></li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row pb-3">
                <div class="col-xl-8 col-lg-8 col-md-12 col-sm-12 col-12">
                    <div class="card h-100">
                        <h5 class="card-header"></h5>
                        <div class="card-body">
                            <div class="container">
                                <h4>{{$giftCard->name }} Gift Card Rate(s)</h4>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Country</th>
                                            <th>Currency</th>
                                            <th>Min Amount</th>
                                            <th>Max Amount</th>
                                            <th>Rate</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($rates as $rate)
                                        <tr>
                                            <td>
                                                <img src="{{ $rate->country->flag_url }}" >
                                                {{ $rate->country->name }}
                                            </td>
                                            <td>
                                                {{ $rate->country->currency_code }}
                                            </td>
                                            <td>{{ $rate->min_amount }}</td>
                                            <td>{{ $rate->max_amount }}</td>
                                            <td>{{ $rate->rate }}</td>
                                            <td>
                                                <form action="/admin/rates/delete/{{$rate->id}}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm m-2" onclick="return confirm('Are you sure you want to delete?')">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
                
                
                <div class="col-xl-4 col-lg-4 col-md-12 col-sm-12 col-12">
                    <div class="card h-100">
                        <h5 class="card-header"></h5>
                        <div class="card-body">
                            <div class="container">
                                <h4>{{$giftCard->name }} Gift Card Rate</h4>
                                <form action="/admin/gift-card/{{$giftCard->id}}/rates" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="amount_range_min" class="form-label">Amount Range (Min)</label>
                                    <input type="number" name="amount_range_min" id="amount_range_min" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="amount_range_max" class="form-label">Amount Range (Max)</label>
                                    <input type="number" name="amount_range_max" id="amount_range_max" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="country" class="form-label">Country</label>
                                    <div class="custom-dropdown">
                                        <button class="dropdown-toggle form-control my-2" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                            Select Country
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            @foreach($countries as $c)
                                                <li>
                                                    <a class="dropdown-item" href="#" data-value="{{ $c->id }}">
                                                        <img src="{{ $c->flag_url }}" alt="{{ $c->name }} Flag" class="flag-icon"> {{ $c->name }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                        <input type="hidden" name="country" id="countryInput">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="rate" class="form-label">Rate</label>
                                    <input type="number" name="rate" id="rate" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Add Rate</button>
                            </form>
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
        
        document.addEventListener('DOMContentLoaded', function () {
            const dropdownItems = document.querySelectorAll('.dropdown-item');
            const dropdownButton = document.getElementById('dropdownMenuButton');
            const countryInput = document.getElementById('countryInput');
        
            dropdownItems.forEach(item => {
                item.addEventListener('click', function (e) {
                    e.preventDefault();
                    const countryName = this.textContent.trim();
                    const countryId = this.getAttribute('data-value');
                    dropdownButton.textContent = countryName; // Update dropdown button with selected country
                    countryInput.value = countryId; // Update hidden input with country ID
                });
            });
        });

    </script>
@endpush