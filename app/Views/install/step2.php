<!DOCTYPE html>
<html lang="zh-CN" class="h-full bg-gray-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统安装 - 数据库配置</title>
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
    </style>
</head>
<body class="h-full text-gray-200 selection:bg-purple-500/30">
    <div class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="max-w-2xl w-full glass p-8 rounded-2xl glow-card transition-all duration-300">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold gradient-text mb-2">🔧 数据库配置</h1>
                <p class="text-gray-400">配置数据库连接信息</p>
            </div>
            
            <!-- Progress -->
            <div class="flex mb-8 px-4">
                <?php for($i = 1; $i <= 5; $i++): ?>
                    <div class="flex-1 text-center relative">
                        <div class="w-10 h-10 rounded-full mx-auto mb-2 flex items-center justify-center font-semibold transition-all duration-300
                            <?= $i === 2 ? 'bg-purple-600 text-white' : ($i < 2 ? 'bg-green-600 text-white' : 'bg-gray-800 text-gray-500') ?>">
                            <?= $i < 2 ? '✓' : $i ?>
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
                        <strong class="font-semibold">错误：</strong><br>
                        <?php foreach ($errors as $error): ?>
                            • <?= htmlspecialchars($error) ?><br>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <div class="bg-blue-500/10 border border-blue-500/20 text-blue-400 p-4 rounded-lg mb-6">
                    <strong class="font-semibold">💡 提示：</strong><br>
                    • 如果数据库不存在，系统会尝试自动创建（需要 CREATE 权限）<br>
                    • 如果自动创建失败，请先手动创建数据库<br>
                    • 建议使用 root 用户或具有 CREATE 权限的用户进行安装
                </div>
                
                <form method="POST" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            数据库主机
                        </label>
                        <input type="text" name="db_host" value="localhost" required 
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition-all duration-200">
                        <p class="text-xs text-gray-500 mt-1">通常是 localhost</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            数据库名称
                        </label>
                        <input type="text" name="db_name" value="web_share" required 
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition-all duration-200">
                        <p class="text-xs text-gray-500 mt-1">用于存储系统数据的数据库名</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            数据库用户名
                        </label>
                        <input type="text" name="db_user" required 
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition-all duration-200">
                        <p class="text-xs text-gray-500 mt-1">MySQL 用户名</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            数据库密码
                        </label>
                        <input type="password" name="db_pass" 
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition-all duration-200">
                        <p class="text-xs text-gray-500 mt-1">MySQL 密码（如无密码可留空）</p>
                    </div>
                    
                    <div class="flex gap-4 pt-6">
                        <a href="?step=1" class="flex-1 px-6 py-3 bg-gray-800 text-gray-300 rounded-lg hover:bg-gray-700 transition-all duration-200 text-center font-medium">
                            ← 上一步
                        </a>
                        <button type="submit" class="flex-1 px-6 py-3 bg-white text-black font-semibold rounded-lg hover:bg-gray-200 transition-all duration-200 transform hover:-translate-y-0.5">
                            下一步：管理员设置 →
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
