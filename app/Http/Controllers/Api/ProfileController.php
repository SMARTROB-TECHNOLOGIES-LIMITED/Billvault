<?php

namespace App\Http\Controllers\Api;

use App\Mail\ForgotTransactionPin;
use App\Helpers;
use Carbon\Carbon;
use App\Models\User;
use App\Models\KycLevel;
use App\Models\TierTwo;
use App\Models\TierThree;
use App\Models\CardDetail;
use App\Models\CardWithdraw;
use App\Models\AdminSetting;
use function App\getSettings;
use App\Http\Controllers\Api\WalletController;
use Illuminate\Support\Facades\Storage;
use App\Jobs\TransactionLog;
use App\Models\TransactionLog as ModelsTransactionLog;

use App\Rules\ParamAlready;
use Faker\Factory as Faker;
use Illuminate\Http\Request;
use App\Rules\SingleWordRule;
use App\Rules\TransactionPin;
use function App\generateOTP;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMail;
use Intervention\Image\Facades\Image;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use App\Rules\ValidReferralUsername;

class ProfileController extends Controller
{
    
    
    
    public function createAccount(Request $request, User $user)
    {
        try {
            // Define validation rules
            $rules = [
                'first_name' => ['required', 'string', new SingleWordRule, new ParamAlready('first_name')],
                'surname' => ['required', 'string', new SingleWordRule, new ParamAlready('surname')],
                'phone_number' => ['required', 'numeric', 'digits:11', 'unique:users,phone_number,' . Auth::user()->id . ',id'],
            ];
    
            // Validate the incoming request
            $validatedData = Validator::make($request->all(['first_name', 'surname', 'other_name', 'phone_number']), $rules, [
                'first_name.required' => 'First name cannot be empty',
                'surname.required' => 'Surname cannot be empty',
                'phone_number.required' => 'Phone number is required',
                'phone_number.numeric' => 'Phone number accepts numeric characters only',
                'phone_number.digits' => 'Phone number must be exactly 11 (eleven) digits',
                'phone_number.unique' => 'Phone number is attached to another account',
            ]);
    
            // Check for validation errors
            if ($validatedData->fails()) {
                return response()->json([
                    'status' => 'false',
                    'data' => [
                        'message' => "Validation failed",
                        'error' => $validatedData->errors(),
                    ]
                ], 400);
            }
    
            // Clean the input
            $request = Purify::clean($request->all(['first_name', 'surname', 'other_name', 'phone_number']));
    
            // Handle customer wallet setup (or any external call)
            $strowallet = Helpers::handleCustomerStrowallet($request);
    
            // Check if wallet setup was successful
            if (!( $strowallet['success'] == "true")) {
                return response()->json([
                    'status' => 'false',
                    'data' => [
                        'message' => "Account setup failed, Try again",
                        'error' => $strowallet
                    ]
                ], 400);
            }
    
            // Find the authenticated user and update the profile
            $userData = $user->find(Auth::user()->id);
            $profile = $request;
            $profile['complete'] = 1;
            $profile['account_number'] = $strowallet['account_number'];
            $profile['bank_name'] = $strowallet['bank_name'];
            $profile['account_name'] = $strowallet['account_name'];
    
            if ($userData->update($profile)) {
                return response()->json([
                    'status' => 'true',
                    'data' => [
                        'message' => "Profile Update Successful",
                        'user' => Auth::user()->refresh(),
                    ]
                ], 200);
            }
    
            // In case the update fails
            return response()->json(['status' => 'false', 'data' => ['message' => "An error occurred"]], 400);
    
        } catch (\Exception $e) {
            // Catch any errors and return a response
            return response()->json([
                'status' => 'false',
                'data' => [
                    'message' => "An unexpected error occurred",
                    'error' => $e->getMessage(),
                ]
            ], 500); // Internal Server Error status code
        }
    }

    
            // 'password' => ['required', 'confirmed', Password::min(6)->mixedCase()->numbers()->symbols()],
    public function profileLevel1(Request $request, User $user) {
        $validatedData = Validator::make($request->all(['username','password','password_confirmation']), [
            'username' => ['nullable', 'string', 'regex:/^[A-Za-z0-9_]+$/', 'unique:users,username', new ParamAlready('username')], 
            'password' => ['required', 'confirmed', Password::min(6)],
            'password_confirmation' => 'required|min:6|same:password',
        ],[
            'username.regex' => 'Username cannot contain spaces!',
            'username.unique' => 'Account with user already exists!',
            
            'referral.regex' => 'Referral cannot contain uppercase, symbols, spaces or digits!',
            
            'password.required' => 'Password is required for security!',
            'password.confirmed' => 'Password & confirm password should be same!',
            'password.min' => 'Password should have at least 6 characters',
            'password_confirmation.required' => 'Password confirmation is required!',
            'password_confirmation.min' => 'Confirm password should have at least 6 characters!',
            'password_confirmation.same' => 'Confirm password does not match password!',
        ]);
        
        isset($request->referral) && !empty($request->referral) ? $rules['referral'] = 'exists:users,code' : null;

        if ($validatedData->fails()) {
            return response()->json([
                'status'=>'false',
                'data' => [
                    'message' => "Validation failed",
                    'error' => $validatedData->errors()
                ]
            ],400);
        }

        $userUpdate = $user->where('email', Auth::user()->email)->first();
        $username = strtolower($request->username ?? '');

        if ($username == "") {
            $username = strtolower(Auth::user()->first_name . Auth::user()->surname);
    
            $originalUsername = $username;
            $counter = 1;
        
            while (User::where('username', $username)->exists()) {
                $username = $originalUsername . str_pad($counter, 2, '0', STR_PAD_LEFT);
                $counter++;
            }
        }
        
        // dd($username);

        $password = Hash::make($request->password);
        $referral = $request->referral == "" ? "" : $request->referral;
        
        $userData = ['username'=>$username,'password'=>$password,'referral'=>$referral];
        $generatedCode = strtoupper(Str::random(8));
        while (User::where('code', $generatedCode)->exists()) {
            $generatedCode = strtoupper(Str::random(8));
        }
        $userData['code'] = $generatedCode;
        if ($userUpdate->update($userData)) {
            return response()->json([
                'status'=>'true',
                'data' => [
                    'message' => "Profile Updated Successfully",
                    'user' => Auth::user()->refresh()
                ]
            ],200);
        }

        return response()->json(['status'=>'false','data' => ['message' => "An error occurred"]],400);
    }

