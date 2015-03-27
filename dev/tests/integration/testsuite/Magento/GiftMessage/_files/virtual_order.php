<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
require __DIR__ . '/../../../Magento/Sales/_files/order.php';
/** @var \Magento\Sales\Model\Order $order */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$order = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId('100000001');
$order->setIsVirtual(1)->save();
