================================================================================
                            AMANSPACE - README
================================================================================

AmanSpace adalah aplikasi web yang aman untuk mengelola data dan file pribadi.
Dibangun dengan Laravel framework yang mengimplementasikan berbagai fitur 
keamanan esensial.

================================================================================
                        CARA MENJALANKAN APLIKASI
================================================================================

MODE DEVELOPMENT
----------------

1. Jalankan Laravel development server:
   php artisan serve

2. Di terminal lain, jalankan Vite dev server (untuk hot reload):
   npm run dev

3. Akses aplikasi di browser:
   http://localhost:8000


MODE PRODUCTION
---------------

1. Build assets:
   npm run build

2. Optimasi Laravel:
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache

3. Konfigurasi web server (Apache/Nginx) untuk mengarah ke direktori public


MEMBUAT ADMIN USER
------------------

Untuk membuat user admin, gunakan command berikut:

php artisan user:make-admin

Command ini akan meminta:
- Email admin
- Nama admin
- Password admin

Atau untuk mempromote user yang sudah ada menjadi admin:

php artisan user:make-admin user@example.com


INSTALASI AWAL
--------------

1. Install dependencies:
   composer install
   npm install

2. Setup environment:
   cp .env.example .env
   php artisan key:generate

3. Konfigurasi database di file .env

4. Run migrations:
   php artisan migrate

5. Seed test user (hanya development):
   php artisan db:seed --class=UserSeeder

   Test User:
   - Email: test@example.com
   - Password: password123


================================================================================
                            PENJELASAN FITUR
================================================================================

1. MANAJEMEN USER
-----------------

Registrasi & Login
- Form registrasi dengan validasi email dan password yang kuat
- Autentikasi dengan verifikasi password menggunakan bcrypt
- Manajemen session yang aman dengan penyimpanan di database
- Rate limiting: Pembatasan percobaan login (maksimal 3 kali per IP per menit)
- IP Blocking: Blokir otomatis setelah 3 percobaan login gagal selama 15 menit

Role & Status User
- Sistem role dengan 2 level (user dan admin)
- Fitur untuk mengaktifkan/nonaktifkan akun user
- User baru otomatis mendapat role 'user' dan status aktif


2. DATA SUBMISSION
------------------

- Buat Submission: Form untuk membuat submission dengan judul (opsional) dan konten
- Lihat Submission: Daftar semua submission milik user
- Hapus Submission: User dapat menghapus submission miliknya sendiri
- Access Control: User hanya dapat melihat dan mengelola submission miliknya sendiri
- Input Validation: Semua input divalidasi dan disanitasi (HTML tags dihapus)


3. MANAJEMEN FILE
-----------------

Upload File
- Tipe file yang diizinkan: JPEG, PNG, GIF, WebP, PDF, TXT, DOC, DOCX
- Ukuran maksimal: 10 MB
- Validasi MIME type untuk keamanan
- Sanitasi nama file (menghapus karakter berbahaya)
- Penyimpanan dengan nama unik (random) untuk mencegah konflik dan path traversal

Download File
- Hanya pemilik file yang dapat mengunduh
- Redirect dengan pesan error jika mencoba mengakses file orang lain

Daftar & Hapus File
- Tampilkan semua file yang diupload oleh user
- User dapat menghapus file miliknya sendiri


4. ADMIN PANEL
--------------

Fitur admin hanya dapat diakses oleh user dengan role 'admin':

Daftar User
- Melihat semua user yang terdaftar di sistem
- Informasi: ID, Nama, Email, Role, Status, Tanggal Registrasi
- Pagination untuk kemudahan navigasi

Nonaktifkan User
- Admin dapat menonaktifkan akun user
- User yang dinonaktifkan tidak dapat login
- Admin tidak dapat menonaktifkan akun sendiri atau admin lain

Hapus User
- Admin dapat menghapus user dari sistem
- Semua data terkait (file, submission) akan ikut terhapus (cascade)
- Admin tidak dapat menghapus akun sendiri atau admin lain

Proteksi Admin
- Admin tidak dapat menghapus/menonaktifkan akun sendiri
- Admin tidak dapat menghapus/menonaktifkan admin lain
- Semua aksi admin dicatat dalam audit log


================================================================================
                  ASPEK KEAMANAN YANG SUDAH DITERAPKAN
================================================================================

1. PASSWORD HASHING (bcrypt/argon2)
------------------------------------
- Menggunakan Hash::make() dengan bcrypt (default Laravel)
- Password tidak pernah disimpan dalam plaintext
- Password requirements: minimal 8 karakter, harus ada huruf besar, huruf 
  kecil, angka, dan simbol
- Lokasi: app/Http/Controllers/AuthController.php, app/Models/User.php


2. CSRF PROTECTION
------------------
- Semua form menggunakan @csrf directive
- CSRF token di meta tag untuk AJAX requests
- Laravel middleware otomatis memvalidasi CSRF token
- Same-Site cookie attribute set ke 'lax' untuk CSRF protection
- Lokasi: Semua form di resources/views, config/session.php


