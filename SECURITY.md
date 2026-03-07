# Security Policy / 安全政策

[English](#english) | [中文](#中文)

---

<a name="english"></a>
## English

## Reporting a Vulnerability

If you discover a security vulnerability within OnePage, please [open a security advisory on GitHub](https://github.com/Chen-hash30/onepage/security/advisories/new).

**Please do not create a public GitHub issue for security vulnerabilities.**

## Security Best Practices

When deploying OnePage, please follow these security recommendations:

### 1. File Permissions

```bash
# Set proper permissions
chmod 600 .env
chmod 600 .installed
chmod 644 schema.sql
chmod 777 uploads/pages
chmod 777 logs
```

### 2. Environment Configuration

- Never commit `.env` file to version control
- Use strong database passwords
- Keep `APP_URL` accurate for proper cookie handling

### 3. Database Security

- Use a dedicated database user (not root)
- Grant minimal required permissions
- Regular backups

### 4. Web Server Security

- Enable HTTPS with SSL certificate
- Configure proper Nginx/Apache settings
- Deny access to hidden files
- Set up rate limiting

### 5. PHP Security

- Keep PHP updated to latest stable version
- Disable dangerous functions in `php.ini`:
  ```ini
  disable_functions = exec,passthru,shell_exec,system,proc_open,popen
  ```
- Set appropriate `upload_max_filesize` and `post_max_size`

### 6. Content Moderation

- Enable AI content moderation for uploaded pages
- Regularly review flagged content
- Set appropriate moderation thresholds

## Security Features

OnePage includes the following security features:

- ✅ Password hashing (bcrypt)
- ✅ SQL injection prevention (PDO prepared statements)
- ✅ XSS protection (output escaping)
- ✅ CSRF token implementation
- ✅ File upload validation
- ✅ Session security
- ✅ Error handling without exposing sensitive information

---

<a name="中文"></a>
## 中文

## 报告漏洞

如果您在 OnePage 中发现安全漏洞，请[在 GitHub 上创建安全公告](https://github.com/Chen-hash30/onepage/security/advisories/new)。

**请不要为安全漏洞创建公开的 GitHub issue。**

## 安全最佳实践

部署 OnePage 时，请遵循以下安全建议：

### 1. 文件权限

```bash
# 设置正确的权限
chmod 600 .env
chmod 600 .installed
chmod 644 schema.sql
chmod 777 uploads/pages
chmod 777 logs
```

### 2. 环境配置

- 永远不要将 `.env` 文件提交到版本控制
- 使用强数据库密码
- 保持 `APP_URL` 准确以确保正确的 cookie 处理

### 3. 数据库安全

- 使用专用数据库用户（不要使用 root）
- 授予最小必要权限
- 定期备份

### 4. Web 服务器安全

- 启用 HTTPS 并配置 SSL 证书
- 配置正确的 Nginx/Apache 设置
- 拒绝访问隐藏文件
- 设置速率限制

### 5. PHP 安全

- 保持 PHP 更新到最新稳定版本
- 在 `php.ini` 中禁用危险函数：
  ```ini
  disable_functions = exec,passthru,shell_exec,system,proc_open,popen
  ```
- 设置适当的 `upload_max_filesize` 和 `post_max_size`

### 6. 内容审核

- 为上传的页面启用 AI 内容审核
- 定期审查标记的内容
- 设置适当的审核阈值

## 安全特性

OnePage 包含以下安全特性：

- ✅ 密码哈希（bcrypt）
- ✅ SQL 注入防护（PDO 预处理语句）
- ✅ XSS 防护（输出转义）
- ✅ CSRF 令牌实现
- ✅ 文件上传验证
- ✅ 会话安全
- ✅ 错误处理不暴露敏感信息

---

## Supported Versions / 支持的版本

| Version | Supported          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |
| < 1.0   | :x:                |

---

## Security Updates / 安全更新

Security updates will be released as needed. Please subscribe to GitHub releases to be notified of security updates.

安全更新将根据需要发布。请订阅 GitHub releases 以获取安全更新通知。
