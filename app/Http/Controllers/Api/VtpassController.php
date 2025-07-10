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
use Illuminate\Support\Facades\Log;

use function App\getSettings;
use function App\retErrorSetting;

class VtpassController extends Controller
{
    private static function vtpass_payloads():array
    {
        return [

            // [
            //     "request_id" => self::generateRequestId(),
            //     "serviceID" => "mtn",
            //     "amount" => "100",
            //     "phone" => "08011111111",
            // ],
            // [
            //     "request_id" => self::generateRequestId(),
            //     "serviceID" => "glo",
            //     "amount" => "100",
            //     "phone" => "08011111111"
            // ],
            // [
            //     "request_id" => self::generateRequestId(),
            //     "serviceID" => "airtel",
            //     "amount" => "100",
            //     "phone" => "08011111111"
            // ],
            // [
            //     "request_id" => self::generateRequestId(),
            //     "serviceID" => "etisalat",
            //     "amount" => "100",
            //     "phone" => "08011111111"
            // ],
            
            //Data
            
            // [
            //     "request_id" => self::generateRequestId(),
            //     "serviceID" => "mtn-data",
            //     "billerCode" => "08011111111",
            //     "phone" => "08011111111",
            //     "variation_code" => "mtn-10mb-100"
            // ],
            // [
            //     "request_id" => self::generateRequestId(),
            //     "serviceID" => "glo-data",
            //     "billerCode" => "08011111111",
            //     "phone" => "08011111111",
            //     "variation_code" => "glo2000"
            // ],
            // [
            //     "request_id" => self::generateRequestId(),
            //     "serviceID" => "airtel-data",
            //     "billerCode" => "08011111111",
            //     "phone" => "08011111111",
            //     "variation_code" => "airt-1000"
            // ],
            // [
            //     "request_id" => self::generateRequestId(),
            //     "serviceID" => "etisalat-data",
            //     "billerCode" => "08011111111",
            //     "phone" => "08011111111",
            //     "variation_code" => "eti-500"
            // ],
            
            // CableTV
            // [
            //     "request_id" => self::generateRequestId(),
            //     "serviceID" => "dstv",
            //     "billersCode" => "1212121212",
            //     "variation_code" => "dstv-padi",

            //     "phone" => "08097238712"
            // ],
            // [
            //     "request_id" => self::generateRequestId(),
            //     "serviceID" => "gotv",
            //     "billersCode" => "1212121212",
            //     "variation_code" => "gotv-smallie",

            //     "phone" => "08097238712"
            // ],
            // [
            //     "request_id" => self::generateRequestId(),
            //     "serviceID" => "startimes",
            //     "billersCode" => "1212121212",
            //     "variation_code" => "basic",

            //     "phone" => "08097238712"
            // ],
            // [
            //     "request_id" => self::generateRequestId(),
            //     "serviceID" => "showmax",
            //     "billersCode" => "1212121212",
            //     "variation_code" => "mobile_only",

            //     "phone" => "08097238712"
            // ],
            
            
            // Electricity
            [
                "request_id" => self::generateRequestId(),
                "serviceID" => "ikeja-electric",
                "billersCode" => "1111111111111",
                "variation_code" => "prepaid",
                "phone" => "08097238712",
                "amount" => "1000"
            ],
            [
                "request_id" => self::generateRequestId(),
                "serviceID" => "eko-electric",
                "billersCode" => "1111111111111",
                "variation_code" => "prepaid",
                "phone" => "08097238712",
                "amount" => "1000"
            ],
            [
                "request_id" => self::generateRequestId(),
                "serviceID" => "kano-electric",
                "billersCode" => "1111111111111",
                "variation_code" => "prepaid",
                "phone" => "08097238712",
                "amount" => "1000"
            ],
            [
                "request_id" => self::generateRequestId(),
                "serviceID" => "portharcourt-electric",
                "billersCode" => "1111111111111",
                "variation_code" => "prepaid",
                "phone" => "08097238712",
                "amount" => "1000"
            ],
            [
                "request_id" => self::generateRequestId(),
                "serviceID" => "ibadan-electric",
                "billersCode" => "1111111111111",
                "variation_code" => "prepaid",
                "phone" => "08097238712",
                "amount" => "1000"
            ],
            [
                "request_id" => self::generateRequestId(),
                "serviceID" => "ibadan-electric",
                "billersCode" => "1111111111111",
                "variation_code" => "prepaid",
                "phone" => "08097238712",
                "amount" => "1000"
            ],
            [
                "request_id" => self::generateRequestId(),
                "serviceID" => "benin-electric",
                "billersCode" => "1111111111111",
                "variation_code" => "prepaid",
                "phone" => "08097238712",
                "amount" => "1000"
            ],
            [
                "request_id" => self::generateRequestId(),
                "serviceID" => "jos-electric",
                "billersCode" => "1111111111111",
                "variation_code" => "prepaid",
                "phone" => "08097238712",
                "amount" => "1000"
            ],[
                "request_id" => self::generateRequestId(),
                "serviceID" => "kaduna-electric",
                "billersCode" => "1111111111111",
                "variation_code" => "prepaid",
                "phone" => "08097238712",
                "amount" => "1000"
            ],
            [
                "request_id" => self::generateRequestId(),
                "serviceID" => "abuja-electric",
                "billersCode" => "1111111111111",
                "variation_code" => "prepaid",
                "phone" => "08097238712",
                "amount" => "1000"
            ],
            [
                "request_id" => self::generateRequestId(),
                "serviceID" => "enugu-electric",
                "billersCode" => "1111111111111",
                "variation_code" => "prepaid",
                "phone" => "08097238712",
                "amount" => "1000"
            ]


        ];

    }
    
    private static function generateRequestId(): string {
        return date('YmdHi') . Str::random(5);
    }
    
    public function vtpass_generate_request_id()
    {
        try {
            $payloads = self::vtpass_payloads();
            
            foreach ($payloads as $payload) {
                
                
                $vtapi = getSettings('vtpass','apikeyvtpass');
                $vtsecret = getSettings('vtpass','secretkeyvtpass');
                if ($vtapi == "error" || $vtsecret == "error") {return response()->json(retErrorSetting());}
    
                $requestParams = Http::withHeaders([
                    'api-key' => $vtapi,
                    'secret-key' => $vtsecret,
                ])->post('https://sandbox.vtpass.com/api/pay', $payload);
                
                $response = json_decode($requestParams->body());
                
                // dd($response);

                if ( $response->code === "000") {
                    $results[] = [
                        "request_id" =>  $payload['request_id'],
                        "product_name" => $response->content->transactions->product_name
                    ];
                    Log::info('VTPass API Response:', $results);
                }else{
                    $results[] = [
                        'error'
                    ];
                }
                
                sleep(20);
                
            }

            return json_encode(["results" => $results]);
        } catch (Throwable $e) {
            throw $e;
        }
    }
}
