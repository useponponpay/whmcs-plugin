<?php
/**
 * PonponPay WHMCS Callback Handler
 *
 * 处理来自PonponPay系统的支付回调通知
 *
 * @author PonponPay开发团队
 * @version 2.0.0
 */

require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

// 引入配置文件以确保 ponponpay_get_api_url 函数可用
if (file_exists(__DIR__ . '/../../../includes/hooks/ponponpay_config.php')) {
    require_once __DIR__ . '/../../../includes/hooks/ponponpay_config.php';
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
            'Authorization: Bearer ' . $apiKey,
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
    // 订单号格式: WHMCS_{invoice_id}_{hash}
    error_log("[PonponPay Callback] 开始验证订单号格式...");
    error_log("[PonponPay Callback] 订单号: " . $data['order_no']);
    
    if (!preg_match('/^WHMCS_(\d+)_[a-zA-Z0-9]+$/', $data['order_no'], $matches)) {
        error_log("[PonponPay Callback] ❌ 订单号格式错误");
        error_log("[PonponPay Callback] 期望格式: WHMCS_{invoice_id}_{hash}");
        error_log("[PonponPay Callback] 实际格式: " . $data['order_no']);
        logCallback([
            'error' => 'Invalid order number format',
            'order_no' => $data['order_no'],
            'expected_format' => 'WHMCS_{invoice_id}_{hash}'
        ], 'Invalid order number format');
        http_response_code(400);
        echo 'Invalid order number format: ' . $data['order_no'];
        exit;
    }

    $invoiceId = (int)$matches[1];
    error_log("[PonponPay Callback] ✅ 订单号格式正确，提取到发票ID: " . $invoiceId);

    // 获取网关配置
    error_log("[PonponPay Callback] 获取网关配置...");
    $gatewayParams = getGatewayVariables('ponponpay');
    if (!$gatewayParams['type']) {
        error_log("[PonponPay Callback] ❌ 网关未配置");
        http_response_code(500);
        echo 'Gateway not configured';
        exit;
    }
    error_log("[PonponPay Callback] ✅ 网关配置正常");

    // 验证 API Key
    $receivedApiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
    $expectedApiKey = $gatewayParams['api_key'] ?? '';
    
    error_log("[PonponPay Callback] 开始验证 API Key...");
    error_log("[PonponPay Callback] 接收到的 API Key: " . ($receivedApiKey ? substr($receivedApiKey, 0, 8) . '...' : '【缺失】'));
    error_log("[PonponPay Callback] 期望的 API Key: " . ($expectedApiKey ? substr($expectedApiKey, 0, 8) . '...' : '【缺失】'));
    
    if (empty($receivedApiKey)) {
        error_log("[PonponPay Callback] ❌ API Key 验证失败：缺少 X-API-Key header");
        logCallback(['error' => 'Missing API Key', 'headers' => getallheaders()], 'API Key validation failed');
        http_response_code(401);
        echo 'Unauthorized: Missing API Key';
        exit;
    }
    
    if ($receivedApiKey !== $expectedApiKey) {
        error_log("[PonponPay Callback] ❌ API Key 验证失败：密钥不匹配");
        logCallback(['error' => 'Invalid API Key', 'received' => substr($receivedApiKey, 0, 8) . '...'], 'API Key validation failed');
        http_response_code(401);
        echo 'Unauthorized: Invalid API Key';
        exit;
    }
    
    error_log("[PonponPay Callback] ✅ API Key 验证通过");
    
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

} catch (Exception $e) {
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

    } catch (Exception $e) {
        error_log("[PonponPay Callback] ❌ handleSuccessfulPayment 异常: " . $e->getMessage());
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
