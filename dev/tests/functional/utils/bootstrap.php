<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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

$objectManagerFactory = \Magento\Framework\App\Bootstrap::createObjectManagerFactory(BP, $_SERVER);
$objectManager = $objectManagerFactory->create($_SERVER);
\Mtf\ObjectManagerFactory::configure($objectManager);
