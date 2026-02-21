<?php
/**
 * PonponPay WHMCS Payment Gateway Module
 *
 * Professional crypto payment gateway with multi-chain, multi-currency support.
 *
 * @author PonponPay Engineering
 * @version 2.0.0
 */

if (!defined("WHMCS")) {
	die("This file cannot be accessed directly");
}

// Load config to ensure ponponpay_get_api_url is available
if (file_exists(dirname(dirname(dirname(__DIR__))) . '/includes/hooks/ponponpay_config.php')) {
	require_once dirname(dirname(dirname(__DIR__))) . '/includes/hooks/ponponpay_config.php';
}

// Load language support
require_once __DIR__ . '/lib/Language.php';

// Safe logger (fallback when ponponpay_safe_log is missing)
if (!function_exists('ponponpay_safe_log')) {
	function ponponpay_safe_log($gateway, $data, $description)
	{
		if (function_exists('logTransaction')) {
			logTransaction($gateway, $data, $description);
		}
	}
}

/**
 * Define module related meta data.
 */
function ponponpay_MetaData()
{
	return array(
		'DisplayName' => ponponpay_lang('gateway_name'),
		'APIVersion' => '2.0',
		'DisableLocalCreditCardInput' => true,
		'TokenisedStorage' => false,
		'Description' => ponponpay_lang('gateway_description'),
		'Author' => 'PonponPay Engineering Team',
		'Version' => '2.0.0',
		'TestMode' => true,
		'RequiresDataStorage' => false,
		'failureException' => false,
	);
}

/**
 * Define gateway configuration options.
 */
function ponponpay_config()
{
	return [
		'FriendlyName' => [
			'Type' => 'System',
			'Value' => ponponpay_lang('friendly_name'),
		],
		'api_key' => [
			'FriendlyName' => 'API Key',
			'Type' => 'text',
			'Size' => '64',
			'Description' => '<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px; border-radius: 8px; margin: 10px 0 0 0;">'
				. '<strong style="font-size: 15px;">' . ponponpay_lang('config_api_credentials') . '</strong><br/>'
				. '<p style="margin: 8px 0 8px 0; line-height: 1.5; font-size: 13px;">'
				. ponponpay_lang('config_api_key_desc') . '<br/>'
				. ponponpay_lang('config_credentials_validated') . '<br/>'
				. '</p>'
				. '<strong style="font-size: 15px;">' . ponponpay_lang('config_wallet_setup') . '</strong><br/>'
				. '<p style="margin: 8px 0 0 0; line-height: 1.5; font-size: 13px;">'
				. '📍 <strong>' . ponponpay_lang('config_wallets') . '</strong>: ' . ponponpay_lang('config_wallets_desc') . ' | 💳 <strong>' . ponponpay_lang('config_payments') . '</strong>: ' . ponponpay_lang('config_payments_desc') . '<br/>'
				. '<a href="https://ponponpay.com" target="_blank" rel="noopener" style="color: #fff; text-decoration: underline;">' . ponponpay_lang('config_open_console') . '</a>'
				. '</p>'
				. '</div>',
		],
	];
}

/**
 * Payment link.
 */
function ponponpay_link(array $params)
{
	echo '<form action="' . $params['returnurl'] . '" method="post">';
	// Add debug log when payment link is invoked
	ponponpay_safe_log('PonponPay Debug', [
		'function' => 'ponponpay_link',
		'api_key' => !empty($params['api_key']) ? substr($params['api_key'], 0, 8) . '...' : 'empty',
		'api_url' => ponponpay_get_api_url(),
		'invoiceid' => $params['invoiceid'] ?? 'unknown'
	], 'Payment Link Called');

	try {
		// Use API mode when API Key is configured
		if (!empty($params['api_key'])) {
			return ponponpay_render_api_payment($params);
		} else {
			return ponponpay_render_basic_payment($params);
		}
	} catch (Exception $e) {
		error_log("[PonponPay] Payment link error: " . $e->getMessage());
		ponponpay_safe_log('PonponPay Error', [
			'error' => $e->getMessage(),
			'params' => $params
		], 'Payment Link Error');

		return ponponpay_render_error_page($e->getMessage());
	}
}

/**
 * Render API-based payment.
 */
