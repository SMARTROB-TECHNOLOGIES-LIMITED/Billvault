<?php

// namespace App\Mail;

// use Carbon\Carbon;
// use Illuminate\Support\Arr;
// use Illuminate\Bus\Queueable;
// use Illuminate\Mail\Mailable;
// use Illuminate\Queue\SerializesModels;
// use MailerSend\Helpers\Builder\Variable;
// use Illuminate\Mail\Mailables\Attachment;
// use MailerSend\LaravelDriver\MailerSendTrait;
// use MailerSend\Helpers\Builder\Personalization;

// class RegisterMail extends Mailable//implements ShouldQueue

// {
//     use Queueable, SerializesModels, MailerSendTrait;

//     public $temp;
//     public $data;
//     public $attachment;
//     /**
//      * Create a new message instance.
//      */
//     public function __construct($temp, $data, $attachment = null)
//     {
//         $this->temp = $temp;
//         $this->data = $data;
//         $this->attachment = $attachment;
//         $this->subject = 'Eros from PayPoint Africa';
//     }

//     public function build()
//     {
//         // Recipient for use with variables and/or personalization
//         $to = Arr::get($this->to, '0.address');

//         return $this
//             // ->view('')
//             // ->text('')
//             // ->attachFromStorageDisk('public', 'example.png')
//             ->mailersend(
//                 template_id: $this->temp,
//                 // variables: [
//                 //     new Variable($to, $this->data)
//                 // ],
//                 // tags: ['tag'],
//                 // personalization: [
//                 //     new Personalization($to, [
//                 //         'var' => 'variable',
//                 //         'number' => 123,
//                 //         'object' => [
//                 //             'key' => 'object-value'
//                 //         ],
//                 //         'objectCollection' => [
//                 //             [
//                 //                 'name' => 'John'
//                 //             ],
//                 //             [
//                 //                 'name' => 'Patrick'
//                 //             ]
//                 //         ],
//                 //     ])
//                 // ],
//                 // precedenceBulkHeader: true,
//                 // sendAt: Carbon::now('Africa/Lagos'),
//             );

//             // dd($dd);
//             // return $dd;
//     }
// }


















namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Envelope;

use Illuminate\Mail\Mailables\Content;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class RegisterMail extends Mailable //implements ShouldQueue
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
