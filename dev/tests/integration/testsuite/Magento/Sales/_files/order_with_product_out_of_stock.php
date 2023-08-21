<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address as OrderAddress;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Sales\Model\Order\Payment;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\Catalog\Model\Product;
use Magento\TestFramework\Helper\Bootstrap;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/default_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/customer.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple_without_custom_options.php');

$objectManager = Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$product = $productRepository->get('simple-2');
$billingAddress = $objectManager->create(
    OrderAddress::class,
    [
        'data' => [
            'region' => 'CA',
            'region_id' => '12',
            'postcode' => '11111',
            'lastname' => 'lastname',
            'firstname' => 'firstname',
            'street' => 'street',
            'city' => 'Los Angeles',
            'email' => 'admin@example.com',
            'telephone' => '11111111',
            'country_id' => 'US',
        ],
    ],
);
$billingAddress->setAddressType('billing');
$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType('shipping');

/** @var Payment $payment */
$payment = $objectManager->create(\Magento\Sales\Model\Order\Payment::class);
$payment->setMethod('checkmo');

/** @var OrderItem $orderItem */
$orderItem = $objectManager->create(OrderItem::class);
$orderItem->setProductId($product->getId())
    ->setQtyOrdered(2)
    ->setBasePrice($product->getPrice())
    ->setPrice($product->getPrice())
    ->setRowTotal($product->getPrice())
    ->setProductType('simple-2')
    ->setName($product->getName())
    ->setSku($product->getSku())
    ->setProductOptions(['info_buyRequest' => ['qty' => 1]]);

/** @var Order $order */
$order = $objectManager->create(Order::class);
$order->setIncrementId('100000001')
    ->setCustomerIsGuest(false)
    ->setCustomerId(1)
    ->setCustomerEmail('customer@null.com')
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress)
    ->setStoreId($objectManager->get(StoreManagerInterface::class)->getStore()->getId())
    ->addItem($orderItem)
    ->setPayment($payment);
/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->create(OrderRepositoryInterface::class);
$orderRepository->save($order);

// load product and set it out of stock
/** @var ProductRepositoryInterface $repository */
$productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
$productSku = 'simple-2';
/** @var Product $product */
$product = $productRepository->get($productSku);
// set product as out of stock
$product->setStockData(
    [
        'use_config_manage_stock'   => 1,
        'qty'                       => 0,
        'is_qty_decimal'            => 0,
        'is_in_stock'               => 0,
    ]
);
$productRepository->save($product);