function ponponpay_render_api_payment($params)
{
	// Skip if invoice already paid
	$invoice = localAPI('GetInvoice', ['invoiceid' => $params['invoiceid']]);
	if ($invoice['result'] === 'success' && strtolower($invoice['status']) === 'paid') {
		return '<div class="alert alert-success">' . ponponpay_lang('invoice_already_paid') . '</div>';
	}

	// Handle order creation request
	if (isset($_GET['act']) && $_GET['act'] === 'create_order') {
		return ponponpay_create_api_order($params);
	}

	// Handle order status check
	if (isset($_GET['act']) && $_GET['act'] === 'check_status') {
		return ponponpay_check_order_status($params);
	}

	// Check existing order
	try {
		$existingOrder = ponponpay_check_existing_order($params);
		if ($existingOrder) {
			// If exists, jump to payment page directly
			$tradeId = $existingOrder['data']['trade_id'] ?? '';
			$paymentUrl = $existingOrder['data']['payment_url'] ?? '';
			if (empty($paymentUrl) && !empty($tradeId)) {
				$paymentUrl = ponponpay_get_api_url() . '/pay/' . $tradeId;
			}

			if (!empty($paymentUrl)) {
				echo '<script>window.open("' . htmlspecialchars($paymentUrl) . '", "_blank");</script>';
				return '<div style="text-align: center; padding: 20px;">' . ponponpay_lang('payment_page_opened') . '</div>';
			}
		}
	} catch (Exception $e) {
		// log and continue to selection page
		error_log("[PonponPay] Check existing order failed: " . $e->getMessage());
	}

	// Render payment selection
	return ponponpay_render_payment_selection($params);
}

/**
 * Render payment selection page.
 */
