<?php

namespace App\Http\Controllers\Api;


use App\Helpers;
use Carbon\Carbon;
use App\Jobs\TransactionLog;
use Illuminate\Http\Request;
use App\Rules\TransactionPin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\TransactionLog AS ModelTransLog;
use Illuminate\Support\Str;
use App\Models\GiftCard;
use App\Models\GiftCardRate;
use App\Models\Country;
use Illuminate\Support\Facades\DB;

use function App\getSettings;
use function App\retErrorSetting;

class ReloadlyGiftCard extends Controller
{

    public function getCardCountries(){
        
        $data = Helpers::getCardCountries();
        
        return response()->json([
            'status'=> 'true',
            'message'=> 'Countries Retrived',
            'data'=> $data
            
        ],200);
        
    }
    
    public function getCardCountry($iso){
        
        $data = Helpers::getCardCountry($iso);
        
        return response()->json([
            'status'=> 'true',
            'message'=> 'Details Retrived',
            'data'=> $data
            
        ],200);
        
    }
    
    public function getProductCategories(){
        
        $data = Helpers::getProductCategories(); 
        
        return response()->json([
            'status'=> 'true',
            'message'=> 'Product Retrived',
            'data'=> $data
            
        ],200);
        
    }
    
    public function getCardProducts(){
        
        $data = Helpers::getCardProducts(); 
        
        return response()->json([
            'status'=> 'true',
            'message'=> 'Product Retrived',
            'data'=> $data
            
        ],200);
        
    }
    
    public function getCountryProducts($iso){
        
        $data = Helpers::getCountryProducts($iso);
        
        return response()->json([
            'status'=> 'true',
            'message'=> 'Details Retrived',
            'data'=> $data
            
        ],200);
        
    }
    
    public function getProductDetails($pid){
        
        $data = Helpers::getProductDetails($pid);
        
        return response()->json([
            'status'=> 'true',
            'message'=> 'Details Retrived',
            'data'=> $data
            
        ],200);
        
    }
    
    public function getProductRedeemInstructions($pid){
        
        $data = Helpers::getProductRedeemInstructions($pid);
        
        return response()->json([
            'status'=> 'true',
            'message'=> 'Instructions Retrived',
            'data'=> $data
            
        ],200);
        
    }
    
    public function getFXRate($currency, $amount){
        
        $data = Helpers::getFXRate($currency, $amount); 
        
        return response()->json([
            'status'=> 'true',
            'message'=> 'Rate Retrived',
            'data'=> $data
            
        ],200);
        
    }

