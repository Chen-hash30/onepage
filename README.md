# OnePage - Web Page Sharing Platform

[English](README.md) | [中文](README.zh-CN.md)

---

## 📖 Overview

OnePage is a modern web page sharing platform that allows users to upload and share static HTML pages with custom domains, AI-powered content moderation, and real-time analytics.

## ✨ Features

- 🚀 **Easy Upload** - Upload HTML pages via web interface or MCP API
- 🎨 **Custom Domains** - Support for custom domain binding
- 🤖 **AI Moderation** - Automatic content review using AI
- 📊 **Analytics** - Real-time page view statistics
- 🔐 **User Management** - Complete user authentication system
- 🎯 **Admin Panel** - Powerful admin dashboard
- 📱 **Responsive Design** - Modern, mobile-friendly UI

## 🛠️ Tech Stack

- **Backend**: PHP 8.2+
- **Database**: MySQL 5.7+ / MariaDB 10.3+
- **Frontend**: Tailwind CSS
- **Web Server**: Nginx / Apache
- **AI Integration**: NVIDIA NIM API

## 📋 Requirements

- PHP >= 8.2
- MySQL >= 5.7 or MariaDB >= 10.3
- Nginx or Apache web server
- Composer (for dependencies)
- SSL certificate (recommended)

## 🚀 Quick Start

1. **Clone the repository**
   ```bash
   git clone https://github.com/Chen-hash30/onepage.git
   cd onepage
   ```

2. **Set permissions**
   ```bash
   chmod 777 .
   chmod -R 777 uploads/pages
   chmod -R 777 logs
   ```

3. **Configure web server**
   - Point your domain's document root to the `public` directory
   - Configure URL rewriting (see [Installation Guide](INSTALL.md))

4. **Run the installer**
   - Visit your website in a browser
   - Follow the installation wizard

5. **Login and start using**
   - Default admin credentials are set during installation

## 📚 Documentation

- [Installation Guide](INSTALL.md)
- [Configuration](docs/CONFIGURATION.md)
- [API Documentation](docs/API.md)
- [Troubleshooting](TROUBLESHOOTING.md)

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📄 License

This project is licensed under the GNU General Public License v3.0 - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Tailwind CSS for the amazing CSS framework
- NVIDIA NIM for AI content moderation
- All contributors and users

##  Screenshots

![Homepage](docs/screenshots/homepage.png)
![Dashboard](docs/screenshots/dashboard.png)
![Admin Panel](docs/screenshots/admin.png)

## 🔗 Links

- [Demo](https://share.kkcws.my) - Public demo site, users without deployment capabilities can register and use
- [Documentation](docs/)
- [Issue Tracker](https://github.com/Chen-hash30/onepage/issues)
- [Discussions](https://github.com/Chen-hash30/onepage/discussions)

---

Made with ❤️ by [Chen-hash30](https://github.com/Chen-hash30)
