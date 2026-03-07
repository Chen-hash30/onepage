# OnePage - 网页分享平台

## 📖 项目简介

OnePage 是一个现代化的网页分享平台，允许用户上传和分享静态 HTML 页面，支持自定义域名、AI 内容审核和实时数据分析。

## ✨ 主要特性

- 🚀 **轻松上传** - 通过网页界面或 MCP API 上传 HTML 页面
- 🎨 **自定义域名** - 支持自定义域名绑定
- 🤖 **AI 审核** - 使用 AI 自动进行内容审核
- 📊 **数据分析** - 实时页面访问统计
- 🔐 **用户管理** - 完整的用户认证系统
- 🎯 **管理后台** - 强大的管理面板
- 📱 **响应式设计** - 现代化、移动端友好的界面

## 🛠️ 技术栈

- **后端**: PHP 8.2+
- **数据库**: MySQL 5.7+ / MariaDB 10.3+
- **前端**: Tailwind CSS
- **Web 服务器**: Nginx / Apache
- **AI 集成**: NVIDIA NIM API

## 📋 系统要求

- PHP >= 8.2
- MySQL >= 5.7 或 MariaDB >= 10.3
- Nginx 或 Apache Web 服务器
- Composer（用于依赖管理）
- SSL 证书（推荐）

## 🚀 快速开始

1. **克隆仓库**
   ```bash
   git clone https://github.com/Chen-hash30/onepage.git
   cd onepage
   ```

2. **设置权限**
   ```bash
   chmod 777 .
   chmod -R 777 uploads/pages
   chmod -R 777 logs
   ```

3. **配置 Web 服务器**
   - 将域名的文档根目录指向 `public` 目录
   - 配置 URL 重写（参见[安装指南](INSTALL.md)）

4. **运行安装程序**
   - 在浏览器中访问你的网站
   - 按照安装向导进行操作

5. **登录并开始使用**
   - 管理员凭据在安装过程中设置

## 📚 文档

- [安装指南](INSTALL.md)
- [配置说明](docs/CONFIGURATION.md)
- [API 文档](docs/API.md)
- [故障排查](TROUBLESHOOTING.md)

## 🤝 参与贡献

欢迎贡献代码！请随时提交 Pull Request。

## 📄 许可证

本项目采用 MIT 许可证 - 详见 [LICENSE](LICENSE) 文件。

## 🙏 致谢

- Tailwind CSS 提供优秀的 CSS 框架
- NVIDIA NIM 提供 AI 内容审核服务
- 所有贡献者和用户

## 📸 截图

![首页](docs/screenshots/homepage.png)
![用户面板](docs/screenshots/dashboard.png)
![管理后台](docs/screenshots/admin.png)

## 🔗 链接

- [演示站点](https://share.kkcws.my) - 公益站，没有部署条件的用户可以注册使用
- [文档](docs/)
- [问题追踪](https://github.com/Chen-hash30/onepage/issues)
- [讨论](https://github.com/Chen-hash30/onepage/discussions)

---

由 [Chen-hash30](https://github.com/Chen-hash30) ❤️ 制作
