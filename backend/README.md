# Young 911 Autowerks - Backend Setup Guide

## Prerequisites

- PHP 8.2 or higher
- Composer
- PostgreSQL (Supabase)
- Node.js & npm (for frontend)

## Installation Steps

### 1. Install Dependencies

```bash
cd backend
composer install
```

### 2. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` file and configure:

#### Database (Supabase)
```
DB_CONNECTION=pgsql
DB_HOST=db.xxxxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your-supabase-password
```

#### Midtrans Payment Gateway
```
MIDTRANS_SERVER_KEY=your-midtrans-server-key
MIDTRANS_CLIENT_KEY=your-midtrans-client-key
MIDTRANS_IS_PRODUCTION=false
```

#### Fonnte WhatsApp API
```
FONNTE_API_KEY=your-fonnte-api-key
FONNTE_TARGET=6281234567890
```

### 3. Run Migrations

```bash
php artisan migrate
```

### 4. Create Admin User

```bash
php artisan make:filament-user
```

Follow the prompts to create an admin account.

### 5. Start Development Server

```bash
php artisan serve
```

The admin panel will be available at: `http://localhost:8000/admin`

### 6. API Endpoints

- `POST /api/bookings` - Create new booking
- `GET /api/bookings` - List all bookings (authenticated)
- `GET /api/bookings/{id}` - Get booking details (authenticated)
- `PATCH /api/bookings/{id}/status` - Update booking status (authenticated)
- `POST /api/bookings/{id}/cancel` - Cancel booking (authenticated)
- `GET /api/bookings/statistics` - Get booking statistics

## Admin Panel Features

### Dashboard
- Total bookings statistics
- Weekly bookings chart
- Service distribution chart
- Recent bookings table
- Revenue tracking

### Booking Management
- View all bookings with filters
- Create new bookings
- Edit booking details
- Update booking status
- Send WhatsApp notifications
- View payment status

### User Management
- View all users
- Create admin users
- Edit user roles
- Delete users

## Technologies Used

- **Framework**: Laravel 12
- **Admin Panel**: Filament 3
- **Database**: PostgreSQL (Supabase)
- **Payment**: Midtrans
- **WhatsApp**: Fonnte API

## Security Notes

1. Change all default credentials
2. Enable production mode for Midtrans in production
3. Use HTTPS in production
4. Regularly backup your database
5. Keep all dependencies updated

## Troubleshooting

### Migration Issues
```bash
php artisan migrate:fresh --seed
```

### Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Optimize for Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Support

For issues or questions, contact:
- Email: info@young911autowerks.com
- Phone: +62 812 3456 7890
