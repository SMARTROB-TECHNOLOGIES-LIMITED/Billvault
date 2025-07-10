<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralBonus extends Model
{
    use HasFactory;

    protected $table = 'referral_bonuses';
    
    protected $fillable = [
        'user_id',
        'bonus_amount',
        'bonus_from'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
