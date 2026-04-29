<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_code',
        'user_id',
        'name',
        'phone',
        'email',
        'car_model',
        'vehicle_info',
        'service_type',
        'preferred_date',
        'scheduled_at',
        'notes',
        'admin_notes',
        'status',
        'payment_status',
        'payment_token',
        'transaction_id',
        'service_price',
    ];

    protected $casts = [
        'preferred_date' => 'date',
        'scheduled_at'   => 'datetime',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
        'service_price'  => 'decimal:2',
    ];

    /**
     * Boot method — auto-generate booking_code on creation.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Booking $booking) {
            if (empty($booking->booking_code)) {
                $booking->booking_code = static::generateBookingCode();
            }
        });
    }

    /**
     * Generate a unique booking code with anti-fraud checksum: Y911-YYYYMMDD-XXXXXX-CHECKSUM
     * Uses Luhn algorithm-like checksum to prevent manual manipulation
     */
    public static function generateBookingCode(): string
    {
        $date   = now()->format('Ymd');
        $prefix = "Y911-{$date}-";

        // Count today's bookings to get the sequence number (6 digits)
        $count = static::whereDate('created_at', now()->toDateString())->count() + 1;
        $sequence = str_pad($count, 6, '0', STR_PAD_LEFT);

        // Generate checksum (last 2 digits using hash of components)
        $checksum = self::generateChecksum($prefix . $sequence);

        return $prefix . $sequence . '-' . $checksum;
    }

    /**
     * Generate checksum for booking code (2 characters)
     * Uses HMAC-SHA256 with app key
     */
    private static function generateChecksum(string $data): string
    {
        $key = config('app.key');
        if (str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        $hash = hash_hmac('sha256', $data, $key);
        // Take first 2 characters of hash, convert to uppercase
        return strtoupper(substr($hash, 0, 2));
    }

    /**
     * Verify booking code checksum (prevents manual code manipulation)
     */
    public static function verifyBookingCode(string $code): bool
    {
        // Format: Y911-YYYYMMDD-XXXXXX-XX
        $parts = explode('-', $code);
        if (count($parts) !== 4 || $parts[0] !== 'Y911') {
            return false;
        }

        $baseCode = "{$parts[0]}-{$parts[1]}-{$parts[2]}";
        $expectedChecksum = self::generateChecksum($baseCode);

        return hash_equals($expectedChecksum, $parts[3]);
    }

    /**
     * Get the user who made the booking (if authenticated)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get invoices for this booking
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the latest invoice for this booking
     */
    public function latestInvoice()
    {
        return $this->hasOne(Invoice::class)->latestOfMany();
    }

    /**
     * Get the review for this booking.
     */
    public function review()
    {
        return $this->hasOne(Review::class);
    }

    /**
     * Check if booking has a review.
     */
    public function hasReview(): bool
    {
        return $this->review()->exists();
    }

    /**
     * Scope for filtering by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for today's bookings
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', now()->toDateString());
    }

    /**
     * Find booking by booking_code
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('booking_code', $code);
    }

    /**
     * Get booking status badge color (Filament)
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'     => 'warning',
            'confirmed'   => 'success',
            'rejected'    => 'danger',
            'in_progress' => 'primary',
            'issue'       => 'warning',
            'completed'   => 'success',
            'cancelled'   => 'gray',
            default       => 'gray',
        };
    }

    /**
     * Get payment status badge color
     */
    public function getPaymentStatusColorAttribute(): string
    {
        return match ($this->payment_status) {
            'paid'    => 'success',
            'pending' => 'warning',
            'failed'  => 'danger',
            default   => 'gray',
        };
    }

    /**
     * Human-readable status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'     => 'Menunggu Konfirmasi',
            'confirmed'   => 'Dikonfirmasi',
            'rejected'    => 'Ditolak',
            'in_progress' => 'Sedang Dikerjakan',
            'issue'       => 'Ada Masalah',
            'completed'   => 'Selesai',
            'cancelled'   => 'Dibatalkan',
            default       => ucfirst($this->status),
        };
    }

    /**
     * Get available slots for a given date
     */
    public static function getAvailableSlots(string $date): array
    {
        $bookedCount = self::whereDate('preferred_date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();

        $maxPerDay = config('booking.max_bookings_per_day', 10);
        $available = max(0, $maxPerDay - $bookedCount);

        return [
            'available' => $available > 0,
            'slots_remaining' => $available,
            'next_available_date' => self::getNextAvailableDate($date),
        ];
    }

    /**
     * Get next available date from a given date
     */
    public static function getNextAvailableDate(string $fromDate): string
    {
        $date = \Carbon\Carbon::parse($fromDate);

        for ($i = 0; $i < 30; $i++) {
            $slots = self::getAvailableSlots($date->toDateString());
            if ($slots['available']) {
                return $date->toDateString();
            }
            $date->addDay();
        }

        return $date->toDateString();
    }
}
