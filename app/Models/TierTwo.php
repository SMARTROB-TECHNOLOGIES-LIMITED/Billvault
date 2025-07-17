<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TierTwo extends Model
{
    use HasFactory;

    protected $table = 'tier_twos';
    
    protected $fillable = ['user_id', 'date_of_birth', 'bvn', 'id_front', 'id_back', 'status'];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
