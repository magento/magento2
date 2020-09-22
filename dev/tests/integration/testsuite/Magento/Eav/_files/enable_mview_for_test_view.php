<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var \Magento\Framework\Mview\View $view */
$view = $objectManager->create(\Magento\Framework\Mview\View::class);
$view->load('test_view');
$view->subscribe();
