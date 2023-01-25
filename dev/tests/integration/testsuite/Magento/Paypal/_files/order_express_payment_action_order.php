<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface as TransactionBuilder;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Paypal/_files/order_express.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple.php');

$objectManager = Bootstrap::getObjectManager();
/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->get(OrderRepositoryInterface::class);
/** @var Order $order */
$order = $objectManager->get(Order::class)->loadByIncrementId('100000001');
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$product = $productRepository->get('simple');
/** @var TransactionBuilder $transactionBuilder */
$transactionBuilder = $objectManager->create(TransactionBuilder::class);
$transaction = $transactionBuilder->setPayment($order->getPayment())
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

$orderRepository->save($order);
