<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TierThree extends Model
{
    use HasFactory;

    protected $table = 'tier_threes';
    
    protected $fillable = ['user_id', 'house_address', 'utility_bill', 'status', 'rejection_reason'];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
