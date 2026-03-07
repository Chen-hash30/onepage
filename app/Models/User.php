<?php

namespace App\Models;

use App\Core\Database;

class User {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($data) {
        $debug = [];
        $debug[] = "=== User::create 调试信息 ===";
        $debug[] = "时间: " . date('Y-m-d H:i:s');
        $debug[] = "接收到的数据: " . json_encode($data);
        
        try {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            $data['accepted_terms'] = $data['accepted_terms'] ?? false;
            
            $debug[] = "处理后的数据: " . json_encode([
                'username' => $data['username'],
                'email' => $data['email'],
                'accepted_terms' => $data['accepted_terms']
            ]);
            
            $sql = "INSERT INTO users (username, email, password, accepted_terms) VALUES (?, ?, ?, ?)";
            $debug[] = "SQL: $sql";
            
            $stmt = $this->db->prepare($sql);
            $debug[] = "准备 SQL 成功";
            
            $result = $stmt->execute([$data['username'], $data['email'], $data['password'], $data['accepted_terms']]);
            $debug[] = "执行结果: " . ($result ? '成功' : '失败');
            $debug[] = "影响行数: " . $stmt->rowCount();
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                $debug[] = "SQL 错误信息: " . json_encode($errorInfo);
            }
            
            echo "<script>console.log(" . json_encode(implode("\n", $debug)) . ");</script>";
            return $result;
        } catch (\Exception $e) {
            $debug[] = "异常: " . $e->getMessage();
            $debug[] = "堆栈: " . $e->getTraceAsString();
            echo "<script>console.error(" . json_encode(implode("\n", $debug)) . ");</script>";
            return false;
        }
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
