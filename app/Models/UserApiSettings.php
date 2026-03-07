<?php

namespace App\Models;

use App\Core\Database;

class UserApiSettings {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findByUserId($userId) {
        $stmt = $this->db->prepare("SELECT * FROM user_api_settings WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function create($userId, $data = []) {
        $stmt = $this->db->prepare("INSERT INTO user_api_settings (user_id, api_enabled, api_key, api_secret) VALUES (?, ?, ?, ?)");
        return $stmt->execute([
            $userId,
            $data['api_enabled'] ?? false,
            $data['api_key'] ?? null,
            $data['api_secret'] ?? null
        ]);
    }

    public function update($userId, $data) {
        $fields = [];
        $values = [];
        
        if (isset($data['api_enabled'])) {
            $fields[] = "api_enabled = ?";
            $values[] = $data['api_enabled'];
        }
        if (isset($data['api_key'])) {
            $fields[] = "api_key = ?";
            $values[] = $data['api_key'];
        }
        if (isset($data['api_secret'])) {
            $fields[] = "api_secret = ?";
            $values[] = $data['api_secret'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $values[] = $userId;
        $stmt = $this->db->prepare("UPDATE user_api_settings SET " . implode(', ', $fields) . " WHERE user_id = ?");
        return $stmt->execute($values);
    }

    public function findByApiKey($apiKey) {
        $stmt = $this->db->prepare("SELECT * FROM user_api_settings WHERE api_key = ? AND api_enabled = 1");
        $stmt->execute([$apiKey]);
        return $stmt->fetch();
    }

    public function generateApiKey() {
        return bin2hex(random_bytes(32));
    }

    public function generateSecret() {
        return bin2hex(random_bytes(16));
    }
}
