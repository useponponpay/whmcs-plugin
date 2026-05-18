<?php
/**
 * PolyPay WHMCS Payment Gateway Module - Main Entry Point
 *
 * Crypto payment gateway entry point that loads the implementation in the folder.
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

// 加载文件夹内的主要实现
require_once __DIR__ . '/polypay/polypay_main.php';

// 加载API客户端
require_once __DIR__ . '/polypay/lib/PolyPayApi.php';

// 注意：所有的 polypay_* 函数都在 polypay_main.php 中定义
// 这个文件只是作为入口点，保持WHMCS的兼容性
