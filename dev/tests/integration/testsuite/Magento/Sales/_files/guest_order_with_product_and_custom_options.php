<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Model\ProductRepository;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple.php');

$addressData = include __DIR__ . '/../../../Magento/Sales/_files/address_data.php';

$objectManager = Bootstrap::getObjectManager();
/** @var OrderAddressInterface $billingAddress */
$billingAddress = $objectManager->create(OrderAddressInterface::class, ['data' => $addressData]);
$billingAddress->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType('shipping');

/** @var OrderPaymentInterface $payment */
$payment = $objectManager->create(OrderPaymentInterface::class);
$payment->setMethod('checkmo');

/** @var ProductRepository $productRepository */
$productRepository = $objectManager->get(ProductRepository::class);
$productRepository->cleanCache();

$optionValuesByType = [
    'field' => 'Test value',
    'date_time' => [
        'year' => '2021',
        'month' => '9',
        'day' => '9',
        'hour' => '2',
        'minute' => '2',
        'day_part' => 'am',
        'date_internal' => '2021-09-09 02:02:00',
    ],
    'drop_down' => '3-1-select',
    'radio' => '4-1-radio',
];

$requestInfo = ['options' => []];
$product = $productRepository->get('simple');
$productOptions = $product->getOptions();
foreach ($productOptions as $option) {
    $requestInfo['options'][$option->getOptionId()] = $optionValuesByType[$option->getType()];
}

/** @var OrderItem $orderItem */
$orderItem = $objectManager->create(OrderItem::class);
$orderItem->setProductId($product->getId());
$orderItem->setSku($product->getSku());
$orderItem->setQtyOrdered(1);
$orderItem->setBasePrice($product->getPrice());
$orderItem->setPrice($product->getPrice());
$orderItem->setRowTotal($product->getPrice());
$orderItem->setProductType($product->getTypeId());
$orderItem->setProductOptions(['info_buyRequest' => $requestInfo]);

/** @var Order $order */
$order = $objectManager->create(Order::class);
$order->setIncrementId('100000001');
$order->setState(Order::STATE_NEW);
$order->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_NEW));
$order->setCustomerIsGuest(true);
$order->setCustomerEmail('customer@example.com');
$order->setCustomerFirstname('firstname');
$order->setCustomerLastname('lastname');
$order->setBillingAddress($billingAddress);
$order->setShippingAddress($shippingAddress);
$order->setAddresses([$billingAddress, $shippingAddress]);
$order->setPayment($payment);
$order->addItem($orderItem);
$order->setStoreId($objectManager->get(StoreManagerInterface::class)->getStore()->getId());
$order->setSubtotal(100);
$order->setBaseSubtotal(100);
$order->setBaseGrandTotal(100);
$order->save();
