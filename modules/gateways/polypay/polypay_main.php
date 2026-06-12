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
		// The gateway name is a brand name and is not localized (FriendlyName is persisted to the database; translating it per client language would garble the admin display)
		'DisplayName' => 'PolyPay - Crypto Payment Gateway',
		'APIVersion' => '2.0',
		'DisableLocalCreditCardInput' => true,
		'TokenisedStorage' => false,
		'Description' => 'Professional crypto payment gateway supporting USDT and more across Tron, Ethereum, Polygon, Solana and other chains.',
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
			'Value' => 'PolyPay - Crypto Payment Gateway',
		],
		'mch_id' => [
			'FriendlyName' => 'Merchant ID',
			'Type' => 'text',
			'Size' => '32',
			'Description' => polypay_lang('config_mch_id_desc'),
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
 * The plugin's internal payment method selection page is no longer used;
 * instead, always redirect to the payment-frontend Hosted Checkout page,
 * where the frontend handles payment method selection and payment.
 */
function polypay_render_api_payment($params)
{
	// Skip if invoice already paid
	$invoice = localAPI('GetInvoice', ['invoiceid' => $params['invoiceid']]);
	if ($invoice['result'] === 'success' && strtolower($invoice['status']) === 'paid') {
		return '<div class="alert alert-success">' . polypay_lang('invoice_already_paid') . '</div>';
	}

	if (!polypay_is_checkout_submit($params) && !polypay_is_addfunds_redirect()) {
		return polypay_render_checkout_button($params);
	}

	return polypay_render_hosted_checkout($params);
}

/**
 * Detect the WHMCS Add Funds pending page.
 *
 * In the Add Funds flow, after WHMCS creates the invoice it stays on the
 * clientarea.php?action=addfunds interstitial page (showing "redirecting to
 * the payment page"). At this point the user has explicitly initiated payment,
 * so redirect straight to Hosted Checkout without requiring another click on
 * the payment button.
 */
function polypay_is_addfunds_redirect()
{
	return ($_REQUEST['action'] ?? '') === 'addfunds';
}

/**
 * Determine whether the buyer explicitly clicked the PolyPay payment button.
 */
function polypay_is_checkout_submit($params)
{
	$invoiceId = (string)($params['invoiceid'] ?? '');
	$postedAction = $_POST['polypay_action'] ?? '';
	$postedInvoiceId = (string)($_POST['polypay_invoice_id'] ?? '');

	return $postedAction === 'checkout' && $postedInvoiceId === $invoiceId;
}

/**
 * Render the manual payment button on the WHMCS invoice page.
 */
function polypay_render_checkout_button($params)
{
	// Always submit to the invoice page: interstitial pages such as addfunds re-render
	// the original page (e.g. the add-funds form) when their REQUEST_URI receives a POST,
	// so the checkout branch would never be reached; the invoice page always renders the gateway code
	$invoiceIdRaw = (int)($params['invoiceid'] ?? 0);
	$action = htmlspecialchars(rtrim($params['systemurl'], '/') . '/viewinvoice.php?id=' . $invoiceIdRaw, ENT_QUOTES);
	$invoiceId = htmlspecialchars((string)($params['invoiceid'] ?? ''), ENT_QUOTES);
	$amount = htmlspecialchars((string)($params['amount'] ?? '0'), ENT_QUOTES);
	$currency = htmlspecialchars((string)($params['currency'] ?? ''), ENT_QUOTES);

	return '
    <div class="coinpay-payment-container" style="max-width: 500px; margin: 0 auto; padding: 24px 20px; border: 1px solid #ddd; border-radius: 8px; background: #fff; text-align: center;">
        <h3 style="color: #333; margin-bottom: 12px;">' . polypay_lang('choose_payment_method') . '</h3>
        <p style="margin: 0 0 18px 0; color: #666;">
            <strong>' . polypay_lang('invoice_amount') . ':</strong> ' . $amount . ' ' . $currency . '
        </p>
        <form action="' . $action . '" method="post" style="margin: 0;">
            <input type="hidden" name="polypay_action" value="checkout">
            <input type="hidden" name="polypay_invoice_id" value="' . $invoiceId . '">
            <button type="submit" style="display: inline-block; padding: 12px 24px; font-size: 16px; border: 0; border-radius: 4px; background-color: #007bff; color: white; cursor: pointer;">
                ' . polypay_lang('continue_to_payment') . '
            </button>
        </form>
    </div>';
}

/**
 * Build the stable merchant order number for a WHMCS invoice.
 *
 * Format: A{shortened merchant ID}_{invoiceid}, e.g. A189696_123.
 * The first letter is the order source identifier: P = PolyPay platform,
 * with plugins assigned in order: A = WHMCS, B = WordPress (WooCommerce),
 * C = Shopify, and so on.
 * Merchant ID shortening rule: strip the MCH prefix and take the last 6
 * characters; when no merchant ID is configured, fall back to a stable
 * 6-character identifier derived from the API Key, ensuring the order
 * number for the same invoice is idempotent.
 */
function polypay_build_order_no($params)
{
	$mchId = strtoupper(trim((string)($params['mch_id'] ?? '')));
	$mchShort = substr((string)preg_replace('/^MCH/', '', $mchId), -6);
	if ($mchShort === '' || $mchShort === false) {
		$mchShort = strtoupper(substr(md5((string)($params['api_key'] ?? '')), 0, 6));
	}

	return 'A' . $mchShort . '_' . $params['invoiceid'];
}

/**
 * Build the hosted checkout URL via backend and render a redirect page.
 *
 * Call the backend /order/checkout endpoint (without currency/network) to get
 * the payment-frontend Hosted Checkout redirect URL, then let the browser
 * redirect to that page for payment method selection.
 */
function polypay_render_hosted_checkout($params)
{
	try {
		// Generate a stable merchant order number so the backend can deduplicate and the callback can resolve the invoice ID
		$orderNo = polypay_build_order_no($params);

		// Callback and post-payment redirect URLs (both based on the WHMCS system URL)
		$systemUrl = rtrim($params['systemurl'], '/');
		$notifyUrl = $systemUrl . '/modules/gateways/callback/polypay.php';
		$redirectUrl = $params['returnurl'];

		// Amount conversion: invoice amount / exchange rate (1:1 when no rate is set)
		$amount = floatval($params['amount']) / floatval($params['exchange_rate'] ?: 1.0);

		// Omit currency/network so payment-frontend shows the payment method selection page
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

		// Support both checkout_url and payment_url response fields
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
 * Shows a transition page with a loading animation and automatically redirects
 * to the payment-frontend payment method selection page; also provides a button
 * as a fallback (in case the browser blocks the automatic redirect).
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
        // Automatically redirect to the payment-frontend payment method selection page
        setTimeout(function () {
            window.location.href = target;
        }, 800);
    })();
    </script>';
}

/**
 * Map the current WHMCS language to a payment-frontend locale code.
 *
 * @return string payment-frontend locale path (zh/en/ja...), defaults to en
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
