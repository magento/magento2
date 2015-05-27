<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

require __DIR__ . '/../../../Magento/Store/_files/core_fixturestore.php';

require __DIR__ . '/../../../Magento/Catalog/_files/product_simple_duplicated.php';
/** @var \Magento\Catalog\Model\Product $product */

$addressData = include __DIR__ . '/address_data.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$billingAddress = $objectManager->create('Magento\Sales\Model\Order\Address', ['data' => $addressData]);
$billingAddress->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType('shipping');

$payment = $objectManager->create('Magento\Sales\Model\Order\Payment');
$payment->setMethod('checkmo');

/** @var \Magento\Sales\Model\Order\Item $orderItem */
$orderItem = $objectManager->create('Magento\Sales\Model\Order\Item');
$orderItem->setProductId($product->getId())->setQtyOrdered(2);

/** @var \Magento\Sales\Model\Order $order */
$order = $objectManager->create('Magento\Sales\Model\Order');
$order->setIncrementId(
    '100000004'
)->setState(
    \Magento\Sales\Model\Order::STATE_PROCESSING
)->setStatus(
    $order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_PROCESSING)
)->setSubtotal(
    100
)->setBaseSubtotal(
    100
)->setCustomerIsGuest(
    true
)->setCustomerEmail(
    'customer@null.com'
)->setBillingAddress(
    $billingAddress
)->setShippingAddress(
    $shippingAddress
)->setStoreId(
    $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore('fixturestore')->getId()
)->addItem(
    $orderItem
)->setPayment(
    $payment
);
$order->save();
