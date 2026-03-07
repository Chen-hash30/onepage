<?php

namespace App\Models;

use App\Core\Database;

class Setting {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function get($key, $default = null) {
        $stmt = $this->db->prepare("SELECT `value` FROM settings WHERE `key` = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['value'] : $default;
    }

    public function set($key, $value) {
        $stmt = $this->db->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?");
        return $stmt->execute([$key, $value, $value]);
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT `key`, `value` FROM settings");
        return $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
    }
}
