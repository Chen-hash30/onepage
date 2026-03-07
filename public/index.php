<?php

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
    echo "500 Internal Server Error";
}
