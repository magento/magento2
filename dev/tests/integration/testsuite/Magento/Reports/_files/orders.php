<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/../../../Magento/Sales/_files/order.php';

// refresh report statistics
/** @var \Magento\Sales\Model\ResourceModel\Report\Order $reportResource */
$reportResource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Sales\Model\ResourceModel\Report\Order'
);
$reportResource->aggregate();
