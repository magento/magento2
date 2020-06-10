<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Tax\ItemFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Model\Sales\Order\TaxFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/default_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/order.php');

$objectManager = Bootstrap::getObjectManager();
/** @var Order $order */
$order = $objectManager->get(OrderInterfaceFactory::class)->create()->loadByIncrementId('100000001');
$amount = 45;
$taxFactory = $objectManager->create(TaxFactory::class);

/** @var \Magento\Tax\Model\Sales\Order\Tax  $tax */
$tax = $taxFactory->create();
$tax->setOrderId($order->getId())
    ->setCode('US-NY-*-Rate 1')
    ->setTitle('US-NY-*-Rate 1')
    ->setPercent(8.37)
    ->setAmount($amount)
    ->setBaseAmount($amount)
    ->setBaseRealAmount($amount);
$tax->save();

$salesOrderFactory = $objectManager->create(ItemFactory::class);

/** @var \Magento\Sales\Model\Order\Tax\Item $salesOrderItem */
$salesOrderItem = $salesOrderFactory->create();
$salesOrderItem->setOrderId($order->getId())
    ->setStoreId($objectManager->get(StoreManagerInterface::class)->getStore()->getId())
    ->setProductOptions([]);
$salesCollection = $objectManager->create(\Magento\Sales\Model\ResourceModel\Order\Item::class);
$salesCollection->save($salesOrderItem);

/** @var \Magento\Sales\Model\Order\Tax\Item $salesOrderTaxItem */
$salesOrderTaxItem = $salesOrderFactory->create();
$salesOrderTaxItem->setTaxId($tax->getId())
    ->setTaxPercent(8.37)
    ->setTaxAmount($amount)
    ->setBaseAmount($amount)
    ->setRealAmount($amount)
    ->setRealBaseAmount($amount)
    ->setAppliedTaxes([$tax])
    ->setTaxableItemType('shipping')
    ->setItemId($salesOrderItem->getId());

$taxItemCollection = $objectManager->create(\Magento\Sales\Model\ResourceModel\Order\Tax\Item::class);
$taxItemCollection->save($salesOrderTaxItem);
