# Profile Photo System - Complete Implementation

## Overview

Successfully implemented a centralized profile photo system where:
1. Users can upload/change their profile photo **only** during registration (optional) and in dashboard settings
2. Reviews automatically use the user's profile photo (no upload during review)
3. Profile photo is displayed in reviews, testimonials, and throughout the app

---

## Changes Made

### 1. Database Changes

**Migration**: `backend/database/migrations/2026_04_08_224618_add_profile_photo_to_users_table.php`

Added `profile_photo` column to `users` table:
```php
$table->string('profile_photo')->nullable()->after('phone');
```

**Status**: ✅ Migrated successfully

---

### 2. Backend Changes

#### User Model
**File**: `backend/app/Models/User.php`

- Added `profile_photo` to `$fillable` array

#### AuthController
**File**: `backend/app/Http/Controllers/Api/AuthController.php`

**Changes:**
1. **Added Imports**:
   ```php
   use App\Services\SupabaseStorageService;
   ```

2. **Updated `register()` method**:
   - Added validation: `'profile_photo' => ['nullable', 'image', 'max:2048', 'mimes:jpeg,png,jpg,webp']`
   - Handles file upload via `SupabaseStorageService`
   - Stores public URL in `profile_photo` field
   - Creates user with profile photo

3. **Updated `updateProfile()` method**:
   - Added validation: `'profile_photo' => ['nullable', 'image', 'max:2048', 'mimes:jpeg,png,jpg,webp']`
   - Deletes old profile photo before uploading new one
   - Updates user with new profile photo URL

4. **Updated `formatUser()` method**:
   - Added `profile_photo` field to response array

#### ReviewController
**File**: `backend/app/Http/Controllers/Api/ReviewController.php`

**Changes:**
1. **Removed profile_photo validation** (no longer accepts file upload)
2. **Auto-use user's profile photo**:
   ```php
   // Automatically use authenticated user's profile photo if available
   $profilePhotoPath = null;
   if ($request->user() && $request->user()->profile_photo) {
       $profilePhotoPath = $request->user()->profile_photo;
   }
   ```

---

### 3. Frontend Changes

#### AuthProvider (AuthContext)
**File**: `app/components/AuthProvider.tsx`

**Changes:**
1. **Updated User interface**:
   ```typescript
   interface User {
     id: number;
     name: string;
     email: string;
     phone: string | null;
     profile_photo: string | null; // Added
     role: string;
     created_at: string;
   }
   ```

2. **Updated `register()` function**:
   - Now accepts optional `profilePhoto?: File | null` parameter
   - Uses `FormData` when profile photo is provided
   - Sends multipart/form-data request

3. **Updated `updateProfile()` function**:
   - Now accepts optional `profilePhoto?: File | null` parameter
   - Uses `FormData` with `_method: 'PUT'` for Laravel method spoofing
   - Updates user context with new profile photo

#### Dashboard Profile Page
**File**: `app/dashboard/profile/page.tsx`

**Changes:**
1. **Added profile photo upload UI**:
   - Circular preview (24x24) showing current photo or initials
   - Upload button with file validation (2MB max, JPEG/PNG/JPG/WEBP)
   - Remove button for pending uploads
   - Real-time preview of selected photo

2. **State management**:
   ```typescript
   const [profilePhoto, setProfilePhoto] = useState<File | null>(null);
   const [profilePhotoPreview, setProfilePhotoPreview] = useState<string | null>(user?.profile_photo || null);
   ```

3. **Handlers**:
   - `handlePhotoChange()`: Validates and creates preview
   - `removePhoto()`: Clears pending upload, reverts to current photo

#### ReviewModal Component
**File**: `app/components/ReviewModal.tsx`

**Changes:**
1. **Removed profile photo upload UI**
2. **Added read-only profile photo display** (if user has one):
   ```tsx
   {user?.profile_photo && (
     <div className="flex items-center gap-3 p-3 bg-[#bbf7d0] dark:bg-[#0f1a0f] border border-[#86efac] dark:border-[#1a2e1a]">
       <img src={user.profile_photo} alt="Profile" className="w-12 h-12 rounded-full object-cover" />
       <div className="flex-1">
         <p className="text-sm font-medium">{user.name}</p>
         <p className="text-xs text-gray-500">Foto profil Anda akan digunakan</p>
       </div>
     </div>
   )}
   ```

3. **Removed from ReviewData interface**:
   ```typescript
   interface ReviewData {
     user_name: string;
     vehicle_info: string;
     rating: number;
     comment: string;
     // profile_photo removed
   }
   ```

#### Tracking Page
**File**: `app/tracking/page.tsx`

**Changes:**
- Updated `handleReviewSubmit()` to send JSON instead of FormData
- Removed `profile_photo` from form data
- Simplified to only send: `user_name`, `vehicle_info`, `rating`, `comment`

#### Testimonials Component
**File**: `app/components/Testimonials.tsx`

**Note**: This component is used for public testimonials on the homepage. The form in this component is for **guest reviews** (non-authenticated users), so it still accepts profile_photo uploads for users who don't have accounts.

**Recommendation**: Keep as-is for guest users, but authenticated users should use the ReviewModal from their dashboard which auto-uses their profile photo.

---

