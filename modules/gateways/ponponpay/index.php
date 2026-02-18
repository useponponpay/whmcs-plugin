<?php
/**
 * PonponPay WHMCS Payment Gateway Module - Index
 * Prevent direct access
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

// Redirect to the main module file
header("Location: ../ponponpay.php");
exit;
