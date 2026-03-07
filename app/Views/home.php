<div class="relative overflow-hidden pt-20 pb-32">
    <!-- Background Decor -->
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[600px] bg-purple-600/20 blur-[120px] rounded-full -z-10"></div>
    <div class="absolute bottom-0 right-0 w-[500px] h-[500px] bg-blue-600/10 blur-[100px] rounded-full -z-10"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-white/5 border border-white/10 mb-8 animate-subtle">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-purple-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-purple-500"></span>
            </span>
            <span class="text-xs font-medium text-gray-400 uppercase tracking-widest">v2.0 现已发布</span>
        </div>
        
        <h1 class="text-4xl sm:text-6xl md:text-8xl font-bold tracking-tight mb-8 leading-[1.1] custom-font">
            不仅仅是托管 <br>
            <span class="gradient-text">重塑网页表达</span>
        </h1>
        <p class="text-lg sm:text-xl text-gray-400 max-w-2xl mx-auto mb-16 leading-relaxed font-light">
            极简、极速、安全。专为现代创作者打造的静态空间，让你的创意在互联网上拥有最优雅的落脚点。
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-6">
            <div class="relative group">
                <a href="/register"
                  class="relative inline-block p-px font-semibold leading-6 text-white bg-neutral-900 shadow-2xl cursor-pointer rounded-2xl shadow-emerald-900 transition-all duration-300 ease-in-out hover:scale-105 active:scale-95 hover:shadow-emerald-600"
                >
                  <span
                    class="absolute inset-0 rounded-2xl bg-gradient-to-r from-emerald-500 via-cyan-500 to-sky-600 p-[2px] opacity-0 transition-opacity duration-500 group-hover:opacity-100"
                  ></span>
                  <span class="relative z-10 block px-6 py-3 rounded-2xl bg-neutral-950">
                    <div class="relative z-10 flex items-center space-x-3">
                      <span
                        class="transition-all duration-500 group-hover:translate-x-1.5 group-hover:text-emerald-300"
                        >即刻起航</span
                      >
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="currentColor"
                        class="w-7 h-7 transition-all duration-500 group-hover:translate-x-1.5 group-hover:text-emerald-300"
                      >
                        <path
                          d="M16.172 11l-5.364-5.364 1.414-1.414L20 12l-7.778 7.778-1.414-1.414L16.172 13H4v-2z"
                        ></path>
                      </svg>
                    </div>
                  </span>
                </a>
            </div>
        </div>

        <!-- Features Preview -->
        <div id="features" class="mt-48 grid grid-cols-1 md:grid-cols-3 gap-8 text-left">
            <div class="glass p-10 rounded-[2.5rem] glow-card transition-all duration-500 group fade-in-up">
                <div class="w-14 h-14 bg-gradient-to-br from-purple-500/20 to-indigo-500/20 rounded-2xl flex items-center justify-center mb-8 text-3xl group-hover:scale-110 transition-transform"><i class="fas fa-bolt"></i></div>
                <h3 class="text-2xl font-semibold mb-4 text-white">瞬间分发</h3>
                <p class="text-gray-400 leading-relaxed font-light">毫秒级解析技术，支持 HTML、CSS、JS 的实时渲染，让全球观众瞬间触达你的作品。</p>
            </div>
            <div class="glass p-10 rounded-[2.5rem] glow-card transition-all duration-500 group fade-in-up delay-200">
                <div class="w-14 h-14 bg-gradient-to-br from-blue-500/20 to-cyan-500/20 rounded-2xl flex items-center justify-center mb-8 text-3xl group-hover:scale-110 transition-transform"><i class="fas fa-gem"></i></div>
                <h3 class="text-2xl font-semibold mb-4 text-white">极致优雅</h3>
                <p class="text-gray-400 leading-relaxed font-light">摒弃繁琐，专注核心。我们提供最纯粹的 URL 命名空间与无广告的展示体验。</p>
            </div>
            <div class="glass p-10 rounded-[2.5rem] glow-card transition-all duration-500 group fade-in-up delay-400">
                <div class="w-14 h-14 bg-gradient-to-br from-pink-500/20 to-orange-500/20 rounded-2xl flex items-center justify-center mb-8 text-3xl group-hover:scale-110 transition-transform"><i class="fas fa-chart-bar"></i></div>
                <h3 class="text-2xl font-semibold mb-4 text-white">智慧洞察</h3>
                <p class="text-gray-400 leading-relaxed font-light">全方位的访问热度分析与地理分布统计，助你精准把握每一个创意的脉搏。</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate');
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.fade-in-up').forEach(el => observer.observe(el));
});
</script>
