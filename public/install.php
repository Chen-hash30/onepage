<?php
/**
 * 自动安装向导
 * 首次访问时自动检测并引导安装
 */

class InstallWizard {
    private $step = 1;
    private $errors = [];
    private $config = [];
    
    public function __construct() {
        session_start();
        $this->checkStep();
    }
    
    private function checkStep() {
        // 对于 POST 请求，优先从 session 获取步骤（无重定向模式）
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['install_next_step'])) {
            $this->step = (int)$_SESSION['install_next_step'];
            unset($_SESSION['install_next_step']);
        } elseif (isset($_GET['step'])) {
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
                            $this->errors[] = '数据库不存在且无法自动创建。请以 root 用户登录 MySQL 并手动创建数据库，或确保当前用户有 CREATE 权限。错误信息：' . $createError->getMessage();
                            include __DIR__ . '/../app/Views/install/step2.php';
                            return;
                        }
                    }
                    
                    $_SESSION['install_config'] = $this->config;
                    $_SESSION['install_next_step'] = 3;
                    session_write_close();
                    
                    // 不使用重定向，直接显示下一步
                    $this->step = 3;
                    $this->step3();
                    return;
                    
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
                $_SESSION['install_next_step'] = 4;
                session_write_close();
                
                // 不使用重定向，直接显示下一步
                $this->step = 4;
                $this->step4();
                return;
            }
        }
        
        include __DIR__ . '/../app/Views/install/step3.php';
    }
    
    private function step4() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->config = $_SESSION['install_config'] ?? [];
            
            // 验证配置是否存在
            if (empty($this->config['db']) || empty($this->config['admin'])) {
                $this->errors[] = '配置信息丢失，请返回重新填写';
                include __DIR__ . '/../app/Views/install/step4.php';
                return;
            }
            
            // 开始安装
            try {
                // 1. 创建 .env 文件
                $this->createEnvFile();
                
                // 2. 导入数据库
                $this->importDatabase();
                
                // 3. 创建管理员账户
                $this->createAdmin();
                
                // 4. 创建安装完成标记
                $markFile = __DIR__ . '/../.installed';
                file_put_contents($markFile, date('Y-m-d H:i:s'));
                
                // 5. 清理 session
                unset($_SESSION['install_config']);
                unset($_SESSION['install_next_step']);
                
                // 不使用重定向，直接显示完成页面
                $this->step = 5;
                $this->step5();
                return;
                
            } catch (Exception $e) {
                $errorMsg = '安装失败：' . $e->getMessage();
                $errorMsg .= '<br><br><strong>可能的原因：</strong><br>'
                    . '• 数据库用户权限不足<br>'
                    . '• 数据库连接失败<br>'
                    . '• SQL 文件有语法错误<br>'
                    . '• 表已存在冲突<br><br>'
                    . '<strong>建议操作：</strong><br>'
                    . '1. 检查数据库用户是否有 CREATE、INSERT、DROP 权限<br>'
                    . '2. 检查 schema.sql 文件是否存在且完整<br>'
                    . '3. 检查 MySQL 服务是否正常运行<br>';
                
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
        // 使用 mysqli 连接，支持多语句执行
        $mysqli = new mysqli(
            $this->config['db']['host'],
            $this->config['db']['user'],
            $this->config['db']['pass']
        );
        
        if ($mysqli->connect_error) {
            throw new Exception('数据库连接失败：' . $mysqli->connect_error);
        }
        
        // 检查 schema.sql 文件是否存在
        $sqlFile = __DIR__ . '/../schema.sql';
        if (!file_exists($sqlFile)) {
            throw new Exception('schema.sql 文件不存在，请检查文件是否完整上传');
        }
        
        // 读取 SQL 文件
        $sql = file_get_contents($sqlFile);
        if ($sql === false) {
            throw new Exception('无法读取 schema.sql 文件，请检查文件权限');
        }
        
        // 动态替换SQL文件中的数据库名为用户实际配置的数据库名
        $dbName = $this->config['db']['name'];
        $sql = str_replace('web_share', $dbName, $sql);
        
        // 执行多语句 SQL
        if (!$mysqli->multi_query($sql)) {
            throw new Exception('SQL 执行失败：' . $mysqli->error);
        }
        
        // 等待所有查询完成
        while ($mysqli->next_result()) {
            if ($result = $mysqli->store_result()) {
                $result->free();
            }
        }
        
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
        
        $mysqli->close();
    }
    
    private function createAdmin() {
        $dsn = "mysql:host={$this->config['db']['host']};dbname={$this->config['db']['name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $this->config['db']['user'], $this->config['db']['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 检查 users 表是否存在
        $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
        if ($stmt->rowCount() === 0) {
            throw new Exception('users 表不存在，数据库导入可能失败');
        }
        
        // 创建管理员账户（强制创建，替换已存在的管理员）
        $hashedPassword = password_hash($this->config['admin']['password'], PASSWORD_DEFAULT);
        
        // 先删除所有管理员
        $pdo->exec("DELETE FROM users WHERE role = 'admin'");
        
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
    }
}

// 运行安装向导
$wizard = new InstallWizard();
$wizard->run();
