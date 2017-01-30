<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Helper\Bootstrap as BootstrapHelper;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndexer;
use Magento\Catalog\Model\Product;
use Magento\Sales\Model\Order\Address as OrderAddress;
use Magento\Sales\Model\Order\Payment as OrderPayment;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;

require __DIR__ . '/../../Store/_files/core_fixturestore.php';

$objectManager = BootstrapHelper::getObjectManager();

$objectManager->get(IndexerRegistry::class)
    ->get(FulltextIndexer::INDEXER_ID)
    ->reindexAll();

require __DIR__ . '/../../Catalog/_files/product_simple_duplicated.php';
/** @var Product $product */

$addressData = include __DIR__ . '/address_data.php';

$billingAddress = $objectManager->create(OrderAddress::class, ['data' => $addressData]);
$billingAddress->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType('shipping');

$payment = $objectManager->create(OrderPayment::class);
$payment->setMethod('checkmo');

/** @var OrderItem $orderItem */
$orderItem = $objectManager->create(OrderItem::class);
$orderItem->setProductId($product->getId())->setQtyOrdered(2);

/** @var Order $order */
$order = $objectManager->create(Order::class);
$order->setIncrementId('100000004')
    ->setState(Order::STATE_PROCESSING)
    ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING))
    ->setSubtotal(100)
    ->setBaseSubtotal(100)
    ->setCustomerIsGuest(true)
    ->setCustomerEmail('customer@null.com')
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress)
    ->setStoreId($objectManager->get(StoreManagerInterface::class)->getStore('fixturestore')->getId())
    ->addItem($orderItem)
    ->setPayment($payment);
$order->save();
