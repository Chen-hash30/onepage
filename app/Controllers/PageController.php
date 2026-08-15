<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Page;
use App\Models\User;

class PageController extends Controller {
    protected $pageModel;
    protected $userModel;

    public function __construct() {
        $this->pageModel = new Page();
        $this->userModel = new User();
    }

    public function upload() {
        if (!isset($_SESSION['user_id'])) {
            return $this->redirect('/login');
        }

        // Check if user has accepted terms
        $user = $this->userModel->findById($_SESSION['user_id']);
        if (!$user || !$user['accepted_terms']) {
            return $this->redirect('/accept-terms');
        }

        $title = $_POST['title'] ?? 'Untitled';
        $file = $_FILES['webfile'] ?? null;

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return $this->redirect('/dashboard?error=upload_failed');
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $slug = bin2hex(random_bytes(4)); // 8 chars slug
        $uploadDir = dirname(__DIR__, 2) . '/uploads/pages/' . $slug;

        if (!mkdir($uploadDir, 0777, true)) {
            error_log('创建目录失败：' . $uploadDir . ' - ' . print_r(error_get_last(), true));
            return $this->redirect('/dashboard?error=dir_creation_failed');
        }

        if ($ext === 'html') {
            move_uploaded_file($file['tmp_name'], $uploadDir . '/index.html');
            $content = file_get_contents($uploadDir . '/index.html');
        } elseif ($ext === 'zip') {
            $zip = new \ZipArchive;
            if ($zip->open($file['tmp_name']) === TRUE) {
                $zip->extractTo($uploadDir);
                $zip->close();
                $content = file_get_contents($uploadDir . '/index.html');
            } else {
                return $this->redirect('/dashboard?error=zip_failed');
            }
        } else {
            return $this->redirect('/dashboard?error=invalid_format');
        }

        // AI 审核
        $aiReviewer = new \App\Core\AIReviewer();
        $result = $aiReviewer->reviewContent($content, null, 'web');
        $score = $result['score'] ?? null;


        $pageId = $this->pageModel->create($_SESSION['user_id'], $title, $slug, $slug);

        if ($score !== null) {
            $this->pageModel->updateAIScore($pageId, $score);

            $threshold = floatval(\App\Core\Config::get('ai.threshold', '7.0'));

            if ($score >= $threshold) {
                // 发送邮件通知管理员
                $adminEmail = \App\Core\Config::get('admin.email', 'admin@yourdomain.com');
                $subject = '网页内容违规通知';
                $message = "用户上传的网页 (ID: $pageId, Slug: $slug) AI 评分: $score，已达到违规阈值。页面已被自动封禁，请登录后台进行审核处理。";
                $mailResult = \App\Core\Mailer::sendNotification($adminEmail, $subject, $message);

                // 自动封禁页面
                $this->pageModel->banPage($pageId);

                return $this->redirect('/dashboard?error=content_banned');
            }
        }

        return $this->redirect('/dashboard?success=1');
    }

