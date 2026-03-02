<?php
/**
 * PonponPay WHMCS Callback Handler
 *
 * 处理来自PonponPay系统的支付回调通知
 *
 * @author PonponPay开发团队
 * @version 2.0.0
 */

/**
 * 定位 WHMCS 根目录（兼容软链接部署）。
 */
function ponponpay_resolve_whmcs_root()
{
    $candidates = [];

    if (!empty($_SERVER['SCRIPT_FILENAME'])) {
        $candidates[] = dirname(dirname(dirname((string)$_SERVER['SCRIPT_FILENAME'])));
    }
    if (!empty($_SERVER['DOCUMENT_ROOT'])) {
        $candidates[] = rtrim((string)$_SERVER['DOCUMENT_ROOT'], '/\\');
    }
    $candidates[] = dirname(__DIR__, 3);

    foreach ($candidates as $root) {
        if ($root && is_file($root . '/init.php')) {
            return $root;
        }
    }

    return null;
}

$whmcsRoot = ponponpay_resolve_whmcs_root();
if (!$whmcsRoot) {
    http_response_code(500);
    exit('WHMCS bootstrap not found');
}

require_once $whmcsRoot . '/init.php';
require_once $whmcsRoot . '/includes/gatewayfunctions.php';
require_once $whmcsRoot . '/includes/invoicefunctions.php';

// 引入配置文件以确保 ponponpay_get_api_url 函数可用
if (file_exists($whmcsRoot . '/includes/hooks/ponponpay_config.php')) {
    require_once $whmcsRoot . '/includes/hooks/ponponpay_config.php';
}

# 注释掉Capsule，使用传统方法

/**
 * 映射网络名称到后端枚举
 */
function mapNetworkToBE($network)
{
    $mapping = [
        'Tron' => 'TRC20',
        'TRC20' => 'TRC20',
        'Ethereum' => 'ERC20',
        'ERC20' => 'ERC20',
        'Polygon' => 'POLYGON',
        'POLYGON' => 'POLYGON',
        'Solana' => 'SOLANA',
        'SOLANA' => 'SOLANA',
        'SOL' => 'SOLANA'
    ];
    return $mapping[$network] ?? 'TRC20';
}

/**
 * 调用后端API
 */
function callBackendAPI($url, $data, $apiKey)
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'X-API-Key: ' . $apiKey,
            'User-Agent: WHMCS-PonponPay-Callback/2.0'
        ]
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        throw new Exception('API请求失败: ' . $error);
    }

    if ($httpCode !== 200) {
        throw new Exception('API返回错误状态码: ' . $httpCode . ', 响应: ' . $response);
    }

    $decoded = json_decode($response, true);
    if ($decoded === null) {
        throw new Exception('API响应JSON解析失败: ' . $response);
    }

    return $decoded;
}

/**
 * 从回调 order_no 解析发票ID。
 * 优先解析 WHMCS_{invoiceId}_{hash}，若不匹配则回查后端订单详情。
 */
function resolveInvoiceIdFromOrderNo($orderNo, $gatewayParams)
{
    if (preg_match('/^WHMCS_(\d+)_[a-zA-Z0-9]+$/', $orderNo, $matches)) {
        return (int)$matches[1];
    }

    $apiKey = trim((string)($gatewayParams['api_key'] ?? ''));
    if ($apiKey === '' || !function_exists('ponponpay_get_api_url')) {
        return 0;
    }

    $apiUrl = rtrim(ponponpay_get_api_url(), '/');
    $detailUrl = $apiUrl . '/api/v1/pay/sdk/order/detail';

    $candidates = [
        ['trade_id' => $orderNo],
        ['mch_order_id' => $orderNo],
    ];
    foreach ($candidates as $payload) {
        try {
            $resp = callBackendAPI($detailUrl, $payload, $apiKey);
            $mchOrderId = $resp['data']['mch_order_id'] ?? '';
            if (is_string($mchOrderId) && preg_match('/^WHMCS_(\d+)_[a-zA-Z0-9]+$/', $mchOrderId, $matches)) {
                return (int)$matches[1];
            }
        } catch (Throwable $e) {
            error_log("[PonponPay Callback] 订单回查失败(" . json_encode($payload, JSON_UNESCAPED_UNICODE) . "): " . $e->getMessage());
        }
    }

    return 0;
}

