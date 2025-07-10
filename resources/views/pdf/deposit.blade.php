<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill Vault App Transaction Receipt</title>
</head>
<body>
    @php
        $data = json_decode($details->data);
        $authorizationData = json_decode($data->authorization, true);
        $fee = 0;
        if (isset($data->authorization->sourceAccountNumber,$data->authorization->sourceAccountName,$data->authorization->sourceBankName,$data->fee)) {
            $more_info = "Sender Name: ".$data->authorization->sourceAccountName.', Sender Account No.: '.$data->authorization->sourceAccountNumber.', Sender Bank: '.$data->authorization->sourceBankName;
            $fee = $data->fee;
        }
        $message = isset($data->message) ? $data->message : "";
    @endphp
    <div style="display: flex;justify-content: space-between;align-items: center;">
         <img style=" height:80px; width:150px;margin:0px;padding:0px" src="https://staging.billvault.app/billvault-receipt.png" alt="Bill Vault Logo"  />
         <div style="line-height: 20px;">
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
        <div style="margin-bottom: 15px;">
            <p style="color:#747373; margin: 0;">Receiver</p>
            <h4 style="margin: 0;">{{ Auth::user()->first_name. ' ' .Auth::user()->surname }}</h4>
        </div>
        <div style="margin-bottom: 15px;">
            <p style="color:#747373; margin: 0;">Sender</p>
            <h4 style="margin: 0;">{{ $authorizationData['sourceAccountName'] }}</h4>
        </div>
        <div style="margin-bottom: 15px;">
            <p style="color:#747373; margin: 0;">Sender Account Number</p>
            <h4 style="margin: 0;">{{ $authorizationData['sourceAccountNumber'] }}</h4>
        </div>
        <div style="margin-bottom: 15px;">
            <p style="color:#747373; margin: 0;">Sender Bank</p>
            <h4 style="margin: 0;">{{ $authorizationData['sourceBankName'] }}</h4>
        </div>
        <div style="margin-bottom: 15px;">
            <p style="color:#747373; margin: 0;">Charge</p>
            <h4 style="margin: 0;">N {{ number_format($fee,2) }}</h4>
        </div>
        <div style="margin-bottom: 15px;">
            <p style="color:#747373; margin: 0;">Transaction Reference</p>
            <h4 style="margin: 0;">{{ $details->transaction_id }}</h4>
        </div>
        <div style="margin-bottom: 15px;">
            <p style="color:#747373; margin: 0;">Remark</p>
            <h4 style="margin: 0;">{{ $message }}</h4>
        </div>
    </div>
</body>
</html>