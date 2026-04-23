# Young 911 Autowerks - Project Context

## Project Overview

A comprehensive car service booking platform for **Young 911 Autowerks**, a Land Rover specialist. The project combines a Next.js 16 frontend with a Laravel 12 backend, featuring an admin panel, payment processing, and WhatsApp notifications.

### Tech Stack

| Layer | Technology |
|-------|------------|
| **Frontend** | Next.js 16 (canary), React 19, TypeScript |
| **Styling** | Tailwind CSS v4, Custom fonts (Outfit) |
| **Backend** | Laravel 12, PHP 8.2+ |
| **Admin Panel** | Filament 5 |
| **Database** | Supabase (PostgreSQL) |
| **Payment** | Midtrans |
| **Notifications** | Fonnte WhatsApp API |

## Project Structure

```
wey911/
├── app/                      # Next.js frontend
│   ├── components/           # React components
│   │   ├── Navbar.tsx
│   │   ├── Hero.tsx
│   │   ├── About.tsx
│   │   ├── Services.tsx
│   │   ├── Gallery.tsx
│   │   ├── FAQ.tsx
│   │   ├── Maps.tsx
│   │   ├── Blog.tsx
│   │   ├── Booking.tsx
│   │   ├── Footer.tsx
│   │   ├── FloatingBooking.tsx
│   │   ├── Testimonials.tsx
│   │   ├── InvoiceSection.tsx
│   │   ├── AIChatbot.tsx
│   │   └── ThemeProvider.tsx
│   ├── hooks/                # Custom React hooks
│   ├── booking/              # Booking page
│   ├── tracking/             # Order tracking page
│   ├── payment/              # Payment pages
│   ├── layout.tsx            # Root layout with fonts
│   ├── page.tsx              # Home page
│   └── globals.css           # Global styles
├── backend/                  # Laravel backend
│   ├── app/
│   │   ├── Filament/         # Admin panel resources
│   │   │   ├── Resources/    # Booking, Invoice, User, ServiceItem
│   │   │   └── Widgets/      # Dashboard widgets
│   │   ├── Http/             # Controllers, Middleware
│   │   ├── Models/           # Eloquent models (Booking, Invoice, User, Payment)
│   │   ├── Services/         # Business logic (BookingService, PaymentService)
│   │   ├── Notifications/    # WhatsApp notification classes
│   │   ├── Mail/             # Email notification classes
│   │   └── Providers/        # Service providers
│   ├── config/               # Laravel configuration
│   ├── database/             # Migrations, seeders, factories
│   ├── routes/               # API routes (api.php)
│   └── resources/            # Views, assets
├── public/                   # Static assets (fonts, images)
├── package.json              # Frontend dependencies
├── tsconfig.json             # TypeScript config
├── next.config.ts            # Next.js config
├── eslint.config.mjs         # ESLint config
└── pnpm-workspace.yaml       # pnpm workspace config
```

## Building and Running

### Prerequisites

- Node.js 18+
- PHP 8.2+
- Composer
- pnpm

### Frontend (Next.js)

```bash
# Install dependencies
pnpm install

# Start development server
pnpm dev

# Build for production
pnpm build

# Start production server
pnpm start

# Run linter
pnpm lint
```

Frontend runs on: `http://localhost:3000`

### Backend (Laravel)

```bash
cd backend

# Install dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Create admin user
php artisan make:filament-user

# Start development server
php artisan serve
```

Backend API runs on: `http://localhost:8000`
Admin panel: `http://localhost:8000/admin`

## Environment Variables

### Frontend (.env.local)

```env
NEXT_PUBLIC_API_URL=http://localhost:8000/api
NEXT_PUBLIC_MIDTRANS_CLIENT_KEY=your-midtrans-client-key
```

### Backend (.env)

```env
# Supabase Database
DB_CONNECTION=pgsql
DB_HOST=db.xxxxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your-password

# Midtrans Payment Gateway
MIDTRANS_SERVER_KEY=your-server-key
MIDTRANS_CLIENT_KEY=your-client-key
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true

# Fonnte WhatsApp API
FONNTE_API_KEY=your-api-key
FONNTE_TARGET=6281234567890

# Supabase (optional)
SUPABASE_URL=https://your-project.supabase.co
SUPABASE_KEY=your-anon-key
```

## Development Conventions

### Code Style

- **TypeScript**: Strict mode enabled, `esModuleInterop`, `bundler` module resolution
- **ESLint**: Uses `eslint-config-next` with TypeScript support
- **Path Aliases**: `@/*` maps to project root
- **PHP**: PSR-4 autoloading, Laravel conventions

### Component Structure

Components follow a consistent pattern:
- Named exports matching filename (e.g., `Hero.tsx` exports `Hero`)
- Tailwind CSS for styling
- Responsive design with mobile-first approach

### Git Workflow

- Main branch for production
- Feature branches for new development
- Standard `.gitignore` for Next.js, Laravel, and node_modules

## Key Features

### Public Website

