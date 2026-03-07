<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-12">
        <div>
            <h1 class="text-4xl font-bold mb-2">我的网页</h1>
            <p class="text-gray-400">管理、追踪并分享你的创意单页。</p>
        </div>
        <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" class="mt-4 md:mt-0 px-6 py-3 bg-white text-black font-semibold rounded-xl hover:bg-gray-200 transition">
            上传新页面
        </button>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
        <?php 
        $totalViews = array_sum(array_column($pages, 'views'));
        $pageCount = count($pages);
        ?>
        <div class="glass p-8 rounded-[2rem] border-white/5 glow-card">
            <p class="text-xs font-medium text-gray-500 mb-2 uppercase tracking-wider">总发布记录</p>
            <p class="text-4xl font-bold bg-gradient-to-r from-white to-gray-500 bg-clip-text text-transparent"><?= $pageCount ?></p>
        </div>
        <div class="glass p-8 rounded-[2rem] border-white/5 glow-card">
            <p class="text-xs font-medium text-gray-500 mb-2 uppercase tracking-wider">累计曝光量</p>
            <p class="text-4xl font-bold bg-gradient-to-r from-white to-gray-500 bg-clip-text text-transparent"><?= $totalViews ?></p>
        </div>
        <div class="glass p-8 rounded-[2rem] border-white/5 glow-card">
            <p class="text-xs font-medium text-gray-500 mb-2 uppercase tracking-wider">存储活跃度</p>
            <p class="text-4xl font-bold bg-gradient-to-r from-green-400 to-emerald-600 bg-clip-text text-transparent">Optimal</p>
        </div>
    </div>

    <!-- Pages List -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php foreach ($pages as $page): ?>
            <div class="glass rounded-[2rem] overflow-hidden group border border-white/5 hover:border-white/20 transition-all duration-500 glow-card">
                <div class="p-8">
                    <div class="flex justify-between items-start mb-6">
                        <h3 class="text-xl font-bold truncate pr-4 text-white group-hover:text-purple-400 transition-colors"><?= htmlspecialchars($page['title']) ?></h3>
                        <div class="flex items-center space-x-1 px-2 py-0.5 rounded-full <?php if ($page['is_public']): ?>bg-green-500/10<?php else: ?>bg-gray-500/10<?php endif; ?>">
                            <span class="w-1 h-1 <?php if ($page['is_public']): ?>bg-green-500 animate-pulse<?php else: ?>bg-gray-500<?php endif; ?> rounded-full"></span>
                            <span class="text-[9px] uppercase font-bold tracking-tighter <?php if ($page['is_public']): ?>text-green-500<?php else: ?>text-gray-500<?php endif; ?>"><?php echo $page['is_public'] ? 'Live' : 'Paused'; ?></span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 text-sm text-gray-500 mb-8 bg-white/5 px-4 py-2 rounded-xl border border-white/5">
                        <span>/p/<?= $page['slug'] ?></span>
                    </div>
                    <div class="flex items-center justify-between mb-8">
                        <span class="text-sm text-gray-400">发布状态</span>
                        <form action="/pages/toggle-public/<?= $page['id'] ?>" method="POST" style="display:inline">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <label class="switch-button" for="switch-<?= $page['id'] ?>">
                                <div class="switch-outer">
                                    <input id="switch-<?= $page['id'] ?>" type="checkbox" <?= $page['is_public'] ? 'checked' : '' ?> onchange="this.form.submit()">
                                    <div class="button">
                                        <span class="button-toggle"></span>
                                        <span class="button-indicator"></span>
                                    </div>
                                </div>
                            </label>
                        </form>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center text-sm font-medium text-gray-400">
                            <span class="mr-4"><i class="fas fa-eye"></i> <?= $page['views'] ?> <span class="text-[10px] uppercase ml-1 opacity-50">Views</span></span>
                        </div>
                        <div class="flex space-x-3">
                            <a href="/p/<?= $page['slug'] ?>" target="_blank" class="w-10 h-10 flex items-center justify-center glass rounded-xl hover:bg-white/10 transition-all border border-white/10" title="预览">
                                <i class="fas fa-link text-xs"></i>
                            </a>
                            <button onclick="sharePage('<?= $page['slug'] ?>')" class="w-10 h-10 flex items-center justify-center glass rounded-xl hover:bg-white/10 transition-all border border-white/10" title="分享">
                                <i class="fas fa-share text-xs"></i>
                            </button>
                            <form action="/pages/delete/<?= $page['id'] ?>" method="POST" onsubmit="return confirm('确定要删除此页面吗？此操作不可撤销。');" style="display:inline">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <button type="submit" class="w-10 h-10 flex items-center justify-center glass rounded-xl hover:bg-red-600/30 transition-all border border-white/10" title="删除">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($pages)): ?>
            <div class="col-span-full py-32 text-center glass rounded-[3rem] border-dashed border-2 border-white/10">
                <div class="w-20 h-20 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl"><i class="fas fa-folder-open"></i></div>
                <p class="text-gray-500 font-light">还没有任何作品发布，点击上方按钮开启你的第一个创意空间</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm hidden">
    <div class="max-w-lg w-full glass p-8 rounded-3xl" onclick="event.stopPropagation()">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">上传网页</h2>
            <button onclick="document.getElementById('uploadModal').classList.add('hidden')" class="text-gray-500 hover:text-white">&times;</button>
        </div>
        <form action="/upload" method="POST" enctype="multipart/form-data" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">项目标题</label>
                <input type="text" name="title" required placeholder="例如：我的作品集" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-purple-500 transition">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">网页文件 (支持 .html 或 .zip)</label>
                <input type="file" name="webfile" accept=".html,.zip" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 outline-none file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-white file:text-black hover:file:bg-gray-200">
                <p class="mt-2 text-xs text-gray-500">提示：zip 包请确保 index.html 位于根目录下。</p>
                <div class="mt-4 p-4 bg-red-500/10 border border-red-500/20 rounded-xl">
                    <p class="text-sm text-red-400 font-medium mb-2">⚠️ 上传须知</p>
                    <p class="text-xs text-red-300">请勿上传任何违法文件，包括但不限于涉及政治敏感、色情、暴力、侵权等内容。一经发现，将立即删除并封禁账号。</p>
                </div>
            </div>
            <button type="submit" class="w-full bg-purple-600 text-white font-bold py-3 rounded-xl hover:bg-purple-700 transition">立即发布</button>
        </form>
    </div>