// ponponpay_get_api_url 函数已移至 includes/hooks/ponponpay_config.php

/**
 * 验证回调来源和签名
 */
function validateCallback($data, $secret)
{
    if (empty($data['sign'])) {
        return false;
    }

    $sign = $data['sign'];
    unset($data['sign']);
    ksort($data);
    $queryString = http_build_query($data);
    $expectedSign = md5($queryString . '&key=' . $secret);

    return hash_equals($expectedSign, $sign);
}

/**
 * 记录回调日志
 */
function logCallback($data, $message = 'Callback received')
{
    if (function_exists('logTransaction')) {
        logTransaction('PonponPay', $data, $message);
    }
}

/**
 * 兼容不同运行环境获取请求头，避免 getallheaders 不存在导致 Fatal Error。
 */
function getRequestHeadersSafe()
{
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        if (is_array($headers)) {
            return $headers;
        }
    }

    $headers = [];
    foreach ($_SERVER as $key => $value) {
        if (strpos($key, 'HTTP_') !== 0) {
            continue;
        }
        $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
        $headers[$headerName] = $value;
    }

    return $headers;
}

/**
 * 校验并消费 nonce（防重放，5分钟窗口）。
 */
function consumeCallbackNonce($nonce, $timestamp)
{
    if (!preg_match('/^[A-Za-z0-9]{16,128}$/', $nonce)) {
        return false;
    }

    $baseDir = __DIR__ . '/../../../storage/cache/ponponpay_nonce';
    if (!is_dir($baseDir) && !@mkdir($baseDir, 0755, true) && !is_dir($baseDir)) {
        $baseDir = rtrim(sys_get_temp_dir(), '/\\') . '/ponponpay_nonce';
        if (!is_dir($baseDir) && !@mkdir($baseDir, 0755, true) && !is_dir($baseDir)) {
            return false;
        }
    }

    $now = time();
    foreach (glob($baseDir . '/*.nonce') ?: [] as $file) {
        if (($now - (int)@filemtime($file)) > 600) {
            @unlink($file);
        }
    }

    $nonceFile = $baseDir . '/' . hash('sha256', $timestamp . '|' . $nonce) . '.nonce';
    $fp = @fopen($nonceFile, 'x');
    if ($fp === false) {
        return false;
    }
    @fwrite($fp, (string)$now);
    @fclose($fp);

    return true;
}

/**
 * 计算回调签名（HMAC-SHA256）。
 */
function buildCallbackSignature($timestamp, $nonce, $rawBody, $apiKey)
{
    $keyHash = hash('sha256', trim((string)$apiKey));
    $payload = $timestamp . "\n" . $nonce . "\n" . $rawBody;
    return hash_hmac('sha256', $payload, $keyHash);
}

/**
 * 主要回调处理逻辑
 */
