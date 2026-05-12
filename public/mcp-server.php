<?php

require_once __DIR__ . '/../app/Core/EnvLoader.php';
EnvLoader::load();

spl_autoload_register(function ($class) {
    $root = dirname(__DIR__);
    $path = str_replace('App\\', 'app\\', $class);
    $file = $root . '/' . str_replace('\\', '/', $path) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Core/Database.php';

use App\Models\UserApiSettings;

class McpApiServer {
    private $apiSettings;
    private $userId;
    private $port;

    public function __construct($port, $apiKey) {
        $this->port = $port;
        $this->apiSettings = new UserApiSettings();
        
        $settings = $this->apiSettings->findByApiKey($apiKey);
        
        if (!$settings) {
            $this->unauthorized('Invalid API key');
        }
        
        if (!$settings['api_enabled']) {
            $this->unauthorized('API is disabled');
        }
        
        $this->userId = $settings['user_id'];
    }

    private function unauthorized($message) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => $message]);
        exit;
    }

    public function handle() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Support both GET (for SSE) and POST (for JSON-RPC)
        if ($method === 'GET') {
            // Extract API key from query string or header
            $apiKey = $_GET['apiKey'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';
            // Remove 'Bearer ' prefix if present
            $apiKey = preg_replace('/^Bearer\s+/i', '', $apiKey);
            
            if (empty($apiKey)) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'API key required']);
                return;
            }
            
            $this->handleSSE($apiKey);
            return;
        }
        
        if ($method !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid JSON']);
            return;
        }

        // Check if client accepts SSE
        $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
        if (strpos($acceptHeader, 'text/event-stream') !== false) {
            // Extract API key for SSE
            $apiKey = $_GET['apiKey'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';
            $apiKey = preg_replace('/^Bearer\s+/i', '', $apiKey);
            
            if (empty($apiKey)) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'API key required']);
                return;
            }
            
            $this->handleSSE($apiKey, $data);
            return;
        }

        header('Content-Type: application/json');
        
        $result = $this->processRequest($data);
        echo json_encode($result);
    }

    private function handleSSE($apiKey, $data = null) {
        // Verify API key
        $settings = $this->apiSettings->findByApiKey($apiKey);
        
        if (!$settings) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid API key']);
            return;
        }
        
        if (!$settings['api_enabled']) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'API is disabled']);
            return;
        }
        
        $this->userId = $settings['user_id'];
        
        // Disable buffering and set SSE headers
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', 1);
        }
        @ini_set('zlib.output_compression', 0);
        @ini_set('output_buffering', 'Off');
        @ini_set('output_handler', '');
        
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // Disable nginx buffering
        
        // Send initialization event
        $this->sendSSEvent('endpoint', json_encode(['uri' => '/api/mcp']));
        
        // If data is provided, process it
        if ($data !== null) {
            $response = $this->processRequest($data);
            $this->sendSSEvent('message', json_encode($response));
        }
        
        // Flush the output
        if (ob_get_level()) {
            ob_end_flush();
        }
        flush();
        
        // Keep connection alive
        set_time_limit(0);
        while (connection_status() === CONNECTION_NORMAL) {
            echo ": ping\n";
            flush();
            sleep(30);
        }
    }

    private function sendSSEvent($event, $data) {
        echo "event: $event\n";
        echo "data: $data\n\n";
        flush();
    }

    private function processRequest($data) {
        $jsonrpc = $data['jsonrpc'] ?? '2.0';
        $method = $data['method'] ?? '';
        $params = $data['params'] ?? [];
        $id = $data['id'] ?? null;

        $response = [
            'jsonrpc' => $jsonrpc,
            'id' => $id
        ];

        try {
            switch ($method) {
                case 'initialize':
                    $response['result'] = $this->initialize($params);
                    break;
                    
                case 'tools/list':
                    $response['result'] = $this->listTools();
                    break;
                    
                case 'tools/call':
                    $response['result'] = $this->callTool($params);
                    break;
                    
                case 'resources/list':
                    $response['result'] = $this->listResources();
                    break;
                    
                case 'resources/read':
                    $response['result'] = $this->readResource($params);
                    break;
                    
                case 'prompts/list':
                    $response['result'] = $this->listPrompts();
                    break;
                    
                case 'ping':
                    $response['result'] = ['pong' => true];
                    break;
                    
                default:
                    $response['error'] = [
                        'code' => -32601,
                        'message' => 'Method not found'
                    ];
            }
        } catch (\Exception $e) {
            $response['error'] = [
                'code' => -32603,
                'message' => 'Internal error: ' . $e->getMessage()
            ];
        }

        return $response;
    }

    public function runCli($inputData) {
        $data = is_string($inputData) ? json_decode($inputData, true) : $inputData;
        return $this->processRequest($data);
    }

    private function initialize($params) {
        return [
            'protocolVersion' => '2024-11-05',
            'capabilities' => [
                'tools' => new \stdClass(),
                'resources' => new \stdClass(),
                'prompts' => new \stdClass()
            ],
            'serverInfo' => [
                'name' => 'easknow-mcp-server',
                'version' => '1.0.0'
            ]
        ];
    }

    private function listTools() {
        return [
            'tools' => [
                [
                    'name' => 'upload_page',
                    'description' => '上传并托管 HTML 网页项目。支持：1) 直接 HTML 字符串 2) HTML 文件对象 3) 完整网站（含 CSS/JS/图片）4) ZIP 压缩包。上传后自动生成访问链接，并进行 AI 安全审核。',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'title' => [
                                'type' => 'string',
                                'description' => '【必填】网页项目标题，例如："我的个人主页"'
                            ],
                            'content' => [
                                'type' => 'string',
                                'description' => '【方式 1】HTML 内容字符串。适用于简单单页，例如："<html><body><h1>Hello</h1></body></html>"'
                            ],
                            'htmlFile' => [
                                'type' => 'object',
                                'description' => '【方式 2】HTML 文件对象。三选一：content(原始内容)、base64(编码内容)、url(文件地址)',
                                'properties' => [
                                    'content' => ['type' => 'string', 'description' => 'HTML 文件原始内容'],
                                    'base64' => ['type' => 'string', 'description' => 'HTML 文件的 Base64 编码'],
                                    'url' => ['type' => 'string', 'description' => 'HTML 文件的下载 URL']
                                ]
                            ],
                            'fileUrl' => [
                                'type' => 'string',
                                'description' => '【方式 3】HTML 或 ZIP 文件的 URL 地址，例如："https://example.com/site.zip"'
                            ],
                            'fileBase64' => [
                                'type' => 'string',
                                'description' => '【方式 4】HTML 或 ZIP 文件的 Base64 编码内容，需配合 fileName 使用'
                            ],
                            'fileName' => [
                                'type' => 'string',
                                'description' => '文件名（包含扩展名），例如："index.html" 或 "website.zip"'
                            ],
                            'resources' => [
                                'type' => 'array',
                                'description' => '【可选】配套资源文件数组（CSS、JS、图片等）。每个资源包含：path(相对路径)、content(内容) 或 base64(编码)',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'path' => ['type' => 'string', 'description' => '文件相对路径，如："css/style.css"、"images/logo.png"'],
                                        'content' => ['type' => 'string', 'description' => '文件内容'],
                                        'base64' => ['type' => 'string', 'description' => '文件的 Base64 编码（适用于图片等二进制文件）']
                                    ]
                                ]
                            ]
                        ],
                        'required' => ['title'],
                        'examples' => [
                            [
                                'title' => '简单单页',
                                'content' => '<!DOCTYPE html><html><head><title>Test</title></head><body><h1>Hello World</h1></body></html>'
                            ],
                            [
                                'title' => '完整网站',
                                'htmlFile' => ['content' => '<!DOCTYPE html>...'],
                                'resources' => [
                                    ['path' => 'css/style.css', 'content' => 'body { margin: 0; }'],
                                    ['path' => 'js/app.js', 'content' => 'console.log("loaded")'],
                                    ['path' => 'images/logo.png', 'base64' => 'iVBORw0KGgoAAAANSUhEUgAA...']
                                ]
                            ],
                            [
                                'title' => 'ZIP 项目',
                                'fileBase64' => 'UEsDBBQAAAA...',
                                'fileName' => 'website.zip'
                            ]
                        ]
                    ]
                ],
                [
                    'name' => 'get_user_pages',
                    'description' => '获取当前用户的所有网页项目',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'limit' => [
                                'type' => 'integer',
                                'description' => '返回结果数量限制'
                            ]
                        ]
                    ]
                ],
                [
                    'name' => 'get_page_details',
                    'description' => '获取指定网页的详细信息',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'slug' => [
                                'type' => 'string',
                                'description' => '网页 slug 标识'
                            ]
                        ],
                        'required' => ['slug']
                    ]
                ],
                [
                    'name' => 'get_user_stats',
                    'description' => '获取当前用户的统计数据',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => new \stdClass()
                    ]
                ],
                [
                    'name' => 'search_pages',
                    'description' => '搜索用户的网页项目',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => [
                                'type' => 'string',
                                'description' => '搜索关键词'
                            ]
                        ],
                        'required' => ['query']
                    ]
                ]
            ]
        ];
    }

    private function callTool($params) {
        $name = $params['name'] ?? '';
        $args = $params['arguments'] ?? [];

        switch ($name) {
            case 'upload_page':
                return $this->uploadPage($args);
                
            case 'get_user_pages':
                return $this->getUserPages($args);
                
            case 'get_page_details':
                return $this->getPageDetails($args);
                
            case 'get_user_stats':
                return $this->getUserStats();
                
            case 'search_pages':
                return $this->searchPages($args);
                
            default:
                throw new \Exception('Unknown tool: ' . $name);
        }
    }

    private function uploadPage($args) {
        if (!isset($args['title'])) {
            throw new \Exception('Title is required');
        }

        $title = $args['title'];
        $content = $args['content'] ?? null;
        $fileUrl = $args['fileUrl'] ?? null;
        $fileBase64 = $args['fileBase64'] ?? null;
        $fileName = $args['fileName'] ?? null;
        $htmlFile = $args['htmlFile'] ?? null; // 新增：直接 HTML 文件对象

        // 确定文件类型和内容
        $fileContent = null;
        $ext = null;
        
        if ($htmlFile && is_array($htmlFile)) {
            // 处理 HTML 文件对象（支持 MCP 文件上传）
            if (isset($htmlFile['content'])) {
                $fileContent = $htmlFile['content'];
                $ext = 'html';
            } elseif (isset($htmlFile['base64'])) {
                $fileContent = base64_decode($htmlFile['base64']);
                $ext = 'html';
            } elseif (isset($htmlFile['url'])) {
                $fileContent = file_get_contents($htmlFile['url']);
                $ext = 'html';
            }
        } elseif ($content) {
            // 直接 HTML 内容
            $fileContent = $content;
            $ext = 'html';
        } elseif ($fileBase64 && $fileName) {
            // Base64 编码的文件
            $fileContent = base64_decode($fileBase64);
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        } elseif ($fileUrl) {
            // 从 URL 下载文件
            $fileContent = file_get_contents($fileUrl);
            $ext = pathinfo(parse_url($fileUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'html';
        }

        if (!$fileContent) {
            throw new \Exception('需要提供 content、htmlFile、fileUrl 或 fileBase64 其中之一');
        }

        // 验证文件类型
        if (!in_array(strtolower($ext), ['html', 'htm', 'zip'])) {
            throw new \Exception('只支持 HTML 或 ZIP 格式的文件');
        }

        // 创建上传目录
        $slug = bin2hex(random_bytes(4));
        $uploadDir = dirname(__DIR__) . '/uploads/pages/' . $slug;
        
        if (!mkdir($uploadDir, 0777, true)) {
            $error = error_get_last();
            throw new \Exception('创建上传目录失败：' . ($error['message'] ?? '未知错误') . '，路径：' . $uploadDir);
        }

        // 保存文件
        if (in_array(strtolower($ext), ['html', 'htm'])) {
            // HTML 文件处理
            file_put_contents($uploadDir . '/index.html', $fileContent);
            
            // 如果是完整的 HTML 项目，检查是否有配套资源
            if (isset($args['resources']) && is_array($args['resources'])) {
                $this->saveResources($uploadDir, $args['resources']);
            }
        } elseif ($ext === 'zip') {
            // ZIP 文件处理
            file_put_contents($uploadDir . '/archive.zip', $fileContent);
            // 解压 ZIP
            $zip = new \ZipArchive;
            if ($zip->open($uploadDir . '/archive.zip') === TRUE) {
                $zip->extractTo($uploadDir);
                $zip->close();
                @unlink($uploadDir . '/archive.zip');
            }
        }

        // 保存到数据库
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO pages (user_id, title, slug, folder_path, custom_domain, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$this->userId, $title, $slug, $slug, null]);
        $pageId = $db->lastInsertId();

        // AI 审核
        require_once __DIR__ . '/../app/Core/AIReviewer.php';
        $aiReviewer = new \App\Core\AIReviewer();
        $htmlContent = file_get_contents($uploadDir . '/index.html');
        $reviewResult = $aiReviewer->reviewContent($htmlContent, $pageId, 'mcp');
        $score = $reviewResult['score'] ?? null;

        if ($score !== null) {
            $stmt = $db->prepare("UPDATE pages SET ai_score = ? WHERE id = ?");
            $stmt->execute([$score, $pageId]);

            $threshold = floatval(\App\Core\Config::get('ai.threshold', '7.0'));
            if ($score >= $threshold) {
                // 发送邮件通知（添加异常处理）
                try {
                    $adminEmail = \App\Core\Config::get('admin.email', 'admin@yourdomain.com');
                    $subject = '网页内容违规通知';
                    $message = "用户上传的网页 (ID: $pageId, Slug: $slug) AI 评分：$score，已达到违规阈值。页面已被自动封禁。";
                    \App\Core\Mailer::sendNotification($adminEmail, $subject, $message);
                } catch (\Exception $e) {
                    // 邮件发送失败不影响封禁结果
                }

                // 封禁页面 - 修正字段名为 banned
                $stmt = $db->prepare("UPDATE pages SET banned = 1 WHERE id = ?");
                $stmt->execute([$pageId]);

                return [
                    'success' => false,
                    'message' => '内容未通过 AI 审核，页面已被封禁',
                    'aiScore' => $score
                ];
            }
        }

        $baseUrl = rtrim(EnvLoader::get('APP_URL', 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')), '/');
        
        return [
            'success' => true,
            'message' => '网页上传成功',
            'page' => [
                'id' => $pageId,
                'title' => $title,
                'slug' => $slug,
                'url' => $baseUrl . '/p/' . $slug,
                'aiScore' => $score
            ]
        ];
    }

    /**
     * 保存 HTML 页面的配套资源文件（CSS、JS、图片等）
     */
    private function saveResources($uploadDir, $resources) {
        foreach ($resources as $resource) {
            if (!isset($resource['path']) || !isset($resource['content'])) {
                continue;
            }
            
            $resourcePath = $uploadDir . '/' . ltrim($resource['path'], '/');
            $resourceDir = dirname($resourcePath);
            
            // 创建目录
            if (!is_dir($resourceDir)) {
                mkdir($resourceDir, 0777, true);
            }
            
            // 保存内容
            $content = $resource['content'];
            if (isset($resource['base64'])) {
                $content = base64_decode($resource['base64']);
            }
            
            file_put_contents($resourcePath, $content);
        }
    }

    private function getUserPages($args) {
        $limit = $args['limit'] ?? 10;
        
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("
            SELECT id, title, slug, views, is_public, created_at 
            FROM pages 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$this->userId, $limit]);
        $pages = $stmt->fetchAll();

        return [
            'content' => [
                [
                    'type' => 'text',
                    'text' => json_encode($pages, JSON_UNESCAPED_UNICODE)
                ]
            ]
        ];
    }

    private function getPageDetails($args) {
        $slug = $args['slug'] ?? '';
        
        if (empty($slug)) {
            throw new \Exception('Slug is required');
        }
        
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("
            SELECT id, title, slug, views, is_public, banned, ai_score, created_at 
            FROM pages 
            WHERE user_id = ? AND slug = ?
        ");
        $stmt->execute([$this->userId, $slug]);
        $page = $stmt->fetch();

        if (!$page) {
            throw new \Exception('Page not found');
        }

        return [
            'content' => [
                [
                    'type' => 'text',
                    'text' => json_encode($page, JSON_UNESCAPED_UNICODE)
                ]
            ]
        ];
    }

    private function getUserStats() {
        $db = \App\Core\Database::getInstance();
        
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM pages WHERE user_id = ?");
        $stmt->execute([$this->userId]);
        $pagesCount = $stmt->fetch()['total'] ?? 0;
        
        $stmt = $db->prepare("SELECT SUM(views) as total FROM pages WHERE user_id = ?");
        $stmt->execute([$this->userId]);
        $viewsCount = $stmt->fetch()['total'] ?? 0;
        
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM pages WHERE user_id = ? AND is_public = 1");
        $stmt->execute([$this->userId]);
        $publicCount = $stmt->fetch()['total'] ?? 0;

        $stats = [
            'total_pages' => (int)$pagesCount,
            'total_views' => (int)$viewsCount,
            'public_pages' => (int)$publicCount,
            'private_pages' => (int)($pagesCount - $publicCount)
        ];

        return [
            'content' => [
                [
                    'type' => 'text',
                    'text' => json_encode($stats, JSON_UNESCAPED_UNICODE)
                ]
            ]
        ];
    }

    private function searchPages($args) {
        $query = $args['query'] ?? '';
        
        if (empty($query)) {
            throw new \Exception('Query is required');
        }
        
        $db = \App\Core\Database::getInstance();
        $searchTerm = '%' . $query . '%';
        $stmt = $db->prepare("
            SELECT id, title, slug, views, is_public, created_at 
            FROM pages 
            WHERE user_id = ? AND (title LIKE ? OR slug LIKE ?)
            ORDER BY created_at DESC 
            LIMIT 20
        ");
        $stmt->execute([$this->userId, $searchTerm, $searchTerm]);
        $pages = $stmt->fetchAll();

        return [
            'content' => [
                [
                    'type' => 'text',
                    'text' => json_encode($pages, JSON_UNESCAPED_UNICODE)
                ]
            ]
        ];
    }

    private function listResources() {
        return [
            'resources' => [
                [
                    'uri' => 'easknow://user/info',
                    'name' => 'user_info',
                    'description' => '当前用户信息',
                    'mimeType' => 'application/json'
                ],
                [
                    'uri' => 'easknow://user/stats',
                    'name' => 'user_stats',
                    'description' => '用户统计数据',
                    'mimeType' => 'application/json'
                ]
            ]
        ];
    }

    private function readResource($params) {
        $uri = $params['uri'] ?? '';
        
        switch ($uri) {
            case 'easknow://user/info':
                return $this->getUserInfo();
                
            case 'easknow://user/stats':
                return $this->getUserStats();
                
            default:
                throw new \Exception('Unknown resource: ' . $uri);
        }
    }

    private function getUserInfo() {
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("SELECT username, email, role, created_at FROM users WHERE id = ?");
        $stmt->execute([$this->userId]);
        $user = $stmt->fetch();

        return [
            'contents' => [
                [
                    'uri' => 'easknow://user/info',
                    'mimeType' => 'application/json',
                    'text' => json_encode($user, JSON_UNESCAPED_UNICODE)
                ]
            ]
        ];
    }

    private function listPrompts() {
        return [
            'prompts' => [
                [
                    'name' => 'analyze_pages',
                    'description' => '分析您的网页项目统计',
                    'arguments' => []
                ]
            ]
        ];
    }
}

// HTTP mode - called from routes
if (php_sapi_name() !== 'cli') {
    // Extract API key from query string or header
    $apiKey = $_GET['apiKey'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $apiKey = preg_replace('/^Bearer\s+/i', '', $apiKey);
    
    if (empty($apiKey)) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'API key required']);
        exit;
    }
    
    $server = new McpApiServer(null, $apiKey);
    $server->handle();
}

// CLI mode
if (php_sapi_name() === 'cli') {
    $port = $argv[1] ?? null;
    $apiKey = $argv[2] ?? null;
    
    if (!$port || !$apiKey) {
        echo "Usage: php mcp-server.php <port> <api_key>\n";
        exit(1);
    }
    
    $server = new McpApiServer($port, $apiKey);
    
    echo "MCP Server starting on port $port...\n";
    
    http_response_code(200);
    header('Content-Type: application/json');
    
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    $result = $server->runCli($data);
    echo json_encode($result);
}
