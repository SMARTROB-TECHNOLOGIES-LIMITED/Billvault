<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paypoint Transaction Receipt</title>
</head>
<body>
    @php
        $data = json_decode($details->data);
    @endphp

    <div style="display: flex;justify-content: space-between;align-items: center;">
        <img style=" height:90px; width:150px;margin:0px;padding:0px" src="https://staging.billvault.app/billvault-receipt.png" alt="Bill Vault Logo"  />
        <div style="line-height: 20px;">
            <h1 style="font-size: 20px; margin-bottom: 0;">Receipt</h1>
            <h5 style="margin-top: 0; color:#747373;">{{ $details->created_at->format('d, M Y H:i:s') }}</h5>
        </div>
    </div>
    <div style="margin-top: -10px;">N <span style="font-weight: 600;font-size: 32px;">{{ number_format($details->amount, 2) }}</span></div>
    
    @if ($details->status == "PENDING")
        <span style="color: rgb(56, 90, 240);">Pending</span>
    @elseif ($details->status == "FAILED")
        <span style="color: rgb(240, 56, 56);">Failed</span>
    @elseif ($details->status == "REVERSED")
        <span style="color: rgb(240, 228, 56);">Reversed</span>
    @else
        <span style="color: rgb(56, 240, 56);">Completed</span>
    @endif

    <div style="margin-top: 30px;">
        <div style="margin-bottom: 15px;">
            <p style="color:#747373; margin: 0;">Name</p>
            <h4 style="margin: 0;">{{ Auth::user()->first_name . ' ' . Auth::user()->surname }}</h4>
        </div>
        <div style="margin-bottom: 15px;">
            <p style="color:#747373; margin: 0;">Email</p>
            <h4 style="margin: 0;">{{ Auth::user()->email }}</h4>
        </div>
        <div style="margin-bottom: 15px;">
            <p style="color:#747373; margin: 0;">Card Amount</p>
            <h4 style="margin: 0;">&#x24; {{ $data->amount }}</h4>
        </div>
        <div style="margin-bottom: 15px;">
            <p style="color:#747373; margin: 0;">Received Amount</p>
            <h4 style="margin: 0;">N {{ $data->payable_amount }}</h4>
        </div>
        
        <div style="margin-bottom: 15px;">
            <p style="color:#747373; margin: 0;">Card Type</p>
            <h4 style="margin: 0;">{{ $data->network }}</h4>
        </div>
        
        <div style="margin-bottom: 15px;">
            <p style="color:#747373; margin: 0;">Country</p>
            <h4 style="margin: 0;">{{ $data->country }}</h4>
        </div>
        
        <div style="margin-bottom: 15px;">
            <p style="color:#747373; margin: 0;">Reference Number</p>
            <h4 style="margin: 0;">{{ $data->request_id }}</h4>
        </div>
        
    </div>
</body>
</html>
