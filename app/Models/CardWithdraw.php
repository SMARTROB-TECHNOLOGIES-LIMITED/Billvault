<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardWithdraw extends Model
{
    use HasFactory;

    // Define the table name if it's different from the plural form of the model name
    protected $table = 'card_withdraws';

    // Define the fillable fields
    protected $fillable = [
        'user_id',
        'ref_no',
        'amount',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
