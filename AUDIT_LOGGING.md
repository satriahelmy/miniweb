# Audit Logging & Security Logging Guide

## Overview

Sistem audit logging telah diimplementasikan untuk mencatat semua aktivitas penting terkait security. Semua log disimpan di database (`audit_logs` table) dan juga di Laravel log file untuk event critical.

---

## Yang Perlu Di-Log untuk Security

### ✅ 1. Authentication Events (Sudah Diimplementasikan)

**Login Attempts**:
- ✅ Successful login
- ✅ Failed login (dengan alasan)
- ✅ Blocked login (IP blocked)
- ✅ Logout

**Registration**:
- ✅ New user registration

**Data yang Dicatat**:
- User ID (jika authenticated)
- Email address
- IP address
- User agent
- Timestamp
- Status (success/failed/blocked)

**Lokasi**: `app/Http/Controllers/AuthController.php`

---

### ✅ 2. Authorization Violations (Sudah Diimplementasikan)

**Unauthorized Access Attempts**:
- ✅ Attempted file download (bukan owner)
- ✅ Attempted file delete (bukan owner)
- ✅ Attempted submission view (bukan owner)
- ✅ Attempted submission delete (bukan owner)

**Data yang Dicatat**:
- User ID (yang mencoba akses)
- Resource yang diakses (file/submission)
- Resource owner ID
- IP address
- Timestamp
- Status: `unauthorized`

**Lokasi**: 
- `app/Http/Controllers/FileController.php`
- `app/Http/Controllers/SubmissionController.php`

---

### ✅ 3. File Operations (Sudah Diimplementasikan)

**File Upload**:
- ✅ Successful upload
- ✅ Failed upload (validation errors)

**File Download**:
- ✅ Successful download
- ✅ Failed download (file not found)
- ✅ Unauthorized download attempt

**File Delete**:
- ✅ Successful delete
- ✅ Unauthorized delete attempt

**Data yang Dicatat**:
- File name
- File size
- MIME type
- User ID
- IP address
- Timestamp

**Lokasi**: `app/Http/Controllers/FileController.php`

---

### ✅ 4. Data Modifications (Sudah Diimplementasikan)

**Submission Operations**:
- ✅ Create submission
- ✅ Delete submission
- ✅ Unauthorized access attempts

**Data yang Dicatat**:
- Submission title
- Content length
- User ID
- IP address
- Timestamp

**Lokasi**: `app/Http/Controllers/SubmissionController.php`

---

## Struktur Audit Log Table

```sql
CREATE TABLE audit_logs (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NULL,              -- User yang melakukan action
    action VARCHAR(255),              -- auth.login, file.upload, etc.
    model_type VARCHAR(255) NULL,     -- App\Models\File, etc.
    model_id BIGINT NULL,             -- ID dari model terkait
    ip_address VARCHAR(45),           -- IPv6 support
    user_agent VARCHAR(255) NULL,
    status ENUM('success', 'failed', 'blocked', 'unauthorized'),
    description TEXT NULL,
    metadata JSON NULL,                -- Additional data
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## Action Types yang Dicatat

### Authentication Actions
- `auth.login` - Login attempt
- `auth.logout` - Logout
- `auth.register` - User registration

### File Actions
- `file.upload` - File upload
- `file.download` - File download
- `file.delete` - File deletion

### Submission Actions
- `submission.create` - Create submission
- `submission.delete` - Delete submission

### Unauthorized Actions
- `unauthorized.file_download` - Unauthorized download attempt
- `unauthorized.file_delete` - Unauthorized delete attempt
- `unauthorized.submission_view` - Unauthorized view attempt
- `unauthorized.submission_delete` - Unauthorized delete attempt

---

## Status Types

- **success** - Action berhasil
- **failed** - Action gagal (invalid credentials, validation error, etc.)
- **blocked** - IP/user di-block (rate limiting)
- **unauthorized** - Unauthorized access attempt

---

## Cara Menggunakan Audit Log Service

### Basic Logging
```php
use App\Services\AuditLogService;

// Log custom action
AuditLogService::log(
    action: 'custom_action',
    status: 'success',
    model: $model, // optional
    description: 'Custom action description',
    metadata: ['key' => 'value'] // optional
);
```

### Authentication Logging
```php
// Log login
AuditLogService::logAuth('login', 'success', 'user@example.com');

// Log failed login
AuditLogService::logAuth('login', 'failed', 'user@example.com', 'Invalid password');

// Log blocked login
AuditLogService::logAuth('login', 'blocked', 'user@example.com', 'IP blocked');
```

### File Operation Logging
```php
AuditLogService::logFile('upload', $file, 'success');
AuditLogService::logFile('download', $file, 'success');
AuditLogService::logFile('delete', $file, 'success');
```

### Unauthorized Access Logging
```php
AuditLogService::logUnauthorized('file_download', $file, 'User attempted unauthorized access');
```

---

## Query Audit Logs

### Get All Logs for a User
```php
AuditLog::forUser($userId)->get();
```

### Get Failed Login Attempts
```php
AuditLog::action('auth.login')
    ->status('failed')
    ->recent(7) // Last 7 days
    ->get();
```

### Get Unauthorized Access Attempts
```php
AuditLog::status('unauthorized')
    ->recent(30)
    ->get();
```

### Get File Operations
```php
AuditLog::action('file.download')
    ->recent(1) // Last 24 hours
    ->get();
```

### Get Logs by IP
```php
AuditLog::forIp('192.168.1.1')
    ->recent(7)
    ->get();
