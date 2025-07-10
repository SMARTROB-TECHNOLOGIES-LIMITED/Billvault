<?php

namespace App\Jobs;

use App\Models\TransactionLog as ModelsTransactionLog;
use App\Mail\ReceiptMail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TransactionLog //implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user_id;
    public $type;
    public $amount;
    public $transaction_id;
    public $status;
    public $recipient;
    public $data;
    public $condition;

    /**
     * Create a new job instance.
     */
    public function __construct($user_id, $type, $amount, $transaction_id, $status, $recipient, $data = null)
    {
        $this->user_id = $user_id;
        $this->type = $type;
        $this->amount = $amount;
        $this->transaction_id = $transaction_id;
        $this->status = $status;
        $this->recipient = $recipient;
        $this->data = $data;
        $this->condition = ModelsTransactionLog::where('transaction_id',$this->transaction_id)->exists() ? true : false;
    }

    /**
     * Execute the job.
     */
    // public function handle(ModelsTransactionLog $log): void
    // {
    //     $data = [];
    //     $data['user_id'] = $this->user_id;
    //     $data['transaction_id'] = $this->transaction_id;
    //     $data['status'] = $this->status;
        
    //     !empty($this->type) ? $data['type'] = $this->type : '';
    //     !empty($this->amount) ? $data['amount'] = $this->amount : '';
    //     !empty($this->recipient) ? $data['recipient'] = $this->recipient : '';
    //     !empty($this->data) ? $data['data'] = $this->data : '';

    //     if ($this->condition == true) {
    //         $log->where('transaction_id',$this->transaction_id)->update($data);
    //     }else {
    //         $log->create($data);
    //     }
    // }
    
    public function handle(ModelsTransactionLog $log): void
    {
        $data = [
            'user_id' => $this->user_id,
            'transaction_id' => $this->transaction_id,
            'status' => $this->status,
        ];
        
        !empty($this->type) ? $data['type'] = $this->type : '';
        !empty($this->amount) ? $data['amount'] = $this->amount : '';
        !empty($this->recipient) ? $data['recipient'] = $this->recipient : '';
        !empty($this->data) ? $data['data'] = $this->data : '';

        $transactionLog = null;

        if ($this->condition) {
            $log->where('transaction_id', $this->transaction_id)->update($data);
            $transactionLog = $log->where('transaction_id', $this->transaction_id)->first();
        } else {
            $transactionLog = $log->create($data);
        }

        // Send Receipt Email
        if ($transactionLog && $this->status != 'Pending') {
            $this->sendReceiptEmail($transactionLog);
        }
    }
    
    private function sendReceiptEmail($transactionLog): void
    {
        // Determine the view for the transaction type
        $view = match (strtolower($this->type)) {
            'deposit' => 'pdf.deposit-mail',
            'transfer' => 'pdf.transfer',
            'airtime' => 'pdf.airtime',
            'data' => 'pdf.data',
            'electricity' => 'pdf.electricity',
            'cable tv' => 'pdf.cable-tv',
            'betting' => 'pdf.betting',
            'top-up' => 'pdf.top-up',
            default => 'pdf.default',
        };
        
        $user = $transactionLog->user;
        // Generate PDF
        $pdf = Pdf::loadView($view, ['details' => $transactionLog, 'user' => $user])->setPaper('a5', 'portrait');
        $pdfPath = tempnam(sys_get_temp_dir(), 'pdf_') . '.pdf';
        file_put_contents($pdfPath, $pdf->output());

        // Send Email
        $user = $transactionLog->user; // Assuming a relationship between TransactionLog and User
        if ($user && $user->email) {
            Mail::to($user->email)->send(new ReceiptMail($transactionLog, $pdfPath, $user));
        }
        unlink($pdfPath);
    }
}
