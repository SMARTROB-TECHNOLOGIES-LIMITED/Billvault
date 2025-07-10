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
                        <h2 class="pageheader-title">SMTP Settings </h2>
                        {{-- <p class="pageheader-text">Proin placerat ante duiullam scelerisque a velit ac porta, fusce sit amet vestibulum mi. Morbi lobortis pulvinar quam.</p> --}}
                        <div class="page-breadcrumb">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">SMTP Settings</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row d-flex justify-content-between">
                <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                    <div class="card">
                        <h5 class="card-header">Paystack</h5>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.setting-submit-paystack') }}">
                                @csrf
                                <div class="form-group">
                                    <label for="publicKeyPay">Public Key</label>
                                    <input id="publicKeyPay" type="text" name="publickeypaystack" value="{{ isset($set->publickeypaystack) ? $set->publickeypaystack : '' }}" placeholder="Enter paystack public key" class="form-control">
                                    @error('publickeypaystack') <small style="color: crimson;">{{ $message }}</small> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="secretKeyPay">Secret Key</label>
                                    <input id="secretKeyPay" type="text" name="secretkeypaystack" value="{{ isset($set->secretkeypaystack) ? $set->secretkeypaystack : '' }}" placeholder="Enter paystack secret key" class="form-control">
                                    @error('secretkeypaystack') <small style="color: crimson;">{{ $message }}</small> @enderror
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
                        <h5 class="card-header">VTPass</h5>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.setting-submit-vtpass') }}">
                                @csrf
                                <div class="form-group">
                                    <label for="apiKeyVTPass">API Key</label>
                                    <input id="apiKeyVTPass" type="text" name="apikeyvtpass" value="{{ isset($vtp->apikeyvtpass) ? $vtp->apikeyvtpass : '' }}" placeholder="Enter vtpass public key" class="form-control">
                                    @error('apikeyvtpass') <small style="color: crimson;">{{ $message }}</small> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="secretKeyVTPass">Secret Key</label>
                                    <input id="secretKeyVTPass" type="text" name="secretkeyvtpass" value="{{ isset($vtp->secretkeyvtpass) ? $vtp->secretkeyvtpass : '' }}" placeholder="Enter vtpass secret key" class="form-control">
                                    @error('secretkeyvtpass') <small style="color: crimson;">{{ $message }}</small> @enderror
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
                        <h5 class="card-header">YouVerify</h5>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.setting-submit-youverify') }}">
                                @csrf
                                <div class="form-group">
                                    <label for="publicKeyyouverify">API Token</label>
                                    <input id="publicKeyyouverify" type="text" name="apitoken" value="{{ isset($you->apitoken) ? $you->apitoken : '' }}" placeholder="Enter youverify api token" class="form-control">
                                    @error('apitoken') <small style="color: crimson;">{{ $message }}</small> @enderror
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
                        <h5 class="card-header">Firebase</h5>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.setting-submit-firebase') }}">
                                @csrf
                                <div class="form-group">
                                    <label for="serverkeyFirebase">Server Key</label>
                                    <input id="serverkeyFirebase" type="text" name="serverkey" value="{{ isset($fcm->serverkey) ? $fcm->serverkey : '' }}" placeholder="Enter firebase server key" class="form-control">
                                    @error('serverkey') <small style="color: crimson;">{{ $message }}</small> @enderror
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
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- footer -->
        <!-- ============================================================== -->
        <div class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                        Copyright © <?=date('Y')?>Concept.
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
    <!-- Optional JavaScript -->
    <script src="{{  asset('assets/admin/vendor/jquery/jquery-3.3.1.min.js') }}"></script>
    <script src="{{  asset('assets/admin/vendor/bootstrap/js/bootstrap.bundle.js') }}"></script>
    <script src="{{  asset('assets/admin/vendor/slimscroll/jquery.slimscroll.js') }}"></script>
    <script src="{{  asset('assets/admin/libs/js/main-js.js') }}"></script>
@endpush