function ponponpay_render_payment_selection($params)
{
	// Ensure required params exist
	$params['amount'] = $params['amount'] ?? 0;
	$params['exchange_rate'] = $params['exchange_rate'] ?? 1.0;
	$params['currency'] = $params['currency'] ?? 'CNY';

	$supportedOptions = [];

	// Prefer fetching merchant payment methods from backend
	try {
		$apiUrl = ponponpay_get_api_url();
		// Backend derives merchant from auth; use POST
		$paymentMethods = ponponpay_call_api(
			$apiUrl . '/api/v1/pay/sdk/payment-methods',
			[],
			$params['api_key']
		);

		// Use backend-provided methods when available
		$methodsList = null;
		if ($paymentMethods && isset($paymentMethods['data'])) {
			if (!empty($paymentMethods['data']['methods'])) {
				$methodsList = $paymentMethods['data']['methods'];
			}
		}

		if ($methodsList && !empty($methodsList)) {
			error_log("[PonponPay] Using backend payment methods, count: " . count($methodsList));

			foreach ($methodsList as $method) {
				$network = $method['network'] ?? '';
				// 后端按网络分组返回 currencies 数组，需展开为 flat 列表
				$currencies = $method['currencies'] ?? [];
				// 兼容旧格式：如果有单独的 currency 字段
				if (empty($currencies) && !empty($method['currency'])) {
					$currencies = [$method['currency']];
				}

				if (!empty($network) && !empty($currencies)) {
					foreach ($currencies as $currency) {
						$key = $network . '_' . $currency;
						$displayName = ponponpay_get_network_display_name($network) . ' - ' . $currency;
						$supportedOptions[$key] = [
							'network' => $network,
							'currency' => $currency,
							'display' => $displayName
						];
					}
				}
			}
		}
	} catch (Exception $e) {
		error_log("[PonponPay] Failed to fetch payment methods: " . $e->getMessage());
	}

	// Fallback: if nothing available, show error
	if (empty($supportedOptions)) {
		error_log("[PonponPay] No available payment channels");
		return ponponpay_render_error_page(ponponpay_lang('no_payment_methods'));
	}

	// Use backend options directly
	$merchantSupportedOptions = $supportedOptions;

	$html = '
    <div class="coinpay-payment-container" style="max-width: 500px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background: #fff;">
        <div class="payment-header" style="text-align: center; margin-bottom: 30px;">
            <h3 style="color: #333; margin-bottom: 15px;">' . ponponpay_lang('choose_payment_method') . '</h3>
            <div class="payment-info" style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <p style="margin: 5px 0; color: #666;"><strong>' . ponponpay_lang('invoice_amount') . ':</strong> ' . htmlspecialchars($params['amount']) . ' ' . htmlspecialchars($params['currency'] ?: 'CNY') . '</p>
                <p style="margin: 5px 0; color: #666;"><strong>' . ponponpay_lang('payable_amount') . ':</strong> <span id="crypto-amount">' . ponponpay_lang('please_select_method') . '</span></p>
            </div>
        </div>

        <div class="payment-form">
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">' . ponponpay_lang('select_network_currency') . '</label>
                <select id="payment-option" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    <option value="">' . ponponpay_lang('please_choose_network') . '</option>';

	foreach ($merchantSupportedOptions as $key => $option) {
		$html .= '<option value="' . htmlspecialchars($key) . '" data-network="' . htmlspecialchars($option['network']) . '" data-currency="' . htmlspecialchars($option['currency']) . '">' . htmlspecialchars($option['display']) . '</option>';
	}

	$html .= '
                </select>
            </div>

            <button id="create-payment" style="width: 100%; padding: 15px 25px; font-size: 18px; border: none; border-radius: 4px; cursor: pointer; background-color: #007bff; color: white;">
                <i class="fas fa-coins"></i> ' . ponponpay_lang('create_crypto_payment') . '
            </button>
        </div>

        <div id="loading" style="text-align: center; display: none; margin-top: 20px;">
            <div style="display: inline-block; width: 3rem; height: 3rem; border: 4px solid #f3f3f3; border-top: 4px solid #007bff; border-radius: 50%; animation: spin 1s linear infinite;"></div>
            <p>' . ponponpay_lang('creating_order') . '</p>
        </div>

        <div id="error-message" style="display: none; margin-top: 20px; padding: 15px; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; color: #721c24;"></div>
    </div>

    <style>
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    #crypto-amount {
        font-weight: bold;
        color: #28a745;
    }
    </style>

    <script>
    // Helper: add params to URL
    function addUrlParams(url, params) {
        const urlObj = new URL(url);
        Object.keys(params).forEach(key => {
            urlObj.searchParams.set(key, params[key]);
        });
        return urlObj.toString();
    }

    document.addEventListener("DOMContentLoaded", function() {
        var exchangeRate = ' . json_encode(floatval($params['exchange_rate'] ?: 1.0)) . ';
        var originalAmount = ' . json_encode(floatval($params['amount'] ?: 0)) . ';

        var langPleaseSelect = ' . json_encode(ponponpay_lang('please_select_method')) . ';
        var langPleaseSelectNetwork = ' . json_encode(ponponpay_lang('please_select_network')) . ';
        var langFailedCreateOrder = ' . json_encode(ponponpay_lang('failed_create_order')) . ';
        var langNetworkError = ' . json_encode(ponponpay_lang('network_error_retry')) . ';

        function updateCryptoAmount() {
            var selectedOption = document.getElementById("payment-option");
            if (selectedOption.value) {
                var currency = selectedOption.options[selectedOption.selectedIndex].getAttribute("data-currency");
                var cryptoAmount = originalAmount / exchangeRate;
                document.getElementById("crypto-amount").textContent = cryptoAmount + " " + currency;
            } else {
                document.getElementById("crypto-amount").textContent = langPleaseSelect;
            }
        }

        document.getElementById("payment-option").addEventListener("change", updateCryptoAmount);

        document.getElementById("create-payment").addEventListener("click", function() {
            var selectedOption = document.getElementById("payment-option");

            if (!selectedOption.value) {
                showError(langPleaseSelectNetwork);
                return;
            }

            var network = selectedOption.options[selectedOption.selectedIndex].getAttribute("data-network");
            var currency = selectedOption.options[selectedOption.selectedIndex].getAttribute("data-currency");

            showLoading();

            fetch(addUrlParams(window.location.href, {
                act: "create_order",
                network: network,
                currency: currency
            }))
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    if (data.success) {
                        if (data.redirect_to_frontend && data.payment_url) {
                            // Open payment page in a new tab
                            window.open(data.payment_url, "_blank");
                        } else {
                            window.location.reload();
                        }
                    } else {
                        showError(data.error || langFailedCreateOrder);
                    }
                })
                .catch(error => {
                    hideLoading();
                    showError(langNetworkError);
                });
        });

        function showLoading() {
            document.getElementById("create-payment").disabled = true;
            document.getElementById("loading").style.display = "block";
            document.getElementById("error-message").style.display = "none";
        }

        function hideLoading() {
            document.getElementById("create-payment").disabled = false;
            document.getElementById("loading").style.display = "none";
        }

        function showError(message) {
            document.getElementById("error-message").textContent = message;
            document.getElementById("error-message").style.display = "block";
        }
    });
    </script>';

	return $html;
}

/**
 * Create API order.
 */
