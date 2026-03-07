<!DOCTYPE html>
<html lang="zh-CN" class="h-full bg-gray-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统安装 - 完成</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { 
            font-family: 'Microsoft YaHei', 'PingFang SC', 'Hiragino Sans GB', sans-serif; 
            cursor: default;
        }
        .glass { 
            background: rgba(15, 15, 20, 0.7); 
            backdrop-filter: blur(16px); 
            border: 1px solid rgba(255, 255, 255, 0.08); 
        }
        .gradient-text { 
            background: linear-gradient(135deg, #fff 0%, #a855f7 50%, #6366f1 100%); 
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
        }
        .glow-card:hover { 
            box-shadow: 0 0 40px rgba(168, 85, 247, 0.15); 
            border-color: rgba(168, 85, 247, 0.3); 
        }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
            40% {transform: translateY(-20px);}
            60% {transform: translateY(-10px);}
        }
        .animate-bounce {
            animation: bounce 1s ease-in-out;
        }
        
        @keyframes scaleUp {
            from {transform: scale(0);}
            to {transform: scale(1);}
        }
        .animate-scale-up {
            animation: scaleUp 0.5s ease-out;
        }
    </style>
</head>
<body class="h-full text-gray-200 selection:bg-purple-500/30">
    <div class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="max-w-2xl w-full glass p-8 rounded-2xl glow-card transition-all duration-300 text-center">
            <!-- Header -->
            <div class="mb-8">
                <div class="w-24 h-24 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-6 animate-scale-up" style="font-size: 60px; color: white;">
                    ✓
                </div>
                <h1 class="text-4xl font-bold gradient-text mb-2 animate-bounce">🎉</h1>
                <h2 class="text-2xl font-bold text-white mb-2">安装完成！</h2>
                <p class="text-gray-400">系统已成功部署，可以开始使用了</p>
            </div>
            
            <!-- Info Box -->
            <div class="bg-white/5 border border-white/10 rounded-lg p-6 mb-6 text-left">
                <div class="space-y-3">
                    <div class="flex justify-between py-2 border-b border-white/5">
                        <span class="text-gray-400">系统版本</span>
                        <span class="text-white">OnePage v1.0</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-gray-400">安装时间</span>
                        <span class="text-white"><?= date('Y-m-d H:i:s') ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Security Tips -->
            <div class="bg-blue-500/10 border border-blue-500/20 text-blue-400 p-4 rounded-lg mb-6 text-left">
                <h4 class="font-semibold mb-2">🔐 安全提示</h4>
                <ul class="space-y-1 text-sm">
                    <li>• 请及时修改管理员密码</li>
                    <li>• 建议配置 HTTPS 加密访问</li>
                    <li>• 定期备份数据库和上传文件</li>
                    <li>• 保持系统更新到最新版本</li>
                </ul>
            </div>
            
            <!-- Next Steps -->
            <div class="bg-green-500/10 border border-green-500/20 text-green-400 p-4 rounded-lg mb-8 text-left">
                <h4 class="font-semibold mb-2">✅ 下一步建议</h4>
                <ul class="space-y-1 text-sm">
                    <li>• 配置 AI 审核功能（可选）</li>
                    <li>• 配置邮件通知功能（可选）</li>
                    <li>• 创建第一个分享页面</li>
                    <li>• 邀请团队成员使用</li>
                </ul>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex gap-4">
                <a href="/" class="flex-1 px-6 py-3 bg-white text-black font-semibold rounded-lg hover:bg-gray-200 transition-all duration-200 transform hover:-translate-y-0.5">
                    🚀 进入系统
                </a>
                <a href="/login" class="flex-1 px-6 py-3 bg-gray-800 text-gray-300 font-semibold rounded-lg hover:bg-gray-700 transition-all duration-200">
                    管理员登录
                </a>
            </div>
        </div>
    </div>
</body>
</html>
