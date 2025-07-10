<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BillVault Transaction Receipt</title>
</head>
<body>
    
    @php
        $data = json_decode($details->data);
        $fee = 0;

        // Decode authorization data
        if (is_string($data->authorization)) {
            $authorizationData = json_decode($data->authorization, true);
        } elseif (is_object($data->authorization)) {
            $authorizationData = (array) $data->authorization;
        } else {
            $authorizationData = [];
        }

        // Fallback source data if not set in authorization
        $fallbackData = [
            'account_name' => 'Mutolib Sodiq',
            'sender_bank_account_number' => 'XXXXXX9490',
            'sender_bank' => 'Bill Vault'
        ];

        // Use value from $authorizationData, else fallbackData
        $senderName = $authorizationData['sourceAccountName'] 
                      ?? $authorizationData['account_name'] 
                      ?? $fallbackData['account_name'];

        $senderAccountNumber = $authorizationData['sourceAccountNumber'] 
                               ?? $authorizationData['sender_bank_account_number'] 
                               ?? $fallbackData['sender_bank_account_number'];

        $senderBank = $authorizationData['sourceBankName'] 
                      ?? $authorizationData['sender_bank'] 
                      ?? $fallbackData['sender_bank'];

        $fee = $data->fee ?? 0;
        $message = $data->message ?? "";
    @endphp

    <div style="display: flex;justify-content: space-between;align-items: center;">
        <img style="position:absolute; top:0;right:0;" alt="BillVault Logo" src="https://staging..app/-receipt.png" style="height:75px; width:75px" />
        <div style="line-height: 20px;">
            <h1 style="font-size: 20px; margin-bottom: 0;">Receipt</h1>
            <h5 style="margin-top: 0; color:#747373;">{{ $details->created_at->format('d, M Y H:s') }}</h5>
        </div>
    </div>

    <div style="margin-top: -10px;">N <span style="font-weight: 600;font-size: 32px;">{{ number_format($details->amount - $fee, 2) }}</span></div>

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
            <h4 style="margin: 0;">{{ $user->first_name . ' ' . $user->surname }}</h4>
        </div>
        <div style="margin-bottom: 15px;">
            <p style="color:#747373; margin: 0;">Sender</p>
            <h4 style="margin: 0;">{{ $senderName }}</h4>
        </div>
        <div style="margin-bottom: 15px;">
            <p style="color:#747373; margin: 0;">Sender Account Number</p>
            <h4 style="margin: 0;">{{ $senderAccountNumber }}</h4>
        </div>
        <div style="margin-bottom: 15px;">
            <p style="color:#747373; margin: 0;">Sender Bank</p>
            <h4 style="margin: 0;">{{ $senderBank }}</h4>
        </div>
        <div style="margin-bottom: 15px;">
            <p style="color:#747373; margin: 0;">Charge</p>
            <h4 style="margin: 0;">N {{ number_format($fee, 2) }}</h4>
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
