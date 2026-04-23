<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class PhoneNumber implements Rule
{
    protected $phone;

    public function passes($attribute, $value)
    {
        // Check if the phone number is in the valid Indonesian format
        // Indonesian phone numbers start with 08 and have 10 to 13 digits.
        $this->phone = $value;
        return preg_match('/^08[0-9]{8,11}$/', $this->phone);
    }

    public function message()
    {
        return 'The :attribute must be a valid Indonesian phone number.';
    }
}