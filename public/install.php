<?php
/**
 * 自动安装向导
 * 首次访问时自动检测并引导安装
 */

class InstallWizard {
    private $step = 1;
    private $errors = [];
    private $config = [];
    private $logFile;
    
    public function __construct() {
        session_start();
        $this->checkStep();
        
        // 创建日志目录和文件
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        $this->logFile = $logDir . '/install.md';
        
        // 初始化日志文件
        if (!file_exists($this->logFile)) {
            $this->log("# 安装向导日志\n\n");
            $this->log("**开始时间**: " . date('Y-m-d H:i:s') . "\n\n");
        }
    }
    
    private function log($message) {
        file_put_contents($this->logFile, $message, FILE_APPEND);
    }
    
    private function checkStep() {
        if (isset($_GET['step'])) {
            $this->step = (int)$_GET['step'];
        }
        
        // 如果已安装，直接跳转
        if ($this->isInstalled() && $this->step !== 5) {
            header('Location: index.php');
            exit;
        }
    }
    
    private function isInstalled() {
        return file_exists(__DIR__ . '/../.installed') && 
               is_file(__DIR__ . '/../.env');
    }
    
    public function run() {
        switch($this->step) {
            case 1:
                $this->step1();
                break;
            case 2:
                $this->step2();
                break;
            case 3:
                $this->step3();
                break;
            case 4:
                $this->step4();
                break;
            case 5:
                $this->step5();
                break;
            default:
                $this->step1();
        }
    }
    
    private function step1() {
        // 步骤 1: 环境检测
        $checks = [
            'php_version' => [
                'name' => 'PHP 版本',
                'required' => '7.4.0',
                'current' => phpversion(),
                'pass' => version_compare(phpversion(), '7.4.0', '>=')
            ],
            'pdo' => [
                'name' => 'PDO 扩展',
                'pass' => extension_loaded('pdo_mysql')
            ],
            'zip' => [
                'name' => 'ZipArchive 扩展',
                'pass' => class_exists('ZipArchive')
            ],
            'openssl' => [
                'name' => 'OpenSSL 扩展',
                'pass' => extension_loaded('openssl')
            ],
            'uploads_writable' => [
                'name' => '上传目录可写',
                'pass' => is_writable(__DIR__ . '/../uploads/pages')
            ],
            'config_writable' => [
                'name' => '配置目录可写',
                'pass' => is_writable(__DIR__ . '/../')
            ]
        ];
        
        $allPass = !in_array(false, array_column($checks, 'pass'));
        
        include __DIR__ . '/../app/Views/install/step1.php';
    }
    
    private function step2() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->config['db'] = [
                'host' => trim($_POST['db_host'] ?? 'localhost'),
                'name' => trim($_POST['db_name'] ?? ''),
                'user' => trim($_POST['db_user'] ?? ''),
                'pass' => $_POST['db_pass'] ?? ''
            ];
            
            // 验证必填字段
            if (empty($this->config['db']['name']) || empty($this->config['db']['user'])) {
                $this->errors[] = '请填写数据库名称和用户名';
            } else {
                // 测试数据库连接
                try {
                    $dsn = "mysql:host={$this->config['db']['host']};charset=utf8mb4";
                    $pdo = new PDO($dsn, $this->config['db']['user'], $this->config['db']['pass']);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    // 检查数据库是否存在
                    $stmt = $pdo->query("SHOW DATABASES LIKE '{$this->config['db']['name']}'");
                    $dbExists = $stmt->rowCount() > 0;
                    
                    if (!$dbExists) {
                        // 尝试创建数据库
                        try {
                            $pdo->exec("CREATE DATABASE `{$this->config['db']['name']}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                        } catch (PDOException $createError) {
                            // 如果创建失败，可能是权限不足，提示用户手动创建
                            $this->errors[] = '数据库不存在且无法自动创建。请以 root 用户登录 MySQL 并手动创建数据库，或确保当前用户有 CREATE 权限。错误信息：' . $createError->getMessage();
                            include __DIR__ . '/../app/Views/install/step2.php';
                            return;
                        }
                    }
                    
                    $_SESSION['install_config'] = $this->config;
                    header('Location: ?step=3');
                    exit;
                    
                } catch (PDOException $e) {
                    $this->errors[] = '数据库连接失败：' . $e->getMessage();
                }
            }
        }
        
