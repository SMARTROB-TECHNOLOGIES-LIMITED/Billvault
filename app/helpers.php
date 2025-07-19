<?php

namespace App;
use App\Models\AdminSetting;
use App\Models\Beneficiary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class Helpers
{


    // const REGMAILID = 'vywj2lpvpo1l7oqz'; // Template Id for registeration mail;
    const REGMAILID = 'x2p034781eylzdrn'; // Template Id for testing soft bounce;

    public static $test_bank = 'test-bank';
    public static $live_bank = 'wema-bank';
    
    // Amadeus Flight Booking features
    
    public static function authenticate()
    {
        $client_id = getSettings('amadeus','apikey');
        $client_secret = getSettings('amadeus','secretkey');
        $response = Http::asForm()->post('https://test.api.amadeus.com/v1/security/oauth2/token', [
            'grant_type'    => 'client_credentials',
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
        ]);
        
        // dd($client_id, $client_secret, $response->body());

        if ($response->successful()) {
            return $response->json()['access_token'];
        }
        
        
    }
    
    public static function searchAirports($keyword, $page = 0)
    {
        $token = self::authenticate();
    
        if (!$token) {
            return ['error' => 'Failed to authenticate with Amadeus API'];
        }
    
        $queryParams = [
            'subType' => 'AIRPORT',
            'keyword' => $keyword,
            // 'countryCode' => $countryCode, 
            'view' => 'LIGHT',
        ];
    
        if ($page) {
            $queryParams['page[offset]'] = $page;
        }
    
        $response = Http::withToken($token)->get('https://test.api.amadeus.com/v1/reference-data/locations', $queryParams);
    
        return $response->json();
    }

    public static function searchFlights($params)
    {
        $accessToken = self::authenticate();
        // dd($accessToken);
        $response = Http::withToken($accessToken)->get('https://test.api.amadeus.com/v2/shopping/flight-offers', $params);

        return $response->json();
    }
    
    
    public static function getFlightPrice($flightOffer)
    {
        $accessToken = self::authenticate();
    
        $response = Http::withToken($accessToken)
            ->post('https://test.api.amadeus.com/v2/shopping/flight-offers/pricing', [
                'data' => [
                    'type' => 'flight-offers-pricing',
                    'flightOffers' => [$flightOffer], 
                ],
            ]);
    
        return $response->json();
    }
    
    public static function bookFlight($params)
    {
        $accessToken = self::authenticate();
    
        $response = Http::withToken($accessToken)->post('https://test.api.amadeus.com/v1/booking/flight-orders', [
            'data' => [
                'type'          => 'flight-order',
                'flightOffers'  => $params['flightOffers'],
                'travelers'     => $params['travelers'],
                'payment'       => $params['payment'],
            ]
        ]);
    
        return $response->json();
    }


    
    // Nomba Endpoints
    
    public static function refreshNombaToken(){
        $accountId = getSettings('nomba_default','accountId');
        $client_id = getSettings('nomba_default', 'client_id');
        $client_secret = getSettings('nomba_default', 'client_secret');
        $url = getSettings('nomba_default','sandboxurl');
        
        $endpoint = $url."/auth/token/issue";
        
        // dd($refresh_token, $access_token, $accountId);
        
        $headers = [
            'Content-Type' => 'application/json',
            'accountId' => $accountId
        ];
        
        $body = [
            "grant_type" => "client_credentials",
            "client_id" => $client_id,
            "client_secret" => $client_secret
        ];
        
        $response = Http::withHeaders($headers)->post($endpoint, $body);
        
        $default = AdminSetting::where('name', 'nomba_access_token')->first();
        $responseData = json_decode($response->body(), true);
        

        if ($default && $responseData['code'] == 00) {
            $default->data = $responseData['data'];  
            $default->save();  
            return $responseData;
        }
        return $responseData; 
    }
    
    public static function createNombaAccount($accountName){
        $accountId = getSettings('nomba_default','accountId');
        $get_access_token = self::refreshNombaToken();
        if ($get_access_token['code'] != 00) {
            return $get_access_token;
        }
        $access_token = $get_access_token['data']['access_token'];
        $url = getSettings('nomba_default','sandboxurl');
        
        
        $endpoint = $url."/accounts/virtual";
        
        // dd($accountId, $get_access_token, $url);
        
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
            'accountId' => $accountId
        ];
       
        $body = [
            "accountRef" => 'REF-' . Str::upper(Str::random(10)) . '-' . time(),
            "accountName" => $accountName,
        ];

        $response = Http::withHeaders($headers)->post($endpoint, $body);
        
        
        
        $responseBody = json_decode($response->body(), true);
        // dd($responseBody);
        return json_decode($response->body(), true);
    }
    
    public static function nombaTransfer ($bankCode, $account_number, $amount, $narration, $accountName, $ref){
        $accountId = getSettings('nomba_default','accountId');
        $get_access_token = self::refreshNombaToken();
        if ($get_access_token['code'] != 00) {
            return $get_access_token;
        }
        $access_token = $get_access_token['data']['access_token'];
        $url = getSettings('nomba_default','sandboxurl');
        
        
        $endpoint = $url."/transfers/bank"; 
        
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
            'accountId' => $accountId
        ];
        
        $body = [
            "amount" => $amount,
            "accountNumber" => $account_number,
            "accountName" => $accountName,
            "bankCode" => $bankCode,
            "merchantTxRef" => $ref,
            "senderName" => Auth::user()->surname." ".Auth::user()->first_name,
            "narration" => $narration,
        ];

        $response = Http::withHeaders($headers)->post($endpoint, $body);
        
        return json_decode($response->body(), true);
        
    }
    
    public static function getBankList (){
        $accountId = getSettings('nomba_default','accountId');
        $get_access_token = self::refreshNombaToken();
        if ($get_access_token['code'] != 00) {
            return $get_access_token;
        }
        $access_token = $get_access_token['data']['access_token'];
        $url = getSettings('nomba_default','sandboxurl');
        
        
        $endpoint = $url."/transfers/banks";
        
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
            'accountId' => $accountId
        ];
       
        $response = Http::withHeaders($headers)->get($endpoint);
        
        return json_decode($response->body(), true);
    
    }
    
    public static function getAccountDetailsWithNomba($bankCode, $account_number){
        $accountId = getSettings('nomba_default','accountId');
        $get_access_token = self::refreshNombaToken();
        if ($get_access_token['code'] != 00) {
            return $get_access_token;
        }
        $access_token = $get_access_token['data']['access_token'];
        $url = getSettings('nomba_default','sandboxurl');
        
        
        $endpoint = $url."/transfers/bank/lookup";
        
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
            'accountId' => $accountId
        ];
       
        $body = [
            "bankCode" => $bankCode,
            "accountNumber" => $account_number,
        ];

        $response = Http::withHeaders($headers)->post($endpoint, $body);
        
        $responseBody = json_decode($response->body(), true);
        
        return $responseBody;
    }
    
    
    // Reloadly Helpers
    public static function reloadlyToken(){
        
        $grant_type = getSettings('reloadly_default','grant_type');
        $client_id = getSettings('reloadly_default','client_id');
        $client_secret = getSettings('reloadly_default','client_secret');
        $audience = getSettings('reloadly_default','audience');
      
        $url = getSettings('safehaven_default','url');
        
        $endpoint = "https://auth.reloadly.com/oauth/token";
        
        $body = [
            "client_id" => $client_id,
            "client_secret" => "$client_secret",
            "grant_type" => $grant_type,
            "audience" => $audience
        ];
        
        $response = Http::post($endpoint, $body);
        $responseData = json_decode($response->body(), true);

        return $responseData['access_token'];
    }
    
    public static function getCardCountries(){
        $access_token = self::reloadlyToken();
        $url = getSettings('reloadly_access_token','url');
        
        $endpoint = $url."/countries";
        
        // dd($endpoint, $access_token);
        
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
        ];
        
        
        $response = Http::withHeaders($headers)->get($endpoint);
        
        return json_decode($response->body(), true);
    }
    
    public static function getCardCountry($iso){
        $access_token = self::reloadlyToken();
        $url = getSettings('reloadly_access_token','url');
        
        $endpoint = "$url/countries/$iso";
        
        // dd($endpoint, $access_token);
        
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
        ];
        
        
        $response = Http::withHeaders($headers)->get($endpoint);
        
        return json_decode($response->body(), true);
    }
    
    public static function getCardProducts(){
        $access_token = self::reloadlyToken();
        $url = getSettings('reloadly_access_token','url');
        
        $endpoint = $url."/products";
        
        // dd($endpoint, $access_token);
        
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
        ];
        
        
        $response = Http::withHeaders($headers)->get($endpoint);
        
        return json_decode($response->body(), true);
    }
    
    public static function getProductCategories(){
        $access_token = self::reloadlyToken();
        $url = getSettings('reloadly_access_token','url');
        
        $endpoint = $url."/product-categories";
        
        // dd($endpoint, $access_token);
        
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
        ];
        
        
        $response = Http::withHeaders($headers)->get($endpoint);
        
        return json_decode($response->body(), true);
    }
    
    public static function getProductDetails($pid){
        $access_token = self::reloadlyToken();
        $url = getSettings('reloadly_access_token','url');
        
        $endpoint = "$url/products/$pid";
        
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
        ];
        
        
        $response = Http::withHeaders($headers)->get($endpoint);
        
        return json_decode($response->body(), true);
    }
    
    public static function getProductRedeemInstructions($pid){
        $access_token = self::reloadlyToken();
        $url = getSettings('reloadly_access_token','url');
        
        $endpoint = "$url/products/$pid/redeem-instructions";
        
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
        ];
        
        
        $response = Http::withHeaders($headers)->get($endpoint);
        
        return json_decode($response->body(), true);
    }
    
    public static function getCountryProducts($iso){
        $access_token = self::reloadlyToken();
        $url = getSettings('reloadly_access_token','url');
        
        $endpoint = "$url/countries/$iso/products";
        
        // dd($endpoint, $access_token);
        
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
        ];
        
        
        $response = Http::withHeaders($headers)->get($endpoint);
        
        return json_decode($response->body(), true);
    }
    
    public static function getFXRate($currency, $amount){
        $access_token = self::reloadlyToken();
        $url = getSettings('reloadly_access_token','url');
        
        $endpoint = $url."/fx-rate/?currencyCode=$currency&amount=$amount";
        
        // dd($endpoint, $access_token);
        
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
        ];
        
        
        $response = Http::withHeaders($headers)->get($endpoint);
        
        return json_decode($response->body(), true);
    }
    
    public static function orderGiftCard($data){
        $access_token = self::reloadlyToken();
        $url = getSettings('reloadly_access_token','url');
        
        $endpoint = "$url/orders";
        
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
        ];
        
        $body = [
            "productId" => $data['productId'],
            "countryCode" => $data['countryCode'],
            "quantity" => $data['quantity'],
            "unitPrice" => $data['unitPrice'],
            "customIdentifier" => 'gift-card-' . Str::upper(Str::random(10)) . '-' . time(),
            "senderName" => Auth::user()->surname." ".Auth::user()->first_name,
            "recipientEmail" => $data['recipientEmail'],
            "recipientPhoneDetails" => [
                "countryCode" => $data['recipientCountryCode'],
                "phoneNumber" => $data['recipientPhoneNumber']
            ]
            
            
        ];

        $response = Http::withHeaders($headers)->post($endpoint, $body);
        
        return json_decode($response->body(), true);
    }
    
    public static function getRedeemCode($tid){
        $access_token = self::reloadlyToken();
        $url = getSettings('reloadly_access_token','url');
        
        $endpoint = "$url/orders/transactions/$tid/cards";
        
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
        ];
        
        
        $response = Http::withHeaders($headers)->get($endpoint);
        
        return json_decode($response->body(), true);
    }
    
    // Reloadly Features Ends here
    public static function handleCustomerPaystack($request_data) {
        $paysk = getSettings('paystack','secretkeypaystack');
        if ($paysk == "error") {return json_encode([]);}
        $headers = [
            'Content-Type' => 'application/json',
            'authorization' => 'Bearer ' . $paysk
        ];
        $body = [
            'email' => Auth::user()->email,
            'first_name' => $request_data['first_name'],
            'last_name' => $request_data['surname'],
            'phone' => $request_data['phone_number'],
        ];
        
        $response = Http::withHeaders($headers)->post('https://api.paystack.co/customer', $body);
        
        $response_body = json_decode($response->body());
        if ($response_body->status == "true") {
            $dva = self::createCustomerDVA($response_body->data->id);
            return $dva;
        }
    }
    
    public static function verifyBVNYouverify($bvn)
    {
        try {
            $youapitoken = getSettings('youverify','apitoken');
            if ($youapitoken == "error") {return response()->json(retErrorSetting());}
            $response = Http::withHeaders([
                'token' => $youapitoken,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])->asForm()->post('https://api.sandbox.youverify.co/v2/api/identity/ng/bvn', [
                'id' => $bvn,
                'isSubjectConsent' => 'true',
            ]);

            if ($response->status() === 200) {
                $responseData = json_decode($response->body(), true);
                if ($responseData['success']) {
                    $data = [
                        'first_name'    =>  $responseData['data']['firstName'],
                        'surname'       =>  $responseData['data']['lastName'],
                        'other_name'    =>  $responseData['data']['middleName'],
                        'phone_number'  =>  $responseData['data']['mobile'],
                        'dob'           =>  $responseData['data']['dateOfBirth']
                    ];

                    // Wrap the data in a common response format
                    $response = [
                        'success' => true,
                        'data' => $data,
                    ];

                    return $response;
                }
            } else {
                $responseData = json_decode($response->body(), true);

                // Wrap the error message in a common response format
                $errorResponse = [
                    'success' => false,
                    'message' => $responseData['message'],
                ];

                throw new \Exception(json_encode($errorResponse));
            }
        } catch (\Throwable $th) {
            // Wrap the exception message in a common response format
            $errorResponse = [
                'success' => false,
                'message' => 'Error: ' . $th->getMessage(),
            ];

            throw new \Exception(json_encode($errorResponse));
        }
    }
    
    public static function handleCustomerStrowallet($request_data){
        $public_key = getSettings('strowallet','publickey');
        if ($public_key == "error") {return json_encode([]);}
        $headers = [
            'accept' => 'application/json',
            'content-type' => 'application/json',
          ];
        $otherName = empty($request_data['other_name']) ? " " : $request_data['other_name'];
        $body = [
            'email' => Auth::user()->email,
            'account_name' => $request_data['first_name']." ".$otherName." ".$request_data['surname'],
            'phone' => $request_data['phone_number'],
            'webhook_url' => 'https://api.paypointapp.africa/api/StroAccountWebHook',
            'public_key' => $public_key
        ];
        
        $response = Http::withHeaders($headers)->post('https://strowallet.com/api/virtual-bank/new-customer/', $body);
        // dd($response->body());
        return json_decode($response->body(), true);
    }
    
    public static function handleVirtualCardAccount($request_data){
        $public_key = getSettings('strowallet','publickey');
        if ($public_key == "error") {return json_encode([]);}
        $headers = [
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ];
        //   dd($request_data);
        $body = [
            'public_key' => $public_key,
            'houseNumber' => $request_data['houseNumber'],
            'firstName' => $request_data['first_name'],
            'lastName' => $request_data['surname'],
            'idNumber' => $request_data['idNumber'],
            'customerEmail' => $request_data['email'],
            'phoneNumber' => $request_data['phone_number'],
            'dateOfBirth' => $request_data['dateOfBirth'],
            'idImage' => $request_data['idImage'], 
            'userPhoto' => $request_data['userPhoto'], 
            'line1' => $request_data['address'],
            'state' => $request_data['state'],
            'zipCode' => $request_data['zipcode'],
            'city' => 'Accra', //$request_data['city'],
            'country' => 'Ghana', //$request_data['country'],
            'idType' => 'PASSPORT' //$request_data['idType'],
        ];
        
        $response = Http::withHeaders($headers)->post('https://strowallet.com/api/bitvcard/create-user/', $body);
        // dd($response->body());
        return json_decode($response->body(), true);
    }
    
    public static function createVirtualCard($user){
        $cardName = $user->first_name." ".$user->other_name." ".$user->surname;
        $cardName = ucwords(strtolower($cardName));
        $email = $user->email;
        
        $public_key = getSettings('strowallet','publickey');
        $amount = getSettings('card_charges','deposit');
        if ($public_key == "error") {return json_encode([]);}
        $headers = [
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ];
          
        $body = [
            'public_key' => $public_key,
            'name_on_card' => $cardName,
            'card_type' => 'visa',
            'amount' => $amount,
            'customerEmail' => $email,
            'mode' => 'sandbox'
            
        ];
        
        $response = Http::withHeaders($headers)->post('https://strowallet.com/api/bitvcard/create-card/', $body);
        return json_decode($response->body(), true);
    }
    
    public static function createLiveVirtualCard($user){
        $cardName = $user->first_name." ".$user->other_name." ".$user->surname;
        $cardName = ucwords(strtolower($cardName));
        $email = $user->email;
        
        $public_key = getSettings('strowallet','publickey');
        $amount = getSettings('card_charges','deposit');
        if ($public_key == "error") {return json_encode([]);}
        $headers = [
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ];
          
        $body = [
            'public_key' => $public_key,
            'name_on_card' => $cardName,
            'card_type' => 'visa',
            'amount' => $amount,
            'customerEmail' => $email,
        ];
        
        $response = Http::withHeaders($headers)->post('https://strowallet.com/api/bitvcard/create-card/', $body);
        return json_decode($response->body(), true);
    }
    
    public static function fundCard($data){
        
        $public_key = getSettings('strowallet','publickey');
        
        if ($public_key == "error") {return json_encode([]);}
        $headers = [
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ];
          
        $body = [
            'public_key' => $public_key,
            'card_id' => $data['card_id'],
            'amount' => $data['amount'],
            // 'mode' => 'sandbox'
        ];
        
        $response = Http::withHeaders($headers)->post('https://strowallet.com/api/bitvcard/fund-card/', $body);
        return json_decode($response->body(), true);
    }
    
    public static function cardTransactions($card_id){
        
        $public_key = getSettings('strowallet','publickey');
        
        if ($public_key == "error") {return json_encode([]);}
        $headers = [
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ];
          
        $body = [
            'public_key' => $public_key,
            'card_id' => $card_id,
            // 'mode' => 'sandbox'
        ];
        
        $response = Http::withHeaders($headers)->post('https://strowallet.com/api/bitvcard/card-transactions/', $body);
        return json_decode($response->body(), true);
    }
    
    public static function cardDetails($card_id){
        
        $public_key = getSettings('strowallet','publickey');
        
        if ($public_key == "error") {return json_encode([]);}
        $headers = [
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ];
          
        $body = [
            'public_key' => $public_key,
            'card_id' => $card_id,
        ];
        
        $response = Http::withHeaders($headers)->post('https://strowallet.com/api/bitvcard/fetch-card-detail/', $body);
        return json_decode($response->body(), true);
    }
    
    public static function withdrawFromCard($data){
        
        $public_key = getSettings('strowallet','publickey');
        $card_id = $data['card_id'];
        $amount = $data['amount'];
        
        if ($public_key == "error") {return json_encode([]);}
        $headers = [
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ];
          
        $response = Http::withHeaders($headers)->post("https://strowallet.com/api/bitvcard/card_withdraw/?card_id=$card_id&amount=$amount&public_key=$public_key");
        
        return json_decode($response->body(), true);
    }
    
    
    public static function freezeCard($card_id, $action){
        
        $public_key = getSettings('strowallet','publickey');
        
        if ($public_key == "error") {return json_encode([]);}
        $headers = [
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ];
          
        $body = [
            'public_key' => $public_key,
            'card_id' => $card_id,
            'action' => $action
        ];
        
        $response = Http::withHeaders($headers)->post('https://strowallet.com/api/bitvcard/action/status/', $body);
        return json_decode($response->body(), true);
    }
    
    public static function withdrawalStatus($ref){
        
        $public_key = getSettings('strowallet','publickey');
        
        if ($public_key == "error") {return json_encode([]);}
        $headers = [
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ];
        
        $response = Http::withHeaders($headers)->get("https://strowallet.com/api/bitvcard/getcard_withdrawstatus/?reference=$ref&public_key=$public_key");
          
        return json_decode($response->body(), true);
    }
    
    public static function addBeneficiary($type, $data, $name = null, $number = null, $provider = null)
    {
        
        $existingBeneficiary = Beneficiary::where('user_id', Auth::id())
            ->where('type', $type)
            ->where('number', $number)
            ->first();
    
        if (!$existingBeneficiary) {
            return Beneficiary::create([
                'user_id' => Auth::id(),
                'type' => $type,
                'data' => $data,
                'name' => $name,
                'number' => $number,
                'provider' => $provider
            ]);
        }
    
        
    }

    

    public static function createCustomerDVA($customer_id) {
        $paysk = getSettings('paystack','secretkeypaystack');
        if ($paysk == "error") {return response()->json(retErrorSetting());}
        $headers = [
            'Content-Type' => 'application/json',
            'authorization' => 'Bearer ' . $paysk
        ];
        $body = [
            "customer" => $customer_id, 
            "preferred_bank" => config('app.env') == "local" ? self::$test_bank : self::$live_bank
        ];
        
        $request = Http::withHeaders($headers)->post('https://api.paystack.co/dedicated_account', $body);
        $response = json_decode($request->body());
        return $response;
    }

    // public static function setSMTP() {
    //     try {
    //         $smtphost = getSettings('smtp', 'smtphost');
    //         $smtpport = getSettings('smtp', 'smtpport');
    //         $smtpfrom = getSettings('smtp', 'smtpfrom');
    //         $smtpusername = getSettings('smtp', 'smtpusername');
    //         $smtppassword = getSettings('smtp', 'smtppassword');

    //         // Check if any setting is equal to "error"
    //         if (in_array("error",[$smtphost, $smtpport, $smtpfrom, $smtpusername, $smtppassword])) {
    //             return response()->json(retErrorSetting());
    //         }

    //         $data = [
    //             'driver' => 'smtp',
    //             'encryption' => 'tls',
    //             'host' => $smtphost,
    //             'port' => (int)$smtpport,
    //             'from' => [
    //                 'address' => $smtpfrom,
    //                 'name' => config('app.name')
    //             ],
    //             'username' => $smtpusername,
    //             'password' => $smtppassword,
    //         ];

    //         // Set the configuration dynamically
    //         Config::set('mail', $data);
    //         // ... set other mail configuration settings
    //     } catch (\Exception $e) {
    //         // Log the error or handle it as needed
    //         // You can customize the response based on your application's needs
    //         $errorResponse = retErrorSetting();
    //         return response()->json($errorResponse, 500); // You might want to use a more appropriate HTTP status code
    //     }
    // }
    
}

