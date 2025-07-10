<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'country_id',
        'is_enabled',
    ];
    
    public function rates()
    {
        return $this->hasMany(GiftCardRate::class);
    }
    
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
}


?>