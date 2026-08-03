# OnePage - Web Page Sharing Platform

![OnePage Logo](picture/logo.png)

[English](README.zh-CN.md) | [中文](README.md)

---

## 📖 Overview

Do you have an AI Agent that generates amazing web pages, data dashboards, and interactive games for you, but you're frustrated because you can't share them with others?

Do you have brilliant ideas from your AI conversations that you want to turn into live websites instantly?

Today, I'm excited to introduce **OnePage Web Sharing Platform**!

🌐 **Public Service Site**: https://share.kkcws.my

✨ **Why it's perfect for AI Agent owners**

### 🚀 Two Usage Options, Each with Unique Advantages

#### 1. Public Service Site (Zero-Cost, Instant Access)
- No deployment needed, sign up and start using immediately
- Upload HTML strings or ZIP files, get a subdomain instantly
- Native MCP (Model Context Protocol) integration - already supports major AI Agent platforms like Cursor and OpenClaw. Just add a few lines to your config and your Agent will have the `upload_page` superpower!
- Built-in AI-powered security system ensures content safety and compliance
- Real-time analytics to track page views and visitor data

#### 2. Self-Deployment (Full Data Control)
- Fully open source on GitHub, completely free to use
- Private deployment option for full data control
- Customizable features to meet your specific needs
- Support for custom domain binding to build your brand presence
- Multi-user management support for team use
- Can integrate with your own AI moderation services

📢 **Invitation**

Whether you want to experience the service instantly or need full control over your data, OnePage has you covered!

If you want your AI Agent to have the ability to "publish web pages", you can either sign up at `share.kkcws.my` for instant access, or deploy your own instance from GitHub for complete control.

Let's move from "chat dialogues" to "the open web" together!

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

## 📸 Screenshots

### Homepage
![Homepage](picture/homepage.png)

### Dashboard
![Dashboard](picture/dashboard.png)

### Dashboard 2
![Dashboard 2](picture/dashboard-2.png)

### Admin Panel
![Admin Panel](picture/adminpanel.png)

### MCP Settings
![MCP Settings](picture/mcpsetting.png)

## 🔗 Links

- [Demo](https://share.s17t9.my) - Public demo site, users without deployment capabilities can register and use
- [Documentation](docs/)
- [Issue Tracker](https://github.com/Chen-hash30/onepage/issues)
- [Discussions](https://github.com/Chen-hash30/onepage/discussions)

---

Made with ❤️ by [Chen-hash30](https://github.com/Chen-hash30)
