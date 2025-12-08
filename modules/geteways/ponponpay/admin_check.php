<?php
/**
 * PonponPay Admin Functions
 * 管理员后台操作功能
 */

require_once '../../../init.php';

use WHMCS\Database\Capsule;

// 检查管理员权限
if (!isset($_SESSION['adminid'])) {
    die('Access denied');
}

/**
 * 检查支付状态
 */
function adminCheckPaymentStatus()
{
    $invoiceId = $_POST['invoice_id'] ?? 0;

    if (!$invoiceId) {
        echo json_encode(['success' => false, 'message' => '缺少发票ID']);
        return;
    }

    // 获取发票信息
    $invoice = Capsule::table('tblinvoices')->find($invoiceId);

    if (!$invoice) {
        echo json_encode(['success' => false, 'message' => '发票不存在']);
        return;
    }

    // 检查是否使用ponponpay支付
    if ($invoice->paymentmethod !== 'ponponpay') {
        echo json_encode(['success' => false, 'message' => '该发票未使用PonponPay支付']);
        return;
    }

    // 从备注中提取订单号（兼容旧版 coinpay 前缀）
    preg_match('/ponponpay_order:([^,\s]+)/', $invoice->notes, $matches);
    $orderNo = $matches[1] ?? '';

    if (!$orderNo) {
        echo json_encode(['success' => false, 'message' => '未找到PonponPay订单号']);
        return;
    }

    // 获取网关配置
    $config = getGatewayVariables('ponponpay');

    if (empty($config) || $config['type'] !== 'ponponpay') {
        echo json_encode(['success' => false, 'message' => 'PonponPay网关未配置']);
        return;
    }

    // 加载API类
    require_once dirname(__FILE__) . '/lib/PonponPayApi.php';

    try {
        $apiUrl = $config['test_mode'] ? $config['test_api_url'] : $config['api_url'];
        $api = new PonponPayApi($config['api_key'], $apiUrl);

        // 查询订单状态
        $result = $api->queryOrder($orderNo);

        if (!$result || !isset($result['status'])) {
            echo json_encode(['success' => false, 'message' => 'API响应无效']);
            return;
        }

        $status = $result['status'];
        $statusText = [
            'pending' => '等待支付',
            'paid' => '支付成功',
            'failed' => '支付失败',
            'expired' => '已过期'
        ];

        // 如果状态是已支付但发票未付款，则更新发票状态
        if ($status === 'paid' && $invoice->status !== 'Paid') {
            $txHash = $result['tx_hash'] ?? '';
            $actualAmount = $result['actual_amount'] ?? $invoice->total;

            $success = addInvoicePayment(
                $invoiceId,
                $txHash,
                $actualAmount,
                0,
                'ponponpay'
            );

            if ($success) {
                logActivity("管理员手动确认PonponPay支付 - 发票ID: {$invoiceId}, 订单号: {$orderNo}");
                echo json_encode([
                    'success' => true,
                    'message' => '支付状态已更新为已付款',
                    'data' => [
                        'status' => $status,
                        'status_text' => $statusText[$status],
                        'tx_hash' => $txHash,
                        'updated' => true
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => '更新发票状态失败']);
            }
        } else {
            echo json_encode([
                'success' => true,
                'data' => [
                    'status' => $status,
                    'status_text' => $statusText[$status] ?? '未知状态',
                    'tx_hash' => $result['tx_hash'] ?? '',
                    'actual_amount' => $result['actual_amount'] ?? 0,
                    'updated' => false
                ]
            ]);
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'API请求失败: ' . $e->getMessage()]);
    }
}

/**
 * 获取交易详情
 */
function getTransactionDetails()
{
    $invoiceId = $_POST['invoice_id'] ?? 0;

    if (!$invoiceId) {
        echo json_encode(['success' => false, 'message' => '缺少发票ID']);
        return;
    }

    // 获取发票信息
    $invoice = Capsule::table('tblinvoices')->find($invoiceId);

    if (!$invoice || $invoice->paymentmethod !== 'ponponpay') {
        echo json_encode(['success' => false, 'message' => '发票不存在或未使用PonponPay支付']);
        return;
    }

    // 获取支付记录
    $payments = Capsule::table('tblaccounts')
        ->where('invoiceid', $invoiceId)
        ->where('gateway', 'ponponpay')
        ->get();

    $paymentDetails = [];
    foreach ($payments as $payment) {
        $paymentDetails[] = [
            'id' => $payment->id,
            'date' => $payment->date,
            'amount' => $payment->amountin,
            'transaction_id' => $payment->transid,
            'description' => $payment->description
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'invoice_id' => $invoiceId,
            'payments' => $paymentDetails,
            'total_paid' => array_sum(array_column($paymentDetails, 'amount')),
            'invoice_total' => $invoice->total,
            'invoice_status' => $invoice->status
        ]
    ]);
}

/**
 * 测试API连接
 */
function testApiConnection()
{
    $config = getGatewayVariables('ponponpay');

    if (empty($config) || $config['type'] !== 'ponponpay') {
        echo json_encode(['success' => false, 'message' => 'PonponPay网关未配置']);
        return;
    }

    require_once dirname(__FILE__) . '/lib/PonponPayApi.php';

    try {
        $apiUrl = $config['test_mode'] ? $config['test_api_url'] : $config['api_url'];
        $api = new PonponPayApi($config['api_key'], $apiUrl);

        $result = $api->testConnection();

        if ($result['success']) {
            echo json_encode([
                'success' => true,
                'message' => 'API连接成功',
                'data' => $result['data']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'API连接失败: ' . $result['error']
            ]);
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'API连接异常: ' . $e->getMessage()]);
    }
}

// 处理请求
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'check_status':
        adminCheckPaymentStatus();
        break;

    case 'get_transaction_details':
        getTransactionDetails();
        break;

    case 'test_connection':
        testApiConnection();
        break;

    default:
        echo json_encode(['success' => false, 'message' => '无效的操作']);
        break;
}