1. **Navbar** - Glassmorphism effect, responsive
2. **Hero** - Auto-changing car backgrounds (5s interval)
3. **About** - Company info with statistics grid
4. **Services** - 6 service cards with hover effects
5. **Gallery** - Filterable image grid
6. **FAQ** - Accordion-style Q&A
7. **Maps** - Google Maps embed + contact info
8. **Blog** - News and tips section
9. **Booking Form** - Complete booking system
10. **Floating Booking Button** - Appears on scroll
11. **Testimonials** - Customer reviews
12. **AI Chatbot** - Customer support assistant
13. **Invoice Section** - Invoice viewing and verification
14. **Tracking** - Booking status tracking by code

### Admin Panel (Filament)

- Dashboard with statistics and charts
- Booking management (CRUD + status workflow)
- Invoice management
- Payment tracking
- User management with roles
- Service item management
- WhatsApp notification integration

### Booking Flow

1. Customer submits booking form
2. System creates booking with auto-generated code (YNG-YYYYMMDD-NNN)
3. Admin reviews and manages status workflow:
   - `pending` → `confirmed` → `in_progress` → `completed`
   - Can also transition to `rejected`, `issue`, or `cancelled`
4. Each status change triggers WhatsApp + Email notifications
5. Payment status tracked via Midtrans

## API Endpoints

### Public

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/bookings` | Create booking |
| GET | `/api/bookings/track/{code}` | Track booking by code |
| GET | `/api/bookings/track/{code}/invoice` | Get invoice by booking code |
| GET | `/api/verify` | Verify invoice authenticity |
| GET | `/api/bookings/statistics` | Get statistics |
| GET | `/api/payment/{invoiceNumber}/status` | Get payment status |
| POST | `/api/payment/{invoiceNumber}/generate` | Generate payment link |
| POST | `/api/midtrans/notification` | Midtrans webhook handler |

### Protected (Auth Required)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/bookings` | List bookings |
| PATCH | `/api/bookings/{booking}/status` | Update status |
| POST | `/api/bookings/{booking}/cancel` | Cancel booking |

## Database Schema

### bookings

- `id`, `user_id`
- `booking_code` (auto-generated: YNG-YYYYMMDD-NNN)
- `name`, `phone`, `email`
- `car_model`, `vehicle_info`
- `service_type` (maintenance/repair/diagnostic/oil-change/brakes/other)
- `preferred_date`, `scheduled_at`
- `notes`, `admin_notes`
- `status` (pending/confirmed/rejected/in_progress/issue/completed/cancelled)
- `payment_status` (pending/paid/failed)
- `payment_token`, `transaction_id`
- `timestamps`

### invoices

- `id`, `booking_id`
- `invoice_number`, `items` (JSONB)
- `subtotal`, `tax`, `discount`, `total`
- `status`, `notes`
- `payment_url`, `payment_status`
- `midtrans_transaction_id`
- `timestamps`

### payments

- `id`, `invoice_id`
- `amount`, `payment_method`
- `transaction_id`, `status`
- `paid_at`
- `timestamps`

### users

- `id`, `name`, `email`, `password`
- `role` (user/admin), `phone`
- `email_verified_at`, `remember_token`
- `timestamps`

### service_items

- `id`, `name`, `description`
- `price`, `category`
- `is_active`
- `timestamps`

## Security Features

- CSRF protection
- SQL injection prevention
- XSS protection
- Rate limiting on API
- Bcrypt password hashing
- Sanctum token authentication
- Role-based access control

## Troubleshooting

### Frontend

```bash
# Clear Next.js cache
rm -rf .next
pnpm dev
```

### Backend

```bash
# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Fresh migrations
php artisan migrate:fresh
```

### Permissions (Linux/Termux)

```bash
chmod -R 775 backend/storage
chmod -R 775 backend/bootstrap/cache
```

## Testing

```bash
# Backend tests
cd backend
php artisan test

# Frontend tests
pnpm test
```

## Deployment

### Frontend (Vercel/Netlify)

```bash
pnpm build
pnpm start
```

### Backend (VPS/Cloud)

```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Resources

- [Next.js Docs](https://nextjs.org/docs)
- [Laravel Docs](https://laravel.com/docs)
- [Filament Docs](https://filamentphp.com/docs)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [Midtrans Docs](https://docs.midtrans.com)
- [Fonnte API](https://fonnte.com/api-doc)

## Filament v5 Notes

This project uses Filament v5. Key differences from v3:

1. **Type Declarations Updated**
   - `$navigationIcon`: `string | \BackedEnum | null`
   - `$navigationGroup`: `string | \UnitEnum | null`

2. **Form → Schema Migration**
   - `Form` replaced with `Schema` from `Filament\Schemas`
   - Method signature: `schema(Schema $schema): Schema`

3. **Actions Namespace**
   - Actions moved to `Filament\Actions` namespace
   - BadgeColumn replaced with `TextColumn::badge()`

### Current Versions

- **Filament**: v5.4.1
- **Livewire**: v4.2.1
- **Laravel**: v12.55.1
- **PHP**: 8.5.1

## Support

- Email: info@young911autowerks.com
- Phone: +62 812 3456 7890
