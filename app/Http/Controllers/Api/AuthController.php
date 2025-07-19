<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Helpers;
use App\Models\User;
use App\Models\LoginActivity;
use App\Mail\RegisterMail;
use Illuminate\Support\Str;
use App\Rules\PasswordCheck;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Stevebauman\Purify\Facades\Purify;
use Symfony\Component\Mailer\Exception\TransportException;
use Stevebauman\Location\Facades\Location;
class AuthController extends Controller
{

    public $fieldType = 'phone_number';
    public $loginCase;


    public function checkLoginCase($uid) {
        if (filter_var($uid, FILTER_VALIDATE_EMAIL)) {
            $this->fieldType = 'email';
        }elseif (preg_match('/^[0-9]+$/', $uid)) {
            $this->fieldType = 'phone_number';
        }else {
            $this->fieldType = 'username';
        }

        request()->merge([$this->fieldType => $uid]);
        return $this->fieldType;
    }

    public function login (Request $request) {
        $this->checkLoginCase($request->uid);
        // return response()->json([$this->fieldType]);
        $validator = Validator::make($request->all([$this->fieldType,'password']), [
            $this->fieldType => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status'=>'false','data' => [
                'message'=>'Authentication failed',
                'error'=>$validator->errors()->all()]
            ],401);
        }

        $credentials = $request->all([$this->fieldType,'password']);

        if (!Auth::attempt($credentials)) {
            return response()->json(['status'=>'false','data' => ['message' => 'Invalid Credentials']],401);
        }

        
        if ($request->user()) {
            if (Auth::user()->view == 1) {
                $user = $request->user();
                $tokenResult = $user->createToken('access_token')->plainTextToken;
                
                $ip_address = $request->ip();
                
                $location = Location::get($ip_address);
                
                // dd($location);
                
                if ($location) {
                
                   $lat = $location->latitude;
                   $lng = $location->longitude;
                   $country = $location->countryName;
                   $region = $location->regionName;
                   $city = $location->cityName;
                    
                    LoginActivity::create([
                        'user_id' => Auth::id(),
                        'logged_in_at' => now(),
                        'ip_address' => $request->ip(),
                        'latitude' => $lat,
                        'longitude' => $lng,
                        'country' => $country,
                        'city_name' => $city,
                        'region_name' =>  $region
                    ]);
                }
    
                return response()->json([
                    'status'=>'true',
                    'data' => [
                        'message' => "Login Successful",
                        'user' => Auth::user(),
                        'access_token' => $tokenResult,
                        'token_type' => 'Bearer'
                    ]
                ],200);
            }
        }

        return response()->json(['status'=>'false','data' => ['message' => 'Account not found']],401);
    }

    public function generateOTP() {
        $g = (string) rand(1000,9999);
        if (strlen($g) != 4) {$this->generateOTP();}
        return $g;
    }
    
    public function emailConfirm(Request $request, User $user) {
        $pure = Purify::clean($request->all(['email']));
        $us = User::where('email',$pure['email'])->where('complete',null)->where('email_verified_at',null);
        if ($us->exists()) {
            $us->delete();
        }
        $validatedData = Validator::make($request->all(['email']), [
            'email' =>  'required|email|string|unique:users,email',
        ],[
            'email.required' => 'Input a valid email address.',
            'email.email' => 'Input a valid email address.',
            'email.unique' => 'Account already exists!',
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
        $request = $pure;
        
        $userData = [
            'email' => $request['email'],
            'email_token' => serialize(['token'=>$token,'expire_at'=>now()->addMinutes(10)])
        ];

        if ($user->create($userData)) {

            $data = ['token'=>$token];
            return $this->emailConfirmOtp($token, $request['email']);
            // return response()->json([
            //     'status'=>'true',
            //     'data' => [
            //         'message' => "E-mail Submitted",
            //     ]
            // ],200);

            // if (Mail::to($request['email'])->send(new RegisterMail('emails.register', $data, 'Paypoint Africa Email Address Confirmation'))) {    
                
            // }

            $user->where('email',$request['email'])->delete();

        }

        return response()->json([
            'status'=>'false',
            'data' => [
                'message' => "An error occurred"
            ]
        ],400);
    }

    public function emailConfirmOtp($token, $email) {
        // $validatedData = Validator::make($request->all(['email','token']), [
        //     'email' =>  'required|email|string|exists:users,email',
        //     'token' => 'required|numeric|digits:4'
        // ],[
        //     'email.required' => 'Input a valid email address.',
        //     'email.email' => 'Input a valid email address.',
        //     'email.exists' => 'Account with email address not found!',
        //     'token.required' => 'Email verification token is required!',
        //     'token.numeric' => 'Token can only contain numeric characters!',
        //     'token.digits' => 'Token must be 4 (four) digits in length!'
        // ]);

        // if ($validatedData->fails()) {
        //     return response()->json([
        //         'status'=>'false',
        //         'data' => [
        //             'message' => "Validation failed",
        //             'error' => $validatedData->errors()
        //         ]
        //     ],400);
        // }

        $userdata = User::where('email', $email)->first();
        // $tokenRaw = unserialize($userdata->email_token);

        // if (!isset($tokenRaw['token'], $tokenRaw['expire_at'])) {
        //     return response()->json(['status'=>'false','data' => ['message' => "Verification token not set"]],400);

        // }
        
        // $token = $tokenRaw['token'];
        // $expire_at = strtotime($tokenRaw['expire_at']);
        // if ($token != $request->token) {
        //     return response()->json(['status'=>'false','data' => ['message' => "Invalid Verification Token"]],400);

        // } elseif (time() > $expire_at) {
        //     return response()->json(['status'=>'false','data' => ['message' => "Token Expired"]],400);

        // }

        

        $userdata->email_verified_at = now();
        $userdata->email_token = null;
        if ($userdata->save() && Auth::loginUsingId($userdata->id)) {
            $user = Auth()->user();
            $tokenResult = $user->createToken('access_token')->plainTextToken;
            return response()->json(['status'=>'true','data' => [
                'message' => 'Email address verified successfully',
                'user' => Auth::user(),
                'access_token' => $tokenResult,
                'token_type' => 'Bearer'
            ]],200);
            
        }

        return response()->json(['status'=>'false','data' => ['message' => "An error occurred"]],400);
    }

    public function forgotPassword(Request $request) {
        $validatedData = Validator::make($request->all(['email']),[
            'email' => "required|email|exists:users,email",
        ],[
            'email.required'=>'A valid email address is required.',
            'email.email'=>'Provide a valid email address.',
            'email.exists'=>'Account not found, check and try again.'
        ]);
        if ($validatedData->fails()) {
            $arr = [
                    'status'=> 'false',
                    'data' => [
                        'message' => 'Validation failed',
                        'error' => $validatedData->errors(),
                    ]
                ];
        } else {
            try {
                $response = Password::sendResetLink($request->only('email'));
                switch ($response) {
                    case Password::RESET_LINK_SENT:
                        return response()->json(['status'=>'true','data'=>["message"=>"An email has been sent to your address, Please check your inbox for the password reset button."]],200);
                    case Password::INVALID_USER:
                        return response()->json(['status'=>'false','data'=>['message'=>"Account not found, check and try again."]],401);
                    default:
                        return response()->json(["status"=> "false","data"=>["message"=> "An error occured, please try again"]],400);
                }
            } catch (TransportException $ex) {
                $arr = array("status" => "false", "message" => "An error occured, please try again", "data" => ['error' => $ex->getMessage()]);
            } catch (Exception $ex) {
                $arr = array("status" => "false", "message" => "An error occured, please try again", "data" => ['error' => $ex->getMessage()]);
            }
        }
        return response()->json($arr,401);
    }

    public function resetPassword(Request $request) {
        $validatedData = Validator::make($request->all(['current_password','password','password_confirmation']), [
            'current_password' => ['required', new PasswordCheck],
            'password' => ['required', 'confirmed', PasswordRule::min(6)->mixedCase()->numbers()->symbols()],
            'password_confirmation' => 'required|min:6|same:password',
        ],[
            'current_password.required' => 'Your current password is required!',
            'password.required' => 'Password is required for security!',
            'password.confirmed' => 'Password & confirm password should be same!',
            'password.min' => 'Password should have at least 6 characters',
            'password_confirmation.required' => 'Password confirmation is required!',
            'password_confirmation.min' => 'Confirm password should have at least 6 characters!',
            'password_confirmation.same' => 'Confirm password does not match password!',
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
    
        if ($user->update(['password' => Hash::make($request->password)])) {
            return response()->json([
                'status'=> 'true',
                'data' => [
                    'message'=> 'Password reset successfully',
                    'user'=> Auth::user()->refresh()
                ]
            ],200);
        }

        return response()->json([
            'status'=> 'false',
            'data' => [
                'message'=> 'Password reset failed',
            ]
        ],400);
    }

    public function logout() {
        $user = Auth::user();
        $user->tokens->each(function ($token, $key) {
            $token->delete();
        });

        return response()->json([
            'status' => 'true',
            'data'=> ['message'=> 'Logout successful']
        ],200);
    }
    
    public function isRestricted(){
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'data' => ['message' =>'User not found'
            ]
            ], 404);
        }
        
        return response()->json([
            'status'=> 'true',
            'data' => [
                'message'=> '1 = Account Restricted, 0 - Account Not Restricted',
                'is_account_restricted' => $user->is_account_restricted
            ]
        ],200);
        
    }
    
    public function isBan(){
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'data' => ['message' =>'User not found'
            ]
            ], 404);
        }
        
        return response()->json([
            'status'=> 'true',
            'data' => [
                'message'=> '1 = Account Blocked, 0 - Account Not Blocked',
                'is_ban' => $user->is_ban
            ]
        ],200);
        
    }
    
    public function getUserDetails(Request $request)
    {
        // Validate the input
        $validatedData = Validator::make($request->all(['email','phone_number']), [
            'phone_number' => ['required'],
            'email' => ['required','email',],
        ],[
            'phone_number.required' => "Please input phone number",
            'email.required' => "E-mail is required!"
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
    

        // Fetch the user details
        $user = User::where('email', $request->email)
                    ->where('phone_number', $request->phone_number)
                    ->first();
                    
        // dd($user);

        // Check if user exists
        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        // Return user details
        return response()->json([
            'success' => true,
            'user' => $user,
        ]);
    }
}
