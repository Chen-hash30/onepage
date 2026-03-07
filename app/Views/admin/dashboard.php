<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-12">
        <h1 class="text-4xl font-bold mb-2">系统概览</h1>
        <p class="text-gray-400">管理全站资源与用户行为。</p>
    </div>

    <!-- Tab Navigation -->
    <div class="mb-8">
        <div class="flex space-x-1 bg-white/5 p-1 rounded-2xl w-fit">
            <a href="/admin?tab=overview" class="px-6 py-3 rounded-xl transition <?= ($activeTab === 'overview') ? 'bg-purple-600 text-white' : 'text-gray-400 hover:text-white hover:bg-white/10' ?>">
                系统概览
            </a>
            <a href="/admin?tab=users" class="px-6 py-3 rounded-xl transition <?= ($activeTab === 'users') ? 'bg-purple-600 text-white' : 'text-gray-400 hover:text-white hover:bg-white/10' ?>">
                用户管理
            </a>
            <a href="/admin?tab=pages" class="px-6 py-3 rounded-xl transition <?= ($activeTab === 'pages') ? 'bg-purple-600 text-white' : 'text-gray-400 hover:text-white hover:bg-white/10' ?>">
                网页管理
            </a>
        </div>
    </div>

    <!-- Status Messages -->
    <?php if (isset($_GET['status'])): ?>
        <?php if ($_GET['status'] === 'banned'): ?>
            <div class="mb-6 glass p-4 rounded-2xl border border-green-500/20 bg-green-500/10">
                <p class="text-green-400">网页已成功封禁！</p>
            </div>
        <?php elseif ($_GET['status'] === 'unbanned'): ?>
            <div class="mb-6 glass p-4 rounded-2xl border border-green-500/20 bg-green-500/10">
                <p class="text-green-400">网页已成功解封！</p>
            </div>
        <?php elseif ($_GET['status'] === 'reviewed'): ?>
            <div class="mb-6 glass p-4 rounded-2xl border border-blue-500/20 bg-blue-500/10">
                <p class="text-blue-400">网页审核完成！AI 评分: <?= htmlspecialchars($_GET['score'] ?? 'N/A') ?></p>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <?php if ($_GET['error'] === 'csrf_invalid'): ?>
            <div class="mb-6 glass p-4 rounded-2xl border border-red-500/20 bg-red-500/10">
                <p class="text-red-400">安全验证失败，请重试！</p>
            </div>
        <?php elseif ($_GET['error'] === 'csrf_missing'): ?>
            <div class="mb-6 glass p-4 rounded-2xl border border-red-500/20 bg-red-500/10">
                <p class="text-red-400">缺少安全令牌，请刷新页面重试！</p>
            </div>
        <?php elseif ($_GET['error'] === 'ban_failed'): ?>
            <div class="mb-6 glass p-4 rounded-2xl border border-red-500/20 bg-red-500/10">
                <p class="text-red-400">封禁操作失败，请检查日志！</p>
            </div>
        <?php elseif ($_GET['error'] === 'unban_failed'): ?>
            <div class="mb-6 glass p-4 rounded-2xl border border-red-500/20 bg-red-500/10">
                <p class="text-red-400">解封操作失败，请检查日志！</p>
            </div>
        <?php elseif ($_GET['error'] === 'page_not_found'): ?>
            <div class="mb-6 glass p-4 rounded-2xl border border-red-500/20 bg-red-500/10">
                <p class="text-red-400">网页不存在！</p>
            </div>
        <?php elseif ($_GET['error'] === 'file_not_found'): ?>
            <div class="mb-6 glass p-4 rounded-2xl border border-red-500/20 bg-red-500/10">
                <p class="text-red-400">网页文件不存在！</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Tab Content -->
    <?php if ($activeTab === 'overview'): ?>
        <!-- Overview Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div class="glass p-6 rounded-2xl">
                <p class="text-sm text-gray-500 mb-1">活跃用户</p>
                <p class="text-3xl font-bold text-blue-500"><?= $stats['users'] ?></p>
            </div>
            <div class="glass p-6 rounded-2xl">
                <p class="text-sm text-gray-500 mb-1">托管页面</p>
                <p class="text-3xl font-bold text-purple-500"><?= $stats['pages'] ?></p>
            </div>
            <div class="glass p-6 rounded-2xl">
                <p class="text-sm text-gray-500 mb-1">全站曝光量</p>
                <p class="text-3xl font-bold text-green-500"><?= $stats['views'] ?></p>
            </div>
        </div>

        <div class="glass rounded-3xl overflow-hidden">
            <div class="p-8 border-b border-white/5">
                <h2 class="text-xl font-bold">最新动态</h2>
            </div>
            <div class="p-8 text-center text-gray-500">
                暂无近期系统日志
            </div>
        </div>

    <?php elseif ($activeTab === 'users'): ?>
        <!-- Users Management -->
        <div class="glass rounded-3xl overflow-hidden">
            <div class="p-8 border-b border-white/5">
                <h2 class="text-xl font-bold">用户管理</h2>
                <p class="text-gray-400 mt-1">查看和管理所有注册用户</p>
            </div>
            <div class="p-8">
                <?php
                $db = \App\Core\Database::getInstance();
                $users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
                ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-white/5">
                                <th class="text-left py-4 px-4 font-semibold">用户名</th>
                                <th class="text-left py-4 px-4 font-semibold">邮箱</th>
                                <th class="text-left py-4 px-4 font-semibold">角色</th>
                                <th class="text-left py-4 px-4 font-semibold">注册时间</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr class="border-b border-white/5 hover:bg-white/5">
                                    <td class="py-4 px-4"><?= htmlspecialchars($user['username']) ?></td>
                                    <td class="py-4 px-4"><?= htmlspecialchars($user['email']) ?></td>
                                    <td class="py-4 px-4">
                                        <span class="px-2 py-1 rounded-full text-xs <?= $user['role'] === 'admin' ? 'bg-purple-500/20 text-purple-400' : 'bg-blue-500/20 text-blue-400' ?>">
                                            <?= $user['role'] === 'admin' ? '管理员' : '用户' ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-gray-400"><?= date('Y-m-d H:i', strtotime($user['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php elseif ($activeTab === 'pages'): ?>
        <!-- Pages Management -->
        <div class="glass rounded-3xl overflow-hidden">
            <div class="p-8 border-b border-white/5">
                <h2 class="text-xl font-bold">网页管理</h2>
                <p class="text-gray-400 mt-1">查看和管理所有托管的网页</p>
            </div>
            <div class="p-8">
                <?php
                $pageModel = new \App\Models\Page();
                $pages = $pageModel->getAllPagesWithUsers();
                ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-white/5">
                                <th class="text-left py-4 px-4 font-semibold">标题</th>
                                <th class="text-left py-4 px-4 font-semibold">创建者</th>
                                <th class="text-left py-4 px-4 font-semibold">访问量</th>
                                <th class="text-left py-4 px-4 font-semibold">状态</th>
                                <th class="text-left py-4 px-4 font-semibold">AI评分</th>
                                <th class="text-left py-4 px-4 font-semibold">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pages as $page): ?>
                                <tr class="border-b border-white/5 hover:bg-white/5">
                                    <td class="py-4 px-4">
                                        <a href="/p/<?= $page['slug'] ?>" target="_blank" class="text-purple-400 hover:text-purple-300">
                                            <?= htmlspecialchars($page['title']) ?>
                                        </a>
                                    </td>
                                    <td class="py-4 px-4"><?= htmlspecialchars($page['username']) ?></td>
                                    <td class="py-4 px-4"><?= $page['views'] ?></td>
                                    <td class="py-4 px-4">
                                        <span class="px-2 py-1 rounded-full text-xs <?= $page['banned'] == 1 ? 'bg-red-500/20 text-red-400' : 'bg-green-500/20 text-green-400' ?>">
                                            <?= $page['banned'] == 1 ? '已封禁' : '正常' ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <?php if ($page['ai_score'] !== null): ?>
                                            <span class="px-2 py-1 rounded-full text-xs bg-blue-500/20 text-blue-400">
                                                <?= number_format($page['ai_score'], 1) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-gray-500">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="flex space-x-2">
                                            <?php if ($page['banned'] != 1): ?>
                                                <button type="button" class="ban-btn text-red-400 hover:text-red-300 text-sm" data-page-id="<?= $page['id'] ?>" data-action="ban">
                                                    封禁
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="ban-btn text-green-400 hover:text-green-300 text-sm" data-page-id="<?= $page['id'] ?>" data-action="unban">
                                                    解封
                                                </button>
                                            <?php endif; ?>
                                            <button type="button" class="review-btn text-purple-400 hover:text-purple-300 text-sm" data-page-id="<?= $page['id'] ?>">
                                                审核
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function disableReview(button) {
    button.disabled = true;
    button.textContent = '审核中...';
    button.closest('form').submit();
}

