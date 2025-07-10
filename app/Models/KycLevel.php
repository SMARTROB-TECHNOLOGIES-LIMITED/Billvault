<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KycLevel extends Model
{
    use HasFactory;

    protected $table = 'kyc_levels';
    
    protected $fillable = [
        'title',
        'status',
        'details',
        'maximum_balance',
        'maximum_transfer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
