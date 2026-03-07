<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Page;

class DashboardController extends Controller {
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
    }

    public function index() {
        $pageModel = new Page();
        $pages = $pageModel->getUserPages($_SESSION['user_id']);
        return $this->view('dashboard/index', [
            'title' => '开发中心',
            'pages' => $pages
        ]);
    }
}
