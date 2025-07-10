@extends('admin.layout')
@push('styles')
    <link rel="stylesheet" href="{{  asset('assets/admin/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{  asset('assets/admin/vendor/fonts/circular-std/style.css') }}">
    <link rel="stylesheet" href="{{  asset('assets/admin/libs/css/style.css') }}">
    {{-- <link rel="stylesheet" href="{{ asset('assets/admin/vendor/fonts/fontawesome/css/fontawesome-all.css') }}"> --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
@endpush
@section('content')
    <div class="dashboard-wrapper">
        <div class="container-fluid dashboard-content">
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                    <div class="page-header">
                        <h2 class="pageheader-title">Transaction Settings </h2>
                        {{-- <p class="pageheader-text">Proin placerat ante duiullam scelerisque a velit ac porta, fusce sit amet vestibulum mi. Morbi lobortis pulvinar quam.</p> --}}
                        <div class="page-breadcrumb">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Transaction Settings</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row d-flex justify-content-center">
                <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="m-0">Transfer</h5>
                            <span class="cursor-pointer btn btn-primary" id="addNewInput"><i class="fa fa-plus"></i></span>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Note: Fee takes effect when transaction amount is greater than or equal to range.</p>
                            <form method="POST" id="transferForm" action="{{ route('admin.setting-submit-transfer') }}">
                                @csrf
                                <div class="w-100 pt-2" id="groupedInputs">
                                    {{-- {{ dd($transfer) }} --}}
                                    @if (count($transfer) > 0)
                                        @foreach ($transfer as $i => $tf)
                                            @php
                                                $tfIntWrand = (float) $tf['range'] * rand(1000,9999);
                                                $tfFeeWrand = (float) $tf['fee'] * rand(1000,9999);
                                            @endphp
                                            <div class="row fgExample" style="background-color: beige">
                                                <div class="form-group col-md-5">
                                                    <label for="{{ $tfIntWrand }}">Range</label>
                                                    <input id="{{ $tfIntWrand  }}" value="{{ $tf['range'] }}" onkeyup="this.nextElementSibling.innerText = ''" type="text" name="tfInt[]" placeholder="Enter range" class="form-control tfInt">
                                                    <small class="tfIntError" style="color: crimson;"></small>
                                                </div>
                                                <div class="form-group col-md-5">
                                                    <label for="{{ $tfFeeWrand }}">Fee</label>
                                                    <input id="{{ $tfFeeWrand }}" value="{{ $tf['fee'] }}" onkeyup="this.nextElementSibling.innerText = ''" type="text" name="tfFee[]" placeholder="Enter fee" class="form-control tfFee">
                                                    <small class="tfFeeError" style="color: crimson;"></small>
                                                </div>
                                                <div class="form-group col-md-2 d-flex justify-content-center align-items-center">
                                                    <i class="fa fa-times removeable" style="cursor: pointer"></i>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="row fgExample" style="background-color: beige">
                                            <div class="form-group col-md-5">
                                                <label for="tfInt">Range</label>
                                                <input id="tfInt" onkeyup="this.nextElementSibling.innerText = ''" type="text" name="tfInt[]" placeholder="Enter range" class="form-control tfInt">
                                                <small class="tfIntError" style="color: crimson;"></small>
                                            </div>
                                            <div class="form-group col-md-5">
                                                <label for="tfFee">Fee</label>
                                                <input id="tfFee" onkeyup="this.nextElementSibling.innerText = ''" type="text" name="tfFee[]" placeholder="Enter fee" class="form-control tfFee">
                                                <small class="tfFeeError" style="color: crimson;"></small>
                                            </div>
                                            <div class="form-group col-md-2 d-flex justify-content-center align-items-center">
                                                <i class="fa fa-times removeable" style="cursor: pointer"></i>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 pl-0">
                                        <p class="text-right">
                                            <button type="submit" class="btn btn-space btn-primary">Submit</button>
                                            <button class="btn btn-space btn-secondary">Cancel</button>
                                        </p>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="m-0">Deposit</h5>
                            <span class="cursor-pointer btn btn-primary" id="addNewInputDeposit"><i class="fa fa-plus"></i></span>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Note: Fee and Addon takes effect when transaction amount is greater than or equal to range. Also fee is caluclated  in %.</p>
                            <form method="POST" id="depositForm" action="{{ route('admin.setting-submit-deposit') }}">
                                @csrf
                                <div class="w-100 pt-2" id="groupedInputsDeposit">
                                    {{-- {{ dd($transfer) }} --}}
                                    @if (count($deposit) > 0)
                                        @foreach ($deposit as $i => $dp)
                                            @php
                                                $dpIntWrand = (float) $tf['range'] * rand(1000,9999);
                                                $dpFeeWrand = (float) $dp['fee'] * rand(1000,9999);
                                                $dpAddWrand = (float) $dp['addOn'] * rand(1000,9999);
                                            @endphp
                                            <div class="row dpExample" style="background-color: beige">
                                                <div class="form-group col-md-4">
                                                    <label for="{{ $dpIntWrand }}">Range</label>
                                                    <input id="{{ $dpIntWrand  }}" value="{{ $dp['range'] }}" onkeyup="this.nextElementSibling.innerText = ''" type="text" name="dpInt[]" placeholder="Enter range" class="form-control dpInt">
                                                    <small class="dpIntError" style="color: crimson;"></small>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="{{ $dpFeeWrand }}">Fee</label>
                                                    <input id="{{ $dpFeeWrand }}" value="{{ $dp['fee'] }}" onkeyup="this.nextElementSibling.innerText = ''" type="text" name="dpFee[]" placeholder="Enter fee" class="form-control dpFee">
                                                    <small class="dpFeeError" style="color: crimson;"></small>
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label for="{{ $dpAddWrand }}">Fee</label>
                                                    <input id="{{ $dpAddWrand }}" value="{{ $dp['addOn'] }}" onkeyup="this.nextElementSibling.innerText = ''" type="text" name="dpAddOn[]" placeholder="Enter fee" class="form-control dpAddOn">
                                                    <small class="dpFeeError" style="color: crimson;"></small>
                                                </div>
                                                <div class="form-group col-md-1 d-flex justify-content-center align-items-center">
                                                    <i class="fa fa-times removeableDp" style="cursor: pointer"></i>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="row dpExample" style="background-color: beige">
                                            <div class="form-group col-md-4">
                                                <label for="dpInt">Range</label>
                                                <input id="dpInt" onkeyup="this.nextElementSibling.innerText = ''" type="text" name="dpInt[]" placeholder="Enter range" class="form-control dpInt">
                                                <small class="dpIntError" style="color: crimson;"></small>
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label for="dpFee">Fee</label>
                                                <input id="dpFee" onkeyup="this.nextElementSibling.innerText = ''" type="text" name="dpFee[]" placeholder="Enter fee" class="form-control dpFee">
                                                <small class="dpFeeError" style="color: crimson;"></small>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="dpAddOn">Add-on</label>
                                                <input id="dpAddOn" onkeyup="this.nextElementSibling.innerText = ''" type="text" name="dpAddOn[]" placeholder="Enter fee" class="form-control dpAddOn">
                                                <small class="dpAddOnError" style="color: crimson;"></small>
                                            </div>
                                            <div class="form-group col-md-1 d-flex justify-content-center align-items-center">
                                                <i class="fa fa-times removeableDp" style="cursor: pointer"></i>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 pl-0">
                                        <p class="text-right">
                                            <button type="submit" class="btn btn-space btn-primary">Submit</button>
                                            <button class="btn btn-space btn-secondary">Cancel</button>
                                        </p>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
               <!-- <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="m-0">Card Creation Carges</h5>
                        </div>
                        <div class="card-body">
                            <!--<p class="text-muted">Note: Fee is caluclated  in .</p>
                            <form method="POST" id="depositForm" action="{{ route('admin.setting-submit-card-charges') }}">
                                @csrf
                                <div class="w-100 pt-2 row" id="groupedInputsDeposit">
                                    <div class="form-group col-md-4">
                                        <label for="dpInt">Fee($) [%]</label>
                                        <input  type="text" name="card_charges" placeholder="Enter Percentage" class="form-control dpInt" value="{{$card['card_charges']}}">
                                        <small class="dpIntError" style="color: crimson;"></small>
                                    </div>
                                    
                                    <div class="form-group col-md-4">
                                        <label for="dpInt">Addon($)</label>
                                        <input  type="text" name="card_addon" placeholder="Enter Amount" class="form-control dpInt" value="{{$card['card_addon']}}">
                                        <small class="dpIntError" style="color: crimson;"></small>
                                    </div>
                                    
                                    <div class="form-group col-md-4">
                                        <label for="dpInt">Top up($)</label>
                                        <input  type="text" name="top_up" placeholder="Enter Amount" class="form-control dpInt" value="{{$card['top_up']}}">
                                        <small class="dpIntError" style="color: crimson;"></small>
                                    </div>
                                    
                                    <div class="form-group col-md-4">
                                        <label for="dpInt">Initial Deposit($)</label>
                                        <input  type="text" name="deposit" placeholder="Enter Amount" min="5" class="form-control dpInt" value="{{$card['deposit']}}">
                                        <small class="dpIntError" style="color: crimson;"></small>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 pl-0">
                                        <p class="text-right">
                                            <button type="submit" class="btn btn-space btn-primary">Submit</button>
                                        </p>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="m-0">Dollar Exchage Rate</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" id="depositForm" action="{{ route('admin.setting-submit-exchange-rate') }}">
                                @csrf
                                <div class="w-100 pt-2 row" id="groupedInputsDeposit">
                                    <div class="form-group col-md-6">
                                        <label for="dpInt">NGN/USD:</label>
                                        <input  type="text" name="ngn_usd" placeholder="Enter Percentage" class="form-control dpInt" value="{{$exchangeRate['ngn_usd']}}">
                                        <small class="dpIntError" style="color: crimson;"></small>
                                    </div>
                                    
                                    <div class="form-group col-md-6">
                                        <label for="dpInt">USD/NGN:</label>
                                        <input  type="text" name="usd_ngn" placeholder="Enter Amount" class="form-control dpInt" value="{{$exchangeRate['usd_ngn']}}">
                                        <small class="dpIntError" style="color: crimson;"></small>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 pl-0">
                                        <p class="text-right">
                                            <button type="submit" class="btn btn-space btn-primary">Submit</button>
                                        </p>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>-->
            </div>
        </div>
        
        
    </div>
@endsection
@push('scripts')
    <!-- Optional JavaScript -->
    <script src="{{  asset('assets/admin/vendor/jquery/jquery-3.3.1.min.js') }}"></script>
    <script src="{{  asset('assets/admin/vendor/bootstrap/js/bootstrap.bundle.js') }}"></script>
    <script src="{{  asset('assets/admin/vendor/slimscroll/jquery.slimscroll.js') }}"></script>
    <script src="{{  asset('assets/admin/libs/js/main-js.js') }}"></script>
    <script>
        // function addNewInput() {
        //     const clone = document.querySelector(".row.fgExample").cloneNode(true)
        //     document.getElementById('groupedInputs').appendChild(clone);
        // }
        // $(document).ready(function () {
        //     let arr = [
        //         {'tfInt':5001.00,'tfFee':10.00},
        //     ]
        //     arr.forEach(el => {
        //         addNewInput(el.tfInt,el.tfFee)
        //     });
        // })
        $(document).ready(function () {
            // Add event listener for the button click
            $("#addNewInput").on("click", function () {
                // Clone the first form group and append it to the container
                const clonedGroup = $("#groupedInputs .row.fgExample:first").clone(true);
                clonedGroup.find('input').each(function() {
                    let id = Math.floor(Math.random() * 10000)
                    $(this).val("");
                    $(this).attr("id", id)
                    $(this).prev().attr("for",id)
                    $(this).next().text("")
                });

                $("#groupedInputs").append(clonedGroup);
            });

            $("#addNewInputDeposit").on("click", function () {
                // Clone the first form group and append it to the container
                const clonedGroup = $("#groupedInputsDeposit .row.dpExample:first").clone(true);
                clonedGroup.find('input').each(function() {
                    let id = Math.floor(Math.random() * 10000)
                    $(this).val("");
                    $(this).attr("id", id)
                    $(this).prev().attr("for",id)
                    $(this).next().text("")
                });

                $("#groupedInputsDeposit").append(clonedGroup);
            });
            $("i.removeableDp").on("click", function () {
                if (document.querySelectorAll("i.removeableDp").length > 1) {
                    $(this).closest(".row.dpExample").remove()
                }
            })

            $("i.removeable").on("click", function () {
                if (document.querySelectorAll("i.removeable").length > 1) {
                    $(this).closest(".row.fgExample").remove()
                }
            })
            
            // Add event listener for form submission
            $("#transferForm").on("submit",function (event) {
                event.preventDefault()
                // Validate the form using HTML5 validation
                if (!this.checkValidity()) {
                    // Prevent form submission if validation fails
                }

                var formData = new FormData(this);
                formData.append('_token', "{{ csrf_token() }}")

                $.ajax({
                    type: 'POST',
                    url: '{{ route('admin.setting-submit-transfer') }}',
                    contentType: false,
                    processData: false,
                    data: formData,
                    success: function (response) {
                        toastr.success(response.t, response.m)
                    },
                    error: function (error) {
                        const obj = error.responseJSON
                        const reason = obj.reason
                        if (reason == "validation") {
                            const msg = obj.message
                            const errMsgs = obj.error ?? null
                            const inputs = document.querySelectorAll(".row.fgExample")

                            Object.entries(errMsgs).forEach(([key,value]) => {
                                let split = key.split(".");
                                let index = split[split.length - 1]
                                let target = inputs[index]
                                let actualInput = target.querySelector("." + split[0] + 'Error')
                                actualInput.innerText = value
                                console.log(actualInput);
                            });
                        }else if (reason == "alert") {
                            toastr.error(obj.t, obj.m)
                        }
                    }
                });
            });


            $("#depositForm").on("submit",function (event) {
                event.preventDefault()
                // Validate the form using HTML5 validation
                if (!this.checkValidity()) {
                    // Prevent form submission if validation fails
                }

                var formData = new FormData(this);
                formData.append('_token', "{{ csrf_token() }}")

                $.ajax({
                    type: 'POST',
                    url: '{{ route('admin.setting-submit-deposit') }}',
                    contentType: false,
                    processData: false,
                    data: formData,
                    success: function (response) {
                        toastr.success(response.t, response.m)
                    },
                    error: function (error) {
                        const obj = error.responseJSON
                        const reason = obj.reason
                        if (reason == "validation") {
                            const msg = obj.message
                            const errMsgs = obj.error ?? null
                            const inputs = document.querySelectorAll(".row.dpExample")

                            Object.entries(errMsgs).forEach(([key,value]) => {
                                let split = key.split(".");
                                let index = split[split.length - 1]
                                let target = inputs[index]
                                let actualInput = target.querySelector("." + split[0] + 'Error')
                                actualInput.innerText = value
                                console.log(actualInput);
                            });
                        }else if (reason == "alert") {
                            toastr.error(obj.t, obj.m)
                        }
                    }
                });
            });
        });
    </script>
@endpush