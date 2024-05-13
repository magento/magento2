<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\GiftMessage\Model\Message;
use Magento\GiftMessage\Model\ResourceModel\Message as MessageResource;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address as OrderAddress;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple.php');

$addressData = include __DIR__ . '/../../../../Magento/Sales/_files/address_data.php';

$objectManager = Bootstrap::getObjectManager();

/** @var Order $order */
/** @var Order\Payment $payment */
/** @var Order\Item $orderItem */
/** @var array $addressData Data for creating addresses for the orders. */
$orders = [
    [
        'increment_id' => '999999990',
        'state' => Order::STATE_NEW,
        'status' => 'processing',
        'grand_total' => 120.00,
        'subtotal' => 120.00,
        'base_grand_total' => 120.00,
        'store_id' => 1,
        'website_id' => 1,
    ],
    [
        'increment_id' => '999999991',
        'state' => Order::STATE_PROCESSING,
        'status' => 'processing',
        'grand_total' => 130.00,
        'base_grand_total' => 130.00,
        'subtotal' => 130.00,
        'total_paid' => 130.00,
        'store_id' => 1,
        'website_id' => 1,
    ]
];

/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->create(OrderRepositoryInterface::class);

$payment = $objectManager->create(\Magento\Sales\Model\Order\Payment::class);
$payment->setMethod('checkmo');
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$product = $productRepository->get('simple');

/** @var array $orderData */
foreach ($orders as $orderData) {
    /** @var  Magento\Sales\Model\Order $order */
    $order = $objectManager->create(Order::class);

    // Reset addresses
    /** @var Order\Address $billingAddress */
    $billingAddress = $objectManager->create(OrderAddress::class, ['data' => $addressData]);
    $billingAddress->setAddressType('billing');

    $shippingAddress = clone $billingAddress;
    $shippingAddress->setId(null)->setAddressType('shipping');

    /** @var MessageResource $message */
    $message = $objectManager->create(MessageResource::class);

    /** @var Message $message */
    $messageModel = $objectManager->create(Message::class);

    $messageModel->setSender('John Doe');
    $messageModel->setRecipient('Jane Roe');
    $messageModel->setMessage('Gift Message Text');
    $message->save($messageModel);

    /** @var MessageResource $productMessage */
    $productMessage = $objectManager->create(MessageResource::class);
    /** @var Message $productMessageModel */
    $productMessageModel = $objectManager->create(Message::class);

    $productMessageModel->setSender('Jack');
    $productMessageModel->setRecipient('Luci');
    $productMessageModel->setMessage('Good Job!');
    $productMessage->save($productMessageModel);

    /** @var Order\Item $orderItem */
    $orderItem = $objectManager->create(Order\Item::class);
    $orderItem->setProductId($product->getId())
        ->setQtyOrdered(2)
        ->setBasePrice($product->getPrice())
        ->setPrice($product->getPrice())
        ->setRowTotal($product->getPrice())
        ->setProductType('simple')
        ->setGiftMessageId($productMessageModel->getId());

    $order
        ->setData($orderData)
        ->addItem($orderItem)
        ->setCustomerIsGuest(false)
        ->setCustomerId(1)
        ->setCustomerEmail('customer@example.com')
        ->setBillingAddress($billingAddress)
        ->setShippingAddress($shippingAddress)
        ->setPayment($payment);
    $order->setGiftMessageId($messageModel->getId());
    $orderRepository->save($order);
}
