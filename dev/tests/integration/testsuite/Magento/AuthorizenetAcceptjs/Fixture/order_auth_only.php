<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface as TransactionBuilder;

/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();

$addressData = include __DIR__ . '/../../Sales/_files/address_data.php';
require __DIR__ . '/../../../Magento/Catalog/_files/product_simple.php';

$billingAddress = $objectManager->create(Address::class, ['data' => $addressData]);
$billingAddress->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)
    ->setAddressType('shipping');

/** @var OrderItem $orderItem */
$orderItem = $objectManager->create(OrderItem::class);
$orderItem->setProductId($product->getId())
    ->setQtyOrdered(2)
    ->setBasePrice($product->getPrice())
    ->setPrice($product->getPrice())
    ->setRowTotal($product->getPrice())
    ->setProductType('simple');

require __DIR__ . '/payment.php';

$order = $objectManager->create(Order::class);
$order->setIncrementId('100000002')
    ->setSubtotal($product->getPrice() * 2)
    ->setBaseSubtotal($product->getPrice() * 2)
    ->setCustomerEmail('admin@example.com')
    ->setCustomerIsGuest(true)
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress)
    ->setStoreId(
        $objectManager->get(StoreManagerInterface::class)->getStore()
            ->getId()
    )
    ->addItem($orderItem)
    ->setPayment($payment);

$payment->setParentTransactionId(1234);

/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->get(OrderRepositoryInterface::class);
$orderRepository->save($order);

/** @var TransactionBuilder $transactionBuilder */
$transactionBuilder = $objectManager->create(TransactionBuilder::class);
$transactionAuthorize = $transactionBuilder->setPayment($payment)
    ->setOrder($order)
    ->setTransactionId(1234)
    ->build(Transaction::TYPE_AUTH);

$transactionAuthorize->setAdditionalInformation('real_transaction_id', '1234');

$transactionRepository = $objectManager->create(TransactionRepositoryInterface::class);
$transactionRepository->save($transactionAuthorize);