</div>

<!-- Share Modal -->
<div id="shareModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm hidden">
    <div class="max-w-md w-full glass p-8 rounded-3xl text-center" onclick="event.stopPropagation()">
        <h2 class="text-2xl font-bold mb-4">分享网页</h2>
        <div id="qrcode" class="bg-white p-4 rounded-2xl inline-block mb-6">
            <img id="qrImage" src="" alt="QR Code" class="w-48 h-48">
        </div>
        <div class="mb-6">
            <p class="text-xs text-gray-500 mb-2 uppercase tracking-widest">唯一访问链接</p>
            <div class="flex">
                <input type="text" id="shareUrl" readonly class="w-full bg-white/5 border border-white/10 rounded-l-xl px-4 py-2 text-sm outline-none">
                <button onclick="copyLink()" class="bg-white text-black px-4 py-2 rounded-r-xl font-bold text-sm hover:bg-gray-200 whitespace-nowrap">复制</button>
            </div>
        </div>
        <button onclick="document.getElementById('shareModal').classList.add('hidden')" class="w-full py-3 glass rounded-xl hover:bg-white/10 transition">关闭</button>
    </div>
</div>

<script>
function sharePage(slug) {
    const url = window.location.origin + '/p/' + slug;
    document.getElementById('shareUrl').value = url;
    document.getElementById('qrImage').src = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(url);
    document.getElementById('shareModal').classList.remove('hidden');
}

function copyLink() {
    const copyText = document.getElementById("shareUrl");
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(copyText.value);
    alert("链接已复制到剪贴板");
}
</script>
