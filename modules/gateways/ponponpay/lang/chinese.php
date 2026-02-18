<?php
/**
 * PonponPay WHMCS Payment Gateway - Chinese Language File
 *
 * @package    PonponPay
 * @author     PonponPay Engineering
 * @version    2.0.0
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

$_PONPONPAY_LANG = [
    // Meta & Config
    'gateway_name' => 'PonponPay',
    'gateway_description' => '专业的加密货币支付网关，支持 USDT、BTC、ETH 等多种货币，覆盖 Tron、Ethereum、Polygon、Solana 等多条链。安全可靠的收款服务。',
    'friendly_name' => 'PonponPay - 加密货币支付网关',
    
    // Config descriptions
    'config_api_credentials' => '🔐 API 凭证',
    'config_api_key_desc' => '登录商户控制台，从 <strong>"API 密钥"</strong> 页面复制 API Key。',
    'config_credentials_validated' => '保存时将自动验证凭证。',
    'config_wallet_setup' => '⚙️ 钱包和支付设置',
    'config_wallets' => '钱包',
    'config_wallets_desc' => '添加收款地址',
    'config_payments' => '支付',
    'config_payments_desc' => '启用网络和货币',
    'config_open_console' => '打开 ponponpay.com',
    
    // Payment selection page
    'invoice_already_paid' => '账单已支付',
    'payment_page_opened' => '支付页面已在新标签页中打开',
    'choose_payment_method' => '选择加密货币支付方式',
    'invoice_amount' => '账单金额',
    'payable_amount' => '应付金额',
    'please_select_method' => '请选择支付方式',
    'select_network_currency' => '选择网络和货币：',
    'please_choose_network' => '请选择网络和货币...',
    'create_crypto_payment' => '创建加密货币支付',
    'creating_order' => '正在创建支付订单，请稍候...',
    'please_select_network' => '请选择网络和货币',
    'failed_create_order' => '创建订单失败',
    'network_error_retry' => '网络错误，请重试',
    'no_payment_methods' => '暂无可用的支付方式，请联系客服。',
    
    // Payment page
    'crypto_payment' => '加密货币支付',
    'time_remaining' => '剩余时间',
    'calculating' => '计算中...',
    'amount_to_pay' => '支付金额',
    'scan_qr_to_pay' => '扫描二维码支付',
    'payment_qr_code' => '支付二维码',
    'network' => '网络',
    'payment_address' => '支付地址',
    'copy' => '复制',
    'copied' => '已复制',
    'payment_tips' => '支付提示：',
    'tip_correct_network' => '请使用正确的网络（%s）。',
    'tip_exact_amount' => '金额必须精确匹配：%s %s。',
    'tip_complete_before_timer' => '请在倒计时结束前完成支付。',
    'tip_auto_redirect' => '支付完成后页面将自动跳转。',
    'check_status' => '检查状态',
    'checking' => '检查中...',
    'refresh_page' => '刷新页面',
    'order_expired' => '订单已过期',
    
    // Basic payment (no API)
    'basic_payment_title' => 'PonponPay 加密货币支付',
    'setup_reminder' => '设置提醒：',
    'setup_reminder_desc' => '请在网关设置中配置以下项目以启用完整功能：',
    'setup_merchant_id' => '商户 ID',
    'setup_api_key_secret' => 'API 密钥和密钥',
    'setup_wallet_address' => '钱包地址配置',
    
    // Error messages
    'payment_system_error' => '支付系统错误',
    'contact_support' => '请联系客服获取帮助。',
    'order_number_required' => '需要订单号或交易 ID',
    
    // Validation errors
    'invalid_exchange_rate' => '汇率必须是大于 0 的数字。',
    'invalid_api_key_format' => '⚠️ 格式无效',
    'api_key_length_error' => 'API 密钥长度不正确。当前长度：%d。',
    'api_key_fix' => 'API 密钥应至少为 32 个字符。请粘贴完整的密钥。',
    'activation_failed' => '⚠️ 激活失败',
    'settings_saved_inactive' => '💡 注意：设置已保存，但由于激活失败，网关处于非活动状态。请修复上述问题后重新保存。',
    'api_connection_error' => '⚠️ API 连接错误',
    'api_connection_error_desc' => '无法连接到支付网关服务器。',
    'details' => '详情',
    'fix' => '修复方法',
    'api_connection_fix' => '检查网络连接，验证 API 服务器 URL，或联系客服。',
    'settings_saved_unverified' => '💡 注意：设置已保存，但在凭证验证之前网关可能无法正常工作。',
    
    // Activation/Deactivation
    'gateway_activated' => 'PonponPay 网关已激活。订单将通过后端 API 记录。',
    'gateway_deactivated' => 'PonponPay 网关已停用。',
    
    // Network names
    'network_tron' => 'Tron (TRC20)',
    'network_ethereum' => 'Ethereum (ERC20)',
    'network_polygon' => 'Polygon (MATIC)',
    'network_solana' => 'Solana (SOL)',
    
    // Callback messages
    'gateway_not_configured' => '网关未配置',
    'invalid_json_data' => '无效的 JSON 数据',
    'invalid_signature' => '无效的签名',
    'missing_order_number' => '缺少订单号',
    'invoice_not_found' => '未找到账单',
    'signature_verification_failed' => '回调签名验证失败',
    'invoice_already_paid_ignore' => '账单已支付，忽略重复回调',
    'payment_success' => '支付成功',
    'payment_processing_failed' => '支付处理失败',
    'payment_failed_or_expired' => '支付失败或已过期',
    'unknown_payment_status' => '未知支付状态',
    'missing_invoice_id' => '缺少账单 ID',
    'invoice_not_exist' => '账单不存在',
    'order_number_not_exist' => '订单号不存在',
    'network_request_failed' => '网络请求失败',
    'invalid_response_data' => '响应数据无效',
    
    // Status texts
    'status_pending' => '等待支付',
    'status_paid' => '支付成功',
    'status_failed' => '支付失败',
    'status_expired' => '已过期',
    'status_unknown' => '未知状态',
    
    // Hooks
    'order_paid_log' => 'PonponPay：订单 #%d 支付成功',
    'order_cancelled_log' => 'PonponPay：订单 #%d 已取消',
    'check_failed' => '检查失败：',
];
