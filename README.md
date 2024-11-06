# Random Image API

随机图片 API 服务，支持多种返回格式和图片筛选。

## 特性

- 多种返回格式(重定向/JSON/文本)
- 图片分类和格式筛选 
- 内置速率限制和调用统计
- Web UI 界面

## 快速开始

1. 克隆并配置
```bash
git clone https://github.com/JLinmr/RandomPic-API-Ruom.git
```

2. 环境要求
- PHP 7.4+
- 目录可写权限

3. 配置文件
在 `config.php` 中可以修改以下配置:
```php
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

    // 错误信息
    // 静态资源
]
```

## API 使用

基础URL: `https://dev.ruom.top/img.php`

### 参数

| 参数 | 说明 | 可选值 | 默认值 |
|------|------|--------|--------|
| type | 返回类型 | redirect/json/text | redirect |
| category | 分类 | 任意字符串 | - |
| format | 格式 | jpg/png/webp等 | - |

### 示例

```http
# 直接获取
GET /img.php

# JSON响应
GET /img.php?type=json

# 筛选分类
GET /img.php?category=动漫&format=webp
```

## 图片配置

在 img.txt 中按行添加图片URL。支持两种分类方式:

### 1. 目录分类
```
https://example.com/二次元/初音未来.png
https://example.com/风景/东京.jpg
```

### 2. 标签分类
```
https://example.com/001.png [二次元,初音]
https://example.com/002.jpg [风景,东京]
```

### 配置说明

- 每行一个图片URL
- 建议选择一种分类方式并保持一致
- 确保图片URL可直接访问
- 支持常见图片格式(jpg/png/gif/webp等)

## 开源协议

MIT License