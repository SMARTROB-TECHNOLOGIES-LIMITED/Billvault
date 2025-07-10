<?php 
    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    
    class Country extends Model
    {
        use HasFactory;
    
        /**
         * The attributes that are mass assignable.
         *
         * @var array<int, string>
         */
        protected $fillable = [
            'name',
            'flag_url',
        ];
    
        /**
         * Get the gift cards associated with the country.
         */
        public function giftCards()
        {
            return $this->hasMany(GiftCard::class);
        }
        
        public function giftCardRate()
        {
            return $this->hasMany(GiftCardRate::class);
        }
    }
?>