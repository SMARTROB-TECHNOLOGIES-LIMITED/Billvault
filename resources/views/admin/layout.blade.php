<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($pg) ? $pg.' :: ' : "" }}{{ config('app.name') }}</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    @stack('styles')
</head>
<body>


    <div class="dashboard-main-wrapper">
        @include('admin.topside-bar')
        @yield('content')
    </div>
 
    

@stack('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    toastr.options = {
        closeButton: true,
        // debug: true,
        newestOnTop: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        preventDuplicates: false,
        onclick: null,
        showDuration: '300',
        hideDuration: '1000',
        timeOut: '7000',
        extendedTimeOut: '3000',
        showEasing: 'swing',
        hideEasing: 'linear',
        showMethod: 'fadeIn',
        hideMethod: 'fadeOut'
    };
</script>
<script>
    
</script>
@if (Session::has('alert'))
    @php
        $al = Session::get('alert');
    @endphp
    <script>
        let type = "{{$al['t']}}";
        let msg = "{{$al['m']}}";
        if (type == "Error") {
            toastr.error(`${type}`, `${msg}`)
        }else if (type == "Success"){
            toastr.success(`${type}`, `${msg}`)
        }else {
            toastr.error("Unknown", "Alert type is not recognized")
        }
    </script>
@endif
</body>
</html>



