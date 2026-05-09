# BMS Installation & Setup Guide

## Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache with mod_rewrite enabled
- Composer (for PHP dependencies)

## Installation Steps

### Step 1: Create Database
1. Open phpMyAdmin or MySQL command line
2. Create a new database named `bms`:
   ```sql
   CREATE DATABASE IF NOT EXISTS bms;
   ```

### Step 2: Import Database Schema
1. Import the schema file from `database/bms_schema.sql`:
   ```sql
   SOURCE /path/to/BMS/database/bms_schema.sql;
   ```
   Or use phpMyAdmin to import the SQL file

### Step 3: Install PHP Dependencies
Navigate to the BMS directory and run:
```bash
cd /xampp/htdocs/BMS
composer install
```

This will install:
- mPDF 8.1+ (for PDF exports)
- PhpSpreadsheet 1.28+ (for Excel exports)

### Step 4: Seed Initial Data
Run the database seed script to create admin user and sample data:
```bash
php database/seed.php
```

This creates:
- Admin user (email: admin@bms.com, password: admin123)
- Sample business
- Sample products
- Sample investment

**IMPORTANT:** Change the admin password after first login!

### Step 5: Configure Permissions (Windows/XAMPP)
1. Create an `exports` folder in the root BMS directory (for PDF/Excel exports)
2. Set permissions using Command Prompt:
   ```cmd
   icacls "C:\xampp\htdocs\BMS\exports" /grant Users:F
   ```

### Step 6: Verify Apache Configuration
1. Ensure `mod_rewrite` is enabled in Apache
2. The `.htaccess` file in BMS root directory handles URL rewriting
3. Verify `RewriteBase /BMS/` matches your installation path

### Step 7: Test Installation
1. Navigate to: `http://localhost/BMS/`
2. You should be redirected to login page
3. Login with:
   - Email: admin@bms.com
   - Password: admin123

## API Usage

### Authentication
All API endpoints (except /auth/login and /auth/register) require Bearer token authentication:

```bash
# Login to get token
curl -X POST http://localhost/BMS/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@bms.com","password":"admin123"}'

# Response includes token
# {
#   "success": true,
#   "data": {
#     "token": "abc123...",
#     "user": {...}
#   }
# }
```

### Using Token
Include Bearer token in Authorization header:
```bash
curl -X GET http://localhost/BMS/api/v1/businesses \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### API Endpoints

**Authentication:**
- `POST /api/v1/auth/login` - User login
- `POST /api/v1/auth/register` - User registration

**Businesses:**
- `GET /api/v1/businesses` - List all businesses
- `POST /api/v1/businesses` - Create business
- `GET /api/v1/businesses/{id}` - Get business
- `PUT /api/v1/businesses/{id}` - Update business
- `DELETE /api/v1/businesses/{id}` - Delete business

**Inventory:**
- `GET /api/v1/inventory?business_id=1` - List products
- `POST /api/v1/inventory` - Create product
- `GET /api/v1/inventory/{id}` - Get product
- `PUT /api/v1/inventory/{id}` - Update product
- `DELETE /api/v1/inventory/{id}` - Delete product
- `GET /api/v1/inventory/low_stock?business_id=1` - Get low stock items

**Sales:**
- `GET /api/v1/sales?business_id=1` - List sales
- `POST /api/v1/sales` - Create sale
- `GET /api/v1/sales/{id}` - Get sale details

**Investments:**
- `GET /api/v1/investments?business_id=1` - List investments
- `POST /api/v1/investments` - Create investment
- `GET /api/v1/investments/{id}` - Get investment
- `PUT /api/v1/investments/{id}` - Update investment
- `DELETE /api/v1/investments/{id}` - Delete investment

**Profits:**
- `GET /api/v1/profits?business_id=1&from=2024-01-01&to=2024-01-31` - Calculate profit

**Reports:**
- `GET /api/v1/reports/sales?business_id=1&from=2024-01-01&to=2024-01-31` - Sales report
- `GET /api/v1/reports/inventory?business_id=1` - Inventory report
- `GET /api/v1/reports/profit?business_id=1&from=2024-01-01&to=2024-01-31` - Profit report

## Configuration

### Database Connection
Edit `config/database.php` or `config/constants.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Your MySQL password
define('DB_NAME', 'bms');
```

### API Configuration
Edit `config/api_config.php`:
```php
define('ALLOWED_ORIGINS', ['http://localhost', 'http://localhost:3000']);
define('TOKEN_SECRET', 'your-secure-key'); // Change this!
```

## Security Notes

1. **Change Admin Password**: Log in and change the default admin password immediately
2. **Update TOKEN_SECRET**: Change the TOKEN_SECRET in `config/api_config.php` to a secure random string
3. **Database Credentials**: Update DB credentials in production
4. **HTTPS**: Use HTTPS in production for API endpoints
5. **CORS**: Configure ALLOWED_ORIGINS in `config/api_config.php` for your frontend domain

## Troubleshooting

### "Database connection failed"
- Check MySQL server is running
- Verify DB credentials in `config/constants.php`
- Ensure database `bms` exists

### "Redirect loop" at login
- Check `session.php` is loading correctly
- Verify `BASE_URL` in `config/constants.php` is correct

### ".htaccess not working"
- Enable mod_rewrite in Apache: `a2enmod rewrite`
- Verify `.htaccess` file exists in BMS root
- Check `AllowOverride All` is set in Apache config

### API endpoints returning 404
- Verify `.htaccess` rewrite rules
- Check URI format matches routing in `api/index.php`
- Ensure business_id parameter is provided where required

### PDF/Excel exports not working
- Verify `composer install` completed successfully
- Check `exports` folder exists and has write permissions
- Review error logs in `exports` folder

## File Structure Reminder

```
BMS/
├── config/              # Database and API config
├── classes/             # Business logic classes
├── handlers/            # Form handlers
├── auth/                # Authentication pages
├── api/                 # REST API (v1/)
├── modules/             # Admin dashboard modules
├── includes/            # Shared HTML components
├── assets/              # CSS, JS, images
├── database/            # Schema and seed scripts
├── exports/             # PDF/Excel export folder (auto-created)
├── composer.json        # PHP dependencies
├── .htaccess            # URL rewriting rules
└── README.md            # Project documentation
```

## Support & Documentation

For more information, see:
- `README.md` - Project overview
- `database/bms_schema.sql` - Database structure
- `api/index.php` - API routing logic
- Code comments in class files for method documentation
