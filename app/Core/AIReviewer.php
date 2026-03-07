<?php

namespace App\Core;

class AIReviewer {
    private $apiKey;
    private $apiUrl;
    private $model;
    private $enabled;
    private $logFile;

    public function __construct() {
        $this->enabled = Config::get('ai.enabled', false);
        $this->apiKey = Config::get('ai.api_key', '');
        $this->apiUrl = Config::get('ai.api_url', '');
        $this->model = Config::get('ai.model', '');
        
        // 初始化日志文件路径
        $this->logFile = dirname(__DIR__, 2) . '/logs/ai-review.log.md';
        if (!is_dir(dirname($this->logFile))) {
            mkdir(dirname($this->logFile), 0755, true);
        }
    }

    /**
     * 记录 AI 审核日志到 MD 文件
     */
    private function logReview($pageId, $content, $result, $source = 'web') {
        $timestamp = date('Y-m-d H:i:s');
        $date = date('Y-m-d');
        
        $logEntry = "## [$timestamp] 审核记录 - $date\n\n";
        $logEntry .= "**来源**: $source\n";
        $logEntry .= "**页面 ID**: " . ($pageId ?? 'N/A') . "\n\n";
        
        $logEntry .= "### 请求信息\n";
        $logEntry .= "- **API URL**: {$this->apiUrl}\n";
        $logEntry .= "- **Model**: {$this->model}\n";
        $logEntry .= "- **内容长度**: " . strlen($content) . " 字符\n\n";
        
        if (isset($result['score'])) {
            $logEntry .= "### 审核结果\n";
            $logEntry .= "- **评分**: {$result['score']}/10\n";
            $logEntry .= "- **状态**: " . ($result['score'] >= 7 ? '❌ 违规' : '✅ 通过') . "\n\n";
        }
        
        if (isset($result['error'])) {
            $logEntry .= "### 错误信息\n";
            $logEntry .= "- **错误**: {$result['error']}\n\n";
        }
        
        // 处理请求数据 - 如果是字符串则解码
        if (isset($result['request_data'])) {
            $logEntry .= "### 请求数据\n";
            $requestData = $result['request_data'];
            // 如果是字符串，尝试解码
            if (is_string($requestData)) {
                $decoded = json_decode($requestData, true);
                if ($decoded) {
                    $requestData = $decoded;
                }
            }
            $logEntry .= "```json\n" . json_encode($requestData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n```\n\n";
        }
        
        // 处理 API 响应
        if (isset($result['api_result'])) {
            $logEntry .= "### API 响应\n";
            $logEntry .= "```json\n" . json_encode($result['api_result'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n```\n\n";
        }
        
        if (isset($result['response'])) {
            $logEntry .= "### 原始响应\n";
            $logEntry .= "```\n" . substr($result['response'], 0, 1000) . "\n```\n\n";
        }
        
        $logEntry .= "---\n\n";
        
        // 追加到日志文件
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    public function reviewContent($content, $pageId = null, $source = 'web') {

        // 添加请求间隔，避免 429
        sleep(1);

        if (!$this->enabled) {
            $result = ['score' => null, 'error' => 'AI 功能未启用'];
            $this->logReview($pageId, $content, $result, $source);
            return $result;
        }

        if (empty($this->apiKey)) {
            $result = ['score' => null, 'error' => 'API 密钥为空'];
            $this->logReview($pageId, $content, $result, $source);
            return $result;
        }


        // 添加重试机制 - 只在网络错误时重试，避免 API 限流
        $maxRetries = 1; // 减少重试次数，避免 429
        $retryDelay = 5; // 秒，增加延迟
        $lastError = null;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {

            $result = $this->makeAPIRequest($content);
            if ($result !== null && isset($result['score'])) {
                $this->logReview($pageId, $content, $result, $source);
                return $result;
            }

            // 如果是 API 限流 (429) 或客户端错误，不重试
            if ($result !== null && isset($result['error'])) {
                $lastError = $result['error'];
                if (strpos($result['error'], '429') !== false || strpos($result['error'], 'Too Many Requests') !== false) {
                    break;
                }
                if (strpos($result['error'], '4') === 0) { // 4xx 客户端错误，不重试
                    break;
                }
            } else {
                $lastError = '未知错误';
            }

            if ($attempt < $maxRetries) {
                sleep($retryDelay);
                $retryDelay *= 2; // 指数退避
            }
        }

        $result = ['score' => null, 'error' => '审核失败', 'last_error' => $lastError, 'request_data' => null];
        $this->logReview($pageId, $content, $result, $source);
        return $result;
    }

    private function makeAPIRequest($content) {
        // 移除内容长度限制，让API自行处理
        $prompt = "请分析这个网页内容是否存在安全违规行为。从0-10进行评分（0=安全，10=严重违规）。只返回数字评分。\n\n内容：\n" . $content . "\n\n评分：";

        $data = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => '你是一个严格的内容安全审核AI，专门检测违反中国法律法规的内容。你必须严格遵守中国法律法规，包括《网络安全法》、《治安管理处罚法》和《刑法》。对任何潜在违规行为零容忍。你的审核标准必须严格，不得宽松。

评分标准（0-10分）：
0-1分：完全安全，无任何违规内容
2-3分：轻微问题，如少量不良用词或不恰当表达
4-5分：中等违规，如涉及敏感话题但不严重，或轻微色情暗示
6-7分：严重违规，如明显色情内容、赌博宣传、政治敏感信息
8-9分：非常严重违规，如暴力恐怖内容、诈骗信息、恶意代码
10分：极端严重违规，如涉及国家安全、儿童色情、恐怖主义等

重点关注的违规类型：
• 政治敏感内容：涉及国家领导人、民族宗教、领土主权等
• 色情低俗信息：色情图片、视频、文字描述
• 赌博诈骗：赌博网站、诈骗信息、非法集资
• 暴力恐怖：暴力描写、恐怖主义宣传
• 侵犯版权：盗版内容、侵权信息
• 恶意代码：病毒、木马、钓鱼网站
• 个人信息泄露：泄露他人隐私信息
• 其他违法内容：毒品、枪支、非法交易等

请基于内容严重程度给出相应分数，只返回数字。'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 1.0,
            'stream' => false
        ];


        // 验证JSON编码
        $jsonData = json_encode($data);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $errorMsg = "JSON编码错误: " . json_last_error_msg();
            return ['score' => null, 'error' => $errorMsg];
        }

        // 使用file_get_contents发送HTTP请求
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->apiKey
                ],
                'content' => $jsonData,
                'timeout' => 30,
                'ignore_errors' => true
            ]
        ]);


        $startTime = microtime(true);
        $response = file_get_contents($this->apiUrl, false, $context);
        $endTime = microtime(true);
        $totalTime = round($endTime - $startTime, 2);

        // 获取HTTP状态码
        $httpCode = 0;
        if (isset($http_response_header)) {
            foreach ($http_response_header as $header) {
                if (preg_match('/^HTTP\/\d+\.\d+\s+(\d+)/', $header, $matches)) {
                    $httpCode = (int)$matches[1];
                    break;
                }
            }
        }


        if ($response === false) {
            $errorMsg = "HTTP请求失败";
            return ['score' => null, 'error' => $errorMsg, 'request_data' => $jsonData];
        }

        if ($httpCode !== 200) {
            $errorMsg = "API调用失败，HTTP状态码: " . $httpCode;
            // 尝试解析错误响应
            $errorData = json_decode($response, true);
            if ($errorData && isset($errorData['error'])) {
                $errorMsg .= " - " . ($errorData['error']['message'] ?? '未知错误');
            }
            // 如果是429限流，特殊处理
            if ($httpCode == 429) {
                $errorMsg = "API请求过于频繁 (429 Too Many Requests)";
            } elseif ($httpCode == 400) {
                $errorMsg = "参数错误 (400 Bad Request)";
            }
            return ['score' => null, 'error' => $errorMsg, 'response' => substr($response, 0, 500), 'http_code' => $httpCode, 'request_data' => $jsonData];
        }


        $result = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $errorMsg = "JSON解析错误: " . json_last_error_msg();
            return ['score' => null, 'error' => $errorMsg, 'raw_response' => $response, 'request_data' => $jsonData];
        }


        if (!isset($result['choices']) || !is_array($result['choices']) || empty($result['choices'])) {
            $errorMsg = "响应中没有choices字段或为空";
            return ['score' => null, 'error' => $errorMsg, 'api_result' => $result, 'request_data' => $jsonData];
        }

        // 改进评分提取：尝试多种方法
        $score = '';

        // 方法1：从content中提取（优先级最高）
        $contentScore = trim($result['choices'][0]['message']['content'] ?? '');

        // 清理content中的评分 - 只保留数字
        $contentScore = preg_replace('/[^\d.]/', '', $contentScore);
        if (is_numeric($contentScore) && $contentScore !== '') {
            $score = $contentScore;
        }

        // 方法2：如果content没有数字，尝试从reasoning_content提取
        if (empty($score)) {
            $reasoningContent = $result['choices'][0]['message']['reasoning_content'] ?? '';

            if (preg_match('/(\d+(?:\.\d+)?)/', $reasoningContent, $matches)) {
                $score = $matches[1];
            }
        }

        // 方法3：从整个响应JSON中搜索数字
        if (empty($score)) {
            $fullResponse = json_encode($result, JSON_UNESCAPED_UNICODE);
            if (preg_match_all('/(\d+(?:\.\d+)?)/', $fullResponse, $matches)) {
                // 找到所有数字，取最后一个（通常是最终评分）
                $allNumbers = $matches[1];
                $score = end($allNumbers);
            }
        }


        if (is_numeric($score)) {
            $scoreFloat = floatval($score);
            return [
                'score' => $scoreFloat,
                'ai_response' => $response,
                'parsed_result' => $result,
                'request_data' => $jsonData
            ];
        }

        return [
            'score' => null,
            'error' => '评分不是有效数字',
            'ai_response' => $response,
            'parsed_result' => $result,
            'request_data' => $jsonData
        ];
    }
    
}
