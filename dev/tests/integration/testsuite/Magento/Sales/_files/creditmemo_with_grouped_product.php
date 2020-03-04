<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Item;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/order_with_grouped_product.php';

$objectManager = Bootstrap::getObjectManager();

$creditmemoFactory = $objectManager->get(CreditmemoFactory::class);
$creditmemo = $creditmemoFactory->createByOrder($order, $order->getData());
$creditmemo->setOrder($order);
$creditmemo->setState(Creditmemo::STATE_OPEN);
$creditmemo->setIncrementId('100000002');
$creditmemo->save();

$orderItem = current($order->getAllItems());
$orderItem->setName('Test item')
    ->setQtyRefunded(1)
    ->setQtyInvoiced(10)
    ->setOriginalPrice(20);

$creditItem = $objectManager->get(Item::class);
$creditItem->setCreditmemo($creditmemo)
    ->setName('Creditmemo item')
    ->setOrderItemId($orderItem->getId())
    ->setQty(1)
    ->setPrice(20)
    ->save();