function ponponpay_create_api_order($params)
{
	// Clear previous output buffer
	if (ob_get_level()) {
		ob_clean();
	}
	header('Content-Type: application/json');

	try {
		$network = $_GET['network'] ?? $params['default_network'];
		$currency = $_GET['currency'] ?? $params['default_currency'];

		// Create order number using API key + invoice id hash
		// Format: WHMCS_{invoiceid}_{8-char hash}
		$hashSource = $params['api_key'] . '_' . $params['invoiceid'];
		$hash = substr(md5($hashSource), 0, 8);
		$orderNo = 'WHMCS_' . $params['invoiceid'] . '_' . $hash;

		// Debug: order number
		error_log("[PonponPay] Generated order number: " . $orderNo . " (invoice: " . $params['invoiceid'] . ")");

		// Callback and redirect URLs (WHMCS system URL)
		$systemUrl = rtrim($params['systemurl'], '/');
		$notifyUrl = $systemUrl . '/modules/gateways/callback/ponponpay.php';
		$redirectUrl = $params['returnurl'];  // redirect to invoice page after payment

		// Debug callbacks
		error_log("[PonponPay] Notify URL: " . $notifyUrl);
		error_log("[PonponPay] Redirect URL: " . $redirectUrl);

		// Prepare backend order payload
		$orderData = [
			'mch_order_id' => $orderNo,
			'currency' => $currency,
			'network' => ponponpay_map_network_to_backend($network),
			'amount' => floatval($params['amount'] / floatval($params['exchange_rate'] ?: 1.0)),
			'product_no' => 'WHMCS_INVOICE_' . $params['invoiceid'],
			'type' => 'WHMCS',
			'notify_url' => $notifyUrl,      // ✅ callback (backend receives payment notice)
			'redirect_url' => $redirectUrl,  // ✅ redirect after payment
			'extra' => json_encode([
				'whmcs_invoice_id' => $params['invoiceid'],
				'original_amount' => $params['amount'],
				'original_currency' => $params['currency'],
				'exchange_rate' => floatval($params['exchange_rate'] ?: 1.0),
			])
		];

		// Debug: order creation start
		error_log("[PonponPay] Creating payment order: " . $orderNo);
		error_log("[PonponPay] Order payload: " . json_encode($orderData, JSON_UNESCAPED_UNICODE));

		// Call backend order API
		$apiUrl = ponponpay_get_api_url();
		error_log("[PonponPay] Order API: " . $apiUrl . '/api/v1/pay/sdk/order/add');

		$response = ponponpay_call_api($apiUrl . '/api/v1/pay/sdk/order/add', $orderData, $params['api_key']);

		if ($response && $response['code'] == 0) {
			// Debug: order created
			error_log("[PonponPay] Order created: " . $orderNo);
			error_log("[PonponPay] Order response: " . json_encode($response, JSON_UNESCAPED_UNICODE));

			// Log success
			ponponpay_safe_log('PonponPay Order', [
				'order_no' => $orderNo,
				'invoice_id' => $params['invoiceid'],
				'amount' => $orderData['amount'],
				'currency' => $currency,
				'network' => $orderData['network']
			], 'Order Created Successfully');

			// Use payment_url from backend
			$paymentUrl = $response['data']['payment_url'] ?? '';
			if (empty($paymentUrl)) {
				// Fallback build URL
				$paymentUrl = ponponpay_get_api_url() . '/pay/' . ($response['data']['trade_id'] ?? '');
			}

			// Return URL for redirect
			echo json_encode([
				'success' => true,
				'redirect_to_frontend' => true,
				'trade_id' => $response['data']['trade_id'] ?? null,
				'payment_url' => $paymentUrl
			]);
			exit; // ensure no further output

		} else {
			// Debug: creation failed
			error_log("[PonponPay] Order creation failed: " . ($response['message'] ?? 'Unknown error'));
			error_log("[PonponPay] Failure response: " . json_encode($response, JSON_UNESCAPED_UNICODE));
			throw new Exception($response['message'] ?? 'Failed to create order');
		}

	} catch (Exception $e) {
		ponponpay_safe_log('PonponPay Error', [
			'function' => 'ponponpay_create_api_order',
			'error' => $e->getMessage(),
			'invoice_id' => $params['invoiceid']
		], 'Order Creation Failed');

		echo json_encode(['success' => false, 'error' => $e->getMessage()]);
	}
	exit;
}

/**
 * Check order status.
 */
function ponponpay_check_order_status($params)
{
	// Clear previous output buffer
	if (ob_get_level()) {
		ob_clean();
	}
	header('Content-Type: application/json');

	try {
		// Support order_no or trade_id queries
		$orderNo = $_GET['order_no'] ?? '';
		$tradeId = $_GET['trade_id'] ?? '';

		if (empty($orderNo) && empty($tradeId)) {
			error_log("[PonponPay] Order status check failed: both order_no and trade_id are empty");
			throw new Exception(ponponpay_lang('order_number_required'));
		}

		// Prefer trade_id when provided
		$queryParam = $tradeId ? $tradeId : $orderNo;
		$queryField = $tradeId ? 'trade_id' : 'mch_order_id';

		// Debug log
		error_log("[PonponPay] Checking order status: " . $queryParam . " (field: " . $queryField . ")");

		$apiUrl = ponponpay_get_api_url();
		error_log("[PonponPay] Order detail API: " . $apiUrl . '/api/v1/pay/sdk/order/detail');

		$result = ponponpay_call_api($apiUrl . '/api/v1/pay/sdk/order/detail', [
			$queryField => $queryParam
		], $params['api_key']);

		// Debug log
		error_log("[PonponPay] Order status response: " . json_encode($result, JSON_UNESCAPED_UNICODE));

		echo json_encode(['success' => true, 'data' => $result['data'] ?? null]);

	} catch (Exception $e) {
		ponponpay_safe_log('PonponPay Error', [
			'function' => 'ponponpay_check_order_status',
			'error' => $e->getMessage(),
			'order_no' => $orderNo
		], 'Order Status Check Failed');

		echo json_encode(['success' => false, 'error' => $e->getMessage()]);
	}
	exit;
}

