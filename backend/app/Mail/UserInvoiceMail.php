<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email sent to USER when their invoice is ready.
 */
class UserInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Invoice $invoice) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🧾 Invoice ' . $this->invoice->invoice_number . ' — Young 911 Autowerks',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user-invoice',
        );
    }
}