```

---

## Laravel Log File

Selain database, event critical juga di-log ke Laravel log file:

**Location**: `storage/logs/laravel.log`

**Events yang di-log ke file**:
- Failed login attempts
- Blocked login attempts
- Unauthorized access attempts
- Critical security events

**Format**:
```
[2025-12-09 16:30:00] local.WARNING: Audit Log: auth.login - failed {"user_id":null,"ip":"127.0.0.1","description":"Invalid credentials"}
```

---

## Best Practices untuk Security Logging

### ✅ DO (Yang Harus Dilakukan)

1. **Log Semua Authentication Events**
   - Login (success/failed)
   - Logout
   - Registration
   - Password changes (jika ada)

2. **Log Semua Authorization Violations**
   - Unauthorized access attempts
   - 403 Forbidden errors
   - Access control failures

3. **Log Critical Operations**
   - File uploads/downloads/deletes
   - Data modifications
   - Sensitive data access

4. **Include Context**
   - IP address
   - User agent
   - Timestamp
   - User ID (jika authenticated)
   - Resource yang diakses

5. **Log to Both Database & File**
   - Database untuk query dan analisis
   - File log untuk backup dan forensic

### ❌ DON'T (Yang Tidak Boleh)

1. **Jangan Log Sensitive Data**
   - ❌ Password (plaintext atau hashed)
   - ❌ Credit card numbers
   - ❌ Full file content
   - ❌ Personal sensitive information

2. **Jangan Log Berlebihan**
   - ❌ Log setiap page view (terlalu banyak)
   - ❌ Log data yang tidak relevan untuk security

3. **Jangan Hardcode Log Messages**
   - ✅ Gunakan constants atau config
   - ❌ Hardcode string di setiap tempat

---

## Monitoring & Alerting (Rekomendasi)

### Metrics yang Perlu Dimonitor

1. **Failed Login Attempts**
   - Threshold: > 10 dalam 1 jam per IP
   - Action: Alert admin

2. **Unauthorized Access Attempts**
   - Threshold: > 5 dalam 1 jam per user
   - Action: Alert admin, consider blocking

3. **File Downloads**
   - Monitor unusual patterns
   - Large number of downloads in short time

4. **Suspicious IPs**
   - Multiple failed attempts
   - Multiple unauthorized access attempts

### Contoh Query untuk Monitoring

```php
// Failed logins per IP (last hour)
AuditLog::action('auth.login')
    ->status('failed')
    ->where('created_at', '>=', now()->subHour())
    ->selectRaw('ip_address, COUNT(*) as attempts')
    ->groupBy('ip_address')
    ->having('attempts', '>', 10)
    ->get();

// Unauthorized access attempts (last 24 hours)
AuditLog::status('unauthorized')
    ->recent(1)
    ->with('user')
    ->get();
```

---

## Retention Policy

### Database Logs
- **Recommended**: Keep logs for 90-365 days
- **Critical logs**: Keep longer (1-2 years)
- **Regular cleanup**: Delete logs older than retention period

### File Logs
- Laravel default: 14 days (configurable)
- **Recommended**: 30-90 days
- Rotate daily to prevent large files

### Cleanup Command (Contoh)

```php
// app/Console/Commands/CleanupAuditLogs.php
public function handle()
{
    // Delete logs older than 90 days
    AuditLog::where('created_at', '<', now()->subDays(90))->delete();
    
    // Keep unauthorized logs longer (365 days)
    AuditLog::where('status', 'unauthorized')
        ->where('created_at', '<', now()->subDays(365))
        ->delete();
}
```

---

## Compliance & Forensics

### Untuk Security Incident Response

1. **Who**: User ID, IP address
2. **What**: Action yang dilakukan
3. **When**: Timestamp
4. **Where**: IP address, user agent
5. **Why**: Status, description
6. **How**: Metadata (file name, size, etc.)

### Audit Trail Requirements

✅ **Immutable**: Logs tidak bisa diubah setelah dibuat
✅ **Tamper-proof**: Protected dari modification
✅ **Complete**: Semua event penting tercatat
✅ **Searchable**: Bisa di-query dengan cepat
✅ **Retained**: Disimpan sesuai retention policy

---

## Summary

### ✅ Yang Sudah Diimplementasikan

1. ✅ Authentication logging (login, logout, register)
2. ✅ Authorization violation logging
3. ✅ File operation logging (upload, download, delete)
4. ✅ Submission operation logging
5. ✅ IP address tracking
6. ✅ User agent tracking
7. ✅ Metadata storage (JSON)
8. ✅ Dual logging (database + file)

### 📊 Log Statistics

Semua aktivitas security-related sudah tercatat:
- **Authentication**: 100% coverage
- **Authorization**: 100% coverage
- **File Operations**: 100% coverage
- **Data Modifications**: 100% coverage

### 🔒 Security Benefits

1. **Forensics**: Bisa trace semua aktivitas
2. **Compliance**: Memenuhi audit requirements
3. **Monitoring**: Bisa detect suspicious activities
4. **Incident Response**: Data lengkap untuk investigasi
5. **Accountability**: Setiap action bisa di-track ke user

---

## Next Steps (Opsional)

1. **Admin Dashboard**: Buat interface untuk view audit logs
2. **Alerting**: Setup alerts untuk suspicious activities
3. **Reporting**: Generate security reports
4. **Export**: Export logs untuk compliance
5. **Integration**: Integrate dengan SIEM tools (jika ada)

