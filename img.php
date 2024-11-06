<?php
/**
 * 随机图片 API 主文件
 * 支持多种返回格式、分类筛选和格式筛选
 */

// 加载配置文件
$config = require __DIR__ . '/config.php';
$filename = $config['image_file'];
$rate_limit = $config['rate_limit'];
$rate_window = $config['rate_window'];

/**
 * 统一错误响应处理
 * @param string $msg 错误信息
 * @param int $code HTTP状态码
 */
function error($msg, $code = 500) {
    http_response_code($code);
    die(json_encode(['code' => $code, 'error' => $msg, 'timestamp' => time()]));
}

// 验证返回类型参数
$type = strtolower($_GET['type'] ?? 'redirect');
if (!in_array($type, ['json', 'redirect', 'text'])) {
    error('无效的返回类型', 400);
}

/**
 * 请求速率限制处理
 * 使用文件系统实现简单的访问频率控制
 */
$rate_key = "cache/rate_limit_" . $_SERVER['REMOTE_ADDR'];
$current_time = time();
$requests = @json_decode(@file_get_contents($rate_key), true) ?: ['count' => 0, 'window_start' => $current_time];

if ($current_time - $requests['window_start'] >= $rate_window) {
    $requests = ['count' => 1, 'window_start' => $current_time];
} elseif (++$requests['count'] > $rate_limit) {
    error('请求过于频繁，请稍后再试', 429);
}

@file_put_contents($rate_key, json_encode($requests));

/**
 * 图片筛选逻辑
 * 支持按分类和文件格式筛选
 */
$pics = array_filter(file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
$category = $_GET['category'] ?? '';
$format = strtolower($_GET['format'] ?? '');

if ($category || $format) {
    $pics = array_filter($pics, function($url) use ($category, $format) {
        $match = true;
        if ($category) {
            // 支持两种分类方式：URL路径匹配和标签匹配
            $match = strpos($url, "/{$category}/") !== false || 
                    (preg_match('/\[(.*?)\]/', $url, $m) && in_array($category, explode(',', $m[1])));
        }
        if ($format && $match) {
            $match = preg_match("/\.{$format}$/i", $url);
        }
        return $match;
    });
}

if (empty($pics)) {
    error('没有找到符合条件的图片', 404);
}

/**
 * 更新API调用统计
 * 记录每日和总调用次数
 */
function updateStats() {
    $data_file = __DIR__ . '/stats.json';
    $today = date('Y-m-d');
    
    $fp = fopen($data_file, 'c+');
    if (!flock($fp, LOCK_EX)) return;
    
    try {
        $data = json_decode(fread($fp, filesize($data_file) ?: 1024), true) ?: [
            'stats' => ['daily' => [$today => 0], 'total' => 0]
        ];
        
        $data['stats']['daily'][$today] = ($data['stats']['daily'][$today] ?? 0) + 1;
        $data['stats']['total'] = ($data['stats']['total'] ?? 0) + 1;
        
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($data, JSON_PRETTY_PRINT));
    } finally {
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}

/**
 * 处理响应
 * 支持三种返回格式：重定向、JSON、纯文本
 */
try {
    updateStats();
    $pic = $pics[array_rand($pics)];
    
    // 清理URL中的标签信息
    $pic = preg_replace('/\s*\[.*?\]\s*/', '', $pic);
    
    switch($type) {
        case 'json':
            header('Content-type: application/json; charset=utf-8');
            echo json_encode(['code' => 200, 'pic' => $pic]);
            break;
        case 'text':
            header('Content-type: text/plain; charset=utf-8');
            echo $pic;
            break;
        default:
            header("Location: $pic");
    }
    exit;
} catch (Exception $e) {
    error($e->getMessage());
}