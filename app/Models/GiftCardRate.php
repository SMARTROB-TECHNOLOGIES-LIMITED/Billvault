<?php 
    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    
    class GiftCardRate extends Model
    {
        use HasFactory;
    
        protected $fillable = [
            'gift_card_id',
            'country_id',
            'min_amount',
            'max_amount',
            'rate',
        ];
    
        public function giftCard()
        {
            return $this->belongsTo(GiftCard::class);
        }
        
        public function country()
        {
            return $this->belongsTo(Country::class, 'country_id');
        }
    }
?>