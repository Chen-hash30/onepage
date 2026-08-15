<?php

$router->add('GET', '/', 'HomeController@index');
$router->add('GET', '/login', 'AuthController@loginForm');
$router->add('POST', '/login', 'AuthController@login');
$router->add('GET', '/register', 'AuthController@registerForm');
$router->add('POST', '/register', 'AuthController@register');
$router->add('POST', '/send-verification-code', 'AuthController@sendVerificationCode');
$router->add('POST', '/check-username', 'AuthController@checkUsername');
$router->add('GET', '/logout', 'AuthController@logout');
$router->add('GET', '/profile', 'AuthController@profile');
$router->add('POST', '/profile', 'AuthController@updateProfile');
$router->add('GET', '/accept-terms', 'AuthController@acceptTermsForm');
$router->add('POST', '/accept-terms', 'AuthController@acceptTerms');

// API Settings for MCP
$router->add('GET', '/api/settings', 'ApiController@settings');
$router->add('POST', '/api/save', 'ApiController@saveSettings');
$router->add('POST', '/api/toggle', 'ApiController@toggleApi');
$router->add('POST', '/api/regenerate-key', 'ApiController@regenerateKey');
$router->add('GET', '/api/status', 'ApiController@getStatus');

// MCP API Endpoint (JSON-RPC)
$router->add('GET', '/api/mcp', function() {
    require __DIR__ . '/../public/mcp-server.php';
});
$router->add('POST', '/api/mcp', function() {
    require __DIR__ . '/../public/mcp-server.php';
});

// MCP SSE Endpoint (dedicated)
$router->add('GET', '/api/mcp/sse', function() {
    require __DIR__ . '/../public/mcp-server.php';
});

// Webpage Hosting
$router->add('GET', '/dashboard', 'DashboardController@index');
$router->add('POST', '/upload', 'PageController@upload');
$router->add('GET', '/p/{slug}', 'PageController@view');
$router->add('GET', '/manage', 'PageController@manage');
// Page deletion (user dashboard)
$router->add('POST', '/pages/delete/{id}', 'PageController@delete');
$router->add('POST', '/pages/toggle-public/{id}', 'PageController@togglePublic');

// 文件重新上传替换（访问地址保持不变）
$router->add('POST', '/pages/replace/{id}', 'PageController@replace');

// Admin routes - specific routes first
$router->add('POST', '/admin/ban/{id}', 'AdminController@banPage');
$router->add('POST', '/admin/unban/{id}', 'AdminController@unbanPage');
$router->add('POST', '/admin/review/{id}', 'AdminController@reviewPage');
$router->add('GET', '/admin', 'AdminController@index');
$router->add('POST', '/admin', 'AdminController@index');
// Redirect old routes to new tab-based system
$router->add('GET', '/admin/users', function() {
    header('Location: /admin?tab=users');
    exit;
});
$router->add('GET', '/admin/pages', function() {
    header('Location: /admin?tab=pages');
    exit;
});
