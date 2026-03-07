<?php

/**
 * 环境变量加载器
 * 
 * 读取 .env 文件并将其加载到 PHP $_SERVER 和 $_ENV 中
 */

class EnvLoader {
    private static $loaded = false;

    public static function load($path = null) {
        if (self::$loaded) {
            return true;
        }

        $possiblePaths = [
            dirname(__DIR__, 2) . '/.env',
            dirname(__DIR__, 3) . '/.env',
            '/www/wwwroot/share.easknow.com/.env',
            __DIR__ . '/../../.env',
        ];

        if ($path !== null) {
            array_unshift($possiblePaths, $path);
        }

        foreach ($possiblePaths as $tryPath) {
            if (file_exists($tryPath)) {
                return self::loadFromFile($tryPath);
            }
        }

        return false;
    }

    private static function loadFromFile($path) {
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);

                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
                
                // 定义常量，供 getenv() 使用
                if (!defined("ENV_{$name}")) {
                    define("ENV_{$name}", $value);
                }
            }
        }

        self::$loaded = true;
        return true;
    }
    
    /**
     * 获取环境变量值
     * 替代 getenv() 函数，从 $_SERVER 和 $_ENV 读取
     */
    public static function get($key, $default = '') {
        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        $const = "ENV_{$key}";
        if (defined($const)) {
            return constant($const);
        }
        return $default;
    }
}

EnvLoader::load();
