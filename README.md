# AmanSpace

A secure web application built with Laravel framework that implements essential security features including user authentication, data submission, and secure file management.

## Features

### 1. User Management
- **User Registration**: Secure user registration with email validation
- **User Login**: Authentication with password verification
- **Password Hashing**: Passwords are hashed using bcrypt (Laravel's default)
- **Session Handling**: Secure session management with database storage

### 2. Data Submission
- **Text Submission Form**: Users can create submissions with title and content
- **Input Validation**: All inputs are validated and sanitized
- **Content Management**: View, create, and delete submissions
- **Access Control**: Users can only access their own submissions

### 3. File Management
- **File Upload**: Secure file upload with validation and sanitization
- **File Download**: Download files with proper access control
- **File Sanitization**: 
  - Filename sanitization (removes dangerous characters)
  - MIME type validation
  - File size limits (10 MB maximum)
  - Secure storage in private directory
- **Access Control**: Users can only download their own files

## Security Features

### Password Security
- ✅ **Password Hashing**: Using bcrypt (via Laravel's Hash facade)
- ✅ **No Plaintext Storage**: Passwords are never stored in plaintext
- ✅ **Password Requirements**: Enforced through Laravel's Password validation rules

### CSRF Protection
- ✅ **CSRF Tokens**: All forms include CSRF tokens
- ✅ **Automatic Protection**: Laravel middleware automatically validates CSRF tokens
- ✅ **Same-Site Cookies**: Configured to prevent CSRF attacks

### Input Validation
- ✅ **Server-Side Validation**: All inputs validated on the server
- ✅ **Input Sanitization**: HTML tags stripped from text inputs
- ✅ **Type Checking**: File types validated using MIME type checking
- ✅ **Size Limits**: File size and content length limits enforced

### File Upload Security
- ✅ **MIME Type Validation**: Only allowed file types accepted
- ✅ **Filename Sanitization**: Dangerous characters removed from filenames
- ✅ **Unique Storage Names**: Files stored with random unique names
- ✅ **Private Storage**: Files stored in private directory (not publicly accessible)
- ✅ **Path Traversal Prevention**: Secure file paths prevent directory traversal attacks

### Access Control
- ✅ **Authorization Checks**: Users can only access their own resources
- ✅ **File Download Protection**: Files can only be downloaded by their owner
- ✅ **Submission Access Control**: Users can only view/edit their own submissions

### Session Security
- ✅ **Secure Sessions**: Sessions stored in database
- ✅ **HTTP Only Cookies**: Session cookies marked as HTTP-only
- ✅ **Session Regeneration**: Session ID regenerated on login
- ✅ **Session Timeout**: Configurable session lifetime

### Rate Limiting & IP Blocking
- ✅ **Login Rate Limiting**: Max 5 attempts per IP per minute (throttle middleware)
- ✅ **IP-based Blocking**: Automatic lockout after 5 failed login attempts
- ✅ **Database Tracking**: All login attempts logged with IP address and timestamp
- ✅ **Lockout Duration**: 15 minutes lockout after exceeding max attempts
- ✅ **Remaining Attempts Warning**: Users warned about remaining attempts

### Audit Logging & Security Logging
- ✅ **Comprehensive Audit Trail**: All security-related events logged to database
- ✅ **Authentication Logging**: Login, logout, registration, failed attempts
- ✅ **Authorization Violations**: Unauthorized access attempts logged
- ✅ **File Operations**: Upload, download, delete operations tracked
- ✅ **Data Modifications**: Submission create/delete tracked
- ✅ **Dual Logging**: Database + Laravel log file for critical events
- ✅ **IP & User Agent Tracking**: Complete context for each action
- ✅ **Metadata Storage**: Additional data stored as JSON for detailed analysis

### No Hardcoded Secrets
- ✅ **Environment Variables**: All secrets stored in `.env` file
- ✅ **Application Key**: Generated via `php artisan key:generate`
- ✅ **Database Credentials**: Stored in environment variables

## Technology Stack

- **Framework**: Laravel 12.x
- **PHP**: PHP 8.2+
- **Database**: MySQL/PostgreSQL/SQLite (configurable)
- **Frontend**: Tailwind CSS 4.0
- **Build Tool**: Vite

## Installation & Setup

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

### Step 7: Seed Default Users (Development Only)
```bash
php artisan db:seed --class=UserSeeder
```

> **⚠️ SECURITY WARNING**: Default users are **ONLY** created in development/testing environments. 
> They will **NOT** be created in production for security reasons.

This will create two default users for testing (only in local/development environment):
- **Admin User**: 
  - Email: `admin@example.com`
  - Password: `password123`
- **Test User**: 
  - Email: `test@example.com`
  - Password: `password123`

**For Production**: 
- Default users are automatically skipped
- Create admin accounts manually through registration or database
- Use strong, unique passwords

### Step 8: Start Development Server
```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

## How to Run the Application

### Development Mode
1. Start Laravel development server:
   ```bash
   php artisan serve
   ```

2. In another terminal, start Vite dev server (for hot reload):
   ```bash
   npm run dev
   ```

3. Access the application at `http://localhost:8000`

### Production Mode
1. Build assets:
   ```bash
   npm run build
   ```

2. Optimize Laravel:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

3. Configure your web server (Apache/Nginx) to point to the `public` directory

## Project Structure

```
miniweb/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── AuthController.php      # Authentication (register/login/logout)
│   │       ├── SubmissionController.php # Submission management
│   │       └── FileController.php       # File upload/download
│   └── Models/
│       ├── User.php                     # User model
│       ├── Submission.php               # Submission model
│       └── File.php                     # File model
├── database/
│   └── migrations/
│       ├── create_users_table.php
│       ├── create_submissions_table.php
│       └── create_files_table.php
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php           # Main layout
│       ├── auth/
│       │   ├── login.blade.php          # Login page
│       │   └── register.blade.php       # Registration page
│       ├── submissions/
│       │   ├── index.blade.php          # List submissions
│       │   ├── create.blade.php          # Create submission
│       │   └── show.blade.php            # View submission
│       ├── files/
│       │   ├── index.blade.php          # List files
│       │   └── create.blade.php         # Upload file
│       └── dashboard.blade.php           # Dashboard
├── routes/
│   └── web.php                          # Application routes
└── storage/
    └── app/
        └── private/                     # Private file storage
```

## Default Users (Development Only)

After running the seeder in **development/testing** environment, you can login with this default account:

1. **Test Account**
   - Email: `test@example.com`
   - Password: `password123`

### ⚠️ Security Best Practices

**For Development/Testing:**
- Default users are automatically created only in `local`, `testing`, or `development` environments
- Safe to use for testing purposes

**For Production:**
- ✅ Default users are **NOT** created automatically in production
- ✅ Create admin accounts manually through registration
- ✅ Use strong, unique passwords
- ✅ Remove any default users if they exist: `php artisan users:remove-default`

### Remove Default Users

To remove default users for security (e.g., before deploying to production):

```bash
php artisan users:remove-default
```

Or with force flag (no confirmation):
```bash
php artisan users:remove-default --force
```

## Usage Guide

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

## Security Implementation Details

### Password Hashing
- Laravel uses bcrypt by default via `Hash::make()`
- Passwords are automatically hashed when creating users
- Password verification uses `Hash::check()`

### CSRF Protection
- All POST/PUT/PATCH/DELETE requests require CSRF token
- Tokens are automatically included in Blade forms via `@csrf`
- Laravel middleware validates tokens automatically

### Input Validation
- Validation rules defined in controllers
- Failed validation returns error messages
- Input sanitization using `strip_tags()` for text inputs

### File Upload Security
- MIME type validation against whitelist
- Filename sanitization using regex
- Files stored with random unique names
- Storage in private directory (not web-accessible)
- Access control checks ownership before download

### Session Security
- Sessions stored in database
- HTTP-only cookies prevent JavaScript access
- Session ID regeneration on login
- Configurable session lifetime

## Testing

To run tests:
```bash
php artisan test
```

## Troubleshooting

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

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Authors

Group of 3 students - Mini Secure Web Application Project

## Acknowledgments

- Laravel Framework
- Tailwind CSS
- All security best practices implemented
