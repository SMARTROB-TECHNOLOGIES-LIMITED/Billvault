<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Welcome to Bill Vault</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden;">
                    <tr>
                        <td style="background-color: #4CAF50; padding: 20px; color: white; text-align: center;">
                            <h1 style="margin: 0;">Welcome to Bill Vault</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 30px; font-family: Arial, sans-serif;">
                            <p style="font-size: 16px; color: #333;">
                                Dear {{ $user->first_name }} {{ $user->surname }},
                            </p>
                        
                            <p style="font-size: 16px; color: #333;">
                                Welcome to <strong>BillVault</strong>! We’re thrilled to have you on board. Your account has been successfully created, and you’re now ready to enjoy a seamless and secure bill payment experience.
                            </p>
                        
                            <h3 style="color: #4CAF50;">Your Account Details:</h3>
                            <ul style="font-size: 16px; color: #333; line-height: 1.6;">
                                <li><strong>Account Name:</strong> {{ $user->account_name }}</li>
                                <li><strong>Email:</strong> {{ $user->email }}</li>
                                <li><strong>Account Number:</strong> {{ $user->account_number }}</li>
                                <li><strong>Bank Name:</strong> {{ $user->bank_name }}</li>
                                <li><strong>Referral Code:</strong> {{ $user->code }}</li>
                            </ul>
                        
                            <h3 style="color: #4CAF50;">Getting Started:</h3>
                            <ul style="font-size: 16px; color: #333; line-height: 1.6;">
                                <li><strong>Funding your Wallet:</strong> Click <em>Add Money</em> in your dashboard and make a transfer to your unique account number above.</li>
                                <li><strong>Bill Payments:</strong> Easily view and pay bills from a wide range of providers directly from your dashboard.</li>
                                <li><strong>Support:</strong> Need assistance? Our dedicated team is available 24/7. Email us at <a href="mailto:support@billvault.app" style="color: #4CAF50;">support@billvault.app</a> 
                                or visit our  <a href="https://billvault.app/contact/" style="color: #4CAF50;">Help Center</a>.</li>
                            </ul>
                        
                            <p style="font-size: 16px; color: #333;">
                                For more tips and features, check out our <a href="https://billvault.app/#features" style="color: #4CAF50;">Getting Started Guide</a>.
                            </p>
                        
                            <p style="font-size: 16px; color: #333;">
                                We are committed to providing you with the best bill payment experience. Thank you for choosing BillVault!
                            </p>
                        
                            <p style="font-size: 16px; color: #333;">
                                Best regards,<br>
                                The BillVault Team<br>
                                <a href="www.billvault.app" style="color: #4CAF50;">www.billvault.app</a> | 
                                <a href="mailto:support@billvault.app" style="color: #4CAF50;">support@billvault.app</a> |
                                <span style="color: #333;">+234 815 306 8255</span>
                            </p>
                        
                            <hr style="border: none; border-top: 1px solid #ccc; margin: 20px 0;">
                        
                            <p style="font-size: 12px; color: #888;">
                                Disclaimer: This email is intended for the user who created an account with BillVault. If you did not create an account, please disregard this message or contact us immediately.
                            </p>
                        </td>

                    </tr>
                    <tr>
                        <td style="background-color: #f4f4f4; text-align: center; padding: 20px; color: #999; font-size: 14px;">
                            &copy; {{ date('Y') }} Bill Vault. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
