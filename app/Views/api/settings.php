<div class="max-w-4xl mx-auto px-4 py-12">
    <div class="glass p-8 rounded-3xl">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold">MCP API 设置</h1>
                <p class="text-gray-500 mt-1">启用 API 以允许 MCP 服务连接</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-gray-400">API 状态</span>
                <button id="apiToggle" class="relative w-14 h-8 rounded-full transition-colors <?= $settings && $settings['api_enabled'] ? 'bg-green-500' : 'bg-gray-600' ?>">
                    <span class="absolute top-1 left-1 w-6 h-6 bg-white rounded-full transition-transform <?= $settings && $settings['api_enabled'] ? 'translate-x-6' : '' ?>"></span>
                </button>
            </div>
        </div>

        <?php if (isset($_GET['status']) && $_GET['status'] === 'saved'): ?>
            <div class="bg-green-500/10 border border-green-500/20 text-green-500 p-3 rounded-lg mb-4 text-sm">
                设置已保存！
            </div>
        <?php endif; ?>

        <div class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">API Key</label>
                <div class="flex gap-2">
                    <input type="text" id="apiKeyDisplay" 
                        value="<?= $settings && $settings['api_key'] ? htmlspecialchars($settings['api_key']) : '尚未生成' ?>" 
                        readonly
                        class="flex-1 bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-gray-400 text-sm">
                    <button type="button" id="copyApiKey" class="px-4 py-3 bg-white/10 hover:bg-white/20 rounded-xl transition" title="复制">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </button>
                    <button type="button" id="regenerateKey" class="px-4 py-3 bg-red-500/10 text-red-500 hover:bg-red-500/20 rounded-xl transition" title="重新生成">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">API 端点</label>
                <div class="bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-gray-300 font-mono text-sm">
                    <?= rtrim(EnvLoader::get('APP_URL', 'http://localhost'), '/') ?>/api/mcp
                </div>
            </div>

            <div class="bg-yellow-500/10 border border-yellow-500/20 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div>
                        <p class="text-yellow-500 text-sm font-medium">安全提示</p>
                        <p class="text-gray-400 text-xs mt-1">
                            API Key 仅在重新生成时显示，请妥善保存。如果丢失，点击右侧的刷新按钮重新生成。
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 pt-6 border-t border-white/10">
            <h3 class="text-lg font-medium mb-4">使用说明</h3>
            <div class="space-y-4 text-gray-400 text-sm">
                <div>
                    <h4 class="font-medium text-gray-300 mb-2">📋 连接方式</h4>
                    <p class="mb-2">MCP 服务支持两种连接方式：</p>
                    <ul class="list-disc list-inside space-y-1 ml-2">
                        <li><strong>SSE 模式（推荐）</strong>：Server-Sent Events，实时双向通信</li>
                        <li><strong>HTTP 模式</strong>：标准 JSON-RPC 请求</li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-medium text-gray-300 mb-2">🔧 Claude Desktop 配置</h4>
                    <p class="mb-2">编辑配置文件：</p>
                    <div class="bg-black/30 rounded-lg p-3 font-mono text-xs mb-2">
                        <span class="text-gray-500"># macOS: ~/Library/Application Support/Claude/claude_desktop_config.json</span><br>
                        <span class="text-gray-500"># Windows: %APPDATA%\Claude\claude_desktop_config.json</span>
                    </div>
                    <div class="bg-black/30 rounded-lg p-4 font-mono text-xs overflow-x-auto">
<?php $apiUrl = rtrim(EnvLoader::get('APP_URL', 'http://localhost'), '/') . '/api/mcp'; ?>
<pre>{
  "mcpServers": {
    "easknow": {
      "url": "<?= $apiUrl ?>/sse?apiKey=YOUR_API_KEY",
      "type": "sse"
    }
  }
}</pre>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">将 <code>YOUR_API_KEY</code> 替换为你的实际 API Key</p>
                </div>

                <div>
                    <h4 class="font-medium text-gray-300 mb-2">🌐 Cursor IDE 配置</h4>
                    <p class="mb-2">在 Cursor 设置中添加 MCP 服务器：</p>
                    <div class="bg-black/30 rounded-lg p-4 font-mono text-xs overflow-x-auto">
<pre>{
  "mcp": {
    "servers": {
      "easknow": {
        "url": "<?= $apiUrl ?>/sse?apiKey=YOUR_API_KEY",
        "transportType": "sse"
      }
    }
  }
}</pre>
                    </div>
                </div>

                <div>
                    <h4 class="font-medium text-gray-300 mb-2">🔌 其他 MCP 客户端</h4>
                    <p class="mb-2">使用标准 SSE 连接：</p>
                    <div class="bg-black/30 rounded-lg p-4 font-mono text-xs overflow-x-auto">
Endpoint: <?= $apiUrl ?>/sse?apiKey=YOUR_API_KEY
Protocol: MCP over SSE
                    </div>
                    <p class="text-xs text-gray-500 mt-2">或使用 HTTP POST 方式（不支持流式）：<code><?= $apiUrl ?></code></p>
                </div>

                <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-3">
                    <p class="text-blue-500 text-xs">
                        <strong>💡 提示：</strong> 确保 API 状态已启用，并妥善保管你的 API Key。如果 Key 泄露，请立即重新生成。
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const apiToggle = document.getElementById('apiToggle');
    const regenerateKeyBtn = document.getElementById('regenerateKey');
    const copyApiKeyBtn = document.getElementById('copyApiKey');
    const apiKeyDisplay = document.getElementById('apiKeyDisplay');

    apiToggle.addEventListener('click', function() {
        const enabled = !this.classList.contains('bg-green-500');
        
        fetch('/api/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'enabled=' + enabled + '&csrf_token=<?= csrf_token() ?>'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (enabled) {
                    this.classList.remove('bg-gray-600');
                    this.classList.add('bg-green-500');
                    this.querySelector('span').classList.add('translate-x-6');
                } else {
                    this.classList.remove('bg-green-500');
                    this.classList.add('bg-gray-600');
                    this.querySelector('span').classList.remove('translate-x-6');
                }
            } else {
                alert(data.message || '操作失败');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('操作失败，请重试');
        });
    });

    regenerateKeyBtn.addEventListener('click', function() {
        if (!confirm('确定要重新生成 API Key 吗？旧 Key 将立即失效。')) {
            return;
        }

        const formData = new FormData();
        formData.append('csrf_token', '<?= csrf_token() ?>');

        fetch('/api/regenerate-key', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.api_key) {
                    apiKeyDisplay.value = data.api_key;
                    alert('API Key 已重新生成，请妥善保存！');
                }
            } else {
                alert(data.message || '生成失败');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('生成失败，请重试');
        });
    });

    copyApiKeyBtn.addEventListener('click', function() {
        const apiKey = apiKeyDisplay.value;
        if (apiKey && apiKey !== '尚未生成') {
            navigator.clipboard.writeText(apiKey).then(function() {
                alert('API Key 已复制');
            }, function(err) {
                console.error('复制失败:', err);
            });
        }
    });
});
</script>
