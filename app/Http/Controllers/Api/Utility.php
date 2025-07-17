<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Helpers;
use App\Models\User;
use App\Mail\TokenMail;
use App\Models\AirtimeToCash;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use App\Jobs\TransactionLog;
use App\Notifications\TransactionNotification;
use App\Models\AdminSetting;
use Illuminate\Http\Request;
use App\Rules\TransactionPin;
use function App\getSettings;
use function App\retErrorSetting;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Services\FirebaseService;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\WalletController;
use App\Models\TransactionLog as ModelsTransactionLog;

class Utility extends Controller
{

    public $serviceList = [];
    public $cableList = [];
    public $electricityList = [];
    public $betsites = [];

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
        
        $this->serviceList = [
            ['name' => 'Mtn', 'img' => asset('assets/images/vtu/mtn.jpg'), 'airtime' => "mtn", 'data' => "mtn-data"],
            ['name' => '9mobile', 'img' => asset('assets/images/vtu/etisalat.jpg'), 'airtime' => "etisalat", 'data' => "etisalat-data"],
            ['name' => 'Airtel', 'img' => asset('assets/images/vtu/airtel.jpg'), 'airtime' => "airtel", 'data' => "airtel-data"],
            ['name' => 'Glo', 'img' => asset('assets/images/vtu/glo.jpg'), 'airtime' => "glo", 'data' => "glo-data"]
        ];
        
        $this->dataAirtimeList = [
            ['name' => 'Mtn', 'img' => asset('assets/images/vtu/mtn.jpg'), 'airtime' => "mtn", 'data' => "mtn-data"],
            ['name' => '9mobile', 'img' => asset('assets/images/vtu/etisalat.jpg'), 'airtime' => "etisalat", 'data' => "etisalat-data"],
            ['name' => 'Airtel', 'img' => asset('assets/images/vtu/airtel.jpg'), 'airtime' => "airtel", 'data' => "airtel-data"],
            ['name' => 'Glo', 'img' => asset('assets/images/vtu/glo.jpg'), 'airtime' => "glo", 'data' => "glo-data"],
            ['name' => '9mobile SME', 'img' => asset('assets/images/vtu/etisalat.jpg'), 'data' => "9mobile-sme-data"],
            // ['name' => 'Glo SME', 'img' => asset('assets/images/vtu/glo.jpg'), 'data' => "glo-sme-data"],
            ['name' => 'Spectranet', 'img' => asset('assets/images/vtu/spectranet.jpeg'), 'data' => "spectranet"],
            ['name' => 'Smile', 'img' => asset('assets/images/vtu/smile.jpeg'), 'data' => "smile-direct"]
            
        ];
        
        $this->cableList = [
            ['name' => 'DSTV', 'img' => asset('assets/images/cable/dstv.jpg'), 'serviceID' => "dstv", 'convenience_fee' => 0],
            ['name' => 'GOTV Payment', 'img' => asset('assets/images/cable/gotv.jpg'), 'serviceID' => "gotv", 'convenience_fee' => 0],
            ['name' => 'StarTimes Subscription', 'img' => asset('assets/images/cable/startimes.jpg'), 'serviceID' => "startimes", 'convenience_fee' => 0],
            ['name' => 'ShowMax', 'img' => asset('assets/images/cable/showmax.jpg'), 'serviceID' => "showmax", 'convenience_fee' => 0]
        ];
        
        $this->electricityList = [
            ['name' => 'Ikeja Electric - IKEDC', 'img' => asset('assets/images/electricity/ikeja.png'), 'serviceID' => "ikeja-electric", 'convenience_fee' => 0],
            ['name' => 'Eko Electric - EKEDC', 'img' => asset('assets/images/electricity/ekedc.jpg'), 'serviceID' => "eko-electric", 'convenience_fee' => 0],
            ['name' => 'Kano Electric - KEDCO', 'img' => asset('assets/images/electricity/kano.png'), 'serviceID' => "kano-electric", 'convenience_fee' => 0],
            ['name' => 'Port Harcourt Electric - PHED', 'img' => asset('assets/images/electricity/ph.jpeg'), 'serviceID' => "portharcourt-electric", 'convenience_fee' => 0],
            ['name' => 'Jos Electric - JED', 'img' => asset('assets/images/electricity/jos.jpeg'), 'serviceID' => "jos-electric", 'convenience_fee' => 0],
            ['name' => 'Ibadan Electric - IBEDC', 'img' => asset('assets/images/electricity/ibd.png'), 'serviceID' => "ibadan-electric", 'convenience_fee' => 0],
            ['name' => 'Kaduna Electric - KAEDCO', 'img' => asset('assets/images/electricity/kaduna.jpeg'), 'serviceID' => "kaduna-electric", 'convenience_fee' => 0],
            ['name' => 'Abuja Electric - AEDC', 'img' => asset('assets/images/electricity/abuja.jpeg'), 'serviceID' => "abuja-electric", 'convenience_fee' => 0],
            ['name' => 'Enugu Electric - EEDC', 'img' => asset('assets/images/electricity/enugu.png'), 'serviceID' => "enugu-electric", 'convenience_fee' => 0],
            ['name' => 'Benin Electric - BEDC', 'img' => asset('assets/images/electricity/benin.jpeg'), 'serviceID' => "benin-electric", 'convenience_fee' => 0],
        ];
        
        $this->betsites = [
            ['id' => 1, 'name' => 'Betnaija', 'img' => asset('assets/images/bet/betnaija.jpeg')],
            ['id' => 2, 'name' => 'Sportybet', 'img' => asset('assets/images/bet/sportybet.png')],
            ['id' => 3, 'name' => 'Nairabet', 'img' => asset('assets/images/bet/nairabet.jpg')],
            ['id' => 4, 'name' => 'Betking', 'img' => asset('assets/images/bet/betking.jpeg')],
            ['id' => 5, 'name' => 'Betway', 'img' => asset('assets/images/bet/betway.png')],
            ['id' => 6, 'name' => 'Betlion', 'img' => asset('assets/images/bet/betlion.jpg')],
            ['id' => 7, 'name' => 'Cloudbet', 'img' => asset('assets/images/bet/cloudbet.jpg')],
            ['id' => 8, 'name' => 'Livescorebet', 'img' => asset('assets/images/bet/livescorebet.jpg')],
            ['id' => 9, 'name' => 'Merrybet', 'img' => asset('assets/images/bet/merrybet.jpeg')],
            ['id' => 10, 'name' => 'Supabet', 'img' => asset('assets/images/bet/supabet.jpg')],
            ['id' => 11, 'name' => 'Betland', 'img' => asset('assets/images/bet/betland.jpg')],
            ['id' => 12, 'name' => 'Bangbet', 'img' => asset('assets/images/bet/bangbet.jpg')],
            ['id' => 14, 'name' => 'NaijaBet', 'img' => asset('assets/images/bet/naijabet.jpeg')],
        ];
        
