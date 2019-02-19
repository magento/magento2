<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Sales\Model\Order;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Address as OrderAddress;
use Magento\Sales\Model\Order\Payment;

require 'order.php';
/** @var Order $order */
/** @var  Order\Payment $payment */
/** @var  Order\Item $orderItem */
/** @var  Order\Address $billingAddress */
/** @var  Order\Address $shippingAddress */
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
        'payment' => $payment
    ],
    [
        'increment_id' => '100000003',
        'state' => \Magento\Sales\Model\Order::STATE_PROCESSING,
        'status' => 'processing',
        'grand_total' => 140.00,
        'base_grand_total' => 140.00,
        'subtotal' => 140.00,
        'store_id' => 0,
        'website_id' => 0,
        'payment' => $payment
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
        'payment' => $payment
    ],
];

$orderList = [];
/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->create(OrderRepositoryInterface::class);
/** @var array $orderData */
foreach ($orders as $orderData) {
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

    /** @var Payment $payment */
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

    $order
        ->setData($orderData)
        ->addItem($orderItem)
        ->setCustomerIsGuest(true)
        ->setCustomerEmail('customer@null.com')
        ->setBillingAddress($billingAddress)
        ->setShippingAddress($shippingAddress)
        ->setPayment($payment);

    $orderRepository->save($order);
    $orderList[] = $order;
}
