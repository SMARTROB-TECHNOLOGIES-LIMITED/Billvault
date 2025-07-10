<?php

namespace App\Http\Controllers\Api;


use App\Helpers;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use App\Jobs\TransactionLog;
use Illuminate\Http\Request;
use App\Rules\TransactionPin;
use App\Models\PaystackRecipient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;

use function App\getSettings;
use function App\retErrorSetting;

class SafeHaven extends Controller
{


    public function generateRef() {
        return Uuid::uuid4();
    }

    public function getBankList() {
        $access_token = Helpers::refreshToken();

        $client_id = getSettings('safehaven_default','client_id');
        $url = getSettings('safehaven_default','url');
        $debitAccountNumber = getSettings('safehaven_default','debitAccountNumber');

        $endpoint = $url."transfers/banks";

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
            'ClientID' => $client_id
        ];

        $response = Http::withHeaders($headers)->get($endpoint);


        $response_body = json_decode($response->body());
        if ($response_body->statusCode == 200) {
            $filteredData = collect($response_body->data)->map(function ($item) {
                return [
                    'bankName' => $item->name,
                    'bankCode' => $item->bankCode,
                    // 'categoryId' => $item->categoryId,
                    // 'routingKey' => $item->routingKey
                ];
            });

            return response()->json([
                'status' => 'true',
                'message' => 'List retrieved successfully',
                'data' => $filteredData
            ], 200);
        }else{
            return response()->json([
                'status'=>'false',
                'data' => [
                    'message' => "Error Getting Bank list",
                    'error' => "Error Getting Bank list",
                ]
            ],400);
        }
    }

    public function getAccountDetails(Request $request) {
        $validatedData = Validator::make($request->all(['account_number','bank_code']), [
            'bank_code' => ['required','numeric'],
            'account_number' => ['required','numeric','digits:10'],
        ],[
            'bank_code.required' => "Please select recipient bank!",
            'bank_code.numeric' => "Bank can only contain digits!",
            'account_number.required' => "Account number is required!",
            'account_number.numeric' => "Account number can only contain digits!",
            'account_number.digits' => "Account number can contain only 10 digits!"
        ]);

        if ($validatedData->fails()) {
            return response()->json([
                'status'=>'false',
                'data' => [
                    'message' => "Validation failed",
                    'error' => $validatedData->errors()
                ]
            ],400);
        }

        $request = Purify::clean($request->all());
        $bankCode = $request['bank_code'];
        $accountNumber = $request['account_number'];

        $response = Helpers::getAccountDetails($bankCode, $accountNumber);
        if ($response['statusCode'] == 200) {
            return response()->json([
                'status'=> 'true',
                'message'=> 'Account resolved successfully',
                'data'=> $response['data']
            ],200);
        }

        return response()->json([
            'status'=> 'false',
            'data'=> [
                'message'=> 'Failed to resolve account',
                $response
            ]
        ],400);
    }

    public function safeHavenTransfer(Request $request, $ref = null) {


            $validatedData = Validator::make($request->all(['reason','amount','recipient_code','transaction_pin', 'bank_name', 'account_number', 'bank_code', 'name_enquiry_reference']), [

                'transaction_pin' => ['required','numeric','digits:4', new TransactionPin],
                'amount' => ['required','numeric','min:50'],
                'is_beneficiary' => ['boolean'],
                'bank_name' => ['required'],
                'account_number' => ['required', 'numeric', 'digits:10'],
                'bank_code' => ['required'],
                'name_enquiry_reference' => ['required']
            ],[
                'transaction_pin.required' => 'Transaction pin cannot be empty',
                'transaction_pin.numeric' => 'Only digits allowed for transaction pin',
                'transaction_pin.digits' => 'Transaction pin must have 4 (four) digits',

                'amount.required' => "Enter amount to transfer",
                'amount.numeric' => "Amount can only be numeric characters",
                'amount.min' => "Minimum transaction amount is 50 NGN",
                'is_beneficiary.boolean' => 'Beneficiary status must be true or false',

                'bank_name.required' => 'Bank Name Required',

                'account_number.required' => "Account number is required!",
                'account_number.numeric' => "Account number can only contain digits!",
                'account_number.digits' => '"Account number must have 10 (digits) digits',

                'bank_code.required' => "Select Bank",
                'name_enquiry_reference.required' => "Account Number Not validated",

            ]);

            if ($validatedData->fails()) {
                return response()->json([
                    'status'=>'false',
                    'data' => [
                        'message' => "Validation failed",
                        'error' => $validatedData->errors()
                    ]
                ],400);
            }


            $is_beneficiary = isset($request['is_beneficiary']) ? $request['is_beneficiary'] : null;
            $request = Purify::clean($request->all());


            //Check Limit
            $amt_wo_fee = $request['amount'];
            $chargeInit = new WalletController();
            $fee = (float) $chargeInit->callTfCharges($amt_wo_fee);
            $amt_w_fee = (float) $amt_wo_fee + $fee;

            $wallet = new WalletController();
            $limit = json_decode($wallet->callCheckLimit($amt_wo_fee));
            if ($limit->code == 0) {return response()->json([
                'status' => 'false',
                'data'=> ['message' => $limit->msg]],400);
            }


            //Deduct amount
            $dedRes = json_decode($wallet->callWalletUpdate($amt_w_fee, 'id', Auth::user()->id, '-'));
            if ($dedRes->code !== 1) {
                return response()->json([
                    'status' => 'false',
                    'data'=> [
                        'message' => 'Transaction failed',
                        'error'=> $dedRes->msg
                    ]
                ],400);
            }

            $source = "balance";
            $reference = $ref == null ? $this->generateRef() : $ref;
            $reaso_on = 'Transfer from ' . Auth::user()->first_name. ' '. Auth::user()->surname;
            isset($request['narration']) ? $request['narration'] : $reaso_on  ;


        try {
            $sessionId= $request['name_enquiry_reference'];
            $account_number = $request['account_number'];
            $bank_code = $request['bank_code'];
            $response = Helpers::safeHavenTransfer($sessionId, $bank_code, $account_number, $amt_wo_fee, $reaso_on);

            // dd($response);


            if (!isset($response['statusCode']) || $response['statusCode'] != 200) {
                $dedRes = json_decode($wallet->callWalletUpdate($amt_w_fee, 'id', Auth::user()->id, '+'));
                return response()->json(retErrorSetting());
            }

            $user_id = Auth::user()->id;

            $responseData = $response['data'];

            $data = json_encode([
                'id' => $responseData['sessionId'],
                'img' => asset('assets/images/bank/bank.jpg'),
                'status' => "Success",
                'reference' => $responseData['paymentReference'],
                'amount' => (float) ($request['amount'] + ($fee)),
                'fee' => (float) ($fee),
                'reason' => $responseData['narration'],
                'transfer_code' => $responseData['sessionId'],
                'account_number' => $responseData['creditAccountNumber'],
                'provider' => $responseData['provider'],
                'bank'  =>  $request['bank_name'],
                'account_name'=>$responseData['creditAccountName'],
                'recipient' => $responseData['creditAccountName'],
                'message'   => $response['message'],
            ]);
            if ($response['statusCode'] == 200) {
                $savedData = [
                    "account_number" => $request['account_number'],
                    "bank_code" => $request['bank_code'],
                    "name_enquiry_reference" => $request['name_enquiry_reference'],
                    'bank_name'  =>  $request['bank_name'],
                    'account_name' => $responseData['creditAccountName'],
                ];

                if (isset($is_beneficiary) && ($is_beneficiary == 1 || $is_beneficiary == true)) {
                    Helpers::addBeneficiary('transfer', $savedData, $responseData['creditAccountName'], $request['account_number'], $request['bank_name']);
                }
                TransactionLog::dispatch($user_id, "Transfer", $request['amount'], $responseData['paymentReference'], "Success", $request['name_enquiry_reference'], $data);
                return response()->json([
                    "status"=> "true",
                    "data" => [
                        "message" => "Transaction successful",
                        "transaction_id" => $responseData['paymentReference']
                    ]
                ],200);
            }


            json_decode($wallet->callWalletUpdate($amt_w_fee, 'id', Auth::user()->id));
            TransactionLog::dispatch($user_id, "Transfer", $request['amount'], $reference, "failed", $request['recipient_code'], $data);
            return response()->json([
                "status"=> "false",
                "data"=> [
                    "message"=> "Transaction failed",
                ]
            ],400);

        } catch (\Illuminate\Http\Client\RequestException $e) {
            if (strpos($e->getMessage(), 'Could not resolve host') !== false) {
                return response()->json([
                    "status" => "false",
                    "data" => [
                        "message" => "Network error: Unable to resolve the host. Please check your connection or try again later."
                    ]
                ], 503); // 503 is the HTTP status code for service unavailable
            }

            return response()->json([
                "status" => "false",
                "data" => [
                    "message" => "An error occurred: " . $e->getMessage()
                ]
            ], 500);
        }


    }

    public function refreshToken(){
        $strowallet = Helpers::refreshToken();
        return $strowallet['access_token'];
    }

    public function verifyBVN(Request $request){
        $validatedData = Validator::make($request->only(['bvn']), [
            'bvn' => 'required|numeric|digits:11|unique:users,bvn',
        ], [
            'bvn.unique' => 'BVN already attached to an account!',
            'bvn.required' => 'BVN is required.',
            'bvn.numeric' => 'BVN must be a numeric value.',
            'bvn.digits' => 'BVN must be exactly 11 digits.',
        ]);

        if ($validatedData->fails()) {
            return response()->json([
                'status'=>'false',
                'data' => [
                    'message' => "Validation failed",
                    'error' => $validatedData->errors()
                ]
            ],400);
        }
        $bvn = $request->bvn;
        $user = Auth::user();
        $verify_data = Helpers::verifyBVN($bvn);


        if($verify_data['statusCode']==200 && $verify_data['data']['status']=="SUCCESS"){
            $user->pre_bvn = $bvn;
            $user->save();
            return response()->json([
                'status'=> 'true',
                'message'=> 'OTP sent to complete verification',
                'data'=> $verify_data['data']

            ],200);
        }else{
            return response()->json([
                'status'=>'false',
                'data' => [
                    'message' => "Verification failed",
                    'error' => $verify_data['message']
                ]
            ],400);
        }
    }

    public function newCustomerRegistration(Request $request, User $user) {
        $pure = Purify::clean($request->all(['email']));
        $us = User::where('email',$pure['email'])->where('complete',null)->where('email_verified_at',null);
        if ($us->exists()) {
            $us->delete();
        }
        $validatedData = Validator::make($request->only(['email', 'phone_number', 'bvn']), [
            'email' => 'required|email|string|unique:users,email',
            'phone_number' => 'required|unique:users,phone_number',
            'bvn' => 'required|numeric|digits:11|unique:users,bvn'
        ], [
            'email.required' => 'Input a valid email address.',
            'email.email' => 'Input a valid email address.',
            'email.unique' => 'Account already exists!',
            'phone_number.required' => 'Phone number is required.',
            'phone_number.unique' => 'Phone number already exists!',
            'bvn.unique' => 'BVN already attached to an account!',
            'bvn.required' => 'BVN is required.',
            'bvn.numeric' => 'BVN must be a numeric value.',
            'bvn.digits' => 'BVN must be exactly 11 digits.',
        ]);

        if ($validatedData->fails()) {
            return response()->json([
                'status'=>'false',
                'data' => [
                    'message' => "Validation failed",
                    'error' => $validatedData->errors()
                ]
            ],400);
        }

        $token = $this->generateOTP();
        $bvn = $request['bvn'];
        $verify_data = Helpers::verifyBVN($bvn);

        if($verify_data['statusCode']==200 && $verify_data['data']['status']=="SUCCESS"){
            $userData = [
                'email' => $request['email'],
                'bvn' => $request['bvn'],
                'phone_number' => $request['phone_number'],
                'email_token' => serialize(['token'=>$token,'expire_at'=>now()->addMinutes(10)])
            ];

            if ($user->create($userData)) {
                return $this->emailConfirmOtp($token, $request['email'], $verify_data['data']['_id']);
            }
        }else{
            return response()->json([
                'status'=>'false',
                'data' => [
                    'message' => "Verification failed",
                    'error' => $verify_data['message']
                ]
            ],400);
        }

    }

    public function createSafeHavenAccount(Request $request){
        $validatedData = Validator::make($request->all(['v_id','otp']), [
            'v_id' =>  'required',
            'otp' => 'required|numeric|digits:6'
        ],[
            'email.required' => 'Verify BVN.',
            'email.exists' => 'Account with email address not found!',
            'otp.required' => 'Email verification token is required!',
            'otp.numeric' => 'Token can only contain numeric characters!',
            'otp.digits' => 'otp must be 6 (six) digits in length!'
        ]);

        if ($validatedData->fails()) {
            return response()->json([
                'status'=>'false',
                'data' => [
                    'message' => "Validation failed",
                    'error' => $validatedData->errors()
                ]
            ],400);
        }
        $user = Auth::user();
        $otp = $request['otp'];
        $vid = $request['v_id'];
        $account_data = Helpers::createSafeHavenAccount($otp, $vid);

        if( $account_data['statusCode']==200 ){
            $res = $account_data['data'];
            $fname = ucfirst(strtolower($res['subAccountDetails']['firstName']));
            $sname = ucfirst(strtolower($res['subAccountDetails']['lastName']));
            $bvn = ucfirst(strtolower($res['subAccountDetails']['bvn']));
                $user->first_name = $fname;
                $user->surname = $sname;
                $user->account_number = $res['accountNumber'];
                $user->account_name = $res['accountName'];
                $user->bank_name = "SafeHaven MFB";
                $user->bvn = $bvn;
                $user->is_bvn_verified = 1;


            if ($user->save()) {
                return response()->json([
                    'status'=>'true',
                    'is_bvn_verified' => 1,
                    'data' => [
                        'message' => 'Account Verification completed',
                    ]
                ],200);
            }
        }else{
            return response()->json([
                'status'=>'false',
                'data' => [
                    'message' => "Verification failed",
                    'error' => $account_data['message']
                ]
            ],400);
        }

    }

    public function generateOTP() {
        $g = (string) rand(1000,9999);
        if (strlen($g) != 4) {$this->generateOTP();}
        return $g;
    }

    public function emailConfirmOtp($token, $email, $verificationID = null) {

        $message = (is_null($verificationID)) ? "Kindly proceed to the next step" : "OTP sent to BVN number for verification";

        $userdata = User::where('email', $email)->first();

        $userdata->email_verified_at = now();
        $userdata->email_token = null;
        if ($userdata->save() && Auth::loginUsingId($userdata->id)) {
            $user = Auth()->user();
            $tokenResult = $user->createToken('access_token')->plainTextToken;
            return response()->json(['status'=>'true','data' => [
                'message' => $message,
                '_id' => $verificationID,
                'user' => Auth::user(),
                'access_token' => $tokenResult,
                'token_type' => 'Bearer'
            ]],200);

        }

        return response()->json(['status'=>'false','data' => ['message' => "An error occurred"]],400);
    }

    // New Flow

    public function Registration(Request $request, User $user) {
        $pure = Purify::clean($request->all(['email']));
        $us = User::where('email',$pure['email'])->where('complete',null)->where('email_verified_at',null);
        if ($us->exists()) {
            $us->delete();
        }
        $validatedData = Validator::make($request->only(['email', 'phone_number', 'first_name', 'last_name']), [
            'email' => 'required|email|string|unique:users,email',
            'phone_number' => 'required|unique:users,phone_number',
            'first_name' => 'required',
            'last_name' => 'required',
        ], [
            'email.required' => 'Input a valid email address.',
            'email.email' => 'Input a valid email address.',
            'email.unique' => 'Account already exists!',
            'phone_number.required' => 'Phone number is required.',
            'phone_number.unique' => 'Phone number already exists!',
            'last_name' => 'Input your last name.',
            'first_name' => 'Input your first name.',
        ]);

        if ($validatedData->fails()) {
            return response()->json([
                'status'=>'false',
                'data' => [
                    'message' => "Validation failed",
                    'error' => $validatedData->errors()
                ]
            ],400);
        }

        $token = $this->generateOTP();


        $userData = [
            'email' => $request['email'],
            'first_name' => $request['first_name'],
            'surname' => $request['last_name'],
            'phone_number' => $request['phone_number'],
            'complete' => 0,
            'email_token' => serialize(['token'=>$token,'expire_at'=>now()->addMinutes(10)])
        ];

        if ($user->create($userData)) {
            return $this->emailConfirmOtp($token, $request['email']);
        }


    }

    public function createSafeHavenAccountAndComplete(Request $request){
        $validatedData = Validator::make($request->all(['v_id','otp']), [
            'v_id' =>  'required',
            'otp' => 'required|numeric|digits:6'
        ],[
            'email.required' => 'Verify BVN.',
            'email.exists' => 'Account with email address not found!',
            'otp.required' => 'Email verification token is required!',
            'otp.numeric' => 'Token can only contain numeric characters!',
            'otp.digits' => 'otp must be 6 (six) digits in length!'
        ]);

        if ($validatedData->fails()) {
            return response()->json([
                'status'=>'false',
                'data' => [
                    'message' => "Validation failed",
                    'error' => $validatedData->errors()
                ]
            ],400);
        }
        $user = Auth::user();
        $otp = $request['otp'];
        $vid = $request['v_id'];
        $account_data = Helpers::createSafeHavenAccount($otp, $vid);

        if( $account_data['statusCode']==200 ){
            $res = $account_data['data'];
            $fname = ucfirst(strtolower($res['subAccountDetails']['firstName']));
            $sname = ucfirst(strtolower($res['subAccountDetails']['lastName']));
            $bvn = ucfirst(strtolower($res['subAccountDetails']['bvn']));
                $user->first_name = $fname;
                $user->surname = $sname;
                $user->account_number = $res['accountNumber'];
                $user->account_name = $res['accountName'];
                $user->bank_name = "SafeHaven MFB";
                $user->bvn = $bvn;
                $user->pre_bvn = null;
                $user->is_bvn_verified = 1;
                $user->complete = 1;


            if ($user->save()) {
                return response()->json([
                    'status'=>'true',
                    'is_bvn_verified' => 1,
                    'complete' => 1,
                    'data' => [
                        'message' => 'Account Verification completed',
                    ]
                ],200);
            }
        }else{
            return response()->json([
                'status'=>'false',
                'data' => [
                    'message' => "Verification failed",
                    'error' => $account_data['message']
                ]
            ],400);
        }

    }
}
