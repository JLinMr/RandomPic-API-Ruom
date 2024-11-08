<?php
$config = require __DIR__ . '/config.php';
$categories = $config['categories'];
$statsFile = $config['stats_file'];
$cdnAssets = $config['cdn_assets'];

// 简化统计数据处理逻辑
if (isset($_GET['action']) && $_GET['action'] === 'stats') {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    
    try {
        $data = [];
        if (file_exists($statsFile)) {
            $data = json_decode(file_get_contents($statsFile), true) ?: [];
        }
        
        $today = date('Y-m-d');
        $labels = array_map(fn($i) => date('Y-m-d', strtotime("-$i days")), range(6, 0));
        $values = array_map(fn($date) => $data['stats']['daily'][$date] ?? 0, $labels);
        
        echo json_encode([
            'today' => $data['stats']['daily'][$today] ?? 0,
            'total' => $data['stats']['total'] ?? 0,
            'labels' => $labels,
            'values' => $values
        ]);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => '获取统计数据失败']);
        exit;
    }
}

function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    return $protocol . $_SERVER['HTTP_HOST'];
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>随机图片 API</title>
    <link rel="preload" href="<?php echo $cdnAssets['bg']; ?>" as="image">
    <link href="<?php echo $cdnAssets['tailwind']; ?>" rel="stylesheet">
    <link rel="shortcut icon" href="/favicon.ico">
    <script src="<?php echo $cdnAssets['echarts']; ?>"></script>
    <meta name="description" content="随机图片 API 服务提供高质量随机图片接口">
    <meta name="keywords" content="随机图片,API,图片接口">
    <style>
        .gradient-bg {
            background: url(<?php echo $cdnAssets['bg']; ?>) no-repeat 100% 100% / cover fixed;
        }
        
        /* 滚动条美化 */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        
        /* Firefox 滚动条美化 */
        * {
            scrollbar-width: thin;
            scrollbar-color: #c1c1c1 #f1f1f1;
        }

        .github-corner:hover .octo-arm {
            animation: octocat-wave 560ms ease-in-out;
        }
        @keyframes octocat-wave {
            0%, 100% { transform: rotate(0) }
            20%, 60% { transform: rotate(-25deg) }
            40%, 80% { transform: rotate(10deg) }
        }
        @media (max-width: 500px) {
            .github-corner:hover .octo-arm {
                animation: none;
            }
            .github-corner .octo-arm {
                animation: octocat-wave 560ms ease-in-out;
            }
        }
    </style>