    public function setTransactionPin(Request $request, User $user) {
        $validatedData = Validator::make($request->all(['transaction_pin']), [
            'transaction_pin' => 'required|numeric|digits:4',
        ],[
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
        
        

        $userUpdate = $user->where('id', Auth::user()->id)->first();
        $userData['transaction_pin'] = Hash::make($request->transaction_pin);
        // dd($userData, Auth::user()->id, $userUpdate);
        if ($userUpdate->update($userData)) {
            return response()->json([
                'status'=>'true',
                'data' => [
                    'message' => "Transaction Pin Successfully Set",
                    'user' => Auth::user()->refresh()
                ]
            ],200);
        }

        return response()->json(['status'=>'false','data' => ['message' => "An error occurred"]],400);
    }
    
    public function completeSetUpAndconfirmTransactionPin(Request $request) {
        $user = Auth::user();
        // dd($request->transaction_pin, Auth::user(), Hash::check($request->transaction_pin, $user->transaction_pin));
        // Validate the transaction pin
        // $validatedData = $request->validate([
        //     'transaction_pin' => ['required', 'numeric', 'digits:4'],
        // ], [
        //     'transaction_pin.required' => 'Transaction pin cannot be empty',
        //     'transaction_pin.numeric' => 'Only digits allowed for transaction pin',
        //     'transaction_pin.digits' => 'Transaction pin must have 4 (four) digits',
        // ]);
        
        if(empty($request->transaction_pin)){
            return response()->json([
                'status' => 'false',
                'data' => [
                    'message' => 'Transaction pin required.',
                ]
            ], 400);
        }
        
        $user = Auth::user();
        // dd($user); 
    
        // Check if the transaction pin matches
        if (!Hash::check($request->transaction_pin, $user->transaction_pin)) { 
            return response()->json([
                'status' => 'false',
                'data' => [
                    'message' => 'Invalid transaction pin.',
                ]
            ], 400);
        }
        
        $allowMail = $user->complete == 1 ? false : true;
    
        
        $user->complete = 1;  
        
        if($user->save()){
            if($allowMail){
                Mail::to($user->email)->send(new WelcomeMail($user));
            }
            
            return response()->json([
                'status' => 'true',
                'data' => [
                    'message' => "Transaction Pin Confirmed",
                ]
            ], 200);
        }
    
        
    }


    public function forgotTransactionPin(Request $request, User $user) {
        $token = generateOTP();
        $email = Auth::user()->email;
        $userData = [
            'email_token' => serialize(['token'=>$token,'expire_at'=>now()->addMinutes(10)])
        ];
        if ($user->where('email', $email)->update($userData)) {
            $data = ['token'=>$token];
            if (Mail::to($email)->send(new ForgotTransactionPin('emails.forgot-transaction-pin', $data, 'Billvault :: Reset Transaction Pin'))) {    
                return response()->json([
                    'status'=>'true',
                    'data' => [
                        'message' => "An OTP has been sent to your email address " . $email,
                    ]
                ],200);
            }
        }
        return response()->json([
            'status'=>'false',
            'data' => [
                'message' => "Failed to send dispatch email to account",
            ]
        ],400);
    }

    public function forgotPinOTPConfirm (Request $request, User $user) {
        $validatedData = Validator::make($request->all(['transaction_pin','pin_confirmation']), [
            'transaction_pin' => 'required|numeric|digits:4',
            'pin_confirmation' => 'required|same:transaction_pin',
        ],[
            'transaction_pin.required' => 'Transaction pin cannot be empty',
            'transaction_pin.numeric' => 'Only digits allowed for transaction pin',
            'transaction_pin.digits' => 'Transaction pin must have 4 (four) digits',
            'pin_confirmation.required' => 'Pin confirmation cannot be empty',
            'pin_confirmation.same' => 'Pin confirmation must be same as new pin.'
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

        $userUpdate = $user->where('email', Auth::user()->email)->where('username', Auth::user()->username)->first();
        $userData['transaction_pin'] = Hash::make($request->transaction_pin);
        if ($userUpdate->update($userData)) {
            return response()->json([
                'status'=>'true',
                'data' => [
                    'message' => "Transaction Pin Successfully Set",
                    'user' => Auth::user()->refresh()
                ]
            ],200);
        }

        return response()->json(['status'=>'false','data' => ['message' => "An error occurred"]],400);
    }

    public function confirmPinOtp(Request $request, User $user) {
        $validatedData = Validator::make($request->all(['token']), [
            'token' => 'required|numeric|digits:4'
        ],[
            'token.required' => 'Email verification token is required!',
            'token.numeric' => 'Token can only contain numeric characters!',
            'token.digits' => 'Token must be 4 (four) digits in length!'
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
        $userdata = $user->where('email', Auth::user()->email)->first();
        $tokenRaw = unserialize($userdata->email_token);

        if (!isset($tokenRaw['token'], $tokenRaw['expire_at'])) {
            return response()->json(['status'=>'false','data' => ['message' => "Verification token not set"]],400);
        }
        
        $token = $tokenRaw['token'];
        $expire_at = strtotime($tokenRaw['expire_at']);
        if ($token != $request->token) {
            return response()->json(['status'=>'false','data' => ['message' => "Invalid Verification Token"]],400);

        } elseif (time() > $expire_at) {
            return response()->json(['status'=>'false','data' => ['message' => "Token Expired"]],400);

        }

        return response()->json([
            'status'=>'true',
            'data' => [
                'message' => 'OTP confirmed successfully',
                'user' => Auth::user()->refresh()
            ]
        ],200);
    }

    public function confirmTransactionPin(Request $request) {
        $validatedData = Validator::make($request->all(['transaction_pin']), [
            'transaction_pin' => ['required','numeric','digits:4', new TransactionPin],
        ],[
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
        
        $user = Auth::user();

        if (Hash::check($request->transaction_pin, Auth::user()->transaction_pin)) {
            Mail::to($user->email)->send(new WelcomeMail($user));
            return response()->json([
                'status'=>'true',
                'data' => [
                    'message' => "Transaction Pin Confirmed",
                ]
            ],200);
        }
    }
    
    public function pinLogin(Request $request) {
        $validatedData = Validator::make($request->all(['transaction_pin']), [
            'transaction_pin' => ['required','numeric','digits:4', new TransactionPin],
        ],[
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

        if (Hash::check($request->transaction_pin, Auth::user()->transaction_pin)) {
            return response()->json([
                'status'=>'true',
                'data' => [
                    'message' => "Transaction Pin Confirmed",
                    'user' => Auth::user()->refresh()
                ]
            ],200);
        }
    }

    public function bvnVerification(Request $request, User $user) {
        $validatedData = Validator::make($request->all(['bvn']), [
            'bvn' => ['required','numeric','digits:11','unique:users,bvn',new ParamAlready('bvn')],
        ],[
            'bvn.required' => 'BVN cannot be empty',
            'bvn.numeric' => 'Only digits allowed for BVN',
            'bvn.digits' => 'BVN must have 11 (eleven) digits',
            'bvn.unique' => 'BVN already attached to an account',
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

        try {
            $request = Purify::clean($request->all());
            $bvn = $request['bvn'];
            if (config("app.env") != "local") {
                $response = Helpers::verifyBVN($bvn);
                $profile = $response['data'];
            }else {
                $bvn .= rand(1000,9999);
                $faker = Faker::create();
                $profile = [
                    'firstname' => $faker->firstName,
                    'surname' => $faker->lastName,
                    'other_name' => $faker->firstName,
                    'phone_number' => $faker->numerify('###########'),
                    'dob' => $faker->date,
                ];
            }
            $userData = $user->find(Auth::user()->id);
            $userData->update(['bvn'=>$bvn]);

            // Handle successful response
            return response()->json([
                'success' => "true",
                'data' => $profile,
            ],200);
        } catch (\Exception $e) {
            // Handle error response
            return response()->json([
                'success' => "false",
                'data' => [
                    'message' => "Unable to verify BVN",
                    'error' => json_decode($e->getMessage()),
                ]
            ],400);
        }
    }

    public function profileLevel2(Request $request, User $user) {
        $rules = [
            'first_name' => ['required','string', new SingleWordRule, new ParamAlready('first_name')],
            'surname' => ['required','string', new SingleWordRule, new ParamAlready('surname')],
            'other_name' => [new SingleWordRule, new ParamAlready('other_name')],
            'phone_number' => ['required','numeric','digits:11','unique:users,phone_number,' . Auth::user()->id . ',id'],
            'dob' => ['required','date'],
            'gender' => ['required','string'],
        ];
        isset($request->referral) && !empty($request->referral) ? $rules['referral'] = ['exists:users,username','not_in:' . Auth::user()->username] : null;

        $validatedData = Validator::make($request->all(['first_name','surname','other_name','phone_number','dob','gender','referral']), $rules,[
            'first_name.required' => 'First name cannot be empty',
            'surname.required' => 'Surname cannot be empty',
            'other_name.required' => 'Other name cannot be empty',
            'phone_number.required' => 'Phone number is required',
            'phone_number.numeric' => 'Phone number accepts numeric characters only',
            'phone_number.digits' => 'Phone number must be exactly 11 (eleven) digits',
            'phone_number.unique' => 'Phone number is attached to another account',
            'dob.required' => 'Date of birth is required',
            'dob.date' => 'Date of birth must be a valid date',
            'gender.required' => 'Gender cannot be empty',
            'referral.exists'=> 'There is no account with username: ' . $request['referral'] ?? "",
            'referral.not_in'=> 'Unable to add account as referral',
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



        $request = Purify::clean($request->all(['first_name','surname','other_name','phone_number','dob','gender']));


        if ($paystack = Helpers::handleCustomerPaystack($request)) {
            if (!(isset($paystack->status) && $paystack->status == "true")) {
                return response()->json([
                    'status'=>'false',
                    'data' => [
                        'message' => "Account setup failed, Try again",
                    ]
                ],400);
            }
        }else {
            return response()->json([
                'status'=>'false',
                'data' => [
                    'message' => "Account setup failed, Try again",
                ]
            ],400);
        }

        $userData = $user->find(Auth::user()->id);
        $profile = $request;
        $profile['gender'] = ucfirst(strtolower($request['gender']));
        $profile['account_number'] = $paystack->data->account_number;
        $profile['paystack_id'] = ['id'=>$paystack->data->customer->id,'cus_code'=>$paystack->data->customer->customer_code];
        $profile['complete'] = 1;

        if ($userData->update($profile)) {
            return response()->json([
                'status'=>'true',
                'data' => [
                    'message' => "Profile Update Successful",
                    'user' => Auth::user()->refresh()
                ]
            ],200);
        }

        return response()->json(['status'=>'false','data' => ['message' => "An error occurred"]],400);
    }

    public function getUsersAndReferralCount(){
        
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        
        $userId = $user->code;

        if (!$userId) {
            $generatedCode = strtoupper(Str::random(8));
            while (User::where('code', $generatedCode)->exists()) {
                $generatedCode = strtoupper(Str::random(8));
            }
            $user->code = $generatedCode;
            $user->save();
            // return response()->json(['error' => 'Unauthenticated'], 401);
        }

        
        $referralCount = User::where('referral', $user->code)->count();

        
        return response()->json(['referral_count' => $referralCount, 'code' => $user->code], 200);
    }
    
    public function getActiveLevels(){
        $kycLevels = KycLevel::get(['id', 'title', 'details', 'maximum_balance', 'maximum_transfer']);
        $user = Auth::user();
        return response()->json([
            'status'=>'true',
            'current_level' => $user->account_level,
            'data' => [
                'message' => "KYC Levels",
                'data' => $kycLevels,
            ]
        ],200);
        
    }
    
    public function level_two_status(){
        $user_id = Auth::user()->id;
        $tierTwo = TierTwo::where('user_id', $user_id)->orderBy('created_at', 'desc')->first();
    
        if ($tierTwo) {
            return response()->json([
                'status' => true,
                'message' => '1 = Approved, 0 = Pending, 2 = Rejected',
                'data' => [
                    'status' => $tierTwo->status,
                    'rejected_reason' => $tierTwo->status == 2 ? $tierTwo->rejection_reason : null,
                ]
                
            ]);
        }

        return response()->json([
            'status' => 'Yet to submit details'
        ]);
    }
    
    public function level_three_status(){
        $user_id = Auth::user()->id;
        $tierTwo = TierThree::where('user_id', $user_id)->orderBy('created_at', 'desc')->first();
    
        if ($tierTwo) {
            return response()->json([
                'status' => true,
                'message' => '1 = Approved, 0 = Pending, 2 = Rejected',
                'data' => [
                    'status' => $tierTwo->status,
                    'rejected_reason' => $tierTwo->status == 2 ? $tierTwo->rejection_reason : null
                ]
                
            ]);
        }

        return response()->json([
            'status' => 'Yet to submit details'
        ]);
    }
    
    public function createTier2(Request $request){
        $rules = [
            'date_of_birth' => 'required',
            'id_front' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'bvn' => 'required|digits:11',
            'id_type' => 'required'
        ];
    
        $messages = [
            'date_of_birth.required' => 'Date of birth is required',
            'id_type.required' => 'ID type is required',
            'id_front.required' => 'Front ID image is required',
            'bvn.required' => 'BVN is required',
            'bvn.digits' => 'BVN must be exactly 11 digits'
        ];
    
        $validatedData = Validator::make($request->all(), $rules, $messages);
    
        if ($validatedData->fails()) {
            return response()->json([
                'status' => 'false',
                'data' => [
                    'message' => "Validation failed",
                    'error' => $validatedData->errors()
                ]
            ], 400);
        }
        
        $user = Auth::user();
        
        $myId = $user->id;
        
        if (!$user) { 
            return response()->json(['error' => 'User not found'], 404);
        }
        
        $existingTierTwo = TierTwo::where('user_id', $myId)->whereIn('status', [0, 1])->first();
    
        if ($existingTierTwo) {
            return response()->json([
                'status' => 'false',
                'data' => [
                    'message' => "Submission not allowed. You already have a Tier 2 record with pending or approved status.",
                ],
            ], 403);
        }
    
        // Save the images
        $frontPath = $request->file('id_front')->storeAs('ids', Str::uuid() . '.' . $request->file('id_front')->extension(), 'public');
        $backPath = $request->hasFile('id_back')
        ? $request->file('id_back')->storeAs('ids', Str::uuid() . '.' . $request->file('id_back')->extension(), 'public')
        : null;
    
        // Create new Tier 2 entry
        
        TierTwo::create([
            'user_id' => "$user->id",
            'date_of_birth' => "$request->date_of_birth",
            'bvn' => "$request->bvn",
            'id_front' => "$frontPath",
            'id_back' => "$backPath",
            'id_type' => "$request->id_type",
        ]);
    
        return response()->json([
            'status' => 'true',
            'message' => 'Tier 2 information created successfully'
        ], 200);
    }
    
    public function createTier3(Request $request){
        $rules = [
            'house_address' => 'required',
            'utility_bill' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ];
    
        $messages = [
            'house_address.required' => 'House address required',
            'utility_bill.required' => 'Utility bill image is required',
        ];
    
        $validatedData = Validator::make($request->all(), $rules, $messages);
    
        if ($validatedData->fails()) {
            return response()->json([
                'status' => 'false',
                'data' => [
                    'message' => "Validation failed",
                    'error' => $validatedData->errors()
                ]
            ], 400);
        }
        
        $user = Auth::user();
        
        $myId = $user->id;
        
        if (!$user) { 
            return response()->json(['error' => 'User not found'], 404);
        }
        
        $existingTierTwo = TierThree::where('user_id', $myId)->whereIn('status', [0, 1])->first();
    
        if ($existingTierTwo) {
            return response()->json([
                'status' => 'false',
                'data' => [
                    'message' => "Submission not allowed. You already have a Tier 3 record with pending or approved status.",
                ],
            ], 403);
        }
    
        // Save the images
        $utility_bill = $request->file('utility_bill')->storeAs('ids', Str::uuid() . '.' . $request->file('utility_bill')->extension(), 'public');
        
    
        // Create new Tier 3 entry
        $tier_three = TierThree::create([
            'user_id' => "$user->id",
            'house_address' => "$request->house_address",
            'utility_bill' => "$utility_bill",
        ]);
        
        if($tier_three){
            return response()->json([  
            "status"=> "true",
            "data"=> [
                "message"=> "Tier three information submitted successfully"
                ]
            ],200);
        }
        
    }

    public function profileImage(Request $request) {
        $validatedData = Validator::make($request->all(['image']), [
            'image' => ['required','image','mimes:jpeg,png,jpg,gif','max:20120']
        ],[
            'image.required' => 'You must select profile image',
            'image.image' => 'Field must be an image',
            'image.mimes' => 'Field accepts jpeg, jpg, png, and webp only',
            'image.max' => 'Profile image max size is 2mb',
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
        $file = $request->file('image');
        
        $uniqueName = Auth::user()->username.Carbon::now()->timestamp. '.' . $file->getClientOriginalExtension();
        if ($raw = $file->storeAs('public/images',$uniqueName)) {
            $path = storage_path('app/'.$raw); // live
            $new_path = '/storage'.str_replace(storage_path(), '', $path); // live
            $prevPath = base_path(Auth::user()->profile); // live
            // $prevPath = $new_path; // live
            // $path = public_path('storage'.str_replace("public","",$raw)); // local
            // $new_path = str_replace(public_path(), '', $path); // local
            // $prevPath = public_path(Auth::user()->profile); // local
            $newImage = Image::make($path)->resize(100,100);
            if (!(str_contains(Auth::user()->profile, "default.jpg"))) {
                if (File::exists($prevPath)) {
                    File::delete($prevPath);
                }
            }
            if ($newImage->save($path)) {
                // Retrieve the currently authenticated user
                $user = Auth::user();
                // Set the profile property to $path
                $user->profile = str_replace("\\", "/", $new_path);
                // Save the user to the database
                if ($user->save()) {
                    return response()->json(["status"=> "true","data"=> ["message"=> "Profile image updated successfully","data"=>Auth::user()->refresh()]],200);
                }
                File::delete($path);
            }
        }

        return response()->json(["status"=> "false","data"=> ["message"=> "Failed to upload image, try again!"]],400);

    }
    
    // Virtual Card Features x
    
    public function createVirtualCardAccount(Request $request){
        $user = Auth::user();
        $user_id = $user->id;
        $account_level= $user->account_level;
        $cardName = $user->first_name." ".$user->other_name." ".$user->surname;
        $email = $user->email;
        $rules = [];
        
        $combinedData = [];

        if ($account_level == 1) {
            $data = Purify::clean($request->all());
            $combinedData = array_merge($data, [
                'first_name' => $user->first_name ,
                'surname' => $user->surname,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
            ]);
            $rules = [
                'dateOfBirth' => 'required',
                'idType' => 'required|string',
                'idNumber' => 'required|string',
                'idImage' => 'required',
                'userPhoto' => 'required',
                'address' => 'required|string',
                'houseNumber' => 'required|string',
                'state' => 'required|string',
                'zipcode' => 'required|string',
                'country' => 'required|string',
                'city' => 'required|string',
            ];
            
            if ($request->hasFile('idImage')) {
                $idImagePath = $request->file('idImage')->storeAs('ids', Str::uuid() . '.' . $request->file('idImage')->extension(), 'public');
                $idImageUrl = url(Storage::url("app/public/".$idImagePath));
                $combinedData['idImage'] = $idImageUrl;
            }
        
            if ($request->hasFile('userPhoto')) {
                $userPhotoPath = $request->file('userPhoto')->storeAs('images', Str::uuid() . '.' . $request->file('userPhoto')->extension(), 'public');
                // $userPhotoPath = $request->file('userPhoto')->store('uploads/images', 'public');
                $userPhotoUrl = url(Storage::url("app/public/".$userPhotoPath));
                $combinedData['userPhoto'] = $userPhotoUrl;
            }
            
        } elseif ($account_level == 2) {
            $data = Purify::clean($request->all());
            $tierTwoData = TierTwo::where('user_id', $user_id)->where('status', 1)->firstOrFail();
            $combinedData = array_merge($data, [
                'first_name' => $user->first_name ,
                'surname' => $user->surname,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'userPhoto' => "https://api.paypointapp.africa/".$user->profile,
                'dateOfBirth' => $user->dob,
                'idType' => $tierTwoData->id_type ?? 'BVN',
                'idImage' => "https://api.paypointapp.africa/storage/app/public/".$tierTwoData->id_front,
            ]);
            
            
            $rules = [
                'address' => 'required|string',
                'houseNumber' => 'required|string',
                'state' => 'required|string',
                'zipcode' => 'required|string',
                'country' => 'required|string',
                'city' => 'required|string',
                'idNumber' => 'required|string',
            ];
        } elseif ($account_level == 3) {
            $data = Purify::clean($request->all());
            $tierTwoData = TierTwo::where('user_id', $user_id)->where('status', 1)->firstOrFail();
            $combinedData = array_merge($data, [
                'first_name' => $user->first_name ,
                'surname' => $user->surname,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'userPhoto' => "https://api.paypointapp.africa/".$user->profile,
                'dateOfBirth' => $user->dob,
                'idType' => $tierTwoData->id_type ?? 'BVN',
                'idImage' => "https://api.paypointapp.africa/storage/app/public/".$tierTwoData->id_front,
            ]);
            $rules = [
                'houseNumber' => 'required|string',
                'state' => 'required|string',
                'zipcode' => 'required|string',
                'country' => 'required|string',
                'city' => 'required|string',
                'idNumber' => 'required|string',
            ];
        }
    
        
        $validatedData = Validator::make($request->all(), $rules);
        
        if ($validatedData->fails()) {
            return response()->json([
                'status'=>'false',
                'data' => [
                    'message' => "Validation failed",
                    'error' => $validatedData->errors()
                ]
            ],400);
        }
        
        $card_charges = getSettings('card_charges','card_charges');
        $card_addon = getSettings('card_charges','card_addon');
        $card_deposit = getSettings('card_charges','deposit');
        $exchange_rate = getSettings('exchange_rate','ngn_usd');
        
        $charges_amount = ($card_charges / 100) * $card_deposit;
        
        $total_card_charges = $charges_amount + $card_addon;
        
        $total_charges= $total_card_charges + $card_deposit;
        $charges_in_naira = $exchange_rate * $total_charges;
        
        
        
        $wallet = new WalletController();
        $dedRes = json_decode($wallet->callWalletUpdate($charges_in_naira, 'id', Auth::user()->id, '-'));
        
        if ($dedRes->code !== 1) {
            return response()->json([
                'status' => 'false',
                'data'=> [
                    'message' => 'Payment failed',
                    'charges' => "Card Charges: $total_card_charges USD, Initial Deposit: $card_deposit USD, Exchange Rate: $exchange_rate, Amount to deduct: $charges_in_naira NGN",
                    'error'=> $dedRes->msg
                ]
            ],400);
        }
        
        
        $virtualCardAccount = Helpers::handleVirtualCardAccount($combinedData);
        
        // dd($virtualCardAccount);
        
        if (!( $virtualCardAccount['success'] == "true")) {
            
            if($virtualCardAccount['message']== "a card user with this email already exists"){
                $virtualCard = Helpers::createLiveVirtualCard($user);
                
                if (isset($virtualCard['error'])) {
                    $dedRes = json_decode($wallet->callWalletUpdate($charges_in_naira, 'id', Auth::user()->id, '+'));
                    return response()->json([
                        'status' => 'false',
                        'data' => $virtualCard
                    ], 400);
                }
        
                if (!( $virtualCard['success'] == "true")) {
                    $dedRes = json_decode($wallet->callWalletUpdate($charges_in_naira, 'id', Auth::user()->id, '+'));
                    return response()->json([
                        'status'=>'false',
                        'data' => $virtualCard
                    ],400); 
                }
                else{
                    $response = $virtualCard['response'];
                    $userId = $user->id;
                    // Create a new CardDetail record
                    
                    $cardDetail = new CardDetail();
                    $cardDetail->name_on_card = $response['name_on_card'];
                    $cardDetail->card_id = (string)$response['card_id'];
                    $cardDetail->card_created_date = $response['card_created_date'];
                    $cardDetail->card_type = $response['card_type'];
                    $cardDetail->card_brand = $response['card_brand'];
                    $cardDetail->card_user_id = $response['card_user_id'];
                    $cardDetail->reference = $response['reference'];
                    $cardDetail->card_status = $response['card_status'];
                    $cardDetail->customer_id = $response['customer_id'];
                    $cardDetail->user_id = $userId; // Associate the card with the user
                    
                    // Save the card details
                    $cardDetail->save();
                    $user->is_with_card = 1;
                    $user->save();
                    
                    TransactionLog::dispatch(Auth::user()->id,'Card Creation',$charges_in_naira,$response['reference'],'successful',$email,json_encode($virtualCard['response']));
                    return response()->json(["data" => $virtualCard]);
                }
                
            return response()->json([
                'status'=>'false',
                'data' => $virtualCardAccount
            ],400);
        }
        
        
        }else{
            
            $virtualCard = Helpers::createLiveVirtualCard($user);
            
            if (isset($virtualCard['error'])) {
                $dedRes = json_decode($wallet->callWalletUpdate($charges_in_naira, 'id', Auth::user()->id, '+'));
                return response()->json([
                    'status' => 'false',
                    'data' => $virtualCard
                ], 400);
            }
        
            if (!( $virtualCard['success'] == "true")) {
                $dedRes = json_decode($wallet->callWalletUpdate($charges_in_naira, 'id', Auth::user()->id, '+'));
                return response()->json([
                    'status'=>'false',
                    'data' => $virtualCard
                ],400); 
            }
            else{
                $response = $virtualCard['response'];
                $userId = $user->id;
                // Create a new CardDetail record
                
                $cardDetail = new CardDetail();
                $cardDetail->name_on_card = $response['name_on_card'];
                $cardDetail->card_id = (string)$response['card_id'];
                $cardDetail->card_created_date = $response['card_created_date'];
                $cardDetail->card_type = $response['card_type'];
                $cardDetail->card_brand = $response['card_brand'];
                $cardDetail->card_user_id = $response['card_user_id'];
                $cardDetail->reference = $response['reference'];
                $cardDetail->card_status = $response['card_status'];
                $cardDetail->customer_id = $response['customer_id'];
                $cardDetail->user_id = $userId; // Associate the card with the user
                
                // Save the card details
                $cardDetail->save();
                $user->is_with_card = 1;
                $user->save();
                
                TransactionLog::dispatch(Auth::user()->id,'Card Creation',$charges_in_naira,$response['reference'],'successful',$email,json_encode($virtualCard['response']));
                return response()->json(["data" => $virtualCard]);
            }
            
        }

        
    }
    
    public function createdVirtualCard(){
        $user = Auth::user();
        $userId = $user->id;
        $virtualCard = Helpers::createdVirtualCard($user);
        
        if (!( $virtualCard['success'] == "true")) {
            
            return response()->json([
                'status'=>'false',
                'data' => $virtualCard
            ],400); 
        }
        else{
            $response = $virtualCard['response'];
            // Create a new CardDetail record
            
            $cardDetail = new CardDetail();
            $cardDetail->name_on_card = $response['name_on_card'];
            $cardDetail->card_id = (string)$response['card_id'];
            $cardDetail->card_created_date = $response['card_created_date'];
            $cardDetail->card_type = $response['card_type'];
            $cardDetail->card_brand = $response['card_brand'];
            $cardDetail->card_user_id = $response['card_user_id'];
            $cardDetail->reference = $response['reference'];
            $cardDetail->card_status = $response['card_status'];
            $cardDetail->customer_id = $response['customer_id'];
            $cardDetail->user_id = $userId; 
            
            // Save the card details
            $cardDetail->save();
            $user->is_with_card = 1;
            $user->save();
            return response()->json(["data" => $virtualCard]);
        }
    }
    
    public function fundCard(Request $request){
        $rules = [
            'amount' => 'required|numeric|min:3',
            'transaction_pin' => ['required','numeric','digits:4', new TransactionPin],
        ];
        
        $validatedData = Validator::make($request->all(), $rules);
        
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
        $cardDetail = $user->cardDetails()->where('user_id', $user->id)->first();
        if($cardDetail){
            $card_id = $cardDetail->card_id;
            $amount = $request->amount;
            $status = $cardDetail->card_status;
            $top_up_charges = getSettings('card_charges','top_up');
            $total_in_dollar = $amount + $top_up_charges;
            $exchange_rate = getSettings('exchange_rate','ngn_usd');
            $charges_in_naira = $exchange_rate * $total_in_dollar;
            
            
            $wallet = new WalletController();
            $dedRes = json_decode($wallet->callWalletUpdate($charges_in_naira, 'id', Auth::user()->id, '-'));
            
            if ($dedRes->code !== 1) {
                return response()->json([
                    'status' => 'false',
                    'data'=> [
                        'message' => "Payment failed, your wallet will be charged with $charges_in_naira NGN",
                        'error'=> $dedRes->msg
                    ]
                ],400);
            }
            
            
            $data = [
                    'card_id' => $card_id,
                    'amount'  => $amount
                ];
            $fundCard = Helpers::fundCard($data);
            
            // dd($fundCard['apiresponse']['']);
            if (isset($fundCard['error'])) {
                // Handle the error case
                $dedRes = json_decode($wallet->callWalletUpdate($charges_in_naira, 'id', Auth::user()->id, '+'));
                return response()->json([
                    'status' => 'false',
                    'data' => $fundCard
                ], 400); 
            } elseif (!( $fundCard['success'] == "true")) {
                // Handle if success is not true
                $dedRes = json_decode($wallet->callWalletUpdate($charges_in_naira, 'id', Auth::user()->id, '+'));
                return response()->json([
                    'status'=>'false',
                    'data' => $fundCard
                ],400); 
            }
            else{
                TransactionLog::dispatch(Auth::user()->id,'Card Funding',$charges_in_naira,$fundCard['apiresponse']['data']['reference'],'successful','',json_encode($fundCard));
                return response()->json(["data" => $fundCard]);
            }
            
            
            
        }else{
            return response()->json([
                'status'=>'false',
                'data' => 'This account has no card attached to it'
            ],400); 
        }
        
    }
    
    public function cardTransactions(){
        $user = Auth::user();
        $cardDetail = $user->cardDetails()->where('user_id', $user->id)->first();
        if($cardDetail){
            $card_id = $cardDetail->card_id;
            $transactions = Helpers::cardTransactions($card_id);
            // if (!( $transactions['success'] == "true")) {
            //     return response()->json([
            //         'status'=>'false',
            //         'data' => $transactions
            //     ],400); 
            // }
            // else{
                return response()->json(["data" => $transactions]);
            // }
            
        }else{
            return response()->json([
                'status'=>'false',
                'data' => 'This account has no card attached to it'
            ],400); 
        }
        
    }
    
    public function cardDetails(){
        $user = Auth::user();
        $cardDetail = $user->cardDetails()->where('user_id', $user->id)->first();
        if($cardDetail){
            $card_id = $cardDetail->card_id;
            $cardDetails = Helpers::cardDetails($card_id);
             
            if (!( $cardDetails['success'] == "true")) {
                
                return response()->json([
                    'status'=>'false',
                    'data' => [
                        'message' => "Validation failed",
                        'error' => $cardDetails
                    ]
                ],400); 
            }
            else{
                $freeze_status = ($cardDetail->is_card_freeze)? 'Freezed' : 'Unfreeezed';
                $cardDetails['response']['card_detail']['is_card_freeze'] = $freeze_status;
                
                return response()->json([
                    "data" => $cardDetails
                ]);
            }
            
        }else{
            return response()->json([
                'status'=>'false',
                'data' => [
                    'message' => "Validation failed",
                    'error' => 'This account has no card attached to it'
                ]
            ],400); 
        }
    }
    
    public function withdrawFromCard(Request $request){
        $rules = [
            'amount' => 'required|numeric|min:3',
            'transaction_pin' => ['required','numeric','digits:4', new TransactionPin],
        ];
        
        $validatedData = Validator::make($request->all(), $rules);
        
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
        $cardDetail = $user->cardDetails()->where('user_id', $user->id)->first();
        if($cardDetail){
            $card_id = $cardDetail->card_id;
            $amount = $request->amount;
            $status = $cardDetail->card_status;
            
            $data = [
                    'card_id' => $card_id,
                    'amount'  => $amount
                ];
            $withdrawFromCard = Helpers::withdrawFromCard($data);
            if (!( $withdrawFromCard['success'] == "true")) {
                return response()->json([
                    'status'=>'false',
                    'data' => $withdrawFromCard
                ],400); 
            }
            else{
                
                $exchange_rate = getSettings('exchange_rate','usd_ngn');
                
                $amount_to_credit = $exchange_rate * $amount;
                $reference = $withdrawFromCard['reference'];
                
                if($withdrawFromCard['status_code'] == 400){
                    return response()->json([
                        'status'=>'false',
                        'data' => $withdrawFromCard['response']
                    ],400);
                }else{
                    $card_status = $withdrawFromCard['response']['data']['status'];
                    $user_id = $user->id;
                    
                    $withdraw = new CardWithdraw();
                    $withdraw->ref_no = $reference;
                    $withdraw->amount = $amount_to_credit;
                    $withdraw->user_id = $user_id;
                    $withdraw->status = $card_status;
                    $withdraw->save();
                    TransactionLog::dispatch(Auth::user()->id,'Card Withdrawal',$amount_to_credit,$withdrawFromCard['reference'],$card_status,'',json_encode($withdrawFromCard['response']['data']));
                    if($card_status == 'success'){
                        $wallet = new WalletController();
                        $dedRes = json_decode($wallet->callWalletUpdate($amount_to_credit, 'id', $user_id, '+'));
                        
                        if ($dedRes->code !== 1) {
                            return response()->json([
                                'status' => 'false',
                                'data'=> [
                                    'message' => 'Payment failed',
                                    'error'=> $dedRes->msg
                                ]
                            ],400);
                        }
                    }
                }
                return response()->json(["data" => $withdrawFromCard['response']]);
            }
            
        }else{
            return response()->json([
                'status'=>'false',
                'data' => 'This account has no card attached to it'
            ],400); 
        }
    }
    
    public function withdrawStatus(){
        $pendingWithdrawals = CardWithdraw::all();
        foreach($pendingWithdrawals as $pending){
            $ref = $pending->ref_no;
            $user_id = $pending->user_id;
            $status = Helpers::withdrawalStatus($ref);
            if($status['status'] == 'Completed'){
                $amount = $status['amount'];
                
                $exchange_rate = getSettings('exchange_rate','usd_ngn');
                    
                $amount_to_credit = $exchange_rate * $amount;
                
                $user = User::find($user_id); 
    
                // Update the user's balance
                $user->balance += $amount_to_credit; 
                $user->save(); 
                
                $transactionLog = ModelsTransactionLog::where('user_id', $user_id)->where('transaction_id', $ref)->first();
            
                if ($transactionLog) {
                    // Update the status to Completed
                    $transactionLog->status = 'Successful';
                    $transactionLog->amount = $amount_to_credit;
                    $transactionLog->save(); // Save the updated status
                }
                
                $pending->delete();
            }
        }
    }
    
    public function cardCharges(){
        $card_charges = getSettings('card_charges','card_charges');
        $card_addon = getSettings('card_charges','card_addon');
        $card_deposit = getSettings('card_charges','deposit');
        $top_up_charges = getSettings('card_charges','top_up');
        $exchange_rate = getSettings('exchange_rate','ngn_usd');
        $usd_ngn = getSettings('exchange_rate','usd_ngn');
        
        return response()->json([
            'status'=>'true',
            'data' => [
                'card_charges'  => $card_charges ,
                'card_addon'    => $card_addon ,
                'ngn_usd'       => $exchange_rate,
                'usd_ngn'       => $usd_ngn,
                'initial_deposit' => $card_deposit,
                'top_up_charges' => $top_up_charges
            ]
        ],200); 
    }
    
    public function freezeCard(){
        $user = Auth::user();
        $cardDetail = $user->cardDetails()->where('user_id', $user->id)->first();
        if($cardDetail){
            $card_id = $cardDetail->card_id;
            $action = ($cardDetail->is_card_freeze)? 'unfreeze' : 'freeze';
            $freezeCard = Helpers::freezeCard($card_id, $action);
            // dd($freezeCard);
            if(!($freezeCard['status'] == "true") ){
                 return response()->json([
                    'status'=>'false',
                    'note' => 'The freeze features is only for live Card',
                    'data' =>$freezeCard
                ],400); 
            }
            else{
                
                $freeze_status = ($cardDetail->is_card_freeze)? 0 : 1;
                $cardDetail->is_card_freeze = $freeze_status;
                $cardDetail->save();
                return response()->json([
                    "data" =>$freezeCard
                ]);
            }
        }else{
            return response()->json([
                'status'=>'false',
                'data' => 'This account has no card attached to it'
            ],400); 
        }
    }
}