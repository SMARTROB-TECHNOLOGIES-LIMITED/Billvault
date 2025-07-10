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

class FlightBookingController extends Controller
{
    

    public function generateRef() {
        return Uuid::uuid4();
    }
    
    public function searchFlights(Request $request)
    {
        $request->validate([
            'originLocationCode'      => 'required|string|size:3',
            'destinationLocationCode' => 'required|string|size:3',
            'departureDate'           => 'required|date',
            'returnDate'              => 'nullable|date',
            'adults'                  => 'required|integer|min:1',
            'children'                => 'nullable|integer|min:0',
            'infants'                 => 'nullable|integer|min:0',
            'travelClass'             => 'nullable|string|in:ECONOMY,PREMIUM_ECONOMY,BUSINESS,FIRST',
            'nonStop'                 => 'nullable|boolean',
            'currencyCode'            => 'nullable|string|size:3',
            'maxPrice'                => 'nullable|integer|min:0',
            'max'                     => 'nullable|integer|min:1|max:250',
        ]);
    
        // Get the request parameters as expected by the API
        $queryParams = $request->all();
    
        // Call helper function
        $response = Helpers::searchFlights($queryParams);
    
        if (isset($response['data'])) {
            return response()->json([
                'status'  => true,
                'message' => 'Flights retrieved successfully',
                'data'    => $response['data']
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'data'   => [
                    'message' => "Error fetching flight details",
                    'error'   => $response['errors'] ?? 'Unknown error',
                ]
            ], $response['status'] ?? 500);
        }
    }
    
    
    public function getFlightFinalPrice(Request $request)
    {
        $request->validate([
            'flightOffer' => 'required|array', // The entire flight offer object must be passed
        ]);
    
        $response = Helpers::getFlightPrice($request->flightOffer);
    
        if (isset($response['data'])) {
            return response()->json([
                'status'  => true,
                'message' => 'Flight price retrieved successfully',
                'data'    => $response['data']
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'data'   => [
                    'message' => "Error fetching flight price",
                    'error'   => $response['errors'] ?? 'Unknown error',
                ]
            ], $response['status'] ?? 500);
        }
    }
    
    public function searchAirportAndCity(Request $request)
    {
        $request->validate([
            'keyword' => 'required|string|min:2',
            // 'countryCode' => 'required|string|min:2',
            'page' => 'sometimes|nullable|integer',
        ]);
    
        $keyword = $request->input('keyword');
        $countryCode = $request->input('countryCode');
        $page = $request->input('page', 0); // Default to 0 if not provided
    
        $response  = Helpers::searchAirports($keyword, $page);
        
        $data = $response['data'] ?? [];
        $count = $response['meta']['count'] ?? 0;
    
        // Check if no results were found
        if (empty($data)) {
            return response()->json([
                'status' => 'false',
                'message' => 'No results found for the given keyword.',
                'count' => 0,
                'data' => []
            ], 404);
        }
    
        return response()->json([
            'status' => 'true',
            'count' => $count,
            'data' => $data
        ]);
    
        
    }


    public function bookFlight(Request $request)
    {
        $request->validate([
            'flightOffers' => 'required|array',
            'travelers'    => 'required|array',
            'payment'      => 'required|array',
        ]);
    
        $queryParams = $request->only([
            'flightOffers', 'travelers', 'payment'
        ]);
    
        $response = Helpers::bookFlight($queryParams);
    
        if (isset($response['errors'])) {
            return response()->json([
                'status'  => 'false',
                'message' => 'Flight booking failed',
                'error'   => $response['errors'],
            ], 400);
        }
    
        return response()->json([
            'status'  => 'true',
            'message' => 'Flight booked successfully',
            'data'    => $response,
        ], 200);
    }

    
    
}
