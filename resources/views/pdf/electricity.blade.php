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
        $fee = (float) 0.00;
        if (isset($data->convenience_fee)) {
            $fee = $data->convenience_fee;
        }
        $more_info = 'Package: '.$data->package;
    @endphp
    <div style="display: flex;justify-content: space-between;align-items: center;">
         <img style=" height:90px; width:150px;margin:0px;padding:0px" src="https://staging.billvault.app/billvault-receipt.png" alt="Bill Vault Logo"  /><div style="line-height: 20px;">
            <h1 style="font-size: 20px; margin-bottom: 0;">Receipt</h1>
            <h5 style="margin-top: 0; color:#747373;">{{ $details->created_at->format('d, M Y H:s') }}</h5>
        </div>
    </div>
    <div style="margin-top: -10px;">N <span style="font-weight: 600;font-size: 32px;">{{ number_format($details->amount - $fee,2) }}</span></div>
    @if ($details->status == "pending")
        <span style="color: rgb(56, 90, 240);">Pending</span>
    @elseif ($details->status == "failed")
        <span style="color: rgb(240, 56, 56);">Failed</span>
    @elseif ($details->status == "reversed")
        <span style="color: rgb(240, 228, 56);">Reversed</span>
    @else
        <span style="color: rgb(56, 240, 56);">Completed</span>
    @endif

    <div style="margin-top: 30px;">
        <h4>{{$data->token ?? null}}</h4>
        <div style="margin-bottom: 15px;">
            <p style="color:#747373; margin: 0;">Transaction Type</p>
            <h4 style="margin: 0;">{{ $details->type }}</h4>
        </div>
        <div style="margin-bottom: 15px;">
            <p style="color:#747373; margin: 0;">Biller</p>
            <h4 style="margin: 0;">{{ $data->service_name }}</h4>
        </div>
        <div style="margin-bottom: 15px;">
            <p style="color:#747373; margin: 0;">Sender</p>
            <h4 style="margin: 0;">{{ Auth::user()->first_name. ' ' .Auth::user()->surname }}</h4>
        </div>
        <div style="margin-bottom: 15px;">
            <p style="color:#747373; margin: 0;">Metre No.</p>
            <h4 style="margin: 0;">{{ $data->metreNo }}</h4>
        </div>
        <div style="margin-bottom: 15px;">
            <p style="color:#747373; margin: 0;">Customer/Recipient</p>
            <h4 style="margin: 0;">{{ $data->customer_name }}</h4>
        </div>
        <div style="margin-bottom: 15px;">
            <p style="color:#747373; margin: 0;">Units</p>
            <h4 style="margin: 0;">{{ $data->units }}</h4>
        </div>
        <div style="margin-bottom: 15px;">
            <p style="color:#747373; margin: 0;">Convenience Fee</p>
            <h4 style="margin: 0;">N <b>{{ number_format($data->convenience_fee,2) }}</b></h4>
        </div>
        <div style="margin-bottom: 15px;">
            <p style="color:#747373; margin: 0;">Transaction Reference</p>
            <h4 style="margin: 0;">{{ $details->transaction_id }}</h4>
        </div>
        <div style="margin-bottom: 15px;">
            <p style="color:#747373; margin: 0;">More Information</p>
            <h4 style="margin: 0;">
                {{ $more_info }}
            </h4>
        </div>
    </div>
</body>
</html>