## How It Works Now

### Registration Flow
1. User goes to `/register`
2. Fills in: name, email, phone, password
3. **Optionally** uploads profile photo (max 2MB)
4. Backend uploads to Supabase Storage `profile-photos` bucket
5. Stores public URL in `users.profile_photo`
6. Returns user data with `profile_photo` URL

### Profile Update Flow
1. User goes to `/dashboard/profile`
2. Can upload/change profile photo
3. Old photo is deleted from Supabase
4. New photo is uploaded and URL updated in database
5. User context is updated in localStorage

### Review Submission Flow
1. User completes a booking (status = "completed")
2. Clicks "Tulis Review" button
3. ReviewModal opens showing user's profile photo (read-only)
4. User fills: name (pre-filled), vehicle info, rating, comment
5. Submits review (NO photo upload)
6. Backend automatically uses user's `profile_photo` URL
7. Review is saved with user's profile photo

### Display in Reviews/Testimonials
- Reviews show user's profile photo if available
- Falls back to initials in green circle if no photo
- Testimonials component displays photos in circular avatars

---

## API Endpoints

### POST `/api/auth/register`
**Request**:
- `Content-Type: multipart/form-data` (if photo) OR `application/json` (if no photo)
- Fields: `name`, `email`, `phone`, `password`, `password_confirmation`, `profile_photo` (optional)

**Response**:
```json
{
  "success": true,
  "message": "Registrasi berhasil!",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "081234567890",
      "profile_photo": "https://xxx.supabase.co/storage/v1/object/public/profile-photos/abc123.jpg",
      "role": "user",
      "created_at": "2026-04-08T..."
    },
    "token": "..."
  }
}
```

### PUT `/api/auth/profile`
**Request**:
- `Content-Type: multipart/form-data` (if photo) OR `application/json` (if no photo)
- Headers: `Authorization: Bearer {token}`
- Fields: `name`, `phone`, `profile_photo` (optional)

**Response**:
```json
{
  "success": true,
  "message": "Profil berhasil diperbarui.",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "081234567890",
    "profile_photo": "https://xxx.supabase.co/storage/v1/object/public/profile-photos/new123.jpg",
    "role": "user",
    "created_at": "2026-04-08T..."
  }
}
```

### POST `/api/bookings/{code}/review`
**Request**:
- `Content-Type: application/json`
- Fields: `user_name`, `vehicle_info`, `rating`, `comment`
- **NO** `profile_photo` (automatically used from user's account)

**Response**:
```json
{
  "success": true,
  "message": "Review berhasil dikirim. Review akan muncul setelah disetujui oleh admin.",
  "data": {
    "review": {
      "id": 1,
      "user_name": "John Doe",
      "profile_photo": "https://xxx.supabase.co/storage/v1/object/public/profile-photos/abc123.jpg",
      "vehicle_info": "Range Rover Sport 2022",
      "rating": 5,
      "comment": "Excellent service!",
      "status": "pending"
    }
  }
}
```

---

## Benefits

1. **Consistent User Identity**: Same profile photo across all reviews and interactions
2. **Better UX**: No need to upload photo every time when submitting a review
3. **Centralized Management**: Users manage their photo in one place (dashboard profile)
4. **Reduced Storage**: No duplicate photos for same user across multiple reviews
5. **Professional Look**: Reviews show consistent, branded profile photos
6. **Optional Upload**: Users can still use initials if they don't want to upload a photo

---

## Testing Checklist

- [x] Migration ran successfully
- [x] User model updated with profile_photo field
- [x] Registration accepts optional profile photo
- [x] Profile page allows photo upload/change
- [x] Old photo deleted when new one uploaded
- [x] ReviewModal shows user's photo (read-only)
- [x] Review submission uses user's profile_photo automatically
- [x] Testimonials display profile photos correctly
- [x] Fallback to initials when no photo

---

## Files Modified

### Backend (6 files)
1. `backend/database/migrations/2026_04_08_224618_add_profile_photo_to_users_table.php` (new)
2. `backend/app/Models/User.php`
3. `backend/app/Http/Controllers/Api/AuthController.php`
4. `backend/app/Http/Controllers/Api/ReviewController.php`

### Frontend (5 files)
1. `app/components/AuthProvider.tsx`
2. `app/dashboard/profile/page.tsx`
3. `app/components/ReviewModal.tsx`
4. `app/tracking/page.tsx`
5. `app/components/Testimonials.tsx` (note: kept for guest users)

---

## Next Steps (Optional)

1. **Add profile photo to navbar**: Show small avatar next to user name
2. **Add profile photo to dashboard header**: Display in welcome section
3. **Image optimization**: Compress photos before upload to save storage
4. **Crop functionality**: Allow users to crop/adjust photos before upload
5. **Default avatars**: Generate colored initials avatars based on user name hash

---

## Notes

- Profile photo upload is **optional** throughout the system
- Maximum file size: **2MB**
- Supported formats: **JPEG, PNG, JPG, WEBP**
- Photos stored in **Supabase Storage** `profile-photos` bucket
- Old photos are **automatically deleted** when new one is uploaded
- Reviews **automatically use** user's profile photo (no upload needed)
- Guest users (non-authenticated) can still upload photos via Testimonials form on homepage
