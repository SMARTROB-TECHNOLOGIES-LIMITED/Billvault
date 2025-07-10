<?php 
    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    
    class LoginActivity extends Model
    {
        use HasFactory;
    
        protected $fillable = [
            'user_id',
            'logged_in_at',
            'ip_address',
            'latitude',
            'longitude',
            'country',
            'city_name',
            'region_name'
        ];
        
        public function user()
        {
            return $this->belongsTo(User::class);
        }
    }
    
    

?>