<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class Uppercase implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param Closure(string): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== strtoupper($value)) {
            $fail("validation.custom.uppercase")->translate([
                "attribute" => $attribute,
                "value" => $value
            ]);
        }
    }
}
