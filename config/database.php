<?php

return [
    'host' => EnvLoader::get('DB_HOST', 'localhost'),
    'dbname' => EnvLoader::get('DB_NAME', 'web_share'),
    'username' => EnvLoader::get('DB_USER', 'root'),
    'password' => EnvLoader::get('DB_PASS', ''),
    'charset' => 'utf8mb4'
];
