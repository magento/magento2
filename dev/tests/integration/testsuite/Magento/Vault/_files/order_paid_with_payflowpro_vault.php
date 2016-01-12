<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/../../../Magento/Sales/_files/default_rollback.php';
include __DIR__ . '/../../../Magento/Customer/_files/customer.php';
require __DIR__ . '/../../../Magento/Catalog/_files/product_simple.php';
/** @var \Magento\Catalog\Model\Product $product */

$addressData = include __DIR__ . '/../../../Magento/Sales/_files/address_data.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$billingAddress = $objectManager->create('Magento\Sales\Model\Order\Address', ['data' => $addressData]);
$billingAddress->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType('shipping');

/** @var \Magento\Sales\Model\Order\Payment $payment */
$payment = $objectManager->create('Magento\Sales\Model\Order\Payment');
$payment->setMethod('checkmo');

/** @var \Magento\Sales\Model\Order\Item $orderItem */
$orderItem = $objectManager->create('Magento\Sales\Model\Order\Item');
$orderItem->setProductId($product->getId())->setQtyOrdered(2);
$orderItem->setBasePrice($product->getPrice());
$orderItem->setPrice($product->getPrice());
$orderItem->setRowTotal($product->getPrice());
$orderItem->setProductType('simple');

/** @var \Magento\Sales\Model\Order $order */
$order = $objectManager->create('Magento\Sales\Model\Order');
$order->setIncrementId(
    '100000001'
)->setState(
    \Magento\Sales\Model\Order::STATE_PROCESSING
)->setStatus(
    $order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_PROCESSING)
)->setSubtotal(
    100
)->setBaseSubtotal(
    100
)->setBaseGrandTotal(
    100
)->setCustomerEmail(
    'customer@null.com'
)->setBillingAddress(
    $billingAddress
)->setShippingAddress(
    $shippingAddress
)->setStoreId(
    $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId()
)->addItem(
    $orderItem
)->setPayment(
    $payment
);

$customerIdFromFixture = 1;
/** @var $order \Magento\Sales\Model\Order */
$order->setCustomerId($customerIdFromFixture)->setCustomerIsGuest(false);

$tokenString = 'asdfg';
$payment->setTransactionId('trx1');
$payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH);
//$payment->setTransactionAdditionalInfo('token', $tokenString);


/** @var \Magento\Vault\Model\PaymentToken $paymentToken */
$paymentToken = $objectManager->create('Magento\Vault\Model\PaymentToken');
$paymentToken->setCustomerId($customerIdFromFixture);
$paymentToken->setGatewayToken($tokenString);
$paymentToken->setPaymentMethodCode($payment->getMethod());
$paymentToken->setCreatedAt('2015-12-08 18:06:10');

/** @var \Magento\Sales\Api\Data\OrderPaymentExtensionFactory $orderPaymentExtensionFactory */
$orderPaymentExtensionFactory = $objectManager->create('Magento\Sales\Api\Data\OrderPaymentExtensionFactory');
$orderPaymentExtension = $orderPaymentExtensionFactory->create();
$orderPaymentExtension->setVaultPaymentToken($paymentToken);
$payment->setExtensionAttributes($orderPaymentExtension);


$order->save();
