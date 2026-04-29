<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;

class PhoneNumberRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        // Indonesia phone number format: +62xxx or 62xxx or 0xxx (10-15 digits total)
        // Pattern: optional +, optional 62 or 0, followed by 9-13 digits
        if (!preg_match('/^(\+62|62|0)[0-9]{9,13}$/', str_replace([' ', '-', '(', ')'], '', $value))) {
            $fail('Format nomor telepon tidak valid. Gunakan format: 0812345678, 62812345678, atau +62812345678');
        }
    }
}