try {
    // 获取回调数据
    $input = file_get_contents('php://input');
    error_log("[PonponPay Callback] ==================== 新回调请求 ====================");
    error_log("[PonponPay Callback] 原始输入: " . $input);
    error_log("[PonponPay Callback] Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));
    error_log("[PonponPay Callback] Request Method: " . ($_SERVER['REQUEST_METHOD'] ?? 'unknown'));
    
    $data = json_decode($input, true);
    error_log("[PonponPay Callback] JSON 解析结果: " . ($data ? 'SUCCESS' : 'FAILED'));

    if (!$data) {
        // 如果不是JSON，尝试从POST获取
        error_log("[PonponPay Callback] JSON 解析失败，尝试使用 POST 数据");
        $data = $_POST;
        error_log("[PonponPay Callback] POST 数据: " . json_encode($_POST, JSON_UNESCAPED_UNICODE));
    } else {
        error_log("[PonponPay Callback] 解析后的数据: " . json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    // 记录回调日志（包含原始输入用于调试）
    logCallback([
        'raw_input' => $input,
        'parsed_data' => $data,
        'post_data' => $_POST
    ], 'Payment callback received');

    // 验证必要字段
    error_log("[PonponPay Callback] 开始验证必要字段...");
    error_log("[PonponPay Callback] order_no: " . ($data['order_no'] ?? '【缺失】'));
    error_log("[PonponPay Callback] status: " . ($data['status'] ?? '【缺失】'));
    
    if (empty($data['order_no']) || empty($data['status'])) {
        error_log("[PonponPay Callback] ❌ 验证失败：缺少必要字段");
        error_log("[PonponPay Callback] 所有接收到的字段: " . json_encode(array_keys($data), JSON_UNESCAPED_UNICODE));
        logCallback([
            'error' => 'Missing required fields',
            'order_no' => $data['order_no'] ?? 'missing',
            'status' => $data['status'] ?? 'missing',
            'all_data' => $data
        ], 'Invalid callback data - missing fields');
        http_response_code(400);
        echo 'Invalid callback data: missing order_no or status';
        exit;
    }
    error_log("[PonponPay Callback] ✅ 必要字段验证通过");

    // 从订单号中提取发票ID
    // 主格式: WHMCS_{invoice_id}_{hash}
    // 兼容格式: 非 WHMCS 格式时回查后端订单详情解析 mch_order_id
    error_log("[PonponPay Callback] 开始验证订单号格式...");
    error_log("[PonponPay Callback] 订单号: " . $data['order_no']);

    // 获取网关配置（后续签名验证和回查都需要）
    error_log("[PonponPay Callback] 获取网关配置...");
    $gatewayParams = getGatewayVariables('ponponpay');
    if (!$gatewayParams['type']) {
        error_log("[PonponPay Callback] ❌ 网关未配置");
        http_response_code(500);
        echo 'Gateway not configured';
        exit;
    }
    error_log("[PonponPay Callback] ✅ 网关配置正常");

    $invoiceId = resolveInvoiceIdFromOrderNo($data['order_no'], $gatewayParams);
    if ($invoiceId <= 0) {
        error_log("[PonponPay Callback] ❌ 订单号格式错误");
        error_log("[PonponPay Callback] 期望格式: WHMCS_{invoice_id}_{hash} 或可回查到对应 mch_order_id");
        error_log("[PonponPay Callback] 实际格式: " . $data['order_no']);
        logCallback([
            'error' => 'Invalid order number format',
            'order_no' => $data['order_no'],
            'expected_format' => 'WHMCS_{invoice_id}_{hash} or resolvable to mch_order_id'
        ], 'Invalid order number format');
        http_response_code(400);
        echo 'Invalid order number format: ' . $data['order_no'];
        exit;
    }

    error_log("[PonponPay Callback] ✅ 订单号格式正确，提取到发票ID: " . $invoiceId);

    // 严格回调鉴权：签名 + 时间戳 + nonce，不再兼容旧 API Key 直比逻辑。
    $expectedApiKey = trim((string)($gatewayParams['api_key'] ?? ''));
    $receivedPrefix = $_SERVER['HTTP_X_KEY_PREFIX'] ?? '';
    $receivedTimestamp = $_SERVER['HTTP_X_TIMESTAMP'] ?? '';
    $receivedNonce = $_SERVER['HTTP_X_NONCE'] ?? '';
    $receivedSignature = strtolower(trim((string)($_SERVER['HTTP_X_SIGNATURE'] ?? '')));

    error_log("[PonponPay Callback] 开始验证签名头...");
    error_log("[PonponPay Callback] X-Key-Prefix: " . ($receivedPrefix ? substr($receivedPrefix, 0, 12) . '...' : '【缺失】'));
    error_log("[PonponPay Callback] X-Timestamp: " . ($receivedTimestamp ?: '【缺失】'));
    error_log("[PonponPay Callback] X-Nonce: " . ($receivedNonce ? substr($receivedNonce, 0, 8) . '...' : '【缺失】'));
    error_log("[PonponPay Callback] X-Signature: " . ($receivedSignature ? substr($receivedSignature, 0, 12) . '...' : '【缺失】'));

    if ($expectedApiKey === '') {
        error_log("[PonponPay Callback] ❌ 签名验证失败：网关 API Key 未配置");
        http_response_code(500);
        echo 'Gateway API key not configured';
        exit;
    }

    if ($receivedPrefix === '' || $receivedTimestamp === '' || $receivedNonce === '' || $receivedSignature === '') {
        error_log("[PonponPay Callback] ❌ 签名验证失败：缺少必要签名头");
        logCallback(['error' => 'Missing signature headers', 'headers' => getRequestHeadersSafe()], 'Signature validation failed');
        http_response_code(401);
        echo 'Unauthorized: Missing signature headers';
        exit;
    }

    if (!ctype_digit($receivedTimestamp)) {
        error_log("[PonponPay Callback] ❌ 签名验证失败：时间戳格式非法");
        http_response_code(401);
        echo 'Unauthorized: Invalid timestamp';
        exit;
    }

    $now = time();
    $ts = (int)$receivedTimestamp;
    if (abs($now - $ts) > 300) {
        error_log("[PonponPay Callback] ❌ 签名验证失败：时间戳超出有效窗口");
        http_response_code(401);
        echo 'Unauthorized: Timestamp expired';
        exit;
    }

    $expectedPrefix = substr($expectedApiKey, 0, 12);
    if ($receivedPrefix !== $expectedPrefix) {
        error_log("[PonponPay Callback] ❌ 签名验证失败：Key Prefix 不匹配");
        http_response_code(401);
        echo 'Unauthorized: Invalid key prefix';
        exit;
    }

    if (!consumeCallbackNonce($receivedNonce, $receivedTimestamp)) {
        error_log("[PonponPay Callback] ❌ 签名验证失败：重复 nonce 或 nonce 无效");
        http_response_code(409);
        echo 'Conflict: Nonce already used';
        exit;
    }

    $expectedSignature = buildCallbackSignature($receivedTimestamp, $receivedNonce, $input, $expectedApiKey);
    if (!hash_equals($expectedSignature, $receivedSignature)) {
        error_log("[PonponPay Callback] ❌ 签名验证失败：签名不匹配");
        logCallback([
            'error' => 'Invalid signature',
            'received_signature' => substr($receivedSignature, 0, 12) . '...',
            'expected_signature' => substr($expectedSignature, 0, 12) . '...'
        ], 'Signature validation failed');
        http_response_code(401);
        echo 'Unauthorized: Invalid signature';
        exit;
    }

    error_log("[PonponPay Callback] ✅ 签名验证通过");
    
    // 检查发票是否存在
    error_log("[PonponPay Callback] 检查发票是否存在: " . $invoiceId);
    $invoice = localAPI('GetInvoice', ['invoiceid' => $invoiceId]);
    if ($invoice['result'] !== 'success') {
        error_log("[PonponPay Callback] ❌ 发票不存在: " . $invoiceId);
        http_response_code(404);
        echo 'Invoice not found';
        exit;
    }
    error_log("[PonponPay Callback] ✅ 发票存在，当前状态: " . $invoice['status']);

    // 检查发票状态
    if (strtolower($invoice['status']) === 'paid') {
        error_log("[PonponPay Callback] ⚠️ 发票已支付，跳过处理");
        echo 'OK'; // 已经支付过了
        exit;
    }

    // 处理不同的支付状态
    // 支持数字状态码：1-等待支付，2-支付成功，3-已过期，4-取消支付，5-人工充值
    error_log("[PonponPay Callback] 处理支付状态: " . $data['status']);
    
    $status = $data['status'];
    // 如果是字符串状态，转为小写
    if (!is_numeric($status)) {
        $status = strtolower($status);
    }
    
    switch ($status) {
        // 数字状态码
        case 1: // 等待支付
        case '1':
            error_log("[PonponPay Callback] 📝 处理等待支付状态 (状态码: 1)");
            handlePendingPayment($invoiceId, $data);
            break;

        case 2: // 支付成功
        case 5: // 人工充值
        case '2':
        case '5':
            error_log("[PonponPay Callback] 📝 处理支付成功状态 (状态码: {$status})");
            handleSuccessfulPayment($invoiceId, $data, $gatewayParams);
            break;

        case 3: // 已过期
        case '3':
            error_log("[PonponPay Callback] 📝 处理支付过期状态 (状态码: 3)");
            handleExpiredPayment($invoiceId, $data);
            break;

        case 4: // 取消支付
        case '4':
            error_log("[PonponPay Callback] 📝 处理支付取消状态 (状态码: 4)");
            handleFailedPayment($invoiceId, $data);
            break;

        default:
            error_log("[PonponPay Callback] ❌ 未知的支付状态: " . $data['status']);
            error_log("[PonponPay Callback] 允许的状态: 1-等待支付, 2-支付成功, 3-已过期, 4-取消支付, 5-人工充值");
            error_log("[PonponPay Callback] 或文本状态: paid, success, completed, failed, error, expired, timeout, pending, waiting");
            logCallback([
                'error' => 'Unknown payment status',
                'status' => $data['status'],
                'allowed_statuses' => [
                    'numeric' => ['1-pending', '2-paid', '3-expired', '4-failed', '5-paid'],
                    'text' => ['paid', 'success', 'completed', 'failed', 'error', 'expired', 'timeout', 'pending', 'waiting']
                ],
                'full_data' => $data
            ], 'Unknown payment status: ' . $data['status']);
            http_response_code(400);
            echo 'Unknown payment status: ' . $data['status'];
            exit;
    }

    error_log("[PonponPay Callback] ✅ 回调处理完成，返回 OK");
    error_log("[PonponPay Callback] ==================== 回调处理结束 ====================");
    echo 'OK';

} catch (Throwable $e) {
    error_log("[PonponPay Callback] ❌❌❌ 异常发生 ❌❌❌");
    error_log("[PonponPay Callback] 异常信息: " . $e->getMessage());
    error_log("[PonponPay Callback] 异常位置: " . $e->getFile() . ':' . $e->getLine());
    error_log("[PonponPay Callback] 堆栈跟踪:\n" . $e->getTraceAsString());
    logCallback(['error' => $e->getMessage()], 'Callback error');
    http_response_code(500);
    echo 'Internal error';
}

/**
 * 处理支付成功
 */
function handleSuccessfulPayment($invoiceId, $data, $gatewayParams)
{
    error_log("[PonponPay Callback] >>> 进入 handleSuccessfulPayment 函数");
    error_log("[PonponPay Callback] 发票ID: " . $invoiceId);
    error_log("[PonponPay Callback] 交易哈希: " . ($data['tx_hash'] ?? $data['transaction_id'] ?? 'N/A'));
    
    try {
        // 验证支付金额
        $invoiceData = localAPI('GetInvoice', ['invoiceid' => $invoiceId]);
        $expectedAmount = floatval($invoiceData['total']);
        error_log("[PonponPay Callback] 发票应付金额: " . $expectedAmount . " " . $invoiceData['currencycode']);

        // 添加发票支付记录（订单已在后端创建，这里只需要标记 WHMCS 发票为已支付）
        error_log("[PonponPay Callback] 添加发票支付记录...");
        $transactionId = $data['transaction_id'] ?? $data['tx_hash'] ?? $data['order_no'];
        error_log("[PonponPay Callback] 交易ID: " . $transactionId);
        
        addInvoicePayment(
            $invoiceId,
            $transactionId,
            $expectedAmount,
            0, // 无手续费
            'ponponpay'
        );

        error_log("[PonponPay Callback] ✅ 发票支付记录已添加");
        logCallback($data, 'Payment successful for invoice: ' . $invoiceId);

        // 发送支付成功邮件（可选）
        if (!empty($gatewayParams['send_email'])) {
            sendPaymentConfirmationEmail($invoiceId);
        }

    } catch (Throwable $e) {
        $message = $e->getMessage();
        // 幂等容错：重复交易或已支付视为成功，避免回调重试风暴。
        if (stripos($message, 'Duplicate Transaction ID') !== false
            || stripos($message, 'already exists') !== false
            || stripos($message, 'already paid') !== false) {
            error_log("[PonponPay Callback] ⚠️ 幂等命中，视为成功: " . $message);
            logCallback(['warning' => $message, 'invoice_id' => $invoiceId], 'Idempotent success');
            return;
        }

        error_log("[PonponPay Callback] ❌ handleSuccessfulPayment 异常: " . $message);
        logCallback(['error' => $e->getMessage(), 'invoice_id' => $invoiceId], 'Error processing successful payment');
        throw $e;
    }
    
    error_log("[PonponPay Callback] <<< 退出 handleSuccessfulPayment 函数");
}

/**
 * 处理支付失败
 */
function handleFailedPayment($invoiceId, $data)
{
    error_log("[PonponPay Callback] 处理支付失败，发票ID: " . $invoiceId);
    logCallback($data, 'Payment failed for invoice: ' . $invoiceId);
    
    // WHMCS 发票保持未支付状态，不需要额外处理
    // 后端订单状态由后端系统自行管理，回调无需更新
}

/**
 * 处理支付过期
 */
function handleExpiredPayment($invoiceId, $data)
{
    error_log("[PonponPay Callback] 处理支付过期，发票ID: " . $invoiceId);
    logCallback($data, 'Payment expired for invoice: ' . $invoiceId);
    
    // WHMCS 发票保持未支付状态，不需要额外处理
    // 后端订单状态由后端系统自行管理，回调无需更新
}

/**
 * 处理等待中的支付
 */
function handlePendingPayment($invoiceId, $data)
{
    error_log("[PonponPay Callback] 处理等待支付，发票ID: " . $invoiceId);
    logCallback($data, 'Payment pending for invoice: ' . $invoiceId);
    
    // WHMCS 发票保持未支付状态，等待后续支付完成回调
    // 后端订单状态由后端系统自行管理，回调无需更新
}

// 注：以下后端订单创建和状态更新函数已废弃
// 订单应在创建支付时已在后端创建，回调仅用于更新 WHMCS 发票状态
// 后端订单状态由后端系统自行管理，无需通过回调更新


/**
 * 发送支付确认邮件
 */
function sendPaymentConfirmationEmail($invoiceId)
{
    try {
        // 这里可以自定义邮件发送逻辑
        // WHMCS会自动发送默认的支付确认邮件

        // 如果需要自定义邮件，可以使用以下代码：
        /*
        $invoice = mysql_fetch_array(mysql_query("SELECT * FROM tblinvoices WHERE id = '{$invoiceId}'"));
        $client = mysql_fetch_array(mysql_query("SELECT * FROM tblclients WHERE id = '{$invoice['userid']}'"));

        $emailTemplate = 'payment_confirmation_ponponpay';
        $emailVars = [
            'invoice_id' => $invoiceId,
            'client_name' => $client['firstname'] . ' ' . $client['lastname'],
            'amount' => $invoice['total'],
            'currency' => $invoice['currency']
        ];

        sendMessage($emailTemplate, $invoice['userid'], $emailVars);
        */

    } catch (Exception $e) {
        logCallback(['error' => $e->getMessage()], 'Error sending confirmation email');
    }
}
