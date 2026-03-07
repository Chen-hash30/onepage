<?php

namespace App\Models;

use App\Core\Database;

class Page {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($userId, $title, $slug, $folderPath) {
        $stmt = $this->db->prepare("INSERT INTO pages (user_id, title, slug, folder_path) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$userId, $title, $slug, $folderPath]);
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM pages WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findBySlug($slug) {
        $stmt = $this->db->prepare("
            SELECT p.*, u.username, u.email 
            FROM pages p 
            LEFT JOIN users u ON p.user_id = u.id 
            WHERE p.slug = ?
        ");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }

    public function getUserPages($userId) {
        $stmt = $this->db->prepare("SELECT * FROM pages WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function incrementViews($id) {
        $stmt = $this->db->prepare("UPDATE pages SET views = views + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function logVisit($pageId, $ipAddress, $userAgent) {
        $stmt = $this->db->prepare("INSERT INTO visits (page_id, ip_address, user_agent) VALUES (?, ?, ?)");
        return $stmt->execute([$pageId, $ipAddress, $userAgent]);
    }

    public function banPage($id) {
        try {
            $stmt = $this->db->prepare("UPDATE pages SET banned = 1 WHERE id = ?");
            $result = $stmt->execute([$id]);
            $rowCount = $stmt->rowCount();
            return $result && $rowCount > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function unbanPage($id) {
        try {
            $stmt = $this->db->prepare("UPDATE pages SET banned = 0 WHERE id = ?");
            $result = $stmt->execute([$id]);
            $rowCount = $stmt->rowCount();
            return $result && $rowCount > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function updateAIScore($id, $score) {
        $stmt = $this->db->prepare("UPDATE pages SET ai_score = ? WHERE id = ?");
        return $stmt->execute([$score, $id]);
    }

    public function getAllPagesWithUsers() {
        $stmt = $this->db->prepare("
            SELECT p.*, u.username, u.email 
            FROM pages p 
            JOIN users u ON p.user_id = u.id 
            ORDER BY p.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM pages WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function update($id, $data) {
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
        $values[] = $id;
        $stmt = $this->db->prepare("UPDATE pages SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }
}