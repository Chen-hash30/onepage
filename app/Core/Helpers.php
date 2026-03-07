<?php

function e($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function csrf_token() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if ($token !== ($_SESSION['csrf_token'] ?? '')) {
            die('CSRF token mismatch');
        }
    }
}
