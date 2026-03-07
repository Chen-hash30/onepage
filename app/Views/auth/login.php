<div class="min-h-[80vh] flex items-center justify-center px-4">
    <div class="max-w-md w-full glass p-8 rounded-2xl">
        <h2 class="text-3xl font-bold mb-6 text-center">欢迎回来</h2>
        <?php if (isset($error)): ?>
            <div class="bg-red-500/10 border border-red-500/20 text-red-500 p-3 rounded-lg mb-4 text-sm">
                <?= $error ?>
            </div>
        <?php endif; ?>
        <form action="/login" method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">电子邮箱</label>
                <input type="email" name="email" required class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">密码</label>
                <input type="password" name="password" required class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 outline-none transition">
            </div>
            <button type="submit" class="w-full bg-white text-black font-semibold py-2 rounded-lg hover:bg-gray-200 transition">登录</button>
        </form>
        <p class="mt-6 text-center text-sm text-gray-500">
            没有账号？ <a href="/register" class="text-purple-400 hover:underline">立即注册</a>
        </p>
    </div>
</div>
