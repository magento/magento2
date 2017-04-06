<?php
/**
 * Fixture timers statistics for output tests
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
