<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;

class ServiceTypeRule implements ValidationRule
{
    /**
     * Whitelisted service types to prevent injection attacks
     */
    private const ALLOWED_SERVICES = [
        'regular_maintenance',
        'oil_change',
        'tire_service',
        'brake_service',
        'engine_diagnostic',
        'transmission_service',
        'suspension_service',
        'electrical_service',
        'ac_service',
        'body_repair',
        'paint_service',
        'interior_detailing',
        'general_inspection',
        'custom_service',
    ];

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
        if (!in_array($value, self::ALLOWED_SERVICES, true)) {
            $fail('Tipe layanan tidak valid. Gunakan salah satu dari: ' . implode(', ', self::ALLOWED_SERVICES));
        }
    }

    /**
     * Get allowed services (for frontend use)
     */
    public static function getAllowedServices(): array
    {
        return self::ALLOWED_SERVICES;
    }
}
