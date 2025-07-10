<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public $details;
    public $receiptPath;
    public $user;

    public function __construct($details, $receiptPath, $user)
    {
        $this->details = $details;
        $this->receiptPath = $receiptPath;
        $this->user = $user;
    }

    public function build()
    {
        return $this->view('emails.receipt')
                    ->with('details', $this->details)
                    ->with('user', $this->user)
                    ->attach($this->receiptPath, [
                        'as' => 'Receipt_'.$this->details->transaction_id.'.pdf',
                        'mime' => 'application/pdf',
                    ]);
    }
}

