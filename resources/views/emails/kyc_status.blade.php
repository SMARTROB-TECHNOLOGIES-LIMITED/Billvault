<!DOCTYPE html>
<html>
<head>
    <title>KYC {{$level}} Status Update</title>
</head>
<body>
    <p>Dear {{ $username }},</p>

    @if($status === 'approved')
        <p>We are pleased to inform you that your {{$level}} KYC request has been approved. You can now enjoy additional features on your account.</p>
    @else
        <p>We regret to inform you that your {{$level}} KYC request has been rejected. The reason for rejection is as follows:</p>

        <p><strong>Reason:</strong> {{ $rejectionReason }}</p>
    @endif

    <p>If you have any questions, feel free to reach out to our support team.</p>

    <p>Best regards,<br>Billvault App</p>
</body>
</html>
