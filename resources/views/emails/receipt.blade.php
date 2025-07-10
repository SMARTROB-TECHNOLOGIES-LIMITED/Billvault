<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Transaction Receipt</title>
</head>
<body>
    <h1>Transaction Receipt</h1>
    <p>Dear {{ $user->surname ?? 'Customer' }} {{ $user->first_name ?? '' }},</p>
    <p>Thank you for your transaction. Attached is your receipt:</p>
    <p><strong>Transaction Type:</strong> {{ $details->type }}</p>
    <p><strong>Transaction ID:</strong> {{ $details->transaction_id }}</p>
    <p><strong>Amount:</strong> &#8358;{{ $details->amount }}</p>
    <p><strong>Date:</strong> {{ $details->created_at }}</p>
    <p>Regards,</p>
    <p>BillVault</p>
</body>
</html>