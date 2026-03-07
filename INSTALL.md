# Installation Guide / 安装指南

[English](#english) | [中文](#中文)

---

<a name="english"></a>
## English

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
git clone https://github.com/yourusername/onepage.git
cd onepage

# Or download and extract ZIP file
wget https://github.com/yourusername/onepage/archive/refs/heads/main.zip
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

#### Basic Configuration

```nginx
server {
    listen 80;
    listen 443 ssl http2;
    server_name your-domain.com;
    root /path/to/onepage/public;
    index index.php index.html;

    # SSL Configuration (if using HTTPS)
    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;
    ssl_protocols TLSv1.2 TLSv1.3;

    # Main rewrite rule
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP handling
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Static file caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }

    # Logs
    access_log /var/log/nginx/onepage.access.log;
    error_log /var/log/nginx/onepage.error.log;
}
```

#### For Baota Panel (宝塔面板)

1. Login to Baota Panel
2. Go to **Websites** → **your-domain.com** → **Settings**
3. Click **Configuration File**
4. Find the `location / { ... }` block
5. Modify to:
   ```nginx
   location / {
       try_files $uri $uri/ /index.php?$query_string;
   }
   ```
6. Save and reload

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

---

<a name="中文"></a>
## 中文

## 目录

- [系统要求](#系统要求)
- [安装方法](#安装方法)
  - [方法一：自动安装（推荐）](#方法一自动安装推荐)
  - [方法二：手动安装](#方法二手动安装)
- [Web 服务器配置](#web-服务器配置)
  - [Nginx 配置](#nginx-配置)
  - [Apache 配置](#apache-配置)
- [安装后设置](#安装后设置)
- [故障排查](#故障排查)

## 系统要求

### 服务器要求

- **PHP**: >= 8.2
  - 扩展：pdo, pdo_mysql, mysqli, json, mbstring, openssl
- **数据库**: MySQL >= 5.7 或 MariaDB >= 10.3
- **Web 服务器**: Nginx（推荐）或 Apache
- **操作系统**: Linux（Ubuntu, CentOS, Debian 等）

### PHP 扩展

确保已安装以下 PHP 扩展：

```bash
# Ubuntu/Debian
sudo apt-get install php8.2 php8.2-fpm php8.2-mysql php8.2-json php8.2-mbstring php8.2-xml php8.2-curl

# CentOS/RHEL
sudo yum install php82 php82-fpm php82-mysqlnd php82-json php82-mbstring php82-xml php82-curl
```

### 权限要求

以下目录必须可写：

```bash
chmod 777 .
chmod -R 777 uploads/pages
chmod -R 777 logs
```

## 安装方法

### 方法一：自动安装（推荐）

#### 步骤 1：下载和解压

```bash
# 从 GitHub 克隆
git clone https://github.com/yourusername/onepage.git
cd onepage

# 或下载并解压 ZIP 文件
wget https://github.com/yourusername/onepage/archive/refs/heads/main.zip
unzip main.zip
cd onepage-main
```

#### 步骤 2：设置权限

```bash
chmod 777 .
chmod -R 777 uploads/pages
chmod -R 777 logs
```

#### 步骤 3：配置 Web 服务器

将域名的文档根目录指向 `public` 目录：

```nginx
# Nginx 示例
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

#### 步骤 4：运行安装向导

1. 在浏览器中访问你的网站：`https://your-domain.com`
2. 系统会自动跳转到安装向导
3. 按照 5 步安装流程操作：
   - **步骤 1**：环境检测
   - **步骤 2**：数据库配置
   - **步骤 3**：管理员账户设置
   - **步骤 4**：开始安装
   - **步骤 5**：安装完成

#### 步骤 5：登录

使用安装时设置的管理员凭据登录。

### 方法二：手动安装

如果自动安装程序无法工作，可以手动安装：

#### 步骤 1：创建数据库

```sql
CREATE DATABASE onepage DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'onepage'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON onepage.* TO 'onepage'@'localhost';
FLUSH PRIVILEGES;
```

#### 步骤 2：导入数据库结构

```bash
mysql -u onepage -p onepage < schema.sql
```

#### 步骤 3：创建配置文件

在项目根目录创建 `.env` 文件：

```env
# 应用配置
APP_NAME=OnePage
APP_URL=https://your-domain.com

# 数据库配置
DB_HOST=localhost
DB_NAME=onepage
DB_USER=onepage
DB_PASS=your_password

# AI 审核（可选）
AI_ENABLED=false
AI_API_KEY=your_api_key
AI_API_URL=https://integrate.api.nvidia.com/v1/chat/completions
AI_MODEL=qwen/qwen3.5-122b-a10b
AI_THRESHOLD=6.0

# 邮件配置（可选）
MAIL_METHOD=phpmail
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your_email@example.com
MAIL_PASSWORD=your_email_password
MAIL_FROM_EMAIL=noreply@example.com
MAIL_FROM_NAME=OnePage System
MAIL_ENCRYPTION=tls
```

#### 步骤 4：创建管理员账户

```sql
USE onepage;

-- 先生成密码哈希（在 PHP 中执行）：
-- echo password_hash('your_password', PASSWORD_DEFAULT);

INSERT INTO users (username, email, password, role, accepted_terms, created_at)
VALUES ('admin', 'admin@example.com', '$2y$10$...', 'admin', 1, NOW());
```

#### 步骤 5：创建安装标记

```bash
echo "手动安装完成于 $(date)" > .installed
```

#### 步骤 6：设置权限

```bash
chmod 600 .env
chmod 777 .
chmod -R 777 uploads/pages
chmod -R 777 logs
```

## Web 服务器配置

### Nginx 配置

#### 基础配置

```nginx
server {
    listen 80;
    listen 443 ssl http2;
    server_name your-domain.com;
    root /path/to/onepage/public;
    index index.php index.html;

    # SSL 配置（如果使用 HTTPS）
    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;
    ssl_protocols TLSv1.2 TLSv1.3;

    # 主重写规则
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP 处理
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # 静态文件缓存
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # 禁止访问隐藏文件
    location ~ /\. {
        deny all;
    }

    # 日志
    access_log /var/log/nginx/onepage.access.log;
    error_log /var/log/nginx/onepage.error.log;
}
```

#### 宝塔面板配置

1. 登录宝塔面板
2. 网站 → your-domain.com → 设置
3. 点击"配置文件"
4. 找到 `location / { ... }` 块
5. 修改为：
   ```nginx
   location / {
       try_files $uri $uri/ /index.php?$query_string;
   }
   ```
6. 保存并重载

### Apache 配置

在 `public` 目录创建 `.htaccess` 文件：

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# 防止目录列表
Options -Indexes

# 保护敏感文件
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>
```

## 安装后设置

### 安全建议

1. **首次登录后立即修改管理员密码**
2. **启用 HTTPS** 并配置 SSL 证书
3. **设置正确的文件权限**：
   ```bash
   chmod 600 .env
   chmod 600 .installed
   chmod 644 schema.sql
   ```
4. **配置防火墙**，只开放 80 和 443 端口
5. **设置定期备份**，包括数据库和上传文件

### 可选配置

#### 启用 AI 内容审核

1. 从 [NVIDIA NIM](https://build.nvidia.com/) 获取 API 密钥
2. 编辑 `.env` 文件：
   ```env
   AI_ENABLED=true
   AI_API_KEY=your_api_key
   ```
3. 根据需要调整阈值（默认：6.0）

#### 配置邮件通知

在 `.env` 文件中配置 SMTP 设置：

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

### 备份策略

#### 数据库备份

```bash
# 每日备份脚本
mysqldump -u onepage -p onepage > backup_$(date +%Y%m%d).sql
```

#### 文件备份

```bash
# 备份上传文件和配置
tar -czf backup_$(date +%Y%m%d).tar.gz uploads/ .env
```

## 故障排查

### 常见问题

#### 1. 除首页外其他页面都显示 404

**问题**：只有首页能访问，其他页面显示 404

**解决方案**：配置 Nginx/Apache 的 URL 重写（参见 [Web 服务器配置](#web-服务器配置)）

#### 2. "Class not found" 错误

**问题**：PHP 显示 "Class 'App\Core\Router' not found"

**解决方案**：检查 `public/index.php` 中的自动加载器，确保文件路径正确

#### 3. 数据库连接失败

**问题**：无法连接到数据库

**解决方案**：
- 检查 `.env` 中的数据库凭据
- 验证 MySQL 服务是否运行
- 检查用户权限

#### 4. 权限被拒绝

**问题**：无法写入目录

**解决方案**：
```bash
chmod 777 .
chmod -R 777 uploads/pages
chmod -R 777 logs
```

#### 5. 安装循环

**问题**：安装向导在步骤 4 一直循环

**解决方案**：检查 `logs/install.md` 中的详细错误日志

### 获取帮助

1. 查看[故障排查指南](TROUBLESHOOTING.md)
2. 查看安装日志：`logs/install.md`
3. 检查 PHP 错误日志
4. 在 GitHub 上[提交问题](https://github.com/Chen-hash30/onepage/issues)

---

## Need Help? / 需要帮助？

- 📖 [Documentation / 文档](docs/)
- 🐛 [Issue Tracker / 问题追踪](https://github.com/Chen-hash30/onepage/issues)
- 💬 [Discussions / 讨论](https://github.com/Chen-hash30/onepage/discussions)
