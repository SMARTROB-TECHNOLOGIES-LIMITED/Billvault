<?php

namespace App\Rules;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\ValidationRule;

class ParamAlready implements ValidationRule
{

    public $field;
    
    public function __construct($field) {
        $this->field = $field;
    }
    
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Get the currently authenticated user
        $field = $this->field;
        $user = Auth::user()->$field;

        if (!(empty($user))) {
            $fail(ucfirst($this->field) . ' already set');
        }
    }
}
