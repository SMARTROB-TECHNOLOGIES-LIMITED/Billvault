<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>
    <p style="text-align:center;display:flex;justify-content:center;align-items:center;gap-x:15px;">
        <img alt="Paypoint Africa Logo" src="https://api.paypointapp.africa/assets/images/application/logo.jpg" style="height:75px; width:75px" />
        <strong>&nbsp; </strong>
        <span style="font-size:20px">
            <strong>Paypoint Africa (Statement of Account)</strong>
        </span>
    </p>

    <p style="text-align:center">
        <strong>Between {{ $start }} to {{ $end }}</strong>
    </p>

    <table border="1" cellpadding="1" cellspacing="1" style="width:100%">
        <tbody>
            <tr>
                <th scope="row">Reference</th>
                <td style="text-align:center"><strong>Date</strong></td>
                <td style="text-align:center"><strong>Transaction</strong></td>
                <td style="text-align:center"><strong>Amount</strong></td>
                <td style="text-align:center"><strong>Charge</strong></td>
                <td style="text-align:center"><strong>Recipient</strong></td>
                <td style="text-align:center"><strong>Status</strong></td>
                <td style="text-align:center"><strong>More Information</strong></td>
            </tr>
            @foreach ($transactions as $tran)
                @php
                    $data = json_decode($tran->data);
                    $recipient = $tran->recipient;
                    $more_info = "";
                    $fee = 0;
                    $data->charge = 1000;
                    if (strtolower($tran->type) == "deposit") {
                        $recipient = "Me";
                        $data->charge = 102;
                        if (isset($data->authorization->sender_bank_account_number,$data->authorization->account_name,$data->authorization->sender_bank)) {
                            $more_info = "Sender Name: ".$data->authorization->account_name.', Sender Account No.: '.$data->authorization->sender_bank_account_number.', Sender Bank: '.$data->authorization->sender_bank;
                            $fee = $data->charge;
                        } 
                    }elseif (strtolower($tran->type) == "transfer") {
                        if (isset($data->recipient,$data->reason,$data->charge)) {
                            $recipient = "Name: ".$data->recipient->account_name.', Bank: '.$data->recipient->bank_name.', Number:'.$data->recipient->account_number;
                            $more_info = "Reason: ".$data->reason;
                            $fee = $data->charge;
                        }
                    }elseif (strtolower($tran->type) == "electricity") {
                        if (isset($data->service_name,$data->metreNo,$data->customer_name,$data->units,$data->convenience_fee)) {
                            $more_info = "Service: ".$data->service_name.', Metre No: '.$data->metreNo.', Units: '.$data->units.', Customer Name: '.$data->customer_name;
                            $fee = $data->convenience_fee;
                        }
                    }elseif (strtolower($tran->type) == "cable tv") {
                        if (isset($data->service_name,$data->phone,$data->convenience_fee)) {
                            $more_info = "Service: ".$data->service_name.', Phone: '.$data->phone;
                            $fee = $data->convenience_fee;
                        }
                    }elseif (strtolower($tran->type) == "data") {
                        if (isset($data->service_name,$data->phone)) {
                            $more_info = "Service: ".$data->service_name.', Phone: '.$data->phone;
                        }
                    }elseif (strtolower($tran->type) == "airtime") {
                        if (isset($data->service_name,$data->phone)) {
                            $more_info = "Service: ".$data->service_name.', Phone: '.$data->phone;
                        }
                    }
                @endphp
                <tr>
                    <td style="text-align:center">{{ $tran->transaction_id }}</td>
                    <td style="text-align:center">{{ $tran->created_at }}</td>
                    <td style="text-align:center">{{ ucwords($tran->type) }}</td>
                    <td style="text-align:center">N{{ number_format($tran->amount, 2) }}</td>
                    <td style="text-align:center">N{{ number_format($fee, 2) }}</td>
                    <td style="text-align:center">{{ $recipient }}</td>
                    <td style="text-align:center">{{ $tran->status }}</td>
                    <td style="text-align:center">{{ $more_info }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p>&nbsp;</p>
</body>

</html>
