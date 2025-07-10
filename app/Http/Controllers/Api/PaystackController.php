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

use function App\getSettings;
use function App\retErrorSetting;

class PaystackController extends Controller
{

    public function generateRef() {
         return Uuid::uuid4();
    }

    public function getBankListOld($country='nigeria', $perpage = 50, $currency='NGN') {
        !is_numeric($perpage) or empty($perpage) ? $perpage = 12 : false;

        $paysk = getSettings('paystack','secretkeypaystack');
        if ($paysk == "error") {return response()->json(retErrorSetting());}

        $headers = [
            'Content-Type' => 'application/json',
            'authorization' => 'Bearer ' . $paysk
        ];
        $body = [
        ];
        
        $response = Http::withHeaders($headers)->get('https://api.paystack.co/bank?currency='.$currency.'&perPage='.$perpage.'&pay_with_bank_transfer=true', $body);
        // $response = Http::withHeaders($headers)->get('https://api.paystack.co/bank?currency='.$currency.'&perPage='.$perpage, $body);
        $response_body = json_decode($response->body());
        if ($response_body->status == "true") {
            $collection = collect($response_body->data)->where('active', 'true')->all();
            return response()->json([
                'status' => 'true',
                'data'=> [
                    'message' => 'List retrieved successfully',
                    'data'=> $collection
                ]
            ],200);
        }
    }
    
