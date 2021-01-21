<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Address as OrderAddress;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/order.php');
$objectManager = Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$product = $productRepository->get('simple');
/** @var Order $order */
$order = $objectManager->get(OrderInterfaceFactory::class)->create()->loadByIncrementId('100000001');
$payment = $order->getPayment();
$orderItems = $order->getItems();
$orderItem = reset($orderItems);
$addressData = include __DIR__ . '/address_data.php';
$orders = [
    [
        'increment_id' => '100000002',
        'state' => \Magento\Sales\Model\Order::STATE_NEW,
        'status' => 'processing',
        'order_currency_code' =>'USD',
        'base_currency_code' =>'USD',
        'grand_total' => 120.00,
        'subtotal' => 120.00,
        'base_grand_total' => 120.00,
        'store_id' => 1,
        'website_id' => 1,
    ],
    [
        'increment_id' => '100000003',
        'state' => \Magento\Sales\Model\Order::STATE_PROCESSING,
        'status' => 'processing',
        'order_currency_code' =>'USD',
        'base_currency_code' =>'USD',
        'grand_total' => 130.00,
        'base_grand_total' => 130.00,
        'subtotal' => 130.00,
        'total_paid' => 130.00,
        'store_id' => 0,
        'website_id' => 0,
    ],
    [
        'increment_id' => '100000004',
        'state' => \Magento\Sales\Model\Order::STATE_PROCESSING,
        'status' => 'closed',
        'order_currency_code' =>'USD',
        'base_currency_code' =>'USD',
        'grand_total' => 140.00,
        'base_grand_total' => 140.00,
        'subtotal' => 140.00,
        'store_id' => 1,
        'website_id' => 1,
    ],
    [
        'increment_id' => '100000005',
        'state' => \Magento\Sales\Model\Order::STATE_COMPLETE,
        'status' => 'complete',
        'order_currency_code' =>'USD',
        'base_currency_code' =>'USD',
        'grand_total' => 150.00,
        'base_grand_total' => 150.00,
        'subtotal' => 150.00,
        'total_paid' => 150.00,
        'store_id' => 1,
        'website_id' => 1,
    ],
    [
        'increment_id' => '100000006',
        'state' => \Magento\Sales\Model\Order::STATE_PROCESSING,
        'status' => 'Processing',
        'order_currency_code' =>'USD',
        'base_currency_code' =>'USD',
        'grand_total' => 160.00,
        'base_grand_total' => 160.00,
        'subtotal' => 160.00,
        'total_paid' => 160.00,
        'store_id' => 1,
        'website_id' => 1,
    ],
    [
        'increment_id' => '100000007',
        'state' => \Magento\Sales\Model\Order::STATE_PROCESSING,
        'status' => 'Processing',
        'order_currency_code' =>'USD',
        'base_currency_code' =>'USD',
        'grand_total' => 180.00,
        'base_grand_total' => 180.00,
        'subtotal' => 170.00,
        'tax_amount' => 5.00,
        'shipping_amount'=> 5.00,
        'base_shipping_amount'=> 4.00,
        'store_id' => 1,
        'website_id' => 1,
    ],
    [
        'increment_id' => '100000008',
        'state' => \Magento\Sales\Model\Order::STATE_PROCESSING,
        'status' => 'Processing',
        'order_currency_code' =>'USD',
        'base_currency_code' =>'USD',
        'grand_total' => 190.00,
        'base_grand_total' => 190.00,
        'subtotal' => 180.00,
        'tax_amount' => 5.00,
        'shipping_amount'=> 5.00,
        'base_shipping_amount'=> 4.00,
        'store_id' => 1,
        'website_id' => 1,
    ]
];

/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->create(OrderRepositoryInterface::class);
/** @var array $orderData */
foreach ($orders as $orderData) {
    $newPayment = clone $payment;
    $newPayment->setId(null);
    /** @var $order \Magento\Sales\Model\Order */
    $order = $objectManager->create(
        \Magento\Sales\Model\Order::class
    );

    // Reset addresses
    /** @var Order\Address $billingAddress */
    $billingAddress = $objectManager->create(OrderAddress::class, ['data' => $addressData]);
    $billingAddress->setAddressType('billing');

    $shippingAddress = clone $billingAddress;
    $shippingAddress->setId(null)->setAddressType('shipping');

    /** @var Order\Item $orderItem */
    $orderItem = $objectManager->create(Order\Item::class);
    $orderItem->setProductId($product->getId())
        ->setQtyOrdered(2)
        ->setBasePrice($product->getPrice())
        ->setPrice($product->getPrice())
        ->setRowTotal($product->getPrice())
        ->setProductType('simple')
        ->setName($product->getName())
        ->setSku($product->getSku());

    $order->setData($orderData)
        ->addItem($orderItem)
        ->setCustomerIsGuest(false)
        ->setCustomerId(1)
        ->setCustomerEmail('customer@example.com')
        ->setBillingAddress($billingAddress)
        ->setShippingAddress($shippingAddress)
        ->setPayment($newPayment);

    $orderRepository->save($order);
}
