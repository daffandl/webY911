<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email sent to ADMIN when a new booking is created.
 */
class AdminNewBookingMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔔 Booking Baru: ' . $this->booking->booking_code . ' — Young 911 Autowerks',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-new-booking',
        );
    }
}
