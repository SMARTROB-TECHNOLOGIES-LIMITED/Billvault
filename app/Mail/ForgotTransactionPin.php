<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ForgotTransactionPin extends Mailable
{
    use Queueable, SerializesModels;

    public $temp;
    public $data;
    public $subject;
    public $cc;
    public $attachment;
    /**
     * Create a new message instance.
     */
    public function __construct($temp,$data,$subject,$cc=[],$attachment=null)
    {
        $this->temp = $temp;
        $this->data = $data;
        $this->subject = $subject;
        $this->cc = $cc;
        $this->attachment = $attachment;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: $this->temp,
            with: $this->data
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