    public function view($slug, $extra = null) {
        $page = $this->pageModel->findBySlug($slug);

        // Check if page exists
        if (!$page) {
            http_response_code(404);
            echo "页面未找到";
            exit;
        }

        // Check if page is banned
        if ($page['banned'] == 1) {
            http_response_code(403); // Forbidden
            $title = htmlspecialchars($page['title']);
            $username = htmlspecialchars($page['username'] ?? '未知用户');
            $email = htmlspecialchars($page['email'] ?? '未知邮箱');
            echo "<!DOCTYPE html>
<html lang='zh-CN' class='h-full bg-gray-950'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>网页已被封禁</title>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>
    <script src='https://cdn.tailwindcss.com'></script>
    <style>
        body { font-family: 'Microsoft YaHei', 'PingFang SC', 'Hiragino Sans GB', 'WenQuanYi Micro Hei', sans-serif; cursor: default; }
        .glass { background: rgba(15, 15, 20, 0.7); backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.08); }
        .gradient-text { background: linear-gradient(135deg, #fff 0%, #a855f7 50%, #6366f1 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .glow-card:hover { box-shadow: 0 0 40px rgba(168, 85, 247, 0.15); border-color: rgba(168, 85, 247, 0.3); }
        @keyframes float { 0% { transform: translateY(0px); } 50% { transform: translateY(-10px); } 100% { transform: translateY(0px); } }
        .animate-float { animation: float 6s ease-in-out infinite; }
        @keyframes subtle-pulse { 0%, 100% { opacity: 0.8; } 50% { opacity: 1; } }
        .animate-subtle { animation: subtle-pulse 4s ease-in-out infinite; }
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
        .main-container {
          position: fixed;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          z-index: -1;
          display: flex;
          justify-content: center;
          align-items: center;
          overflow: hidden;
          opacity: 0.5;
        }
        .loader {
          width: 100%;
          height: 100%;
        }
        .grid-line {
          stroke: #222;
          stroke-width: 0.5;
        }
        .browser-frame {
          fill: #111;
          stroke: #666;
          stroke-width: 1;
          filter: drop-shadow(0 0 10px rgba(0, 0, 0, 0.9));
        }
        .browser-top {
          fill: #1a1a1a;
        }
        .loading-text {
          font-family: Haettenschweiler, sans-serif;
          font-size: 14px;
          fill: #e4e4e4;
        }
        .skeleton {
          fill: #2d2d2d;
          rx: 4;
          ry: 4;
          animation: pulse 1.8s ease-in-out infinite;
          filter: drop-shadow(0 0 3px rgba(255, 255, 255, 0.02));
        }
        @keyframes pulse {
          0% {
            fill: #2d2d2d;
          }
          50% {
            fill: #505050;
          }
          100% {
            fill: #2d2d2d;
          }
        }
        .trace-flow {
          stroke-width: 1;
          fill: none;
          stroke-dasharray: 120 600;
          stroke-dashoffset: 720;
          animation: flow 5s linear infinite;
          opacity: 0.95;
          stroke-linejoin: round;
          filter: drop-shadow(0 0 8px currentColor) blur(0.5px);
          color: #00ccff;
        }
        .trace-flow:nth-child(1) {
          stroke: url(#traceGradient1);
        }
        .trace-flow:nth-child(2) {
          stroke: url(#traceGradient2);
        }
        .trace-flow:nth-child(3) {
          stroke: url(#traceGradient3);
        }
        .trace-flow:nth-child(4) {
          stroke: url(#traceGradient4);
        }
        @keyframes flow {
          from {
            stroke-dashoffset: 720;
          }
          to {
            stroke-dashoffset: 0;
          }
        }
    </style>
</head>
<body class='h-full text-gray-200 selection:bg-purple-500/30'>
    <div class=\"main-container\">
      <div class=\"loader\">
        <svg
          viewBox=\"0 0 900 900\"
          xmlns=\"http://www.w3.org/2000/svg\"
          preserveAspectRatio=\"none\"
        >
          <defs>
            <linearGradient
              id=\"traceGradient1\"
              x1=\"250\"
              y1=\"120\"
              x2=\"100\"
              y2=\"200\"
              gradientUnits=\"userSpaceOnUse\"
            >
              <stop offset=\"0%\" stop-color=\"#00ccff\" stop-opacity=\"1\"></stop>
              <stop offset=\"100%\" stop-color=\"#00ccff\" stop-opacity=\"0.5\"></stop>
            </linearGradient>

            <linearGradient
              id=\"traceGradient2\"
              x1=\"650\"
              y1=\"120\"
              x2=\"800\"
              y2=\"300\"
              gradientUnits=\"userSpaceOnUse\"
            >
              <stop offset=\"0%\" stop-color=\"#00ccff\" stop-opacity=\"1\"></stop>
              <stop offset=\"100%\" stop-color=\"#00ccff\" stop-opacity=\"0.5\"></stop>
            </linearGradient>

            <linearGradient
              id=\"traceGradient3\"
              x1=\"250\"
              y1=\"380\"
              x2=\"400\"
              y2=\"400\"
              gradientUnits=\"userSpaceOnUse\"
            >
              <stop offset=\"0%\" stop-color=\"#00ccff\" stop-opacity=\"1\"></stop>
              <stop offset=\"100%\" stop-color=\"#00ccff\" stop-opacity=\"0.5\"></stop>
            </linearGradient>

            <linearGradient
              id=\"traceGradient4\"
              x1=\"650\"
              y1=\"120\"
              x2=\"500\"
              y2=\"100\"
              gradientUnits=\"userSpaceOnUse\"
            >
              <stop offset=\"0%\" stop-color=\"#00ccff\" stop-opacity=\"1\"></stop>
              <stop offset=\"100%\" stop-color=\"#00ccff\" stop-opacity=\"0.5\"></stop>
            </linearGradient>
          </defs>

          <g id=\"grid\">
            <g>
              <line x1=\"0\" y1=\"0\" x2=\"0\" y2=\"100%\" class=\"grid-line\"></line>
              <line x1=\"100\" y1=\"0\" x2=\"100\" y2=\"100%\" class=\"grid-line\"></line>
              <line x1=\"200\" y1=\"0\" x2=\"200\" y2=\"100%\" class=\"grid-line\"></line>
              <line x1=\"300\" y1=\"0\" x2=\"300\" y2=\"100%\" class=\"grid-line\"></line>
              <line x1=\"400\" y1=\"0\" x2=\"400\" y2=\"100%\" class=\"grid-line\"></line>
              <line x1=\"500\" y1=\"0\" x2=\"500\" y2=\"100%\" class=\"grid-line\"></line>
              <line x1=\"600\" y1=\"0\" x2=\"600\" y2=\"100%\" class=\"grid-line\"></line>
              <line x1=\"700\" y1=\"0\" x2=\"700\" y2=\"100%\" class=\"grid-line\"></line>
              <line x1=\"800\" y1=\"0\" x2=\"800\" y2=\"100%\" class=\"grid-line\"></line>
              <line x1=\"900\" y1=\"0\" x2=\"900\" y2=\"100%\" class=\"grid-line\"></line>
              <line x1=\"1000\" y1=\"0\" x2=\"1000\" y2=\"100%\" class=\"grid-line\"></line>
              <line x1=\"1100\" y1=\"0\" x2=\"1100\" y2=\"100%\" class=\"grid-line\"></line>
              <line x1=\"1200\" y1=\"0\" x2=\"1200\" y2=\"100%\" class=\"grid-line\"></line>
              <line x1=\"1300\" y1=\"0\" x2=\"1300\" y2=\"100%\" class=\"grid-line\"></line>
              <line x1=\"1400\" y1=\"0\" x2=\"1400\" y2=\"100%\" class=\"grid-line\"></line>
              <line x1=\"1500\" y1=\"0\" x2=\"1500\" y2=\"100%\" class=\"grid-line\"></line>
              <line x1=\"1600\" y1=\"0\" x2=\"1600\" y2=\"100%\" class=\"grid-line\"></line>
            </g>

            <g>
              <line x1=\"0\" y1=\"100\" x2=\"100%\" y2=\"100\" class=\"grid-line\"></line>
              <line x1=\"0\" y1=\"200\" x2=\"100%\" y2=\"200\" class=\"grid-line\"></line>
              <line x1=\"0\" y1=\"300\" x2=\"100%\" y2=\"300\" class=\"grid-line\"></line>
              <line x1=\"0\" y1=\"400\" x2=\"100%\" y2=\"400\" class=\"grid-line\"></line>
              <line x1=\"0\" y1=\"500\" x2=\"100%\" y2=\"500\" class=\"grid-line\"></line>
              <line x1=\"0\" y1=\"600\" x2=\"100%\" y2=\"600\" class=\"grid-line\"></line>
              <line x1=\"0\" y1=\"700\" x2=\"100%\" y2=\"700\" class=\"grid-line\"></line>
              <line x1=\"0\" y1=\"800\" x2=\"100%\" y2=\"800\" class=\"grid-line\"></line>
            </g>
          </g>

          <g id=\"browser\" transform=\"translate(0, 200)\">
            <rect
              x=\"250\"
              y=\"120\"
              width=\"400\"
              height=\"260\"
              rx=\"8\"
              ry=\"8\"
              class=\"browser-frame\"
            ></rect>

            <rect
              x=\"250\"
              y=\"120\"
              width=\"400\"
              height=\"30\"
              rx=\"8\"
              ry=\"8\"
              class=\"browser-top\"
            ></rect>

            <text x=\"294\" y=\"140\" text-anchor=\"middle\" class=\"loading-text\">
              Loading...
            </text>

            <rect x=\"270\" y=\"160\" width=\"360\" height=\"20\" class=\"skeleton\"></rect>
            <rect x=\"270\" y=\"190\" width=\"200\" height=\"15\" class=\"skeleton\"></rect>
            <rect x=\"270\" y=\"215\" width=\"300\" height=\"15\" class=\"skeleton\"></rect>
            <rect x=\"270\" y=\"240\" width=\"360\" height=\"90\" class=\"skeleton\"></rect>
            <rect x=\"270\" y=\"340\" width=\"180\" height=\"20\" class=\"skeleton\"></rect>
          </g>

          <g id=\"traces\" transform=\"translate(0, 200)\">
            <path d=\"M100 300 H250 V120\" class=\"trace-flow\"></path>
            <path d=\"M800 200 H650 V380\" class=\"trace-flow\"></path>
            <path d=\"M400 520 V380 H250\" class=\"trace-flow\"></path>
            <path d=\"M500 50 V120 H650\" class=\"trace-flow\"></path>
          </g>
        </svg>
      </div>
    </div>
    <main class='min-h-full flex items-center justify-center px-4'>
        <div class='max-w-md w-full glass p-8 rounded-3xl text-center'>
            <div class='w-20 h-20 bg-red-500/10 rounded-full flex items-center justify-center mx-auto mb-6 text-4xl text-red-500'>
                <i class='fas fa-ban'></i>
            </div>
            <h1 class='text-3xl font-bold mb-4'>网页已被封禁</h1>
            <p class='text-gray-400 mb-6'>此网页已被管理员封禁。如有疑问，请联系网页开发者。</p>
            <div class='glass p-4 rounded-xl'>
                <p class='text-sm text-gray-500 mb-2'>页面信息</p>
                <p class='text-sm'>标题: {$title}</p>
                <p class='text-sm'>创建者: {$username}</p>
                <p class='text-sm'>邮箱: {$email}</p>
            </div>
        </div>
    </main>
</body>
</html>";
            exit;
        }

        // Check if page is public
        if ($page['is_public'] == 0) {
            http_response_code(403); // Forbidden
            $title = htmlspecialchars($page['title']);
            $username = htmlspecialchars($page['username'] ?? '未知用户');
            $email = htmlspecialchars($page['email'] ?? '未知邮箱');
            echo "<!DOCTYPE html>
<html lang='zh-CN' class='h-full bg-gray-950'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>网页已被暂停</title>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>
    <script src='https://cdn.tailwindcss.com'></script>
    <style>
        body { font-family: 'Microsoft YaHei', 'PingFang SC', 'Hiragino Sans GB', 'WenQuanYi Micro Hei', sans-serif; cursor: default; }
        .glass { background: rgba(15, 15, 20, 0.7); backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.08); }
        .gradient-text { background: linear-gradient(135deg, #fff 0%, #a855f7 50%, #6366f1 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .glow-card:hover { box-shadow: 0 0 40px rgba(168, 85, 247, 0.15); border-color: rgba(168, 85, 247, 0.3); }
        @keyframes float { 0% { transform: translateY(0px); } 50% { transform: translateY(-10px); } 100% { transform: translateY(0px); } }
        .animate-float { animation: float 6s ease-in-out infinite; }
        @keyframes subtle-pulse { 0%, 100% { opacity: 0.8; } 50% { opacity: 1; } }
        .animate-subtle { animation: subtle-pulse 4s ease-in-out infinite; }
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
        .main-container {
          position: fixed;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          z-index: -1;
          display: flex;
          justify-content: center;
          align-items: center;
          overflow: hidden;
          opacity: 0.5;
        }
        .loader {
          width: 100%;
          height: 100%;
        }
        .grid-line {
          stroke: #222;
          stroke-width: 0.5;
        }
        .browser-frame {
          fill: #111;
          stroke: #666;
          stroke-width: 1;
          filter: drop-shadow(0 0 10px rgba(0, 0, 0, 0.9));
        }
        .browser-top {
          fill: #1a1a1a;
        }
        .loading-text {
          font-family: Haettenschweiler, sans-serif;
          font-size: 14px;
          fill: #e4e4e4;
        }
        .skeleton {
          fill: #2d2d2d;
          rx: 4;
          ry: 4;
          animation: pulse 1.8s ease-in-out infinite;
          filter: drop-shadow(0 0 3px rgba(255, 255, 255, 0.02));
        }
        @keyframes pulse {
          0% {
            fill: #2d2d2d;
          }
          50% {
            fill: #505050;
          }
          100% {
            fill: #2d2d2d;
          }
        }
        .trace-flow {
          stroke-width: 1;
          fill: none;
          stroke-dasharray: 120 600;
          stroke-dashoffset: 720;
          animation: flow 5s linear infinite;
          opacity: 0.95;
          stroke-linejoin: round;
          filter: drop-shadow(0 0 8px currentColor) blur(0.5px);
          color: #00ccff;
        }
        .trace-flow:nth-child(1) {
          stroke: url(#traceGradient1);
        }
        .trace-flow:nth-child(2) {
          stroke: url(#traceGradient2);
        }
        .trace-flow:nth-child(3) {
          stroke: url(#traceGradient3);
        }
        .trace-flow:nth-child(4) {
          stroke: url(#traceGradient4);
        }
        @keyframes flow {
          from {
            stroke-dashoffset: 720;
          }
          to {
            stroke-dashoffset: 0;
          }
        }
    </style>
</head>
<body class='h-full text-gray-200 selection:bg-purple-500/30'>
    <div class=\"main-container\">
      <div class=\"loader\">
        <svg
          viewBox=\"0 0 900 900\"
          xmlns=\"http://www.w3.org/2000/svg\"
          preserveAspectRatio=\"none\"
        >
          <defs>
            <linearGradient
              id=\"traceGradient1\"
              x1=\"250\"
              y1=\"120\"
              x2=\"100\"
              y2=\"200\"
              gradientUnits=\"userSpaceOnUse\"
            >
              <stop offset=\"0%\" stop-color=\"#00ccff\" stop-opacity=\"1\"></stop>
              <stop offset=\"100%\" stop-color=\"#00ccff\" stop-opacity=\"0.5\"></stop>
            </linearGradient>

            <linearGradient
              id=\"traceGradient2\"
              x1=\"650\"
              y1=\"120\"
              x2=\"800\"
              y2=\"300\"
              gradientUnits=\"userSpaceOnUse\"
            >
              <stop offset=\"0%\" stop-color=\"#00ccff\" stop-opacity=\"1\"></stop>
              <stop offset=\"100%\" stop-color=\"#00ccff\" stop-opacity=\"0.5\"></stop>
            </linearGradient>

            <linearGradient
              id=\"traceGradient3\"
              x1=\"250\"
              y1=\"380\"
              x2=\"400\"
              y2=\"400\"
              gradientUnits=\"userSpaceOnUse\"
            >
              <stop offset=\"0%\" stop-color=\"#00ccff\" stop-opacity=\"1\"></stop>
              <stop offset=\"100%\" stop-color=\"#00ccff\" stop-opacity=\"0.5\"></stop>
            </linearGradient>

            <linearGradient
              id=\"traceGradient4\"
              x1=\"650\"
              y1=\"120\"
              x2=\"500\"
              y2=\"100\"
              gradientUnits=\"userSpaceOnUse\"
            >
              <stop offset=\"0%\" stop-color=\"#00ccff\" stop-opacity=\"1\"></stop>
              <stop offset=\"100%\" stop-color=\"#00ccff\" stop-opacity=\"0.5\"></stop>
            </linearGradient>
          </defs>

          <g id=\"grid\">
            <g>
              <line x1=\"0\" y1=\"0\" x2=\"0\" y2=\"100%\" class=\"grid-line\"></line>
              <line x1=\"100\" y1=\"0\" x2=\"100\" y2=\"100%\" class=\"grid-line\"></line>
              <line x1=\"200\" y1=\"0\" x2=\"200\" y2=\"100%\" class=\"grid-line\"></line>
              <line x1=\"300\" y1=\"0\" x2=\"300\" y2=\"100%\" class=\"grid-line\"></line>
              <line x1=\"400\" y1=\"0\" x2=\"400\" y2=\"100%\" class=\"grid-line\"></line>
              <line x1=\"500\" y1=\"0\" x2=\"500\" y2=\"100%\" class=\"grid-line\"></line>
              <line x1=\"600\" y1=\"0\" x2=\"600\" y2=\"100%\" class=\"grid-line\"></line>
              <line x1=\"700\" y1=\"0\" x2=\"700\" y2=\"100%\" class=\"grid-line\"></line>
              <line x1=\"800\" y1=\"0\" x2=\"800\" y2=\"100%\" class=\"grid-line\"></line>
              <line x1=\"900\" y1=\"0\" x2=\"900\" y2=\"100%\" class=\"grid-line\"></line>
              <line x1=\"1000\" y1=\"0\" x2=\"1000\" y2=\"100%\" class=\"grid-line\"></line>
              <line x1=\"1100\" y1=\"0\" x2=\"1100\" y2=\"100%\" class=\"grid-line\"></line>
              <line x1=\"1200\" y1=\"0\" x2=\"1200\" y2=\"100%\" class=\"grid-line\"></line>
              <line x1=\"1300\" y1=\"0\" x2=\"1300\" y2=\"100%\" class=\"grid-line\"></line>
              <line x1=\"1400\" y1=\"0\" x2=\"1400\" y2=\"100%\" class=\"grid-line\"></line>
              <line x1=\"1500\" y1=\"0\" x2=\"1500\" y2=\"100%\" class=\"grid-line\"></line>
              <line x1=\"1600\" y1=\"0\" x2=\"1600\" y2=\"100%\" class=\"grid-line\"></line>
            </g>

            <g>
              <line x1=\"0\" y1=\"100\" x2=\"100%\" y2=\"100\" class=\"grid-line\"></line>
              <line x1=\"0\" y1=\"200\" x2=\"100%\" y2=\"200\" class=\"grid-line\"></line>
              <line x1=\"0\" y1=\"300\" x2=\"100%\" y2=\"300\" class=\"grid-line\"></line>
              <line x1=\"0\" y1=\"400\" x2=\"100%\" y2=\"400\" class=\"grid-line\"></line>
              <line x1=\"0\" y1=\"500\" x2=\"100%\" y2=\"500\" class=\"grid-line\"></line>
              <line x1=\"0\" y1=\"600\" x2=\"100%\" y2=\"600\" class=\"grid-line\"></line>
              <line x1=\"0\" y1=\"700\" x2=\"100%\" y2=\"700\" class=\"grid-line\"></line>
              <line x1=\"0\" y1=\"800\" x2=\"100%\" y2=\"800\" class=\"grid-line\"></line>
            </g>
          </g>

          <g id=\"browser\" transform=\"translate(0, 200)\">
            <rect
              x=\"250\"
              y=\"120\"
              width=\"400\"
              height=\"260\"
              rx=\"8\"
              ry=\"8\"
              class=\"browser-frame\"
            ></rect>

            <rect
              x=\"250\"
              y=\"120\"
              width=\"400\"
              height=\"30\"
              rx=\"8\"
              ry=\"8\"
              class=\"browser-top\"
            ></rect>

            <text x=\"294\" y=\"140\" text-anchor=\"middle\" class=\"loading-text\">
              Loading...
            </text>

            <rect x=\"270\" y=\"160\" width=\"360\" height=\"20\" class=\"skeleton\"></rect>
            <rect x=\"270\" y=\"190\" width=\"200\" height=\"15\" class=\"skeleton\"></rect>
            <rect x=\"270\" y=\"215\" width=\"300\" height=\"15\" class=\"skeleton\"></rect>
            <rect x=\"270\" y=\"240\" width=\"360\" height=\"90\" class=\"skeleton\"></rect>
            <rect x=\"270\" y=\"340\" width=\"180\" height=\"20\" class=\"skeleton\"></rect>
          </g>

          <g id=\"traces\" transform=\"translate(0, 200)\">
            <path d=\"M100 300 H250 V120\" class=\"trace-flow\"></path>
            <path d=\"M800 200 H650 V380\" class=\"trace-flow\"></path>
            <path d=\"M400 520 V380 H250\" class=\"trace-flow\"></path>
            <path d=\"M500 50 V120 H650\" class=\"trace-flow\"></path>
          </g>
        </svg>
      </div>
    </div>
    <main class='min-h-full flex items-center justify-center px-4'>
        <div class='max-w-md w-full glass p-8 rounded-3xl text-center'>
            <div class='w-20 h-20 bg-orange-500/10 rounded-full flex items-center justify-center mx-auto mb-6 text-4xl text-orange-500'>
                <i class='fas fa-pause'></i>
            </div>
            <h1 class='text-3xl font-bold mb-4'>网页已被暂停</h1>
            <p class='text-gray-400 mb-6'>此页面已被发布者暂停访问。</p>
            <div class='glass p-4 rounded-xl'>
                <p class='text-sm text-gray-500 mb-2'>页面信息</p>
                <p class='text-sm'>标题: {$title}</p>
                <p class='text-sm'>创建者: {$username}</p>
                <p class='text-sm'>邮箱: {$email}</p>
            </div>
        </div>
    </main>
</body>
</html>";
            exit;
        }

        // Increment views only on index
        $this->pageModel->incrementViews($page['id']);
        $this->pageModel->logVisit($page['id'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);

        $filePath = __DIR__ . '/../../uploads/pages/' . $slug . '/index.html';
        if (file_exists($filePath)) {
            header("Content-Type: text/html");
            readfile($filePath);
            exit;
        } else {
            echo "Index.html not found.";
            exit;
        }
    }

    public function serveAsset($slug, $path) {
        // Check if the page is banned before serving any assets
        $page = $this->pageModel->findBySlug($slug);
        if (!$page || $page['banned'] == 1) {
            http_response_code(403); // Forbidden
            exit;
        }

        $baseDir = realpath(__DIR__ . '/../../uploads/pages/' . $slug);
        $filePath = realpath($baseDir . '/' . $path);

        // Security check: ensure path is within the baseDir
        if ($filePath && strpos($filePath, $baseDir) === 0 && file_exists($filePath)) {
            $mimeType = mime_content_type($filePath);
            // Fix for some mime types
            $ext = pathinfo($filePath, PATHINFO_EXTENSION);
            if ($ext === 'css') $mimeType = 'text/css';
            if ($ext === 'js') $mimeType = 'application/javascript';

            header("Content-Type: " . $mimeType);
            readfile($filePath);
            exit;
        }
        http_response_code(404);
        exit;
    }

    public function delete($id) {
        if (!isset($_SESSION['user_id'])) {
            return $this->redirect('/login');
        }

        // CSRF 校验，防止未授权删除
        if (!$this->verifyCsrf()) {
            return $this->redirect('/dashboard?error=csrf');
        }

        $page = $this->pageModel->findById($id);
        if (!$page) {
            return $this->redirect('/dashboard?error=not_found');
        }

        // Only owner or admin can delete
        $isOwner = ($page['user_id'] == $_SESSION['user_id']);
        $isAdmin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
        if (!($isOwner || $isAdmin)) {
            return $this->redirect('/dashboard?error=permission');
        }

        $folder = __DIR__ . '/../../uploads/pages/' . $page['slug'];

        // Recursively remove folder if exists
        if (is_dir($folder)) {
            $this->rrmdir($folder);
        }

        // 同时清理该页面的备份目录，避免残留
        $backupFolder = __DIR__ . '/../../uploads/backups/' . $page['slug'];
        if (is_dir($backupFolder)) {
            $this->rrmdir($backupFolder);
        }

        $deleted = $this->pageModel->delete($id);
        if ($deleted) {
            return $this->redirect('/dashboard?deleted=1');
        }
        return $this->redirect('/dashboard?error=delete_failed');
    }

    public function togglePublic($id) {
        if (!isset($_SESSION['user_id'])) {
            return $this->redirect('/login');
        }

        // CSRF 校验，防止未授权修改发布状态
        if (!$this->verifyCsrf()) {
            return $this->redirect('/dashboard?error=csrf');
        }

        $page = $this->pageModel->findById($id);
        if (!$page) {
            return $this->redirect('/dashboard?error=not_found');
        }

        // Only owner can toggle
        if ($page['user_id'] != $_SESSION['user_id']) {
            return $this->redirect('/dashboard?error=permission');
        }

        $newStatus = $page['is_public'] ? 0 : 1;
        $updated = $this->pageModel->update($id, ['is_public' => $newStatus]);
        if ($updated) {
            return $this->redirect('/dashboard?status=toggled');
        }
        return $this->redirect('/dashboard?error=toggle_failed');
    }

    private function rrmdir($dir) {
        if (!is_dir($dir)) return;
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->rrmdir($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }

    // ==================== 文件编辑与管理 ====================

    /**
     * 权限校验：仅页面所有者或管理员可管理
     */
    private function canManage($page) {
        if (!isset($_SESSION['user_id'])) return false;
        if ((int)$page['user_id'] === (int)$_SESSION['user_id']) return true;
        if (($_SESSION['role'] ?? '') === 'admin') return true;
        return false;
    }

    /**
     * CSRF 校验（用于所有写操作）
     */
    private function verifyCsrf() {
        return ($_POST['csrf_token'] ?? '') !== '' && ($_POST['csrf_token'] ?? '') === ($_SESSION['csrf_token'] ?? '');
    }

    /**
     * 原子写入文件：先写临时文件再替换，确保更新过程中访问不中断
     */
    private function atomicWrite($filePath, $content) {
        $dir = dirname($filePath);
        $tmp = $dir . '/.tmp_' . bin2hex(random_bytes(6));
        if (@file_put_contents($tmp, $content) === false) return false;
        @chmod($tmp, 0644);
        // Windows 下 rename 不会覆盖已存在文件，需先移除旧文件
        if (PHP_OS_FAMILY === 'Windows' && file_exists($filePath)) {
            @unlink($filePath);
        }
        if (!@rename($tmp, $filePath)) {
            if (!@copy($tmp, $filePath)) {
                @unlink($tmp);
                return false;
            }
            @unlink($tmp);
        }
        return true;
    }

    /**
     * 备份当前 index.html 到 uploads/backups/{slug}/，返回相对项目根目录的路径
     */
    private function backupFile($slug, $sourcePath, $versionNumber) {
        $backupDir = dirname(__DIR__, 2) . '/uploads/backups/' . $slug;
        if (!is_dir($backupDir) && !mkdir($backupDir, 0777, true)) return null;
        $backupName = $versionNumber . '_' . date('Ymd_His') . '.html';
        $backupPath = $backupDir . '/' . $backupName;
        if (file_exists($sourcePath) && @copy($sourcePath, $backupPath)) {
            return 'uploads/backups/' . $slug . '/' . $backupName;
        }
        return null;
    }

    /**
     * 递归复制目录内容（用于 zip 替换时同步资源文件）
     */
    private function copyDirContents($srcDir, $destDir) {
        if (!is_dir($srcDir)) return;
        $items = scandir($srcDir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $src = $srcDir . DIRECTORY_SEPARATOR . $item;
            $dest = $destDir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($src)) {
                if (!is_dir($dest)) mkdir($dest, 0777, true);
                $this->copyDirContents($src, $dest);
            } else {
                @copy($src, $dest);
            }
        }
    }

    /**
     * 重新上传文件替换现有文件（访问地址不变，旧版本自动备份）
     */
    public function replace($id) {
        if (!isset($_SESSION['user_id'])) {
            return $this->redirect('/login');
        }
        \App\Models\Page::ensureSchema();

        if (!$this->verifyCsrf()) {
            return $this->redirect('/dashboard?error=csrf');
        }

        $page = $this->pageModel->findById($id);
        if (!$page) {
            return $this->redirect('/dashboard?error=not_found');
        }
        if (!$this->canManage($page)) {
            return $this->redirect('/dashboard?error=permission');
        }

        $file = $_FILES['webfile'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return $this->redirect('/dashboard?error=upload_failed');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['html', 'zip'], true)) {
            return $this->redirect('/dashboard?error=invalid_format');
        }

        $dirName = $page['folder_path'] ?? $page['slug'];
        $pageDir = __DIR__ . '/../../uploads/pages/' . $dirName;
        $filePath = $pageDir . '/index.html';

        // 先备份当前版本
        $versionNumber = $this->pageModel->getNextVersionNumber($id);
        $backupPath = $this->backupFile($page['slug'], $filePath, $versionNumber);

        $content = '';
        if ($ext === 'html') {
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                return $this->redirect('/dashboard?error=upload_failed');
            }
            $content = (string)file_get_contents($filePath);
        } else {
            // zip：先解压到临时目录，再原子更新 index.html，最后同步其余资源
            $tmpDir = dirname(__DIR__, 2) . '/uploads/tmp/' . bin2hex(random_bytes(6));
            if (!is_dir($tmpDir)) mkdir($tmpDir, 0777, true);

            $zip = new \ZipArchive;
            if ($zip->open($file['tmp_name']) !== TRUE) {
                $this->rrmdir($tmpDir);
                return $this->redirect('/dashboard?error=zip_failed');
            }
            $zip->extractTo($tmpDir);
            $zip->close();

            $newIndex = $tmpDir . '/index.html';
            if (!file_exists($newIndex)) {
                $this->rrmdir($tmpDir);
                return $this->redirect('/dashboard?error=index_missing');
            }
            $content = (string)file_get_contents($newIndex);
            if (!$this->atomicWrite($filePath, $content)) {
                $this->rrmdir($tmpDir);
                return $this->redirect('/dashboard?error=save_failed');
            }
            $this->copyDirContents($tmpDir, $pageDir);
            $this->rrmdir($tmpDir);
        }

        $this->pageModel->createVersion($id, $versionNumber, $backupPath, '文件替换上传', $_SESSION['user_id']);

        // 与初次上传保持一致：对新内容执行 AI 审核
        $aiReviewer = new \App\Core\AIReviewer();
        $result = $aiReviewer->reviewContent($content, $id, 'web');
        $score = $result['score'] ?? null;
        if ($score !== null) {
            $this->pageModel->updateAIScore($id, $score);
            $threshold = floatval(\App\Core\Config::get('ai.threshold', '7.0'));
            if ($score >= $threshold) {
                $this->pageModel->banPage($id);
                try {
                    $adminEmail = \App\Core\Config::get('admin.email', 'admin@yourdomain.com');
                    $subject = '网页内容违规通知';
                    $message = "用户替换上传的网页 (ID: $id, Slug: {$page['slug']}) AI 评分: $score，已达到违规阈值。页面已被自动封禁。";
                    \App\Core\Mailer::sendNotification($adminEmail, $subject, $message);
                } catch (\Exception $e) {
                    // 忽略邮件发送失败
                }
                return $this->redirect('/dashboard?error=content_banned');
            }
        }

        return $this->redirect('/dashboard?success=replaced');
    }
}
