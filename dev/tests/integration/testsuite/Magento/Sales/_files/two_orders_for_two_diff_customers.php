<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/order.php');
Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/two_customers.php');

$objectManager = Bootstrap::getObjectManager();
/** @var Order $order */
$order = $objectManager->get(OrderInterfaceFactory::class)->create()->loadByIncrementId('100000001');
$order->setCustomerId(1)->setCustomerIsGuest(false)->save();

$shippingAddress = $order->getShippingAddress();
$billingAddress = $order->getBillingAddress();
$orderItems = $order->getItems();
$orderItem = reset($orderItems);
$payment2 = $objectManager->create(\Magento\Sales\Model\Order\Payment::class);
$payment2->setMethod('checkmo');

/** @var \Magento\Sales\Model\Order $order */
$order2 = $objectManager->create(\Magento\Sales\Model\Order::class);
$order2->setIncrementId('100000002')
    ->setState(
        \Magento\Sales\Model\Order::STATE_PROCESSING
    )->setStatus(
        $order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_PROCESSING)
    )->setSubtotal(
        100
    )->setBaseSubtotal(
        100
    )->setBaseGrandTotal(
        100
    )->setCustomerIsGuest(
        true
    )->setCustomerEmail(
        'customer2@null.com'
    )->setBillingAddress(
        $billingAddress
    )->setShippingAddress(
        $shippingAddress
    )->setStoreId(
        $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->getStore()->getId()
    )->addItem(
        $orderItem
    )->setPayment(
        $payment2
    );

$order2->save();
$order2->setCustomerId(2)->setCustomerIsGuest(false)->save();
