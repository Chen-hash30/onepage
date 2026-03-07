<?php

namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller {
    public function index() {
        return $this->view('home', ['title' => 'OnePage - 创作与分享']);
    }
}
