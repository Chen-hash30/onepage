<?php

namespace App\Models;

use App\Core\Database;

class User {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($data) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['accepted_terms'] = $data['accepted_terms'] ?? false;
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password, accepted_terms) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$data['username'], $data['email'], $data['password'], $data['accepted_terms']]);
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function update($id, $data) {
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
        $values[] = $id;
        $stmt = $this->db->prepare("UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }
}
