# AmanSpace

AmanSpace adalah aplikasi web yang aman untuk mengelola data dan file pribadi. Dibangun dengan Laravel framework yang mengimplementasikan berbagai fitur keamanan esensial termasuk autentikasi pengguna, pengiriman data, dan manajemen file yang aman.

## 📋 Daftar Isi

- [Cara Menjalankan Aplikasi](#cara-menjalankan-aplikasi)
- [Penjelasan Fitur](#penjelasan-fitur)
- [Aspek Keamanan yang Diterapkan](#aspek-keamanan-yang-diterapkan)
- [Installation & Setup](#installation--setup)
- [Technology Stack](#technology-stack)

---

## 🚀 Cara Menjalankan Aplikasi

### Mode Development

1. **Jalankan Laravel development server:**
   ```bash
   php artisan serve
   ```

2. **Di terminal lain, jalankan Vite dev server (untuk hot reload):**
   ```bash
   npm run dev
   ```

3. **Akses aplikasi di browser:**
   ```
   http://localhost:8000
   ```

### Mode Production

1. **Build assets:**
   ```bash
   npm run build
   ```

2. **Optimasi Laravel:**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

3. **Konfigurasi web server (Apache/Nginx)** untuk mengarah ke direktori `public`

### Membuat Admin User

Untuk membuat user admin, gunakan command berikut:

```bash
php artisan user:make-admin
```

Command ini akan meminta:
- Email admin
- Nama admin
- Password admin

Atau untuk mempromote user yang sudah ada menjadi admin:

```bash
php artisan user:make-admin user@example.com
```

---

## ✨ Penjelasan Fitur

### 1. Manajemen User

#### Registrasi & Login
- **Registrasi User**: Form registrasi dengan validasi email dan password yang kuat
- **Login**: Autentikasi dengan verifikasi password menggunakan bcrypt
- **Session Management**: Manajemen session yang aman dengan penyimpanan di database
- **Rate Limiting**: Pembatasan percobaan login (maksimal 5 kali per IP per menit)
- **IP Blocking**: Blokir otomatis setelah 5 percobaan login gagal selama 15 menit

#### Role & Status User
- **Role Management**: Sistem role dengan 2 level (user dan admin)
- **User Status**: Fitur untuk mengaktifkan/nonaktifkan akun user
- **Default Role**: User baru otomatis mendapat role 'user' dan status aktif

### 2. Data Submission

- **Buat Submission**: Form untuk membuat submission dengan judul (opsional) dan konten
- **Lihat Submission**: Daftar semua submission milik user
- **Hapus Submission**: User dapat menghapus submission miliknya sendiri
- **Access Control**: User hanya dapat melihat dan mengelola submission miliknya sendiri
- **Input Validation**: Semua input divalidasi dan disanitasi (HTML tags dihapus)

### 3. Manajemen File

- **Upload File**: Upload file dengan validasi dan sanitasi
  - Tipe file yang diizinkan: JPEG, PNG, GIF, WebP, PDF, TXT, DOC, DOCX
  - Ukuran maksimal: 10 MB
  - Validasi MIME type untuk keamanan
  - Sanitasi nama file (menghapus karakter berbahaya)
  - Penyimpanan dengan nama unik (random) untuk mencegah konflik dan path traversal
  
- **Download File**: Download file dengan kontrol akses
  - Hanya pemilik file yang dapat mengunduh
  - Redirect dengan pesan error jika mencoba mengakses file orang lain
  
- **Daftar File**: Tampilkan semua file yang diupload oleh user
- **Hapus File**: User dapat menghapus file miliknya sendiri

### 4. Admin Panel

Fitur admin hanya dapat diakses oleh user dengan role 'admin':

- **Daftar User**: Melihat semua user yang terdaftar di sistem
  - Informasi: ID, Nama, Email, Role, Status, Tanggal Registrasi
  - Pagination untuk kemudahan navigasi
  
- **Nonaktifkan User**: Admin dapat menonaktifkan akun user
  - User yang dinonaktifkan tidak dapat login
  - Admin tidak dapat menonaktifkan akun sendiri atau admin lain
  
- **Hapus User**: Admin dapat menghapus user dari sistem
  - Semua data terkait (file, submission) akan ikut terhapus (cascade)
  - Admin tidak dapat menghapus akun sendiri atau admin lain
  
- **Proteksi Admin**: 
  - Admin tidak dapat menghapus/menonaktifkan akun sendiri
  - Admin tidak dapat menghapus/menonaktifkan admin lain
  - Semua aksi admin dicatat dalam audit log

---

## 🔒 Aspek Keamanan yang Diterapkan

### 1. Password Hashing (bcrypt/argon2)

✅ **Implementasi:**
- Menggunakan `Hash::make()` dengan bcrypt (default Laravel)
- Model User menggunakan cast `'password' => 'hashed'` (auto-hash saat assign)
- Password tidak pernah disimpan dalam plaintext
- Password requirements: minimal 8 karakter, harus ada huruf besar, huruf kecil, angka, dan simbol

**Lokasi:** 
- `app/Http/Controllers/AuthController.php` (line 51)
- `app/Models/User.php` (line 46)

### 2. CSRF Protection

✅ **Implementasi:**
- Semua form menggunakan `@csrf` directive
- CSRF token di meta tag untuk AJAX requests
- Laravel middleware otomatis memvalidasi CSRF token
- Same-Site cookie attribute set ke 'lax' untuk CSRF protection

**Lokasi:**
- Semua form di `resources/views/**/*.blade.php` menggunakan `@csrf`
- `resources/views/layouts/app.blade.php` (line 6)
- `config/session.php` (line 202)

### 3. Input Validation

✅ **Implementasi:**
- Server-side validation menggunakan Laravel Validator
- Validasi untuk semua input (email, password, file, content)
- Input sanitization menggunakan `strip_tags()` untuk text inputs
- File type validation (MIME type checking)
- File size validation (max 10MB)

**Validasi yang diterapkan:**
- Email: format email, unique, max 255
- Password: required, confirmed, minimal 8 karakter dengan kompleksitas tinggi
- Submission: title (nullable, max 255), content (required, max 10000)
- File: required, file type, max size (10MB), MIME type whitelist

**Lokasi:**
- `app/Http/Controllers/AuthController.php`
- `app/Http/Controllers/SubmissionController.php`
- `app/Http/Controllers/FileController.php`

### 4. File Upload Sanitization

✅ **Implementasi:**
- MIME type validation (whitelist approach)
- Filename sanitization (remove dangerous characters)
- Unique stored filename (prevent conflicts & path traversal)
- File size limit (10MB)
- Storage di private directory (tidak publicly accessible)
- Extension validation

**Sanitization yang diterapkan:**
1. MIME type check: Hanya file dengan MIME type yang diizinkan
2. Filename sanitization: `preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName)`
3. Unique stored name: `Str::random(40) . '.' . $extension`
4. Private storage: `storage/app/private/uploads/{user_id}/`
5. DoS Protection: Validasi ukuran file eksplisit dalam bytes sebelum pemrosesan

**Lokasi:**
- `app/Http/Controllers/FileController.php`:
  - Line 15-24: Allowed MIME types whitelist
  - Line 27: Max file size constant
  - Line 75-85: File size validation & MIME type validation
  - Line 95-99: Filename sanitization & unique name generation
  - Line 111: Private storage

### 5. Access Control untuk Download

✅ **Implementasi:**
- Authorization check di controller method
- Query filtering (hanya menampilkan file milik user)
- Redirect dengan pesan error yang user-friendly (bukan 403 page)
- 403 Forbidden response untuk unauthorized access di middleware admin

**Protection Layers:**
1. Query filtering: Hanya file milik user yang ditampilkan di list (`where('user_id', Auth::id())`)
2. Controller check: Verifikasi ownership sebelum download
3. User-friendly error: Redirect ke halaman sendiri dengan pesan error, bukan 403 page

**Lokasi:**
- `app/Http/Controllers/FileController.php`:
  - Line 37: Query filter `where('user_id', Auth::id())`
  - Line 136-142: Authorization check di download()
  - Line 155-161: Authorization check di destroy()

### 6. Tidak Ada Hardcoded Secrets

✅ **Implementasi:**
- Semua secrets menggunakan environment variables (.env)
- APP_KEY di-generate via `php artisan key:generate`
- Database credentials di .env
- Tidak ada password/secret hardcoded di source code

**Verifikasi:**
- ✅ `.env` file di .gitignore (tidak di-commit)
- ✅ `.env.example` sebagai template
- ✅ Semua config menggunakan `env()` helper
- ✅ Tidak ada hardcoded credentials di code

**Lokasi:**
- `config/app.php`: `env('APP_KEY')`
- `config/database.php`: `env('DB_*')`
- `config/session.php`: `env('SESSION_*')`

### 7. Tidak Menyimpan Password Plaintext

✅ **Implementasi:**
- Password selalu di-hash dengan bcrypt sebelum disimpan
- Model User menggunakan cast `'password' => 'hashed'`
- Password tidak pernah ditampilkan atau di-return dalam response
- Password di hidden attributes

**Verifikasi:**
- ✅ Password selalu di-hash dengan bcrypt sebelum save
- ✅ Password tidak pernah di-return dalam JSON/response
- ✅ Password tidak ada di logs atau error messages

**Lokasi:**
- `app/Models/User.php`:
  - Line 32-34: `protected $hidden = ['password', ...]`
  - Line 46: `'password' => 'hashed'` (auto-hash)
- `app/Http/Controllers/AuthController.php`:
  - Line 51: `Hash::make($request->password)` (explicit hash)

### 8. Session Harus Secure

✅ **Implementasi:**
- Session driver: database (lebih secure dari file)
- HTTP-only cookies: `true` (prevent XSS)
- Same-Site cookies: `'lax'` (CSRF protection)
- Session encryption: Configurable via env
- Session regeneration: On login
- Secure cookie: Configurable via env (untuk HTTPS)

**Security Features:**
1. Database storage: Session disimpan di database (lebih secure)
2. HTTP-only: JavaScript tidak bisa akses session cookie
3. Same-Site: Mencegah CSRF attacks
4. Regeneration: Session ID di-regenerate saat login
5. Encryption: Optional session encryption

**Lokasi:**
- `config/session.php`:
  - Line 21: `'driver' => env('SESSION_DRIVER', 'database')`
  - Line 185: `'http_only' => env('SESSION_HTTP_ONLY', true)`
  - Line 202: `'same_site' => env('SESSION_SAME_SITE', 'lax')`
  - Line 50: `'encrypt' => env('SESSION_ENCRYPT', false)`
- `app/Http/Controllers/AuthController.php`:
  - Line 109: `$request->session()->regenerate()` (on login)

### 9. Security Headers

✅ **Implementasi:**
- Content Security Policy (CSP) header untuk mencegah XSS
- X-Frame-Options: DENY (mencegah clickjacking)
- X-Content-Type-Options: nosniff (mencegah MIME type sniffing)
- X-Powered-By header dihilangkan (mencegah information disclosure)
- Referrer-Policy dan Permissions-Policy

**Lokasi:**
- `app/Http/Middleware/SecurityHeaders.php`

### 10. Rate Limiting & IP Blocking

✅ **Implementasi:**
- Login rate limiting: Max 5 attempts per IP per minute
- IP-based blocking: Automatic lockout setelah 5 failed attempts
- Database tracking: Semua login attempts dicatat dengan IP address
- Lockout duration: 15 menit
- Remaining attempts warning: User diperingatkan tentang sisa percobaan

**Lokasi:**
- `app/Http/Controllers/AuthController.php` (line 78-92)
- `app/Models/LoginAttempt.php`

### 11. Audit Logging

✅ **Implementasi:**
- Comprehensive audit trail: Semua event keamanan dicatat ke database
- Authentication logging: Login, logout, registration, failed attempts
- Authorization violations: Unauthorized access attempts dicatat
- File operations: Upload, download, delete operations tracked
- Data modifications: Submission create/delete tracked
- IP & User Agent tracking: Context lengkap untuk setiap aksi

**Lokasi:**
- `app/Services/AuditLogService.php`
- `app/Models/AuditLog.php`

---

## 📦 Installation & Setup

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js and npm
- Database (MySQL, PostgreSQL, or SQLite)

### Step 1: Clone the Repository

```bash
git clone <repository-url>
cd miniweb
```

### Step 2: Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### Step 3: Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Step 4: Configure Database

Edit `.env` file and set your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=miniweb
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Step 5: Run Migrations

```bash
php artisan migrate
```

### Step 6: Build Frontend Assets

```bash
# For development
npm run dev

# For production
npm run build
```

### Step 7: Seed Test User (Development Only)

```bash
php artisan db:seed --class=UserSeeder
```

> **⚠️ SECURITY WARNING**: Default users are **ONLY** created in development/testing environments. 
> They will **NOT** be created in production for security reasons.

Ini akan membuat 1 test user untuk testing (hanya di environment local/development):
- **Test User**: 
  - Email: `test@example.com`
  - Password: `password123`

**Untuk Production**: 
- Default users otomatis di-skip
- Buat admin account menggunakan command: `php artisan user:make-admin`
- Gunakan password yang kuat dan unik

### Step 8: Create Admin User

Untuk membuat admin user pertama kali:

```bash
php artisan user:make-admin
```

Ikuti instruksi di command untuk memasukkan email, nama, dan password admin.

---

## 🛠 Technology Stack

- **Framework**: Laravel 12.x
- **PHP**: PHP 8.2+
- **Database**: MySQL/PostgreSQL/SQLite (configurable)
- **Frontend**: Tailwind CSS 4.0
- **Build Tool**: Vite

---

## 📖 Usage Guide

### User Registration

1. Navigate to `/register`
2. Fill in name, email, and password
3. Click "Register"
4. You will be automatically logged in

### User Login

1. Navigate to `/login`
2. Enter your email and password
3. Click "Login"
4. You will be redirected to the dashboard

### Creating a Submission

1. After logging in, go to Dashboard
2. Click "Create New" under Submissions
3. Enter title (optional) and content
4. Click "Create Submission"

### Uploading a File

1. Go to Dashboard
2. Click "Upload File" under Files
3. Select a file (allowed types: JPEG, PNG, GIF, WebP, PDF, TXT, DOC, DOCX)
4. Maximum file size: 10 MB
5. Click "Upload File"

### Downloading a File

1. Go to "My Files" page
2. Click "Download" next to the file you want
3. Only files you uploaded can be downloaded

### Admin Panel

1. Login sebagai admin
2. Klik "Admin Panel" di navbar atau card di dashboard
3. Kelola user: lihat daftar, disable/enable, atau hapus user

---

## 🧪 Testing

To run tests:

```bash
php artisan test
```

---

## 🔧 Troubleshooting

### Database Connection Error

- Check `.env` file database credentials
- Ensure database exists
- Run `php artisan migrate`

### File Upload Not Working

- Check `storage/app/private` directory permissions
- Ensure directory exists: `storage/app/private/uploads`
- Check PHP `upload_max_filesize` and `post_max_size` settings

### Session Not Working

- Ensure database migrations are run
- Check `SESSION_DRIVER` in `.env` (should be 'database')
- Clear cache: `php artisan config:clear`

---

## 📝 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## 👥 Authors

Group of 3 students - AmanSpace Project

---

## 🙏 Acknowledgments

- Laravel Framework
- Tailwind CSS
- All security best practices implemented
