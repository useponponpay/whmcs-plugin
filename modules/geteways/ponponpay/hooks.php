<?php
/**
 * PonponPay WHMCS Payment Gateway Hooks
 *
 * 提供钩子函数来处理订单状态变更、支付状态通知等
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * 订单支付成功后的钩子
 */
add_hook('InvoicePaid', 1, function($vars) {
    // 检查是否使用ponponpay支付网关
    $invoiceId = $vars['invoiceid'];

    // 获取发票信息
    $invoice = localAPI('GetInvoice', array('invoiceid' => $invoiceId));

    if ($invoice['result'] == 'success' && $invoice['paymentmethod'] == 'ponponpay') {
        // 记录支付成功日志
        logActivity("PonponPay: 订单 #{$invoiceId} 支付成功");

        // 可以在这里添加其他业务逻辑
        // 例如发送确认邮件、更新外部系统等
    }
});

/**
 * 订单取消后的钩子
 */
add_hook('InvoiceCancelled', 1, function($vars) {
    $invoiceId = $vars['invoiceid'];

    // 获取发票信息
    $invoice = localAPI('GetInvoice', array('invoiceid' => $invoiceId));

    if ($invoice['result'] == 'success' && $invoice['paymentmethod'] == 'ponponpay') {
        // 记录订单取消日志
        logActivity("PonponPay: 订单 #{$invoiceId} 已取消");

        // 可以在这里处理退款逻辑或通知外部系统
    }
});

/**
 * 客户端区域页面钩子 - 添加JavaScript
 */
add_hook('ClientAreaHeadOutput', 1, function($vars) {
    if ($vars['filename'] == 'viewinvoice') {
        return '<script src="modules/gateways/ponponpay/ponponpay.js"></script>';
    }
});

/**
 * 管理员区域钩子 - 添加支付状态检查功能
 */
add_hook('AdminAreaHeadOutput', 1, function($vars) {
    if ($vars['filename'] == 'invoices') {
        return '<script>
            function checkPonponPayStatus(invoiceId) {
                jQuery.post("modules/gateways/ponponpay/admin_check.php", {
                    action: "check_status",
                    invoice_id: invoiceId
                }, function(data) {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert("检查失败: " + data.message);
                    }
                }, "json");
            }
        </script>';
    }
});
