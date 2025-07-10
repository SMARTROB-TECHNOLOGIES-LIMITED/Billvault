<?php

namespace App\Jobs;

use Google_Client;
use Google\Service\FirebaseCloudMessaging;
use Google\Service\FirebaseCloudMessaging\Message;
use Google\Service\FirebaseCloudMessaging\Notification;
use Google\Service\FirebaseCloudMessaging\SendMessageRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $timeout = 300;

    protected $title;
    protected $body;
    protected $deviceTokens;

    /**
     * Create a new job instance.
     *
     * @param string $title
     * @param string $body
     * @param array $deviceTokens
     */
    public function __construct(string $title, string $body, array $deviceTokens)
    {
        $this->title = $title;
        $this->body = $body;
        $this->deviceTokens = $deviceTokens;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("FCM_SERVICE_ACCOUNT path: " . env('FCM_SERVICE_ACCOUNT'));
        Log::info("FCM_PROJECT_ID: " . env('FCM_PROJECT_ID'));

        // Initialize Google Client inside the handle method
        $client = new Google_Client();
        $client->setAuthConfig(env('FCM_SERVICE_ACCOUNT')); 
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        // Initialize Firebase Cloud Messaging Service
        $fcm = new FirebaseCloudMessaging($client);

       
        
        // Iterate through device tokens
        foreach ($this->deviceTokens as $deviceToken) {
            Log::info("Processing device token: $deviceToken");

            // Create a Notification instance
            $notification = new Notification();
            $notification->setTitle($this->title);
            $notification->setBody($this->body);

            // Create a Message instance
            $message = new Message();
            $message->setToken($deviceToken); // Set the device token
            $message->setNotification($notification);

            // Create a SendMessageRequest instance
            $sendMessageRequest = new SendMessageRequest();
            $sendMessageRequest->setMessage($message);

            // Send the message inside a try-catch block
            try {
                // Send the notification to Firebase
                $fcm->projects_messages->send('projects/' . env('FCM_PROJECT_ID'), $sendMessageRequest);
                
                // Log success
                Log::info("Notification sent successfully to device token: $deviceToken");
            } catch (\Exception $e) {
                // Log the error without stopping the process
                Log::error("Failed to send notification to device token: $deviceToken. Error: " . $e->getMessage());
                
                // Continue to the next device
                continue;
            }
        }
    }
}