    public function getBankList() {
        

        $paysk = getSettings('strowallet','publickey');
        if ($paysk == "error") {return response()->json(retErrorSetting());}

        $headers = [
            'Content-Type' => 'application/json',
            'authorization' => 'Bearer ' . $paysk
        ];
        
        $response = Http::get('https://strowallet.com/api/banks/lists', [
            'public_key' => $paysk
        ]);

        
        $response_body = json_decode($response->body());
        if (isset($response_body->success) && $response_body->success == "true") {
            $collection = collect($response_body->data);
            return response()->json([
                'status' => 'true',
                'data'=> [
                    'message' => 'List retrieved successfully',
                    'data'=> $collection
                ]
            ],200);
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

    public function createRecipient(Request $request, PaystackRecipient $pr) {
        $validatedData = Validator::make($request->all(['account_number','bank_code','name']), [
            'name' => ['required'],
            'bank_code' => ['required','numeric'],
            'account_number' => ['required','numeric','digits:10'],
        ],[
            'name.required' => "Enter recipient name!",
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
        $pp = $pr->where('account_number', $request['account_number']);
        if ($pp->doesntExist()) {
            $type = "nuban";
            $currency = "NGN";

            $paysk = getSettings('paystack','secretkeypaystack');
            if ($paysk == "error") {return response()->json(retErrorSetting());}
            $body = [
                "type" => $type,
                "name" => $request['name'],
                "account_number" => $request['account_number'],
                "bank_code" => $request['bank_code'],
                "currency" => $currency
            ];
            $headers = [
                'Content-Type' => 'application/json',
                'authorization' => 'Bearer ' . $paysk
            ];
            $response = Http::withHeaders($headers)->post("https://api.paystack.co/transferrecipient", $body);
            $response = json_decode($response->body());
            if ($response->status == "true") {
                $dets = [
                    'user_id' => Auth::user()->id,
                    'bank_code' => $response->data->details->bank_code,
                    'bank_name' => $response->data->details->bank_name,
                    'account_number' => $response->data->details->account_number,
                    'account_name' => $response->data->details->account_name,
                    'recipient_code' => $response->data->recipient_code,
                    'authorization_code' => $response->data->details->authorization_code,
                    'data' => json_encode($response->data),
                ];
                if ($pr->create($dets)) {
                    return [
                        "status"=> "true",
                        "data"=> [
                            "message"=> "Recipient created successfully",
                            "data"=> [
                                'bank_code' => $response->data->details->bank_code,
                                'bank_name' => $response->data->details->bank_name,
                                'account_number' => $response->data->details->account_number,
                                'account_name' => $response->data->details->account_name,
                                'recipient_code' => $response->data->recipient_code,
                                'authorization_code' => $response->data->details->authorization_code,
                            ],
                        ]
                    ];
                }
            }
        }else {
            $s = $pp->first(['bank_code','bank_name','account_number','account_name','recipient_code']);
            return [
                "status"=> "true",
                "data"=> [
                    "message"=> "Recipient already exists",
                    "data" =>[
                        'bank_code' => $s->bank_code,
                        'bank_name' => $s->bank_name,
                        'account_number' => $s->account_number,
                        'account_name' => $s->account_name,
                        'recipient_code' => $s->recipient_code,
                    ]
                ]
            ];
        }

        return [
            "status"=> "false",
            "data"=> [
                "message"=> "Something went wrong, Please try again",
            ]
        ];
    }

    public function resolveAccount(Request $request) {
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
        $query = PaystackRecipient::where('account_number',$request['account_number'])->where('bank_code',$request['bank_code']);
        if ($query->exists()) {
            $response = $query->first();
            $data = [
                "account_number"=> $response->account_number,
                "account_name"=> $response->account_name,
                "bank_id"=>null,
            ];

            return response()->json([
                'status'=> 'true',
                'data'=> [
                    'message'=> 'Account resolved successfully',
                    'data'=> $data
                ]
            ],200);
        }else {
            $paysk = getSettings('paystack','secretkeypaystack');
            if ($paysk == "error") {return response()->json(retErrorSetting());}
            $headers = [
                'Content-Type' => 'application/json',
                'authorization' => 'Bearer ' . $paysk
            ];
            $response = Http::withHeaders($headers)->get("https://api.paystack.co/bank/resolve?account_number=". $request['account_number'] ."&bank_code=" . $request['bank_code']);
            $response = json_decode($response->body());
            if ($response->status == 'true') {
                return response()->json([
                    'status'=> 'true',
                    'data'=> [
                        'message'=> 'Account resolved successfully',
                        'data'=> $response->data
                    ]
                ],200);
            }
        }
        return response()->json([
            'status'=> 'false',
            'data'=> [
                'message'=> 'Failed to resolve account',
                $response
            ]
        ],400);
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
        
        $paysk = getSettings('strowallet','publickey');
        
        
        if ($paysk == "error") {return response()->json(retErrorSetting());}
        $headers = [
            'Content-Type' => 'application/json',
            'authorization' => 'Bearer ' . $paysk
        ];
        $response = Http::get('https://strowallet.com/api/banks/get-customer-name', [
            'public_key' => $paysk,
            'bank_code' => $request['bank_code'],
            'account_number' => $request['account_number']
        ]);
        $response = json_decode($response->body());
        if ($response->success == 'true') {
            return response()->json([
                'status'=> 'true',
                'data'=> [
                    'message'=> 'Account resolved successfully',
                    'data'=> $response->data
                ]
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
    
    public function initiateTransferPaystack(Request $request, $ref = null) {
        $recipient_code = $this->createRecipient($request, new PaystackRecipient());
        if ($recipient_code['status'] == "true") {
            $request['recipient_code'] = $recipient_code['data']['data']['recipient_code'];
        }

        $validatedData = Validator::make($request->all(['reason','amount','recipient_code','transaction_pin']), [
            'reason' => ['min:3'],
            'transaction_pin' => ['required','numeric','digits:4', new TransactionPin],
            'amount' => ['required','numeric','min:50'],
            'recipient_code' => ['required','exists:paystack_recipients,recipient_code'],
        ],[
            'amount.required' => "Enter amount to transfer",
            'amount.numeric' => "Amount can only be numeric characters",
            'amount.min' => "Minimum transaction amount is 50 NGN",
            'reason.min' => "Enter at least 3 characters!",
            'account_number.required' => "Transaction amount is required!",
            'account_number.numeric' => "Amount can only contain digits!",
            'recipient_code.required' => "The recipient details is required!",
            'recipient_code.exists' => "The recipient details must already exist!",
            'transaction_pin.required' => 'Transaction pin cannot be empty',
            'transaction_pin.numeric' => 'Only digits allowed for transaction pin',
            'transaction_pin.digits' => 'Transaction pin must have 4 (four) digits',
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
        // $wallet = new WalletController();
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
        $reaso_on = 'Transfer from ' . Auth::user()->first_name. ' '. Auth::user()->surname . isset($request['reason']);
        isset($request['reason']) ? ' - '.$reaso_on .= $request['reason'] : '.';
        $body = [
            "source" => $source, 
            "amount" => (float) ($amt_wo_fee * 100),
            "reference" => $reference,
            "recipient" => $request['recipient_code'], 
            "reason" =>  $reaso_on,
        ];

        $paysk = getSettings('paystack','secretkeypaystack');
        if ($paysk == "error") {return response()->json(retErrorSetting());}
        $headers = [
            'Content-Type' => 'application/json',
            'authorization' => 'Bearer ' . $paysk
        ];
        $response = Http::withHeaders($headers)->post("https://api.paystack.co/transfer", $body);
        $response = json_decode($response->body());
        
        if (!isset($response->status) || $response->status != "true") {return response()->json(retErrorSetting());}

        $user_id = Auth::user()->id;
        // $person = PaystackRecipient::where('recipient_code',$request['recipient_code'])->first(['bank_name','bank_code','account_number','account_name','authorization_code']);
        $rec_data = $recipient_code['data']['data'];
        $data = json_encode([
            'id' => $response->data->id,
            'img' => asset('assets/images/bank/bank.jpg'),
            'status' => $response->data->status,
            'reference' => $response->data->reference,
            'amount' => (float) ($response->data->amount/100 + ($fee)),
            'fee' => (float) ($fee),
            'reason' => $response->data->reason,
            'transfer_code' => $response->data->transfer_code,
            'currency' => $response->data->currency,
            'account_number' => $rec_data['account_number'],
            'bank_name' => $rec_data['bank_name'],
            'bank' => $rec_data['bank_code'],
            'account_name'=>$rec_data['account_name'],
            'recipient' => $rec_data['recipient_code']
        ]);
        if ($response->status == "true") {
            TransactionLog::dispatch($user_id, "Transfer", $request['amount'], $reference, "pending", $request['recipient_code'], $data);
            // Storage::put('trf/data'.Carbon::now()->format('Y-m-d_His'), json_encode($response, JSON_PRETTY_PRINT));
            return response()->json([
                "status"=> "true",
                "data" => [
                    "message" => "Transaction successful",
                    "transaction_id" => $reference
                ]     
            ],200);
        }

        // Storage::put('trf/data'.Carbon::now()->format('Y-m-d_His'), json_encode($response, JSON_PRETTY_PRINT));


        json_decode($wallet->callWalletUpdate($amt_w_fee, 'id', Auth::user()->id));
        TransactionLog::dispatch($user_id, "Transfer", $request['amount'], $reference, "failed", $request['recipient_code'], $data);
        return response()->json([
            "status"=> "false",
            "data"=> [
                "message"=> "Transaction failed",
            ]
        ],400);
    }
    
    public function initiateTransfer(Request $request, $ref = null) {
        
        
            $validatedData = Validator::make($request->all(['reason','amount','recipient_code','transaction_pin']), [
                
                'transaction_pin' => ['required','numeric','digits:4', new TransactionPin],
                'amount' => ['required','numeric','min:50'],
                'is_beneficiary' => ['boolean']
                // 'bank_name' => ['required'],
                // 'account_number' => ['required', 'numeric', 'digits:10'], 
                // 'bank_code' => ['required'],
                // 'name_enquiry_reference' => ['required'] 
            ],[
                'transaction_pin.required' => 'Transaction pin cannot be empty',
                'transaction_pin.numeric' => 'Only digits allowed for transaction pin',
                'transaction_pin.digits' => 'Transaction pin must have 4 (four) digits',
                'transaction_pin.digits' => '"Account number must have 10 (digits) digits',
                
                'amount.required' => "Enter amount to transfer",
                'amount.numeric' => "Amount can only be numeric characters",
                'amount.min' => "Minimum transaction amount is 50 NGN",
                'is_beneficiary.boolean' => 'Beneficiary status must be true or false',
                
                // 'bank_name.required' => 'Bank Name Required',
                
                // 'account_number.required' => "Account number is required!",
                // 'account_number.numeric' => "Account number can only contain digits!", 
                 
                // 'bank_code.required' => "Select Bank",
                // 'name_enquiry_reference.required' => "Account Number Not validated",
                
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
            $strowallet = getSettings('strowallet','publickey');
            if ($strowallet == "error") {return response()->json(retErrorSetting());}
            
            $body = [
                "public_key" => $strowallet,
                "account_number" => $request['account_number'],
                "bank_code" => $request['bank_code'],
                "amount" => $request['amount'],
                "reference" => $reference,
                "name_enquiry_reference" => $request['name_enquiry_reference'], 
                "narration" =>  $reaso_on,
            ];
    
        try {
            
            $response = Http::post('https://strowallet.com/api/banks/request', $body);
            $response = json_decode($response->body());
            
            dd($response);
            
            if (!isset($response->success) || $response->success != "true") {
                $dedRes = json_decode($wallet->callWalletUpdate($amt_w_fee, 'id', Auth::user()->id, '+'));
                return response()->json(retErrorSetting());
            }
        
            $user_id = Auth::user()->id;
            
            $data = json_encode([
                'id' => $response->response->sessionId,
                'img' => asset('assets/images/bank/bank.jpg'),
                'status' => "Success",
                'reference' => $response->response->paymentReference,
                'amount' => (float) ($request['amount'] + ($fee)),
                'fee' => (float) ($fee),
                'reason' => $response->response->narration,
                'transfer_code' => $response->response->sessionId,
                'account_number' => $response->response->creditAccountNumber,
                'provider' => $response->response->provider,
                'bank'  =>  $request['bank_name'],
                'account_name'=>$response->response->creditAccountName,
                'recipient' => $response->response->creditAccountName,
                'message'   => $response->response->message,
            ]);
            if ($response->success == "true") {
                $savedData = [
                    "account_number" => $request['account_number'],
                    "bank_code" => $request['bank_code'],
                    "name_enquiry_reference" => $request['name_enquiry_reference'], 
                    'bank_name'  =>  $request['bank_name'],
                ];
                
                if (isset($is_beneficiary) && ($is_beneficiary == 1 || $is_beneficiary === true)) {
                    Helpers::addBeneficiary('transfer', $savedData);
                }
                TransactionLog::dispatch($user_id, "Transfer", $request['amount'], $response->response->paymentReference, "Success", $request['name_enquiry_reference'], $data);
                return response()->json([
                    "status"=> "true",
                    "data" => [
                        "message" => "Transaction successful",
                        "transaction_id" => $response->response->paymentReference
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

    
}
