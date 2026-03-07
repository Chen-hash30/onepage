<?php

namespace App\Core;

class Controller {
    protected function view($name, $data = []) {
        extract($data);
        ob_start();
        require __DIR__ . "/../Views/{$name}.php";
        $content = ob_get_clean();
        require __DIR__ . "/../Views/layout/main.php";
    }

    protected function json($data, $status = 200) {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }

    protected function redirect($url) {
        header("Location: {$url}");
        exit;
    }
}
