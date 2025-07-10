@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-check-circle me-2"></i> {{ __('Password Updated') }}
                </div>

                <div class="card-body text-center">
                    <div class="success-icon mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                    </div>

                    <h3 class="mb-4">Password Updated Successfully!</h3>
                    <p class="mb-4">Your password details have been successfully updated.</p>

                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i> Kindly go back to the app to continue.
                    </div>

                    @if ($errors->any())
                    <div class="alert alert-danger mt-4">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                   
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .success-icon {
        animation: bounce 1s ease infinite;
    }
    @keyframes bounce {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }
</style>
@endsection
