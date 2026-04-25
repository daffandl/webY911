<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function notification(Request $request)
    {
        $signature = $request->header('x-signature');
        $payload = $request->getContent();

        if (!$this->verifyNotification($payload, $signature)) {
            Log::error('Invalid signature for Midtrans notification', [
                'payload' => $this->redactSensitiveData($payload),
                'signature' => 'REDACTED'
            ]);
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        // Process the notification
        Log::info('Midtrans notification processed', [
            'payload' => $this->redactSensitiveData($payload)
        ]);

        // Handle the notification logic here...
    }

    private function verifyNotification($payload, $signature)
    {
        $secretKey = config('midtrans.server_key');
        $expectedSignature = hash_hmac('sha512', $payload, $secretKey);

        return hash_equals($expectedSignature, $signature);
    }

    private function redactSensitiveData($payload)
    {
        // Redact sensitive information from the payload; customize as needed
        $data = json_decode($payload, true);
        if (isset($data['card'])) {
            $data['card'] = 'REDACTED';
        }
        return json_encode($data);
    }
}
