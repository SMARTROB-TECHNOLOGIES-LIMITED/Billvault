<?php

namespace App\Http\Controllers\Admin;
use App\Jobs\SendNotificationJob;
use App\Models\User;
use App\Models\AdminSetting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Log;
use function App\getSettings;

class MiscellaneousController extends Controller
{

    public function notification() {
        return view('admin.notification');
    }

    public function sendNotificationold(Request $request)
    {
        $validatedData = $request->validate([
            'header' => ['required', 'string'],
            'body' => ['required', 'string'],
        ],[
            'header.required' => "Notification header is required",
            'header.string' => "Field accepts valid input only",
            'body.required' => "Notification body is required",
            'body.string' => "Field accepts valid input only",
        ]);
        
        $SERVER_API_KEY = getSettings('firebase','serverkey');
        if ($SERVER_API_KEY == "error") {
            return redirect()->back()->with('alert', ['t' => 'Error', 'm' => 'Firebase server key not set.']);
        }
        
        $request->merge(Purify::clean($request->all())); // Merging cleaned input into request
        
        $firebaseTokens = User::whereNotNull('device_token')->pluck('device_token')->toArray(); // Using toArray() to get an array
        
        $data = [
            "registration_ids" => $firebaseTokens,
            "notification" => [
                "title" => $request->header,
                "body" => $request->body,
                "icon" => empty($request->imageurl) ? asset('assets/images/bank/bank.jpg') : $request->imageurl,
                'click_action' => 'https://admin.simplepayapp.ng/',
                "content_available" => true,
                "priority" => "high",
            ]
        ];
        $dataString = json_encode($data);
        
        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];
    
        $ch = curl_init();
    
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
    
        $response = json_decode(curl_exec($ch), true); // Convert to associative array
        
        // dd($response);
        
        curl_close($ch); // Close curl resource
        
        if (isset($response['failure']) && $response['failure'] > 0) {
            $invalidTokens = collect($response['results'])->filter(function ($result) {
                return isset($result['error']) && $result['error'] === 'NotRegistered';
            })->keys()->toArray();
            
            // Remove invalid tokens from database
            User::whereIn('device_token', $invalidTokens)->update(['device_token' => null]);
        }
        
        if ($response && isset($response['success']) && $response['success'] > 0) {
            return redirect()->back()->with('alert', ['t' => 'Success', 'm' => 'Notifications sent out successfully.']);
        }
        return redirect()->back()->with('alert', ['t' => 'Error', 'm' => 'An error occurred. Please try again.']);
    }
    
    public function sendNotification(Request $request)
    {
        // Validate the request
        $validatedData = $request->validate([
            'header' => ['required', 'string'],
            'body' => ['required', 'string'],
        ], [
            'header.required' => "Notification header is required",
            'header.string' => "Field accepts valid input only",
            'body.required' => "Notification body is required",
            'body.string' => "Field accepts valid input only",
        ]);
    
        // Clean the input to avoid malicious data
        $request->merge(Purify::clean($request->all())); 
    
        // Retrieve all device tokens
        $firebaseTokens = User::whereNotNull('device_token')->pluck('device_token')->toArray(); 
        
        if (empty($firebaseTokens)) {
            return redirect()->back()->with('alert', ['t' => 'Error', 'm' => 'No users with valid device tokens found.']);
        }
        
        // Split tokens into chunks of 1000 (Firebase allows up to 1000 tokens per request)
        $chunks = array_chunk($firebaseTokens, 1000);
    
        // Use Laravel's queue for background processing of notifications
        foreach ($chunks as $chunk) {
            Log::info("Dispatching notification to chunk of " . count($chunk) . " tokens.");
            SendNotificationJob::dispatch($request->header, $request->body, $chunk);
        }
    
        // Return a success message
        return redirect()->back()->with('alert', ['t' => 'Success', 'm' => 'Notifications sent successfully.']);
    }

}
