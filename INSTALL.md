# Installation Guide

[English](INSTALL.md) | [中文](INSTALL.zh-CN.md)

---

## Table of Contents

- [Requirements](#requirements)
- [Installation Methods](#installation-methods)
  - [Method 1: Automatic Installation (Recommended)](#method-1-automatic-installation-recommended)
  - [Method 2: Manual Installation](#method-2-manual-installation)
- [Web Server Configuration](#web-server-configuration)
  - [Nginx Configuration](#nginx-configuration)
  - [Apache Configuration](#apache-configuration)
- [Post-Installation](#post-installation)
- [Troubleshooting](#troubleshooting)

## Requirements

### Server Requirements

- **PHP**: >= 8.2
  - Extensions: pdo, pdo_mysql, mysqli, json, mbstring, openssl
- **Database**: MySQL >= 5.7 or MariaDB >= 10.3
- **Web Server**: Nginx (recommended) or Apache
- **Operating System**: Linux (Ubuntu, CentOS, Debian, etc.)

### PHP Extensions

Make sure these PHP extensions are installed:

```bash
# Ubuntu/Debian
sudo apt-get install php8.2 php8.2-fpm php8.2-mysql php8.2-json php8.2-mbstring php8.2-xml php8.2-curl

# CentOS/RHEL
sudo yum install php82 php82-fpm php82-mysqlnd php82-json php82-mbstring php82-xml php82-curl
```

### Permissions

The following directories must be writable:

```bash
chmod 777 .
chmod -R 777 uploads/pages
chmod -R 777 logs
```

## Installation Methods

### Method 1: Automatic Installation (Recommended)

#### Step 1: Download and Extract

```bash
# Clone from GitHub
git clone https://github.com/Chen-hash30/onepage.git
cd onepage

# Or download and extract ZIP file
wget https://github.com/Chen-hash30/onepage/archive/refs/heads/main.zip
unzip main.zip
cd onepage-main
```

#### Step 2: Set Permissions

```bash
chmod 777 .
chmod -R 777 uploads/pages
chmod -R 777 logs
```

#### Step 3: Configure Web Server

Point your domain's document root to the `public` directory:

```nginx
# Nginx example
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/onepage/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

#### Step 4: Run Installation Wizard

1. Visit your website in a browser: `https://your-domain.com`
2. You will be automatically redirected to the installation wizard
3. Follow the 5-step installation process:
   - **Step 1**: Environment Check
   - **Step 2**: Database Configuration
   - **Step 3**: Admin Account Setup
   - **Step 4**: Installation
   - **Step 5**: Complete

#### Step 5: Login

Use the admin credentials you set during installation to login.

### Method 2: Manual Installation

If the automatic installer doesn't work, you can install manually:

#### Step 1: Create Database

```sql
CREATE DATABASE onepage DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'onepage'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON onepage.* TO 'onepage'@'localhost';
FLUSH PRIVILEGES;
```

#### Step 2: Import Database Schema

```bash
mysql -u onepage -p onepage < schema.sql
```

#### Step 3: Create Configuration File

Create `.env` file in the project root:

```env
# Application
APP_NAME=OnePage
APP_URL=https://your-domain.com

# Database
DB_HOST=localhost
DB_NAME=onepage
DB_USER=onepage
DB_PASS=your_password

# AI Moderation (Optional)
AI_ENABLED=false
AI_API_KEY=your_api_key
AI_API_URL=https://integrate.api.nvidia.com/v1/chat/completions
AI_MODEL=qwen/qwen3.5-122b-a10b
AI_THRESHOLD=6.0

# Email (Optional)
MAIL_METHOD=phpmail
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your_email@example.com
MAIL_PASSWORD=your_email_password
MAIL_FROM_EMAIL=noreply@example.com
MAIL_FROM_NAME=OnePage System
MAIL_ENCRYPTION=tls
```

#### Step 4: Create Admin Account

```sql
USE onepage;

-- Generate password hash first (in PHP):
-- echo password_hash('your_password', PASSWORD_DEFAULT);

INSERT INTO users (username, email, password, role, accepted_terms, created_at)
VALUES ('admin', 'admin@example.com', '$2y$10$...', 'admin', 1, NOW());
```

#### Step 5: Create Installation Marker

```bash
echo "Manual installation completed at $(date)" > .installed
```

#### Step 6: Set Permissions

```bash
chmod 600 .env
chmod 777 .
chmod -R 777 uploads/pages
chmod -R 777 logs
```

## Web Server Configuration

### Nginx Configuration

#### For Baota Panel (宝塔面板) - Recommended

1. Login to Baota Panel
2. Go to **Websites** → **your-domain.com** → **Settings**
3. Click **Pseudo Static** (伪静态)
4. Add the following rule:
   ```nginx
   location / {
       if (!-e $request_filename) {
           rewrite ^(.*)$ /index.php?s=$1 last;
           break;
       }
   }
   ```
5. Save

#### Manual Nginx Configuration

If you're not using Baota Panel, add the following to your nginx config:

```nginx
location / {
    if (!-e $request_filename) {
        rewrite ^(.*)$ /index.php?s=$1 last;
        break;
    }
}
```

### Apache Configuration

Create `.htaccess` file in the `public` directory:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Prevent directory listing
Options -Indexes

# Protect sensitive files
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>
```

## Post-Installation

### Security Recommendations

1. **Change admin password** immediately after first login
2. **Enable HTTPS** with SSL certificate
3. **Set proper file permissions**:
   ```bash
   chmod 600 .env
   chmod 600 .installed
   chmod 644 schema.sql
   ```
4. **Configure firewall** to only allow ports 80 and 443
5. **Set up regular backups** for database and uploads

### Optional Configurations

#### Enable AI Content Moderation

1. Get API key from [NVIDIA NIM](https://build.nvidia.com/)
2. Edit `.env` file:
   ```env
   AI_ENABLED=true
   AI_API_KEY=your_api_key
   ```
3. Adjust threshold as needed (default: 6.0)

#### Configure Email Notifications

Edit `.env` file with your SMTP settings:

```env
MAIL_METHOD=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_FROM_EMAIL=noreply@your-domain.com
MAIL_FROM_NAME=OnePage System
MAIL_ENCRYPTION=tls
```

### Backup Strategy

#### Database Backup

```bash
# Daily backup script
mysqldump -u onepage -p onepage > backup_$(date +%Y%m%d).sql
```

#### File Backup

```bash
# Backup uploads and config
tar -czf backup_$(date +%Y%m%d).tar.gz uploads/ .env
```

## Troubleshooting

### Common Issues

#### 1. 404 Error on All Pages Except Homepage

**Problem**: Only homepage works, other pages show 404

**Solution**: Configure URL rewriting in Nginx/Apache (see [Web Server Configuration](#web-server-configuration))

#### 2. "Class not found" Error

**Problem**: PHP shows "Class 'App\Core\Router' not found"

**Solution**: Check autoloader in `public/index.php` and ensure correct file paths

#### 3. Database Connection Failed

**Problem**: Cannot connect to database

**Solution**: 
- Check database credentials in `.env`
- Verify MySQL service is running
- Check user permissions

#### 4. Permission Denied

**Problem**: Cannot write to directories

**Solution**:
```bash
chmod 777 .
chmod -R 777 uploads/pages
chmod -R 777 logs
```

#### 5. Installation Loop

**Problem**: Installation wizard keeps looping on step 4

**Solution**: Check `logs/install.md` for detailed error logs

### Getting Help

1. Check [Troubleshooting Guide](TROUBLESHOOTING.md)
2. View installation logs: `logs/install.md`
3. Check PHP error logs
4. [Open an issue](https://github.com/Chen-hash30/onepage/issues) on GitHub
5. [Start a discussion](https://github.com/Chen-hash30/onepage/discussions)

---

## Links

- [Homepage](https://github.com/Chen-hash30/onepage)
- [Issue Tracker](https://github.com/Chen-hash30/onepage/issues)
- [Discussions](https://github.com/Chen-hash30/onepage/discussions)
