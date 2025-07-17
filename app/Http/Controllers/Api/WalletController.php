<?php

    namespace App\Http\Controllers\Api;

    use Carbon\Carbon;
    use App\Models\User;
    use App\Models\KycLevel;
    use Illuminate\Support\Str;
    use App\Jobs\TransactionLog;
    use App\Jobs\PushNotification;
    use Illuminate\Http\Request; 
    use App\Rules\TransactionPin;
    use App\Http\Controllers\Controller;
    use Illuminate\Support\Facades\Auth;
    use App\Models\AdminSetting;
    use Stevebauman\Purify\Facades\Purify;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Facades\Validator;
    use App\Models\TransactionLog as ModelTrnLog;
    use Illuminate\Support\Facades\Log;
    use function App\getSettings;
    use function App\retErrorSetting;
    use App\Models\ReferralBonus;    

    class WalletController extends Controller
    {
        
        private $chargeDpDefault = [
            ["range"=>0 ,"charge" => 0, "addOn" => 10.00],
            ["range"=>5001 ,"charge" => 0, "addOn" => 10.00],
            ["range"=>50001 ,"charge" => 0, "addOn" => 10.00],
        ];

        private $chargeTfDefault = [
            ["range" => 0, "fee" => 10.00],
            ["range" => 5001, "fee" => 25.00],
            ["range" => 50001, "fee" => 50.00],
            ["range" => 100001, "fee" => 75.00],
        ];
        
        // Constants for response codes
        const OPERATION_FAILED = ['code' => 0, 'msg' => 'Something went wrong failed'];
        const SUCCESSFUL = ['code' => 1, 'msg' => 'Payment Successful'];
        const INSUFFICIENT_BALANCE = ['code' => 2, 'msg' => 'Insufficient balance'];
        const LIMIT_EXCEEDED = ['code' => 3, 'msg' => 'Account Limit exceeded'];
        const ACCOUNT_RESTRICTED = ['code' => 4, 'msg' => 'Account Restricted'];
        const FRUAD_ALERT = ['code' => 5, 'msg' => 'Account Restricted, kindly contact admin.'];
        const MAINTENANCE = ['code' => 5, 'msg' => 'Update Going, we will be back shortly'];
        
        // 10181
        
        
        // private static $limitDefs = [
        //     1 => ['tf' => 50000],
        //     2 => ['tf' => 200000],
        //     3 => ['tf' => 5000000]
        // ];
        
        
        private static function referralBonus($refferedBy, $referralBonus){
            
            $referrer = User::where('code', $refferedBy)->first();
            
            if ($referrer) {
                $r_uid = $referrer->id;  
                
                $ref = Str::random(20);
                
                // $pay = json_decode($this->walletUpdate($referralBonus, 'code', $refferedBy));
                
                $pay = json_decode(self::walletUpdate($referralBonus, 'code', $refferedBy));
                
                $ref_data = json_encode([
                    'id' => $ref,
                    'img' => asset('assets/images/bank/bank.jpg'),
                    'status' => "Successful",
                    'reference' => $ref,
                    'amount' => (float) ($referralBonus),
                    'account_number' => $referrer->account_number,
                    'bank'  =>  "Bill Vault",
                ]);

                // Log the transaction
                TransactionLog::dispatch($r_uid, "Referral Bonus", $referralBonus, $ref, "Success", $referrer->email, $ref_data);
                

            } else {
                // Handle case where the referred user is not found
                Log::error('Referrer not found', ['referral_code' => $refferedBy]);
            }
        }
        
        private static function getLimitDefs() {
            // Fetch KycLevel records from the database
            $kycLevels = KycLevel::all();
    
            // Initialize an empty array for limit definitions
            $limitDefs = [];
    
            // Iterate over KycLevel records and populate limitDefs
            foreach ($kycLevels as $kycLevel) {
                $level = $kycLevel->id; // Assuming 'level' is the column name
                $maxTransfer = $kycLevel->maximum_transfer; // Assuming 'minimum_transfer' is the column name
    
                $limitDefs[$level] = ['tf' => $maxTransfer];
            }
    
            return $limitDefs;
        }
        
        private static function getLimitBalance() {
            // Fetch KycLevel records from the database
            $kycLevels = KycLevel::all();
    
            // Initialize an empty array for limit definitions
            $limitDefs = [];
    
            
            foreach ($kycLevels as $kycLevel) {
                $level = $kycLevel->id; 
                $maxBalance = $kycLevel->maximum_balance; 
    
                $limitDefs[$level] = ['tf' => $maxBalance];
            }
    
            return $limitDefs;
        }

        public function tfCharges($amt) {
            if (!is_numeric($amt) || $amt < 1) {
                return response()->json([
                    'status' => 'false',
                    'data' => [
                        'message' => 'Invalid transaction amount entered',
                    ]
                ],400);
            }
            $fee = $this->callTfCharges($amt);
            return response()->json([
                'status' => 'true',
                'data' => [
                    'message' => 'Fee generated successfully',
                    'fee' => $fee
                ]
            ],200);
        }

        public function callTfCharges($amt) {
            $q = AdminSetting::where('name','transfer');
            $charge = $this->chargeTfDefault;
            if ($q->exists()) {
                $raw = $q->first();
                $charge = json_decode($raw->data, true) ? json_decode($raw->data, true) : [];
            }
            // Extract the "range" column from the array
            $ranges = array_column($charge, 'range');
            // Use array_multisort to sort $charge based on $ranges
            array_multisort($ranges, SORT_ASC, $charge);

            $fee = 0;
            foreach ($charge as $key => $ch) {
                if ($amt >= $ch['range'] && (isset($charge[$key+1]['range']) && $amt < $charge[$key+1]['range'])) {
                    $fee = $ch['fee'];
                    return (int)$fee;
                }else {
                    $fee = end($charge)['fee'];
                }
            }
            return $fee;
        }
        
        public function callDpstCharge($amt) {
            $q = AdminSetting::where('name','deposit');
            $charge = $this->chargeDpDefault;
            if ($q->exists()) {
                $raw = $q->first();
                $charge = json_decode($raw->data, true) ? json_decode($raw->data, true) : [];
            }
            // Extract the "range" column from the array
            $ranges = array_column($charge, 'range');
            // Use array_multisort to sort $charge based on $ranges
            array_multisort($ranges, SORT_ASC, $charge);

            $fee = 0;
            foreach ($charge as $key => $ch) {
                if ($amt >= $ch['range'] && (isset($charge[$key+1]['range']) && $amt < $charge[$key+1]['range'])) {
                    $fee = (float)$ch['addOn'] + (float)($amt * $ch['fee']);
                    return (int)$fee;
                }else {
                    $end = end($charge);
                    $fee = (float)$end['addOn'] + (float)($amt * $end['fee']);
                }
            }
            return $fee;
        }
        
        public function safeHavenDepositWebhook(Request $request, User $user){
            $type = $request->input('type');
            $data = $request->input('data');
    
            // Assign individual variables from the data object
            $transactionId = $data['_id'];
            $client = $data['client'];
            $amount = $data['amount'];
            $fees = $data['fees'];
            $creditAccountName = $data['creditAccountName'];
            $creditAccountNumber = $data['creditAccountNumber'];
            $debitAccountName = $data['debitAccountName'];
            $debitAccountNumber = $data['debitAccountNumber'];
            $status = $data['status'];
            $narration = $data['narration'];
            $provider = $data['provider'];
            $approvedAt = $data['approvedAt'];
    
            // Log the webhook data (optional, for debugging)
            Log::info('Transfer webhook received:', $data);
            
            if ($status === 'Completed' && $data['responseCode'] === '00') {
                $q = $user->where('account_number',$creditAccountNumber);
                $uid = $q->exists() ? $q->first(['id'])->id : $this->returnIt(['message' => 'Failed']);
                $fee = $this->callDpstCharge($amount);
                $credit  = $amount - $fee;
                
                $data = json_encode([
                    'id'                =>  $transactionId,
                    'img'               =>  null,
                    'status'            =>  'Success',
                    'reference'         =>  $transactionId,
                    'authorization'     =>  json_encode(['sourceAccountNumber' => $debitAccountNumber , 'sourceAccountName' => strtolower($debitAccountName), 'sourceBankName' => $provider]),
                    'amount'            =>  $credit,
                    'fee'               =>  (float) $fee,
                    'message'           =>  $narration,
                    'fees_breakdown'    =>  $amount,
                    'currency'          =>  "NGN",
                ]);
    
                $ref = $transactionId;
                $type = 'Deposit';
                $trans_status = 'successful';
                
                $pay = json_decode($this->walletUpdate($credit, 'account_number', $creditAccountNumber));
                if($pay->code == 1) {
                    // Storage::put('dpst/'.Carbon::now()->format('Y-m-d_His'), json_encode($event, JSON_PRETTY_PRINT));
                    PushNotification::dispatch($uid,"Deposit Successful",$narration,null,null,['transaction_id'=>$ref]);
                    TransactionLog::dispatch($uid, $type, $credit, $ref, $trans_status, null, $data);
                    return ['Webhook received', 200];
                }
                return ['Error', 401];
            }
        }
        
        public function nombaDepositWebhook(Request $request, User $user){
            Log::info('Transfer webhook received:', ['payload' => $request->all()]);

            // Extract event type and data from the request
            $eventType = $request->input('event_type');
            $requestId = $request->input('requestId');
            $data = $request->input('data');
            
            if (!$eventType || !$data) {
                return response()->json(['message' => 'Invalid webhook payload'], 400);
            }
            
            if($eventType == 'payment_success'){
                $transactionId = $data['transaction']['transactionId'];
                $amount = $data['transaction']['transactionAmount'];
                $creditAccountName = $data['transaction']['aliasAccountName'];
                $creditAccountNumber = $data['transaction']['aliasAccountNumber'];
                $senderAccountName = $data['customer']['senderName'];
                $senderAccountNumber = $data['customer']['accountNumber'];
                $senderBankName = $data['customer']['bankName'];
                $status = 'Successful';
                $narration = $data['transaction']['narration'];
                $approvedAt = $data['transaction']['time'];
                $sessionId = $data['transaction']['sessionId'];
                
                $q = $user->where('account_number',$creditAccountNumber);
                $userDetails = $q->exists() ? $q->first(['id', 'referral', 'balance']) : $this->returnIt(['message' => 'Failed']);
            
                $uid = $userDetails->id;
                $refferedBy = $userDetails->referral;
                $currentBalance = $userDetails->balance;
                
                $hasDepositedBefore = ModelTrnLog::where('user_id', $uid)
                            ->where('type', 'Deposit')
                            ->exists();
                
                
                $fee = $this->callDpstCharge($amount);
                $credit  = $amount - $fee;
                
                $balanceBefore = $currentBalance;
                $balanceAfter = $currentBalance + $credit;
                
                $data = json_encode([
                    'id'                =>  $transactionId,
                    'img'               =>  null,
                    'status'            =>  'Success',
                    'reference'         =>  $transactionId,
                    'authorization'     =>  json_encode(['sourceAccountNumber' => $creditAccountNumber , 'sourceAccountName' => strtolower($senderAccountName), 'sourceBankName' => $senderBankName]),
                    'amount'            =>  $credit,
                    'fee'               =>  (float) $fee,
                    'message'           =>  $narration,
                    'fees_breakdown'    =>  $amount,
                    'currency'          =>  "NGN",
                    'balance_before'    => $balanceBefore,
                    'balance_after'     => $balanceAfter,
                ]);
    
                $ref = $transactionId;
                $type = 'Deposit';
                $trans_status = 'successful';
                
                $pay = json_decode($this->walletUpdate($credit, 'account_number', $creditAccountNumber));
                if($pay->code == 1) {
                    if(!$hasDepositedBefore && !empty($refferedBy)){
                        $referralData = floatval(AdminSetting::where('name', 'referral')->first()->data);
            
                        $referralBonus = getSettings('referral','bonus');
                        
                        $referralLimit = getSettings('referral','limit');
                        
                        if($credit >= $referralLimit){
                            self::referralBonus($refferedBy, $referralBonus);
                        }
            
                    }
                    PushNotification::dispatch($uid,"Deposit Successful",$narration,null,null,['transaction_id'=>$ref]);
                    TransactionLog::dispatch($uid, $type, $credit, $ref, $trans_status, $sessionId, $data);
                    return ['Webhook received', 200];
                }
                return ['Error', 401];
            }
        }

        public function paypointTfUDet(Request $request, User $user) {
            $validatedData = Validator::make($request->all(), [
                'username' => ['required'],
            ], [
                'username.required' => "Username is required",
            ]);
        
            if ($validatedData->fails()) {
                return response()->json([
                    'status' => 'false',
                    'data' => [
                        'message' => "Validation failed",
                        'error' => $validatedData->errors()
                    ]
                ], 400);
            }
        
            $request = Purify::clean($request->all());
            $input = strtolower($request['username']);
        
            if ($input == strtolower(Auth::user()->username) || $input == Auth::user()->account_number) {
                return response()->json([
                    'status' => 'false',
                    'data' => [
                        'message' => 'Failed to retrieve user details',
                        'error' => ['username' => 'Cannot query your own account']
                    ]
                ], 400);
            }
        
            $q = $user->where(function ($query) use ($input) {
                $query->whereRaw('LOWER(username) = ?', [$input])
                      ->orWhere('account_number', $input);
            })->where('view', 1);
        
            if (!$q->exists()) {
                return response()->json([
                    'status' => 'false',
                    'data' => [
                        'message' => 'User not found',
                        'error' => ['username' => 'No matching user found']
                    ]
                ], 400);
            }
        
            $userDetails = $q->first(['first_name', 'surname', 'other_name', 'username', 'account_number', 'profile']);
        
            return response()->json([
                'status' => 'true',
                'data' => [
                    'message' => 'User details retrieved successfully',
                    'data' => $userDetails
                ]
            ], 200);
        }


        public function p2pTf(Request $request, User $user) {
            $validatedData = Validator::make($request->all(), [
                'username' => ['required'],
                'amount' => ['required', 'numeric', 'min:100'],
                'transaction_pin' => ['required', 'numeric', 'digits:4', new TransactionPin],
                'reason' => ['nullable', 'min:3'],
            ], [
                'username.required' => "Username is required",
                'amount.required' => 'Transaction amount is required',
                'amount.numeric' => 'Amount must be numeric',
                'amount.min' => 'Minimum transaction amount is 100 NGN',
                'transaction_pin.required' => 'Transaction pin is required',
                'transaction_pin.numeric' => 'Transaction pin must be numeric',
                'transaction_pin.digits' => 'Transaction pin must be 4 digits',
                'reason.min' => "Enter at least 3 characters",
            ]);
        
            if ($validatedData->fails()) {
                return response()->json([
                    'status' => 'false',
                    'data' => [
                        'message' => "Validation failed",
                        'error' => $validatedData->errors()
                    ]
                ], 400);
            }
        
            $request = Purify::clean($request->all());
            $input = strtolower($request['username']);
        
            if ($input == strtolower(Auth::user()->username) || $input == Auth::user()->account_number) {
                return response()->json([
                    'status' => 'false',
                    'data' => [
                        'message' => 'Transaction failed',
                        'error' => ['username' => 'Cannot transfer to your own account']
                    ]
                ], 400);
            }
        
            // Check limit
            $wallet = new WalletController();
            $limit = json_decode($wallet->callCheckLimit($request['amount']));
            if ($limit->code == 0) {
                return response()->json([
                    'status' => 'false',
                    'data' => ['message' => $limit->msg]
                ], 400);
            }
        
            $q = $user->where(function ($query) use ($input) {
                $query->whereRaw('LOWER(username) = ?', [$input])
                      ->orWhere('account_number', $input);
            })->where('view', 1);
        
            if (!$q->exists()) {
                return response()->json([
                    'status' => 'false',
                    'data' => [
                        'message' => 'User not found',
                        'error' => ['username' => 'No matching recipient found']
                    ]
                ], 400);
            }
        
            $recipient = $q->first(['id', 'first_name', 'surname', 'username', 'account_number', 'profile', 'email', 'phone_number']);
        
            // Deduct from sender
            $dedRes = json_decode($wallet->callWalletUpdate($request['amount'], 'id', Auth::user()->id, '-'));
            if ($dedRes->code != 1) {
                return response()->json([
                    'status' => 'false',
                    'data' => [
                        'message' => 'Transfer failed',
                        'error' => $dedRes->msg
                    ]
                ], 400);
            }
        
            $now = Carbon::now('Africa/Lagos');
            $requestId = $now->format('YmdHi');
            $reference = $requestId . Str::random(5);
            $status = 'successful';
        
            $dataSender = [
                'id' => $reference,
                'status' => $status,
                'reference' => $reference,
                'authorization' => [
                    "account_name" => Auth::user()->first_name . ' ' . Auth::user()->surname,
                    "sender_country" => "NG",
                    "sender_bank" => 'Bill Vault',
                    "card_type" => 'transfer',
                    "sender_bank_account_number" => "XXXXXX" . substr(Auth::user()->account_number, -4),
                ]
            ];
        
            $dataReceiver = [
                'id' => $requestId,
                'img' => $recipient->profile,
                'status' => $status,
                'reference' => $requestId,
                'amount' => (float) $request['amount'],
                'reason' => $request['reason'] ?? '',
                'username' => $recipient->username,
                'currency' => "NGN",
                'account_number' => $recipient->account_number,
                'bank_name' => 'Bill Vault',
                'bank' => 000,
                'account_name' => $recipient->first_name . ' ' . $recipient->surname,
                'recipient_code' => ""
            ];
        
            TransactionLog::dispatch(Auth::user()->id, 'Transfer', $request['amount'], $requestId, $status, $recipient->username, json_encode($dataReceiver));
            PushNotification::dispatch(Auth::user()->id, "Transaction Successful", "Amount of N" . number_format($request['amount'], 2) . ' transferred to ' . $dataReceiver['account_name'] . ' successfully.', null, null, ['transaction_id' => $reference, 'icon' => asset('assets/images/bank/bank.jpg')]);
        
            $addRes = json_decode($wallet->callWalletUpdate($request['amount'], 'id', $recipient->id, '+'));
            if ($addRes->code != 1) {
                $wallet->callWalletUpdate($request['amount'], 'id', Auth::user()->id, '+');
                return response()->json([
                    'status' => 'false',
                    'data' => [
                        'message' => 'Transfer failed',
                        'error' => $addRes->msg
                    ]
                ], 400);
            }
        
            TransactionLog::dispatch($recipient->id, 'Deposit', $request['amount'], $reference . 'stm', $status, $recipient->username, json_encode($dataSender));
            PushNotification::dispatch($recipient->id, "Deposit Successful", "You have received N" . number_format($request['amount'], 2) . ' from ' . Auth::user()->first_name . ' ' . Auth::user()->surname, null, null, ['transaction_id' => $reference, 'icon' => asset('assets/images/bank/bank.jpg')]);
        
            return response()->json([
                'status' => 'true',
                'data' => [
                    'message' => 'Transaction successful',
                    'transaction_id' => $requestId
                ]
            ], 200);
        }


        private static function totalTransaction() {
            $today = Carbon::now()->format('Y-m-d');
            $totalTransactions = ModelTrnLog::where('user_id', Auth::user()->id)->where('type', '!=', 'Deposit')->where('status','successful')->whereRaw("DATE(created_at) = '$today'")->sum('amount');
            return $totalTransactions;

        }

        private static function checkLimit ($amount) {
            $accountLevel = Auth::user()->account_level;
            $limitDefs = self::getLimitDefs();
            $levelDets = $limitDefs[$accountLevel];
            $userLevel = $levelDets['tf'];
            $totToday = self::totalTransaction();
            if ( ($totToday + $amount) > $userLevel ) {
                // return json_encode(['code'=>0, 'msg'=>'Transfer exceeds account limit of '. $userLevel .' NGN']);
                return json_encode(['code'=>0, 'msg'=>'Transfer exceeds account limit of '. $userLevel .' NGN, Only '. ($userLevel - $totToday) . ' left']);
            }
            return json_encode(['code'=> 1,'msg'=> '']);
        }

        public static function callCheckLimit($amount) {
            return self::checkLimit($amount);
        }

        private static function walletUpdate($amount, $field, $value, $mode = '+') {
            
            $user = User::where($field, $value)->first();

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }
            
            // Fetch user balance
            if($user->id != 122 || $user->id != 151){
                $balance = $user->balance;
                
                $debitTypeArray = [
                    'Transfer', 'Airtime', 'Data', 'Electricity', 
                    'Card Creation', 'Card Funding', 'Cable TV', 
                    'Betting', 'Gift Card', 'WAEC Result Checker PIN', 
                    'Jamb', 'WAEC Registration PIN'
                ];
                
                $creditTypeArray = ['Deposit', 'Top-up', 'ATC', 'Sell Gift Card', 'Referral Bonus'];
                
                $user = User::where($field, $value)->first();
    
                $balance = $user->balance;
    
                $sumOfDeposits = ModelTrnLog::where('user_id', $user->id)
                    ->whereIn('type', $creditTypeArray)
                    ->whereIn('status', ['Success', 'Successful'])
                    ->sum('amount');
                
                $firstTransaction = ModelTrnLog::where('user_id', $user->id)
                    ->whereIn('status', ['Success', 'Successful'])
                    ->orderBy('created_at', 'asc')
                    ->first();
                
                $allDebitTransactions = ModelTrnLog::where('user_id', $user->id)
                    ->whereIn('type', $debitTypeArray)
                    ->whereIn('status', ['Success', 'Successful'])
                    ->sum('amount');
    
    
                $newTransaction = $amount + $allDebitTransactions; //dd($sumOfDeposits);
                
                // Restrict accounts based on conditions
                if (!in_array($user->id, [1, 2, 3, 122, 151,147])) { // Exclude specific user IDs
                    if ($mode !== "+") {
                        if (
                            ($firstTransaction && !in_array($firstTransaction->type, $creditTypeArray)) ||
                            ($balance > $sumOfDeposits) ||
                            ($allDebitTransactions > $sumOfDeposits) ||
                            ($newTransaction > $sumOfDeposits && $balance > $amount)
                        ) {
                            if ($balance > 0) {
                                $user->update(['is_account_restricted' => 1, 'is_ban' => 1, 'view' => 0]);
                                return json_encode(self::FRUAD_ALERT);
                            }
                        }
                    }
                }
            }
            
            if ($user) {
                $accountLevel = $user->account_level;
                $limitDefs = self::getLimitBalance();
                $levelDets = $limitDefs[$accountLevel];
                $userLevel = $levelDets['tf'];
                $currentBalance = (float)$user->balance;
                $wallet = new WalletController();
                // Update the balance based on the mode (+ or -)
                if ($mode === '+') {
                    $newBalance = $currentBalance + (float)$amount;
                    if($newBalance > $userLevel){
                        $user->update(['is_account_restricted' => 1]);
                    }
                } else {
                    if($user->is_account_restricted){
                        return json_encode(self::ACCOUNT_RESTRICTED);
                    }else{
                        $limit = json_decode($wallet->callCheckLimit($amount));
                        
                        if ($limit->code == 0) {return json_encode(self::LIMIT_EXCEEDED);
                        }
                        if ($currentBalance >= $amount) {
                            $newBalance = $currentBalance - (float)$amount;
                        } else {
                            return json_encode(self::INSUFFICIENT_BALANCE);
                        }
                    }
                    
                }
                // Update the user's balance
                if ($user->update(['balance' => $newBalance])) {
                    return json_encode(self::SUCCESSFUL);
                }
        
                return json_encode(self::OPERATION_FAILED);
            }
        
            return json_encode(self::OPERATION_FAILED);
        }

        public static function callWalletUpdate($amount, $field, $value, $mode = '+') {
            return self::walletUpdate($amount, $field, $value, $mode);
        }

        public function transfer(ModelTrnLog $trans, $event) {
            $ref = $event->data->reference;
            $q = $trans->join('users','transaction_logs.user_id','users.id')->where('transaction_logs.status','!=','successful')->where('transaction_logs.transaction_id',$ref);
            if ($q->doesntExist()) {
                return ['Not recd', 401];
            }
            $uid = $q->first(['users.id'])->id;
            $amount = ($event->data->amount / 100);
            $status = $this->checkStatus($event->data->status);

            //if success or pending update transaction status
            $rec_acct_name = $event->data->recipient->details->account_name;
            if ($status == 'successful' || $status == 'pending') {
                TransactionLog::dispatch($uid, null, null, $ref, $status, null, null);
                PushNotification::dispatch($uid,"Transfer Reversal","Amount of N" . number_format($amount,2) . ' transferred to ' . $rec_acct_name . ' has been reversed.');
                // Storage::put('tf/received'.Carbon::now()->format('Y-m-d_His'), json_encode($event, JSON_PRETTY_PRINT));
                return ['Webhook received', 200];
            }

            //if transaction is not success or pending, refund account and update status
            $pay = json_decode($this->walletUpdate($amount, 'id', $uid));
            if($pay->code == 1) {
                TransactionLog::dispatch($uid, null, null, $ref, $status, null, null);
                PushNotification::dispatch($uid,"Transfer Reversal","Amount of N" . number_format($amount,2) . ' transferred to ' . $rec_acct_name . ' has been reversed.');
                // Storage::put('tf/refund'.Carbon::now()->format('Y-m-d_His'), json_encode($event, JSON_PRETTY_PRINT));
                return ['Webhook received', 200];
            }
            
            //if refund failed, webhook not received
            return ['Not received', 401];
        }
        
        
        public function deposit(User $user, $event) {
            // Storage::put('dpst/begin_'.Carbon::now()->format('Y-m-d_His'), json_encode($event, JSON_PRETTY_PRINT));
            $q = $user->where('account_number',$event['accountNumber']);
            $uid = $q->exists() ? $q->first(['id'])->id : $this->returnIt(['message' => 'Failed']);

            $amount = ($event['transactionAmount']);
            $fee = $this->callDpstCharge($amount);
            $credit  = $amount - $fee;
            
            $data = json_encode([
                'id'                =>  $event['sessionId'],
                'img'               =>  null,
                'status'            =>  'Success',
                'reference'         =>  $event['sessionId'],
                'authorization'     =>  json_encode(['sourceAccountNumber' => $event['sourceAccountNumber'] , 'sourceAccountName' => strtolower($event['sourceAccountName']), 'sourceBankName' => $event['sourceBankName']]),
                'amount'            =>  $credit,
                'fee'               =>  (float) $fee,
                'message'           =>  $event['tranRemarks'],
                'fees_breakdown'    =>  $event['feeAmount'],
                'currency'          =>  $event['currency'],
            ]);

            $ref = $event['sessionId'];
            $type = 'Deposit';
            $status = 'successful';
            
            $pay = json_decode($this->walletUpdate($credit, 'account_number', $event['accountNumber']));
            if($pay->code == 1) {
                // Storage::put('dpst/'.Carbon::now()->format('Y-m-d_His'), json_encode($event, JSON_PRETTY_PRINT));
                PushNotification::dispatch($uid,"Deposit Successful","You have received a deposit of N" . number_format($credit,2).' from '.$event['sourceAccountName'],null,null,['transaction_id'=>$ref]);
                TransactionLog::dispatch($uid, $type, $credit, $ref, $status, null, $data);
                return ['Webhook received', 200];
            }
            return ['Error', 401];
        }

        private function checkStatus($status) {
            if ($status == "success") {
                $r = "successful";
            }elseif ($status == "failed") {
                $r = "failed";
            }elseif ($status == "reversed") {
                $r = "reversed";
            }else {
                $r = "pending";
            }
            return $r;
        }

        private function returnIt($data, $code = 401) {
            return response()->json($data, $code);
        }
        
        public function getUserTransactionSummary(Request $request)
        {
            $user = auth()->user();
    
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
    
            // Get the month to filter (default to current month)
            $month = $request->query('month', Carbon::now()->month);
            $year = $request->query('year', Carbon::now()->year);
     
            // Calculate total expenses and credits for the specified month and year
            $debitTypeArray = [
                'Transfer', 'Airtime', 'Data', 'Electricity', 
                'Card Creation', 'Card Funding', 'Cable TV', 
                'Betting', 'Gift Card', 'WAEC Result Checker PIN', 
                'Jamb', 'WAEC Registration PIN'
            ];
            
            $creditTypeArray = ['Deposit', 'Top-up', 'ATC', 'Sell Gift Card'];
            
            $expenses = ModelTrnLog::where('user_id', $user->id)
                ->whereIn('type', $debitTypeArray) 
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->sum('amount');
            
            $credits = ModelTrnLog::where('user_id', $user->id)
                ->whereIn('type', $creditTypeArray) 
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->sum('amount');

    
            return response()->json([
                'status' => true,
                'data' => [
                    'month' => $month,
                    'year' => $year,
                    'total_expenses' => $expenses,
                    'total_credits' => $credits,
                ]
            ]);
        }
    }
