<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Envelope;

use Illuminate\Mail\Mailables\Content;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class TokenMail extends Mailable //implements ShouldQueue
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
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        // if (!empty($this->attachment)) {
        //     return [
        //         new Attachment(
        //             'attachment.txt', // Replace with the actual file name
        //             $this->attachment // Replace with the attachment data
        //         ),
        //     ];
        // }
        return [];
    }
}