/**
 * Check if order already exists for this invoice.
 */
function ponponpay_check_existing_order($params)
{
	try {
		// Generate order number consistently for lookup
		$hashSource = $params['api_key'] . '_' . $params['invoiceid'];
		$hash = substr(md5($hashSource), 0, 8);
		$orderNo = 'WHMCS_' . $params['invoiceid'] . '_' . $hash;

		// Debug existing order check
		error_log("[PonponPay] Checking existing order: " . $orderNo);

		$apiUrl = ponponpay_get_api_url();
		$result = ponponpay_call_api($apiUrl . '/api/v1/pay/sdk/order/detail', [
			'mch_order_id' => $orderNo
		], $params['api_key']);

		// If API returns data
		if (isset($result['data']) && !empty($result['data'])) {
			$orderData = $result['data'];

			// Only return pending orders (status 1)
			if (isset($orderData['status']) && $orderData['status'] == 1) {
				// Derive network/currency from order
				$network = $orderData['network'] ?? 'Tron';
				$currency = $orderData['currency'] ?? 'USDT';

				error_log("[PonponPay] Found existing order: " . json_encode($orderData, JSON_UNESCAPED_UNICODE));

				return [
					'data' => $orderData,
					'network' => $network,
					'currency' => $currency
				];
			} else {
				error_log("[PonponPay] Order not pending: " . ($orderData['status'] ?? 'unknown'));
			}
		}

		return null;
	} catch (Exception $e) {
		// Not found or call failed
		error_log("[PonponPay] Check existing order failed: " . $e->getMessage());
		return null;
	}
}

/**
 * Render basic payment (without API).
 */
function ponponpay_render_basic_payment($params)
{
	$html = '<div style="max-width: 500px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">';
	$html .= '<h3>' . ponponpay_lang('basic_payment_title') . '</h3>';
	$html .= '<p><strong>' . ponponpay_lang('invoice_amount') . ':</strong> ' . htmlspecialchars($params['amount']) . ' ' . htmlspecialchars($params['currency']) . '</p>';
	$html .= '<p><strong>Invoice ID:</strong> ' . htmlspecialchars($params['invoiceid']) . '</p>';
	$html .= '<div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 4px; margin: 20px 0;">';
	$html .= '<p><strong>' . ponponpay_lang('setup_reminder') . '</strong></p>';
	$html .= '<p>' . ponponpay_lang('setup_reminder_desc') . '</p>';
	$html .= '<ul>';
	$html .= '<li>' . ponponpay_lang('setup_merchant_id') . '</li>';
	$html .= '<li>' . ponponpay_lang('setup_api_key_secret') . '</li>';
	$html .= '<li>' . ponponpay_lang('setup_wallet_address') . '</li>';
	$html .= '</ul>';
	$html .= '</div>';
	$html .= '</div>';

	return $html;
}

/**
 * Render error page.
 */
function ponponpay_render_error_page($error)
{
	return '<div style="max-width: 500px; margin: 0 auto; padding: 20px; border: 1px solid #dc3545; border-radius: 8px; background: #f8d7da; color: #721c24;">
                <h4>' . ponponpay_lang('payment_system_error') . '</h4>
                <p>' . htmlspecialchars($error) . '</p>
                <p><em>' . ponponpay_lang('contact_support') . '</em></p>
            </div>';
}





// ponponpay_get_api_url is defined in includes/hooks/ponponpay_config.php

/**
 * Plugin activation (called when saving WHMCS config)
 */
function ponponpay_plugin_activate($params)
{
	try {
		$apiUrl = ponponpay_get_api_url();

		// Debug log
		error_log("[PonponPay] Calling plugin activate API: " . $apiUrl . '/api/v1/pay/sdk/plugin/activate');

		// Activate plugin
		$response = ponponpay_call_api(
			$apiUrl . '/api/v1/pay/sdk/plugin/activate',
			[
				'plugin_type' => 'whmcs'
			],
			$params['api_key']
		);

		error_log("[PonponPay] Plugin activation response: " . json_encode($response, JSON_UNESCAPED_UNICODE));
		return $response;
	} catch (Exception $e) {
		error_log("[PonponPay] Plugin activation exception: " . $e->getMessage());
		return [
			'code' => -1,
			'message' => 'Plugin activation failed: ' . $e->getMessage()
		];
	}
}

/**
 * Helper functions.
 */
