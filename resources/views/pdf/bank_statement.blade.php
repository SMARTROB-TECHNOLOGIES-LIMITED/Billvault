<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Statement of Account ({{ $start }} to {{ $end }})</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; }
        .badge-success { color: green; font-weight: bold; }
        .badge-danger { color: red; font-weight: bold; }
        .badge-secondary { color: gray; font-weight: bold; }
        img.logo { height: 90px; width: 150px; }
    </style>
</head>
<body>
    <div class="header">
        <img src="https://staging.billvault.app/billvault-receipt.png" alt="Bill Vault Logo" class="logo">
        <h2>Bill Vault</h2>
        <h3>Statement of Account</h3>
        <p><strong>Period: {{ $start }} to {{ $end }}</strong></p>
    </div>

    @php
        $debitTypeArray = ['Transfer', 'Airtime', 'Data', 'Electricity', 'Card Creation', 'Card Funding', 'Cable TV', 'Betting', 'Gift Card', 'WAEC Result Checker PIN', 'Jamb', 'WAEC Registration PIN'];
        $creditTypeArray = ['Deposit', 'Top-up', 'ATC', 'Sell Gift Card', 'Referral Bonus'];
    @endphp

    <table>
        <thead>
            <tr>
                <th>Reference</th>
                <th>Date</th>
                <th>Transaction</th>
                <th>Direction</th>
                <th>Balance Before</th>
                <th>Balance After</th>
                <th>Amount</th>
                <th>Charge</th>
                <th>Recipient</th>
                <th>Status</th>
                <th>More Information</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transactions as $tran)
                @php
                    $data = json_decode($tran->data ?? '{}');
                    $direction = in_array($tran->type, $debitTypeArray) ? 'Debit' : (in_array($tran->type, $creditTypeArray) ? 'Credit' : 'Unknown');
                    $balance_before = $data->balance_before ?? null;
                    $balance_after = $data->balance_after ?? null;
                    $fee = $data->fee ?? 0;
                    $recipient = $tran->recipient ?? '-';
                    $more_info = $data->reason ?? '';
                @endphp
                <tr>
                    <td>{{ $tran->transaction_id }}</td>
                    <td>{{ $tran->created_at }}</td>
                    <td>{{ ucwords($tran->type) }}</td>
                    <td class="badge-{{ $direction == 'Credit' ? 'success' : ($direction == 'Debit' ? 'danger' : 'secondary') }}">
                        {{ $direction }}
                    </td>
                    <td>N{{ number_format($balance_before, 2) }}</td>
                    <td>N{{ number_format($balance_after, 2) }}</td>
                    <td>N{{ number_format($tran->amount, 2) }}</td>
                    <td>N{{ number_format($fee, 2) }}</td>
                    <td>{{ $recipient }}</td>
                    <td>{{ $tran->status }}</td>
                    <td>{{ $more_info }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
