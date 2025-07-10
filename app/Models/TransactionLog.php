<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionLog extends Model
{
    use HasFactory;

    public $fillable = [
        'reference',
        'user_id',
        'transaction_id',
        'type',
        'amount',
        'data',
        'recipient',
        'status',
        'balance_before',
        'balance_after'
    ];

    public $table = 'transaction_logs';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    
}
