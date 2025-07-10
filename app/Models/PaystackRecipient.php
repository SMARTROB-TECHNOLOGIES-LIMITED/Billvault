<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaystackRecipient extends Model
{
    use HasFactory;

    protected $table = 'paystack_recipients';
    
    protected $fillable = [
        'user_id',
        'bank_name',
        'bank_code',
        'account_number',
        'account_name',
        'recipient_code',
        'authorization_code',
        'data'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
