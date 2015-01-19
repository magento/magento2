<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/../../../Magento/Sales/_files/order.php';

// refresh report statistics
/** @var \Magento\Sales\Model\Resource\Report\Order $reportResource */
$reportResource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Sales\Model\Resource\Report\Order'
);
$reportResource->aggregate();
