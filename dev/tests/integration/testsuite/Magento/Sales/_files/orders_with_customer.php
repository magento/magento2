<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Sales\Model\Order;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Address as OrderAddress;

require 'order.php';
/** @var Order $order */
/** @var Order\Payment $payment */
/** @var Order\Item $orderItem */
/** @var array $addressData Data for creating addresses for the orders. */
$orders = [
    [
        'increment_id' => '100000002',
        'state' => \Magento\Sales\Model\Order::STATE_NEW,
        'status' => 'processing',
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
        'grand_total' => 150.00,
        'base_grand_total' => 150.00,
        'subtotal' => 150.00,
        'total_paid' => 150.00,
        'store_id' => 1,
        'website_id' => 1,
    ],
    [
        'increment_id' => '100000006',
        'state' => \Magento\Sales\Model\Order::STATE_COMPLETE,
        'status' => 'complete',
        'grand_total' => 160.00,
        'base_grand_total' => 160.00,
        'subtotal' => 160.00,
        'total_paid' => 160.00,
        'store_id' => 1,
        'website_id' => 1,
    ],
];

/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->create(OrderRepositoryInterface::class);
/** @var array $orderData */
foreach ($orders as $orderData) {
    $newPayment = clone $payment;
    $newPayment->setId(null);
    /** @var $order \Magento\Sales\Model\Order */
    $order = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
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
        ->setProductType('simple');

    $order
        ->setData($orderData)
        ->addItem($orderItem)
        ->setCustomerIsGuest(false)
        ->setCustomerId(1)
        ->setCustomerEmail('customer@example.com')
        ->setBillingAddress($billingAddress)
        ->setShippingAddress($shippingAddress)
        ->setPayment($newPayment);

    $orderRepository->save($order);
}
