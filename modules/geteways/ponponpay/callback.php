<?php
/**
 * PonponPay Callback Handler
 * 处理支付回调和状态检查
 */

require_once '../../../init.php';

use WHMCS\Database\Capsule;

// 安全检查
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * 获取支付网关配置
 */
function getPonponPayConfig() {
    $gatewayParams = getGatewayVariables('ponponpay');
    return $gatewayParams;
}

/**
 * 记录日志
 */
function ponponpayLog($message, $data = []) {
    logTransaction('ponponpay', $data, $message);
}

/**
 * 验证回调签名
 */
function verifyCallbackSignature($data, $signature, $apiKey) {
    $computedSignature = hash_hmac('sha256', json_encode($data), $apiKey);
    return hash_equals($signature, $computedSignature);
}

/**
 * 处理支付回调
 */
function handlePaymentCallback() {
    $config = getPonponPayConfig();

    if (empty($config) || $config['type'] !== 'ponponpay') {
        http_response_code(400);
        die('Gateway not configured');
    }

    // 获取回调数据
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        http_response_code(400);
        die('Invalid JSON data');
    }

    // 验证签名
    $signature = $_SERVER['HTTP_X_PONPONPAY_SIGNATURE']
        ?? '';
    if (!verifyCallbackSignature($data, $signature, $config['api_key'])) {
        ponponpayLog('回调签名验证失败', $data);
        http_response_code(401);
        die('Invalid signature');
    }

    // 处理不同类型的回调
    $orderNo = $data['order_no'] ?? '';
    $status = $data['status'] ?? '';
    $txHash = $data['tx_hash'] ?? '';
    $actualAmount = $data['actual_amount'] ?? 0;

    if (empty($orderNo)) {
        http_response_code(400);
        die('Missing order number');
    }

    // 查找对应的发票
    $invoice = Capsule::table('tblinvoices')
        ->where(function ($query) use ($orderNo) {
            $query->where('notes', 'LIKE', "%ponponpay_order:{$orderNo}%");
        })
        ->first();

    if (!$invoice) {
        ponponpayLog('未找到对应发票', ['order_no' => $orderNo]);
        http_response_code(404);
        die('Invoice not found');
    }

    $invoiceId = $invoice->id;

    // 根据状态处理
    switch ($status) {
        case 'paid':
            // 检查是否已经支付
            if ($invoice->status === 'Paid') {
                ponponpayLog('发票已支付，忽略重复回调', ['invoice_id' => $invoiceId, 'order_no' => $orderNo]);
                break;
            }

            // 标记为已支付
            $success = addInvoicePayment(
                $invoiceId,
                $txHash, // 交易ID
                $actualAmount,
                0, // 手续费
                'ponponpay'
            );

            if ($success) {
                ponponpayLog('支付成功', [
                    'invoice_id' => $invoiceId,
                    'order_no' => $orderNo,
                    'tx_hash' => $txHash,
                    'amount' => $actualAmount
                ]);

                // 发送支付确认邮件
                sendMessage('Invoice Payment Confirmation', $invoice->userid);
            } else {
                ponponpayLog('支付处理失败', [
                    'invoice_id' => $invoiceId,
                    'order_no' => $orderNo
                ]);
            }
            break;

        case 'failed':
        case 'expired':
            ponponpayLog('支付失败或过期', [
                'invoice_id' => $invoiceId,
                'order_no' => $orderNo,
                'status' => $status
            ]);
            break;

        default:
            ponponpayLog('未知支付状态', [
                'invoice_id' => $invoiceId,
                'order_no' => $orderNo,
                'status' => $status
            ]);
            break;
    }

    // 返回成功响应
    echo json_encode(['success' => true]);
}

/**
 * 检查支付状态
 */
function checkPaymentStatus() {
    $invoiceId = $_POST['invoice_id'] ?? 0;

    if (!$invoiceId) {
        echo json_encode(['success' => false, 'message' => '缺少发票ID']);
        return;
    }

    $config = getPonponPayConfig();

    if (empty($config) || $config['type'] !== 'ponponpay') {
        echo json_encode(['success' => false, 'message' => '网关未配置']);
        return;
    }

    // 查找发票
    $invoice = Capsule::table('tblinvoices')->find($invoiceId);

    if (!$invoice) {
        echo json_encode(['success' => false, 'message' => '发票不存在']);
        return;
    }

    // 从备注中提取订单号
    preg_match('/ponponpay_order:([^,\s]+)/', $invoice->notes, $matches);
    $orderNo = $matches[1] ?? '';

    if (!$orderNo) {
        echo json_encode(['success' => false, 'message' => '订单号不存在']);
        return;
    }

    // 调用API检查状态
    $apiUrl = $config['test_mode'] ? $config['test_api_url'] : $config['api_url'];
    $checkUrl = rtrim($apiUrl, '/') . '/order/status';

    $response = wp_remote_post($checkUrl, [
        'headers' => [
            'Authorization' => 'Bearer ' . $config['api_key'],
            'Content-Type' => 'application/json'
        ],
        'body' => json_encode(['order_no' => $orderNo]),
        'timeout' => 30
    ]);

    if (is_wp_error($response)) {
        echo json_encode(['success' => false, 'message' => '网络请求失败']);
        return;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (!$data || !isset($data['status'])) {
        echo json_encode(['success' => false, 'message' => '响应数据无效']);
        return;
    }

    // 根据状态返回结果
    $statusText = [
        'pending' => '等待支付',
        'paid' => '支付成功',
        'failed' => '支付失败',
        'expired' => '已过期'
    ];

    echo json_encode([
        'success' => true,
        'data' => [
            'status' => $data['status'],
            'status_text' => $statusText[$data['status']] ?? '未知状态',
            'tx_hash' => $data['tx_hash'] ?? '',
            'actual_amount' => $data['actual_amount'] ?? 0
        ]
    ]);
}

// 处理请求
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'callback':
        handlePaymentCallback();
        break;

    case 'check_status':
        checkPaymentStatus();
        break;

    default:
        // 默认作为回调处理
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty(file_get_contents('php://input'))) {
            handlePaymentCallback();
        } else {
            http_response_code(400);
            die('Invalid request');
        }
        break;
}
