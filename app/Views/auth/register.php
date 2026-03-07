<div class="min-h-[80vh] flex items-center justify-center px-4">
    <div class="max-w-md w-full glass p-8 rounded-2xl">
        <h2 class="text-3xl font-bold mb-6 text-center">创建账号</h2>
        <?php if (isset($error)): ?>
            <div class="bg-red-500/10 border border-red-500/20 text-red-500 p-3 rounded-lg mb-4 text-sm">
                <?= $error ?>
            </div>
        <?php endif; ?>
        <form action="/register" method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">用户名</label>
                <input type="text" name="username" id="username" required class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 outline-none transition" onblur="checkUsername()">
                <div id="username-msg" class="text-sm mt-1"></div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">电子邮箱</label>
                <div class="flex space-x-2">
                    <input type="email" name="email" id="email" required class="flex-1 bg-white/5 border border-white/10 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 outline-none transition">
                    <button type="button" id="sendBtn" onclick="sendCode()" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">发送验证码</button>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">验证码</label>
                <input type="text" name="verification_code" required class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">密码</label>
                <input type="password" name="password" required class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 outline-none transition">
            </div>
            <button type="submit" class="w-full bg-white text-black font-semibold py-2 rounded-lg hover:bg-gray-200 transition">注册</button>
        </form>
        <p class="mt-6 text-center text-sm text-gray-500">
            已有账号？ <a href="/login" class="text-purple-400 hover:underline">去登录</a>
        </p>
    </div>
</div>

<script>
let countdown = 0;
let timer;

function checkUsername() {
    const username = document.getElementById('username').value;
    const msgDiv = document.getElementById('username-msg');

    if (!username) {
        msgDiv.textContent = '';
        return;
    }

    fetch('/check-username', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'username=' + encodeURIComponent(username)
    })
    .then(response => response.json())
    .then(data => {
        msgDiv.textContent = data.message;
        msgDiv.className = data.available ? 'text-green-500 text-sm mt-1' : 'text-red-500 text-sm mt-1';
    })
    .catch(error => {
        msgDiv.textContent = '检查失败';
        msgDiv.className = 'text-red-500 text-sm mt-1';
    });
}

function sendCode() {
    const email = document.getElementById('email').value;
    const sendBtn = document.getElementById('sendBtn');

    if (!email) {
        alert('请输入邮箱');
        return;
    }

    if (countdown > 0) return;

    sendBtn.disabled = true;
    sendBtn.textContent = '请稍后...';

    fetch('/send-verification-code', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'email=' + encodeURIComponent(email)
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            startCountdown();
        } else {
            sendBtn.disabled = false;
            sendBtn.textContent = '发送验证码';
        }
    })
    .catch(error => {
        alert('发送失败');
        sendBtn.disabled = false;
        sendBtn.textContent = '发送验证码';
    });
}

function startCountdown() {
    countdown = 60;
    const sendBtn = document.getElementById('sendBtn');
    sendBtn.disabled = true;

    timer = setInterval(() => {
        sendBtn.textContent = `重新发送 (${countdown}s)`;
        countdown--;
        if (countdown < 0) {
            clearInterval(timer);
            sendBtn.disabled = false;
            sendBtn.textContent = '发送验证码';
        }
    }, 1000);
}
</script>
