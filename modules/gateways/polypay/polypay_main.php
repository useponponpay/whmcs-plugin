<?php
/**
 * PolyPay WHMCS Payment Gateway Module
 *
 * Professional crypto payment gateway with multi-chain, multi-currency support.
 *
 * @author PolyPay Engineering
 * @version 2.0.0
 */

if (!defined("WHMCS")) {
	die("This file cannot be accessed directly");
}

// Load config to ensure polypay_get_api_url is available
if (file_exists(dirname(dirname(dirname(__DIR__))) . '/includes/hooks/polypay_config.php')) {
	require_once dirname(dirname(dirname(__DIR__))) . '/includes/hooks/polypay_config.php';
}

// Load language support
require_once __DIR__ . '/lib/Language.php';

// Safe logger (fallback when polypay_safe_log is missing)
if (!function_exists('polypay_safe_log')) {
	function polypay_safe_log($gateway, $data, $description)
	{
		if (function_exists('logTransaction')) {
			logTransaction($gateway, $data, $description);
		}
	}
}

/**
 * Define module related meta data.
 */
function polypay_MetaData()
{
	return array(
		'DisplayName' => polypay_lang('gateway_name'),
		'APIVersion' => '2.0',
		'DisableLocalCreditCardInput' => true,
		'TokenisedStorage' => false,
		'Description' => polypay_lang('gateway_description'),
		'Author' => 'PolyPay Engineering Team',
		'Version' => '2.0.0',
		'TestMode' => true,
		'RequiresDataStorage' => false,
		'failureException' => false,
	);
}

/**
 * Define gateway configuration options.
 */
function polypay_config()
{
	return [
		'FriendlyName' => [
			'Type' => 'System',
			'Value' => polypay_lang('friendly_name'),
		],
		'api_key' => [
			'FriendlyName' => 'API Key',
			'Type' => 'text',
			'Size' => '64',
			'Description' => '<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px; border-radius: 8px; margin: 10px 0 0 0;">'
				. '<strong style="font-size: 15px;">' . polypay_lang('config_api_credentials') . '</strong><br/>'
				. '<p style="margin: 8px 0 8px 0; line-height: 1.5; font-size: 13px;">'
				. polypay_lang('config_api_key_desc') . '<br/>'
				. polypay_lang('config_credentials_validated') . '<br/>'
				. '</p>'
				. '<strong style="font-size: 15px;">' . polypay_lang('config_wallet_setup') . '</strong><br/>'
				. '<p style="margin: 8px 0 0 0; line-height: 1.5; font-size: 13px;">'
				. '📍 <strong>' . polypay_lang('config_wallets') . '</strong>: ' . polypay_lang('config_wallets_desc') . ' | 💳 <strong>' . polypay_lang('config_payments') . '</strong>: ' . polypay_lang('config_payments_desc') . '<br/>'
				. '<a href="https://polypay.ai" target="_blank" rel="noopener" style="color: #fff; text-decoration: underline;">' . polypay_lang('config_open_console') . '</a>'
				. '</p>'
				. '</div>',
		],
	];
}

/**
 * Payment link.
 */
function polypay_link(array $params)
{
	echo '<form action="' . $params['returnurl'] . '" method="post">';
	// Add debug log when payment link is invoked
	polypay_safe_log('PolyPay Debug', [
		'function' => 'polypay_link',
		'api_key' => !empty($params['api_key']) ? substr($params['api_key'], 0, 8) . '...' : 'empty',
		'api_url' => polypay_get_api_url(),
		'invoiceid' => $params['invoiceid'] ?? 'unknown'
	], 'Payment Link Called');

	try {
		// Use API mode when API Key is configured
		if (!empty($params['api_key'])) {
			return polypay_render_api_payment($params);
		} else {
			return polypay_render_basic_payment($params);
		}
	} catch (Exception $e) {
		error_log("[PolyPay] Payment link error: " . $e->getMessage());
		polypay_safe_log('PolyPay Error', [
			'error' => $e->getMessage(),
			'params' => $params
		], 'Payment Link Error');

		return polypay_render_error_page($e->getMessage());
	}
}

/**
 * Render API-based payment.
 *
 * 不再使用插件内部的支付方式选择页，统一跳转到 payment-frontend 的
 * Hosted Checkout 页面，由前端完成支付方式的选择与支付。
 */
function polypay_render_api_payment($params)
{
	// Skip if invoice already paid
	$invoice = localAPI('GetInvoice', ['invoiceid' => $params['invoiceid']]);
	if ($invoice['result'] === 'success' && strtolower($invoice['status']) === 'paid') {
		return '<div class="alert alert-success">' . polypay_lang('invoice_already_paid') . '</div>';
	}

	// 直接跳转到 payment-frontend 的支付方式选择页
	return polypay_render_hosted_checkout($params);
}

