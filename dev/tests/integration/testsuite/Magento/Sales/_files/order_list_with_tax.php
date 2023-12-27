<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Sales\Model\Order\Tax\ItemFactory;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Model\Sales\Order\TaxFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/default_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/order_list.php');

$objectManager = Bootstrap::getObjectManager();
/** @var Collection $orderCollection */
$orderCollection = $objectManager->create(Collection::class);
$orderList = $orderCollection->addFieldToFilter(
    'increment_id',
    ['in' => ['100000002','100000003','100000004']]
)->getItems();
/** @var \Magento\Sales\Model\Order $order */
$order = $objectManager->create(\Magento\Sales\Model\Order::class);
$order->loadByIncrementId('100000001');
$payment = $order->getPayment();
$billingAddress = $order->getBillingAddress();
$shippingAddress = $order->getShippingAddress();
$items = $order->getItems();
$orderItem = reset($items);
/** @var array $orderList */
foreach ($orderList as $order) {
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

    /** @var \Magento\Sales\Model\Order\Tax\Item $salesOrderItem */
    $salesOrderTaxItem = $salesOrderFactory->create();
    $salesOrderTaxItem->setTaxId($tax->getId())
        ->setTaxPercent(8.37)
        ->setAmount($amount)
        ->setBaseAmount($amount)
        ->setRealAmount($amount)
        ->setRealBaseAmount($amount)
        ->setAppliedTaxes([$tax])
        ->setTaxableItemType('shipping');

    $taxItemCollection = $objectManager->create(\Magento\Sales\Model\ResourceModel\Order\Tax\Item::class);
    $taxItemCollection->save($salesOrderTaxItem);
}
