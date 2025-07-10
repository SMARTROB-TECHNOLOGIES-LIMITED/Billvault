<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SingleWordRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!(preg_match('/^[\w-]+$/', $value))) {
            $field = ucfirst(str_replace("_",' ',$attribute));
            $fail($field . " must be a single word (can contain '-')");
        }
    }
}