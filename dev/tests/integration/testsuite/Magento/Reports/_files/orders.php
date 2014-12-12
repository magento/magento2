<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

require __DIR__ . '/../../../Magento/Sales/_files/order.php';

// refresh report statistics
/** @var \Magento\Sales\Model\Resource\Report\Order $reportResource */
$reportResource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Sales\Model\Resource\Report\Order'
);
$reportResource->aggregate();
