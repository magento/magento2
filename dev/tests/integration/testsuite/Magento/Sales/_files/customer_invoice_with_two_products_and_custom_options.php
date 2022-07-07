<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\Sales\Api\InvoiceManagementInterface;

Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/customer.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/products_in_category.php');

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$addressData = include __DIR__ . '/../../../Magento/Sales/_files/address_data.php';

$billingAddress = $objectManager->create(\Magento\Sales\Model\Order\Address::class, ['data' => $addressData]);
$billingAddress->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType('shipping');

$payment = $objectManager->create(\Magento\Sales\Model\Order\Payment::class);
$payment->setMethod('checkmo');

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

/** @var $product \Magento\Catalog\Model\Product */
$product2 = $objectManager->create(\Magento\Catalog\Model\Product::class);
$product2 = $repository->get('simple_with_cross');

/** @var \Magento\Sales\Model\Order\Item $orderItem */
$orderItem = $objectManager->create(\Magento\Sales\Model\Order\Item::class);
$orderItem->setProductId($product->getId());
$orderItem->setName($product->getName());
$orderItem->setSku($product->getSku());
$orderItem->setQtyOrdered(1);
$orderItem->setBasePrice($product->getPrice());
$orderItem->setPrice($product->getPrice());
$orderItem->setRowTotal($product->getPrice());
$orderItem->setProductType($product->getTypeId());
$orderItem->setProductOptions(['info_buyRequest' => $requestInfo]);

/** @var \Magento\Sales\Model\Order\Item $orderItem2 */
$orderItem2 = $objectManager->create(\Magento\Sales\Model\Order\Item::class);
$orderItem2->setProductId($product2->getId());
$orderItem2->setSku($product2->getSku());
$orderItem2->setName($product2->getName());
$orderItem2->setQtyOrdered(1);
$orderItem2->setBasePrice($product2->getPrice());
$orderItem2->setPrice($product2->getPrice());
$orderItem2->setRowTotal($product2->getPrice());
$orderItem2->setProductType($product2->getTypeId());
$orderItem2->setProductOptions(['info_buyRequest' => $requestInfo]);

/** @var \Magento\Sales\Model\Order $order */
$order = $objectManager->create(\Magento\Sales\Model\Order::class);
$order->setIncrementId('100000001');
$order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
$order->setStatus($order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_PROCESSING));
$order->setCustomerIsGuest(true);
$order->setCustomerEmail('customer@null.com');
$order->setCustomerFirstname('firstname');
$order->setCustomerLastname('lastname');
$order->setBillingAddress($billingAddress);
$order->setShippingAddress($shippingAddress);
$order->setAddresses([$billingAddress, $shippingAddress]);
$order->setPayment($payment);
$order->addItem($orderItem);
$order->addItem($orderItem2);
$order->setStoreId($objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->getStore()->getId());
$order->setSubtotal(100);
$order->setBaseSubtotal(100);
$order->setBaseGrandTotal(100);
$order->setGrandTotal(100);
$order->setOrderCurrencyCode('USD');
$order->setBaseCurrencyCode('EUR');
$order->setCustomerId(1)
    ->setCustomerIsGuest(false)
    ->save();
/** @var InvoiceManagementInterface $orderService */
$orderService = $objectManager->create(
    InvoiceManagementInterface::class
);
$invoice = $orderService->prepareInvoice($order);
$invoice->register();
$order = $invoice->getOrder();
$order->setIsInProcess(true);
$transactionSave = $objectManager
    ->create(\Magento\Framework\DB\Transaction::class);
$transactionSave->addObject($invoice)->addObject($order)->save();
