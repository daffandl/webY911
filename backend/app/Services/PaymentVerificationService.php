<?php

namespace App\Services;

class PaymentVerificationService
{
    /**
     * Verify HMAC signature for Midtrans payments.
     *
     * @param string $signature_key
     * @param array $payload
     * @param string $signature
     * @return bool
     */
    public function verifyHmacSignature(string $signature_key, array $payload, string $signature): bool
    {
        // Create the string to be hashed
        $hash_string = json_encode($payload);

        // Generate HMAC signature from payload
        $generated_signature = hash_hmac('sha512', $hash_string, $signature_key);

        // Compare the generated signature with the provided signature
        return hash_equals($generated_signature, $signature);
    }
}