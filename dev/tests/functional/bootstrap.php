<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

defined('MTF_BOOT_FILE') || define('MTF_BOOT_FILE', __FILE__);
defined('MTF_BP') || define('MTF_BP', str_replace('\\', '/', (__DIR__)));
defined('MTF_TESTS_PATH') || define('MTF_TESTS_PATH', MTF_BP . '/tests/app/');
defined('MTF_STATES_PATH') || define('MTF_STATES_PATH', MTF_BP . '/lib/Magento/Mtf/App/State/');

require_once __DIR__ . '/../../../app/bootstrap.php';
restore_error_handler();
require_once __DIR__ . '/vendor/autoload.php';
