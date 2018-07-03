<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface as TransactionBuilder;
use Magento\Sales\Model\Order\Item;

require __DIR__ . '/order_express.php';
require __DIR__ . '/../../../Magento/Catalog/_files/product_simple.php';

$objectManager = Bootstrap::getObjectManager();

/** @var TransactionBuilder $transactionBuilder */
$transactionBuilder = $objectManager->create(TransactionBuilder::class);
$transaction = $transactionBuilder->setPayment($payment)
    ->setOrder($order)
    ->setTransactionId(1)
    ->build(Transaction::TYPE_ORDER);

$transactionRepository = $objectManager->create(TransactionRepositoryInterface::class);
$transactionRepository->save($transaction);

/** @var Item $orderItem */
$orderItem = $objectManager->create(Item::class);
$orderItem->setProductId($product->getId())->setQtyOrdered(1);
$orderItem->setBasePrice($product->getPrice());
$orderItem->setPrice($product->getPrice());
$orderItem->setRowTotal($product->getPrice());
$orderItem->setRowTotalInclTax($product->getPrice());
$orderItem->setBaseRowTotal($product->getPrice());
$orderItem->setBaseRowTotalInclTax($product->getPrice());
$orderItem->setBaseRowInvoiced($product->getPrice());
$orderItem->setProductType('simple');

$totalAmount = $product->getPrice();

/** @var Order $order */
$order->addItem($orderItem)
    ->setSubtotal($totalAmount)
    ->setBaseSubtotal($totalAmount)
    ->setBaseGrandTotal($totalAmount)
    ->setGrandTotal($totalAmount);

/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->get(OrderRepositoryInterface::class);
$orderRepository->save($order);
