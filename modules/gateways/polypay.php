<?php
/**
 * PolyPay WHMCS Payment Gateway Module - Main Entry Point
 *
 * Crypto payment gateway entry point that loads the implementation in the folder.
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

// Load the main implementation inside the folder
require_once __DIR__ . '/polypay/polypay_main.php';

// Load the API client
require_once __DIR__ . '/polypay/lib/PolyPayApi.php';

// Note: all polypay_* functions are defined in polypay_main.php
// This file serves only as the entry point, keeping WHMCS compatibility