        include __DIR__ . '/../app/Views/install/step2.php';
    }
    
    private function step3() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->config = $_SESSION['install_config'] ?? [];
            
            $this->config['admin'] = [
                'email' => trim($_POST['admin_email'] ?? ''),
                'username' => trim($_POST['admin_username'] ?? ''),
                'password' => $_POST['admin_password'] ?? ''
            ];
            
            // 验证管理员信息
            if (empty($this->config['admin']['email']) || 
                empty($this->config['admin']['username']) || 
                empty($this->config['admin']['password'])) {
                $this->errors[] = '请填写所有管理员信息';
            } elseif (!filter_var($this->config['admin']['email'], FILTER_VALIDATE_EMAIL)) {
                $this->errors[] = '邮箱格式不正确';
            } elseif (strlen($this->config['admin']['password']) < 6) {
                $this->errors[] = '密码长度至少 6 位';
            } else {
                $_SESSION['install_config'] = $this->config;
                header('Location: ?step=4');
                exit;
            }
        }
        
        include __DIR__ . '/../app/Views/install/step3.php';
    }
    
    private function step4() {
        $this->log("## 步骤 4: 开始安装\n\n");
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->log("**请求方法**: POST\n");
            $this->log("**时间**: " . date('Y-m-d H:i:s') . "\n\n");
            
            $this->config = $_SESSION['install_config'] ?? [];
            
            // 验证配置是否存在
            if (empty($this->config['db']) || empty($this->config['admin'])) {
                $this->log("❌ **错误**: 配置信息丢失\n\n");
                $this->errors[] = '配置信息丢失，请返回重新填写';
                include __DIR__ . '/../app/Views/install/step4.php';
                return;
            }
            
            $this->log("**数据库配置**:\n");
            $this->log("- 主机: `{$this->config['db']['host']}`\n");
            $this->log("- 数据库名: `{$this->config['db']['name']}`\n");
            $this->log("- 用户: `{$this->config['db']['user']}`\n\n");
            
            $this->log("**管理员配置**:\n");
            $this->log("- 用户名: `{$this->config['admin']['username']}`\n");
            $this->log("- 邮箱: `{$this->config['admin']['email']}`\n\n");
            
            // 开始安装
            try {
                // 1. 创建 .env 文件
                $this->log("### 1. 创建 .env 文件\n");
                $this->createEnvFile();
                $this->log("✅ .env 文件创建成功\n\n");
                
                // 2. 导入数据库
                $this->log("### 2. 导入数据库\n");
                $this->importDatabase();
                $this->log("\n✅ 数据库导入成功\n\n");
                
                // 3. 创建管理员账户
                $this->log("### 3. 创建管理员账户\n");
                $this->createAdmin();
                $this->log("✅ 管理员账户创建成功\n\n");
                
                // 4. 创建安装完成标记
                $this->log("### 4. 创建安装标记\n");
                $markFile = __DIR__ . '/../.installed';
                $result = file_put_contents($markFile, date('Y-m-d H:i:s'));
                if ($result === false) {
                    throw new Exception('无法创建安装标记文件，请检查目录权限');
                }
                $this->log("✅ 安装标记创建成功\n\n");
                
                // 5. 清理 session
                $this->log("### 5. 清理并跳转\n");
                unset($_SESSION['install_config']);
                
                $this->log("\n## ✅ 安装完成\n\n");
                $this->log("**完成时间**: " . date('Y-m-d H:i:s') . "\n");
                $this->log("**状态**: 成功\n\n---\n\n");
                
                header('Location: ?step=5');
                exit;
                
            } catch (Exception $e) {
                $this->log("\n❌ **安装失败**\n\n");
                $this->log("**错误信息**: " . $e->getMessage() . "\n\n");
                $this->log("**错误追踪**:\n```\n" . $e->getTraceAsString() . "\n```\n\n");
                $this->log("**状态**: 失败\n\n---\n\n");
                
                $errorMsg = '安装失败：' . $e->getMessage();
                
                // 记录错误到日志
                error_log('安装错误：' . $e->getMessage());
                error_log('错误追踪：' . $e->getTraceAsString());
                
                // 提供详细的解决建议
                $errorMsg .= '<br><br><strong>可能的原因：</strong><br>'
                    . '• 数据库用户权限不足<br>'
                    . '• 数据库连接失败<br>'
                    . '• SQL 文件有语法错误<br>'
                    . '• 表已存在冲突<br><br>'
                    . '<strong>建议操作：</strong><br>'
                    . '1. 检查数据库用户是否有 CREATE、INSERT、DROP 权限<br>'
                    . '2. 检查 schema.sql 文件是否存在且完整<br>'
                    . '3. 检查 MySQL 服务是否正常运行<br>'
                    . '4. 查看 <code>logs/install.md</code> 获取详细日志';
                
                $this->errors[] = $errorMsg;
            }
        }
        
        include __DIR__ . '/../app/Views/install/step4.php';
    }
    
    private function step5() {
        include __DIR__ . '/../app/Views/install/step5.php';
    }
    
    private function createEnvFile() {
        $envContent = <<<EOF
# ====================
# 应用配置
# ====================
APP_NAME=OnePage
APP_URL=http://{$_SERVER['HTTP_HOST']}

# ====================
# 数据库配置
# ====================
DB_HOST={$this->config['db']['host']}
DB_NAME={$this->config['db']['name']}
DB_USER={$this->config['db']['user']}
DB_PASS={$this->config['db']['pass']}

# ====================
# AI 审核配置
# ====================
AI_ENABLED=false
AI_API_KEY=
AI_API_URL=https://integrate.api.nvidia.com/v1/chat/completions
AI_MODEL=qwen/qwen3.5-122b-a10b
AI_THRESHOLD=6.0

# ====================
# 邮件配置
# ====================
MAIL_METHOD=phpmail
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_EMAIL=noreply@example.com
MAIL_FROM_NAME=OnePage System
MAIL_ENCRYPTION=tls

# ====================
# 管理员配置
# ====================
ADMIN_EMAIL={$this->config['admin']['email']}
EOF;
        
        file_put_contents(__DIR__ . '/../.env', $envContent);
        chmod(__DIR__ . '/../.env', 0600);
    }
    
    private function importDatabase() {
        try {
            $this->log("- 连接数据库服务器...\n");
            
            // 使用 mysqli 连接，支持多语句执行
            $mysqli = new mysqli(
                $this->config['db']['host'],
                $this->config['db']['user'],
                $this->config['db']['pass']
            );
            
            if ($mysqli->connect_error) {
                throw new Exception('数据库连接失败：' . $mysqli->connect_error);
            }
            
            $this->log("- 数据库连接成功\n");
            
            // 检查 schema.sql 文件是否存在
            $sqlFile = __DIR__ . '/../schema.sql';
            if (!file_exists($sqlFile)) {
                throw new Exception('schema.sql 文件不存在，请检查文件是否完整上传');
            }
            
            $this->log("- 找到 schema.sql 文件\n");
            
            // 读取 SQL 文件
            $sql = file_get_contents($sqlFile);
            if ($sql === false) {
                throw new Exception('无法读取 schema.sql 文件，请检查文件权限');
            }
            
            $this->log("- 读取 SQL 文件成功（" . strlen($sql) . " 字节）\n");
            $this->log("- 开始执行 SQL 语句...\n");
            
            // 执行多语句 SQL
            if (!$mysqli->multi_query($sql)) {
                throw new Exception('SQL 执行失败：' . $mysqli->error);
            }
            
            // 等待所有查询完成
            $queryCount = 0;
            while ($mysqli->next_result()) {
                if ($result = $mysqli->store_result()) {
                    $result->free();
                }
                $queryCount++;
            }
            
            $this->log("- 执行了 {$queryCount} 条 SQL 语句\n");
            
            // 选择数据库并验证表
            $mysqli->select_db($this->config['db']['name']);
            $result = $mysqli->query("SHOW TABLES");
            $tables = [];
            while ($row = $result->fetch_array()) {
                $tables[] = $row[0];
            }
            
            if (empty($tables)) {
                throw new Exception('数据库表创建失败，表列表为空');
            }
            
            $this->log("- 成功创建以下表：\n");
            foreach ($tables as $table) {
                $this->log("  - `{$table}`\n");
            }
            
            $mysqli->close();
            
        } catch (Exception $e) {
            $this->log("- ❌ 错误: " . $e->getMessage() . "\n");
            throw $e;
        }
    }
    
    private function createAdmin() {
        try {
            $this->log("- 连接数据库...\n");
            
            $dsn = "mysql:host={$this->config['db']['host']};dbname={$this->config['db']['name']};charset=utf8mb4";
            $pdo = new PDO($dsn, $this->config['db']['user'], $this->config['db']['pass']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // 检查 users 表是否存在
            $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
            if ($stmt->rowCount() === 0) {
                throw new Exception('users 表不存在，数据库导入可能失败');
            }
            
            $this->log("- users 表存在\n");
            
            // 创建管理员账户（强制创建，替换已存在的管理员）
            $hashedPassword = password_hash($this->config['admin']['password'], PASSWORD_DEFAULT);
            
            // 先删除所有管理员
            $pdo->exec("DELETE FROM users WHERE role = 'admin'");
            $this->log("- 清除旧管理员账户\n");
            
            // 插入新的管理员
            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, password, role, accepted_terms, created_at)
                VALUES (?, ?, ?, 'admin', 1, NOW())
            ");
            $stmt->execute([
                $this->config['admin']['username'],
                $this->config['admin']['email'],
                $hashedPassword
            ]);
            
            $this->log("- 创建管理员账户: `{$this->config['admin']['username']}`\n");
            $this->log("- 管理员邮箱: `{$this->config['admin']['email']}`\n");
            
        } catch (Exception $e) {
            $this->log("- ❌ 错误: " . $e->getMessage() . "\n");
            throw new Exception('创建管理员账户失败：' . $e->getMessage());
        }
    }
}

// 运行安装向导
$wizard = new InstallWizard();
$wizard->run();
