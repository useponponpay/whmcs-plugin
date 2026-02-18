<?php
/**
 * PonponPay API Client
 * Client class for interacting with PonponPay API
 */

class PonponPayApi
{
    private $apiKey;
    private $apiUrl;
    private $timeout;

    /**
     * Constructor
     */
    public function __construct($apiKey, $apiUrl, $timeout = 30)
    {
        $this->apiKey = $apiKey;
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->timeout = $timeout;
    }

    /**
     * Create payment order
     */
    public function createOrder($params)
    {
        $endpoint = '/order/create';
        return $this->makeRequest('POST', $endpoint, $params);
    }

    /**
     * Query order status
     */
    public function queryOrder($orderNo)
    {
        $endpoint = '/order/status';
        return $this->makeRequest('POST', $endpoint, ['order_no' => $orderNo]);
    }

    /**
     * Get supported coins list
     */
    public function getSupportedCoins()
    {
        $endpoint = '/coins';
        return $this->makeRequest('GET', $endpoint);
    }

    /**
     * Get exchange rate
     */
    public function getExchangeRate($from, $to)
    {
        $endpoint = '/rate';
        return $this->makeRequest('GET', $endpoint, [
            'from' => $from,
            'to' => $to
        ]);
    }

    /**
     * Make HTTP request
     */
    private function makeRequest($method, $endpoint, $params = [])
    {
        $url = $this->apiUrl . $endpoint;

        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
            'User-Agent: WHMCS-PonponPay/2.0'
        ];

        $ch = curl_init();

        // Basic settings
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2
        ]);

        // Set parameters based on request method
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($params) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            }
        } elseif ($method === 'GET' && $params) {
            $url .= '?' . http_build_query($params);
            curl_setopt($ch, CURLOPT_URL, $url);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception('cURL Error: ' . $error);
        }

        $data = json_decode($response, true);

        if ($httpCode >= 400) {
            $errorMsg = $data['message'] ?? 'HTTP Error ' . $httpCode;
            throw new Exception($errorMsg, $httpCode);
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response: ' . json_last_error_msg());
        }

        return $data;
    }

    /**
     * Verify callback signature
     */
    public function verifyCallback($data, $signature)
    {
        $computedSignature = hash_hmac('sha256', json_encode($data), $this->apiKey);
        return hash_equals($signature, $computedSignature);
    }

    /**
     * Generate QR code URL
     */
    public function generateQRCodeUrl($address, $amount, $coin)
    {
        // Generate corresponding URI scheme based on different coins
        $uri = '';

        switch (strtoupper($coin)) {
            case 'BTC':
                $uri = "bitcoin:{$address}?amount={$amount}";
                break;
            case 'ETH':
                $uri = "ethereum:{$address}?value=" . bcmul($amount, '1000000000000000000');
                break;
            case 'USDT':
                $uri = "ethereum:{$address}?value=0"; // USDT is ERC20 token
                break;
            default:
                $uri = "{$coin}:{$address}?amount={$amount}";
                break;
        }

        // Use Google Charts API to generate QR code (can also use other services)
        return "https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=" . urlencode($uri);
    }

    /**
     * Format amount
     */
    public function formatAmount($amount, $decimals = 8)
    {
        return rtrim(rtrim(number_format($amount, $decimals, '.', ''), '0'), '.');
    }

    /**
     * Get network name
     */
    public function getNetworkName($network)
    {
        $networks = [
            'tron' => 'Tron (TRC20)',
            'ethereum' => 'Ethereum (ERC20)',
            'polygon' => 'Polygon',
            'bsc' => 'Binance Smart Chain (BEP20)',
            'solana' => 'Solana'
        ];

        return $networks[$network] ?? ucfirst($network);
    }

    /**
     * Get coin icon URL
     */
    public function getCoinIconUrl($coin)
    {
        $coin = strtolower($coin);
        return "https://cryptoicons.org/api/icon/{$coin}/200";
    }

    /**
     * Test API connectivity
     */
    public function testConnection()
    {
        try {
            $endpoint = '/ping';
            $response = $this->makeRequest('GET', $endpoint);
            return ['success' => true, 'data' => $response];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get wallet balance
     */
    public function getWalletBalance($coin = null)
    {
        $endpoint = '/wallet/balance';
        $params = $coin ? ['coin' => $coin] : [];
        return $this->makeRequest('GET', $endpoint, $params);
    }

    /**
     * Get transaction history
     */
    public function getTransactionHistory($limit = 10, $offset = 0)
    {
        $endpoint = '/transactions';
        return $this->makeRequest('GET', $endpoint, [
            'limit' => $limit,
            'offset' => $offset
        ]);
    }
}
