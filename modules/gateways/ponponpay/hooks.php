<?php
/**
 * PonponPay WHMCS Payment Gateway Hooks
 *
 * Provides hook functions for order status changes and payment notifications
 */

if (!defined("WHMCS")) {
	die("This file cannot be accessed directly");
}

// Load language support
require_once __DIR__ . '/lib/Language.php';

/**
 * Hook after invoice payment success
 */
add_hook('InvoicePaid', 1, function ($vars) {
	$invoiceId = $vars['invoiceid'];

	// Get invoice info
	$invoice = localAPI('GetInvoice', array('invoiceid' => $invoiceId));

	if ($invoice['result'] == 'success' && $invoice['paymentmethod'] == 'ponponpay') {
		// Log payment success
		logActivity(ponponpay_lang('order_paid_log', $invoiceId));
	}
});

/**
 * Hook after invoice cancellation
 */
add_hook('InvoiceCancelled', 1, function ($vars) {
	$invoiceId = $vars['invoiceid'];

	// Get invoice info
	$invoice = localAPI('GetInvoice', array('invoiceid' => $invoiceId));

	if ($invoice['result'] == 'success' && $invoice['paymentmethod'] == 'ponponpay') {
		// Log order cancellation
		logActivity(ponponpay_lang('order_cancelled_log', $invoiceId));
	}
});

/**
 * Client area page hook - Add JavaScript
 */
add_hook('ClientAreaHeadOutput', 1, function ($vars) {
	if ($vars['filename'] == 'viewinvoice') {
		return '<script src="modules/gateways/ponponpay/ponponpay.js"></script>';
	}
});

/**
 * Admin area hook - Add payment status check functionality
 */
add_hook('AdminAreaHeadOutput', 1, function ($vars) {
	if ($vars['filename'] == 'invoices') {
		$checkFailedMsg = ponponpay_lang('check_failed');
		return '<script>
            function checkPonponPayStatus(invoiceId) {
                jQuery.post("modules/gateways/ponponpay/admin_check.php", {
                    action: "check_status",
                    invoice_id: invoiceId
                }, function(data) {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert("' . addslashes($checkFailedMsg) . '" + data.message);
                    }
                }, "json");
            }
        </script>';
	}
});

/**
 * 下单后自动跳转到发票支付页面（避免停留在 Order Confirmation 页面）
 */
add_hook('AfterShoppingCartCheckout', 1, function ($vars) {
	$invoiceId = $vars['InvoiceID'] ?? 0;

	if (!empty($invoiceId)) {
		$invoice = localAPI('GetInvoice', ['invoiceid' => $invoiceId]);
		if ($invoice['result'] === 'success' && $invoice['paymentmethod'] === 'ponponpay') {
			header('Location: viewinvoice.php?id=' . $invoiceId);
			exit;
		}
	}
});
