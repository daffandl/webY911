<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email sent to USER when there is an issue with their booking (ada masalah).
 */
class UserBookingIssueMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ Ada Masalah pada Kendaraan: ' . $this->booking->booking_code . ' — Young 911 Autowerks',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user-booking-issue',
        );
    }
}
