<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserApiSettings;

class ApiController extends Controller {
    protected $apiSettingsModel;

    public function __construct() {
        $this->apiSettingsModel = new UserApiSettings();
    }

    public function settings() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $settings = $this->apiSettingsModel->findByUserId($userId);
        
        return $this->view('api/settings', [
            'title' => 'API 设置',
            'settings' => $settings
        ]);
    }

    public function saveSettings() {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => '请先登录']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $generateNewKey = isset($_POST['generate_new_key']);

        $existingSettings = $this->apiSettingsModel->findByUserId($userId);

        $data = [];

        if ($existingSettings) {
            if ($generateNewKey || !$existingSettings['api_key']) {
                $data['api_key'] = $this->apiSettingsModel->generateApiKey();
                $data['api_secret'] = $this->apiSettingsModel->generateSecret();
            }
            
            if (!empty($data)) {
                $this->apiSettingsModel->update($userId, $data);
            }
        } else {
            $data['api_key'] = $this->apiSettingsModel->generateApiKey();
            $data['api_secret'] = $this->apiSettingsModel->generateSecret();
            $this->apiSettingsModel->create($userId, $data);
        }

        $updatedSettings = $this->apiSettingsModel->findByUserId($userId);
        
        echo json_encode([
            'success' => true, 
            'message' => '设置已保存',
            'api_key' => $updatedSettings['api_key']
        ]);
    }

    public function toggleApi() {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => '请先登录']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $enabled = isset($_POST['enabled']) ? $_POST['enabled'] === 'true' : false;

        $existingSettings = $this->apiSettingsModel->findByUserId($userId);

        if ($existingSettings) {
            $this->apiSettingsModel->update($userId, ['api_enabled' => $enabled ? 1 : 0]);
        } else {
            $apiKey = $this->apiSettingsModel->generateApiKey();
            $apiSecret = $this->apiSettingsModel->generateSecret();
            $this->apiSettingsModel->create($userId, [
                'api_enabled' => $enabled ? 1 : 0,
                'api_key' => $apiKey,
                'api_secret' => $apiSecret
            ]);
        }

        echo json_encode(['success' => true, 'message' => $enabled ? 'API 已启用' : 'API 已禁用']);
    }

    public function getStatus() {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => '请先登录']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $settings = $this->apiSettingsModel->findByUserId($userId);

        if (!$settings) {
            echo json_encode([
                'success' => true,
                'enabled' => false,
                'configured' => false
            ]);
            return;
        }

        echo json_encode([
            'success' => true,
            'enabled' => (bool)$settings['api_enabled'],
            'configured' => !empty($settings['api_key']),
        ]);
    }

    public function regenerateKey() {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => '请先登录']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $existingSettings = $this->apiSettingsModel->findByUserId($userId);

        $newKey = $this->apiSettingsModel->generateApiKey();
        $newSecret = $this->apiSettingsModel->generateSecret();

        if ($existingSettings) {
            $this->apiSettingsModel->update($userId, [
                'api_key' => $newKey,
                'api_secret' => $newSecret
            ]);
        } else {
            $this->apiSettingsModel->create($userId, [
                'api_enabled' => false,
                'api_key' => $newKey,
                'api_secret' => $newSecret
            ]);
        }

        echo json_encode([
            'success' => true,
            'api_key' => $newKey
        ]);
    }
}
