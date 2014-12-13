<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

session_start();
defined('MTF_BOOT_FILE') || define('MTF_BOOT_FILE', __FILE__);
require_once __DIR__ . '/../../../app/bootstrap.php';
restore_error_handler();
require_once __DIR__ . '/vendor/autoload.php';