</head>
<body class="gradient-bg min-h-screen">
    <!-- 添加 GitHub 角标 -->
    <a href="https://github.com/JLinmr/RandomPic-API-Ruom.git" class="github-corner fixed top-0 right-0 z-50" target="_blank" aria-label="View source on GitHub">
        <svg width="120" height="120" viewBox="0 0 250 250" style="fill:#64CEAA; color:#fff; position: absolute; top: 0; border: 0; right: 0;" aria-hidden="true">
            <path d="M0,0 L115,115 L130,115 L142,142 L250,250 L250,0 Z"></path>
            <path d="M128.3,109.0 C113.8,99.7 119.0,89.6 119.0,89.6 C122.0,82.7 120.5,78.6 120.5,78.6 C119.2,72.0 123.4,76.3 123.4,76.3 C127.3,80.9 125.5,87.3 125.5,87.3 C122.9,97.6 130.6,101.9 134.4,103.2" fill="currentColor" style="transform-origin: 130px 106px;" class="octo-arm"></path>
            <path d="M115.0,115.0 C114.9,115.1 118.7,116.5 119.8,115.4 L133.7,101.6 C136.9,99.2 139.9,98.4 142.2,98.6 C133.8,88.0 127.5,74.4 143.8,58.0 C148.5,53.4 154.0,51.2 159.7,51.0 C160.3,49.4 163.2,43.6 171.4,40.1 C171.4,40.1 176.1,42.5 178.8,56.2 C183.1,58.6 187.2,61.8 190.9,65.4 C194.5,69.0 197.7,73.2 200.1,77.6 C213.8,80.2 216.3,84.9 216.3,84.9 C212.7,93.1 206.9,96.0 205.4,96.6 C205.1,102.4 203.0,107.8 198.3,112.5 C181.9,128.9 168.3,122.5 157.7,114.1 C157.9,116.9 156.7,120.9 152.7,124.9 L141.0,136.5 C139.8,137.7 141.6,141.9 141.8,141.8 Z" fill="currentColor" class="octo-body"></path>
        </svg>
    </a>

    <div class="container mx-auto px-4 py-8">
        <nav class="bg-white bg-opacity-80 rounded-lg p-4 shadow mb-8">
            <h1 class="text-3xl font-bold text-center text-gray-800">随机图片 API</h1>
        </nav>

        <section class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
            <div class="bg-white bg-opacity-80 rounded-lg p-6 shadow hover:shadow-lg transition-shadow">
                <h2 class="text-2xl font-semibold mb-4">调用统计</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <p class="text-gray-600">今日调用</p>
                        <p class="text-3xl font-bold text-blue-600" id="todayCount">--</p>
                    </div>
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <p class="text-gray-600">总调用次数</p>
                        <p class="text-3xl font-bold text-green-600" id="totalCount">--</p>
                    </div>
                </div>
                <div id="apiChart" class="mt-4" style="height: 300px;"></div>
            </div>

            <div class="bg-white bg-opacity-80 rounded-lg p-6 shadow hover:shadow-lg transition-shadow relative">
                <h2 class="text-2xl font-semibold mb-4">接口测试</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-600 mb-2">返回类型</label>
                        <select id="type" class="w-full p-2 border rounded border-gray-200 rounded hover:border-blue-500 transition-colors focus:outline-none">
                            <option value="redirect">图片</option>
                            <option value="json">JSON</option>
                            <option value="text">纯文本</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-600 mb-2">分类筛选（可选）</label>
                        <input type="text" id="category" class="w-full p-2 border border-gray-200 rounded hover:border-blue-500 transition-colors focus:outline-none" placeholder="输入分类关键词">
                    </div>
                    <div>
                        <label class="block text-gray-600 mb-2">格式筛选（可选）</label>
                        <input type="text" id="format" class="w-full p-2 border border-gray-200 rounded hover:border-blue-500 transition-colors focus:outline-none" placeholder="如：jpg, png, webp">
                    </div>
                    <button onclick="testAPI()" class="w-full bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 transition">
                        测试接口
                    </button>

                    <div id="textResult" class="mt-4 hidden"></div>
                </div>

                <div id="imageModal" class="hidden opacity-0 transition-all duration-300 absolute inset-0 bg-white bg-opacity-95 backdrop-blur-sm rounded-lg z-10 flex flex-col p-4">
                    <button onclick="closeImageModal()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                    <div class="flex-1 flex flex-col h-full overflow-hidden" id="imageModalContent">
                    </div>
                </div>
            </div>
        </section>
        
        <section class="bg-white bg-opacity-80 rounded-lg p-6 shadow hover:shadow-lg transition-shadow mb-8">
            <h2 class="text-2xl font-semibold mb-6">API 使用文档</h2>
            
            <!-- 基础信息卡片 -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-blue-50 rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-blue-700 mb-3">基础 URL</h3>
                    <div class="bg-white rounded p-3 shadow-sm">
                        <code class="text-blue-600"><?php echo getBaseUrl(); ?>/img.php</code>
                    </div>
                </div>
                
                <div class="bg-green-50 rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-green-700 mb-3">支持的返回格式</h3>
                    <div class="grid grid-cols-3 gap-2">
                        <span class="bg-white rounded px-3 py-2 text-center shadow-sm">图片</span>
                        <span class="bg-white rounded px-3 py-2 text-center shadow-sm">JSON</span>
                        <span class="bg-white rounded px-3 py-2 text-center shadow-sm">文本</span>
                    </div>
                </div>
            </div>

            <!-- 参数说明 -->
            <div class="mb-8">
                <h3 class="text-xl font-semibold mb-4">请求参数</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white rounded-lg overflow-hidden">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left">参数名</th>
                                <th class="px-4 py-3 text-left">类型</th>
                                <th class="px-4 py-3 text-left">说明</th>
                                <th class="px-4 py-3 text-left">示例</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr>
                                <td class="px-4 py-3"><code class="bg-gray-100 px-2 py-1 rounded">type</code></td>
                                <td class="px-4 py-3">string</td>
                                <td class="px-4 py-3">返回类型：redirect/json/text</td>
                                <td class="px-4 py-3"><code>type=json</code></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3"><code class="bg-gray-100 px-2 py-1 rounded">category</code></td>
                                <td class="px-4 py-3">string</td>
                                <td class="px-4 py-3">图片分类筛选</td>
                                <td class="px-4 py-3"><code>category=风景</code></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3"><code class="bg-gray-100 px-2 py-1 rounded">format</code></td>
                                <td class="px-4 py-3">string</td>
                                <td class="px-4 py-3">图片格式筛选</td>
                                <td class="px-4 py-3"><code>format=webp</code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 分类展示 -->
            <div class="mb-8">
                <h3 class="text-xl font-semibold mb-4">支持的分类</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    <?php foreach ($categories as $category): ?>
                    <div class="group">
                        <div class="flex items-center space-x-2 bg-white px-4 py-3 rounded-lg border border-gray-200 hover:border-blue-500 transition-colors">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full bg-blue-500"></div>
                            <span class="text-gray-700 group-hover:text-gray-900 transition-colors">
                                <?php echo $category; ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- 调用示例 -->
            <div>
                <h3 class="text-xl font-semibold mb-4">调用示例</h3>
                <div class="space-y-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-gray-700 mb-2">1. 直接获取随机图片：</p>
                        <code class="bg-white block p-3 rounded shadow-sm"><?php echo getBaseUrl(); ?>/img.php</code>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-gray-700 mb-2">2. 获取 JSON 格式响应：</p>
                        <code class="bg-white block p-3 rounded shadow-sm"><?php echo getBaseUrl(); ?>/img.php?type=json</code>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-gray-700 mb-2">3. 组合参数使用：</p>
                        <code class="bg-white block p-3 rounded shadow-sm mb-2"><?php echo getBaseUrl(); ?>/img.php?type=json&category=风景&format=jpg</code>
                        <code class="bg-white block p-3 rounded shadow-sm"><?php echo getBaseUrl(); ?>/img.php?type=text&category=风景</code>
                    </div>
                </div>
            </div>
        </section>

        <footer class="text-center text-gray-600 mt-8">
            <p class="bg-white bg-opacity-80 rounded-lg p-4 shadow">© 2024 梦爱吃鱼 随机图片 API 服务</p>
        </footer>
    </div>

    <script>
        async function fetchStats() {
            try {
                const { today, total, labels, values } = await fetch('index.php?action=stats')
                    .then(res => res.ok ? res.json() : Promise.reject(`HTTP error! status: ${res.status}`));
                
                document.getElementById('todayCount').textContent = today;
                document.getElementById('totalCount').textContent = total;
                
                echarts.init(document.getElementById('apiChart')).setOption({
                    title: { text: '最近7天调用趋势', textStyle: { fontSize: 14 }, left: 'center' },
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: {
                            type: 'line'
                        },
                        formatter: function(params) {
                            const data = params[0];
                            return `${data.name}<br/>调用次数：${data.value}`;
                        }
                    },
                    grid: { top: '15%', left: '3%', right: '4%', bottom: '3%', containLabel: true },
                    xAxis: { type: 'category', data: labels },
                    yAxis: { type: 'value' },
                    series: [{
                        type: 'line',
                        data: values,
                        smooth: true,
                        areaStyle: {
                            color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                                { offset: 0, color: 'rgba(59, 130, 246, 0.3)' },
                                { offset: 1, color: 'rgba(59, 130, 246, 0.1)' }
                            ])
                        }
                    }]
                });
            } catch (error) {
                console.error('获取统计数据失败:', error);
                ['todayCount', 'totalCount'].forEach(id => 
                    document.getElementById(id).textContent = '加载失败'
                );
            }
        }

        document.addEventListener('DOMContentLoaded', fetchStats);

        function testAPI() {
            const params = ['type', 'category', 'format'].reduce((acc, param) => {
                const value = document.getElementById(param).value;
                return value ? `${acc}&${param}=${encodeURIComponent(value)}` : acc;
            }, `?_t=${Date.now()}`);
            
            const url = `img.php${params}`;
            
            if (document.getElementById('type').value === 'redirect') {
                showImageModal(url);
            } else {
                showTextResult(url);
            }
        }

        function showImageModal(url) {
            const modal = document.getElementById('imageModal');
            const modalContent = document.getElementById('imageModalContent');
            
            modal.classList.remove('hidden', 'opacity-0');
            modalContent.innerHTML = `
                <div class="flex flex-col h-full">
                    <div class="flex-1 flex items-center justify-center min-h-0">
                        <div id="imageLoader" class="flex flex-col items-center">
                            <svg class="animate-spin h-10 w-10 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="mt-4 text-gray-600">图片加载中...</p>
                        </div>
                        <img src="${url}" 
                            class="max-w-full max-h-full object-contain rounded shadow-lg hidden opacity-0 transition-opacity duration-500" 
                            onload="clearTimeout(this.timeoutId); this.classList.remove('hidden'); setTimeout(() => this.classList.remove('opacity-0'), 50); document.getElementById('imageLoader').remove();"
                            onerror="fetch('${url}').then(res => showImageError(res.status, res.statusText))">
                    </div>
                </div>
            `;

            // 设置10秒超时
            const img = modalContent.querySelector('img');
            img.timeoutId = setTimeout(() => {
                if (!img.complete) {
                    showImageError(408, '请求超时');
                }
            }, 10000);
        }

        // 添加公共的错误处理函数
        function getErrorMessage(status) {
            const errorMessages = {
                404: '资源不存在',
                403: '无访问权限',
                408: '请求超时',
                500: '服务器错误',
                503: '服务暂时不可用'
            };
            return errorMessages[status] || '未知错误';
        }

        function showImageError(status, message) {
            const errorText = getErrorMessage(status);
            const modalContent = document.getElementById('imageModalContent');
            modalContent.innerHTML = `
                <div class="flex items-center justify-center h-full">
                    <div class="text-red-500 text-center">
                        <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p>加载失败 (${status})</p>
                        <p class="text-sm mt-2">${errorText}</p>
                    </div>
                </div>
            `;
        }

        function showTextResult(url) {
            const textResult = document.getElementById('textResult');
            textResult.classList.remove('hidden');
            textResult.innerHTML = '<p class="text-blue-600">正在请求数据...</p>';
            
            fetch(url)
                .then(response => response.text().then(text => ({
                    ok: response.ok,
                    status: response.status,
                    text: text
                })))
                .then(({ok, status, text}) => {
                    let content = text;
                    try {
                        // 如果是 JSON，将其转换为单行字符串
                        const jsonContent = JSON.parse(text);
                        content = JSON.stringify(jsonContent);
                    } catch {}
                    
                    textResult.innerHTML = `
                        <div class="${ok ? 'bg-gray-100' : 'bg-red-50'} p-4 rounded">
                            <div class="${ok ? 'text-gray-800' : 'text-red-600'} break-all">
                                ${content}
                            </div>
                        </div>
                    `;
                })
                .catch(() => {
                    textResult.innerHTML = `
                        <div class="bg-red-50 p-4 rounded">
                            <div class="text-red-600">网络错误</div>
                        </div>
                    `;
                });
        }

        function closeImageModal() {
            const modal = document.getElementById('imageModal');
            modal.classList.add('opacity-0');
            setTimeout(() => modal.classList.add('hidden'), 300);
        }

        document.getElementById('imageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeImageModal();
            }
        });
    </script>
</body>
</html> 