3. INPUT VALIDATION
-------------------
- Server-side validation menggunakan Laravel Validator
- Validasi untuk semua input (email, password, file, content)
- Input sanitization menggunakan strip_tags() untuk text inputs
- File type validation (MIME type checking)
- File size validation (max 10MB)
- Lokasi: app/Http/Controllers/AuthController.php, 
  app/Http/Controllers/SubmissionController.php,
  app/Http/Controllers/FileController.php


4. FILE UPLOAD SANITIZATION
----------------------------
- MIME type validation (whitelist approach)
- Filename sanitization (remove dangerous characters)
- Unique stored filename (prevent conflicts & path traversal)
- File size limit (10MB)
- Storage di private directory (tidak publicly accessible)
- Extension validation
- DoS Protection: Validasi ukuran file eksplisit dalam bytes sebelum 
  pemrosesan
- Lokasi: app/Http/Controllers/FileController.php


5. ACCESS CONTROL UNTUK DOWNLOAD
---------------------------------
- Authorization check di controller method
- Query filtering (hanya menampilkan file milik user)
- Redirect dengan pesan error yang user-friendly (bukan 403 page)
- Protection Layers:
  * Query filtering: Hanya file milik user yang ditampilkan di list
  * Controller check: Verifikasi ownership sebelum download
  * User-friendly error: Redirect ke halaman sendiri dengan pesan error
- Lokasi: app/Http/Controllers/FileController.php


6. TIDAK ADA HARDCODED SECRETS
-------------------------------
- Semua secrets menggunakan environment variables (.env)
- APP_KEY di-generate via php artisan key:generate
- Database credentials di .env
- Tidak ada password/secret hardcoded di source code
- .env file di .gitignore (tidak di-commit)
- Lokasi: config/app.php, config/database.php, config/session.php


7. TIDAK MENYIMPAN PASSWORD PLAINTEXT
-------------------------------------
- Password selalu di-hash dengan bcrypt sebelum disimpan
- Model User menggunakan cast 'password' => 'hashed'
- Password tidak pernah ditampilkan atau di-return dalam response
- Password di hidden attributes
- Lokasi: app/Models/User.php, app/Http/Controllers/AuthController.php


8. SESSION HARUS SECURE
-----------------------
- Session driver: database (lebih secure dari file)
- HTTP-only cookies: true (prevent XSS)
- Same-Site cookies: 'lax' (CSRF protection)
- Session encryption: Configurable via env
- Session regeneration: On login
- Secure cookie: Configurable via env (untuk HTTPS)
- Lokasi: config/session.php, app/Http/Controllers/AuthController.php


9. SECURITY HEADERS
-------------------
- Content Security Policy (CSP) header untuk mencegah XSS
- X-Frame-Options: DENY (mencegah clickjacking)
- X-Content-Type-Options: nosniff (mencegah MIME type sniffing)
- X-Powered-By header dihilangkan (mencegah information disclosure)
- Referrer-Policy dan Permissions-Policy
- Lokasi: app/Http/Middleware/SecurityHeaders.php


10. RATE LIMITING & IP BLOCKING
-------------------------------
- Login rate limiting: Max 3 attempts per IP per minute
- IP-based blocking: Automatic lockout setelah 3 failed attempts
- Database tracking: Semua login attempts dicatat dengan IP address
- Lockout duration: 15 menit
- Remaining attempts warning: User diperingatkan tentang sisa percobaan
- Lokasi: app/Http/Controllers/AuthController.php, 
  app/Models/LoginAttempt.php


11. AUDIT LOGGING
-----------------
- Comprehensive audit trail: Semua event keamanan dicatat ke database
- Authentication logging: Login, logout, registration, failed attempts
- Authorization violations: Unauthorized access attempts dicatat
- File operations: Upload, download, delete operations tracked
- Data modifications: Submission create/delete tracked
- IP & User Agent tracking: Context lengkap untuk setiap aksi
- Lokasi: app/Services/AuditLogService.php, app/Models/AuditLog.php


12. PRODUCTION SECURITY
-----------------------
- ProductionSecurity middleware: Memastikan APP_DEBUG=false di production
- Redirect Safety: Validasi redirect URL untuk mencegah open redirect 
  vulnerabilities
- Lokasi: app/Http/Middleware/ProductionSecurity.php,
  app/Helpers/RedirectHelper.php


13. ROLE-BASED ACCESS CONTROL (RBAC)
-------------------------------------
- AdminMiddleware: Proteksi route admin hanya untuk user dengan role 'admin'
- Redirect dengan pesan error yang user-friendly untuk unauthorized access
- Lokasi: app/Http/Middleware/AdminMiddleware.php


================================================================================
                              TECHNOLOGY STACK
================================================================================

- Framework: Laravel 12.x
- PHP: PHP 8.2+
- Database: MySQL/PostgreSQL/SQLite (configurable)
- Frontend: Tailwind CSS 4.0
- Build Tool: Vite

================================================================================
                                  AUTHORS
================================================================================

Group of 3 students - AmanSpace Project
- Helmy Satria Martha Putra    23524018
- Ibnu Prastowo Haryono Putro  23524030
- Meldrin Tupamahu             23524031

================================================================================
