# Young 911 Autowerks - Land Rover Specialist Website

A comprehensive car service booking platform built with Next.js 16, Laravel 12, Filament Admin Panel, Supabase, Midtrans, and Fonnte WhatsApp API.

## 🚀 Tech Stack

### Frontend
- **Framework**: Next.js 16
- **Styling**: Tailwind CSS v4
- **Components**: Custom React components

### Backend
- **Framework**: Laravel 12
- **Admin Panel**: Filament 3
- **Database**: Supabase (PostgreSQL)
- **Payment Gateway**: Midtrans
- **WhatsApp Notification**: Fonnte API

## 📁 Project Structure

```
wey911/
├── app/                    # Next.js frontend
│   ├── components/         # React components
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
│   │   └── FloatingBooking.tsx
│   ├── layout.tsx
│   ├── page.tsx
│   └── globals.css
├── backend/                # Laravel backend
│   ├── app/
│   │   ├── Filament/       # Admin panel resources
│   │   ├── Http/
│   │   ├── Models/
│   │   ├── Services/
│   │   └── Providers/
│   ├── config/
│   ├── database/
│   ├── routes/
│   └── resources/
└── package.json
```

## 🎨 Website Features

### Public Website
1. **Navbar** - Glassmorphism effect with booking button
2. **Hero Section** - Auto-changing car backgrounds (5s interval), centered title/subtitle, booking CTA
3. **About Section** - Company info with stats grid
4. **Services Section** - 6 service cards with hover effects
5. **Gallery Section** - Filterable image grid
6. **FAQ Section** - Accordion-style Q&A
7. **Maps Section** - Google Maps embed + contact info
8. **Blog Section** - Latest news & tips
9. **Booking Form** - Complete booking system
10. **Footer** - Links, social media, contact info
11. **Floating Booking Button** - Appears on scroll

### Admin Panel (Filament)
1. **Dashboard**
   - Statistics cards (Total, Pending, In Progress, Revenue)
   - Weekly bookings chart
   - Service distribution chart
   - Recent bookings table

2. **Booking Management**
   - List all bookings with filters
   - Create/Edit/View bookings
   - Status management (Pending → Confirmed → In Progress → Completed)
   - WhatsApp notification integration
   - Payment status tracking

3. **User Management**
   - User list with roles
   - Create admin users
   - Role management

4. **Navigation**
   - Collapsible sidebar
   - Icon-based menu items
   - Badge notifications

## 🛠️ Installation

### Frontend Setup

```bash
# Install dependencies
pnpm install

# Start development server
pnpm dev
```

Frontend will be available at `http://localhost:3000`

### Backend Setup

```bash
cd backend

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Configure database and run migrations
php artisan migrate

# Create admin user
php artisan make:filament-user

# Start server
php artisan serve
```

Backend API will be available at `http://localhost:8000`
Admin panel at `http://localhost:8000/admin`

## ⚙️ Configuration

### Environment Variables

#### Frontend (.env.local)
```
NEXT_PUBLIC_API_URL=http://localhost:8000/api
NEXT_PUBLIC_MIDTRANS_CLIENT_KEY=your-midtrans-client-key
```

#### Backend (.env)

**Database (Supabase)**
```env
DB_CONNECTION=pgsql
DB_HOST=db.xxxxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your-supabase-password
```

**Midtrans**
```env
MIDTRANS_SERVER_KEY=your-midtrans-server-key
MIDTRANS_CLIENT_KEY=your-midtrans-client-key
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true
```

**Fonnte WhatsApp**
```env
FONNTE_API_KEY=your-fonnte-api-key
FONNTE_TARGET=6281234567890
```

**Supabase**
```env
SUPABASE_URL=https://your-project.supabase.co
SUPABASE_KEY=your-supabase-anon-key
```

## 📱 API Endpoints

### Public Endpoints
- `POST /api/bookings` - Create new booking
- `GET /api/bookings/statistics` - Get booking statistics

### Protected Endpoints (Requires Authentication)
- `GET /api/bookings` - List all bookings
- `GET /api/bookings/{id}` - Get booking details
- `PATCH /api/bookings/{id}/status` - Update booking status
- `POST /api/bookings/{id}/cancel` - Cancel booking

## 🔐 Security Features

- CSRF protection
- SQL injection prevention
- XSS protection
- Rate limiting on API endpoints
- Secure password hashing (bcrypt)
- Sanctum API token authentication
- Role-based access control (Admin/User)

## 📊 Database Schema

### Bookings Table
- id, user_id
- name, phone, email
- car_model, service_type
- preferred_date, notes
- status (pending/confirmed/in_progress/completed/cancelled)
- payment_status (pending/paid/failed)
- payment_token, transaction_id
- timestamps

### Users Table
- id, name, email, password
- role (user/admin), phone
- email_verified_at, remember_token
- timestamps

## 🎯 Key Features

### Booking Flow
1. Customer fills booking form on website
2. System creates booking with "pending" status
3. Midtrans payment URL generated
4. WhatsApp confirmation sent to customer (Fonnte)
5. WhatsApp notification sent to admin
6. Admin reviews and confirms booking
7. Status updates trigger WhatsApp notifications
8. Payment status tracked via Midtrans webhooks

### WhatsApp Notifications (Fonnte)
- Booking confirmation to customer
- New booking alert to admin
- Status update notifications
- Cancellation confirmations

### Payment Processing (Midtrans)
- Credit/Debit cards
- Bank transfers
- E-wallets (GoPay, OVO, Dana, etc.)
- Installment options
- Automatic payment status updates

## 🚀 Deployment

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

## 📝 Admin Panel Usage

1. Login to `/admin` with admin credentials
2. Dashboard shows overview statistics
3. Navigate to "Bookings" to manage appointments
4. Use filters to find specific bookings
5. Click on booking to view/edit details
6. Use "Notify" action to send WhatsApp updates
7. Manage users in "User Management" section

## 🧪 Testing

```bash
# Backend tests
cd backend
php artisan test

# Frontend tests
pnpm test
```

## 📄 License

MIT License - See LICENSE file for details

## 👥 Support

For technical support:
- Email: info@young911autowerks.com
- Phone: +62 812 3456 7890

## 🙏 Credits

- Next.js Team
- Laravel Team
- Filament Team
- Tailwind CSS Team
- Midtrans
- Fonnte
- Supabase

---

Built with ❤️ for Young 911 Autowerks
