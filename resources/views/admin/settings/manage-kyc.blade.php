@extends('admin.layout')
@push('styles')
    <link rel="stylesheet" href="{{  asset('assets/admin/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{  asset('assets/admin/vendor/fonts/circular-std/style.css') }}">
    <link rel="stylesheet" href="{{  asset('assets/admin/libs/css/style.css') }}">
    {{-- <link rel="stylesheet" href="{{ asset('assets/admin/vendor/fonts/fontawesome/css/fontawesome-all.css') }}"> --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
     <link rel="stylesheet" href="{{ asset('assets/admin/summernote/summernote-lite.css') }}">
@endpush
@section('content')
    <div class="dashboard-wrapper">
        <div class="container-fluid dashboard-content">
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                    <div class="page-header">
                        <h2 class="pageheader-title">KYC Settings Settings </h2>
                        <div class="page-breadcrumb">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">KYC Settings Settings</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row d-flex justify-content-between">
                <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                    <div class="card">
                        <h5 class="card-header">First Level Settings</h5>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.kyc.level-update') }}">
                                @csrf
                                <input value="{{$levelOne->id}}" name="id" type="hidden">
                                <div class="form-group">
                                    <label for="title">Message</label>
                                    <input id="title" type="text" name="title" value="{{ isset($levelOne->title) ? $levelOne->title : '' }}"   class="form-control">
                                    @error('title') <small style="color: crimson;">{{ $message }}</small> @enderror
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label for="maximum_balance">Maximum Balance</label>
                                            <input id="maximum_balance" type="text" name="maximum_balance" value="{{ isset($levelOne->maximum_balance) ? $levelOne->maximum_balance : '' }}"   class="form-control">
                                            @error('maximum_balance') <small style="color: crimson;">{{ $message }}</small> @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label for="minimum_balance">Maximum Transfer</label>
                                            <input id="minimum_balance" type="text" name="maximum_transfer" value="{{ isset($levelOne->maximum_transfer) ? $levelOne->maximum_transfer : '' }}"   class="form-control">
                                            @error('maximum_transfer') <small style="color: crimson;">{{ $message }}</small> @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label>Level Description*</label>
                                    <textarea name="details" id="summernote1" class="summernote form-control @error('description') is-invalid @enderror">{{ $levelOne->details }}</textarea>
                                    @error('description')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 pl-0">
                                        <p class="text-right">
                                            <button type="submit" class="btn btn-space btn-primary">Update</button>
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
                        <h5 class="card-header">Second Level Settings</h5>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.kyc.level-update') }}">
                                @csrf
                                <input value="{{$levelTwo->id}}" name="id" type="hidden">
                                <div class="form-group">
                                    <label for="title">Message</label>
                                    <input id="title" type="text" name="title" value="{{ isset($levelTwo->title) ? $levelTwo->title : '' }}"   class="form-control">
                                    @error('title') <small style="color: crimson;">{{ $message }}</small> @enderror
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label for="maximum_balance">Maximum Balance</label>
                                            <input id="maximum_balance" type="text" name="maximum_balance" value="{{ isset($levelTwo->maximum_balance) ? $levelTwo->maximum_balance : '' }}"   class="form-control">
                                            @error('maximum_balance') <small style="color: crimson;">{{ $message }}</small> @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label for="minimum_balance">Maximum Transfer</label>
                                            <input id="minimum_balance" type="text" name="maximum_transfer" value="{{ isset($levelTwo->maximum_transfer) ? $levelTwo->maximum_transfer : '' }}"   class="form-control">
                                            @error('maximum_transfer') <small style="color: crimson;">{{ $message }}</small> @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label>Level Description*</label>
                                    <textarea name="details" id="summernote2" class="summernote form-control @error('description') is-invalid @enderror">{{ $levelTwo->details }}</textarea>
                                    @error('description')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 pl-0">
                                        <p class="text-right">
                                            <button type="submit" class="btn btn-space btn-primary">Update</button>
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
                        <h5 class="card-header">Third Level Settings</h5>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.kyc.level-update') }}">
                                @csrf
                                <input value="{{$levelThree->id}}" name="id" type="hidden">
                                <div class="form-group">
                                    <label for="title">Message</label>
                                    <input id="title" type="text" name="title" value="{{ isset($levelThree->title) ? $levelThree->title : '' }}"   class="form-control">
                                    @error('title') <small style="color: crimson;">{{ $message }}</small> @enderror
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label for="maximum_balance">Maximum Balance</label>
                                            <input id="maximum_balance" type="text" name="maximum_balance" value="{{ isset($levelThree->maximum_balance) ? $levelThree->maximum_balance : '' }}"   class="form-control">
                                            @error('maximum_balance') <small style="color: crimson;">{{ $message }}</small> @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label for="minimum_balance">Maximum Transfer</label>
                                            <input id="minimum_balance" type="text" name="maximum_transfer" value="{{ isset($levelThree->maximum_transfer) ? $levelThree->maximum_transfer : '' }}"   class="form-control">
                                            @error('maximum_transfer') <small style="color: crimson;">{{ $message }}</small> @enderror
                                        </div>
                                    </div>
                                </div>
                                <!--<div class="form-group">-->
                                <!--    <label for="secretKeyPay">Status</label>-->
                                <!--    <select id="status" name="status" class="form-control">-->
                                <!--        <option value="1" {{ isset($levelThree->status) && $levelThree->status == 1 ? 'selected' : '' }}>Enabled</option>-->
                                <!--        <option value="0" {{ isset($levelThree->status) && $levelThree->status == 0 ? 'selected' : '' }}>Disabled</option>-->
                                <!--    </select>-->
                                <!--    @error('status') <small style="color: crimson;">{{ $message }}</small> @enderror-->
                                <!--</div>-->
                                <div class="form-group">
                                    <label>Level Description*</label>
                                    <textarea name="details" id="summernote" class="summernote form-control @error('description') is-invalid @enderror">{{ $levelThree->details }}</textarea>
                                    @error('description')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 pl-0">
                                        <p class="text-right">
                                            <button type="submit" class="btn btn-space btn-primary">Update</button>
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
    <script src="{{  asset('assets/admin/summernote/summernote-lite.js') }}"></script>
    <script>
        $('.summernote').summernote({
            placeholder: 'add content',
            tabsize: 2,
            height: 140,
            tooltip: false,
            popover: {

                link: [],
                air: []
            },
            toolbar: [
                ["style", false],
                ["font", ["bold", "underline", "clear"]],
                ["fontname", false],
                ["color", ["color"]],
                ["para", ["ul", "ol", "paragraph"]],
                ["table", ["table"]],
                ["insert", ["link"]],
                ["view", ["fullscreen", "codeview", "help"]]
            ],
        });
    </script>
@endpush