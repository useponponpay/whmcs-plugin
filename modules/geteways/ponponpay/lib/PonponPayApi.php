<?php
/**
 * PonponPay API Client
 * 与 PonponPay API 交互的客户端类
 */

class PonponPayApi
{
    private $apiKey;
    private $apiUrl;
    private $timeout;

    /**
     * 构造函数
     */
    public function __construct($apiKey, $apiUrl, $timeout = 30)
    {
        $this->apiKey = $apiKey;
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->timeout = $timeout;
    }

    /**
     * 创建支付订单
     */
    public function createOrder($params)
    {
        $endpoint = '/order/create';
        return $this->makeRequest('POST', $endpoint, $params);
    }

    /**
     * 查询订单状态
     */
    public function queryOrder($orderNo)
    {
        $endpoint = '/order/status';
        return $this->makeRequest('POST', $endpoint, ['order_no' => $orderNo]);
    }

    /**
     * 获取支持的币种列表
     */
    public function getSupportedCoins()
    {
        $endpoint = '/coins';
        return $this->makeRequest('GET', $endpoint);
    }

    /**
     * 获取汇率
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
     * 发起HTTP请求
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

        // 基础设置
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2
        ]);

        // 根据请求方法设置参数
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
     * 验证回调签名
     */
    public function verifyCallback($data, $signature)
    {
        $computedSignature = hash_hmac('sha256', json_encode($data), $this->apiKey);
        return hash_equals($signature, $computedSignature);
    }

    /**
     * 生成二维码URL
     */
    public function generateQRCodeUrl($address, $amount, $coin)
    {
        // 根据不同币种生成对应的URI scheme
        $uri = '';

        switch (strtoupper($coin)) {
            case 'BTC':
                $uri = "bitcoin:{$address}?amount={$amount}";
                break;
            case 'ETH':
                $uri = "ethereum:{$address}?value=" . bcmul($amount, '1000000000000000000');
                break;
            case 'USDT':
                $uri = "ethereum:{$address}?value=0"; // USDT是ERC20代币
                break;
            default:
                $uri = "{$coin}:{$address}?amount={$amount}";
                break;
        }

        // 使用Google Charts API生成二维码（也可以使用其他服务）
        return "https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=" . urlencode($uri);
    }

    /**
     * 格式化金额
     */
    public function formatAmount($amount, $decimals = 8)
    {
        return rtrim(rtrim(number_format($amount, $decimals, '.', ''), '0'), '.');
    }

    /**
     * 获取网络名称
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
     * 获取币种图标URL
     */
    public function getCoinIconUrl($coin)
    {
        $coin = strtolower($coin);
        return "https://cryptoicons.org/api/icon/{$coin}/200";
    }

    /**
     * 检查API连通性
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
     * 获取钱包余额
     */
    public function getWalletBalance($coin = null)
    {
        $endpoint = '/wallet/balance';
        $params = $coin ? ['coin' => $coin] : [];
        return $this->makeRequest('GET', $endpoint, $params);
    }

    /**
     * 获取交易历史
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
