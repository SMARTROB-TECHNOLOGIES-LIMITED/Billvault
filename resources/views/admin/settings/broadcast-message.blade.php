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
                        <h2 class="pageheader-title">Broadcast Message Settings </h2>
                        <div class="page-breadcrumb">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Broadcast Message Settings</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row d-flex justify-content-between">
                <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                    <div class="card">
                        <h5 class="card-header">Broadcast Message</h5>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.setting-submit-broadcast') }}">
                                @csrf
                                <div class="form-group">
                                    <label for="publicKeyPay">Message</label>
                                    <input id="publicKeyPay" type="text" name="message" value="{{ isset($brd->data) ? $brd->data : '' }}" placeholder="Enter paystack public key" class="form-control">
                                    @error('message') <small style="color: crimson;">{{ $message }}</small> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="secretKeyPay">Status</label>
                                    <select id="status" name="status" class="form-control">
                                        <option value="1" {{ isset($brd->important) && $brd->important == 1 ? 'selected' : '' }}>Enabled</option>
                                        <option value="0" {{ isset($brd->important) && $brd->important == 0 ? 'selected' : '' }}>Disabled</option>
                                    </select>
                                    @error('status') <small style="color: crimson;">{{ $message }}</small> @enderror
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
                        <h5 class="card-header">Transfer Message</h5>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.setting-submit-notification') }}">
                                @csrf
                                <div class="form-group">
                                    <label for="apiKeyVTPass">Message</label>
                                    <input id="apiKeyVTPass" type="text" name="message" value="{{ isset($not->data) ? $not->data : '' }}" placeholder="Enter vtpass public key" class="form-control">
                                    @error('message') <small style="color: crimson;">{{ $message }}</small> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="secretKeyVTPass">Status</label>
                                    <select id="statusVTPass" name="status" class="form-control">
                                        <option value="1" {{ isset($not->important) && $not->important == 1 ? 'selected' : '' }}>Enabled</option>
                                        <option value="0" {{ isset($not->important) && $not->important == 0 ? 'selected' : '' }}>Disabled</option>
                                    </select>
                                    @error('status') <small style="color: crimson;">{{ $message }}</small> @enderror
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
        
    </div>
@endsection
@push('scripts')
    <!-- Optional JavaScript -->
    <script src="{{  asset('assets/admin/vendor/jquery/jquery-3.3.1.min.js') }}"></script>
    <script src="{{  asset('assets/admin/vendor/bootstrap/js/bootstrap.bundle.js') }}"></script>
    <script src="{{  asset('assets/admin/vendor/slimscroll/jquery.slimscroll.js') }}"></script>
    <script src="{{  asset('assets/admin/libs/js/main-js.js') }}"></script>
@endpush