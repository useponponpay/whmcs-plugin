<?php
/**
 * PonponPay Gateway Configuration Hook
 * Logs gateway config changes.
 */

if (!defined('WHMCS')) {
    die('You cannot access this file directly.');
}

// Safe logger
function ponponpay_log($gateway, $data, $description)
{
    try {
        // Prefer WHMCS logTransaction
        if (function_exists('logTransaction')) {
            logTransaction($gateway, $data, $description);
            return;
        }

        // Fallback to file log
        $logFile = dirname(__DIR__, 2) . '/storage/logs/ponponpay_hook.log';
        $logDir = dirname($logFile);

        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $logEntry = date('Y-m-d H:i:s') . " [$gateway] $description: " . json_encode($data) . "\n";
        @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

    } catch (Exception $e) {
        // Swallow errors to avoid impacting flow
        error_log("PonponPay Hook Log Error: " . $e->getMessage());
    }
}

/**
 * Handle gateway config updates (log only).
 */
function ponponpay_handle_admin_config_update($vars)
{
    // Log access to gateway config page
    if ($vars['filename'] === 'configgateways') {
        ponponpay_log('PonponPay Hook Debug', [
            'function' => 'ponponpay_handle_admin_config_update',
            'filename' => $vars['filename'],
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'post_gateway' => $_POST['gateway'] ?? 'none'
        ], 'Gateway Config Page Accessed');
    }

    // React only on POST for this gateway
    if ($vars['filename'] === 'configgateways' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gateway']) && $_POST['gateway'] === 'ponponpay') {

        ponponpay_log('PonponPay Hook Debug', [
            'function' => 'ponponpay_handle_admin_config_update',
            'step' => 'config_update_detected'
        ], 'PonponPay Config Update Detected');

        try {
            // Fetch updated gateway config
            $gatewayConfig = getGatewayVariables('ponponpay');

            // Log success
            ponponpay_log('PonponPay Hook Success', [
                'api_key' => !empty($gatewayConfig['api_key']) ? substr($gatewayConfig['api_key'], 0, 8) . '...' : 'empty',
                'message' => 'Gateway config updated'
            ], 'Config Update Success');

        } catch (Exception $e) {
            ponponpay_log('PonponPay Hook Error', [
                'error' => $e->getMessage(),
                'request_uri' => $_SERVER['REQUEST_URI'] ?? ''
            ], 'Config Update Failed');
        }
    }
}




/**
 * Call API via hook
 */
function ponponpay_call_api_via_hook($url, $data, $apiKey)
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
            'User-Agent: WHMCS-PonponPay-Hook/2.0'
        ]
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    // Log API call result
    ponponpay_log('PonponPay Hook Debug', [
        'url' => $url,
        'http_code' => $httpCode,
        'curl_error' => $error ?: 'none',
        'response_length' => strlen($response)
    ], 'API Call Result');

    if ($error) {
        ponponpay_log('PonponPay Hook Error', [
            'url' => $url,
            'curl_error' => $error
        ], 'API Curl Error');
        throw new Exception('API request failed: ' . $error);
    }

    if ($httpCode !== 200) {
        ponponpay_log('PonponPay Hook Error', [
            'url' => $url,
            'http_code' => $httpCode,
            'response' => substr($response, 0, 500)
        ], 'API HTTP Error');
        throw new Exception('API returned HTTP error: ' . $httpCode);
    }

    $decoded = json_decode($response, true);

    if ($decoded === null) {
        ponponpay_log('PonponPay Hook Error', [
            'url' => $url,
            'response' => substr($response, 0, 500)
        ], 'API JSON Decode Error');
        throw new Exception('Failed to decode API JSON response');
    }

    return $decoded;
}

/**
 * API server URL helper
 */
if (!function_exists('ponponpay_get_api_url')) {
    function ponponpay_get_api_url()
    {
        return 'http://localhost:11050';
//        return 'https://api.ponponpay.com';
    }
}

// Register hook on admin pages
add_hook('AdminAreaPage', 1, 'ponponpay_handle_admin_config_update');
