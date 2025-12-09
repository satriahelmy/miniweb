# Rate Limiting & IP Blocking Implementation

## Implementasi yang Sudah Dibuat

### 1. ✅ Rate Limiting di Aplikasi (Application Level)

**Lokasi**: `routes/web.php` dan `app/Http/Controllers/AuthController.php`

**Fitur**:
- ✅ **Throttle Middleware**: Max 5 requests per IP per minute (Laravel built-in)
- ✅ **IP-based Blocking**: Tracking login attempts per IP address
- ✅ **Database Tracking**: Menyimpan semua login attempts di database
- ✅ **Automatic Lockout**: Block IP selama 15 menit setelah 5 failed attempts
- ✅ **Remaining Attempts Warning**: Memberi peringatan sisa attempts

**Cara Kerja**:
1. Setiap login attempt direkam di database (`login_attempts` table)
2. Jika IP melakukan 5 failed attempts dalam 15 menit → **BLOCKED**
3. IP akan di-block selama 15 menit
4. Setelah 15 menit, IP bisa mencoba lagi
5. Jika login berhasil, failed attempts di-clear untuk IP tersebut

**Konfigurasi**:
```php
$maxAttempts = 5;        // Max failed attempts
$lockoutMinutes = 15;    // Lockout duration
```

---

## Perbedaan: Aplikasi Level vs Firewall Level

### 🔵 Application Level (Di Aplikasi Laravel)

**Keuntungan**:
- ✅ Mudah diimplementasikan dan dikonfigurasi
- ✅ Tracking detail (IP, email, waktu, success/failed)
- ✅ Bisa melihat history attempts
- ✅ Bisa unblock manual dari database
- ✅ Fleksibel (bisa block berdasarkan email juga)
- ✅ Tidak perlu akses server/firewall

**Keterbatasan**:
- ⚠️ Masih bisa di-bypass dengan VPN/Proxy
- ⚠️ Membutuhkan database query (sedikit overhead)
- ⚠️ IP bisa berubah (dynamic IP)

**Kapan Digunakan**:
- Untuk aplikasi web biasa
- Ketika perlu tracking detail
- Ketika tidak punya akses firewall
- Untuk blocking sementara (temporary blocking)

---

### 🔴 Firewall Level (Di Server/Firewall)

**Keuntungan**:
- ✅ Sangat efektif (block di level network)
- ✅ Tidak bisa di-bypass dengan mudah
- ✅ Tidak ada overhead database
- ✅ Block semua traffic dari IP (bukan hanya aplikasi)

**Keterbatasan**:
- ⚠️ Perlu akses server/firewall
- ⚠️ Tidak ada tracking detail
- ⚠️ Sulit untuk unblock manual
- ⚠️ Bisa block IP yang legitimate (shared IP)

**Kapan Digunakan**:
- Untuk blocking permanen
- Ketika ada serangan DDoS
- Ketika perlu block di level infrastruktur
- Untuk high-security applications

**Contoh Tools**:
- **fail2ban** (Linux)
- **Cloudflare Firewall Rules**
- **AWS WAF**
- **Nginx Rate Limiting**
- **Apache mod_evasive**

---

## Implementasi Saat Ini

### Yang Sudah Diimplementasikan:

1. **Laravel Throttle Middleware** (Application Level)
   ```php
   Route::post('/login', ...)->middleware('throttle:5,1');
   ```
   - Max 5 requests per IP per minute
   - Otomatis return 429 Too Many Requests

2. **Custom IP Blocking** (Application Level)
   - Tracking di database
   - Block setelah 5 failed attempts
   - Lockout 15 menit
   - Warning sisa attempts

3. **Database Tracking**
   - Table: `login_attempts`
   - Fields: IP address, email, success/failed, timestamp
   - Bisa digunakan untuk analisis

---

## Cara Menggunakan

### 1. Run Migration
```bash
php artisan migrate
```

### 2. Test Rate Limiting
1. Coba login dengan password salah 5 kali
2. IP akan di-block selama 15 menit
3. Pesan error akan muncul dengan sisa waktu

### 3. View Login Attempts (Optional)
Bisa membuat admin panel untuk melihat:
```php
LoginAttempt::where('success', false)
    ->where('attempted_at', '>=', now()->subDay())
    ->get();
```

---

## Rekomendasi untuk Production

### Untuk Aplikasi Web Biasa:
✅ **Gunakan Application Level** (sudah diimplementasikan)
- Cukup untuk kebanyakan kasus
- Mudah dikelola
- Tracking detail

### Untuk High-Security Applications:
✅ **Kombinasi Application + Firewall Level**
1. Application level untuk tracking & temporary blocking
2. Firewall level (fail2ban) untuk permanent blocking
3. Cloudflare untuk DDoS protection

### Contoh Setup Firewall (fail2ban):
```ini
# /etc/fail2ban/jail.local
[laravel]
enabled = true
port = http,https
filter = laravel
logpath = /var/log/laravel.log
maxretry = 5
bantime = 3600
```

---

## Kesimpulan

**Implementasi Saat Ini**: ✅ **Application Level**
- Rate limiting dengan throttle middleware
- IP-based blocking dengan database tracking
- Automatic lockout setelah failed attempts
- Warning untuk remaining attempts

**Untuk Production**: 
- ✅ Sudah cukup untuk aplikasi web biasa
- ✅ Bisa ditambahkan firewall level jika diperlukan
- ✅ Database tracking bisa digunakan untuk monitoring

**Jawaban Singkat**:
- ✅ **Sudah di-handling di aplikasi** (Laravel)
- ✅ **Tracking berdasarkan IP** (disimpan di database)
- ✅ **Bisa ditambahkan firewall level** jika diperlukan (opsional)

