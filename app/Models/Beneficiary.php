<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Beneficiary extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'type',
        'data',
        'name',
        'number',
        'provider'
    ];

    protected $casts = [
        'data' => 'array' // Ensures the `data` field is cast to and from JSON
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
