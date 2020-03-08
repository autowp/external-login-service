<?php

declare(strict_types=1);

error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';
/**
 * Start output buffering, if enabled
 */
if (defined('TESTS_AUTOWP_OB_ENABLED') && constant('TESTS_AUTOWP_OB_ENABLED')) {
    ob_start();
}
