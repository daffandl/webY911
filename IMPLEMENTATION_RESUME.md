# Customer Reschedule & Cancel Implementation

## Overview
Implemented customer self-service feature for rescheduling and cancelling bookings, as specified in ANALISIS_DAN_REKOMENDASI_SISTEM.md (Priority 0, Item #3).

## Features Implemented

### 1. Backend (Laravel)

#### Database Migration
- **File:** `backend/database/migrations/2026_04_03_000001_create_booking_cancellations_table.php`
- Created `booking_cancellations` table to track cancellation analytics
- Fields: `booking_id`, `reason_category`, `reason_text`, `cancelled_at`, timestamps

#### API Endpoints
- **POST** `/api/bookings/{code}/reschedule` - Customer reschedule endpoint
- **POST** `/api/bookings/{code}/cancel` - Customer cancel endpoint  
- **GET** `/api/bookings/availability` - Check date availability

#### Controller Methods (`backend/app/Http/Controllers/Api/BookingController.php`)
- `reschedule()` - Handles customer reschedule requests with:
  - Status validation (only `pending` or `confirmed` can be rescheduled)
  - Date availability checking
  - Admin notification via WhatsApp
  
- `customerCancel()` - Handles customer cancellation with:
  - Status validation (only `pending` or `confirmed` can be cancelled)
  - Required reason and category validation
  - Cancellation tracking for analytics
  - Admin notification via WhatsApp

- `availability()` - Returns available slots for a given date

#### Booking Model Updates (`backend/app/Models/Booking.php`)
- `getAvailableSlots()` - Check available booking slots for a date
- `getNextAvailableDate()` - Find next available date from given date
- Configurable max bookings per day via `booking.max_bookings_per_day` (default: 10)

#### Service Layer (`backend/app/Services/BookingService.php`)
- `rescheduleBooking()` - Notify admin of customer reschedule
- `cancelBooking()` - Track cancellation reason and notify admin

#### WhatsApp Notifications (`backend/app/Services/FonnteService.php`)
- `notifyAdminBookingRescheduled()` - Send WA to admin when customer reschedules
- `notifyAdminBookingCancelled()` - Send WA to admin when customer cancels
- Message builders with formatted booking details

### 2. Frontend (Next.js)

#### Tracking Page Updates (`app/tracking/page.tsx`)

**New Components:**
- `RescheduleModal` - Modal for rescheduling bookings
  - Date picker with availability checking
  - Real-time slot availability display
  - Warning when date is full
  - Low slot warning (≤3 slots remaining)
  - Optional reason field
  - Next available date suggestion
  
- `CancelModal` - Modal for cancelling bookings
  - Cancellation reason categories:
    - Change of Plans
    - Found Other Service
    - Price Issue
    - Schedule Conflict
    - Other
  - Required reason text field
  - Warning about irreversible cancellation

**State Management:**
- `showRescheduleModal`, `showCancelModal` - Modal visibility
- `rescheduleSubmitting`, `cancelSubmitting` - Loading states
- `availableSlots` - Date availability data

**Handler Functions:**
- `handleRescheduleSubmit()` - API call to reschedule endpoint
- `handleCancelSubmit()` - API call to cancel endpoint
- `checkAvailability()` - Check date availability

**UI Components:**
- "Kelola Booking" section with Reschedule & Cancel buttons
- Only visible for `pending` or `confirmed` bookings
- Styled with project's design system (clip-path, green theme)

## API Request/Response Examples

### Reschedule Booking

**Request:**
```http
POST /api/bookings/YNG-20260403-001/reschedule
Content-Type: application/json

{
  "preferred_date": "2026-04-10",
  "reason": "Ada acara keluarga di tanggal sebelumnya"
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Booking berhasil direschedule"
}
```

**Response (Error - Date Full):**
```json
{
  "success": false,
  "message": "Tanggal yang dipilih penuh, silakan pilih tanggal lain"
}
```

### Cancel Booking

**Request:**
```http
POST /api/bookings/YNG-20260403-001/cancel
Content-Type: application/json

{
  "reason": "Sudah servis di bengkel lain",
  "cancel_reason_category": "found_other_service"
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Booking berhasil dibatalkan"
}
```

### Check Availability

**Request:**
```http
GET /api/bookings/availability?date=2026-04-10
```

**Response:**
```json
{
  "available": true,
  "slots_remaining": 7,
  "next_available_date": "2026-04-10"
}
```

## Business Benefits

1. **Customer Self-Service** ✅
   - Customers can reschedule/cancel without calling admin
   - 24/7 availability
   - Improved customer experience

2. **Reduced Admin Workload** ✅
   - Automated notifications to admin
   - No manual status updates needed
   - Cancellation reasons tracked automatically

3. **Business Intelligence** ✅
   - Track cancellation reasons for insights
   - Identify patterns (price issues, scheduling conflicts)
   - Data-driven decision making

4. **Overbooking Prevention** ✅
   - Real-time availability checking
   - Configurable max bookings per day
   - Automatic next available date suggestion

## Validation Rules

### Reschedule
- Booking status must be `pending` or `confirmed`
- New date must be in the future
- Date must have available slots

### Cancel
- Booking status must be `pending` or `confirmed`
- Reason is required (max 500 characters)
- Cancellation category is required

## WhatsApp Notification Examples

### Admin - Reschedule Notification
```
📅 BOOKING DIRESCHEDULE — Young 911 Autowerks

Halo Admin, customer telah mereschedule booking:

📋 Kode Booking: YNG-20260403-001
👤 Nama: John Doe
📱 WhatsApp: 628123456789
🚗 Tipe Mobil: Range Rover Sport
🔧 Layanan: Full Service

📆 Tanggal Lama: 03 Apr 2026
📆 Tanggal Baru: 10 Apr 2026

📝 Alasan: Ada acara keluarga
```

### Admin - Cancel Notification
```
🚫 BOOKING DIBATALKAN CUSTOMER — Young 911 Autowerks

Halo Admin, customer telah membatalkan booking:

📋 Kode Booking: YNG-20260403-001
👤 Nama: John Doe
📱 WhatsApp: 628123456789
🚗 Tipe Mobil: Range Rover Sport
🔧 Layanan: Full Service

📊 Kategori Pembatalan: found_other_service
📝 Alasan: Sudah servis di bengkel lain
```

## Testing Checklist

- [x] Migration runs successfully
- [x] API routes registered
- [ ] Reschedule flow (manual testing)
- [ ] Cancel flow (manual testing)
- [ ] Availability checking
- [ ] WhatsApp notifications
- [ ] Frontend modal interactions
- [ ] Form validations
- [ ] Error handling

## Future Enhancements

1. **Email Notifications** - Send confirmation emails to customers
2. **Reschedule Limits** - Limit number of reschedules per booking
3. **Cancellation Fees** - Apply fees for late cancellations
4. **Calendar Integration** - Sync with Google Calendar
5. **SMS Fallback** - Send SMS if WA fails

## Files Changed

### Backend
- `backend/database/migrations/2026_04_03_000001_create_booking_cancellations_table.php` (NEW)
- `backend/app/Http/Controllers/Api/BookingController.php` (MODIFIED)
- `backend/app/Models/Booking.php` (MODIFIED)
- `backend/app/Services/BookingService.php` (MODIFIED)
- `backend/app/Services/FonnteService.php` (MODIFIED)
- `backend/routes/api.php` (MODIFIED)

### Frontend
- `app/tracking/page.tsx` (MODIFIED)

## Configuration

Add to `backend/config/booking.php` (create if not exists):
```php
<?php
return [
    'max_bookings_per_day' => env('BOOKING_MAX_PER_DAY', 10),
];
```

## Environment Variables (Optional)
```env
BOOKING_MAX_PER_DAY=10
```
