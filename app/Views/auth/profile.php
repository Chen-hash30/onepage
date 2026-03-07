<div class="max-w-4xl mx-auto px-4 py-12">
    <div class="glass p-8 rounded-3xl">
        <?php if (isset($_GET['status']) && $_GET['status'] === 'updated'): ?>
            <div class="bg-green-500/10 border border-green-500/20 text-green-500 p-3 rounded-lg mb-4 text-sm">
                个人资料已成功更新！
            </div>
        <?php elseif (isset($_GET['error'])): ?>
            <div class="bg-red-500/10 border border-red-500/20 text-red-500 p-3 rounded-lg mb-4 text-sm">
                <?php if ($_GET['error'] === 'username_required'): ?>
                    用户名不能为空。
                <?php elseif ($_GET['error'] === 'update_failed'): ?>
                    更新失败，请重试。
                <?php else: ?>
                    发生未知错误。
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <div class="flex items-center space-x-6 mb-8">
            <div class="w-24 h-24 bg-gradient-to-tr from-purple-500 to-blue-500 rounded-full flex items-center justify-center text-3xl font-bold">
                <?= strtoupper(substr($user['username'], 0, 1)) ?>
            </div>
            <div>
                <h1 class="text-3xl font-bold"><?= htmlspecialchars($user['username']) ?></h1>
                <p class="text-gray-500"><?= htmlspecialchars($user['email']) ?></p>
            </div>
        </div>

        <form action="/profile" method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">用户名</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">电子邮箱</label>
                    <input type="email" value="<?= htmlspecialchars($user['email']) ?>" readonly class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 opacity-50 cursor-not-allowed">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">角色</label>
                <div class="text-white bg-white/10 inline-block px-4 py-2 rounded-lg capitalize">
                    <?= $user['role'] ?>
                </div>
            </div>
            <div class="pt-6">
                <button type="submit" class="px-8 py-3 bg-white text-black font-bold rounded-xl hover:bg-gray-200 transition">保存更改</button>
            </div>
        </form>

        <div class="mt-8 pt-8 border-t border-white/10">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium">MCP API 服务</h3>
                    <p class="text-gray-500 text-sm mt-1">启用 API 以允许 MCP 服务连接</p>
                </div>
                <div class="flex items-center gap-4">
                    <?php if ($apiSettings && $apiSettings['api_enabled']): ?>
                        <div class="flex items-center gap-2 text-green-500 text-sm">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                            已启用
                        </div>
                    <?php else: ?>
                        <div class="flex items-center gap-2 text-gray-500 text-sm">
                            <span class="w-2 h-2 bg-gray-500 rounded-full"></span>
                            未启用
                        </div>
                    <?php endif; ?>
                    <a href="/api/settings" class="px-6 py-3 bg-purple-500/20 text-purple-400 hover:bg-purple-500/30 rounded-xl transition flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        API设置
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
