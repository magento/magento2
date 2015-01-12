<?php
/**
 * Performance framework bootstrap script
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/../../../../app/bootstrap.php';
require_once __DIR__ . '/autoload.php';

$testsBaseDir = dirname(__DIR__);
$appBootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
$bootstrap = new \Magento\TestFramework\Performance\Bootstrap($appBootstrap, $testsBaseDir);
$bootstrap->cleanupReports();
return $bootstrap;