    public function orderGiftCard(Request $request) {
        $validatedData = Validator::make($request->all(), [
            'productId' => 'required|integer|min:1',
            'countryCode' => 'required|string|size:2',
            'quantity' => 'required|integer|min:1',
            'unitPrice' => 'required|numeric|min:0.01',
            'recipientEmail' => 'required|email|max:255',
            'recipientCountryCode' => 'required|string|size:2',
            'recipientPhoneNumber' => 'required|numeric|digits_between:7,15',
            'transaction_pin' => ['required','numeric','digits:4', new TransactionPin],
        ], [
            'productId.required' => 'Product ID is required.',
            'productId.integer' => 'Product ID must be a valid integer.',
            'countryCode.required' => 'Country code is required.',
            'countryCode.size' => 'Country code must be exactly 2 characters.',
            'quantity.required' => 'Quantity is required.',
            'quantity.integer' => 'Quantity must be an integer.',
            'quantity.min' => 'Quantity must be at least 1.',
            'unitPrice.required' => 'Unit price is required.',
            'unitPrice.numeric' => 'Unit price must be a valid number.',
            'unitPrice.min' => 'Unit price must be a positive value.',
            'recipientEmail.required' => 'Recipient email is required.',
            'recipientEmail.email' => 'Enter a valid email address.',
            'recipientCountryCode.required' => 'Recipient country code is required.',
            'recipientPhoneNumber.required' => 'Recipient phone number is required.',
            'recipientPhoneNumber.numeric' => 'Recipient phone number must be numeric.',
            'recipientPhoneNumber.digits_between' => 'Phone number must be between 7 and 15 digits.',
            'transaction_pin.required' => 'Transaction pin cannot be empty',
            'transaction_pin.numeric' => 'Only digits allowed for transaction pin',
            'transaction_pin.digits' => 'Transaction pin must have 4 (four) digits',
        ]);
    
        if ($validatedData->fails()) {
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => "Validation failed",
                    'error' => $validatedData->errors()
                ]
            ], 400);
        }
    
        $data = $validatedData->validated();
        
        $response = Helpers::orderGiftCard($data);
        $amount = $data['unitPrice'];
        
        if($response['status'] == "SUCCESSFUL"){
            unset($response['balanceInfo']);
            $requestId = $response['customIdentifier'];
            $recipientEmail = $response['recipientEmail'];
            $savedData = json_encode($response);
            TransactionLog::dispatch(Auth::user()->id,'Gift Card',$amount,$requestId,'successful',$recipientEmail, $savedData);
            return response()->json([
                'status' => true,
                'data' => ['message' => "Gift Card Purchased Successful",'transaction_id' => $requestId],
                // 'response' =>  $response
            ], 200);
        }else{
            return response()->json([
                'status'=>'false',
                'data' => [
                    'message' => "Verification failed",
                    'error' => $response
                ]
            ],400);
        }
        
    }
    
    public function getRedeemCode($tid) {
        $details = ModelTransLog::where('transaction_id',$tid)->where('user_id', Auth::user()->id);
        if ($details->exists()) {
            $tranDetails = $details->first();
            $transData = json_decode($tranDetails->data);
            $transactionId = $transData->transactionId;
            $data = Helpers::getRedeemCode($transactionId);
            return response()->json([
                'status' => 'true',
                'data'=> [
                    'message' => 'Reedem Code retrieved successfully',
                    'data'=> $data 
                ]
            ],200);
        }
        return response()->json([
            'status' => 'false',
            'data'=> [
                'message' => 'Transaction Not Found.',
            ]
        ],400);
    }
    
    // Sell Gift Card
    
    public function getProducts(){
        
        $data = GiftCard::select('id', 'name', 'image')
        ->where('is_enabled', true)
        ->get(); 
        
        return response()->json([
            'status'=> 'true',
            'message'=> 'Product Retrived',
            'data'=> $data
            
        ],200);
        
    }
    
    public function getProductsCountries($pid){
        
        // $rates = GiftCardRate::with('giftCard.country') 
        //                   ->where('gift_card_id', $pid)
        //                   ->get();
    
        // $countries = $rates->map(function ($rate) {
        //     return $rate->giftCard->country;
        // });
        
        // $giftCardRates = GiftCardRate::with('country')
        //     ->where('gift_card_id', $pid)
        //     ->get()
        //     ->groupBy('country.id')
        //     ->map(function ($group) {
        //         return $group->first(); // Ensure you retrieve a single result per group
        //     });
        
        $countries = DB::table('gift_card_rates as gcr')
            ->join('countries as c', 'gcr.country_id', '=', 'c.id')
            ->where('gcr.gift_card_id', $pid)
            ->select('c.*')
            ->distinct() // Ensure unique results
            ->get();


    
        return response()->json([
            'status'=> 'true',
            'message'=> 'Details Retrived',
            'data' => $countries
        ],200);
        
    }
    
    public function calculateRate(Request $request)
    {
        // Validate the input
        $rules = [
            'amount' => 'required|numeric|min:0',
            'gift_card_id' => 'required|exists:gift_cards,id',
            'country_id' => 'required|exists:countries,id',
        ];
    
        // Custom error messages (optional)
        $messages = [
            'amount.required' => 'The amount field is required.',
            'amount.numeric' => 'The amount must be a numeric value.',
            'amount.min' => 'The amount must be greater than or equal to 0.',
            'gift_card_id.required' => 'The gift card ID is required.',
            'gift_card_id.exists' => 'The selected gift card ID does not exist.',
            'country_id.required' => 'The country ID is required.',
            'country_id.exists' => 'The selected country ID does not exist.',
        ];
    
        // Run the validation
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422); 
        }
    
        // Extract validated data
        $validated = $validator->validated();
        $amount = $validated['amount'];
        $giftCardId = $validated['gift_card_id'];
        $countryId = $validated['country_id'];
    
        // Get the GiftCardRate that matches the gift card ID and country ID
        $rate = GiftCardRate::where('gift_card_id', $giftCardId)
                            ->where('country_id', $countryId)
                            ->where('min_amount', '<=', $amount)
                            ->where('max_amount', '>=', $amount)
                            ->first();
    
        if ($rate) {
            // Calculate the payable amount
            $payableAmount = ($amount * $rate->rate);
    
            return response()->json([
                'success' => true,
                'rate' => $rate->rate,
                'payable_amount' => $payableAmount,
                'message' => 'Rate calculated successfully.'
            ]);
        }
    
        return response()->json([
            'success' => false,
            'message' => 'No rate found for the given amount range and country.'
        ]);
    }
    

    public function sellGiftCard(Request $request)
    {
        $rules = [
            'gift_card_id' => 'required|exists:gift_cards,id',
            'country_id' => 'required|exists:countries,id',
            'amount' => 'required|numeric|min:0',
            'images' => 'required|array|min:1',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    
        $messages = [
            'gift_card_id.required' => 'Gift card  is required.',
            'gift_card_id.exists' => 'The selected gift card does not exist.',
            'country_id.required' => 'Country  is required.',
            'country_id.exists' => 'The selected country does not exist.',
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a valid number.',
            'amount.min' => 'Amount must be at least 0.',
            'images.required' => 'At least one image is required.',
            'images.array' => 'Images must be provided as an array.',
            'images.min' => 'At least one image is required.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Only jpeg, png, jpg, gif, and svg formats are allowed.',
            'images.*.max' => 'Each image may not be greater than 2048 kilobytes.',
        ];
    
        
        $validator = Validator::make($request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed. Please correct the errors and try again.'
            ], 422); 
        }
    
        
        $validated = $validator->validated();
    
        // Fetch the gift card
        $giftCard = GiftCard::find($validated['gift_card_id']);
        $country = Country::find($validated['country_id']);
    
        if (!$giftCard->is_enabled) {
            return response()->json([
                'success' => false,
                'message' => 'The selected gift card is currently disabled.'
            ], 400); 
        }
    
        // Fetch the applicable rate
        $rate = GiftCardRate::where('gift_card_id', $validated['gift_card_id'])
            ->where('country_id', $validated['country_id'])
            ->where('min_amount', '<=', $validated['amount'])
            ->where('max_amount', '>=', $validated['amount'])
            ->first();
    
        if (!$rate) {
            return response()->json([
                'success' => false,
                'message' => 'No applicable rate found for the specified amount.'
            ], 400); 
        }
    
        // Calculate the payable amount
        $payableAmount = $validated['amount'] * $rate->rate;
        $requestId = Str::random(20);
        
        
        $imagePaths = [];
        foreach ($validated['images'] as $image) {
            $filename = Str::random(20) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('public/giftcards', $filename);
            $imagePaths[] = asset('/storage/app/public/giftcards/' . $filename);
        }
        
        $data = [
            'amount' => $validated['amount'],
            'payable_amount' => $payableAmount,
            'network' => $giftCard->name,
            'country' => $country->name,
            'currency' => $country->currency_code,
            'card_img' => asset($giftCard->image),
            'request_id' => $requestId,
            'country_img' => $country->flag_url,
            'transaction_images' => $imagePaths,
        ];
    
        // Store the GiftCardSale record in the database
        TransactionLog::dispatch(Auth::user()->id,'Sell Gift Card',$payableAmount,$requestId,'Pending',$request['sender_number'],json_encode($data));
        
        return response()->json([
            'status'=>'true',
            'data' => ['message' => "Gift Card Sales processed, you will be credited once confirmed.",'transaction_id' => $requestId]
        ],200);
    
    }
    
   
    public function transactionLog()
    {
        $transactions = ModelTransLog::where('type', 'Sell Gift Card')
        ->where('user_id', Auth::user()->id)
        ->orderBy('created_at', 'desc')
        ->get();
        
        return response()->json([
            'status'=> 'true',
            'message'=> 'Transaction logs',
            'data' => $transactions
        ],200);
    }
    
}
