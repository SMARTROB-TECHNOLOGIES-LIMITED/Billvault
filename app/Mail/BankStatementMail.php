<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BankStatementMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    protected $pdfContent;
    protected $pdfFilename;
    protected $startDate;
    protected $endDate;

    public function __construct($pdfContent, $pdfFilename, $startDate, $endDate)
    {
        $this->pdfContent = $pdfContent;
        $this->pdfFilename = $pdfFilename;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function build()
    {
         return $this->view('emails.bank-statement',['sD'=>$this->startDate,'eD'=>$this->endDate])
            ->subject('Request For Statement Of Account')
            ->attachData($this->pdfContent, $this->pdfFilename, [
                'mime' => 'application/pdf',
            ]);
            // ->with([
            //     'text' => 'Good day, we recieved a request from your account concerning your bank statement dating from ' . $this->startDate . ' to ' . $this->endDate
            // ]);
    }

    /**
     * Get the message envelope.
     */
    // public function envelope(): Envelope
    // {
    //     return new Envelope(
    //         subject: 'Bank Statement Mail',
    //     );
    // }

    // /**
    //  * Get the message content definition.
    //  */
    // public function content(): Content
    // {
    //     return new Content(
    //         view: 'view.name',
    //     );
    // }

    // /**
    //  * Get the attachments for the message.
    //  *
    //  * @return array<int, \Illuminate\Mail\Mailables\Attachment>
    //  */
    // public function attachments(): array
    // {
    //     return [];
    // }
}
