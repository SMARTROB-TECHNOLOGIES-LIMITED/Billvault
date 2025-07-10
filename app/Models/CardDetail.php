<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardDetail extends Model
{
    use HasFactory;

    // Define the table name if it's different from the plural form of the model name
    protected $table = 'card_details';

    // Define the fillable fields
    protected $fillable = [
        'user_id',
        'name_on_card',
        'card_id',
        'is_card_freeze',
        'card_created_date',
        'card_type',
        'card_brand',
        'card_user_id',
        'reference',
        'card_status',
        'customer_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
