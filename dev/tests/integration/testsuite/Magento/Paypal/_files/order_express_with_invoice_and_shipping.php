<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DB\Transaction;
use Magento\Paypal\Model\Config;
use Magento\Sales\Api\InvoiceManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple.php');

$objectManager = Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$product = $productRepository->get('simple');
$addressData = include __DIR__ . '/address_data.php';
$billingAddress = $objectManager->create(
    Address::class,
    ['data' => $addressData]
);
$billingAddress->setAddressType('billing');
$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType('shipping');

$payment = $objectManager->create(Payment::class);
$payment->setMethod(Config::METHOD_WPP_EXPRESS);

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

$itemsAmount = $product->getPrice();
$shippingAmount = 20;
$totalAmount = $itemsAmount + $shippingAmount;

/** @var Order $order */
$order = $objectManager->create(Order::class);
$order->setCustomerEmail('co@co.co')
    ->setIncrementId('100000001')
    ->addItem($orderItem)
    ->setSubtotal($itemsAmount)
    ->setBaseSubtotal($itemsAmount)
    ->setBaseGrandTotal($totalAmount)
    ->setGrandTotal($totalAmount)
    ->setBaseCurrencyCode('USD')
    ->setCustomerIsGuest(true)
    ->setStoreId(1)
    ->setEmailSent(true)
    ->setState(Order::STATE_PROCESSING)
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress)
    ->setBaseTotalPaid($totalAmount)
    ->setTotalPaid($totalAmount)
    ->setData('base_to_global_rate', 1)
    ->setData('base_to_order_rate', 1)
    ->setData('shipping_amount', $shippingAmount)
    ->setData('base_shipping_amount', $shippingAmount)
    ->setPayment($payment);

/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->get(OrderRepositoryInterface::class);
$orderRepository->save($order);

/** @var InvoiceService $invoiceService */
$invoiceService = $objectManager->create(InvoiceManagementInterface::class);

/** @var Transaction $transaction */
$transaction = $objectManager->create(Transaction::class);

$invoice = $invoiceService->prepareInvoice($order, [$orderItem->getId() => 1]);
$invoice->register();

$transaction->addObject($invoice)->addObject($order)->save();
