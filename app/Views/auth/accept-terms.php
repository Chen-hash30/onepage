<div class="min-h-[80vh] flex items-center justify-center px-4">
    <div class="max-w-2xl w-full glass p-8 rounded-2xl">
        <h2 class="text-3xl font-bold mb-6 text-center">上传须知</h2>
        <div class="prose prose-invert max-w-none mb-8">
            <p class="text-gray-300 mb-4">在使用OnePage上传网页功能前，请仔细阅读并同意以下条款：</p>
            <div class="bg-red-500/10 border border-red-500/20 p-4 rounded-xl mb-6">
                <h3 class="text-red-400 font-semibold mb-2">禁止内容</h3>
                <p class="text-red-300 text-sm">严禁上传任何违法文件，包括但不限于：</p>
                <ul class="text-red-300 text-sm list-disc list-inside mt-2 space-y-1">
                    <li>政治敏感内容</li>
                    <li>色情、暴力或血腥内容</li>
                    <li>侵权材料（版权、商标等）</li>
                    <li>恶意软件或病毒</li>
                    <li>其他违反法律法规的内容</li>
                </ul>
            </div>
            <div class="bg-blue-500/10 border border-blue-500/20 p-4 rounded-xl mb-6">
                <h3 class="text-blue-400 font-semibold mb-2">责任声明</h3>
                <p class="text-blue-300 text-sm">用户对上传的内容承担全部责任。OnePage有权对违规内容进行删除、封禁账号等处理。</p>
            </div>
        </div>
        <form action="/accept-terms" method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div class="flex items-center">
                <input type="checkbox" id="agree" required class="mr-3">
                <label for="agree" class="text-sm text-gray-300">我已阅读并同意上述条款</label>
            </div>
            <button type="submit" class="w-full bg-purple-600 text-white font-bold py-3 rounded-xl hover:bg-purple-700 transition">同意并继续</button>
        </form>
    </div>
</div>