/**
 * Build the hosted checkout URL via backend and render a redirect page.
 *
 * 调用后端 /order/checkout 接口（不传 currency/network），获取 payment-frontend
 * 的 Hosted Checkout 跳转链接，再由浏览器跳转到该页面进行支付方式选择。
 */
function polypay_render_hosted_checkout($params)
{
	try {
		// 生成稳定的商户订单号，保证后端去重以及回调能解析出发票ID
		// 格式：WHMCS_{invoiceid}_{8位hash}
		$hashSource = $params['api_key'] . '_' . $params['invoiceid'];
		$hash = substr(md5($hashSource), 0, 8);
		$orderNo = 'WHMCS_' . $params['invoiceid'] . '_' . $hash;

		// 回调与支付完成跳转地址（均使用 WHMCS 系统URL）
		$systemUrl = rtrim($params['systemurl'], '/');
		$notifyUrl = $systemUrl . '/modules/gateways/callback/polypay.php';
		$redirectUrl = $params['returnurl'];

		// 金额换算：发票金额 / 汇率（无汇率时按 1:1）
		$amount = floatval($params['amount']) / floatval($params['exchange_rate'] ?: 1.0);

		// 不传 currency/network，让 payment-frontend 展示支付方式选择页
		$checkoutData = [
			'mch_order_id' => $orderNo,
			'amount'       => $amount,
			'notify_url'   => $notifyUrl,
			'redirect_url' => $redirectUrl,
			'locale'       => polypay_get_checkout_locale(),
		];

		error_log("[PolyPay] Building hosted checkout for invoice: " . $params['invoiceid'] . ", order: " . $orderNo);

		$apiUrl = polypay_get_api_url();
		$response = polypay_call_api($apiUrl . '/api/v1/pay/sdk/order/checkout', $checkoutData, $params['api_key']);

		// 兼容 checkout_url / payment_url 两个返回字段
		$checkoutUrl = $response['data']['checkout_url'] ?? ($response['data']['payment_url'] ?? '');
		if (empty($checkoutUrl)) {
			throw new Exception($response['message'] ?? polypay_lang('failed_create_order'));
		}

		polypay_safe_log('PolyPay Checkout', [
			'order_no'     => $orderNo,
			'invoice_id'   => $params['invoiceid'],
			'amount'       => $amount,
			'checkout_url' => $checkoutUrl,
		], 'Hosted Checkout URL Created');

		return polypay_render_checkout_redirect($checkoutUrl);
	} catch (Exception $e) {
		error_log("[PolyPay] Build hosted checkout failed: " . $e->getMessage());
		polypay_safe_log('PolyPay Error', [
			'function'   => 'polypay_render_hosted_checkout',
			'error'      => $e->getMessage(),
			'invoice_id' => $params['invoiceid'] ?? 'unknown',
		], 'Hosted Checkout Failed');

		return polypay_render_error_page($e->getMessage());
	}
}

/**
 * Render a page that redirects the buyer to the hosted checkout page.
 *
 * 展示一个带加载动画的过渡页，并自动跳转到 payment-frontend 的支付方式选择页；
 * 同时提供按钮作为兜底（避免浏览器拦截自动跳转）。
 */
function polypay_render_checkout_redirect($checkoutUrl)
{
	$safeUrl = htmlspecialchars($checkoutUrl, ENT_QUOTES);

	return '
    <div class="coinpay-payment-container" style="max-width: 500px; margin: 0 auto; padding: 30px 20px; border: 1px solid #ddd; border-radius: 8px; background: #fff; text-align: center;">
        <div style="display: inline-block; width: 3rem; height: 3rem; border: 4px solid #f3f3f3; border-top: 4px solid #007bff; border-radius: 50%; animation: spin 1s linear infinite; margin-bottom: 20px;"></div>
        <h3 style="color: #333; margin-bottom: 10px;">' . polypay_lang('choose_payment_method') . '</h3>
        <p style="color: #666; margin-bottom: 20px;">' . polypay_lang('redirecting_to_payment') . '</p>
        <a href="' . $safeUrl . '" id="polypay-checkout-link" style="display: inline-block; padding: 12px 24px; font-size: 16px; border-radius: 4px; background-color: #007bff; color: white; text-decoration: none;">
            ' . polypay_lang('continue_to_payment') . '
        </a>
    </div>

    <style>
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    </style>

    <script>
    (function () {
        var target = ' . json_encode($checkoutUrl) . ';
        // 自动跳转到 payment-frontend 的支付方式选择页
        setTimeout(function () {
            window.location.href = target;
        }, 800);
    })();
    </script>';
}

