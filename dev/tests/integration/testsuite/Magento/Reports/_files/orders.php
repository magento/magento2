<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/order.php');

// refresh report statistics
/** @var \Magento\Sales\Model\ResourceModel\Report\Order $reportResource */
$reportResource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Sales\Model\ResourceModel\Report\Order::class
);
$reportResource->aggregate();
