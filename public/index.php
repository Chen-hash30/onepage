<?php

// 开启错误显示（调试模式）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../app/Core/EnvLoader.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 自动安装检测
$isInstalled = file_exists(__DIR__ . '/../.installed') && is_file(__DIR__ . '/../.env');
if (!$isInstalled && strpos($_SERVER['REQUEST_URI'], '/install.php') === false) {
    header('Location: /install.php');
    exit;
}

spl_autoload_register(function ($class) {
    $root = dirname(__DIR__);
    // 将命名空间 App\ 转换为目录 app/
    $path = str_replace('App\\', 'app\\', $class);
    $file = $root . '/' . str_replace('\\', '/', $path) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

require __DIR__ . '/../app/Core/Helpers.php';

use App\Core\Router;

$router = new Router();

require __DIR__ . '/../app/routes.php';

$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    $router->dispatch($uri, $method);
} catch (Exception $e) {
    http_response_code(500);
    
    // 输出详细错误信息到浏览器控制台
    $errorInfo = [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ];
    
    echo "<script>console.error('500 Internal Server Error:', " . json_encode($errorInfo) . ");</script>";
    echo "<h1>500 Internal Server Error</h1>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<pre>File: " . htmlspecialchars($e->getFile()) . " Line: " . $e->getLine() . "</pre>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
