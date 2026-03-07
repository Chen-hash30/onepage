<!DOCTYPE html>
<html lang="zh-CN" class="h-full bg-gray-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统安装 - 环境检测</title>
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
        <div class="max-w-4xl w-full glass p-8 rounded-2xl glow-card transition-all duration-300">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="loader flex items-center justify-center mb-4">
                    <div class="loader_cube loader_cube--color" style="position: relative; width: 25px; height: 25px; background: linear-gradient(135deg, #1afbf0, #da00ff); border-radius: 5px;"></div>
                </div>
                <h1 class="text-4xl font-bold gradient-text mb-2">🚀 系统安装向导</h1>
                <p class="text-gray-400">只需几步即可完成系统部署</p>
            </div>
            
            <!-- Progress -->
            <div class="flex mb-8 px-4">
                <?php for($i = 1; $i <= 5; $i++): ?>
                    <div class="flex-1 text-center relative">
                        <div class="w-10 h-10 rounded-full mx-auto mb-2 flex items-center justify-center font-semibold transition-all duration-300
                            <?= $i === 1 ? 'bg-purple-600 text-white' : 'bg-gray-800 text-gray-500' ?>">
                            <?= $i === 1 ? '1' : ($i < 1 ? '✓' : $i) ?>
                        </div>
                        <div class="text-xs text-gray-400">环境检测</div>
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
                        <strong class="font-semibold">发现问题：</strong><br>
                        <?php foreach ($errors as $error): ?>
                            • <?= htmlspecialchars($error) ?><br>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <h2 class="text-2xl font-bold mb-6 text-white">环境检测结果</h2>
                
                <?php foreach ($checks as $key => $check): ?>
                    <div class="flex items-center py-4 border-b border-white/5 glow-card transition-all duration-200">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center mr-4 transition-all
                            <?= $check['pass'] ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' ?>">
                            <?= $check['pass'] ? '✓' : '✗' ?>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-white mb-1"><?= htmlspecialchars($check['name']) ?></div>
                            <?php if (isset($check['current'])): ?>
                                <div class="text-sm text-gray-400">
                                    当前版本：<?= htmlspecialchars($check['current']) ?>
                                    <?php if (isset($check['required'])): ?>
                                        (要求：<?= htmlspecialchars($check['required']) ?>+)
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if ($allPass): ?>
                    <div class="bg-green-500/10 border border-green-500/20 text-green-400 p-4 rounded-lg mt-6">
                        ✓ 所有环境检查通过！
                    </div>
                <?php else: ?>
                    <div class="bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-lg mt-6">
                        ✗ 存在不满足的环境要求，请先修复
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Footer -->
            <div class="mt-8 pt-6 border-t border-white/5 text-center px-4">
                <?php if ($allPass): ?>
                    <a href="?step=2" class="inline-block px-8 py-3 bg-white text-black font-semibold rounded-lg hover:bg-gray-200 transition-all duration-200 transform hover:-translate-y-0.5">
                        下一步：数据库配置 →
                    </a>
                <?php else: ?>
                    <button class="inline-block px-8 py-3 bg-gray-700 text-gray-400 font-semibold rounded-lg cursor-not-allowed">
                        请先修复环境问题
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