// 邮件配置切换
document.addEventListener('DOMContentLoaded', function() {
    const mailMethodSelect = document.querySelector('select[name="mail_method"]');
    const smtpSettings = document.getElementById('smtp-settings');

    if (mailMethodSelect && smtpSettings) {
        mailMethodSelect.addEventListener('change', function() {
            if (this.value === 'smtp') {
                smtpSettings.style.display = 'block';
            } else {
                smtpSettings.style.display = 'none';
            }
        });
    }
});

// AJAX ban/unban functionality
document.addEventListener('DOMContentLoaded', function() {
    const banButtons = document.querySelectorAll('.ban-btn');

    banButtons.forEach(button => {
        button.addEventListener('click', function() {
            const pageId = this.getAttribute('data-page-id');
            const action = this.getAttribute('data-action');
            const csrfToken = '<?= csrf_token() ?>';


            // Disable button during request
            this.disabled = true;
            this.textContent = '处理中...';

            // Prepare form data
            const formData = new FormData();
            formData.append('csrf_token', csrfToken);

            // Determine URL
            const url = action === 'ban' ? `/admin/ban/${pageId}` : `/admin/unban/${pageId}`;


            // Send AJAX request
            fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {

                // 检查响应类型
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Server returned non-JSON response: ' + contentType);
                }

                return response.text().then(text => {

                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        throw new Error('JSON parse error: ' + e.message + '. Response: ' + text);
                    }
                });
            })
            .then(data => {

                // Output debug info to console
                if (data.debug) {
                    console.group('Debug Information');
                    if (data.debug.error) {
                        console.error('Error:', data.debug.error);
                    }
                    console.groupEnd();
                }

                if (data.success) {
                    // Update button and status
                    if (action === 'ban') {
                        this.textContent = '解封';
                        this.setAttribute('data-action', 'unban');
                        this.className = 'ban-btn text-green-400 hover:text-green-300 text-sm';

                        // Update status badge
                        const statusBadge = this.closest('tr').querySelector('.px-2.py-1');
                        statusBadge.className = 'px-2 py-1 rounded-full text-xs bg-red-500/20 text-red-400';
                        statusBadge.textContent = '已封禁';
                    } else {
                        this.textContent = '封禁';
                        this.setAttribute('data-action', 'ban');
                        this.className = 'ban-btn text-red-400 hover:text-red-300 text-sm';

                        // Update status badge
                        const statusBadge = this.closest('tr').querySelector('.px-2.py-1');
                        statusBadge.className = 'px-2 py-1 rounded-full text-xs bg-green-500/20 text-green-400';
                        statusBadge.textContent = '正常';
                    }

                    // Show success message
                    showMessage(data.message, 'success');
                } else {
                    // Show error message
                    showMessage(data.error || '操作失败', 'error');
                    // Reset button
                    this.textContent = action === 'ban' ? '封禁' : '解封';
                }

                // Re-enable button
                this.disabled = false;
            })
            .catch(error => {
                console.error('AJAX Error Details:', {
                    message: error.message,
                    stack: error.stack,
                    pageId: pageId,
                    action: action,
                    url: url
                });

                showMessage('网络错误: ' + error.message, 'error');

                // Reset button
                this.textContent = action === 'ban' ? '封禁' : '解封';
                this.disabled = false;
            });
        });
    });

    // AJAX review functionality with modal
    const reviewButtons = document.querySelectorAll('.review-btn');

    reviewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const pageId = this.getAttribute('data-page-id');
            const csrfToken = '<?= csrf_token() ?>';

            // 直接开始审核，不显示弹窗
            performReview(pageId, csrfToken);
        });
    });

    function performReview(pageId, csrfToken) {

        // 防止重复请求
        if (window.reviewInProgress) {
            return;
        }
        window.reviewInProgress = true;

        // 显示审核中提示
        showMessage('请稍后，正在审核...', 'warning');

        const url = `/admin/review/${pageId}`;

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `csrf_token=${encodeURIComponent(csrfToken)}`
        })
        .then(response => {

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.text();
        })
        .then(text => {

            // Split the response by newlines and parse each JSON line
            const lines = text.trim().split('\n');

            let finalResult = null;

            lines.forEach((line, index) => {

                if (line.trim()) {
                    try {
                        const data = JSON.parse(line.trim());


                        if (index === lines.length - 1) {
                            finalResult = data;
                        }
                    } catch (e) {
                        if (index === lines.length - 1) {
                            finalResult = {
                                success: false,
                                error: '解析响应失败',
                                details: line.substring(0, 100) + '...'
                            };
                        }
                    }
                }
            });

            // Handle final result after all steps are shown
            setTimeout(() => {

                if (finalResult && finalResult.success) {

                    // Output AI response to console

                    // Update the page score in the table immediately
                    const reviewBtn = document.querySelector(`[data-page-id="${pageId}"]`);
                    if (reviewBtn) {
                        const row = reviewBtn.closest('tr');
                        if (row) {
                            const scoreCell = row.querySelector('td:nth-child(5)');
                            if (scoreCell && finalResult.data && finalResult.data.score !== undefined) {
                                // Replace the entire cell content with the new score
                                scoreCell.innerHTML = `
                                    <span class="px-2 py-1 rounded-full text-xs bg-blue-500/20 text-blue-400">
                                        ${finalResult.data.score.toFixed(1)}
                                    </span>
                                `;
                            }
                        }
                    }

                    // Also update the page data in memory if needed
                    // This ensures consistency if the page is refreshed later

                    // Show success message
                    const existingMessages = document.querySelectorAll('.message-notification');
                    existingMessages.forEach(msg => msg.remove());
                    showMessage('审核完成', 'success');
                } else {

                    const existingMessages = document.querySelectorAll('.message-notification');
                    existingMessages.forEach(msg => msg.remove());
                    showMessage(finalResult?.error || '审核失败', 'error');
                }

                // 重置审核状态
                window.reviewInProgress = false;
            }, (lines.length + 1) * 500);
        })
        .catch(error => {

            // Remove existing messages and show error message
            const existingMessages = document.querySelectorAll('.message-notification');
            existingMessages.forEach(msg => msg.remove());
            showMessage('审核请求失败: ' + error.message, 'error');

            // 重置审核状态
            window.reviewInProgress = false;
        });
    }

    function addProgressStep(container, data, isLast) {
        const stepDiv = document.createElement('div');
        stepDiv.className = `flex items-start space-x-3 p-3 rounded-lg ${
            data.success
                ? 'bg-green-500/10 border border-green-500/20'
                : 'bg-red-500/10 border border-red-500/20'
        }`;

        const icon = data.success
            ? '<svg class="w-5 h-5 text-green-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>'
            : '<svg class="w-5 h-5 text-red-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';

        let content = `
            <div class="flex-shrink-0">${icon}</div>
            <div class="flex-1">
                <div class="font-medium ${data.success ? 'text-green-400' : 'text-red-400'}">
                    ${data.step || '未知步骤'}
                </div>
                <div class="text-sm text-gray-300 mt-1">
                    ${data.message || data.error || '处理中...'}
                </div>
        `;

        if (data.details) {
            content += `<div class="text-xs text-gray-500 mt-1 font-mono">${data.details}</div>`;
        }

        if (data.data) {
            content += '<div class="text-xs text-gray-400 mt-2">';
            Object.entries(data.data).forEach(([key, value]) => {
                content += `<div>${key}: <span class="text-white">${JSON.stringify(value)}</span></div>`;
            });
            content += '</div>';
        }

        content += '</div>';

        if (!isLast) {
            content += '<div class="text-xs text-gray-500">继续处理...</div>';
        }

        stepDiv.innerHTML = content;
        container.appendChild(stepDiv);

        // Scroll to bottom
        container.scrollTop = container.scrollHeight;
    }

    // Function to show messages
    function showMessage(message, type) {
        // Remove existing messages
        const existingMessages = document.querySelectorAll('.message-notification');
        existingMessages.forEach(msg => msg.remove());

        // Create new message
        const messageDiv = document.createElement('div');
        messageDiv.className = `message-notification fixed top-4 right-4 z-50 p-4 rounded-2xl glass border ${
            type === 'success'
                ? 'border-green-500/20 bg-green-500/10 text-green-400'
                : type === 'warning'
                ? 'border-orange-500/20 bg-orange-500/10 text-orange-400'
                : 'border-red-500/20 bg-red-500/10 text-red-400'
        }`;
        messageDiv.innerHTML = `
            <p>${message}</p>
            <button class="ml-4 text-sm underline" onclick="this.parentElement.remove()">关闭</button>
        `;

        document.body.appendChild(messageDiv);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (messageDiv.parentElement) {
                messageDiv.remove();
            }
        }, 5000);
    }
});
</script>