/**
 * Map the current WHMCS language to a payment-frontend locale code.
 *
 * @return string payment-frontend 的语言路径（zh/en/ja...），默认 en
 */
function polypay_get_checkout_locale()
{
	$map = [
		'english'    => 'en',
		'chinese'    => 'zh',
		'japanese'   => 'ja',
		'korean'     => 'ko',
		'spanish'    => 'es',
		'french'     => 'fr',
		'german'     => 'de',
		'portuguese' => 'pt',
		'russian'    => 'ru',
		'arabic'     => 'ar',
	];

	$current = PolyPayLanguage::getCurrentLanguage();
	return $map[$current] ?? 'en';
}

/**
 * Render basic payment (without API).
 */
function polypay_render_basic_payment($params)
{
	$html = '<div style="max-width: 500px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">';
	$html .= '<h3>' . polypay_lang('basic_payment_title') . '</h3>';
	$html .= '<p><strong>' . polypay_lang('invoice_amount') . ':</strong> ' . htmlspecialchars($params['amount']) . ' ' . htmlspecialchars($params['currency']) . '</p>';
	$html .= '<p><strong>Invoice ID:</strong> ' . htmlspecialchars($params['invoiceid']) . '</p>';
	$html .= '<div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 4px; margin: 20px 0;">';
	$html .= '<p><strong>' . polypay_lang('setup_reminder') . '</strong></p>';
	$html .= '<p>' . polypay_lang('setup_reminder_desc') . '</p>';
	$html .= '<ul>';
	$html .= '<li>' . polypay_lang('setup_merchant_id') . '</li>';
	$html .= '<li>' . polypay_lang('setup_api_key_secret') . '</li>';
	$html .= '<li>' . polypay_lang('setup_wallet_address') . '</li>';
	$html .= '</ul>';
	$html .= '</div>';
	$html .= '</div>';

	return $html;
}

/**
 * Render error page.
 */
function polypay_render_error_page($error)
{
	return '<div style="max-width: 500px; margin: 0 auto; padding: 20px; border: 1px solid #dc3545; border-radius: 8px; background: #f8d7da; color: #721c24;">
                <h4>' . polypay_lang('payment_system_error') . '</h4>
                <p>' . htmlspecialchars($error) . '</p>
                <p><em>' . polypay_lang('contact_support') . '</em></p>
            </div>';
}





// polypay_get_api_url is defined in includes/hooks/polypay_config.php

/**
 * Plugin activation (called when saving WHMCS config)
 */
function polypay_plugin_activate($params)
{
	try {
		$apiUrl = polypay_get_api_url();

		// Debug log
		error_log("[PolyPay] Calling plugin activate API: " . $apiUrl . '/api/v1/pay/sdk/plugin/activate');

		// Activate plugin
		$response = polypay_call_api(
			$apiUrl . '/api/v1/pay/sdk/plugin/activate',
			[
				'plugin_type' => 'whmcs'
			],
			$params['api_key']
		);

		error_log("[PolyPay] Plugin activation response: " . json_encode($response, JSON_UNESCAPED_UNICODE));
		return $response;
	} catch (Exception $e) {
		error_log("[PolyPay] Plugin activation exception: " . $e->getMessage());
		return [
			'code' => -1,
			'message' => 'Plugin activation failed: ' . $e->getMessage()
		];
	}
}

