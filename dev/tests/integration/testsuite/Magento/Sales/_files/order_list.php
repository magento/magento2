<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Address as OrderAddress;
use Magento\Sales\Model\Order\Payment;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/order.php');

$objectManager = Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$product = $productRepository->get('simple');
$addressData = include __DIR__ . '/address_data.php';
$orders = [
    [
        'increment_id' => '100000002',
        'state' => \Magento\Sales\Model\Order::STATE_NEW,
        'status' => 'processing',
        'grand_total' => 120.00,
        'subtotal' => 120.00,
        'base_grand_total' => 120.00,
        'store_id' => 1,
        'website_id' => 1
    ],
    [
        'increment_id' => '100000003',
        'state' => \Magento\Sales\Model\Order::STATE_PROCESSING,
        'status' => 'processing',
        'grand_total' => 140.00,
        'base_grand_total' => 140.00,
        'subtotal' => 140.00,
        'store_id' => 0,
        'website_id' => 0
    ],
    [
        'increment_id' => '100000004',
        'state' => \Magento\Sales\Model\Order::STATE_PROCESSING,
        'status' => 'closed',
        'grand_total' => 140.00,
        'base_grand_total' => 140.00,
        'subtotal' => 140.00,
        'store_id' => 1,
        'website_id' => 1
    ],
];

$orderList = [];
/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->create(OrderRepositoryInterface::class);
/** @var array $orderData */
foreach ($orders as $orderData) {
    /** @var $order \Magento\Sales\Model\Order */
    $order = $objectManager->create(\Magento\Sales\Model\Order::class);

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
    /** @var OrderItem $orderItem */
    $orderItem = $objectManager->create(OrderItem::class);
    $orderItem->setProductId($product->getId())
        ->setQtyOrdered(2)
        ->setBasePrice($product->getPrice())
        ->setPrice($product->getPrice())
        ->setRowTotal($product->getPrice())
        ->setProductType('simple')
        ->setName($product->getName())
        ->setSku($product->getSku());

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
