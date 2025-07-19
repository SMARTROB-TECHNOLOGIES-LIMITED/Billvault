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
use App\Services\FirebaseService;
use App\Notifications\TransactionNotification;

use function App\getSettings;
use function App\retErrorSetting;

class NombaController extends Controller
{
    
    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }
    

    public function generateRef() {
        return Uuid::uuid4();
    }
    
    
    public function refreshToken(){
        $token = Helpers::refreshNombaToken();
        return $token;
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
        $accountName = $request['first_name']." ".$request['last_name'];
        $account = Helpers::createNombaAccount($accountName);
        
        if($account['code'] == 00 OR $account['code'] == 200){
            $data = $account['data'];
            $userData = [
                'email' => $request['email'],
                'first_name' => $request['first_name'],
                'surname' => $request['last_name'],
                'phone_number' => $request['phone_number'],
                'complete' => 0,
                'account_number' => $data['bankAccountNumber'],
                'account_name' => $data['bankAccountName'],
                'is_nomba' => 1,
                'bank_name' => $data['bankName'],
                'email_token' => serialize(['token'=>$token,'expire_at'=>now()->addMinutes(10)])
            ];

            if ($user->create($userData)) {
                return $this->emailConfirmOtp($token, $request['email']);
            }
            
        }
        
        return response()->json([
            'status'=>'false',
            'data' => [
                'error' => 'Error Creating Account',
                'data' => $account,
            ]
        ],$account['code']);

        
        
        
    }
    
    public function createNombaAccount(Request $request){
        $user = Auth::user();
        $accountName = $user->first_name." ".$user->last_name;
        
        if(is_null($accountName)){
            return response()->json([
                'status'=>'false',
                'data' => [
                    'error' => 'Validation Error',
                    'message' => 'Account Registraion Not Completed, Name not available',
                ]
            ],400);
        }
        
        $account = Helpers::createNombaAccount($accountName);
        
        if($account['code'] == 00 OR $account['code'] == 200){
            
            $data = $account['data'];
            $user->account_number = $data['bankAccountNumber']; 
            $user->account_name = $data['bankAccountName'];
            $user->bank_name = $data['bankName'];
            $user->is_nomba = 1;
            if ($user->save()) {
                return response()->json([
                    'status'=>'true',
                    'data' => [
                        'message' => 'Account Updated Successfully',
                    ]
                ],200);
            }
        }
        
        return response()->json([
            'status'=>'false',
            'data' => [
                'error' => $account['description'],
                'message' => $account,
            ]
        ],$account['code']);

    }
    
    public function getBankList(){
        $response = Helpers::getBankList();
        if($response['code'] == 200 OR $response['code'] == 00){
            return response()->json([
                'status' => 'true',
                'message' => 'List retrieved successfully',
                'data' => $response['data']
            ], 200);
        }else{
            return response()->json([
                'status'=>'false',
                'data' => [
                    'message' => "Error Getting Bank list",
                    'error' => $response['description'],
                ]
            ],$response['code']);
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
        
        $response = Helpers::getAccountDetailsWithNomba($bankCode, $accountNumber);
        
        // return $response;
        if ($response['code'] == 00) {
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
                'error' => $response['description']
            ]
        ],$response['code']);
    }

    public function nombaTransfer(Request $request, $ref = null)
    {
        $validatedData = Validator::make($request->only([
            'reason','amount','transaction_pin', 'bank_name', 'account_number', 'bank_code'
        ]), [
            'transaction_pin' => ['required','numeric','digits:4', new TransactionPin],
            'amount' => ['required','numeric','min:50'],
            'is_beneficiary' => ['boolean'],
            'bank_name' => ['required'],
            'account_number' => ['required', 'numeric','digits:10'],
            'bank_code' => ['required'],
        ]);
    
        if ($validatedData->fails()) {
            return response()->json([
                'status' => false,
                'data' => ['message' => 'Validation failed', 'error' => $validatedData->errors()]
            ], 400);
        }
        
        $data = $validatedData->validated();

        
        // if ($data['bank_code'] == '305') {
        //     return response()->json([
        //         'status' => false,
        //         'data' => [
        //             'message' => 'Transfers to this bank are not allowed at the moment.'
        //         ]
        //     ], 422);
        // }
    
        $getName = Helpers::getAccountDetailsWithNomba($request['bank_code'], $request['account_number']);
        if (!isset($getName['code']) || $getName['code'] != 00) {
            return response()->json([
                'status' => false,
                'data' => ['message' => 'Transaction Failed', 'error' => $getName['description']]
            ], 400);
        }
    
        $accountName = $getName['data']['accountName'];
        $is_beneficiary = $request->boolean('is_beneficiary', false);
        $request = Purify::clean($request->all());
    
        $amount = (float) $request['amount'];
        $wallet = new WalletController();
        $fee = (float) $wallet->callTfCharges($amount);
        $totalAmount = $amount + $fee;
    
        $limit = json_decode($wallet->callCheckLimit($amount));
        
        // dd($limit);
        if ($limit->code != 1) {
            return response()->json([
                'status' => false,
                'data' => ['message' => $limit->msg]
            ], 400);
        }
        $user = Auth::user();
        
        $balanceBefore = $user->balance;
        $balanceAfter = $balanceBefore - $totalAmount;
    
        $deduction = json_decode($wallet->callWalletUpdate($totalAmount, 'id', Auth::id(), '-'));
        if ($deduction->code !== 1) {
            return response()->json([
                'status' => false,
                'data' => ['message' => 'Transaction failed', 'error' => $deduction->msg]
            ], 400);
        }
    
        $reference = $ref ?? 'Trans-Ref-' . Str::upper(Str::random(10)) . '-' . time();
        
        $reason = $request['narration'] ?? 'Transfer from ' . $user->first_name . ' ' . $user->surname;
    
        $transactionData = json_encode([
            'img' => asset('assets/images/bank/bank.jpg'),
            'status' => "Pending",
            'transaction_id' => $reference,
            'amount' => $totalAmount,
            'fee' => $fee,
            'reason' => $reason,
            'account_number' => $request['account_number'],
            'bank' => $request['bank_name'],
            'account_name' => $accountName,
            'recipient' => $accountName,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
        ]);
    
        // Step 1: Save pending log before processing external transfer
        TransactionLog::dispatch(
            $user->id,
            "Transfer",
            $amount,
            $reference,
            "Pending",
            $request['account_number'],
            $transactionData
        );
    
        try {
            $response = Helpers::nombaTransfer(
                $request['bank_code'],
                $request['account_number'],
                $amount,
                $reason,
                $accountName, $reference
            );
    
            // if (!isset($response['code']) || $response['code'] != 00) {
            //     $this->processRefund($user->id, $totalAmount);
                
            //     $user->refresh();
            //     $refreshedBalance = $user->balance;
                
            //     $updatedTransactionData = json_encode(array_merge(json_decode($transactionData, true), [
            //         'status' => 'Failed',
            //         'balance_before' => $refreshedBalance,
            //         'balance_after' => $refreshedBalance,
            //         'message' => $response['description']
            //     ]));
    
            //     // Update log to failed
            //     TransactionLog::dispatch(
            //         $user->id,
            //         "Transfer",
            //         $amount,
            //         $reference,
            //         "Failed",
            //         $request['account_number'],
            //         $updatedTransactionData 
            //     );
    
            //     return response()->json([
            //         'status' => false,
            //         'data' => ['message' => $response['description'] ?? 'Transaction failed']
            //     ], 400);
            // }
            
            if (!isset($response['code']) || !in_array($response['code'], [00, 202])) {
                // Only refund if not success or processing
                $this->processRefund($user->id, $totalAmount);
            
                $user->refresh();
                $refreshedBalance = $user->balance;
            
                $updatedTransactionData = json_encode(array_merge(json_decode($transactionData, true), [
                    'status' => 'Failed',
                    'balance_before' => $refreshedBalance,
                    'balance_after' => $refreshedBalance,
                    'message' => $response['description'] ?? 'Transaction failed'
                ]));
            
                TransactionLog::dispatch(
                    $user->id,
                    "Transfer",
                    $amount,
                    $reference,
                    "Failed",
                    $request['account_number'],
                    $updatedTransactionData 
                );
            
                return response()->json([
                    'status' => false,
                    'data' => ['message' => $response['description'] ?? 'Transaction failed']
                ], 400);
            }
            
            // Handle 202 (Processing)
            if ($response['code'] == 202) {
                $updatedTransactionData = json_encode(array_merge(json_decode($transactionData, true), [
                    'status' => 'Processing',
                    'transaction_id' => $response['data']['id'] ?? null,
                    'message' => $response['description'] ?? 'Processing'
                ]));
            
                TransactionLog::dispatch(
                    $user->id,
                    "Transfer",
                    $amount,
                    $reference,
                    "Processing",
                    $request['account_number'],
                    $updatedTransactionData
                );
            
                return response()->json([
                    "status" => true,
                    "data" => [
                        "message" => "Transfer is processing",
                        "transaction_id" => $reference
                    ]
                ], 202);
            }

    
            $responseData = $response['data'];
    
            // Update transaction to Success
            $transactionData = json_encode(array_merge(json_decode($transactionData, true), [
                'status' => 'Success',
                'transaction_id' => $responseData['id'],
                'message' => $response['description'] ?? 'Success',
            ]));
    
            TransactionLog::dispatch(
                $user->id,
                "Transfer",
                $amount,
                $reference,
                "Success",
                $request['account_number'],
                $transactionData
            );
    
            if ($is_beneficiary) {
                Helpers::addBeneficiary('transfer', [
                    "account_number" => $request['account_number'],
                    "bank_code" => $request['bank_code'],
                    'bank_name' => $request['bank_name'],
                    'account_name' => $accountName
                ], $accountName, $request['account_number'], $request['bank_name']);
            }
    
            if ($user->device_token) {
                $this->firebaseService->sendNotification("Transfer Successful", "Transaction Successful", $user->device_token);
            }
    
            $user->notify(new TransactionNotification(
                "Transfer Successful",
                "$amount Successfully Transferred to $accountName",
                null,
                null,
                ['transaction_id' => $responseData['id']]
            ));
    
            return response()->json([
                "status" => true,
                "data" => [
                    "message" => "Transaction successful",
                    "transaction_id" => $reference
                ]
            ], 200);
    
        } catch (\Throwable $e) {
            $this->processRefund($user->id, $totalAmount);
            
            $user->refresh();
            $refreshedBalance = $user->balance;
            
            $updatedTransactionData = json_encode(array_merge(json_decode($transactionData, true), [
                'status' => 'Failed',
                'balance_before' => $refreshedBalance,
                'balance_after' => $refreshedBalance,
                'message' => "Network error or timeout."
            ]));
    
            TransactionLog::dispatch(
                $user->id,
                "Transfer",
                $amount,
                $reference,
                "Failed",
                $request['account_number'],
                $updatedTransactionData
            );
    
            return response()->json([
                "status" => false,
                "data" => [
                    "message" => "Network error or timeout.",
                    "error" => $e->getMessage()
                ]
            ], 500);
        }
    }


    private function processRefund($userId, $amount)
    {
        $wallet = new WalletController();
        return json_decode($wallet->callWalletUpdate($amount, 'id', $userId, '+'));
    }

    
}
