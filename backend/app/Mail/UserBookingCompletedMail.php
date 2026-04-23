<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email sent to USER when their booking service is completed (selesai).
 */
class UserBookingCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✨ Layanan Selesai: ' . $this->booking->booking_code . ' — Young 911 Autowerks',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user-booking-completed',
        );
    }
}
