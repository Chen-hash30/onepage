<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Page;
use App\Core\Database;

class AdminController extends Controller {
    public function __construct() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: /');
            exit;
        }
    }

    public function index() {
        $db = Database::getInstance();
        $userCount = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $pageCount = $db->query("SELECT COUNT(*) FROM pages")->fetchColumn();
        $totalViews = $db->query("SELECT SUM(views) FROM pages")->fetchColumn() ?: 0;

        return $this->view('admin/dashboard', [
            'title' => '超级管理后台',
            'stats' => [
                'users' => $userCount,
                'pages' => $pageCount,
                'views' => $totalViews
            ],
            'activeTab' => $_GET['tab'] ?? 'overview'
        ]);
    }

    public function banPage($id) {
        // 立即设置 JSON 头部，防止任何其他输出
        header('Content-Type: application/json');
        ob_clean(); // 清理任何之前的输出

        try {
            // CSRF token validation
            if (!isset($_POST['csrf_token'])) {
                echo json_encode(['success' => false, 'error' => 'CSRF token missing']);
                exit;
            }

            if ($_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
                echo json_encode(['success' => false, 'error' => 'CSRF token invalid']);
                exit;
            }

            $pageModel = new \App\Models\Page();
            $result = $pageModel->banPage($id);

            if ($result) {
                echo json_encode(['success' => true, 'message' => '页面已封禁']);
            } else {
                echo json_encode(['success' => false, 'error' => '封禁操作失败']);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => '服务器错误: ' . $e->getMessage()]);
        }
        exit;
    }

    public function unbanPage($id) {
        // 立即设置 JSON 头部，防止任何其他输出
        header('Content-Type: application/json');
        ob_clean(); // 清理任何之前的输出

        try {
            // CSRF token validation
            if (!isset($_POST['csrf_token'])) {
                echo json_encode(['success' => false, 'error' => 'CSRF token missing']);
                exit;
            }

            if ($_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
                echo json_encode(['success' => false, 'error' => 'CSRF token invalid']);
                exit;
            }

            $pageModel = new \App\Models\Page();
            $result = $pageModel->unbanPage($id);

            if ($result) {
                echo json_encode(['success' => true, 'message' => '页面已解封']);
            } else {
                echo json_encode(['success' => false, 'error' => '解封操作失败']);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => '服务器错误: ' . $e->getMessage()]);
        }
        exit;
    }

    public function reviewPage($id) {
        header('Content-Type: application/json');

        try {
            $pageModel = new \App\Models\Page();
            $page = $pageModel->findById($id);

            if (!$page) {
                echo json_encode(['success' => false, 'error' => '页面不存在']);
                exit;
            }

            // 使用 slug 作为目录名（folder_path 可能为旧数据）
            $dirName = $page['folder_path'] ?? $page['slug'];
            $filePath = __DIR__ . '/../../uploads/pages/' . $dirName . '/index.html';
            if (!file_exists($filePath)) {
                echo json_encode(['success' => false, 'error' => '页面文件不存在']);
                exit;
            }

            $content = file_get_contents($filePath);
            if ($content === false) {
                echo json_encode(['success' => false, 'error' => '无法读取文件内容']);
                exit;
            }

            $aiReviewer = new \App\Core\AIReviewer();
            $result = $aiReviewer->reviewContent($content);

            if ($result['score'] !== null) {
                $pageModel->updateAIScore($page['id'], $result['score']);

                $threshold = floatval(\App\Core\Config::get('ai.threshold', '7.0'));

                if ($result['score'] >= $threshold) {
                    $pageModel->banPage($page['id']);

                    try {
                        $adminEmail = \App\Core\Config::get('admin.email', 'admin@yourdomain.com');
                        $subject = '网页内容违规通知';
                        $message = "管理员手动审核网页 (ID: {$page['id']}, Slug: " . ($page['slug'] ?? 'N/A') . ") AI 评分: {$result['score']}，已达到违规阈值。页面已被自动封禁。";
                        \App\Core\Mailer::sendNotification($adminEmail, $subject, $message);
                    } catch (\Exception $e) {
                    }

                    echo json_encode([
                        'success' => true,
                        'message' => '页面已被封禁',
                        'data' => ['score' => $result['score'], 'violation' => true]
                    ]);
                } else {
                    echo json_encode([
                        'success' => true,
                        'message' => '内容审核通过',
                        'data' => ['score' => $result['score'], 'violation' => false]
                    ]);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'AI审核失败']);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => '审核失败']);
        }
    }
}