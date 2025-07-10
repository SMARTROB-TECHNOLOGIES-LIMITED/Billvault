<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\User;

class ValidReferralUsername implements Rule
{
    public function passes($attribute, $value)
    {
        // Check if the username exists in the users table
        return User::where('username', $value)->exists();
    }

    public function message()
    {
        return 'The referral username is not valid.';
    }
}
