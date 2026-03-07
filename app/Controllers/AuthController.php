<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class AuthController extends Controller {
    protected $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function loginForm() {
        return $this->view('auth/login');
    }

    public function login() {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $user = $this->userModel->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['username'];
            if (!$user['accepted_terms']) {
                return $this->redirect('/accept-terms');
            }
            return $this->redirect('/dashboard');
        }

        return $this->view('auth/login', ['error' => 'Invalid credentials']);
    }

    public function registerForm() {
        return $this->view('auth/register');
    }

    public function sendVerificationCode() {
        $email = $_POST['email'] ?? '';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => '邮箱格式无效']);
            return;
        }

        if (strpos($email, '@gmail.com') !== false) {
            echo json_encode(['success' => false, 'message' => 'Gmail邮箱无法注册，请使用其他邮箱']);
            return;
        }

        if ($this->userModel->findByEmail($email)) {
            echo json_encode(['success' => false, 'message' => '该邮箱已被注册']);
            return;
        }

        $code = rand(100000, 999999);
        $_SESSION['verification_code'] = $code;
        $_SESSION['verification_email'] = $email;

        $subject = 'OnePage 注册验证码';
        $message = "<h2>您的注册验证码是: <strong>$code</strong></h2><p>请在10分钟内完成注册。</p>";

        if (\App\Core\Mailer::sendNotification($email, $subject, $message)) {
            echo json_encode(['success' => true, 'message' => '验证码已发送']);
        } else {
            echo json_encode(['success' => false, 'message' => '发送失败']);
        }
    }

    public function checkUsername() {
        $username = $_POST['username'] ?? '';
        if (empty($username)) {
            echo json_encode(['available' => false, 'message' => '用户名不能为空']);
            return;
        }

        if (!preg_match('/^[a-zA-Z0-9_]{4,20}$/', $username)) {
            echo json_encode(['available' => false, 'message' => '用户名格式无效']);
            return;
        }

        if ($this->userModel->findByUsername($username)) {
            echo json_encode(['available' => false, 'message' => '用户名已被注册']);
        } else {
            echo json_encode(['available' => true, 'message' => '用户名可用']);
        }
    }

    public function register() {
        // 开启错误显示
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        
        // 记录调试信息
        $debug = [];
        $debug[] = "=== 注册调试信息 ===";
        $debug[] = "时间: " . date('Y-m-d H:i:s');
        $debug[] = "POST 数据: " . json_encode($_POST);
        $debug[] = "SESSION 数据: " . json_encode($_SESSION);
        
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $verificationCode = $_POST['verification_code'] ?? '';

        $debug[] = "用户名: $username";
        $debug[] = "邮箱: $email";
        $debug[] = "验证码: $verificationCode";

        // Validation
        if (empty($username) || empty($email) || empty($password) || empty($verificationCode)) {
            $debug[] = "错误: 所有字段都是必需的";
            echo "<script>console.log(" . json_encode(implode("\n", $debug)) . ");</script>";
            return $this->view('auth/register', ['error' => '所有字段都是必需的']);
        }

        if (strpos($email, '@gmail.com') !== false) {
            $debug[] = "错误: Gmail邮箱无法注册";
            echo "<script>console.log(" . json_encode(implode("\n", $debug)) . ");</script>";
            return $this->view('auth/register', ['error' => 'Gmail邮箱无法注册，请使用其他邮箱']);
        }

        if (!preg_match('/^[a-zA-Z0-9_]{4,20}$/', $username)) {
            $debug[] = "错误: 用户名格式无效";
            echo "<script>console.log(" . json_encode(implode("\n", $debug)) . ");</script>";
            return $this->view('auth/register', ['error' => '用户名必须是4-20位字母、数字或下划线']);
        }

        if (strlen($password) < 8 || !preg_match('/[a-zA-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $debug[] = "错误: 密码格式无效";
            echo "<script>console.log(" . json_encode(implode("\n", $debug)) . ");</script>";
            return $this->view('auth/register', ['error' => '密码至少8位，必须包含字母和数字']);
        }

        try {
            $debug[] = "检查邮箱是否已注册...";
            if ($this->userModel->findByEmail($email)) {
                $debug[] = "错误: 该邮箱已被注册";
                echo "<script>console.log(" . json_encode(implode("\n", $debug)) . ");</script>";
                return $this->view('auth/register', ['error' => '该邮箱已被注册']);
            }

            $debug[] = "检查验证码...";
            $debug[] = "SESSION 验证码: " . ($_SESSION['verification_code'] ?? '未设置');
            $debug[] = "SESSION 邮箱: " . ($_SESSION['verification_email'] ?? '未设置');
            
            if ($verificationCode != ($_SESSION['verification_code'] ?? '')) {
                $debug[] = "错误: 验证码不匹配";
                echo "<script>console.log(" . json_encode(implode("\n", $debug)) . ");</script>";
                return $this->view('auth/register', ['error' => '验证码错误']);
            }

            if ($email != ($_SESSION['verification_email'] ?? '')) {
                $debug[] = "错误: 邮箱不匹配";
                echo "<script>console.log(" . json_encode(implode("\n", $debug)) . ");</script>";
                return $this->view('auth/register', ['error' => '邮箱不匹配']);
            }

            $debug[] = "开始创建用户...";
            $result = $this->userModel->create(['username' => $username, 'email' => $email, 'password' => $password]);
            $debug[] = "创建用户结果: " . ($result ? '成功' : '失败');
            
            if ($result) {
                unset($_SESSION['verification_code'], $_SESSION['verification_email']);
                $debug[] = "注册成功，准备跳转";
                echo "<script>console.log(" . json_encode(implode("\n", $debug)) . ");</script>";
                return $this->redirect('/accept-terms');
            }

            $debug[] = "错误: 注册失败";
            echo "<script>console.log(" . json_encode(implode("\n", $debug)) . ");</script>";
            return $this->view('auth/register', ['error' => '注册失败']);
        } catch (\Exception $e) {
            $debug[] = "异常: " . $e->getMessage();
            $debug[] = "堆栈: " . $e->getTraceAsString();
            echo "<script>console.error(" . json_encode(implode("\n", $debug)) . ");</script>";
            return $this->view('auth/register', ['error' => '注册失败: ' . $e->getMessage()]);
        }
    }

    public function logout() {
        session_destroy();
        return $this->redirect('/');
    }

    public function profile() {
        if (!isset($_SESSION['user_id'])) return $this->redirect('/login');
        $user = $this->userModel->findById($_SESSION['user_id']);
        
        $apiSettingsModel = new \App\Models\UserApiSettings();
        $apiSettings = $apiSettingsModel->findByUserId($_SESSION['user_id']);
        
        return $this->view('auth/profile', [
            'user' => $user,
            'apiSettings' => $apiSettings
        ]);
    }

    public function updateProfile() {
        if (!isset($_SESSION['user_id'])) return $this->redirect('/login');

        $username = $_POST['username'] ?? '';

        // Validate input
        if (empty($username)) {
            return $this->redirect('/profile?error=username_required');
        }

        // Update user
        if ($this->userModel->update($_SESSION['user_id'], ['username' => $username])) {
            $_SESSION['username'] = $username; // Update session
            return $this->redirect('/profile?status=updated');
        } else {
            return $this->redirect('/profile?error=update_failed');
        }
    }

    public function acceptTermsForm() {
        if (!isset($_SESSION['user_id'])) return $this->redirect('/login');
        return $this->view('auth/accept-terms');
    }

    public function acceptTerms() {
        if (!isset($_SESSION['user_id'])) return $this->redirect('/login');

        $this->userModel->update($_SESSION['user_id'], ['accepted_terms' => true]);
        return $this->redirect('/dashboard');
    }
}
