<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define('PROJECT_ROOT', dirname(__DIR__));
require_once PROJECT_ROOT . '/vendor/autoload.php';
$RELATIVE_FW_PATH = '/vendor/magento/magento2-functional-testing-framework';

//Load constants from .env file
if (file_exists(PROJECT_ROOT . '/.env')) {
    $env = new \Dotenv\Loader(PROJECT_ROOT . '/.env');
    $env->load();

    if (array_key_exists('TESTS_MODULE_PATH', $_ENV) xor array_key_exists('TESTS_BP', $_ENV)) {
        throw new Exception('You must define both parameters TESTS_BP and TESTS_MODULE_PATH or neither parameter');
    }

    foreach ($_ENV as $key => $var) {
        defined($key) || define($key, $var);
    }
}
defined('FW_BP') || define('FW_BP', PROJECT_ROOT . $RELATIVE_FW_PATH);
