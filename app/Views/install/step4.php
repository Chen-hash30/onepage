<!DOCTYPE html>
<html lang="zh-CN" class="h-full bg-gray-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统安装 - 开始安装</title>
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
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .animate-spin {
            animation: spin 1s linear infinite;
        }
    </style>
</head>
<body class="h-full text-gray-200 selection:bg-purple-500/30">
    <div class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="max-w-2xl w-full glass p-8 rounded-2xl glow-card transition-all duration-300">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold gradient-text mb-2">🎉 开始安装</h1>
                <p class="text-gray-400">确认配置信息并开始安装</p>
            </div>
            
            <!-- Progress -->
            <div class="flex mb-8 px-4">
                <?php for($i = 1; $i <= 5; $i++): ?>
                    <div class="flex-1 text-center relative">
                        <div class="w-10 h-10 rounded-full mx-auto mb-2 flex items-center justify-center font-semibold transition-all duration-300
                            <?= $i === 4 ? 'bg-purple-600 text-white' : ($i < 4 ? 'bg-green-600 text-white' : 'bg-gray-800 text-gray-500') ?>">
                            <?= $i < 4 ? '✓' : $i ?>
                        </div>
                        <div class="text-xs text-gray-400">
                            <?php 
                                $labels = ['环境检测', '数据库配置', '管理员设置', '开始安装', '完成'];
                                echo $labels[$i-1];
                            ?>
                        </div>
                        <?php if ($i < 5): ?>
                            <div class="absolute top-5 left-1/2 w-full h-0.5 bg-gray-800 -z-10"></div>
                        <?php endif; ?>
                    </div>
                <?php endfor; ?>
            </div>
            
            <!-- Content -->
            <div class="px-4">
                <?php if (!empty($errors)): ?>
                    <div class="bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-lg mb-6">
                        <strong class="font-semibold">❌ 安装失败：</strong><br><br>
                        <?php foreach ($errors as $error): ?>
                            <div class="mb-2"><?= $error ?></div>
                        <?php endforeach; ?>
                        
                        <div class="mt-4 pt-4 border-t border-red-500/20">
                            <strong class="font-semibold">🔧 建议操作：</strong><br>
                            1. 检查数据库用户权限（需要 CREATE、INSERT 权限）<br>
                            2. 检查 schema.sql 文件是否存在<br>
                            3. 查看 PHP 错误日志获取详细信息<br>
                            4. 如果问题持续，请手动安装数据库
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="bg-white/5 border border-white/10 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-white mb-4">安装配置摘要</h3>
                    <ul class="space-y-2 text-sm">
                        <?php
                        $config = $_SESSION['install_config'] ?? [];
                        echo '<li class="flex justify-between py-2 border-b border-white/5">';
                        echo '<span class="text-gray-400">数据库主机：</span>';
                        echo '<span class="text-white">' . htmlspecialchars($config['db']['host'] ?? '-') . '</span>';
                        echo '</li>';
                        echo '<li class="flex justify-between py-2 border-b border-white/5">';
                        echo '<span class="text-gray-400">数据库名称：</span>';
                        echo '<span class="text-white">' . htmlspecialchars($config['db']['name'] ?? '-') . '</span>';
                        echo '</li>';
                        echo '<li class="flex justify-between py-2 border-b border-white/5">';
                        echo '<span class="text-gray-400">数据库用户：</span>';
                        echo '<span class="text-white">' . htmlspecialchars($config['db']['user'] ?? '-') . '</span>';
                        echo '</li>';
                        echo '<li class="flex justify-between py-2 border-b border-white/5">';
                        echo '<span class="text-gray-400">管理员邮箱：</span>';
                        echo '<span class="text-white">' . htmlspecialchars($config['admin']['email'] ?? '-') . '</span>';
                        echo '</li>';
                        echo '<li class="flex justify-between py-2">';
                        echo '<span class="text-gray-400">管理员用户名：</span>';
                        echo '<span class="text-white">' . htmlspecialchars($config['admin']['username'] ?? '-') . '</span>';
                        echo '</li>';
                        ?>
                    </ul>
                </div>
                
                <div class="text-center py-6">
                    <p class="text-gray-400 mb-2">点击"开始安装"按钮，系统将：</p>
                    <ul class="text-gray-300 space-y-1 text-sm">
                        <li>• 创建配置文件</li>
                        <li>• 初始化数据库表结构</li>
                        <li>• 创建管理员账户</li>
                    </ul>
                </div>
                
                <form method="POST" id="installForm" class="mt-6">
                    <button type="submit" id="installBtn" 
                        class="w-full px-6 py-4 bg-white text-black font-semibold rounded-lg hover:bg-gray-200 transition-all duration-200 transform hover:-translate-y-0.5 flex items-center justify-center">
                        <span>🚀 开始安装</span>
                    </button>
                </form>
                
                <div id="loading" class="hidden text-center py-6">
                    <div class="w-12 h-12 border-4 border-gray-700 border-t-purple-500 rounded-full animate-spin mx-auto mb-4"></div>
                    <p class="text-gray-400">正在安装，请稍候...</p>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="mt-6 pt-6 border-t border-white/5 text-center px-4">
                <a href="?step=3" class="inline-block px-6 py-3 bg-gray-800 text-gray-300 rounded-lg hover:bg-gray-700 transition-all duration-200 text-center font-medium">
                    ← 上一步
                </a>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('installForm').addEventListener('submit', function(e) {
            document.getElementById('installBtn').style.display = 'none';
            document.getElementById('loading').classList.remove('hidden');
        });
    </script>
</body>
</html>
