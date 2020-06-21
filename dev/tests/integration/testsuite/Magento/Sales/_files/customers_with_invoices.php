<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/three_customers.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple.php');

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$addressData = include __DIR__ . '/../../../Magento/Sales/_files/address_data.php';

$billingAddress = $objectManager->create(\Magento\Sales\Model\Order\Address::class, ['data' => $addressData]);
$billingAddress->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType('shipping');

$payment = $objectManager->create(\Magento\Sales\Model\Order\Payment::class);
$payment->setMethod('checkmo');

$payment2 = $objectManager->create(\Magento\Sales\Model\Order\Payment::class);
$payment2->setMethod('checkmo');

/** @var $product \Magento\Catalog\Model\Product */
$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
$repository = $objectManager->create(\Magento\Catalog\Model\ProductRepository::class);
$product = $repository->get('simple');

$optionValuesByType = [
    'field' => 'Test value',
    'date_time' => [
        'year' => '2015',
        'month' => '9',
        'day' => '9',
        'hour' => '2',
        'minute' => '2',
        'day_part' => 'am',
        'date_internal' => '',
    ],
    'drop_down' => '3-1-select',
    'radio' => '4-1-radio',
];

$requestInfo = ['options' => []];
$productOptions = $product->getOptions();
foreach ($productOptions as $option) {
    $requestInfo['options'][$option->getOptionId()] = $optionValuesByType[$option->getType()];
}

/** @var \Magento\Sales\Model\Order\Item $orderItem */
$orderItem = $objectManager->create(\Magento\Sales\Model\Order\Item::class);
$orderItem->setProductId($product->getId());
$orderItem->setSku($product->getSku());
$orderItem->setName($product->getName());
$orderItem->setQtyOrdered(1);
$orderItem->setBasePrice($product->getPrice());
$orderItem->setPrice($product->getPrice());
$orderItem->setRowTotal($product->getPrice());
$orderItem->setProductType($product->getTypeId());
$orderItem->setProductOptions(['info_buyRequest' => $requestInfo]);

/** @var \Magento\Sales\Model\Order $order */
$order = $objectManager->create(\Magento\Sales\Model\Order::class);
$order->setIncrementId('100000001');
$order->setState(\Magento\Sales\Model\Order::STATE_NEW);
$order->setStatus($order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_NEW));
$order->setCustomerIsGuest(true);
$order->setCustomerEmail('customer@null.com');
$order->setCustomerFirstname('firstname');
$order->setCustomerLastname('lastname');
$order->setBillingAddress($billingAddress);
$order->setShippingAddress($shippingAddress);
$order->setAddresses([$billingAddress, $shippingAddress]);
$order->setPayment($payment);
$order->addItem($orderItem);
$order->setStoreId($objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->getStore()->getId());
$order->setSubtotal(100);
$order->setBaseSubtotal(100);
$order->setBaseGrandTotal(100);
$order->setOrderCurrencyCode('USD');
$order->setGrandTotal(100);
$order->setCustomerId(1)
    ->setCustomerIsGuest(false)
    ->save();

$orderService = $objectManager->create(
    \Magento\Sales\Api\InvoiceManagementInterface::class
);
$invoice = $orderService->prepareInvoice($order);
$invoice->register();
$order = $invoice->getOrder();
$order->setIsInProcess(true);
$transactionSave = $objectManager
    ->create(\Magento\Framework\DB\Transaction::class);
$transactionSave->addObject($invoice)->addObject($order)->save();

/** @var \Magento\Sales\Model\Order\Item $orderItem */
$orderItem2 = $objectManager->create(\Magento\Sales\Model\Order\Item::class);
$orderItem2->setProductId($product->getId());
$orderItem2->setSku($product->getSku());
$orderItem2->setQtyOrdered(1);
$orderItem2->setBasePrice($product->getPrice());
$orderItem2->setPrice($product->getPrice());
$orderItem2->setRowTotal($product->getPrice());
$orderItem2->setProductType($product->getTypeId());
$orderItem2->setProductOptions(['info_buyRequest' => $requestInfo]);

/** @var \Magento\Sales\Model\Order $order */
$order2 = $objectManager->create(\Magento\Sales\Model\Order::class);
$order2->setIncrementId('100000002');
$order2->setState(\Magento\Sales\Model\Order::STATE_NEW);
$order2->setStatus($order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_NEW));
$order2->setCustomerIsGuest(true);
$order2->setCustomerEmail('customer@null.com');
$order2->setCustomerFirstname('firstname');
$order2->setCustomerLastname('lastname');
$order2->setBillingAddress($billingAddress);
$order2->setShippingAddress($shippingAddress);
$order2->setAddresses([$billingAddress, $shippingAddress]);
$order2->setPayment($payment2);
$order2->addItem($orderItem2);
$order2->setStoreId($objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->getStore()->getId());
$order2->setSubtotal(100);
$order2->setBaseSubtotal(100);
$order2->setBaseGrandTotal(100);
$order2->setCustomerId(2)
    ->setCustomerIsGuest(false)
    ->save();

$invoice2 = $orderService->prepareInvoice($order2);
$invoice2->register();
$order2 = $invoice2->getOrder();
$order2->setIsInProcess(true);
$transactionSave = $objectManager
    ->create(\Magento\Framework\DB\Transaction::class);
$transactionSave->addObject($invoice)->addObject($order2)->save();
