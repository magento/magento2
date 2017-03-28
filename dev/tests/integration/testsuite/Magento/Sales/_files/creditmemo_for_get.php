<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require 'default_rollback.php';
require __DIR__ . '/order.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Sales\Model\Order $order */
$order = $objectManager->create(\Magento\Sales\Model\Order::class);
$order->loadByIncrementId('100000001');

/** @var \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory */
$creditmemoFactory = $objectManager->get(\Magento\Sales\Model\Order\CreditmemoFactory::class);
$creditmemo = $creditmemoFactory->createByOrder($order, $order->getData());
$creditmemo->setOrder($order);
$creditmemo->setState(Magento\Sales\Model\Order\Creditmemo::STATE_OPEN);
$creditmemo->setIncrementId('100000001');
$creditmemo->save();

/** @var \Magento\Sales\Model\Order\Item $orderItem */
$orderItem = current($order->getAllItems());
$orderItem->setName('Test item')
    ->setQtyRefunded(1)
    ->setQtyInvoiced(10)
    ->setOriginalPrice(20);

/** @var \Magento\Sales\Model\Order\Creditmemo\Item $creditItem */
$creditItem = $objectManager->get(\Magento\Sales\Model\Order\Creditmemo\Item::class);
$creditItem->setCreditmemo($creditmemo)
    ->setName('Creditmemo item')
    ->setOrderItemId($orderItem->getId())
    ->setQty(1)
    ->setPrice(20)
    ->save();