function ponponpay_get_network_display_name($network)
{
	$names = [
		'Tron' => ponponpay_lang('network_tron'),
		'Ethereum' => ponponpay_lang('network_ethereum'),
		'Polygon' => ponponpay_lang('network_polygon'),
		'Solana' => ponponpay_lang('network_solana')
	];
	return $names[$network] ?? $network;
}

// Map network names to backend enums
function ponponpay_map_network_to_backend($network)
{
	$mapping = [
		'Tron' => 'Tron',
		'TRC20' => 'Tron',
		'Ethereum' => 'Ethereum',
		'ERC20' => 'Ethereum',
		'Polygon' => 'Polygon',
		'POLYGON' => 'Polygon',
		'Solana' => 'Solana',
		'SOLANA' => 'Solana',
		'SOL' => 'Solana',
		'BSC' => 'BSC',
		'BEP20' => 'BSC',
	];
	return $mapping[$network] ?? $network;
}

function ponponpay_call_api($url, $data, $apiKey)
{
	// Debug log - API call start
	error_log("[PonponPay] API call started: " . $url);
	error_log("[PonponPay] Payload: " . json_encode($data, JSON_UNESCAPED_UNICODE));
	error_log("[PonponPay] API key prefix: " . substr($apiKey, 0, 8) . "...");

	// Log API call start
	ponponpay_safe_log('PonponPay Debug', [
		'function' => 'ponponpay_call_api',
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
			'User-Agent: WHMCS-PonponPay/2.0'
		]
	]);

	$response = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$error = curl_error($ch);
	curl_close($ch);

	// Debug log - API call result
	error_log("[PonponPay] API status code: " . $httpCode);
	if ($error) {
		error_log("[PonponPay] CURL error: " . $error);
	}
	error_log("[PonponPay] Response length: " . strlen($response));
	error_log("[PonponPay] Response preview: " . substr($response, 0, 200));

	// Log API call result
	ponponpay_safe_log('PonponPay Debug', [
		'function' => 'ponponpay_call_api',
		'url' => $url,
		'http_code' => $httpCode,
		'curl_error' => $error,
		'response_length' => strlen($response),
		'response_preview' => substr($response, 0, 200),
		'step' => 'api_call_result'
	], 'API Call Result');

	if ($error) {
		error_log("[PonponPay] API request failed: " . $error);
		ponponpay_safe_log('PonponPay Error', [
			'function' => 'ponponpay_call_api',
			'url' => $url,
			'curl_error' => $error,
			'step' => 'curl_error'
		], 'API Curl Error');
		throw new Exception('API request failed: ' . $error);
	}

	if ($httpCode !== 200) {
		error_log("[PonponPay] API returned HTTP error: " . $httpCode . ", response: " . $response);
		ponponpay_safe_log('PonponPay Error', [
			'function' => 'ponponpay_call_api',
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
		error_log("[PonponPay] JSON decode failed: " . json_last_error_msg());
	} else {
		error_log("[PonponPay] API call success, response: " . json_encode($decoded, JSON_UNESCAPED_UNICODE));
	}

	ponponpay_safe_log('PonponPay Debug', [
		'function' => 'ponponpay_call_api',
		'url' => $url,
		'decoded_response' => $decoded,
		'step' => 'api_call_success'
	], 'API Call Success');

	return $decoded;
}

/**
 * Admin configuration validation.
 */
function ponponpay_config_validate($params)
{
	$errors = [];

	// Basic parameter validation
	if (!empty($params['exchange_rate']) && (!is_numeric($params['exchange_rate']) || floatval($params['exchange_rate']) <= 0)) {
		$errors[] = ponponpay_lang('invalid_exchange_rate');
	}

	// Validate API credentials when provided
	if (!empty($params['api_key'])) {

		// Validate API key length
		if (strlen($params['api_key']) < 32) {
			$errors[] = '<strong style="color: #dc3545;">' . ponponpay_lang('invalid_api_key_format') . '</strong><br/>'
				. ponponpay_lang('api_key_length_error', strlen($params['api_key'])) . '<br/>'
				. '<strong>' . ponponpay_lang('fix') . ':</strong> ' . ponponpay_lang('api_key_fix');
			return $errors;
		}

		// Debug log - validation start
		error_log("[PonponPay] Start config validation, API Key: " . substr($params['api_key'], 0, 8) . "...");

		try {
			// Call plugin activation
			error_log("[PonponPay] Calling plugin activation");
			$activateResult = ponponpay_plugin_activate($params);

			if (!$activateResult || $activateResult['code'] != 0) {
				$errorMessage = $activateResult['message'] ?? 'Unknown error';

				$errorMsg = '<strong style="color: #dc3545;">' . ponponpay_lang('activation_failed') . '</strong><br/>' . $errorMessage;

				error_log("[PonponPay] " . strip_tags($errorMsg));
				$errors[] = $errorMsg;

				// Additional help
				$errors[] = '<div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin-top: 10px;">'
					. ponponpay_lang('settings_saved_inactive')
					. '</div>';

				return $errors;
			}

			// Activation succeeded
			error_log("[PonponPay] Plugin activation succeeded");

			error_log("[PonponPay] ✅ API credentials verified");

			// WHMCS only shows success via logging; no extra action needed

		} catch (Exception $e) {
			$errorMsg = '<strong style="color: #dc3545;">' . ponponpay_lang('api_connection_error') . '</strong><br/>'
				. ponponpay_lang('api_connection_error_desc') . '<br/>'
				. '<strong>' . ponponpay_lang('details') . ':</strong> ' . $e->getMessage() . '<br/>'
				. '<strong>' . ponponpay_lang('fix') . ':</strong> ' . ponponpay_lang('api_connection_fix');
			error_log("[PonponPay] " . strip_tags($errorMsg));
			$errors[] = $errorMsg;

			// Additional help
			$errors[] = '<div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin-top: 10px;">'
				. ponponpay_lang('settings_saved_unverified')
				. '</div>';
		}
	}

	// Log success when no errors
	if (empty($errors)) {
		error_log("[PonponPay] Configuration validated successfully");
	}

	return $errors;
}

/**
 * Gateway activation.
 */
function ponponpay_activate($params)
{
	return [
		'status' => 'success',
		'description' => ponponpay_lang('gateway_activated')
	];
}

/**
 * Gateway deactivation.
 */
function ponponpay_deactivate($params)
{
	return [
		'status' => 'success',
		'description' => ponponpay_lang('gateway_deactivated')
	];
}

/**
 * Render payment page HTML
 */
function ponponpay_generate_payment_page($orderData, $currency, $network, $params)
{
	$address = $orderData['address'] ?? '';
	$actualAmount = $orderData['actual_amount'] ?? 0;
	$expirationTime = $orderData['expiration_time'] ?? 0;
	$tradeId = $orderData['trade_id'] ?? '';

	// Countdown milliseconds
	$remainingTime = max(0, $expirationTime - time()) * 1000;

	// Build QR code URL
	$qrContent = urlencode($address);
	$qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . $qrContent;

	// Network display name
	$networkDisplay = ponponpay_get_network_display_name($network);

	// Language strings for JavaScript
	$langOrderExpired = ponponpay_lang('order_expired');
	$langCopied = ponponpay_lang('copied');
	$langChecking = ponponpay_lang('checking');

	$html = '
    <div class="coinpay-payment-page" style="width: 100%; max-width: 100%; padding: 15px; border: 1px solid #ddd; border-radius: 6px; background: #fff; font-family: Arial, sans-serif; box-sizing: border-box;">
        <div class="payment-header" style="text-align: center; margin-bottom: 20px;">
            <h2 style="color: #333; margin: 0 0 8px 0; font-size: 18px;">' . ponponpay_lang('crypto_payment') . '</h2>
            <div class="countdown-timer" style="background: #f8f9fa; padding: 12px; border-radius: 6px; border: 1px solid #e9ecef;">
                <div style="color: #666; font-size: 12px; margin-bottom: 3px;">' . ponponpay_lang('time_remaining') . '</div>
                <div id="countdown" style="font-size: 20px; font-weight: bold; color: #dc3545;" data-expiration="' . $expirationTime . '">
                    ' . ponponpay_lang('calculating') . '
                </div>
            </div>
        </div>

        <div class="payment-info" style="text-align: center; margin-bottom: 20px;">
            <div style="margin-bottom: 15px;">
                <div style="font-size: 16px; color: #333; margin-bottom: 3px;">' . ponponpay_lang('amount_to_pay') . '</div>
                <div style="font-size: 22px; font-weight: bold; color: #28a745;">' . $actualAmount . ' ' . $currency . '</div>
            </div>

            <div style="margin-bottom: 15px;">
                <div style="font-size: 14px; color: #666; margin-bottom: 8px;">' . ponponpay_lang('scan_qr_to_pay') . '</div>
                <img src="' . $qrCodeUrl . '" alt="' . ponponpay_lang('payment_qr_code') . '" style="max-width: 160px; width: 100%; height: auto; border: 1px solid #ddd; border-radius: 6px;">
            </div>
        </div>

        <div class="payment-details" style="background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 15px;">
            <div style="margin-bottom: 12px;">
                <label style="display: block; margin-bottom: 3px; font-weight: bold; color: #333; font-size: 14px;">' . ponponpay_lang('network') . ':</label>
                <div style="font-size: 14px; color: #495057;">' . $networkDisplay . '</div>
            </div>

            <div style="margin-bottom: 0;">
                <label style="display: block; margin-bottom: 3px; font-weight: bold; color: #333; font-size: 14px;">' . ponponpay_lang('payment_address') . ':</label>
                <div style="display: flex; align-items: center; background: white; padding: 8px; border: 1px solid #ddd; border-radius: 4px; flex-wrap: wrap; gap: 8px;">
                    <input type="text" id="payment-address" value="' . htmlspecialchars($address) . '" readonly
                           style="flex: 1; min-width: 200px; border: none; outline: none; font-family: monospace; font-size: 12px; background: transparent;">
                    <button onclick="copyAddress()" style="padding: 6px 12px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; white-space: nowrap;">
                        <i class="fas fa-copy"></i> ' . ponponpay_lang('copy') . '
                    </button>
                </div>
            </div>
        </div>

        <div class="payment-instructions" style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 12px; border-radius: 6px; margin-bottom: 15px;">
            <h4 style="margin: 0 0 8px 0; color: #856404; font-size: 14px;">' . ponponpay_lang('payment_tips') . '</h4>
            <ul style="margin: 0; padding-left: 18px; color: #856404; font-size: 12px; line-height: 1.4;">
                <li>' . ponponpay_lang('tip_correct_network', $networkDisplay) . '</li>
                <li>' . ponponpay_lang('tip_exact_amount', $actualAmount, $currency) . '</li>
                <li>' . ponponpay_lang('tip_complete_before_timer') . '</li>
                <li>' . ponponpay_lang('tip_auto_redirect') . '</li>
            </ul>
        </div>

        <div class="payment-actions" style="text-align: center; display: flex; gap: 8px; flex-wrap: wrap; justify-content: center;">
            <button onclick="checkPaymentStatus()" style="padding: 10px 16px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; flex: 1; min-width: 120px; max-width: 150px;">
                <i class="fas fa-sync"></i> ' . ponponpay_lang('check_status') . '
            </button>
            <button onclick="window.location.reload()" style="padding: 10px 16px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; flex: 1; min-width: 120px; max-width: 150px;">
                <i class="fas fa-redo"></i> ' . ponponpay_lang('refresh_page') . '
            </button>
        </div>
    </div>

    <script>
        let countdownInterval;
        let tradeId = "' . $tradeId . '";
        let langOrderExpired = ' . json_encode($langOrderExpired) . ';
        let langCopied = ' . json_encode($langCopied) . ';
        let langChecking = ' . json_encode($langChecking) . ';

        // Copy address
        function copyAddress() {
            const addressInput = document.getElementById("payment-address");
            addressInput.select();
            document.execCommand("copy");

            // Show success hint
            const button = event.target.closest("button");
            const originalText = button.innerHTML;
            button.innerHTML = "<i class=\"fas fa-check\"></i> " + langCopied;
            button.style.background = "#28a745";

            setTimeout(() => {
                button.innerHTML = originalText;
                button.style.background = "#007bff";
            }, 2000);
        }

        // Countdown
        function updateCountdown() {
            const countdownElement = document.getElementById("countdown");
            const expirationTime = parseInt(countdownElement.getAttribute("data-expiration"));
            const currentTime = Math.floor(Date.now() / 1000);
            const remainingSeconds = Math.max(0, expirationTime - currentTime);

            if (remainingSeconds <= 0) {
                countdownElement.innerHTML = langOrderExpired;
                countdownElement.style.color = "#dc3545";
                clearInterval(countdownInterval);
                return;
            }

            const minutes = Math.floor(remainingSeconds / 60);
            const seconds = remainingSeconds % 60;
            countdownElement.innerHTML = String(minutes).padStart(2, "0") + ":" + String(seconds).padStart(2, "0");

            // Turn red when less than 5 minutes
            if (remainingSeconds < 300) {
                countdownElement.style.color = "#dc3545";
            }
        }

        // Check payment status
        function checkPaymentStatus() {
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = "<i class=\"fas fa-spinner fa-spin\"></i> " + langChecking;
            button.disabled = true;

            // AJAX status check
            fetch(window.location.href + "?act=check_status&trade_id=" + encodeURIComponent(tradeId), {
                method: "GET"
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data && data.data.status === 1) {
                    // Paid, redirect
                    window.location.href = "' . $params['returnurl'] . '";
                } else {
                    // Not paid
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error("Failed to check payment status:", error);
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }

        // Start countdown
        updateCountdown();
        countdownInterval = setInterval(updateCountdown, 1000);

        // Auto-check payment status every 30 seconds
        setInterval(() => {
            checkPaymentStatus();
        }, 30000);
    </script>

    <style>
        @import url("https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css");

        .coinpay-payment-page button:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .coinpay-payment-page button:active {
            transform: translateY(0);
        }

        .coinpay-payment-page input[readonly] {
            cursor: pointer;
        }

        .coinpay-payment-page input[readonly]:focus {
            background: #e3f2fd;
        }
    </style>';

	return $html;
}
