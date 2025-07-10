<?php

namespace App\Services;

use Google_Client;
use Google\Service\FirebaseCloudMessaging;
use Google\Service\FirebaseCloudMessaging\Message;
use Google\Service\FirebaseCloudMessaging\Notification;
use Google\Service\FirebaseCloudMessaging\SendMessageRequest;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected $client;

    public function __construct()
    {
        $fcm_service = base_path(env('FCM_SERVICE_ACCOUNT'));
        // dd($fcm_service);
        date_default_timezone_set('UTC');
        // Initialize the Google Client
        $this->client = new Google_Client();
        $this->client->setAuthConfig($fcm_service);
        $this->client->addScope('https://www.googleapis.com/auth/firebase.messaging');
    } 

    public function sendNotification($title, $body, $deviceToken)
    {
        // Initialize Firebase Cloud Messaging Service
        $fcm = new FirebaseCloudMessaging($this->client);

        // Create a Notification instance
        $notification = new Notification();
        $notification->setTitle($title);
        $notification->setBody($body);

        // Create a Message instance
        $message = new Message();
        $message->setToken($deviceToken);
        $message->setNotification($notification);

        // Create an instance of SendMessageRequest
        $sendMessageRequest = new SendMessageRequest();
        $sendMessageRequest->setMessage($message);

        // Send the message
        try {
            $fcm->projects_messages->send('projects/' . env('FCM_PROJECT_ID'), $sendMessageRequest);
            Log::info('Notification sent successfully to ' . $deviceToken);
        } catch (\Exception $e) {
            Log::error('Failed to send notification: ' . $e->getMessage());
            return false;
        }

        return true;
    }
}
