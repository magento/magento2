<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
umask(0);

$mtfRoot = dirname(dirname(__FILE__));
$mtfRoot = str_replace('\\', '/', $mtfRoot);
define('MTF_BP', $mtfRoot);
define('MTF_TESTS_PATH', MTF_BP . '/tests/app/');

$appRoot = dirname(dirname(dirname(dirname(__DIR__))));
require $appRoot . '/app/bootstrap.php';
require __DIR__ . '/../vendor/autoload.php';

$objectManager = \Magento\Mtf\ObjectManagerFactory::getObjectManager();
\Magento\Mtf\ObjectManagerFactory::configure($objectManager);
