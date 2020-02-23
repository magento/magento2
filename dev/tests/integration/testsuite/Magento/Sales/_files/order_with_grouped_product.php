<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address as OrderAddress;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Sales\Model\Order\Payment;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

require 'default_rollback.php';
require __DIR__ . '/../../../Magento/GroupedProduct/_files/product_grouped_with_simple.php';
$addressData = include __DIR__ . '/address_data.php';

$objectManager = Bootstrap::getObjectManager();
$billingAddress = $objectManager->create(OrderAddress::class, ['data' => $addressData]);
$billingAddress->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType('shipping');

$payment = $objectManager->create(Payment::class);
$payment->setMethod('checkmo')
    ->setAdditionalInformation('last_trans_id', '11122')
    ->setAdditionalInformation(
        'metadata',
        [
            'type' => 'free',
            'fraudulent' => false,
        ]
    );
$product = $objectManager->get(ProductRepositoryInterface::class)->get('simple_11');
$orderItem = $objectManager->create(OrderItem::class);
$orderItem->setProductId($product->getId())
    ->setQtyOrdered(2)
    ->setBasePrice($product->getPrice())
    ->setPrice($product->getPrice())
    ->setRowTotal($product->getPrice())
    ->setProductType('grouped')
    ->setName($product->getName())
    ->setSku($product->getSku());

$order = $objectManager->create(Order::class);
$order->setIncrementId('100000002')
    ->setState(Order::STATE_PROCESSING)
    ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING))
    ->setSubtotal(100)
    ->setGrandTotal(100)
    ->setBaseSubtotal(100)
    ->setBaseGrandTotal(100)
    ->setCustomerIsGuest(true)
    ->setCustomerEmail('customer@null.com')
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress)
    ->setStoreId($objectManager->get(StoreManagerInterface::class)->getStore()->getId())
    ->addItem($orderItem)
    ->setPayment($payment);
$orderRepository = $objectManager->create(OrderRepositoryInterface::class);
$orderRepository->save($order);
