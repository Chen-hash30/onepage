# Contributing to OnePage

[English](#english) | [中文](#中文)

---

<a name="english"></a>
## English

Thank you for your interest in contributing to OnePage! This document provides guidelines and instructions for contributing.

## Code of Conduct

By participating in this project, you agree to maintain a respectful and inclusive environment for all contributors.

## How to Contribute

### Reporting Bugs

If you find a bug, please create an issue with:

1. **Clear title** describing the problem
2. **Steps to reproduce** the issue
3. **Expected behavior** vs actual behavior
4. **Environment details** (PHP version, MySQL version, OS, etc.)
5. **Screenshots** if applicable

### Suggesting Features

We welcome feature suggestions! Please:

1. Check if the feature has already been suggested
2. Create an issue with the `enhancement` label
3. Describe the feature in detail
4. Explain why it would be useful

### Pull Requests

1. **Fork** the repository
2. **Create a branch** for your feature/fix:
   ```bash
   git checkout -b feature/your-feature-name
   # or
   git checkout -b fix/your-fix-name
   ```
3. **Make your changes**
4. **Test thoroughly**
5. **Commit** with clear messages:
   ```bash
   git commit -m "Add: feature description"
   git commit -m "Fix: bug description"
   ```
6. **Push** to your fork:
   ```bash
   git push origin feature/your-feature-name
   ```
7. **Create a Pull Request**

### Coding Standards

- Follow **PSR-12** coding standards for PHP
- Use **meaningful variable and function names**
- Add **comments** for complex logic
- Write **secure code** (sanitize inputs, prevent SQL injection, etc.)
- Keep code **DRY** (Don't Repeat Yourself)

### Testing

Before submitting a PR:

1. Test all affected features
2. Check for PHP errors
3. Verify database operations
4. Test on different browsers (for frontend changes)

---

<a name="中文"></a>
## 中文

感谢您有兴趣为 OnePage 做贡献！本文档提供了贡献的指南和说明。

## 行为准则

通过参与本项目，您同意为所有贡献者维护一个尊重和包容的环境。

## 如何贡献

### 报告 Bug

如果您发现了 bug，请创建一个 issue，包含：

1. **清晰的标题**描述问题
2. **重现步骤**
3. **预期行为** vs 实际行为
4. **环境详情**（PHP 版本、MySQL 版本、操作系统等）
5. **截图**（如果适用）

### 建议功能

我们欢迎功能建议！请：

1. 检查该功能是否已被建议
2. 创建带有 `enhancement` 标签的 issue
3. 详细描述功能
4. 解释为什么它有用

### Pull Request

1. **Fork** 仓库
2. **创建分支**：
   ```bash
   git checkout -b feature/your-feature-name
   # 或
   git checkout -b fix/your-fix-name
   ```
3. **进行修改**
4. **充分测试**
5. **提交**并写清楚提交信息：
   ```bash
   git commit -m "Add: 功能描述"
   git commit -m "Fix: bug 描述"
   ```
6. **推送**到你的 fork：
   ```bash
   git push origin feature/your-feature-name
   ```
7. **创建 Pull Request**

### 编码规范

- 遵循 **PSR-12** PHP 编码标准
- 使用**有意义的变量和函数名**
- 为复杂逻辑添加**注释**
- 编写**安全的代码**（过滤输入、防止 SQL 注入等）
- 保持代码**DRY**（不要重复自己）

### 测试

提交 PR 前：

1. 测试所有受影响的功能
2. 检查 PHP 错误
3. 验证数据库操作
4. 在不同浏览器上测试（前端更改）

---

## Development Setup / 开发环境设置

```bash
# Clone your fork
git clone https://github.com/Chen-hash30/onepage.git
cd onepage

# Create a branch
git checkout -b feature/your-feature

# Make changes and test
# ...

# Commit and push
git add .
git commit -m "Add: your feature"
git push origin feature/your-feature
```

---

## Questions? / 有问题？

- 💬 [GitHub Discussions](https://github.com/Chen-hash30/onepage/discussions)
- 🐛 [Report an Issue](https://github.com/Chen-hash30/onepage/issues)

Thank you for contributing! 🎉
感谢您的贡献！🎉
