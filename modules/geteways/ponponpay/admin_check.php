<?php
/**
 * PonponPay Admin Functions
 * Admin backend operation functions
 */

require_once '../../../init.php';

use WHMCS\Database\Capsule;

// Check admin permission
if (!isset($_SESSION['adminid'])) {
    die('Access denied');
}

/**
 * Check payment status
 */
function adminCheckPaymentStatus()
{
    $invoiceId = $_POST['invoice_id'] ?? 0;

    if (!$invoiceId) {
        echo json_encode(['success' => false, 'message' => 'Missing invoice ID']);
        return;
    }

    // Get invoice info
    $invoice = Capsule::table('tblinvoices')->find($invoiceId);

    if (!$invoice) {
        echo json_encode(['success' => false, 'message' => 'Invoice not found']);
        return;
    }

    // Check if using ponponpay payment
    if ($invoice->paymentmethod !== 'ponponpay') {
        echo json_encode(['success' => false, 'message' => 'This invoice is not using PonponPay payment']);
        return;
    }

    // Extract order number from notes (compatible with legacy coinpay prefix)
    preg_match('/ponponpay_order:([^,\s]+)/', $invoice->notes, $matches);
    $orderNo = $matches[1] ?? '';

    if (!$orderNo) {
        echo json_encode(['success' => false, 'message' => 'PonponPay order number not found']);
        return;
    }

    // Get gateway configuration
    $config = getGatewayVariables('ponponpay');

    if (empty($config) || $config['type'] !== 'ponponpay') {
        echo json_encode(['success' => false, 'message' => 'PonponPay gateway not configured']);
        return;
    }

    // Load API class
    require_once dirname(__FILE__) . '/lib/PonponPayApi.php';

    try {
        $apiUrl = $config['test_mode'] ? $config['test_api_url'] : $config['api_url'];
        $api = new PonponPayApi($config['api_key'], $apiUrl);

        // Query order status
        $result = $api->queryOrder($orderNo);

        if (!$result || !isset($result['status'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid API response']);
            return;
        }

        $status = $result['status'];
        $statusText = [
            'pending' => 'Pending payment',
            'paid' => 'Payment successful',
            'failed' => 'Payment failed',
            'expired' => 'Expired'
        ];

        // If status is paid but invoice is unpaid, update invoice status
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
                logActivity("Admin manually confirmed PonponPay payment - Invoice ID: {$invoiceId}, Order No: {$orderNo}");
                echo json_encode([
                    'success' => true,
                    'message' => 'Payment status updated to paid',
                    'data' => [
                        'status' => $status,
                        'status_text' => $statusText[$status],
                        'tx_hash' => $txHash,
                        'updated' => true
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update invoice status']);
            }
        } else {
            echo json_encode([
                'success' => true,
                'data' => [
                    'status' => $status,
                    'status_text' => $statusText[$status] ?? 'Unknown status',
                    'tx_hash' => $result['tx_hash'] ?? '',
                    'actual_amount' => $result['actual_amount'] ?? 0,
                    'updated' => false
                ]
            ]);
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'API request failed: ' . $e->getMessage()]);
    }
}

/**
 * Get transaction details
 */
function getTransactionDetails()
{
    $invoiceId = $_POST['invoice_id'] ?? 0;

    if (!$invoiceId) {
        echo json_encode(['success' => false, 'message' => 'Missing invoice ID']);
        return;
    }

    // Get invoice info
    $invoice = Capsule::table('tblinvoices')->find($invoiceId);

    if (!$invoice || $invoice->paymentmethod !== 'ponponpay') {
        echo json_encode(['success' => false, 'message' => 'Invoice not found or not using PonponPay payment']);
        return;
    }

    // Get payment records
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
 * Test API connection
 */
function testApiConnection()
{
    $config = getGatewayVariables('ponponpay');

    if (empty($config) || $config['type'] !== 'ponponpay') {
        echo json_encode(['success' => false, 'message' => 'PonponPay gateway not configured']);
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
                'message' => 'API connection successful',
                'data' => $result['data']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'API connection failed: ' . $result['error']
            ]);
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'API connection error: ' . $e->getMessage()]);
    }
}

// Handle request
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
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
