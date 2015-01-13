<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$mtfRoot = dirname(dirname(dirname(__FILE__)));
$mtfRoot = str_replace('\\', '/', $mtfRoot);
define('MTF_BP', $mtfRoot);
define('MTF_TESTS_PATH', MTF_BP . '/tests/app/');

require __DIR__ . '/../../../../../app/bootstrap.php';
require MTF_BP . '/vendor/autoload.php';

$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);

$om = $bootstrap->getObjectManager();
/** @var \Mtf\Util\Generate\Factory $generator */
$generator = $om->create('Mtf\Util\Generate\Factory');
$generator->launch();
\Mtf\Util\Generate\GenerateResult::displayResults();
