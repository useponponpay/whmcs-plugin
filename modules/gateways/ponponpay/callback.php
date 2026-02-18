<?php
/**
 * PonponPay Callback Handler
 * Handle payment callbacks and status checks
 */

require_once '../../../init.php';

use WHMCS\Database\Capsule;

// Security check
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Get payment gateway configuration
 */
function getPonponPayConfig() {
    $gatewayParams = getGatewayVariables('ponponpay');
    return $gatewayParams;
}

/**
 * Log transaction
 */
function ponponpayLog($message, $data = []) {
    logTransaction('ponponpay', $data, $message);
}

/**
 * Verify callback signature
 */
function verifyCallbackSignature($data, $signature, $apiKey) {
    $computedSignature = hash_hmac('sha256', json_encode($data), $apiKey);
    return hash_equals($signature, $computedSignature);
}

/**
 * Handle payment callback
 */
function handlePaymentCallback() {
    $config = getPonponPayConfig();

    if (empty($config) || $config['type'] !== 'ponponpay') {
        http_response_code(400);
        die('Gateway not configured');
    }

    // Get callback data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        http_response_code(400);
        die('Invalid JSON data');
    }

    // Verify signature
    $signature = $_SERVER['HTTP_X_PONPONPAY_SIGNATURE']
        ?? '';
    if (!verifyCallbackSignature($data, $signature, $config['api_key'])) {
        ponponpayLog('Callback signature verification failed', $data);
        http_response_code(401);
        die('Invalid signature');
    }

    // Handle different callback types
    $orderNo = $data['order_no'] ?? '';
    $status = $data['status'] ?? '';
    $txHash = $data['tx_hash'] ?? '';
    $actualAmount = $data['actual_amount'] ?? 0;

    if (empty($orderNo)) {
        http_response_code(400);
        die('Missing order number');
    }

    // Find corresponding invoice
    $invoice = Capsule::table('tblinvoices')
        ->where(function ($query) use ($orderNo) {
            $query->where('notes', 'LIKE', "%ponponpay_order:{$orderNo}%");
        })
        ->first();

    if (!$invoice) {
        ponponpayLog('Invoice not found', ['order_no' => $orderNo]);
        http_response_code(404);
        die('Invoice not found');
    }

    $invoiceId = $invoice->id;

    // Handle based on status
    switch ($status) {
        case 'paid':
            // Check if already paid
            if ($invoice->status === 'Paid') {
                ponponpayLog('Invoice already paid, ignoring duplicate callback', ['invoice_id' => $invoiceId, 'order_no' => $orderNo]);
                break;
            }

            // Mark as paid
            $success = addInvoicePayment(
                $invoiceId,
                $txHash, // Transaction ID
                $actualAmount,
                0, // Fee
                'ponponpay'
            );

            if ($success) {
                ponponpayLog('Payment successful', [
                    'invoice_id' => $invoiceId,
                    'order_no' => $orderNo,
                    'tx_hash' => $txHash,
                    'amount' => $actualAmount
                ]);

                // Send payment confirmation email
                sendMessage('Invoice Payment Confirmation', $invoice->userid);
            } else {
                ponponpayLog('Payment processing failed', [
                    'invoice_id' => $invoiceId,
                    'order_no' => $orderNo
                ]);
            }
            break;

        case 'failed':
        case 'expired':
            ponponpayLog('Payment failed or expired', [
                'invoice_id' => $invoiceId,
                'order_no' => $orderNo,
                'status' => $status
            ]);
            break;

        default:
            ponponpayLog('Unknown payment status', [
                'invoice_id' => $invoiceId,
                'order_no' => $orderNo,
                'status' => $status
            ]);
            break;
    }

    // Return success response
    echo json_encode(['success' => true]);
}

/**
 * Check payment status
 */
function checkPaymentStatus() {
    $invoiceId = $_POST['invoice_id'] ?? 0;

    if (!$invoiceId) {
        echo json_encode(['success' => false, 'message' => 'Missing invoice ID']);
        return;
    }

    $config = getPonponPayConfig();

    if (empty($config) || $config['type'] !== 'ponponpay') {
        echo json_encode(['success' => false, 'message' => 'Gateway not configured']);
        return;
    }

    // Find invoice
    $invoice = Capsule::table('tblinvoices')->find($invoiceId);

    if (!$invoice) {
        echo json_encode(['success' => false, 'message' => 'Invoice not found']);
        return;
    }

    // Extract order number from notes
    preg_match('/ponponpay_order:([^,\s]+)/', $invoice->notes, $matches);
    $orderNo = $matches[1] ?? '';

    if (!$orderNo) {
        echo json_encode(['success' => false, 'message' => 'Order number not found']);
        return;
    }

    // Call API to check status
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
        echo json_encode(['success' => false, 'message' => 'Network request failed']);
        return;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (!$data || !isset($data['status'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid response data']);
        return;
    }

    // Return result based on status
    $statusText = [
        'pending' => 'Pending payment',
        'paid' => 'Payment successful',
        'failed' => 'Payment failed',
        'expired' => 'Expired'
    ];

    echo json_encode([
        'success' => true,
        'data' => [
            'status' => $data['status'],
            'status_text' => $statusText[$data['status']] ?? 'Unknown status',
            'tx_hash' => $data['tx_hash'] ?? '',
            'actual_amount' => $data['actual_amount'] ?? 0
        ]
    ]);
}

// Handle request
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'callback':
        handlePaymentCallback();
        break;

    case 'check_status':
        checkPaymentStatus();
        break;

    default:
        // Default to callback handling
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty(file_get_contents('php://input'))) {
            handlePaymentCallback();
        } else {
            http_response_code(400);
            die('Invalid request');
        }
        break;
}