function polypay_call_api($url, $data, $apiKey)
{
	// Debug log - API call start
	error_log("[PolyPay] API call started: " . $url);
	error_log("[PolyPay] Payload: " . json_encode($data, JSON_UNESCAPED_UNICODE));
	error_log("[PolyPay] API key prefix: " . substr($apiKey, 0, 8) . "...");

	// Log API call start
	polypay_safe_log('PolyPay Debug', [
		'function' => 'polypay_call_api',
		'url' => $url,
		'data' => $data,
		'api_key' => substr($apiKey, 0, 8) . '...',
		'step' => 'api_call_start'
	], 'API Call Start');

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
			'User-Agent: WHMCS-PolyPay/2.0'
		]
	]);

	$response = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$error = curl_error($ch);
	curl_close($ch);

	// Debug log - API call result
	error_log("[PolyPay] API status code: " . $httpCode);
	if ($error) {
		error_log("[PolyPay] CURL error: " . $error);
	}
	error_log("[PolyPay] Response length: " . strlen($response));
	error_log("[PolyPay] Response preview: " . substr($response, 0, 200));

	// Log API call result
	polypay_safe_log('PolyPay Debug', [
		'function' => 'polypay_call_api',
		'url' => $url,
		'http_code' => $httpCode,
		'curl_error' => $error,
		'response_length' => strlen($response),
		'response_preview' => substr($response, 0, 200),
		'step' => 'api_call_result'
	], 'API Call Result');

	if ($error) {
		error_log("[PolyPay] API request failed: " . $error);
		polypay_safe_log('PolyPay Error', [
			'function' => 'polypay_call_api',
			'url' => $url,
			'curl_error' => $error,
			'step' => 'curl_error'
		], 'API Curl Error');
		throw new Exception('API request failed: ' . $error);
	}

	if ($httpCode !== 200) {
		error_log("[PolyPay] API returned HTTP error: " . $httpCode . ", response: " . $response);
		polypay_safe_log('PolyPay Error', [
			'function' => 'polypay_call_api',
			'url' => $url,
			'http_code' => $httpCode,
			'response' => $response,
			'step' => 'http_error'
		], 'API HTTP Error');
		throw new Exception('API returned HTTP error: ' . $httpCode);
	}

	$decoded = json_decode($response, true);

	// Debug log - JSON decode
	if ($decoded === null) {
		error_log("[PolyPay] JSON decode failed: " . json_last_error_msg());
	} else {
		error_log("[PolyPay] API call success, response: " . json_encode($decoded, JSON_UNESCAPED_UNICODE));
	}

	polypay_safe_log('PolyPay Debug', [
		'function' => 'polypay_call_api',
		'url' => $url,
		'decoded_response' => $decoded,
		'step' => 'api_call_success'
	], 'API Call Success');

	return $decoded;
}

/**
 * Admin configuration validation.
 */
function polypay_config_validate($params)
{
	$errors = [];

	// Basic parameter validation
	if (!empty($params['exchange_rate']) && (!is_numeric($params['exchange_rate']) || floatval($params['exchange_rate']) <= 0)) {
		$errors[] = polypay_lang('invalid_exchange_rate');
	}

	// Validate API credentials when provided
	if (!empty($params['api_key'])) {

		// Validate API key length
		if (strlen($params['api_key']) < 32) {
			$errors[] = '<strong style="color: #dc3545;">' . polypay_lang('invalid_api_key_format') . '</strong><br/>'
				. polypay_lang('api_key_length_error', strlen($params['api_key'])) . '<br/>'
				. '<strong>' . polypay_lang('fix') . ':</strong> ' . polypay_lang('api_key_fix');
			return $errors;
		}

		// Debug log - validation start
		error_log("[PolyPay] Start config validation, API Key: " . substr($params['api_key'], 0, 8) . "...");

		try {
			// Call plugin activation
			error_log("[PolyPay] Calling plugin activation");
			$activateResult = polypay_plugin_activate($params);

			if (!$activateResult || $activateResult['code'] != 0) {
				$errorMessage = $activateResult['message'] ?? 'Unknown error';

				$errorMsg = '<strong style="color: #dc3545;">' . polypay_lang('activation_failed') . '</strong><br/>' . $errorMessage;

				error_log("[PolyPay] " . strip_tags($errorMsg));
				$errors[] = $errorMsg;

				// Additional help
				$errors[] = '<div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin-top: 10px;">'
					. polypay_lang('settings_saved_inactive')
					. '</div>';

				return $errors;
			}

			// Activation succeeded
			error_log("[PolyPay] Plugin activation succeeded");

			error_log("[PolyPay] ✅ API credentials verified");

			// WHMCS only shows success via logging; no extra action needed

		} catch (Exception $e) {
			$errorMsg = '<strong style="color: #dc3545;">' . polypay_lang('api_connection_error') . '</strong><br/>'
				. polypay_lang('api_connection_error_desc') . '<br/>'
				. '<strong>' . polypay_lang('details') . ':</strong> ' . $e->getMessage() . '<br/>'
				. '<strong>' . polypay_lang('fix') . ':</strong> ' . polypay_lang('api_connection_fix');
			error_log("[PolyPay] " . strip_tags($errorMsg));
			$errors[] = $errorMsg;

			// Additional help
			$errors[] = '<div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin-top: 10px;">'
				. polypay_lang('settings_saved_unverified')
				. '</div>';
		}
	}

	// Log success when no errors
	if (empty($errors)) {
		error_log("[PolyPay] Configuration validated successfully");
	}

	return $errors;
}

/**
 * Gateway activation.
 */
function polypay_activate($params)
{
	return [
		'status' => 'success',
		'description' => polypay_lang('gateway_activated')
	];
}

/**
 * Gateway deactivation.
 */
function polypay_deactivate($params)
{
	return [
		'status' => 'success',
		'description' => polypay_lang('gateway_deactivated')
	];
}
