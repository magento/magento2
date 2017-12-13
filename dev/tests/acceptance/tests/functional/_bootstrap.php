<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// Here you can initialize variables that will be available to your tests
require_once dirname(__DIR__) . '/_bootstrap.php';

$RELATIVE_TESTS_MODULE_PATH = '/Magento/FunctionalTest';

defined('TESTS_BP') || define('TESTS_BP', __DIR__);
defined('TESTS_MODULE_PATH') || define('TESTS_MODULE_PATH', TESTS_BP . $RELATIVE_TESTS_MODULE_PATH);
