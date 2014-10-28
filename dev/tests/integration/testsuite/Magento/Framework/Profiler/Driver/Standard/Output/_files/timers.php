<?php
/**
 * Fixture timers statistics for output tests
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
$timer = new \Magento\Framework\Profiler\Driver\Standard\Stat();
$timer->start('root', 0.01, 50000, 1000);

$timer->start('root->init', 0.02, 55000, 1400);

$timer->start('root->init->init_store', 0.02, 55000, 1400);
$timer->stop('root->init->init_store', 0.03, 56000, 1450);
$timer->start('root->init->init_store', 0.02, 56500, 1550);
$timer->stop('root->init->init_store', 0.03, 57500, 1600);

$timer->stop('root->init', 0.06, 57500, 1600);

$timer->stop('root', 0.09, 100000, 2000);

$timer->start('system', 0.11, 50000, 1000);
$timer->stop('system', 0.13, 60000, 1200);
$timer->start('system', 0.14, 50000, 1000);
$timer->stop('system', 0.15, 60000, 1200);

return $timer;
