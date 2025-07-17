<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'surname',
        'other_name',
        'username',
        'phone_number',
        'email',
        'dob',
        'account_number',
        'paystack_id',
        'transaction_pin',
        'balance',
        'account_level',
        'is_account_restricted',
        'is_with_card',
        'is_ban',
        'password',
        'passport',
        'gender',
        'pre_bvn',
        'bvn',
        'address',
        'view',
        'email_token',
        'complete',
        'code',
        'referral',
        'account_name',
        'bank_name'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'paystack_id',
        'transaction_pin',
        'remember_token',
        'email_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'transaction_pin' => 'hashed',
    ];

    public function transactionLogs()
    {
        return $this->hasMany(TransactionLog::class);
    }

    public function paystackRecipients()
    {
        return $this->hasMany(PaystackRecipient::class);
    }
    public function tier2()
    {
        return $this->hasOne(Tier2::class);
    }
    public function cardDetails()
    {
        return $this->hasMany(CardDetail::class);
    }

}
