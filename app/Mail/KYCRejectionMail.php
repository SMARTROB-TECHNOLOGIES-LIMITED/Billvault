<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class KYCRejectionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $status;
    public $username;
    public $message;
    public $level;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($username, $status, $message=null, $level)
    {
        $this->username = $username;
        $this->status = $status;  
        $this->message = $message; 
        $this->level = $level; 
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        
        return $this->subject('KYC Status Update')
                    ->view('emails.kyc_status')
                    ->with([
                        'username' => $this->username,
                        'status' => $this->status,
                        'rejectionReason' => $this->message,
                        'level' => $this->level,
                    ]);
    }
}