        $this->waecServices = [
            ['name' => 'WAEC Result Checker', 'serviceID' => "waec", 'convenience_fee' => 0],
            ['name' => 'WAEC Registration', 'serviceID' => "waec-registration", 'convenience_fee' => 0],
        ];
    }
    
    
    public function getNetworkList()
    {
        $networks = AirtimeToCash::select('id', 'network_name', 'image')
        ->where('is_enabled', true)
        ->get();

        return response()->json([
            'success' => true,
            'data' => $networks,
        ]);
    }

    /**
     * Get all settings for a specific network by its ID.
     */
    public function getNetworkSettings($id)
    {
        $network = AirtimeToCash::find($id);

        if (!$network) {
            return response()->json([
                'success' => false,
                'message' => 'Network not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $network,
        ]);
    }
    
    
    public function processAirtimeToCash(Request $request)
        {
            $rules = [
            'amount' => 'required|numeric|min:1',
            'network_id' => 'required|exists:airtime_to_cashes,id',
            'sender_number' => 'required|numeric',
        ];
    
        $messages = [
            'amount.required' => 'The amount is required.',
            'amount.numeric' => 'The amount must be a valid number.',
            'amount.min' => 'The amount must be at least 1.',
            'network_id.required' => 'The network ID is required.',
            'network_id.exists' => 'The selected network does not exist.',
            'sender_number.required' => 'The sender number is required.',
            'sender_number.numeric' => 'The sender number must be numeric.',
        ];
    
        // Perform validation
        $validator = Validator::make($request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            // Return custom error response
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed. Please fix the errors and try again.'
            ], 422); // HTTP status code 422: Unprocessable Entity
        }
    
        // Proceed with your logic if validation passes
        $validated = $validator->validated();


        // Retrieve network details
        $network = AirtimeToCash::find($validated['network_id']);

        if (!$network->is_enabled) {
            return response()->json([
                'success' => false,
                'message' => 'This network is currently disabled.',
            ], 400);
        }

        $amount = $validated['amount'];

        // Check minimum and maximum airtime
        if ($amount < $network->minimum_airtime || $amount > $network->maximum_airtime) {
            return response()->json([
                'success' => false,
                'message' => "Amount must be between {$network->minimum_airtime} and {$network->maximum_airtime}.",
            ], 400);
        }

        // Calculate the payable amount
        $payableAmount = $amount * ($network->payment_percentage / 100);
        $requestId  = Str::random(20);

        $data = [
            'amount' => $amount,
            'payable_amount' => $payableAmount,
            'network' => $network->network_name,
            'receiver_number' => $network->receiver_number,
            'img' => asset($network->image),
            'request_id' => $requestId
        ];
        
        TransactionLog::dispatch(Auth::user()->id,'ATC',$payableAmount,$requestId,'Pending',$request['sender_number'],json_encode($data));
        
        return response()->json([
            'status'=>'true',
            'data' => ['message' => "Airtime to Cash processed, you will be credited once confirmed.",'transaction_id' => $requestId]
        ],200);
        
    }

    
    
    // New End point logics
   
    public function getAirtimeList() {
        // $arr = $this->serviceList;
        $airtimeList = array_filter($this->dataAirtimeList, function($service) {
            return isset($service['airtime']);
        });

        return response()->json([
            'status'=>'true',
            'data' => [
                'message' => "Airtime list retrieved successfully",
                'data' => array_values($airtimeList)
            ]
        ],200);
    }

    public function getDataList() {
        $arr = $this->dataAirtimeList;
        
        return response()->json([
            'status'=>'true',
            'data' => [
                'message' => "Data list retrieved successfully",
                'data' => array_values($arr)
            ]
        ],200);
    }
    
    public function verifySmileEmail(Request $request) {
        $validatedData = Validator::make($request->all(['serviceID','billersCode']), [
            'billersCode' => ['required','email'],
            'serviceID' => ['required']

        ],[
            
            'billersCode.required' => 'Smile required',
            'billersCode.numeric' => 'Smile must be a valide email address',
            'serviceID.required' => 'Service ID is required'
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
        // $result = collect($this->electricityList)->pluck('serviceID')->contains($request['serviceID']);
        // if ($result) {
            $vtapi = getSettings('vtpass','apikeyvtpass');
            $vtsecret = getSettings('vtpass','secretkeyvtpass');
            if ($vtapi == "error" || $vtsecret == "error") {return response()->json(retErrorSetting());}
            $requestParams = Http::withHeaders([
                'api-key' => $vtapi,
                'secret-key' => $vtsecret,
            ])->post('https://vtpass.com/api/merchant-verify/smile/email',[
                'serviceID' => $request['serviceID'],
                'billersCode' => $request['billersCode'],
            ]);

            $response = json_decode($requestParams->body());
            if ($response->code == '000' && !isset($response->content->error)) {
                return response()->json([
                    'status'=>'true',
                    'data' => [
                        'message' => "Verification successful",
                        'data' => $response->content
                    ]
                ],200);
            }
            return response()->json([
                'status'=>'false',
                'data' => [
                    'message' => "Verification failed",
                    'error' => $response->content->error
                ]
            ],400);
        // }

        return response()->json([
            'status'=>'false',
            'data' => [
                'message' => "Validation failed",
                'error' => ['serviceID' => 'Invalid service ID']
            ]
        ],400);
    }
    
    public function internationalCountries(Request $request) {
        
        $vtapi = getSettings('vtpass','apikeyvtpass');
        $vtsecret = getSettings('vtpass','secretkeyvtpass');
        if ($vtapi == "error" || $vtsecret == "error") {return response()->json(retErrorSetting());}
        $requestParams = Http::withHeaders([
            'api-key' => $vtapi,
            'secret-key' => $vtsecret,
        ])->get('https://vtpass.com/api/get-international-airtime-countries');

        $response = json_decode($requestParams->body());
        if ($response->response_description == '000' && !isset($response->content->error)) {
            return response()->json([
                'status'=>'true',
                'data' => [
                    'message' => "International Countries Retrieved",
                    'data' => $response->content
                ]
            ],200);
        }
        return response()->json([
            'status'=>'false',
            'data' => [
                'message' => "Retrieving International Countries Failed",
                'error' => $response->content->error
            ]
        ],400);

       
    }
    
    public function productType($e) {
        
        $vtapi = getSettings('vtpass','apikeyvtpass');
        $vtsecret = getSettings('vtpass','secretkeyvtpass');
        
        if ($vtapi == "error" || $vtsecret == "error") {return response()->json(retErrorSetting());}
        $requestParams = Http::withHeaders([
            'api-key' => $vtapi,
            'secret-key' => $vtsecret,
        ])->get('https://vtpass.com/api/get-international-airtime-product-types?code='.$e);
        
        $response = json_decode($requestParams->body());
        if ($response->response_description == '000' && !isset($response->content->error)) {
            
        return response()->json([
                'status'=>'true',
                'data' => [
                    'message' => "International Product Types Retrieved",
                    'data' => $response->content
                ]
            ],200);
        }
        return response()->json([
            'status'=>'false',
            'data' => [
                'message' => "Retrieving International Product Types Failed",
                'error' => $response->content->error
            ]
        ],400);
    }
    
    public function internationalAirtimeOperator($code, $e) {
        
        $vtapi = getSettings('vtpass','apikeyvtpass');
        $vtsecret = getSettings('vtpass','secretkeyvtpass');
        
        if ($vtapi == "error" || $vtsecret == "error") {return response()->json(retErrorSetting());}
        $requestParams = Http::withHeaders([
            'api-key' => $vtapi,
            'secret-key' => $vtsecret,
        ])->get('https://vtpass.com/api/get-international-airtime-operators?code='.$code.'&product_type_id='.$e);
        
        $response = json_decode($requestParams->body());
        if ($response->response_description == '000' && !isset($response->content->error)) {
            
        return response()->json([
                'status'=>'true',
                'data' => [
                    'message' => "International Operator Types Retrieved",
                    'data' => $response->content
                ]
            ],200);
        }
        return response()->json([
            'status'=>'false',
            'data' => [
                'message' => "Retrieving International Operator Types Failed",
                'error' => $response->content->error
            ]
        ],400);
    }
    
    public function internationalAirtimeVariationCode($operatorID, $productID) {
        
        $vtapi = getSettings('vtpass','apikeyvtpass');
        $vtsecret = getSettings('vtpass','secretkeyvtpass');
        
        if ($vtapi == "error" || $vtsecret == "error") {return response()->json(retErrorSetting());}
        $requestParams = Http::withHeaders([
            'api-key' => $vtapi,
            'secret-key' => $vtsecret,
        ])->get("https://vtpass.com/api/service-variations?serviceID=foreign-airtime&operator_id=$operatorID&product_type_id=$productID");
        
        $response = json_decode($requestParams->body());
        if($response == null){
            return response()->json([
                'status'=>'false',
                'data' => [
                    'message' => "Retrieving International Operator Types Failed",
                    'error' => "Variation code not available for the selected operator and product"
                ]
            ],400);
        }
        if ($response->response_description == '000' && !isset($response->content->error)) {
            
        return response()->json([
                'status'=>'true',
                'data' => [
                    'message' => "International Operator Types Retrieved",
                    'data' => $response->content
                ]
            ],200);
        }
        return response()->json([
            'status'=>'false',
            'data' => [
                'message' => "Retrieving International Operator Types Failed",
                'error' => $response->content->error
            ]
        ],400);
    }
    
    public function purchaseInternationalAirtime(Request $request) {
        $validatedData = Validator::make($request->all(['transaction_pin', 'billersCode', 'variation_code', 'amount', 'phone', 'operator_id', 'country_code', 'product_type_id']), [
            'transaction_pin' => ['required','numeric','digits:4', new TransactionPin],
            'billersCode' => ['required'],
            'variation_code' => ['required'],
            'amount' => ['required','numeric','min:50'],
            'phone' => ['required','numeric','digits:11'],
            'operator_id' => ['required'],
            'country_code' => ['required'],
            'product_type_id' => ['required'],
        ],[
            'transaction_pin.required' => 'Transaction pin cannot be empty',
            'transaction_pin.numeric' => 'Only digits allowed for transaction pin',
            'transaction_pin.digits' => 'Transaction pin must have 4 (four) digits',
            'billersCode.required' => 'Field is required',
            'variation_code.required' => 'Field is required',
            'amount.required' => 'Airtime amount is required',
            'amount.numeric' => 'Amount must be numeric',
            'amount.min' => 'Amount must be at least N50',
            'phone.required' => 'Phone number is required',
            'phone.numeric' => 'Phone number accepts numbers only',
            'phone.digits' => 'Phone number can be only 11 digits',
            'operator_id.required' => 'Field is required',
            'country_code.required' => 'Field is required',
            'product_type_id.required' => 'Field is required',
            
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
        // $result = collect($this->serviceList)->pluck('airtime')->contains($request['serviceID']);
        
            $status = 'pending';
            // Set the timezone to Africa/Lagos
            $now = Carbon::now('Africa/Lagos');
            // Format the date and time as "YYYYMMDDHHII"
            $requestId = $now->format('YmdHi');
            // Add any additional alphanumeric string as desired
            $requestId = $requestId . Str::random(5);

            //Deduct amount
            $wallet = new WalletController();
            $dedRes = json_decode($wallet->callWalletUpdate($request['amount'], 'id', Auth::user()->id, '-'));
            if ($dedRes->code !== 1) {
                return response()->json([
                    'status' => 'false',
                    'data'=> [
                        'message' => 'Payment failed',
                        'error'=> $dedRes->msg
                    ]
                ],400);
            }

            $vtapi = getSettings('vtpass','apikeyvtpass');
            $vtsecret = getSettings('vtpass','secretkeyvtpass');
            if ($vtapi == "error" || $vtsecret == "error") {return response()->json(retErrorSetting());}

            $requestParams = Http::withHeaders([
                'api-key' => $vtapi,
                'secret-key' => $vtsecret,
            ])->post('https://vtpass.com/api/pay', [
                'request_id' => $requestId,
                'serviceID' => 'foreign-airtime',
                'billersCode' => $request['billersCode'],
                'variation_code' => $request['variation_code'],
                'amount' => $request['amount'],
                'phone' => $request['phone'],
                'operator_id' => $request['operator_id'],
                'country_code' => $request['country_code'],
                'product_type_id' => $request['product_type_id'],
                'email' => Auth::user()->email
                
            ]);
            
            $response = json_decode($requestParams->body());
            
            if($response->code != 000){
                $dedRes = json_decode($wallet->callWalletUpdate($request['amount'], 'id', Auth::user()->id, '+'));
                return response()->json([
                    'status' => 'false',
                    'data'=> [
                        'message' => 'Payment failed',
                        'error'=> $response
                    ]
                ],400);
               
            }
            $data = [
                'service_name' => $response->content->transactions->product_name,
                'type' => $response->content->transactions->type,
                'phone' => $request['phone'],
                'reference' => $requestId,
                'img' => 'https://vtpass.com/resources/images/flags/CM.png'
            ];

            if ($response->content->transactions->status == "delivered") {
                $confirmParams = Http::withHeaders([
                    'api-key' => $vtapi,
                    'secret-key' => $vtsecret,
                ])->post('https://vtpass.com/api/requery', [
                    'request_id' => $requestId
                ]);

                $confirmResponse = json_decode($confirmParams->body());
                if ($confirmResponse->content->transactions->status == "delivered") {
                    $status = 'successful';
                }elseif ($confirmResponse->content->transactions->status == "pending" || $response->content->transactions->status == "initiated") {
                    $status = 'pending';
                }else {
                    $status = 'failed';
                }

                $reFront = response()->json([
                    'status'=>'true',
                    'data' => ['message' => "Airtime purchase successful",'transaction_id' => $requestId]
                ],200);

                if ($response->content->transactions->status == "delivered") {
                    $status = 'successful';
                }elseif ($response->content->transactions->status == "pending" || $response->content->transactions->status == "initiated") {
                    $status = 'pending';
                }else {
                    $status = 'failed';
                    $wallet = new WalletController();
                    $wallet->callWalletUpdate($request['amount'], 'id', Auth::user()->id, '+');
                    $reFront = response()->json(['status'=>'false','data' => ['message' => "Airtime purchase failed"]],400);
                }
            }
            
            TransactionLog::dispatch(Auth::user()->id,'Airtime',$request['amount'],$requestId,$status,$request['phone'],json_encode($data));
            return $reFront;
        
        return response()->json([
            'status'=>'false',
            'data' => [
                'message' => "Validation failed",
                'error' => ['serviceID' => 'Invalid service ID']
            ]
        ],400);
    }
    
    // International Airtime for logics ends here
    
    // Betting Platforms API
    public function bettingPlatform() {
        $arr = $this->betsites;

        return response()->json([
            'status'=>'true',
            'data' => [
                'message' => "Betting sites list retrieved successfully",
                'data' => $arr
            ]
        ],200);
    }
    
    public function verifyBettingID(Request $request) {
        $validatedData = Validator::make($request->all(['betsite_id','betting_number']), [
            'betting_number' => ['required','numeric'],
            'betsite_id' => ['required']

        ],[
            
            'betting_number.required' => 'Betting number required',
            'betting_number.numeric' => 'Betting number accepts numbers only',
            'betsite_id.required' => 'Bet site is required'
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
        
        $betsite_id = $request['betsite_id'];
        $betting_number = $request['betting_number'];
        
        $ncpin = getSettings('ncwallet','tran_pin');
        $ncsecret = getSettings('ncwallet','secretkey');
        if ($ncpin == "error" || $ncsecret == "error") {return response()->json(retErrorSetting());}
        $requestParams = Http::withHeaders([
            'trnx_pin' => $ncpin,
            'Authorization' => $ncsecret,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->GET("https://ncwallet.africa/api/v1/betting/validation?betsite_id=$betsite_id&betting_number=$betting_number");

        $response = json_decode($requestParams->body());
        // dd($response);
        if ($response->status == 'success' ) {
            return response()->json([
                'status'=>'true',
                'data' => [
                    'message' => "Verification successful",
                    'data' => $response->data
                ]
            ],200);
        }
        return response()->json([
            'status'=>'false',
            'data' => [
                'message' => "Could not validate customer Id, provide valid customer Id/ number",
                'error' => $response
            ]
        ],400);

        return response()->json([
            'status'=>'false',
            'data' => [
                'message' => "Validation failed",
                'error' => ['serviceID' => 'Invalid service ID']
            ]
        ],400);
    }
    
    public function fundBettingAccount(Request $request){
        $validatedData = Validator::make($request->all(['betsite_id','betting_number', 'amount', 'transaction_pin']), [
            'betting_number' => ['required','numeric'],
            'betsite_id' => ['required'],
            'amount' => ['required','numeric','min:100'],
            'transaction_pin' => ['required','numeric','digits:4', new TransactionPin],
            'is_beneficiary' => ['boolean']
        ],[
            
            'amount.required' => 'Amount is required',
            'amount.numeric' => 'Amount must be numeric',
            'amount.min' => 'Amount must be at least N100',
            'transaction_pin.required' => 'Transaction pin cannot be empty',
            'transaction_pin.numeric' => 'Only digits allowed for transaction pin',
            'transaction_pin.digits' => 'Transaction pin must have 4 (four) digits',
            'betting_number.required' => 'Betting number required',
            'betting_number.numeric' => 'Betting number accepts numbers only',
            'betsite_id.required' => 'Bet site is required',
            // 'is_beneficiary.required' => 'is_beneficiary is required',
            'is_beneficiary.boolean' => 'Beneficiary status must be true or false',
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
        
        $betsite_id = $request['betsite_id'];
        $betting_number = $request['betting_number'];
        $amount = $request['amount'];
        $now = Carbon::now('Africa/Lagos');
        $requestId = $now->format('YmdHi');
        $requestId = 'BET'. $requestId . Str::random(10);
        $is_beneficiary = isset($request['is_beneficiary']) ? $request['is_beneficiary'] : null;
        
        $wallet = new WalletController();
        $dedRes = json_decode($wallet->callWalletUpdate($amount, 'id', Auth::user()->id, '-'));
        if ($dedRes->code !== 1) {
            return response()->json([
                'status' => 'false',
                'data'=> [
                    'message' => 'Payment failed',
                    'error'=> $dedRes->msg
                ]
            ],400);
        }
        
        $ncpin = getSettings('ncwallet','tran_pin');
        $ncsecret = getSettings('ncwallet','secretkey');
        $body = [
            'betsite_id' => $betsite_id,
            'amount' => $amount,
            'betting_number' => $betting_number,
            'bypass' => true,
            'ref_id' => $requestId,
        ];
        if ($ncpin == "error" || $ncsecret == "error") {return response()->json(retErrorSetting());}
        $requestParams = Http::withHeaders([
            'trnx_pin' => $ncpin,
            'Authorization' => $ncsecret,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post("https://ncwallet.africa/api/v1/betting", $body);
        
        

        $response = json_decode($requestParams->body());
        
        
        $reFront = response()->json([
            'status'=>'true',
            'data' => ['message' => "Betting Wallet Successfully Funded",'transaction_id' => $requestId]
        ],200);
        
        
        
        if ($response->status == 'success' ) {
            $data = $response->data;
            $betlogo = collect($this->betsites)->firstWhere('id', $betsite_id)['img'] ?? null;
            $data->img = $betlogo; 
            
            
            unset($data->oldbal, $data->newbal);
            
            if (isset($is_beneficiary) && ($is_beneficiary == 1 || $is_beneficiary == true)) {
                Helpers::addBeneficiary('betting', json_encode($data), $data->customer_name, $data->betting_number, $data->betsite_company);
            }
            TransactionLog::dispatch(Auth::user()->id,'Betting',$amount,$requestId,'successful',$betting_number, json_encode($data));
            return $reFront;
        }
        
        $dedRes = json_decode($wallet->callWalletUpdate($amount, 'id', Auth::user()->id, '+'));
        
        unset($response->oldbal, $response->newbal);
        return response()->json([
            'status'=>'false',
            'data' => [
                'message' => "Could not fund betting wallet",
                'error' => $response
            ]
        ],400);
        
    }
    
    // Betting Platform API Ends here

    public function getTvSubscriptionList() {
        $arr = $this->cableList;

        return response()->json([
            'status'=>'true',
            'data' => [
                'message' => "Cable TV list retrieved successfully",
                'data' => $arr
            ]
        ],200);
    }
    
    public function purchaseAirtime(Request $request) {
        $validatedData = Validator::make($request->all(['transaction_pin','amount','serviceID','phone','package', 'is_beneficiary']), [
            'amount' => ['required','numeric','min:50'],
            'transaction_pin' => ['required','numeric','digits:4', new TransactionPin],
            'serviceID' => ['required'],
            'phone' => ['required','numeric','digits:11'],
            'package' => ['required'],
            // 'is_beneficiary' => ['boolean']
        ],[
            // 'is_beneficiary.boolean' => 'Beneficiary status must be true or false',
            'amount.required' => 'Airtime amount is required',
            'amount.numeric' => 'Amount must be numeric',
            'amount.min' => 'Amount must be at least N50',
            'transaction_pin.required' => 'Transaction pin cannot be empty',
            'transaction_pin.numeric' => 'Only digits allowed for transaction pin',
            'transaction_pin.digits' => 'Transaction pin must have 4 (four) digits',
            'serviceID.required' => 'Field is required',
            'package.required' => 'Field is required',
            'phone.required' => 'Phone number is required',
            'phone.numeric' => 'Phone number accepts numbers only',
            'phone.digits' => 'Phone number can be only 11 digits',
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
        $result = collect($this->serviceList)->pluck('airtime')->contains($request['serviceID']);
        if ($result) {
            $status = 'pending';
            // Set the timezone to Africa/Lagos
            $now = Carbon::now('Africa/Lagos');
            // Format the date and time as "YYYYMMDDHHII"
            $requestId = $now->format('YmdHi');
            // Add any additional alphanumeric string as desired
            $requestId = $requestId . Str::random(5);

            //Deduct amount
            $wallet = new WalletController();
            $dedRes = json_decode($wallet->callWalletUpdate($request['amount'], 'id', Auth::user()->id, '-'));
            // dd($dedRes);
            if ($dedRes->code !== 1) {
                return response()->json([
                    'status' => 'false',
                    'data'=> [
                        'message' => 'Payment failed',
                        'error'=> $dedRes->msg
                    ]
                ],400);
            }

            $vtapi = getSettings('vtpass','apikeyvtpass');
            $vtsecret = getSettings('vtpass','secretkeyvtpass');
            if ($vtapi == "error" || $vtsecret == "error") {return response()->json(retErrorSetting());}

            $requestParams = Http::withHeaders([
                'api-key' => $vtapi,
                'secret-key' => $vtsecret,
            ])->post('https://vtpass.com/api/pay', [
                'serviceID' => $request['serviceID'],
                'amount' => $request['amount'],
                'phone' => $request['phone'],
                'request_id' => $requestId
            ]);
            
            $response = json_decode($requestParams->body());
            
            $user = Auth::user();
            $deviceToken = $user->device_token;
            
            if($response->code != 000){
                $dedRes = json_decode($wallet->callWalletUpdate($request['amount'], 'id', Auth::user()->id, '+'));
                return response()->json([
                    'status' => 'false',
                    'data'=> [
                        'message' => 'Payment failed',
                        'error'=> 'Network not availble, pls try again',
                        'res' => $response
                    ]
                ],400);
               
            }
            
            $currentBalance = $user->balance;
            
            $balanceBefore = $currentBalance;
            $balanceAfter = $currentBalance - $request['amount'];
            $data = [
                'service_name' => $response->content->transactions->product_name,
                'type' => $response->content->transactions->type,
                'phone' => $request['phone'],
                'package' => $request['package'],
                'reference' => $requestId,
                'img' => collect($this->serviceList)->where('airtime', $request['serviceID'])->pluck('img')->first(),
                'balance_before'    => $balanceBefore,
                'balance_after'     => $balanceAfter,
            ];

            $is_beneficiary = isset($request['is_beneficiary']) ? $request['is_beneficiary'] : null;
            
            if (isset($is_beneficiary) && ($is_beneficiary == 1 || $is_beneficiary == true)) {
                Helpers::addBeneficiary('airtime', json_encode($data), '', $request['phone'], $response->content->transactions->product_name );
            }

                $reFront = response()->json([
                    'status'=>'true',
                    'data' => ['message' => "Airtime purchase successful",'transaction_id' => $requestId]
                ],200);

                if ($response->content->transactions->status == "delivered") {
                    $status = 'successful';
                }elseif ($response->content->transactions->status == "pending" || $response->content->transactions->status == "initiated") {
                    $status = 'pending';
                }else {
                    $status = 'failed';
                    $wallet = new WalletController();
                    $wallet->callWalletUpdate($request['amount'], 'id', Auth::user()->id, '+');
                    $reFront = response()->json(['status'=>'false','data' => ['message' => "Airtime purchase failed"]],400);
                }
            // }
            
            if(!is_null($deviceToken)){
                $title = "Airtime Purchase";
                $body = "Transaction $status";
                $this->firebaseService->sendNotification($title, $body, $deviceToken );
            }
            $phone = $request['phone'];
            $user->notify(new TransactionNotification(
                    "Airtime Successful",
                    "$phone Successfully recharged",
                    null, // Optional icon
                    null, // Optional URL
                    ['transaction_id' => $requestId]
                ));
            
            TransactionLog::dispatch(Auth::user()->id,'Airtime',$request['amount'],$requestId,$status,$request['phone'],json_encode($data));
            return $reFront;
        }

        return response()->json([
            'status'=>'false',
            'data' => [
                'message' => "Validation failed",
                'error' => ['serviceID' => 'Invalid service ID']
            ]
        ],400);
    }

    public function confirmVTUStatus(Request $request, ModelsTransactionLog $transactionLog) {
        $request = Purify::clean($request->all());
        $response = $request;

        if ($response['type'] != "transaction-update") {return response()->json(['response'=>'error']);}

        $requestId = $response['data']['requestId'];
        // return $requestId;
        $trans = $transactionLog->where('transaction_id', $requestId)->first(['user_id','data']);
        $user_id = $trans->user_id;
        $type = $response['data']['content']['transactions']['type'];
        // if (str_contains($response['data']['content']['transactions']['type'], 'Data')) {
        //     $type = 'Data';
        // }elseif (str_contains($response['data']['content']['transactions']['type'], 'Electricity')) {
        //     $type = 'Electricity';
        // }elseif (str_contains($response['data']['content']['transactions']['type'], 'TV')) {
        //     $type = 'Cable TV';
        // }
        // return $type;
        if ($response['data']['content']['transactions']['status'] == "delivered") {
            TransactionLog::dispatch($user_id,null,null,$requestId,'successful',null,null);
            Storage::put('vtu/success-'.Carbon::now()->format('Y-m-d_His'), json_encode($request, JSON_PRETTY_PRINT));
            return response()->json(['response'=>'success']);

        }elseif ($response['data']['content']['transactions']['status'] == "reversed") {
            $amount = $response['data']['content']['transactions']['amount'];
            if (str_contains($type,'TV') || str_contains($type,'Electricity')) {
                $prevTrans = json_decode($trans->data);
                $amount = $amount + $prevTrans->convenience_fee;
                Storage::put('vtu/withconv_fee-'.Carbon::now()->format('Y-m-d_His'), json_encode($request, JSON_PRETTY_PRINT));

            }


            $wallet = new WalletController();
            $dedRes = json_decode($wallet->callWalletUpdate($amount, 'id', $user_id));   
            if ($dedRes) {
                TransactionLog::dispatch($user_id,null,null,$requestId,'reversed',null,null);
                Storage::put('vtu/reversed-'.Carbon::now()->format('Y-m-d_His'), json_encode($request, JSON_PRETTY_PRINT));
                return response()->json(['response'=>'success']);
            }

        }
        return response()->json(['response'=>'error']);
    }
    
    public function dataVariationList($e) {
        $result = collect($this->dataAirtimeList)->pluck('data')->contains($e);

        if ($result) {
            $vtapi = getSettings('vtpass','apikeyvtpass');
            $vtsecret = getSettings('vtpass','secretkeyvtpass');
            if ($vtapi == "error" || $vtsecret == "error") {return response()->json(retErrorSetting());}
            $requestParams = Http::withHeaders([
                'api-key' => $vtapi,
                'secret-key' => $vtsecret,
            ])->get('https://vtpass.com/api/service-variations?serviceID='.$e);
            
            $response = json_decode($requestParams->body())->content;
            
            if(isset($response->ServiceName)){
                return response()->json([
                    'status'=>'true',
                    'data' => [
                        'message' => $response->ServiceName . " list",
                        'response' => $response
                    ]
                ],200);
            }else{
                return response()->json([
                    'status'=>'false',
                    'data' => [
                        'message' => $response->errors
                    ]
                ],400);
            }

            
        }
        return response()->json([
            'status'=>'false',
            'data' => [
                'message' => "An error occured while retrieving list"
            ]
        ],400);
    }

    public function purchaseData(Request $request) {
        $validatedData = Validator::make($request->all(['transaction_pin','phone','package','serviceID','billersCode','variation_code','amount','is_beneficiary']), [
            'transaction_pin' => ['required','numeric','digits:4', new TransactionPin],
            'phone' => ['required','numeric','digits:11'],
            'amount' => ['required','numeric'],
            'serviceID' => ['required'],
            'package' => ['required'],
            'billersCode' => ['required'],
            'variation_code' => ['required'],
            // 'is_beneficiary' => ['boolean']
        ],[
            // 'is_beneficiary.boolean' => 'Beneficiary status must be true or false',
            'transaction_pin.required' => 'Transaction pin cannot be empty',
            'transaction_pin.numeric' => 'Only digits allowed for transaction pin',
            'transaction_pin.digits' => 'Transaction pin must have 4 (four) digits',
            'phone.required' => 'Phone number is required',
            'phone.numeric' => 'Phone number accepts numbers only',
            'phone.digits' => 'Phone number can be only 11 digits',
            'amount.required' => 'Field is required',
            'amount.numeric' => 'Field must be numeric',
            'serviceID.required' => 'Field is required',
            'package.required' => 'Field is required',
            'billersCode.required' => 'Field is required',
            'variation_code.required' => 'Field is required',
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
        $result = collect($this->serviceList)->pluck('data')->contains($request['serviceID']);
        $status = 'pending';
        $amount = $request['amount'];
        $phone = $request['phone'];
        
        $user = Auth::user();
        $deviceToken = $user->device_token;
        
        $currentBalance = $user->balance;
            
        $balanceBefore = $currentBalance;
        $balanceAfter = $currentBalance - $amount;
        
        if ($result) {
            // Set the timezone to Africa/Lagos
            $now = Carbon::now('Africa/Lagos');
            // Format the date and time as "YYYYMMDDHHII"
            $requestId = $now->format('YmdHi');
            // Add any additional alphanumeric string as desired
            $requestId = $requestId . Str::random(5);

            //Deduct amount
            $wallet = new WalletController();
            $dedRes = json_decode($wallet->callWalletUpdate($request['amount'], 'id', Auth::user()->id, '-'));
            if ($dedRes->code !== 1) {
                return response()->json([
                    'status' => 'false',
                    'data'=> [
                        'message' => 'Payment failed',
                        'error'=> $dedRes->msg
                    ]
                ],400);
            }
            
            $vtapi = getSettings('vtpass','apikeyvtpass');
            $vtsecret = getSettings('vtpass','secretkeyvtpass');
            if ($vtapi == "error" || $vtsecret == "error") {return response()->json(retErrorSetting());}
            $requestParams = Http::withHeaders([
                'api-key' => $vtapi,
                'secret-key' => $vtsecret,
            ])->post('https://vtpass.com/api/pay',[
                'request_id' => $requestId,
                'serviceID' => $request['serviceID'],
                'billersCode' => $request['billersCode'],
                'variation_code' => $request['variation_code'],
                'amount' => $request['amount'],
                'phone' => $request['phone'],
            ]);

            $response = json_decode($requestParams->body());
            if($response->code != 000){
                $dedRes = json_decode($wallet->callWalletUpdate($request['amount'], 'id', Auth::user()->id, '+'));
                return response()->json([
                    'status' => 'false',
                    'data'=> [
                        'message' => 'Payment failed',
                        'error'=> $response
                    ]
                ],400);
               
            }
            
            $data = [
                'service_name' => $response->content->transactions->product_name,
                'type' => $response->content->transactions->type,
                'phone' => $request['phone'],
                'package' => $request['package'],
                'reference' => $requestId,
                'img' => collect($this->serviceList)->where('data', $request['serviceID'])->pluck('img')->first(),
                'balance_before'    => $balanceBefore,
                'balance_after'     => $balanceAfter,
            ];
            
            $is_beneficiary = isset($request['is_beneficiary']) ? $request['is_beneficiary'] : null;
           
            if (isset($is_beneficiary) && ($is_beneficiary == 1 || $is_beneficiary == true)) {
                Helpers::addBeneficiary('data', json_encode($data), '', $request['phone'], $response->content->transactions->product_name);
            }

            $reFront = response()->json([
                'status'=>'true',
                'data' => ['message' => "Data purchase successful",'transaction_id' => $requestId]
            ],200);

            if ($response->content->transactions->status == "delivered") {
                $status = 'successful';
            }elseif ($response->content->transactions->status == "pending" || $response->content->transactions->status == "initiated") {
                $status = 'pending';
            }else {
                $status = 'failed';
                $wallet = new WalletController();
                $wallet->callWalletUpdate($request['amount'], 'id', Auth::user()->id, '+');
                $reFront = response()->json(['status'=>'false','data'=>['message' => "Data purchase failed"]],400);
            }
            
            if(!is_null($deviceToken)){
                $title = "Data Purchase";
                $body = "Transaction $status";
                $this->firebaseService->sendNotification($title, $body, $deviceToken );
            }
            $user->notify(new TransactionNotification(
                "Data recharge Successful",
                "$phone Successfully recharged",
                null, // Optional icon
                null, // Optional URL
                ['transaction_id' => $requestId]
            ));

            TransactionLog::dispatch(Auth::user()->id,'Data',$request['amount'],$requestId,$status,$request['phone'],json_encode($data));
            return $reFront;
        }else {
            return response()->json([
                'status'=>'false',
                'data' => [
                    'message' => "Validation failed",
                    'error' => ['serviceID' => 'Invalid service ID']
                ]
            ],400);
        }
    }

    public function verifyCableTVNumber(Request $request) {
        $validatedData = Validator::make($request->all(['serviceID','billersCode']), [
            'billersCode' => ['required','numeric'],
            'serviceID' => ['required']

        ],[
            
            'billersCode.required' => 'Metre number required',
            'billersCode.numeric' => 'Metre number accepts numbers only',
            'serviceID.required' => 'Service ID is required'
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
        // $result = collect($this->electricityList)->pluck('serviceID')->contains($request['serviceID']);
        // if ($result) {
            $vtapi = getSettings('vtpass','apikeyvtpass');
            $vtsecret = getSettings('vtpass','secretkeyvtpass');
            if ($vtapi == "error" || $vtsecret == "error") {return response()->json(retErrorSetting());}
            $requestParams = Http::withHeaders([
                'api-key' => $vtapi,
                'secret-key' => $vtsecret,
            ])->post('https://vtpass.com/api/merchant-verify',[
                'serviceID' => $request['serviceID'],
                'billersCode' => $request['billersCode'],
            ]);

            $response = json_decode($requestParams->body());
            if ($response->code == '000' && !isset($response->content->error)) {
                return response()->json([
                    'status'=>'true',
                    'data' => [
                        'message' => "Verification successful",
                        'data' => $response->content
                    ]
                ],200);
            }
            return response()->json([
                'status'=>'false',
                'data' => [
                    'message' => "Verification failed",
                    'error' => $response->content->error
                ]
            ],400);
        // }

        return response()->json([
            'status'=>'false',
            'data' => [
                'message' => "Validation failed",
                'error' => ['serviceID' => 'Invalid service ID']
            ]
        ],400);
    }
    
    public function tvProviderVariationList($e) {
        $result = collect($this->cableList)->pluck('serviceID')->contains($e);

        if ($result) {
            $vtapi = getSettings('vtpass','apikeyvtpass');
            $vtsecret = getSettings('vtpass','secretkeyvtpass');
            if ($vtapi == "error" || $vtsecret == "error") {return response()->json(retErrorSetting());}
            $requestParams = Http::withHeaders([
                'api-key' => $vtapi,
                'secret-key' => $vtsecret,
            ])->get('https://vtpass.com/api/service-variations?serviceID='.$e);
            
            $response = json_decode($requestParams->body())->content;

            return response()->json([
                'status'=>'true',
                'data' => [
                    'message' => $response->ServiceName . " list",
                    'response' => $response
                ]
            ],200);
        }
        return response()->json([
            'status'=>'false',
            'data' => [
                'message' => "An error occured while retrieving list"
            ]
        ],400);
    }

    public function purchaseTVSubscription(Request $request) {
        $validatedData = Validator::make($request->all(['transaction_pin','phone','package','serviceID','billersCode','variation_code','amount']), [
            'transaction_pin' => ['required','numeric','digits:4', new TransactionPin],
            'phone' => ['required','numeric','digits:11'],
            'amount' => ['required','numeric'],
            'serviceID' => ['required'],
            'package' => ['required'],
            'billersCode' => ['required'],
            'variation_code' => ['required'],
            'is_beneficiary' => ['boolean']
        ],[
            'transaction_pin.required' => 'Transaction pin cannot be empty',
            'transaction_pin.numeric' => 'Only digits allowed for transaction pin',
            'transaction_pin.digits' => 'Transaction pin must have 4 (four) digits',
            'phone.required' => 'Phone number is required',
            'phone.numeric' => 'Phone number accepts numbers only',
            'phone.digits' => 'Phone number can be only 11 digits',
            'amount.required' => 'Field is required',
            'amount.numeric' => 'Field must be numeric',
            'serviceID.required' => 'Field is required',
            'package.required' => 'Field is required',
            'billersCode.required' => 'Field is required',
            'variation_code.required' => 'Field is required',
            'is_beneficiary.boolean' => 'Beneficiary status must be true or false',
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
        $collect = collect($this->cableList);
        $is_beneficiary = isset($request['is_beneficiary']) ? $request['is_beneficiary'] : null;
        
        $user = Auth::user();
        $deviceToken = $user->device_token;
        

        if ($collect->pluck('serviceID')->contains($request['serviceID'])) {
            // Set the timezone to Africa/Lagos
            $now = Carbon::now('Africa/Lagos');
            // Format the date and time as "YYYYMMDDHHII"
            $requestId = $now->format('YmdHi');
            // Add any additional alphanumeric string as desired
            $requestId = $requestId . Str::random(5);
            $conv_fee = AdminSetting::where('name','cable')->first(['data'])->data ?? $collect->where('serviceID', $request['serviceID'])->pluck('convenience_fee')->first();
            $amount = $request['amount'];
            $amount_w_conv_fee = $request['amount'] + $conv_fee;
            
            
            $currentBalance = $user->balance;
            
            $balanceBefore = $currentBalance;
            $balanceAfter = $currentBalance - $amount_w_conv_fee;

            //Deduct amount
            $wallet = new WalletController();
            $dedRes = json_decode($wallet->callWalletUpdate($amount_w_conv_fee, 'id', $user->id, '-'));
            if ($dedRes->code !== 1) {
                return response()->json([
                    'status' => 'false',
                    'data'=> [
                        'message' => 'Payment failed',
                        'error'=> $dedRes->msg
                    ]
                ],400);
            }

            $vtapi = getSettings('vtpass','apikeyvtpass');
            $vtsecret = getSettings('vtpass','secretkeyvtpass');
            if ($vtapi == "error" || $vtsecret == "error") {return response()->json(retErrorSetting());}
            $requestParams = Http::withHeaders([
                'api-key' => $vtapi,
                'secret-key' => $vtsecret,
            ])->post('https://vtpass.com/api/pay',[
                'request_id' => $requestId,
                'serviceID' => $request['serviceID'],
                'billersCode' => $request['billersCode'],
                'variation_code' => $request['variation_code'],
                'amount' => $request['amount'],
                'phone' => $request['phone']
            ]);

            $response = json_decode($requestParams->body());
            if($response->code != 000){
                $dedRes = json_decode($wallet->callWalletUpdate($request['amount'], 'id', Auth::user()->id, '+'));
                return response()->json([
                    'status' => 'false',
                    'data'=> [
                        'message' => 'Payment failed',
                        'error'=> $response
                    ]
                ],400);
               
            }
            if (isset($response->content->transactions->status,$response->content->transactions->type,$response->content->transactions->product_name)) {
                $data = [
                    'service_name' => $response->content->transactions->product_name,
                    'type' => $response->content->transactions->type,
                    'phone' => $request['phone'],
                    'package' => $request['package'],
                    'convenience_fee' => $conv_fee,
                    'reference' => $requestId,
                    'img' => collect($this->cableList)->where('serviceID', $request['serviceID'])->pluck('img')->first(),
                    'balance_before'    => $balanceBefore,
                    'balance_after'     => $balanceAfter,
                ];

                $reFront = response()->json([
                    'status'=>'true',
                    'data' => ['message' => "Tv subscription purchase successful",'transaction_id' => $requestId]
                ],200);

                if ($response->content->transactions->status == "delivered") {
                    $status = 'successful';
                }elseif ($response->content->transactions->status == "pending" || $response->content->transactions->status == "initiated") {
                    $status = 'pending';
                }else {
                    $status = 'failed';
                }
                $savedData =[
                    'service_name' => $response->content->transactions->product_name, 
                    'type' => $response->content->transactions->type,
                    'serviceID' => $request['serviceID'],
                    'billersCode' => $request['billersCode'],
                ];
                if (isset($is_beneficiary) && ($is_beneficiary == 1 || $is_beneficiary == true)) {
                    Helpers::addBeneficiary('cable_tv', $savedData, '', $request['billersCode'], $response->content->transactions->product_name);
                }
                if(!is_null($deviceToken)){
                    $title = "TV Subscription";
                    $body = "Transaction $status";
                    $this->firebaseService->sendNotification($title, $body, $deviceToken );
                }
                $user->notify(new TransactionNotification(
                    "Cable Subscription Successful",
                    "Cable Biller Successfully recharged",
                    null, // Optional icon
                    null, // Optional URL
                    ['transaction_id' => $requestId]
                ));
                TransactionLog::dispatch(Auth::user()->id,'Cable TV',$amount,$requestId,$status,$request['phone'],json_encode($data));
                return $reFront;
            }
            $wallet = new WalletController();
            $wallet->callWalletUpdate($amount_w_conv_fee, 'id', Auth::user()->id, '+');
            $reFront = response()->json(['status'=>'false','data' => ['message' => "Tv subscription purchase failed"]],400);
            return $reFront;
        }
        return response()->json([
            'status'=>'false',
            'data' => [
                'message' => "Validation failed",
                'error' => ['serviceID' => 'Invalid service ID']
            ]
        ],400);
    }

    public function verifyMetreNumber(Request $request) {
        $validatedData = Validator::make($request->all(['type','billersCode']), [
            'type' => ['required','in:prepaid,postpaid'],
            'billersCode' => ['required','numeric'],
            

        ],[
            'type.required' => 'Please select metre type',
            'type.in' => 'Only prepaid or postpaid allowed',
            'billersCode.required' => 'Metre number required',
            'billersCode.numeric' => 'Metre number accepts numbers only',
            
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
        $result = collect($this->electricityList)->pluck('serviceID')->contains($request['serviceID']);
        if ($result) {
            $vtapi = getSettings('vtpass','apikeyvtpass');
            $vtsecret = getSettings('vtpass','secretkeyvtpass');
            if ($vtapi == "error" || $vtsecret == "error") {return response()->json(retErrorSetting());}
            $requestParams = Http::withHeaders([
                'api-key' => $vtapi,
                'secret-key' => $vtsecret,
            ])->post('https://vtpass.com/api/merchant-verify',[
                'serviceID' => $request['serviceID'],
                'billersCode' => $request['billersCode'],
                'type' => $request['type'],
            ]);

            $response = json_decode($requestParams->body());
            if ($response->code == '000' && !isset($response->content->error)) {
                return response()->json([
                    'status'=>'true',
                    'data' => [
                        'message' => "Verification successful",
                        'data' => $response->content
                    ]
                ],200);
            }
            return response()->json([
                'status'=>'false',
                'data' => [
                    'message' => "Verification failed",
                    'error' => $response->content->error
                ]
            ],400);
        }

        return response()->json([
            'status'=>'false',
            'data' => [
                'message' => "Validation failed",
                'error' => ['serviceID' => 'Invalid service ID']
            ]
        ],400);
    }

    public function getElectricityList() {
        $arr = $this->electricityList;

        return response()->json([
            'status'=>'true',
            'data' => [
                'message' => "Electricity providers list retrieved successfully",
                'data' => $arr
            ]
        ],200);
    }

    public function purchaseElectricity(Request $request) {
        $validatedData = Validator::make($request->all(['transaction_pin','type','serviceID','billersCode','phone','amount','package']), [
            'transaction_pin' => ['required','numeric','digits:4', new TransactionPin],
            'type' => ['required','in:prepaid,postpaid'],
            'billersCode' => ['required','numeric'],
            'amount' => ['required','numeric','min:500'],
            // 'phone' => ['required','numeric','digits:11'],
            'package' => ['required'],
            'serviceID' => ['required'],
            'is_beneficiary' => ['boolean']
        ],[
            'transaction_pin.required' => 'Transaction pin cannot be empty',
            'transaction_pin.numeric' => 'Only digits allowed for transaction pin',
            'transaction_pin.digits' => 'Transaction pin must have 4 (four) digits',
            'type.required' => 'Please select metre type',
            'type.in' => 'Only prepaid or postpaid allowed',
            'billersCode.required' => 'Metre number required',
            'billersCode.numeric' => 'Metre number accepts numbers only',
            'serviceID.required' => 'Field is required',
            // 'phone.required' => 'Phone number is required',
            // 'phone.numeric' => 'Phone number accepts numbers only',
            // 'phone.digits' => 'Phone number can be only 11 digits',
            'amount.required' => 'Amount to purchase is required',
            'amount.numeric' => 'Amount can numbers only',
            'amount.min' => 'A minimum purchase of 500 is required',
            'package.required' => 'Field is required',
            'is_beneficiary.boolean' => 'Beneficiary status must be true or false',
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
        $collect = collect($this->electricityList);
        
        $user= Auth::user();
        
        if ($collect->pluck('serviceID')->contains($request['serviceID'])) {
            // Set the timezone to Africa/Lagos
            $now = Carbon::now('Africa/Lagos');
            // Format the date and time as "YYYYMMDDHHII"
            $requestId = $now->format('YmdHi');
            // Add any additional alphanumeric string as desired
            $requestId = $requestId . Str::random(5);
            $conv_fee = AdminSetting::where('name','electricity')->first(['data'])->data ?? $collect->where('serviceID', $request['serviceID'])->pluck('convenience_fee')->first();
            $amount = $request['amount'];
            $amount_w_conv_fee = $request['amount'] + $conv_fee;
            
            $currentBalance = $user->balance;
            
            $balanceBefore = $currentBalance;
            $balanceAfter = $currentBalance - $amount_w_conv_fee;
            
            //Deduct amount
            $wallet = new WalletController();
            $dedRes = json_decode($wallet->callWalletUpdate($amount_w_conv_fee, 'id', Auth::user()->id, '-'));
            if ($dedRes->code !== 1) {
                return response()->json([
                    'status' => 'false',
                    'data'=> [
                        'message' => 'Payment failed',
                        'error'=> $dedRes->msg
                    ]
                ],400);
            }

            $vtapi = getSettings('vtpass','apikeyvtpass');
            $vtsecret = getSettings('vtpass','secretkeyvtpass');
            if ($vtapi == "error" || $vtsecret == "error") {return response()->json(retErrorSetting());}
            $requestParams = Http::withHeaders([
                'api-key' => $vtapi,
                'secret-key' => $vtsecret,
            ])->post('https://vtpass.com/api/pay',[
                'request_id' => $requestId,
                'serviceID' => $request['serviceID'],
                'billersCode' => $request['billersCode'],
                'variation_code' => $request['type'],
                'amount' => $request['amount'],
                'phone' => Auth::user()->phone_number
            ]);

            $response = json_decode($requestParams->body());
            
            // dd($response, Auth::user()->phone_number );
            
            if($response->code != 000){
                $dedRes = json_decode($wallet->callWalletUpdate($request['amount'], 'id', Auth::user()->id, '+'));
                return response()->json([
                    'status' => 'false',
                    'data'=> [
                        'message' => 'Payment failed',
                        'error'=> $response
                    ]
                ],400);
               
            }
            if (isset($response->content->transactions->status,$response->content->transactions->type,$response->content->transactions->product_name)) {
                $token = $response->token ?? $response->purchase_code ?? $response->mainToken;
                $data = [
                    'service_name' => $response->content->transactions->product_name,
                    'type' => $response->content->transactions->type,
                    'phone' => Auth::user()->phone_number,
                    'metreNo' => $request['billersCode'],
                    'customer_name' => $response->customerName ?? $response->CustomerAddress ?? "None",
                    'customer_address' => $response->customerAddress ?? $response->address ?? "None",
                    'convenience_fee' => $conv_fee,
                    'units' => $response->mainTokenUnits ?? $response->units ?? $response->Units ?? "None",
                    'tariff' => $response->tariffIndex ?? $response->tariff ?? $response->tariffCode ?? $response->Tariff ?? "None",
                    'package' => $request['package'],
                    'reference' => $requestId,
                    'token' => $token,
                    'img' => collect($this->electricityList)->where('serviceID', $request['serviceID'])->pluck('img')->first(),
                    'balance_before'    => $balanceBefore,
                    'balance_after'     => $balanceAfter,
                ];
                $savedData =[
                    'service_name' => $response->content->transactions->product_name,
                    'type' => $response->content->transactions->type,
                    'serviceID' => $request['serviceID'],
                    'billersCode' => $request['billersCode'],
                    // 'Customer_Name' =>$request['customer_name'],
                ];
                
                $cName = $response->customerName ?? "";
                
                if (isset($is_beneficiary) && ($is_beneficiary == 1 || $is_beneficiary == true)) {
                    Helpers::addBeneficiary('electricity', json_encode($savedData), $cName, $request['billersCode'], $response->content->transactions->product_name);
                }
                
                
                $mailData = ['token' => $token];
                Mail::to(Auth::user()->email)->send(new TokenMail('emails.token', $mailData, 'Electricity Token'));
                $reFront = response()->json([
                    'status'=>'true',
                    'data' => ['message' => "Electricity purchase successful",'transaction_id' => $requestId]
                ],200);

                if ($response->content->transactions->status == "delivered") {
                    $status = 'successful';
                }elseif ($response->content->transactions->status == "pending" || $response->content->transactions->status == "initiated") {
                    $status = 'pending';
                } else {
                    $status = 'failed';
                }
                
                

                TransactionLog::dispatch(Auth::user()->id,'Electricity',$amount,$requestId,$status,$request['billersCode'],json_encode($data));
                return $reFront;
            }
            $wallet = new WalletController();
            $wallet->callWalletUpdate($amount_w_conv_fee, 'id', Auth::user()->id, '+');
            $reFront = response()->json(['status'=>'false','data' => ['message' => "Electricity purchase failed"]],400);
            return $reFront;
        }

        return response()->json([
            'status'=>'false',
            'data' => [
                'message' => "Validation failed",
                'error' => ['serviceID' => 'Invalid service ID']
            ]
        ],400);
    }
    
    public function getRecentTransactions($type) {
        $user_id = Auth::user()->id;
        // Validate the type parameter if necessary
        $validTypes = ['Airtime', 'Data', 'Electricity', 'TV Cable'];
        if (!in_array($type, $validTypes)) {
            return response()->json([
                'status' => false,
                'error' => 'Invalid type'
            ], 400);
        }

        // Fetch recent transactions
        $transactions = ModelsTransactionLog::where('type', $type)
            ->where('status', 'successful')
            ->where('user_id', $user_id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->groupBy('recipient')
            ->map(function($group) {
                // Take the first entry in each group (since they're grouped by recipient)
                $transaction = $group->first();
                $data = json_decode($transaction->data);
                return [
                    'recipient' => $transaction->recipient,
                    'img' => $data->img ?? null,
                    'service_name' => $data->service_name ?? null,
                ];
            });
            

        
        return response()->json([
            'status'=>'true',
            'data' => [
                'message' => "Recent $type Transaction",
                'data' => $transactions->values()
            ]
        ],200);

        // return response()->json($transactions);
    }
    
    // Educational Logics
    public function waecServices() {
        $arr = $this->waecServices;

        return response()->json([
            'status'=>'true',
            'data' => [
                'message' => "WEAC Services list retrieved successfully",
                'data' => $arr
            ]
        ],200);
    }
    
    public function educationVariation($e) {
        if(is_null($e)){
            return response()->json([
                'status'=>'false',
                'data' => [
                    'message' => "ServiceID Required"
                ]
            ],400);
        }else {
            $vtapi = getSettings('vtpass','apikeyvtpass');
            $vtsecret = getSettings('vtpass','secretkeyvtpass');
            if ($vtapi == "error" || $vtsecret == "error") {return response()->json(retErrorSetting());} 
            $requestParams = Http::withHeaders([
                'api-key' => $vtapi,
                'secret-key' => $vtsecret,
            ])->get('https://vtpass.com/api/service-variations?serviceID='.$e);
            
            $response = json_decode($requestParams->body())->content;
            
            if(isset($response->ServiceName)){
                return response()->json([
                        'status' => 'true',
                        'data' => [
                            'message' => $response->ServiceName . " list",
                            'response' => $response
                        ]
                    ], 200);

            }else{
                return response()->json([
                    'status'=>'false',
                    'data' => [
                        'response' => $response
                    ]
                ],400);
            }

            
        }
        return response()->json([
            'status'=>'false',
            'data' => [
                'message' => "An error occured while retrieving list"
            ]
        ],400);
    }
    
    public function purchaseWaec(Request $request) {
        $validatedData = Validator::make($request->all(['transaction_pin','quantity','serviceID','variation_code','phone','amount']), [
            'transaction_pin' => ['required','numeric','digits:4', new TransactionPin],
            'variation_code' => ['required'],
            'quantity' => ['numeric'],
            'amount' => ['required','numeric'],
            'phone' => ['required','numeric','digits:11'],
            'serviceID' => ['required'],
        ],[
            'transaction_pin.required' => 'Transaction pin cannot be empty',
            'transaction_pin.numeric' => 'Only digits allowed for transaction pin',
            'transaction_pin.digits' => 'Transaction pin must have 4 (four) digits',
            'variation_code.required' => 'Please select metre type',
            'quantity.numeric' => 'Metre number accepts numbers only',
            'serviceID.required' => 'Field is required',
            'phone.required' => 'Phone number is required',
            'phone.numeric' => 'Phone number accepts numbers only',
            'phone.digits' => 'Phone number can be only 11 digits',
            'amount.required' => 'Amount to purchase is required',
            'amount.numeric' => 'Amount can be numbers only',
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
        $deviceToken = $user->device_token;
        
        $request = Purify::clean($request->all());
        $now = Carbon::now('Africa/Lagos');
        $requestId = $now->format('YmdHi');
        $requestId = $requestId . Str::random(5);
        
        $conv_fee = AdminSetting::where('name','waec')->first(['data'])->data * $request['quantity'];
        $amount = $request['amount'] * $request['quantity'];
        $amount_w_conv_fee = $amount + $conv_fee;
        
        $currentBalance = $user->balance;
            
        $balanceBefore = $currentBalance;
        $balanceAfter = $currentBalance - $amount_w_conv_fee;

        //Deduct amount
        // $wallet = new WalletController();
        // $dedRes = json_decode($wallet->callWalletUpdate($amount_w_conv_fee, 'id', Auth::user()->id, '-'));
        // if ($dedRes->code !== 1) {
        //     return response()->json([
        //         'status' => 'false',
        //         'data'=> [
        //             'message' => 'Payment failed',
        //             'error'=> $dedRes->msg
        //         ]
        //     ],400);
        // }

        $vtapi = getSettings('vtpass','apikeyvtpass');
        $vtsecret = getSettings('vtpass','secretkeyvtpass');
            
        if ($vtapi == "error" || $vtsecret == "error") {return response()->json(retErrorSetting());}
        $requestParams = Http::withHeaders([
            'api-key' => $vtapi,
            'secret-key' => $vtsecret,
        ])->post('https://vtpass.com/api/pay',[
            'request_id' => $requestId,
            'serviceID' => $request['serviceID'],
            'quantity' => $request['quantity'],
            'variation_code' => $request['variation_code'],
            'amount' => $request['amount'],
            'phone' => $request['phone']
        ]);

        $response = json_decode($requestParams->body());
        if($response->code != 000){
            $status = 'failed';
            
            // $dedRes = json_decode($wallet->callWalletUpdate($amount_w_conv_fee, 'id', Auth::user()->id, '+'));
            return response()->json([
                'status' => 'false',
                'data'=> [
                    'message' => 'Purchase failed',
                    'error'=> $response->response_description
                ]
            ],400);
           
        }
        if (isset($response->content->transactions->status,$response->content->transactions->type,$response->content->transactions->product_name)) {
            $purchase_code = $response->purchased_code;
            $product = $response->content->transactions->product_name;
            $data = [
                'service_name' => $response->content->transactions->product_name,
                'type' => $response->content->transactions->type,
                'phone' => $request['phone'],
                'convenience_fee' => $conv_fee,
                'reference' => $requestId,
                'purchased_code' => $response->purchased_code,
                'balance_before'    => $balanceBefore,
                'balance_after'     => $balanceAfter,
            ];
            
            $mailData = ['token' => $response->purchased_code, 'service_name' => $response->content->transactions->product_name,];
            
            Mail::to(Auth::user()->email)->send(new TokenMail('emails.waec', $mailData, $product));

            $reFront = response()->json([
                'status'=>'true',
                'data' => ['message' => "$product Purchase successful",'transaction_id' => $requestId, 'amount' => $amount_w_conv_fee]
            ],200);

            if ($response->content->transactions->status == "delivered") {
                $status = 'successful';
            }elseif ($response->content->transactions->status == "pending" || $response->content->transactions->status == "initiated") {
                $status = 'pending';
            } else {
                $status = 'failed';
            }
            // if(!is_null($deviceToken)){
            //     $title = "$product Purchase";
            //     $body = "Transaction $status";
            //     $this->firebaseService->sendNotification($title, $body, $deviceToken );
            // }
            // $user->notify(new TransactionNotification( $product, "$product Successfully Purchased", null, null, ['transaction_id' => $requestId] ));
            TransactionLog::dispatch(Auth::user()->id, $product, $amount_w_conv_fee,$requestId,$status,$request['phone'],json_encode($data));
            return $reFront;
        }
        $wallet = new WalletController();
        // $wallet->callWalletUpdate($amount_w_conv_fee, 'id', Auth::user()->id, '+');
        $reFront = response()->json(['status'=>'false','data' => ['message' => "WEAC item Purchase failed"]],400);
        return $reFront;
        

        return response()->json([
            'status'=>'false',
            'data' => [
                'message' => "Validation failed",
                'error' => ['serviceID' => 'Invalid service ID']
            ]
        ],400);
    }
    
    public function verifyProfile(Request $request) {
        $validatedData = Validator::make($request->all(['variation_code','billersCode']), [
            'variation_code' => ['required'],
            'billersCode' => ['required','numeric']

        ],[
            'variation_code.required' => 'Please select jamb type',
            'billersCode.required' => 'Profile ID required',
            'billersCode.numeric' => 'Profile ID accepts numbers only',
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
        
            $vtapi = getSettings('vtpass','apikeyvtpass');
            $vtsecret = getSettings('vtpass','secretkeyvtpass');
            
            if ($vtapi == "error" || $vtsecret == "error") {
                return response()->json(retErrorSetting());
            }
            
            try {
                // Make the API request
                $requestParams = Http::withHeaders([
                    'api-key' => $vtapi,
                    'secret-key' => $vtsecret,
                ])->timeout(30) // Timeout of 30 seconds to avoid long waits
                ->post('https://vtpass.com/api/merchant-verify', [
                    'serviceID' => 'jamb',
                    'billersCode' => $request['billersCode'],
                    'type' => $request['variation_code'],
                ]);
            
                $response = json_decode($requestParams->body());
                
                // dd($response);
            
                // Check if the response code is success and no errors
                if ($response->code == '000' && !isset($response->content->error)) {
                    return response()->json([
                        'status' => 'true',
                        'data' => [
                            'message' => "Verification successful",
                            'data' => $response->content
                        ]
                    ], 200);
                }
            
                // If the response contains an error
                return response()->json([
                    'status' => 'false',
                    'data' => [
                        'message' => "Verification failed",
                        'error' => $response->content->error
                    ]
                ], 400);
            
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                // Handle connection issues like timeouts
                return response()->json([
                    'status' => 'false',
                    'data' => [
                        'message' => "Connection error",
                        'error' => $e->getMessage()
                    ]
                ], 500);
            
            } catch (\Exception $e) {
                // Catch any other exceptions that occur
                return response()->json([
                    'status' => 'false',
                    'data' => [
                        'message' => "An error occurred",
                        'error' => $e->getMessage()
                    ]
                ], 500);
            }

       

        return response()->json([
            'status'=>'false',
            'data' => [
                'message' => "Validation failed",
                'error' => ['serviceID' => 'Invalid service ID']
            ]
        ],400);
    }
    
    public function purchaseJamb(Request $request) {
        $validatedData = Validator::make($request->all(['transaction_pin','billersCode','serviceID','variation_code','phone','amount']), [
            'transaction_pin' => ['required','numeric','digits:4', new TransactionPin],
            'variation_code' => ['required'],
            'billersCode' => ['required', 'numeric'],
            'amount' => ['required','numeric'],
            'phone' => ['required','numeric','digits:11'],
            'serviceID' => ['required'],
        ],[
            'transaction_pin.required' => 'Transaction pin cannot be empty',
            'transaction_pin.numeric' => 'Only digits allowed for transaction pin',
            'transaction_pin.digits' => 'Transaction pin must have 4 (four) digits',
            'variation_code.required' => 'Please select metre type',
            'billersCode.required' => 'Billers code cannot be empty',
            'billersCode.numeric' => 'Only digits allowed for Billers Code',
            'serviceID.required' => 'Field is required',
            'phone.required' => 'Phone number is required',
            'phone.numeric' => 'Phone number accepts numbers only',
            'phone.digits' => 'Phone number can be only 11 digits',
            'amount.required' => 'Amount to purchase is required',
            'amount.numeric' => 'Amount can be numbers only',
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
        $deviceToken = $user->device_token;
        
        $request = Purify::clean($request->all());
        $now = Carbon::now('Africa/Lagos');
        $requestId = $now->format('YmdHi');
        $requestId = $requestId . Str::random(5);
        
        $conv_fee = AdminSetting::where('name','jamb')->first(['data'])->data ;
        $amount = $request['amount'] ;
        $amount_w_conv_fee = $amount + $conv_fee;
        
        $currentBalance = $user->balance;
            
        $balanceBefore = $currentBalance;
        $balanceAfter = $currentBalance - $amount_w_conv_fee;

        //Deduct amount
        $wallet = new WalletController();
        $dedRes = json_decode($wallet->callWalletUpdate($amount_w_conv_fee, 'id', Auth::user()->id, '-'));
        if ($dedRes->code !== 1) {
            return response()->json([
                'status' => 'false',
                'data'=> [
                    'message' => 'Payment failed',
                    'error'=> $dedRes->msg
                ]
            ],400);
        }

        $vtapi = getSettings('vtpass','apikeyvtpass');
        $vtsecret = getSettings('vtpass','secretkeyvtpass');
            
        if ($vtapi == "error" || $vtsecret == "error") {return response()->json(retErrorSetting());}
        $requestParams = Http::withHeaders([
            'api-key' => $vtapi,
            'secret-key' => $vtsecret,
        ])->post('https://vtpass.com/api/pay',[
            'request_id' => $requestId,
            'billersCode' => $request['billersCode'],
            'serviceID' => $request['serviceID'],
            'variation_code' => $request['variation_code'],
            'amount' => $request['amount'],
            'phone' => $request['phone']
        ]);

        $response = json_decode($requestParams->body());
        if($response->code != 000){
            $status = 'failed';
            // if(!is_null($deviceToken)){
            //     $title = 'JAMB Purchase Failed';
            //     $body = "Transaction $status";
            //     $this->firebaseService->sendNotification($title, $body, $deviceToken );
            // }
            // $dedRes = json_decode($wallet->callWalletUpdate($amount_w_conv_fee, 'id', Auth::user()->id, '+'));
            return response()->json([
                'status' => 'false',
                'data'=> [
                    'message' => 'Purchase failed',
                    'error'=> $response->response_description
                ]
            ],400);
           
        }
        if (isset($response->content->transactions->status,$response->content->transactions->type,$response->content->transactions->product_name)) {
            $purchase_code = $response->purchased_code;
            $product = $response->content->transactions->product_name;
            $data = [
                'service_name' => $response->content->transactions->product_name,
                'type' => $response->content->transactions->type,
                'phone' => $request['phone'],
                'convenience_fee' => $conv_fee,
                'reference' => $requestId,
                'purchased_code' => $response->purchased_code,
                'balance_before'    => $balanceBefore,
                'balance_after'     => $balanceAfter,
            ];
            
            $mailData = ['token' => $response->purchased_code, 'service_name' => $response->content->transactions->product_name,];
            
            Mail::to(Auth::user()->email)->send(new TokenMail('emails.waec', $mailData, $product));

            $reFront = response()->json([
                'status'=>'true',
                'data' => ['message' => "$product Purchase successful",'transaction_id' => $requestId, 'amount' => $amount_w_conv_fee]
            ],200);

            if ($response->content->transactions->status == "delivered") {
                $status = 'successful';
            }elseif ($response->content->transactions->status == "pending" || $response->content->transactions->status == "initiated") {
                $status = 'pending';
            } else {
                $status = 'failed';
            }
            // if(!is_null($deviceToken)){
            //     $title = "$product Purchase";
            //     $body = "Transaction $status";
            //     $this->firebaseService->sendNotification($title, $body, $deviceToken );
            // }
            // $user->notify(new TransactionNotification( $product, "$product Successfully Purchased", null, null, ['transaction_id' => $requestId] ));
            TransactionLog::dispatch(Auth::user()->id, $product, $amount_w_conv_fee,$requestId,$status,$request['phone'],json_encode($data));
            return $reFront;
        }
        $wallet = new WalletController();
        // $wallet->callWalletUpdate($amount_w_conv_fee, 'id', Auth::user()->id, '+');
        $reFront = response()->json(['status'=>'false','data' => ['message' => "WEAC item Purchase failed"]],400);
        return $reFront;
        

        return response()->json([
            'status'=>'false',
            'data' => [
                'message' => "Validation failed",
                'error' => ['serviceID' => 'Invalid service ID']
            ]
        ],400);
    }

}