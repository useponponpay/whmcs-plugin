<?php
/**
 * PonponPay WHMCS Payment Gateway Module - Main Entry Point
 *
 * Crypto payment gateway entry point that loads the implementation in the folder.
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

// 加载文件夹内的主要实现
require_once __DIR__ . '/ponponpay/ponponpay_main.php';

// 加载API客户端
require_once __DIR__ . '/ponponpay/lib/PonponPayApi.php';

// 注意：所有的 ponponpay_* 函数都在 ponponpay_main.php 中定义
// 这个文件只是作为入口点，保持WHMCS的兼容性
