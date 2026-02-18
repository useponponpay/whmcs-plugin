<?php
/**
 * PonponPay WHMCS Payment Gateway - English Language File
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
    'gateway_description' => 'Professional crypto payment gateway supporting USDT, BTC, ETH and more across Tron, Ethereum, Polygon, Solana, etc. Secure and reliable collections.',
    'friendly_name' => 'PonponPay - Crypto Payment Gateway',
    
    // Config descriptions
    'config_api_credentials' => '🔐 API Credentials',
    'config_api_key_desc' => 'Sign in to the merchant console and copy the API Key from the <strong>"API Keys"</strong> page.',
    'config_credentials_validated' => 'Credentials will be validated automatically on save.',
    'config_wallet_setup' => '⚙️ Wallet & Payment Setup',
    'config_wallets' => 'Wallets',
    'config_wallets_desc' => 'add receiving addresses',
    'config_payments' => 'Payments',
    'config_payments_desc' => 'enable networks and currencies',
    'config_open_console' => 'Open ponponpay.com',
    
    // Payment selection page
    'invoice_already_paid' => 'Invoice already paid',
    'payment_page_opened' => 'Payment page opened in a new tab',
    'choose_payment_method' => 'Choose a crypto payment method',
    'invoice_amount' => 'Invoice Amount',
    'payable_amount' => 'Payable Amount',
    'please_select_method' => 'Please select a method',
    'select_network_currency' => 'Select network and currency:',
    'please_choose_network' => 'Please choose a network & currency...',
    'create_crypto_payment' => 'Create crypto payment',
    'creating_order' => 'Creating payment order, please wait...',
    'please_select_network' => 'Please select a network and currency',
    'failed_create_order' => 'Failed to create order',
    'network_error_retry' => 'Network error, please retry',
    'no_payment_methods' => 'No available payment methods, please contact support.',
    
    // Payment page
    'crypto_payment' => 'Crypto Payment',
    'time_remaining' => 'Time remaining',
    'calculating' => 'calculating...',
    'amount_to_pay' => 'Amount to pay',
    'scan_qr_to_pay' => 'Scan the QR code to pay',
    'payment_qr_code' => 'Payment QR code',
    'network' => 'Network',
    'payment_address' => 'Payment address',
    'copy' => 'Copy',
    'copied' => 'Copied',
    'payment_tips' => 'Payment tips:',
    'tip_correct_network' => 'Please use the correct network (%s).',
    'tip_exact_amount' => 'Amount must match exactly: %s %s.',
    'tip_complete_before_timer' => 'Complete payment before the timer ends.',
    'tip_auto_redirect' => 'The page will auto-redirect after payment.',
    'check_status' => 'Check status',
    'checking' => 'Checking...',
    'refresh_page' => 'Refresh page',
    'order_expired' => 'Order expired',
    
    // Basic payment (no API)
    'basic_payment_title' => 'PonponPay Crypto Payment',
    'setup_reminder' => 'Setup reminder:',
    'setup_reminder_desc' => 'Please configure the following items in the gateway settings to enable full functionality:',
    'setup_merchant_id' => 'Merchant ID',
    'setup_api_key_secret' => 'API key and secret',
    'setup_wallet_address' => 'Wallet address configuration',
    
    // Error messages
    'payment_system_error' => 'Payment system error',
    'contact_support' => 'Please contact support for assistance.',
    'order_number_required' => 'Order number or trade ID is required',
    
    // Validation errors
    'invalid_exchange_rate' => 'Exchange rate must be a number greater than 0.',
    'invalid_api_key_format' => '⚠️ Invalid format',
    'api_key_length_error' => 'API key length is incorrect. Current length: %d.',
    'api_key_fix' => 'API key should be at least 32 characters. Please paste the full key.',
    'activation_failed' => '⚠️ Activation failed',
    'settings_saved_inactive' => '💡 Note: Settings were saved but the gateway is inactive because activation failed. Please fix the issue above and save again.',
    'api_connection_error' => '⚠️ API connection error',
    'api_connection_error_desc' => 'Unable to reach the payment gateway server.',
    'details' => 'Details',
    'fix' => 'Fix',
    'api_connection_fix' => 'Check network connectivity, verify API server URL, or contact support.',
    'settings_saved_unverified' => '💡 Note: Settings were saved, but the gateway may not work until credentials are verified.',
    
    // Activation/Deactivation
    'gateway_activated' => 'PonponPay gateway activated. Orders will be recorded via the backend API.',
    'gateway_deactivated' => 'PonponPay gateway has been deactivated.',
    
    // Network names
    'network_tron' => 'Tron (TRC20)',
    'network_ethereum' => 'Ethereum (ERC20)',
    'network_polygon' => 'Polygon (MATIC)',
    'network_solana' => 'Solana (SOL)',
    
    // Callback messages
    'gateway_not_configured' => 'Gateway not configured',
    'invalid_json_data' => 'Invalid JSON data',
    'invalid_signature' => 'Invalid signature',
    'missing_order_number' => 'Missing order number',
    'invoice_not_found' => 'Invoice not found',
    'signature_verification_failed' => 'Callback signature verification failed',
    'invoice_already_paid_ignore' => 'Invoice already paid, ignoring duplicate callback',
    'payment_success' => 'Payment success',
    'payment_processing_failed' => 'Payment processing failed',
    'payment_failed_or_expired' => 'Payment failed or expired',
    'unknown_payment_status' => 'Unknown payment status',
    'missing_invoice_id' => 'Missing invoice ID',
    'invoice_not_exist' => 'Invoice does not exist',
    'order_number_not_exist' => 'Order number does not exist',
    'network_request_failed' => 'Network request failed',
    'invalid_response_data' => 'Invalid response data',
    
    // Status texts
    'status_pending' => 'Pending payment',
    'status_paid' => 'Payment success',
    'status_failed' => 'Payment failed',
    'status_expired' => 'Expired',
    'status_unknown' => 'Unknown status',
    
    // Hooks
    'order_paid_log' => 'PonponPay: Order #%d payment success',
    'order_cancelled_log' => 'PonponPay: Order #%d cancelled',
    'check_failed' => 'Check failed: ',
];
