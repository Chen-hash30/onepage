<?php

/**
 * OnePage 系统配置文件
 *
 * 此文件包含系统的所有配置参数
 * 修改此文件后，配置会立即生效
 * 
 * 注意：敏感配置请通过环境变量设置，详见 .env 文件
 */

return [
    // ====================
    // 基本设置
    // ====================
    'site_name' => EnvLoader::get('APP_NAME', 'OnePage'),
    'site_description' => '让你的想法跃然纸上',
    'site_url' => EnvLoader::get('APP_URL', 'http://localhost'),

    // ====================
    // AI 审核配置
    // ====================
    'ai' => [
        'enabled' => EnvLoader::get('AI_ENABLED', 'false') === 'true',
        'api_key' => EnvLoader::get('AI_API_KEY', ''),
        'api_url' => EnvLoader::get('AI_API_URL', 'https://integrate.api.nvidia.com/v1/chat/completions'),
        'model' => EnvLoader::get('AI_MODEL', 'qwen/qwen3.5-122b-a10b'),
        'threshold' => (float)EnvLoader::get('AI_THRESHOLD', '6.0'),
        'temperature' => 0.1,
    ],

    // ====================
    // 邮件配置
    // ====================
    'mail' => [
        'method' => EnvLoader::get('MAIL_METHOD', 'smtp'),
        'from_email' => EnvLoader::get('MAIL_FROM_EMAIL', 'noreply@example.com'),
        'from_name' => EnvLoader::get('MAIL_FROM_NAME', 'OnePage System'),

        'smtp' => [
            'host' => EnvLoader::get('MAIL_HOST', 'smtp.example.com'),
            'port' => (int)EnvLoader::get('MAIL_PORT', '587'),
            'username' => EnvLoader::get('MAIL_USERNAME', ''),
            'password' => EnvLoader::get('MAIL_PASSWORD', ''),
            'encryption' => EnvLoader::get('MAIL_ENCRYPTION', 'tls'),
        ],
    ],

    // ====================
    // 管理员配置
    // ====================
    'admin' => [
        'email' => EnvLoader::get('ADMIN_EMAIL', 'admin@example.com'),
    ],

    // ====================
    // 文件上传配置
    // ====================
    'upload' => [
        'max_size' => 10 * 1024 * 1024,
        'allowed_types' => ['html', 'htm'],
        'path' => __DIR__ . '/../uploads/pages/',
    ],
];
