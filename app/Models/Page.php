<?php

namespace App\Models;

use App\Core\Database;

class Page {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * 轻量级迁移：确保 page_versions 版本历史表存在（对已安装实例生效）
     */
    public static function ensureSchema() {
        $db = Database::getInstance();
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS page_versions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                page_id INT NOT NULL,
                version_number INT NOT NULL,
                backup_path VARCHAR(255) DEFAULT NULL,
                note VARCHAR(255) DEFAULT NULL,
                created_by INT DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                KEY idx_page_versions_page (page_id),
                CONSTRAINT fk_page_versions_page FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE
            )");
        } catch (\Exception $e) {
            // 表已存在或数据库权限不足时忽略，避免阻塞主流程
        }
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

    // ==================== 版本管理 ====================

    /**
     * 获取页面下一个版本号
     */
    public function getNextVersionNumber($pageId) {
        $stmt = $this->db->prepare("SELECT COALESCE(MAX(version_number), 0) + 1 FROM page_versions WHERE page_id = ?");
        $stmt->execute([$pageId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * 记录一个新版本（backup_path 存相对项目根目录的路径）
     */
    public function createVersion($pageId, $versionNumber, $backupPath, $note, $createdBy) {
        try {
            $stmt = $this->db->prepare("INSERT INTO page_versions (page_id, version_number, backup_path, note, created_by) VALUES (?, ?, ?, ?, ?)");
            return $stmt->execute([$pageId, $versionNumber, $backupPath, $note, $createdBy]);
        } catch (\Exception $e) {
            // 版本记录失败不影响文件写入结果
            return false;
        }
    }
}