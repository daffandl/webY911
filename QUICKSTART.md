# Quick Start Guide - Young 911 Autowerks

## 🚀 Start Development

### Terminal 1 - Frontend (Next.js)
```bash
cd /data/data/com.termux/files/home/wey911
pnpm dev
```
Frontend: http://localhost:3000

### Terminal 2 - Backend (Laravel)
```bash
cd /data/data/com.termux/files/home/wey911/backend
php artisan serve
```
Backend API: http://localhost:8000
Admin Panel: http://localhost:8000/admin

## 📋 First Time Setup

### 1. Install Backend Dependencies
```bash
cd backend
composer install
```

### 2. Configure Environment
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your credentials:
- Supabase database
- Midtrans payment
- Fonnte WhatsApp

### 3. Run Database Migrations
```bash
php artisan migrate
```

### 4. Create Admin User
```bash
php artisan make:filament-user
```

Enter your admin credentials when prompted.

## 🎯 What You Get

### Public Website (localhost:3000)
- ✅ Glassmorphism navbar with booking button
- ✅ Auto-changing hero background (5 car images)
- ✅ About section with stats
- ✅ 6 service cards with hover effects
- ✅ Filterable gallery
- ✅ FAQ accordion
- ✅ Google Maps preview
- ✅ Blog section
- ✅ Booking form
- ✅ Footer with social links
- ✅ Floating booking button (appears on scroll)

### Admin Panel (localhost:8000/admin)
- ✅ Dashboard with analytics
- ✅ Booking management (CRUD)
- ✅ WhatsApp notifications (Fonnte)
- ✅ Payment processing (Midtrans)
- ✅ User management
- ✅ Collapsible sidebar with icons
- ✅ Real-time statistics

## 🔧 Configuration Examples

### Supabase (PostgreSQL)
Get your credentials from https://supabase.com:
```env
DB_HOST=db.xxxxx.supabase.co
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your-password
```

### Midtrans (Payment Gateway)
Get keys from https://dashboard.midtrans.com:
```env
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxx
MIDTRANS_IS_PRODUCTION=false
```

### Fonnte (WhatsApp API)
Get API key from https://fonnte.com:
```env
FONNTE_API_KEY=your-api-key
FONNTE_TARGET=6281234567890
```

## 📱 Test Booking Flow

1. **Customer Books Service**
   - Visit http://localhost:3000
   - Scroll to booking form
   - Fill in details and submit

2. **System Actions**
   - Creates booking in database
   - Generates Midtrans payment URL
   - Sends WhatsApp to customer
   - Sends WhatsApp to admin

3. **Admin Review**
   - Login to http://localhost:8000/admin
   - View booking in dashboard
   - Update status (Pending → Confirmed)
   - Customer receives WhatsApp notification

## 🎨 Customization

### Change Brand Colors
Edit `backend/app/Providers/FilamentPanelProvider.php`:
```php
->colors([
    'primary' => Color::Red, // Change to your color
])
```

### Update Company Info
- Frontend: Edit components in `app/components/`
- Backend: Update `.env` variables
- Maps: Change embed URL in `app/components/Maps.tsx`

### Add More Services
Edit `app/components/Services.tsx`:
```typescript
const services = [
  {
    icon: ...,
    title: 'New Service',
    description: 'Description',
    price: 'From Rp 1M',
  },
];
```

## 🐛 Troubleshooting

### Frontend Build Errors
```bash
rm -rf .next
pnpm dev
```

### Backend Migration Errors
```bash
php artisan migrate:fresh
```

### Clear All Caches
```bash
cd backend
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Permission Issues (Linux/Mac)
```bash
chmod -R 775 backend/storage
chmod -R 775 backend/bootstrap/cache
```

## 📚 Learn More

- Next.js: https://nextjs.org/docs
- Laravel: https://laravel.com/docs
- Filament: https://filamentphp.com/docs
- Tailwind CSS: https://tailwindcss.com/docs
- Midtrans: https://docs.midtrans.com
- Fonnte: https://fonnte.com/api-doc

## ✅ Pre-Launch Checklist

- [ ] Update all `.env` credentials
- [ ] Run migrations
- [ ] Create admin user
- [ ] Test booking form
- [ ] Test payment gateway
- [ ] Test WhatsApp notifications
- [ ] Update maps location
- [ ] Change favicon
- [ ] Update metadata/SEO
- [ ] Enable HTTPS
- [ ] Set Midtrans to production mode

---

Need help? Contact: info@young911autowerks.com
