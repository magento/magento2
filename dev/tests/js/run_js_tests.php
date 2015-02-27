<?php
/**
 * This script executes all Magento JavaScript unit tests.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/../../../app/autoload.php';
require __DIR__ . '/framework/JsTestRunner.php';

JsTestRunner::fromConfigFile()->runTests();

