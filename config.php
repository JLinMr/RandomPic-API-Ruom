<?php
return [
    // API 限流配置
    'rate_limit' => 60,     // 每个时间窗口内的最大请求次数
    'rate_window' => 60,    // 时间窗口大小(秒)
    
    // 文件路径配置
    'image_file' => 'img.txt',              // 存储图片链接的文本文件
    'stats_file' => __DIR__ . '/stats.json', // API调用统计数据的JSON文件
    
    // 图片分类配置
    'categories' => [
        '动漫', '风景', '人物', '游戏', 
        '美食', '壁纸', '美女', '帅哥'
    ],
    
    // 静态资源CDN配置
    'cdn_assets' => [
        'bg' => 'https://cdn.npmmirror.com/packages/pixpro/1.7.6/files/static/images/bg.webp',      // 页面背景图
        'tailwind' => 'https://lib.baomitu.com/tailwindcss/2.2.19/tailwind.min.css',                // TailwindCSS样式库
        'echarts' => 'https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js'               // ECharts图表库
    ]
]; 