// function getSettingsAPI($n,$k) {
//     $settings = AdminSetting::where('name',$n)->first();
//     $data =  json_decode($settings->data ?? "") ?? collect([]);
//     if (empty($data) || !isset($data->$k)) {
//         return response()->json([
//             'status'=>'false',
//             'data'=>[
//                 'message'=>"Unable to process request"
//             ]
//         ]);
//     }
//     return $data->$k;
// }

function retErrorSetting() {
    return [
        'status'=>'false',
        'data'=>[
            'message'=>"Unable to process request at this time"
        ]
    ];
}

function getSettingsSMTP() {
    $settings = AdminSetting::where('name','smtp')->first();
    $data =  json_decode($settings->data ?? "") ?? collect([]);
    if (empty($data->smtphost) || empty($data->smtpport) || empty($data->smtpfrom) || empty($data->smtpusername) || empty($data->smtppassword)) {
        return 'error';
    }
    return [
        'driver' => 'smtp',
        'encryption' => 'tls',
        'host' => $data->smtphost ?? "error",
        'port' => (int) $data->smtpport ?? "error",
        'from' => [
            'address' => $data->smtpfrom ?? "error",
            'name' => config('app.name')
        ],
        'username' => $data->smtpusername ?? "error",
        'password' => $data->smtppassword ?? "error",
    ];
}

function getSettings($n,$k) {
    $settings = AdminSetting::where('name',$n)->first();
    $data =  json_decode($settings->data ?? "") ?? collect([]);
    if (empty($data) || !isset($data->$k)) {
        return 'error';
    }
    return $data->$k;
}

function getSettingsData($n){
    $settings = AdminSetting::where('name',$n)->first();
    if ( empty($settings) ) {
        return 'error';
    }
    return $settings;
}

function generateOTP() {
    $g = (string) rand(1000,9999);
    if (strlen($g) != 4) {generateOTP();}
    return $g;
}

