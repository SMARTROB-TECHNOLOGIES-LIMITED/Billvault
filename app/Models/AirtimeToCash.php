<?php 
    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    
    class AirtimeToCash extends Model
    {
        use HasFactory;
    
        protected $fillable = [
            'image',
            'network_name',
            'is_enabled',
            'receiver_number',
            'payment_percentage',
            'minimum_airtime',
            'maximum_airtime',
        ];
    }

?>