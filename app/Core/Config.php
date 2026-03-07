<?php

namespace App\Core;

/**
 * 配置管理类
 */
class Config {
    private static $config = null;

    /**
     * 加载配置文件
     */
    private static function load() {
        if (self::$config === null) {
            $configFile = __DIR__ . '/../../config/settings.php';
            if (file_exists($configFile)) {
                self::$config = require $configFile;
            } else {
                // 默认配置
                self::$config = [
                    'site_name' => 'OnePage',
                    'ai' => ['enabled' => false],
                    'mail' => ['method' => 'phpmail'],
                    'admin' => ['email' => 'admin@example.com'],
                ];
            }
        }
    }

    /**
     * 获取配置值
     *
     * @param string $key 配置键名，支持点号分隔的嵌套键，如 'ai.api_key'
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function get($key, $default = null) {
        self::load();

        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $default;
            }
        }

        return $value;
    }

    /**
     * 获取所有配置
     */
    public static function all() {
        self::load();
        return self::$config;
    }

    /**
     * 检查配置是否存在
     */
    public static function has($key) {
        self::load();

        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return false;
            }
            $value = $value[$k];
        }

        return true;
    }
}