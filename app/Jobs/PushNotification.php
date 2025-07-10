<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\TransactionNotification;

use function App\getSettings;
use Illuminate\Bus\Queueable;
use function App\retErrorSetting;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\Notification;

class PushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user_id;
    public $title;
    public $body;
    public $icon;
    public $action;
    public $data;
    /**
     * Create a new job instance.
     */
    public function __construct($user_id, $title, $body, $icon = null, $action = null, $data = null)
    {
        $this->user_id = $user_id;
        $this->title = $title;
        $this->body = $body;
        $this->action = empty($action) ? 'https://api.paypointapp.africa' : $action;
        $this->data = empty($data) ? null : $data;
        $this->icon = empty($icon) ? asset('assets/images/application/logo.jpg') : $icon;
        // Storage::put('tf/received'.$user_id*rand(23456,56789097), json_encode(['djfnj','dkmcskads','dmfkmd'=>['dkfmdkjmjk',349]], JSON_PRETTY_PRINT));
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = User::where('id',$this->user_id)->first();
        Notification::send($user, new TransactionNotification($this->title,$this->body,$this->data['transaction_id'],$this->data));
        

        if (isset($user->device_token) && !empty($user->device_token)) {
            $SERVER_API_KEY = getSettings('firebase','serverkey');
            if ($SERVER_API_KEY == "error") {
                throw new \Exception("Server key for messgaing is not set. Terminating the job.");
            }

            $data = [
                "registration_ids" => [$user->device_token],
                "notification" => [
                    "title" => $this->title,
                    "body" => $this->body,
                    "icon" => $this->icon,
                    'click_action' => $this->action,
                    "content_available" => true,
                    "priority" => "high",
                ],
                'data' => $this->data
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

            curl_exec($ch);
        }
    }
}
