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
            <div class="row d-flex justify-content-center">
                <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                    <div class="card">
                        <h5 class="card-header">SMTP Settings</h5>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.setting-submit-smtp') }}">
                                @csrf
                                <div class="form-group">
                                    <label for="smtphost">SMTP Host Url</label>
                                    <input id="smtphost" type="text" name="smtphost" value="{{ isset($set->smtphost) ? $set->smtphost : '' }}" placeholder="Enter SMTP Host Url" class="form-control">
                                    @error('smtphost') <small style="color: crimson;">{{ $message }}</small> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="smtpport">SMTP Port</label>
                                    <input id="smtpport" type="number" name="smtpport" value="{{ isset($set->smtpport) ? $set->smtpport : '' }}" placeholder="Enter SMTP Port" class="form-control">
                                    @error('smtpport') <small style="color: crimson;">{{ $message }}</small> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="smtpfrom">SMTP From Address</label>
                                    <input id="smtpfrom" type="email" name="smtpfrom" value="{{ isset($set->smtpfrom) ? $set->smtpfrom : '' }}" placeholder="Enter SMTP From Address" class="form-control">
                                    @error('smtpfrom') <small style="color: crimson;">{{ $message }}</small> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="smtpusername">SMTP Username</label>
                                    <input id="smtpusername" type="text" name="smtpusername" value="{{ isset($set->smtpusername) ? $set->smtpusername : '' }}" placeholder="Enter SMTP Username" class="form-control">
                                    @error('smtpusername') <small style="color: crimson;">{{ $message }}</small> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="smtppassword">SMTP Password</label>
                                    <input id="smtppassword" type="text" name="smtppassword" value="{{ isset($set->smtppassword) ? $set->smtppassword : '' }}" placeholder="Enter SMTP Password" class="form-control">
                                    @error('smtppassword') <small style="color: crimson;">{{ $message }}</small> @enderror
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
@endsection
@push('scripts')
    <!-- Optional JavaScript -->
    <script src="{{  asset('assets/admin/vendor/jquery/jquery-3.3.1.min.js') }}"></script>
    <script src="{{  asset('assets/admin/vendor/bootstrap/js/bootstrap.bundle.js') }}"></script>
    <script src="{{  asset('assets/admin/vendor/slimscroll/jquery.slimscroll.js') }}"></script>
    <script src="{{  asset('assets/admin/libs/js/main-js.js') }}"></script>
@endpush