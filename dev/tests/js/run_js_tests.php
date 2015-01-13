<?php
/**
 * This script executes all Magento JavaScript unit tests.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

require __DIR__ . '/../../../app/autoload.php';
require __DIR__ . '/framework/JsTestRunner.php';

JsTestRunner::fromConfigFile()->runTests();

